<?php

namespace App\Komopay\Services\Merchant;

use App\Komopay\Contracts\CursorPage;
use App\Komopay\Contracts\MerchantApi;
use App\Komopay\Exceptions\BusinessException;
use App\Komopay\Mock\Fixtures\MerchantFixtures;

/**
 * In-memory implementation of MerchantApi.
 *
 * Operator state is held on a static array so that the same browser
 * request keeps a coherent view across Livewire renders. (Lifetime is
 * a single PHP process; no cross-request persistence.)
 */
final class MockMerchantApi implements MerchantApi
{
    private static ?array $operators = null;

    private function loadOperators(): array
    {
        if (self::$operators === null) {
            self::$operators = MerchantFixtures::operators();
        }
        return self::$operators;
    }

    public function profile(): array
    {
        return MerchantFixtures::profile();
    }

    public function balance(): array
    {
        return MerchantFixtures::balance();
    }

    public function transactions(array $filters = [], ?string $cursor = null, int $limit = 20): CursorPage
    {
        $rows = MerchantFixtures::transactions();
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
        foreach (MerchantFixtures::transactions() as $t) {
            if ($t['id'] === $id) {
                return $t;
            }
        }
        throw new BusinessException('Transaction not found', 'TRANSACTION_NOT_FOUND', 404);
    }

    public function statements(?string $from = null, ?string $to = null, ?string $cursor = null, int $limit = 20): CursorPage
    {
        return new CursorPage(
            array_slice(MerchantFixtures::statements(), 0, $limit),
            null,
            false,
            $limit,
        );
    }

    public function terminals(): array
    {
        return MerchantFixtures::terminals();
    }

    public function terminal(string $id): array
    {
        foreach (MerchantFixtures::terminals() as $t) {
            if ($t['id'] === $id) {
                return $t;
            }
        }
        throw new BusinessException('Terminal not found', 'TERMINAL_NOT_FOUND', 404);
    }

    public function m2mTransfer(array $request, string $idempotencyKey): array
    {
        $amount = (int) ($request['amount'] ?? 0);
        if ($amount < 1) {
            throw new BusinessException('Amount required', 'VALIDATION_FIELD_REQUIRED', 400);
        }
        $fee = (int) round($amount * 0.01);
        return [
            'transactionId'          => 'tx_m2m_' . substr(md5($idempotencyKey), 0, 8),
            'status'                 => 'COMPLETED',
            'requestedAmount'        => $amount,
            'feeAmount'              => $fee,
            'netAmountToDestination' => $amount - $fee,
            'completedAt'            => now()->toIso8601String(),
            'replayed'               => false,
        ];
    }

    public function createOperator(array $request): array
    {
        $phone = $request['phoneNumber'] ?? '';
        foreach ($this->loadOperators() as $op) {
            if ($op['phoneNumber'] === $phone) {
                throw new BusinessException(
                    'Phone already in use', 'PHONE_ALREADY_IN_USE', 422,
                );
            }
        }
        $new = [
            'id'               => 'op_new_' . uniqid(),
            'merchantId'       => MerchantFixtures::MERCHANT_ID,
            'fullName'         => $request['fullName'] ?? '',
            'phoneCountryCode' => $request['phoneCountryCode'] ?? '269',
            'phoneNumber'      => $phone,
            'status'           => 'ACTIVE',
            'createdAt'        => now()->toIso8601String(),
            'lastLoginAt'      => null,
        ];
        self::$operators[] = $new;
        return $new;
    }

    public function operators(?string $status = null, ?string $cursor = null, int $limit = 20): CursorPage
    {
        $rows = $this->loadOperators();
        if ($status) {
            $rows = array_values(array_filter($rows, fn($o) => $o['status'] === $status));
        }
        return new CursorPage(array_slice($rows, 0, $limit), null, false, $limit);
    }

    public function operator(string $id): array
    {
        foreach ($this->loadOperators() as $op) {
            if ($op['id'] === $id) {
                return $op;
            }
        }
        throw new BusinessException('Operator not found', 'OPERATOR_NOT_FOUND', 404);
    }

    public function suspendOperator(string $id): array
    {
        return $this->transitionOperator($id, 'SUSPENDED');
    }

    public function reactivateOperator(string $id): array
    {
        return $this->transitionOperator($id, 'ACTIVE');
    }

    public function revokeOperator(string $id): array
    {
        return $this->transitionOperator($id, 'REVOKED');
    }

    private function transitionOperator(string $id, string $newStatus): array
    {
        $this->loadOperators();
        foreach (self::$operators as &$op) {
            if ($op['id'] === $id) {
                $op['status'] = $newStatus;
                return $op;
            }
        }
        throw new BusinessException('Operator not found', 'OPERATOR_NOT_FOUND', 404);
    }
}
