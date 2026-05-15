<?php

use App\Komopay\Exceptions\AuthException;
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
        $middleware->alias([
            'guest.portal' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthException $e, Request $request) {
            $actor = session('actor_type') === 'merchant' ? 'merchant' : 'customer';
            app('komopay.tokens.' . $actor)->clear();
            session()->forget(['actor_type', 'auth_user']);
            return redirect(route($actor . '.login') . '?step=sessionExpired');
        });
    })->create();
