<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureClient;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureSaasOwner;
use App\Http\Middleware\SetGymContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetGymContext::class,
        ]);

        $middleware->alias([
            'permission' => CheckPermission::class,
            'role' => EnsureRole::class,
            'client' => EnsureClient::class,
            'saas.owner' => EnsureSaasOwner::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function (Request $request) {
            return $request->user()?->homeRoute() ?? route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            return redirect()->guest(route('login'));
        });
    })->create();
