<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorOtpMail;

class TwoFactorController extends Controller
{
    /**
     * Show the OTP verification form.
     */
    public function show(Request $request)
    {
        // If no pending user in session, send back to login
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

        // Redirect to role-specific landing
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
        $this->sendOtp($user);

        return back()->with('resent', 'A new OTP has been sent to your email.');
    }

    /**
     * Generate and cache an OTP, then email it to the user.
     */
    public static function sendOtp(\App\Models\User $user): void
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache for 5 minutes
        Cache::put('2fa_otp_' . $user->id, $otp, now()->addMinutes(5));

        // Send email
        Mail::to($user->email)->send(new TwoFactorOtpMail($otp, $user->first_name));
    }
}
