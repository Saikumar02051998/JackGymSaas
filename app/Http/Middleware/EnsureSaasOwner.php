<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSaasOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! is_saas()) {
            abort(404);
        }

        $user = $request->user();

        if (! $user || ! $user->hasRole('saas_owner')) {
            abort(403);
        }

        return $next($request);
    }
}
