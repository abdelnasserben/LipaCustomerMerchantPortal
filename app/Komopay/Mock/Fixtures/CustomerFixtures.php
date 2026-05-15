<?php

namespace App\Komopay\Mock\Fixtures;

/**
 * Static mock data for the Customer surface.
 *
 * Every shape here MUST match Customer_Merchant_Frontend_Specification.md
 * sections 7.2–7.4. No keys outside the spec — UI-only derived fields
 * (counterparty name, direction, ...) are computed by Presenters.
 *
 * The single exception is the small CounterpartyDirectory below: it is
 * a mock-only address book keyed by walletId, used by the presenter to
 * render human names. In real-API mode the directory is empty and the
 * presenter falls back to the raw wallet id.
 */
final class CustomerFixtures
{
    public const WALLET_ID = 'wal_5d2a91';

    public static function profile(): array
    {
        return [
            'id'               => 'cus_8e2c9f7e-3a41-4bd8-9f1a-2c91d4ec5a90',
            'externalRef'      => 'C-12849',
            'fullName'         => 'Saïd Ahamadi',
            'dateOfBirth'      => '1988-03-14',
            'phoneCountryCode' => '269',
            'phoneNumber'      => '32098798',
            'nationalIdType'   => 'NATIONAL_ID',
            'kycLevel'         => 'KYC_VERIFIED',
            'kycVerifiedAt'    => '2025-01-15T10:00:00Z',
            'status'           => 'ACTIVE',
            'walletId'         => self::WALLET_ID,
            'limitProfileId'   => 'lp_tier2',
            'addressIsland'    => 'Ngazidja',
            'addressCity'      => 'Moroni',
            'addressDistrict'  => 'Badjanani',
            'createdAt'        => '2024-06-01T08:00:00Z',
        ];
    }

    public static function balance(): array
    {
        return [
            'walletId'         => self::WALLET_ID,
            'availableBalance' => 184250,
            'frozenBalance'    => 12500,
            'currency'         => 'KMF',
            'walletStatus'     => 'ACTIVE',
            'updatedAt'        => '2026-05-14T11:55:00Z',
        ];
    }

    public static function limits(): array
    {
        return [
            'limitProfileId'             => 'lp_tier2',
            'profileName'                => 'KYC Verified · Tier 2',
            'maxTransactionAmount'       => 500000,
            'minTransactionAmount'       => 100,
            'maxDailyAmount'             => 1500000,
            'maxWeeklyAmount'            => 5000000,
            'maxMonthlyAmount'           => 8000000,
            'maxDailyTransactionCount'   => 30,
            'maxMonthlyTransactionCount' => 300,
            'requiredKycLevel'           => 'KYC_VERIFIED',
        ];
    }

    /** @return list<array> CustomerTransactionResponse[] */
    public static function transactions(): array
    {
        return [
            [
                'id'                     => 'tx_b91f2d4e3a',
                'type'                   => 'P2P_TRANSFER',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => self::WALLET_ID,
                'destinationWalletId'    => 'wal_faiza_01',
                'initiatorType'          => 'CUSTOMER',
                'requestedAmount'        => 25000,
                'feeAmount'              => 250,
                'netAmountToDestination' => 24750,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-14T10:42:00Z',
                'completedAt'            => '2026-05-14T10:42:01Z',
            ],
            [
                'id'                     => 'tx_3c8810eaab',
                'type'                   => 'CARD_SALE',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => self::WALLET_ID,
                'destinationWalletId'    => 'wal_mer_e1',
                'initiatorType'          => 'MERCHANT_OPERATOR',
                'cardId'                 => 'card_5f1e22',
                'terminalId'             => 't_1',
                'requestedAmount'        => 8400,
                'feeAmount'              => 0,
                'netAmountToDestination' => 8400,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-14T09:12:00Z',
                'completedAt'            => '2026-05-14T09:12:02Z',
            ],
            [
                'id'                     => 'tx_a17029fc01',
                'type'                   => 'CASH_IN',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => 'wal_agent_204',
                'destinationWalletId'    => self::WALLET_ID,
                'initiatorType'          => 'AGENT',
                'requestedAmount'        => 60000,
                'feeAmount'              => 0,
                'netAmountToDestination' => 60000,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-13T17:55:00Z',
                'completedAt'            => '2026-05-13T17:55:03Z',
            ],
            [
                'id'                     => 'tx_8d24fa12e7',
                'type'                   => 'P2P_TRANSFER',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => 'wal_mariam_01',
                'destinationWalletId'    => self::WALLET_ID,
                'initiatorType'          => 'CUSTOMER',
                'requestedAmount'        => 12000,
                'feeAmount'              => 0,
                'netAmountToDestination' => 12000,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-13T13:08:00Z',
                'completedAt'            => '2026-05-13T13:08:01Z',
            ],
            [
                'id'                     => 'tx_551c2f8b40',
                'type'                   => 'PAYMENT',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => self::WALLET_ID,
                'destinationWalletId'    => 'wal_pharmacie',
                'initiatorType'          => 'MERCHANT_OPERATOR',
                'requestedAmount'        => 5300,
                'feeAmount'              => 0,
                'netAmountToDestination' => 5300,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-12T18:22:00Z',
                'completedAt'            => '2026-05-12T18:22:01Z',
            ],
            [
                'id'                     => 'tx_77ad03ed91',
                'type'                   => 'P2P_TRANSFER',
                'status'                 => 'DECLINED',
                'sourceWalletId'         => self::WALLET_ID,
                'destinationWalletId'    => '',
                'initiatorType'          => 'CUSTOMER',
                'requestedAmount'        => 90000,
                'feeAmount'              => 0,
                'netAmountToDestination' => 0,
                'currency'               => 'KMF',
                'declineReason'          => 'INSUFFICIENT_BALANCE',
                'createdAt'              => '2026-05-12T11:14:00Z',
                'declinedAt'             => '2026-05-12T11:14:00Z',
            ],
            [
                'id'                     => 'tx_bd55207cee',
                'type'                   => 'CASH_OUT',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => self::WALLET_ID,
                'destinationWalletId'    => 'wal_agent_118',
                'initiatorType'          => 'AGENT',
                'requestedAmount'        => 30000,
                'feeAmount'              => 300,
                'netAmountToDestination' => 29700,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-11T14:30:00Z',
                'completedAt'            => '2026-05-11T14:30:02Z',
            ],
            [
                'id'                     => 'tx_cc99001122',
                'type'                   => 'CARD_SALE',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => self::WALLET_ID,
                'destinationWalletId'    => 'wal_shop',
                'initiatorType'          => 'MERCHANT_OPERATOR',
                'cardId'                 => 'card_5f1e22',
                'requestedAmount'        => 3200,
                'feeAmount'              => 0,
                'netAmountToDestination' => 3200,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-10T15:10:00Z',
                'completedAt'            => '2026-05-10T15:10:01Z',
            ],
        ];
    }

