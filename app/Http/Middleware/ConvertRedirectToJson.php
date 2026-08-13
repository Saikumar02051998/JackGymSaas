<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConvertRedirectToJson
{
    /**
     * When a request wants JSON (fetch/axios with Accept: application/json)
     * and the controller returns a redirect (the typical post-action flow),
     * convert it into a JSON payload carrying the flash message + target URL.
     *
     * This makes every existing POST/PUT/DELETE endpoint AJAX-friendly without
     * modifying the controllers. The client decides whether to follow the
     * redirect or just refresh a section of the page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->wantsJson() || ! $response instanceof RedirectResponse) {
            return $response;
        }

        $session = $request->session();

        if ($session->has('errors')) {
            $errors = $session->get('errors');

            $bag = method_exists($errors, 'getMessages')
                ? $errors->getMessages()
                : ($errors instanceof \Illuminate\Support\MessageBag ? $errors->toArray() : []);

            $flat = [];
            foreach ($bag as $field => $messages) {
                $flat[$field] = (array) $messages;
            }

            $session->forget('errors');

            return response()->json([
                'message' => $flat ? reset($flat)[0] : 'Please correct the highlighted errors.',
                'errors' => $flat,
            ], 422);
        }

        $message = $session->get('success')
            ?: $session->get('error')
            ?: $session->get('status')
            ?: $session->get('warning');

        $json = response()->json([
            'ok' => true,
            'message' => $message,
            'success' => $session->get('success'),
            'error' => $session->get('error'),
            'warning' => $session->get('warning'),
            'status' => $session->get('status'),
            'redirect' => $response->getTargetUrl(),
        ]);

        // Flashes were meant for a full page navigation; since we consumed
        // them here, remove them so a later request doesn't show a stale toast.
        $session->forget(['success', 'error', 'status', 'warning']);

        return $json;
    }
}
