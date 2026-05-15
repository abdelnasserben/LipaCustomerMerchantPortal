<?php

namespace App\Komopay\Services\Customer;

use App\Komopay\Contracts\CursorPage;
use App\Komopay\Contracts\CustomerApi;
use App\Komopay\Exceptions\AuthException;
use App\Komopay\Exceptions\BusinessException;
use App\Komopay\Mock\Fixtures\CustomerFixtures;
use Illuminate\Support\Facades\Cache;

/**
 * In-memory implementation of CustomerApi.
 *
 * Returns spec-conformant shapes (section 7) — no HTTP, no persistence.
 * State changes (e.g. report-lost) live for one request only.
 */
final class MockCustomerApi implements CustomerApi
{
    public function profile(): array
    {
        return CustomerFixtures::profile();
    }

    public function balance(): array
    {
        return CustomerFixtures::balance();
    }

    public function limits(): array
    {
        return CustomerFixtures::limits();
    }

    public function activity(int $limit = 10): array
    {
        return array_slice(CustomerFixtures::transactions(), 0, max(0, $limit));
    }

    public function transactions(array $filters = [], ?string $cursor = null, int $limit = 20): CursorPage
    {
        $rows = CustomerFixtures::transactions();

        if (!empty($filters['status'])) {
            $rows = array_values(array_filter($rows, fn($r) => $r['status'] === $filters['status']));
        }
        if (!empty($filters['type'])) {
            $rows = array_values(array_filter($rows, fn($r) => $r['type'] === $filters['type']));
        }

        return new CursorPage(array_slice($rows, 0, $limit), null, false, $limit);
    }

    public function transaction(string $id): array
    {
        foreach (CustomerFixtures::transactions() as $t) {
            if ($t['id'] === $id) {
                return $t;
            }
        }
        throw new BusinessException('Transaction not found', 'TRANSACTION_NOT_FOUND', 404);
    }

    public function statements(?string $from = null, ?string $to = null, ?string $cursor = null, int $limit = 20): CursorPage
    {
        return new CursorPage(
            array_slice(CustomerFixtures::statements(), 0, $limit),
            null,
            false,
            $limit,
        );
    }

    public function beneficiaries(int $limit = 20): array
    {
        return array_slice(CustomerFixtures::beneficiaries(), 0, $limit);
    }

    public function cards(): array
    {
        return CustomerFixtures::cards();
    }

    public function card(string $id): array
    {
        foreach (CustomerFixtures::cards() as $c) {
            if ($c['id'] === $id) {
                return $c;
            }
        }
        throw new BusinessException('Card not found', 'CARD_NOT_FOUND', 404);
    }

    public function reportCardLost(string $id): array
    {
        $card = $this->card($id);
        $card['status'] = 'LOST';
        return $card;
    }

    public function reportCardStolen(string $id): array
    {
        $card = $this->card($id);
        $card['status'] = 'STOLEN';
        return $card;
    }

    /**
     * Mirrors spec 2.4 / 11.3 control gate semantics. Priority: Approval > PIN
     * > Confirmation. PIN tier is cleared by sending the raw PIN; server
     * verifies. 3 wrong PIN attempts → AUTH_PIN_LOCKED (15 min).
     *
     *   amount >= 10 000 KMF without matching `pin`            → PENDING_PIN
     *   amount >  50 000 KMF without `confirmationAcknowledged` → PENDING_CONFIRMATION
     *   else                                                    → EXECUTED
     */
    public function p2pTransfer(array $request, string $idempotencyKey): array
    {
        $amount = (int) ($request['amount'] ?? 0);
        if ($amount < 100) {
            throw new BusinessException('Amount below minimum', 'LIMIT_EXCEEDED', 422);
        }

        $confirmAck = (bool) ($request['confirmationAcknowledged'] ?? false);
        $pin        = isset($request['pin']) ? (string) $request['pin'] : '';

        if ($amount >= 10000 && $pin === '') {
            return [
                'outcome'                => 'PENDING_PIN',
                'matchedThresholdAmount' => 10000,
                'transactionId'          => null,
                'status'                 => null,
                'requestedAmount'        => $amount,
                'feeAmount'              => null,
                'netAmountToDestination' => null,
                'currency'               => 'KMF',
                'completedAt'            => null,
                'replayed'               => null,
            ];
        }
        if ($amount >= 10000) {
            $this->verifyMockPin($pin);
        }

        if ($amount > 50000 && !$confirmAck) {
            return [
                'outcome'                => 'PENDING_CONFIRMATION',
                'matchedThresholdAmount' => 50000,
                'transactionId'          => null,
                'status'                 => null,
                'requestedAmount'        => $amount,
                'feeAmount'              => null,
                'netAmountToDestination' => null,
                'currency'               => 'KMF',
                'completedAt'            => null,
                'replayed'               => null,
            ];
        }

        $fee = (int) round($amount * 0.01);
        return [
            'outcome'                => 'EXECUTED',
            'matchedThresholdAmount' => null,
            'transactionId'          => 'tx_' . substr(md5($idempotencyKey), 0, 10),
            'status'                 => 'COMPLETED',
            'requestedAmount'        => $amount,
            'feeAmount'              => $fee,
            'netAmountToDestination' => $amount - $fee,
            'currency'               => 'KMF',
            'completedAt'            => now()->toIso8601String(),
            'replayed'               => false,
        ];
    }

    public function billPayment(array $request, string $idempotencyKey): array
    {
        // Spec 1.1 / 5.2: bill-pay is feature-flagged OFF by default → 501.
        throw new \App\Komopay\Exceptions\NotImplementedException(
            'Bill payment is disabled', 'BILL_PAYMENT_DISABLED', 501,
        );
    }

    /**
     * Wrong PIN raises AUTH_PIN_INVALID and counts toward the 3-strike /
     * 15-min lock (spec 3.2 / 12.3).
     */
    private function verifyMockPin(string $pin): void
    {
        $bucket = 'mock:auth-pin:failures:' . md5((string) (auth()->id() ?? session()->getId()));
        $fails  = (int) Cache::get($bucket, 0);
        if ($fails >= 3) {
            throw new BusinessException('PIN locked. Try again in 15 minutes.', 'AUTH_PIN_LOCKED', 422);
        }

        // Mock-only fixed PIN: anything other than "1234" is wrong.
        if ($pin !== '1234') {
            Cache::put($bucket, $fails + 1, now()->addMinutes(15));
            throw new AuthException('Invalid PIN', 'AUTH_PIN_INVALID', 401);
        }

        Cache::forget($bucket);
    }
}
