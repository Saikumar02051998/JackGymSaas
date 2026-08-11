<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->intended(Auth::user()->homeRoute());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login:' . Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        $user = User::where('email', $request->input('email'))
            ->orWhere('phone', $request->input('email'))
            ->first();

        $field = 'email';
        if ($user && $user->phone === $request->input('email')) {
            $field = 'phone';
        }

        $attempt = Auth::attempt([$field => $request->input('email'), 'password' => $request->input('password')], $request->boolean('remember'));

        if (! $attempt) {
            RateLimiter::hit($key, 60);

            audit_log('auth.failed', 'auth', $user?->id, 'Failed login attempt');

            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($key);

        $user = Auth::user();

        if ($user->status === 'inactive' || $user->status === 'suspended') {
            Auth::logout();

            return back()->withErrors([
                'email' => 'This account has been deactivated. Contact the gym administrator.',
            ]);
        }

        $request->session()->regenerate();

        audit_log('auth.login', 'auth', $user->id, "User logged in ({$user->name})");

        return redirect()->intended($user->homeRoute());
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            audit_log('auth.logout', 'auth', $user->id, "User logged out ({$user->name})");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
