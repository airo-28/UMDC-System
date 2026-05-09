<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TwoFactorController;

class AuthorizationController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check credentials WITHOUT logging in yet
        if (Auth::validate($credentials)) {
            $user = \App\Models\User::where('email', $credentials['email'])->first();

            // Check if account is active
            if (!$user->is_active) {
                return back()->withErrors([
                    'email' => 'This account has been deactivated. Contact your administrator.',
                ])->withInput();
            }

            // Store pending user in session and send OTP
            $request->session()->put('2fa_user_id', $user->id);
            TwoFactorController::sendOtp($user);

            return redirect()->route('2fa.show');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}