    /** @return list<array> CustomerStatementEntryResponse[] */
    public static function statements(): array
    {
        $txs = self::transactions();
        $balance = 184250;
        $entries = [];
        foreach ($txs as $i => $tx) {
            $isCredit = $tx['destinationWalletId'] === self::WALLET_ID && $tx['status'] === 'COMPLETED';
            $amount = $tx['requestedAmount'];
            $entries[] = [
                'id'             => 'stmt_' . $i,
                'transactionId'  => $tx['id'],
                'entryType'      => $isCredit ? 'CREDIT' : 'DEBIT',
                'amount'         => $amount,
                'runningBalance' => $balance,
                'currency'       => 'KMF',
                'description'    => $tx['type'],
                'postedAt'       => $tx['completedAt'] ?? $tx['createdAt'],
                'globalSequence' => 1000 - $i,
            ];
            $balance += $isCredit ? -$amount : $amount;
        }
        return $entries;
    }

    /** @return list<array> BeneficiaryResponse[] */
    public static function beneficiaries(): array
    {
        return [
            ['customerId' => 'cus_faiza',  'externalRef' => 'C-10021', 'fullName' => 'Faïza Mohamed',  'phoneCountryCode' => '269', 'phoneNumber' => '33210456'],
            ['customerId' => 'cus_mariam', 'externalRef' => 'C-10042', 'fullName' => 'Mariam Soilihi', 'phoneCountryCode' => '269', 'phoneNumber' => '32881703'],
            ['customerId' => 'cus_yusuf',  'externalRef' => 'C-10088', 'fullName' => 'Yusuf Bacar',    'phoneCountryCode' => '269', 'phoneNumber' => '34021967'],
            ['customerId' => 'cus_anissa', 'externalRef' => 'C-10103', 'fullName' => 'Anissa Saïd',    'phoneCountryCode' => '269', 'phoneNumber' => '33417622'],
        ];
    }

    /** @return list<array> CustomerCardResponse[] */
    public static function cards(): array
    {
        return [
            [
                'id'                        => 'card_5f1e22',
                'internalCardLast4'         => '4271',
                'maskedInternalCardNumber'  => '•••• 4271',
                'cardType'                  => 'STANDARD',
                'status'                    => 'ACTIVE',
                'pinEnabled'                => true,
                'activatedAt'               => '2024-12-05T08:00:00Z',
                'expiresAt'                 => '2028-11-30',
                'lastUsedAt'                => '2026-05-14T09:12:00Z',
                'issuedAt'                  => '2024-12-04T00:00:00Z',
            ],
            [
                'id'                        => 'card_91b302',
                'internalCardLast4'         => '0863',
                'maskedInternalCardNumber'  => '•••• 0863',
                'cardType'                  => 'STANDARD',
                'status'                    => 'BLOCKED',
                'pinEnabled'                => true,
                'activatedAt'               => '2023-04-13T08:00:00Z',
                'expiresAt'                 => '2027-04-30',
                'lastUsedAt'                => '2026-03-02T11:08:00Z',
                'issuedAt'                  => '2023-04-12T00:00:00Z',
            ],
        ];
    }
}
