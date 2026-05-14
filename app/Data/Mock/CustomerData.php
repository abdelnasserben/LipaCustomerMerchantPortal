<?php

namespace App\Data\Mock;

class CustomerData
{
    public static function profile(): array
    {
        return [
            'id' => 'cus_8e2c9f7e-3a41-4bd8-9f1a-2c91d4ec5a90',
            'externalRef' => 'C-12849',
            'fullName' => 'Saïd Ahamadi',
            'dateOfBirth' => '1988-03-14',
            'phoneCountryCode' => '269',
            'phoneNumber' => '32098798',
            'nationalIdType' => 'NATIONAL_ID',
            'kycLevel' => 'KYC_VERIFIED',
            'kycVerifiedAt' => '2025-01-15T10:00:00Z',
            'status' => 'ACTIVE',
            'walletId' => 'wal_5d2a91',
            'limitProfileId' => 'lp_tier2',
            'addressIsland' => 'Ngazidja',
            'addressCity' => 'Moroni',
            'addressDistrict' => 'Badjanani',
            'createdAt' => '2024-06-01T08:00:00Z',
        ];
    }

    public static function balance(): array
    {
        return [
            'walletId' => 'wal_5d2a91',
            'availableBalance' => 184250,
            'frozenBalance' => 12500,
            'currency' => 'KMF',
            'walletStatus' => 'ACTIVE',
            'updatedAt' => '2026-05-14T11:55:00Z',
        ];
    }

    public static function limits(): array
    {
        return [
            'limitProfileId' => 'lp_tier2',
            'profileName' => 'KYC Verified · Tier 2',
            'maxTransactionAmount' => 500000,
            'minTransactionAmount' => 100,
            'maxDailyAmount' => 1500000,
            'maxWeeklyAmount' => 5000000,
            'maxMonthlyAmount' => 8000000,
            'maxDailyTransactionCount' => 30,
            'maxMonthlyTransactionCount' => 300,
            'requiredKycLevel' => 'KYC_VERIFIED',
        ];
    }

    public static function transactions(): array
    {
        return [
            [
                'id' => 'tx_b91f2d4e3a',
                'type' => 'P2P_TRANSFER',
                'status' => 'COMPLETED',
                'sourceWalletId' => 'wal_5d2a91',
                'destinationWalletId' => 'wal_faiza_01',
                'initiatorType' => 'CUSTOMER',
                'requestedAmount' => 25000,
                'feeAmount' => 250,
                'netAmountToDestination' => 24750,
                'currency' => 'KMF',
                'createdAt' => '2026-05-14T10:42:00Z',
                'completedAt' => '2026-05-14T10:42:01Z',
                'counterparty' => 'Faïza Mohamed',
                'counterpartyPhone' => '+269 33 21 04 56',
                'direction' => 'out',
            ],
            [
                'id' => 'tx_3c8810eaab',
                'type' => 'CARD_SALE',
                'status' => 'COMPLETED',
                'sourceWalletId' => 'wal_5d2a91',
                'destinationWalletId' => 'wal_mer_e1',
                'initiatorType' => 'MERCHANT_OPERATOR',
                'cardId' => 'card_5f1e22',
                'terminalId' => 't_1',
                'requestedAmount' => 8400,
                'feeAmount' => 0,
                'netAmountToDestination' => 8400,
                'currency' => 'KMF',
                'createdAt' => '2026-05-14T09:12:00Z',
                'completedAt' => '2026-05-14T09:12:02Z',
                'counterparty' => 'Boutique Karthala',
                'direction' => 'out',
            ],
            [
                'id' => 'tx_a17029fc01',
                'type' => 'CASH_IN',
                'status' => 'COMPLETED',
                'sourceWalletId' => 'wal_agent_204',
                'destinationWalletId' => 'wal_5d2a91',
                'initiatorType' => 'AGENT',
                'requestedAmount' => 60000,
                'feeAmount' => 0,
                'netAmountToDestination' => 60000,
                'currency' => 'KMF',
                'createdAt' => '2026-05-13T17:55:00Z',
                'completedAt' => '2026-05-13T17:55:03Z',
                'counterparty' => 'Agent · Anjouan Centre',
                'direction' => 'in',
            ],
            [
                'id' => 'tx_8d24fa12e7',
                'type' => 'P2P_TRANSFER',
                'status' => 'COMPLETED',
                'sourceWalletId' => 'wal_mariam_01',
                'destinationWalletId' => 'wal_5d2a91',
                'initiatorType' => 'CUSTOMER',
                'requestedAmount' => 12000,
                'feeAmount' => 0,
                'netAmountToDestination' => 12000,
                'currency' => 'KMF',
                'createdAt' => '2026-05-13T13:08:00Z',
                'completedAt' => '2026-05-13T13:08:01Z',
                'counterparty' => 'Mariam Soilihi',
                'direction' => 'in',
            ],
            [
                'id' => 'tx_551c2f8b40',
                'type' => 'PAYMENT',
                'status' => 'COMPLETED',
                'sourceWalletId' => 'wal_5d2a91',
                'destinationWalletId' => 'wal_pharmacie',
                'initiatorType' => 'MERCHANT_OPERATOR',
                'requestedAmount' => 5300,
                'feeAmount' => 0,
                'netAmountToDestination' => 5300,
                'currency' => 'KMF',
                'createdAt' => '2026-05-12T18:22:00Z',
                'completedAt' => '2026-05-12T18:22:01Z',
                'counterparty' => 'Pharmacie El Maarouf',
                'direction' => 'out',
            ],
            [
                'id' => 'tx_77ad03ed91',
                'type' => 'P2P_TRANSFER',
                'status' => 'DECLINED',
                'sourceWalletId' => 'wal_5d2a91',
                'destinationWalletId' => null,
                'initiatorType' => 'CUSTOMER',
                'requestedAmount' => 90000,
                'feeAmount' => 0,
                'netAmountToDestination' => 0,
                'currency' => 'KMF',
                'declineReason' => 'INSUFFICIENT_BALANCE',
                'createdAt' => '2026-05-12T11:14:00Z',
                'declinedAt' => '2026-05-12T11:14:00Z',
                'counterparty' => '+269 32 18 04 22',
                'direction' => 'out',
            ],
            [
                'id' => 'tx_bd55207cee',
                'type' => 'CASH_OUT',
                'status' => 'COMPLETED',
                'sourceWalletId' => 'wal_5d2a91',
                'destinationWalletId' => 'wal_agent_118',
                'initiatorType' => 'AGENT',
                'requestedAmount' => 30000,
                'feeAmount' => 300,
                'netAmountToDestination' => 29700,
                'currency' => 'KMF',
                'createdAt' => '2026-05-11T14:30:00Z',
                'completedAt' => '2026-05-11T14:30:02Z',
                'counterparty' => 'Agent · Moroni Volo Volo',
                'direction' => 'out',
            ],
            [
                'id' => 'tx_cc99001122',
                'type' => 'CARD_SALE',
                'status' => 'COMPLETED',
                'sourceWalletId' => 'wal_5d2a91',
                'destinationWalletId' => 'wal_shop',
                'initiatorType' => 'MERCHANT_OPERATOR',
                'cardId' => 'card_5f1e22',
                'requestedAmount' => 3200,
                'feeAmount' => 0,
                'netAmountToDestination' => 3200,
                'currency' => 'KMF',
                'createdAt' => '2026-05-10T15:10:00Z',
                'completedAt' => '2026-05-10T15:10:01Z',
                'counterparty' => 'Librairie Al Kamar',
                'direction' => 'out',
            ],
        ];
    }

