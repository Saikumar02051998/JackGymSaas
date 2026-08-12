<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
                    $routeName = $request->route()?->getName() ?? '';

                    $allowed = $routeName === 'logout'
                        || str_starts_with($routeName, 'subscription.');

                    if (! $allowed) {
                        if ($request->expectsJson()) {
                            return response()->json([
                                'message' => 'Your gym\'s subscription has expired. Please renew your subscription.',
                            ], 403);
                        }

                        return redirect()->route('subscription.index')
                            ->with('status', 'Your gym\'s subscription has expired. Please renew your plan to restore access.');
                    }
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
