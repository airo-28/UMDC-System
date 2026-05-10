<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Mail\TwoFactorOtpMail;

class TwoFactorController extends Controller
{
    /**
     * Show the OTP verification form.
     */
    public function show(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    /**
     * Verify the submitted OTP and complete login.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $userId = $request->session()->get('2fa_user_id');

        if (!$userId) {
            return redirect()->route('login')->withErrors(['otp' => 'Session expired. Please log in again.']);
        }

        $cachedOtp = Cache::get('2fa_otp_' . $userId);

        if (!$cachedOtp || $request->otp != $cachedOtp) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP. Please try again.']);
        }

        // OTP matched — clear it and log the user in
        Cache::forget('2fa_otp_' . $userId);
        $request->session()->forget('2fa_user_id');

        $user = \App\Models\User::find($userId);
        Auth::login($user, remember: false);
        $request->session()->regenerate();

        return match ($user->role) {
            'cashier'           => redirect()->route('pos'),
            'kitchen_manager'   => redirect()->route('kp'),
            'inventory_manager' => redirect()->route('im'),
            default             => redirect()->route('reports.dashboard'),
        };
    }

    /**
     * Resend a fresh OTP to the user.
     */
    public function resend(Request $request)
    {
        $userId = $request->session()->get('2fa_user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);
        $sent = self::sendOtp($user);

        if (!$sent) {
            return back()->withErrors(['otp' => 'Could not resend email. Please try again later.']);
        }

        return back()->with('resent', 'A new OTP has been sent to your email.');
    }

    /**
     * Generate OTP, cache it, and send via Mailtrap HTTP API or fallback Laravel Mail.
     * Returns true on success, false on failure.
     */
    public static function sendOtp(\App\Models\User $user): bool
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache for 5 minutes
        Cache::put('2fa_otp_' . $user->id, $otp, now()->addMinutes(5));

        $apiToken = config('services.brevo.api_token');

        if ($apiToken) {
            // Use Mailtrap HTTP API — works on Render (no SMTP port blocking)
            return self::sendViaMailtrap($user, $otp, $apiToken);
        }

        // Fallback: use Laravel Mail (works locally with Gmail SMTP)
        try {
            Mail::to($user->email)->send(new TwoFactorOtpMail($otp, $user->first_name));
            return true;
        } catch (\Exception $e) {
            Log::error('2FA mail failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send OTP email via Brevo (Sendinblue) HTTP API — no SMTP, works on Render.
     */
    private static function sendViaMailtrap(\App\Models\User $user, string $otp, string $apiToken): bool
    {
        $fromAddress = config('mail.from.address', 'noreply@example.com');
        $fromName    = config('mail.from.name', 'UM Dining Center');

        $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 480px; margin: auto; padding: 32px;
                    background: #fff; border-radius: 16px; border: 1px solid #f0f0f0;'>
            <div style='text-align: center; margin-bottom: 24px;'>
                <h2 style='color: #2d3436; margin-top: 16px;'>&#128737; Two-Factor Verification</h2>
            </div>
            <p style='color: #636e72;'>Hello, <strong>{$user->first_name}</strong>!</p>
            <p style='color: #636e72;'>Use the code below to complete your login to the UM Dining Center System.</p>
            <div style='text-align: center; margin: 32px 0;'>
                <span style='font-size: 2.5rem; font-weight: 900; letter-spacing: 12px;
                              color: #c0392b; font-family: monospace;'>{$otp}</span>
            </div>
            <p style='color: #b2bec3; font-size: 0.85rem; text-align: center;'>
                This code expires in <strong>5 minutes</strong>. Do not share it with anyone.
            </p>
        </div>";

        try {
            $response = Http::withHeaders([
                'api-key'      => $apiToken,
                'Content-Type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender'      => ['email' => $fromAddress, 'name' => $fromName],
                'to'          => [['email' => $user->email, 'name' => $user->first_name]],
                'subject'     => 'Your UM Dining Center Login Code',
                'htmlContent' => $htmlBody,
                'textContent' => "Your verification code is: {$otp}\nThis code expires in 5 minutes.",
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Brevo API error: ' . $response->status() . ' — ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Brevo HTTP failed: ' . $e->getMessage());
            return false;
        }
    }
}