    public static function activity(int $limit = 10): array
    {
        return array_slice(self::transactions(), 0, $limit);
    }

    public static function statements(): array
    {
        $txs = self::transactions();
        $balance = 184250;
        $entries = [];
        foreach ($txs as $i => $tx) {
            $isCredit = $tx['direction'] === 'in';
            $amount = $tx['requestedAmount'];
            $entries[] = [
                'id' => 'stmt_' . $i,
                'transactionId' => $tx['id'],
                'entryType' => $isCredit ? 'CREDIT' : 'DEBIT',
                'amount' => $amount,
                'runningBalance' => $balance,
                'currency' => 'KMF',
                'description' => self::descriptionForType($tx['type'], $tx['counterparty'] ?? ''),
                'postedAt' => $tx['completedAt'] ?? $tx['createdAt'],
                'globalSequence' => 1000 - $i,
            ];
            $balance += $isCredit ? -$amount : $amount;
        }
        return $entries;
    }

    private static function descriptionForType(string $type, string $counterparty): string
    {
        return match ($type) {
            'P2P_TRANSFER' => "P2P · {$counterparty}",
            'CASH_IN'      => "Cash-in · {$counterparty}",
            'CASH_OUT'     => "Cash-out · {$counterparty}",
            'CARD_SALE'    => "Card sale · {$counterparty}",
            'PAYMENT'      => "Payment · {$counterparty}",
            default        => $counterparty ?: $type,
        };
    }

    public static function beneficiaries(): array
    {
        return [
            ['customerId' => 'cus_faiza', 'externalRef' => 'C-10021', 'fullName' => 'Faïza Mohamed', 'phoneCountryCode' => '269', 'phoneNumber' => '33210456'],
            ['customerId' => 'cus_mariam', 'externalRef' => 'C-10042', 'fullName' => 'Mariam Soilihi', 'phoneCountryCode' => '269', 'phoneNumber' => '32881703'],
            ['customerId' => 'cus_yusuf', 'externalRef' => 'C-10088', 'fullName' => 'Yusuf Bacar', 'phoneCountryCode' => '269', 'phoneNumber' => '34021967'],
            ['customerId' => 'cus_anissa', 'externalRef' => 'C-10103', 'fullName' => 'Anissa Saïd', 'phoneCountryCode' => '269', 'phoneNumber' => '33417622'],
        ];
    }

    public static function cards(): array
    {
        return [
            [
                'id' => 'card_5f1e22',
                'cardType' => 'STANDARD',
                'status' => 'ACTIVE',
                'pinEnabled' => true,
                'activatedAt' => '2024-12-05T08:00:00Z',
                'expiresAt' => '2028-11-30',
                'lastUsedAt' => '2026-05-14T09:12:00Z',
                'issuedAt' => '2024-12-04T00:00:00Z',
                'last4' => '4271',
            ],
            [
                'id' => 'card_91b302',
                'cardType' => 'STANDARD',
                'status' => 'BLOCKED',
                'pinEnabled' => true,
                'activatedAt' => '2023-04-13T08:00:00Z',
                'expiresAt' => '2027-04-30',
                'lastUsedAt' => '2026-03-02T11:08:00Z',
                'issuedAt' => '2023-04-12T00:00:00Z',
                'last4' => '0863',
            ],
        ];
    }
}
