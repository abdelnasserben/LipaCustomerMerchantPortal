<?php

namespace App\Komopay\Services\Notification;

use App\Komopay\Contracts\CustomerAuthApi;
use App\Komopay\Contracts\NotificationApi;
use App\Komopay\Exceptions\AuthException;
use App\Komopay\Http\KomopayHttpClient;
use App\Komopay\Support\TokenStore;

/**
 * Real HTTP implementation of NotificationApi against /api/v1/notifications/*
 * (spec 5.5). The same endpoints accept Customer, Merchant and Agent JWTs —
 * scope is enforced server-side from the principal.
 *
 * For the Customer flavor, $auth is provided and a 401 triggers the one-shot
 * refresh-and-retry pattern from HttpCustomerApi. For Merchant (no refresh
 * channel today) $auth is null and a 401 bubbles straight up to
 * HandlesAuthException, which kicks the user to re-login.
 */
final class HttpNotificationApi implements NotificationApi
{
    public function __construct(
        private readonly KomopayHttpClient $http,
        private readonly TokenStore $tokens,
        private readonly ?CustomerAuthApi $auth = null,
    ) {}

    /** @template T @param callable(array $opts):T $call @return T */
    private function withAuth(callable $call)
    {
        $opts = ['bearer' => $this->tokens->accessToken()];
        try {
            return $call($opts);
        } catch (AuthException $e) {
            if ($this->auth === null) {
                throw $e;
            }
            $refresh = $this->tokens->refreshToken();
            if (!$refresh) {
                throw new AuthException('Session expired', 'TOKEN_EXPIRED', 401);
            }
            $new = $this->auth->refresh($refresh);
            $this->tokens->put($new);
            $opts['bearer'] = $new['accessToken'] ?? null;
            return $call($opts);
        }
    }

    public function list(int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        return $this->withAuth(
            fn ($o) => (array) $this->http->get('notifications', ['limit' => $limit], $o)->data,
        );
    }

    public function unreadCount(): int
    {
        return $this->withAuth(function ($o) {
            $data = (array) $this->http->get('notifications/unread', [], $o)->data;
            return (int) ($data['unread'] ?? 0);
        });
    }

    public function markRead(string $id): void
    {
        $this->withAuth(fn ($o) => $this->http->post("notifications/{$id}/read", [], $o));
    }

    public function markAllRead(): int
    {
        return $this->withAuth(function ($o) {
            $data = (array) $this->http->post('notifications/read-all', [], $o)->data;
            return (int) ($data['updated'] ?? 0);
        });
    }
}
