<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $actor = session('actor_type');

        if ($actor === 'merchant' && app('komopay.tokens.merchant')->accessToken()) {
            return redirect()->route('merchant.dashboard');
        }

        if ($actor === 'customer' && app('komopay.tokens.customer')->accessToken()) {
            return redirect()->route('customer.dashboard');
        }

        return $next($request);
    }
}
