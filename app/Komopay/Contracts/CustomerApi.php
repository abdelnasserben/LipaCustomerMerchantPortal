<?php

namespace App\Komopay\Contracts;

/**
 * Customer portal surface — spec section 5.2.
 *
 * Every method returns plain associative arrays whose shape matches
 * the response schemas in spec section 7. Callers MUST NOT add or
 * rename keys — those names are the contract.
 *
 * Errors are signalled by exceptions from App\Komopay\Exceptions\*.
 */
interface CustomerApi
{
    /** GET /me — CustomerProfileResponse (spec 7.2). */
    public function profile(): array;

    /** GET /me/balance — CustomerBalanceResponse (spec 7.2). */
    public function balance(): array;

    /** GET /me/limits — CustomerLimitsResponse (spec 7.2). May raise BusinessException(404). */
    public function limits(): array;

    /** GET /me/activity?limit — list<CustomerTransactionResponse>. */
    public function activity(int $limit = 10): array;

    /** GET /me/transactions — PagedResponse<CustomerTransactionResponse>. */
    public function transactions(array $filters = [], ?string $cursor = null, int $limit = 20): CursorPage;

    /** GET /me/transactions/{id}. */
    public function transaction(string $id): array;

    /** GET /me/statements — PagedResponse<CustomerStatementEntryResponse>. */
    public function statements(?string $from = null, ?string $to = null, ?string $cursor = null, int $limit = 20): CursorPage;

    /** GET /me/beneficiaries — list<BeneficiaryResponse>. */
    public function beneficiaries(int $limit = 20): array;

    /** GET /me/cards — list<CustomerCardResponse>. */
    public function cards(): array;

    /** GET /me/cards/{id}. */
    public function card(string $id): array;

    /** POST /me/cards/{id}/report-lost. */
    public function reportCardLost(string $id): array;

    /** POST /me/cards/{id}/report-stolen. */
    public function reportCardStolen(string $id): array;

    /**
     * POST /me/p2p — P2pTransferResponse (spec 7.4). Outcome may be EXECUTED |
     * PENDING_PIN | PENDING_CONFIRMATION; on continuation, callers MUST resend
     * the SAME idempotency key with `pin=<raw PIN>` (cleared by the server)
     * and/or `confirmationAcknowledged=true`. Priority is Approval > PIN >
     * Confirmation. A wrong PIN raises AuthPinInvalidException; 3 wrong
     * attempts raise AuthPinLockedException (15 min lock).
     */
    public function p2pTransfer(array $request, string $idempotencyKey): array;

    /** POST /me/bill-payments — may raise NotImplementedException(501) when feature flag is off. */
    public function billPayment(array $request, string $idempotencyKey): array;
}
