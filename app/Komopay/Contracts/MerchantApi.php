<?php

namespace App\Komopay\Contracts;

/**
 * Merchant portal surface — spec section 5.4.
 * Returned shapes match spec section 7.5–7.7.
 */
interface MerchantApi
{
    public function profile(): array;

    public function balance(): array;

    public function transactions(array $filters = [], ?string $cursor = null, int $limit = 20): CursorPage;

    public function transaction(string $id): array;

    public function statements(?string $from = null, ?string $to = null, ?string $cursor = null, int $limit = 20): CursorPage;

    /** GET /merchant/terminals — full list (PagedResponse.last; spec 2.2). */
    public function terminals(): array;

    public function terminal(string $id): array;

    /** POST /merchant/m2m — MerchantToMerchantResponse (no 202 control gate, spec 2.4). */
    public function m2mTransfer(array $request, string $idempotencyKey): array;

    /** POST /merchant/operators — OperatorResponse. */
    public function createOperator(array $request): array;

    /** GET /merchant/operators?status — PagedResponse<OperatorResponse>. */
    public function operators(?string $status = null, ?string $cursor = null, int $limit = 20): CursorPage;

    public function operator(string $id): array;

    public function suspendOperator(string $id): array;

    public function reactivateOperator(string $id): array;

    public function revokeOperator(string $id): array;
}
