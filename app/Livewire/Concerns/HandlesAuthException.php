<?php

namespace App\Livewire\Concerns;

use App\Komopay\Exceptions\AuthException;
use Livewire\Component;

/**
 * Catches AuthException thrown by KomoPay API calls inside Livewire
 * components and redirects to the matching login page with the
 * `sessionExpired` step (spec 12.1).
 *
 * Use on customer components with $actor = 'customer', merchant with 'merchant'.
 *
 * @mixin Component
 */
trait HandlesAuthException
{
    public function exception(\Throwable $e, callable $stopPropagation): void
    {
        if (!$e instanceof AuthException) {
            return;
        }

        $actor = property_exists($this, 'actor') ? $this->actor : 'customer';
        app('komopay.tokens.' . $actor)->clear();
        session()->forget(['actor_type', 'auth_user']);

        $route = $actor === 'merchant' ? 'merchant.login' : 'customer.login';
        $this->redirect(route($route) . '?step=sessionExpired', navigate: true);
        $stopPropagation();
    }
}
