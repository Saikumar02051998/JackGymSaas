<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\Response;

class SetGymContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $gym = $request->user()->gym;

            if ($gym) {
                if (is_saas() && ! $gym->isSubscriptionActive()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->guest(route('login'))
                        ->withErrors(['email' => 'Your gym\'s subscription has expired. Contact your SaaS administrator to renew it.']);
                }

                $timezone = $gym->setting('timezone', $gym->timezone);
                if ($timezone) {
                    config(['app.timezone' => $timezone]);
                    Date::setTestNow(null);
                    date_default_timezone_set($timezone);
                    App::make('Illuminate\Contracts\Config\Repository')->set('app.timezone', $timezone);
                }
            }
        }

        return $next($request);
    }
}
