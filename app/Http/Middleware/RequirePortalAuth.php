<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the authenticated portal routes (/customer/* and /merchant/*).
 *
 * When the session has no token for the matching actor — fresh visit,
 * post-logout, expired session, or a user trying the wrong portal — we
 * short-circuit to the login page with `?step=sessionExpired` instead of
 * letting Livewire components mount and call the API with a null bearer
 * (which would surface as an AuthException or, worse, a crash on a Livewire
 * poll/update request mid-page).
 *
 * Usage: `->middleware('portal.auth:customer')` / `:merchant`.
 */
class RequirePortalAuth
{
    public function handle(Request $request, Closure $next, string $actor): Response
    {
        $sessionActor = $request->session()->get('actor_type');
        $hasToken     = (bool) app('komopay.tokens.' . $actor)->accessToken();

        if (!$hasToken || $sessionActor !== $actor) {
            // Clean up any half-state from a previous/expired session before
            // kicking out, so the login page starts from a clean slate.
            app('komopay.tokens.' . $actor)->clear();
            $request->session()->forget(['actor_type', 'auth_user']);

            return redirect(route($actor . '.login') . '?step=sessionExpired');
        }

        return $next($request);
    }
}
