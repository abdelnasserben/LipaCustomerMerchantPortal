<?php

namespace App\Komopay\Services\Merchant;

use App\Komopay\Contracts\CursorPage;
use App\Komopay\Contracts\MerchantApi;
use App\Komopay\Http\KomopayHttpClient;
use App\Komopay\Support\TokenStore;

/** Real HTTP implementation of MerchantApi against /api/v1/merchant/* (spec 5.4). */
final class HttpMerchantApi implements MerchantApi
{
    public function __construct(
        private readonly KomopayHttpClient $http,
        private readonly TokenStore $tokens,
    ) {}

    private function bearer(): array
    {
        return ['bearer' => $this->tokens->accessToken()];
    }

    public function profile(): array
    {
        return (array) $this->http->get('merchant/me', [], $this->bearer())->data;
    }

    public function balance(): array
    {
        return (array) $this->http->get('merchant/balance', [], $this->bearer())->data;
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

        $r = $this->http->get('merchant/transactions', $query, $this->bearer());
        return new CursorPage((array) $r->data, $r->nextCursor(), $r->hasMore(), (int) ($r->pagination['limit'] ?? $limit));
    }

    public function transaction(string $id): array
    {
        return (array) $this->http->get("merchant/transactions/{$id}", [], $this->bearer())->data;
    }

    public function statements(?string $from = null, ?string $to = null, ?string $cursor = null, int $limit = 20): CursorPage
    {
        $query = array_filter([
            'cursor' => $cursor,
            'limit'  => $limit,
            'from'   => $from,
            'to'     => $to,
        ], fn($v) => $v !== null && $v !== '');

        $r = $this->http->get('merchant/statements', $query, $this->bearer());
        return new CursorPage((array) $r->data, $r->nextCursor(), $r->hasMore(), (int) ($r->pagination['limit'] ?? $limit));
    }

    public function terminals(): array
    {
        // Spec 2.2 / 5.4: returns PagedResponse.last — read `data` only.
        return (array) $this->http->get('merchant/terminals', [], $this->bearer())->data;
    }

    public function terminal(string $id): array
    {
        return (array) $this->http->get("merchant/terminals/{$id}", [], $this->bearer())->data;
    }

    public function m2mTransfer(array $request, string $idempotencyKey): array
    {
        $r = $this->http->post('merchant/m2m', $request, [
            ...$this->bearer(),
            'idempotencyKey' => $idempotencyKey,
        ]);
        return (array) $r->data;
    }

    public function createOperator(array $request): array
    {
        return (array) $this->http->post('merchant/operators', $request, $this->bearer())->data;
    }

    public function operators(?string $status = null, ?string $cursor = null, int $limit = 20): CursorPage
    {
        $query = array_filter([
            'status' => $status,
            'cursor' => $cursor,
            'limit'  => $limit,
        ], fn($v) => $v !== null && $v !== '');

        $r = $this->http->get('merchant/operators', $query, $this->bearer());
        return new CursorPage((array) $r->data, $r->nextCursor(), $r->hasMore(), (int) ($r->pagination['limit'] ?? $limit));
    }

    public function operator(string $id): array
    {
        return (array) $this->http->get("merchant/operators/{$id}", [], $this->bearer())->data;
    }

    public function suspendOperator(string $id): array
    {
        return (array) $this->http->patch("merchant/operators/{$id}/suspend", [], $this->bearer())->data;
    }

    public function reactivateOperator(string $id): array
    {
        return (array) $this->http->patch("merchant/operators/{$id}/reactivate", [], $this->bearer())->data;
    }

    public function revokeOperator(string $id): array
    {
        return (array) $this->http->patch("merchant/operators/{$id}/revoke", [], $this->bearer())->data;
    }
}
