<?php

namespace App\Komopay\Services\Customer;

use App\Komopay\Contracts\CursorPage;
use App\Komopay\Contracts\CustomerApi;
use App\Komopay\Contracts\CustomerAuthApi;
use App\Komopay\Exceptions\AuthException;
use App\Komopay\Http\KomopayHttpClient;
use App\Komopay\Support\TokenStore;

/**
 * Real HTTP implementation of CustomerApi against /api/v1/me/* (spec 5.2).
 *
 * Wraps every authenticated call with a one-shot refresh-and-retry on 401
 * (spec 3.2 / 12.1). If the refresh itself fails, the AuthException bubbles
 * up so the Livewire layer can redirect to login.
 */
final class HttpCustomerApi implements CustomerApi
{
    public function __construct(
        private readonly KomopayHttpClient $http,
        private readonly TokenStore $tokens,
        private readonly CustomerAuthApi $auth,
    ) {}

    /** @template T @param callable(array $opts):T $call @return T */
    private function withAuth(callable $call, array $extraOpts = [])
    {
        $opts = ['bearer' => $this->tokens->accessToken(), ...$extraOpts];
        try {
            return $call($opts);
        } catch (AuthException) {
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

    public function profile(): array
    {
        return $this->withAuth(fn($o) => (array) $this->http->get('me', [], $o)->data);
    }

    public function balance(): array
    {
        return $this->withAuth(fn($o) => (array) $this->http->get('me/balance', [], $o)->data);
    }

    public function limits(): array
    {
        return $this->withAuth(fn($o) => (array) $this->http->get('me/limits', [], $o)->data);
    }

    public function activity(int $limit = 10): array
    {
        return $this->withAuth(fn($o) => (array) $this->http->get('me/activity', ['limit' => $limit], $o)->data);
    }

    public function transactions(array $filters = [], ?string $cursor = null, int $limit = 20): CursorPage
    {
        $query = array_filter([
            'cursor' => $cursor,
            'limit'  => $limit,
            'status' => $filters['status'] ?? null,
            'type'   => $filters['type'] ?? null,
            'from'   => $filters['from'] ?? null,
            'to'     => $filters['to'] ?? null,
        ], fn($v) => $v !== null && $v !== '');

        return $this->withAuth(function ($o) use ($query, $limit) {
            $r = $this->http->get('me/transactions', $query, $o);
            return new CursorPage(
                (array) $r->data,
                $r->nextCursor(),
                $r->hasMore(),
                (int) ($r->pagination['limit'] ?? $limit),
            );
        });
    }

    public function transaction(string $id): array
    {
        return $this->withAuth(fn($o) => (array) $this->http->get("me/transactions/{$id}", [], $o)->data);
    }

    public function statements(?string $from = null, ?string $to = null, ?string $cursor = null, int $limit = 20): CursorPage
    {
        $query = array_filter([
            'cursor' => $cursor,
            'limit'  => $limit,
            'from'   => $from,
            'to'     => $to,
        ], fn($v) => $v !== null && $v !== '');

        return $this->withAuth(function ($o) use ($query, $limit) {
            $r = $this->http->get('me/statements', $query, $o);
            return new CursorPage(
                (array) $r->data,
                $r->nextCursor(),
                $r->hasMore(),
                (int) ($r->pagination['limit'] ?? $limit),
            );
        });
    }

    public function beneficiaries(int $limit = 20): array
    {
        return $this->withAuth(fn($o) => (array) $this->http->get('me/beneficiaries', ['limit' => $limit], $o)->data);
    }

    public function cards(): array
    {
        return $this->withAuth(fn($o) => (array) $this->http->get('me/cards', [], $o)->data);
    }

    public function card(string $id): array
    {
        return $this->withAuth(fn($o) => (array) $this->http->get("me/cards/{$id}", [], $o)->data);
    }

    public function reportCardLost(string $id): array
    {
        return $this->withAuth(fn($o) => (array) $this->http->post("me/cards/{$id}/report-lost", [], $o)->data);
    }

    public function reportCardStolen(string $id): array
    {
        return $this->withAuth(fn($o) => (array) $this->http->post("me/cards/{$id}/report-stolen", [], $o)->data);
    }

    public function p2pTransfer(array $request, string $idempotencyKey): array
    {
        return $this->withAuth(
            fn($o) => (array) $this->http->post('me/p2p', $request, $o)->data,
            ['idempotencyKey' => $idempotencyKey],
        );
    }

    public function billPayment(array $request, string $idempotencyKey): array
    {
        return $this->withAuth(
            fn($o) => (array) $this->http->post('me/bill-payments', $request, $o)->data,
            ['idempotencyKey' => $idempotencyKey],
        );
    }
}
