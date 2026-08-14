<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MailService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $data['email'])->first();

        $otp = app(OtpService::class)->issue($user);

        app(MailService::class)->sendOtp($user, $otp, 'Password reset');

        $request->session()->put('password_reset_email', $user->email);

        return redirect()->route('password.otp')
            ->with('status', 'A password reset code has been sent to your email.');
    }

    public function showOtp(Request $request)
    {
        $email = $request->session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp', ['email' => $email]);
    }

    public function verifyOtp(Request $request)
    {
        $email = $request->session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $data = $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('password.request');
        }

        if (! app(OtpService::class)->verify($user, $data['otp'])) {
            return back()->withErrors(['otp' => 'The code is invalid or has expired.'])->onlyInput('otp');
        }

        return redirect()->route('password.reset');
    }

    public function resendOtp(Request $request)
    {
        $email = $request->session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('password.request');
        }

        $otp = app(OtpService::class)->issue($user);

        app(MailService::class)->sendOtp($user, $otp, 'Password reset');

        return back()->with('status', 'A new reset code has been sent to your email.');
    }

    public function showReset()
    {
        if (! session()->has('password_reset_email')) {
            return redirect()->route('password.request');
        }

        $email = session()->get('password_reset_email');

        return view('auth.reset-password', ['email' => $email]);
    }

    public function resetPassword(Request $request)
    {
        $email = session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('password.request');
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->setRememberToken(Str::random(60));

        $user->save();

        app(OtpService::class)->clear($user);

        $request->session()->forget('password_reset_email');

        audit_log('auth.password_reset', 'auth', $user->id, 'Password reset for ' . $user->email);

        return redirect()->route('login')->with('success', 'Password reset successfully. Please login.');
    }
}
