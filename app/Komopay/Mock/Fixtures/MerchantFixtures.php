<?php

namespace App\Komopay\Mock\Fixtures;

/** Spec-conformant mock data for the Merchant surface (sections 7.5–7.7). */
final class MerchantFixtures
{
    public const WALLET_ID   = 'wal_mer_e1';
    public const MERCHANT_ID = 'mer_27c9e1ab';

    public static function profile(): array
    {
        return [
            'id'                     => self::MERCHANT_ID,
            'externalRef'            => 'M-2041',
            'businessName'           => 'Boutique Karthala',
            'legalName'              => 'Karthala Commerce SARL',
            'businessType'           => 'Retail',
            'taxId'                  => 'CMR-2024-0418',
            'phoneCountryCode'       => '269',
            'phoneNumber'            => '33210456',
            'addressIsland'          => 'Ngazidja',
            'addressCity'            => 'Moroni',
            'addressDistrict'        => 'Volo Volo',
            'category'               => 'Grocery & general store',
            'kycLevel'               => 'KYC_VERIFIED',
            'status'                 => 'ACTIVE',
            'walletId'               => self::WALLET_ID,
            'canCashOut'             => true,
            'canReceiveFromMerchant' => true,
            'createdAt'              => '2024-03-10T00:00:00Z',
        ];
    }

    public static function balance(): array
    {
        return [
            'walletId'         => self::WALLET_ID,
            'availableBalance' => 2487600,
            'frozenBalance'    => 42000,
            'currency'         => 'KMF',
            'walletStatus'     => 'ACTIVE',
            'updatedAt'        => '2026-05-14T11:48:00Z',
        ];
    }

    /** @return list<array> MerchantTransactionResponse[] */
    public static function transactions(): array
    {
        return [
            [
                'id'                     => 'tx_m_a01',
                'type'                   => 'CARD_SALE',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => 'wal_said',
                'destinationWalletId'    => self::WALLET_ID,
                'initiatorType'          => 'MERCHANT_OPERATOR',
                'initiatorId'            => 'op_1',
                'operatorId'             => 'op_1',
                'cardId'                 => 'card_5f1e22',
                'terminalId'             => 't_2',
                'requestedAmount'        => 8400,
                'feeAmount'              => 0,
                'commissionAmount'       => 84,
                'netAmountToDestination' => 8316,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-14T11:48:00Z',
                'completedAt'            => '2026-05-14T11:48:01Z',
            ],
            [
                'id'                     => 'tx_m_a02',
                'type'                   => 'CARD_SALE',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => 'wal_cus_2',
                'destinationWalletId'    => self::WALLET_ID,
                'initiatorType'          => 'MERCHANT_OPERATOR',
                'initiatorId'            => 'op_1',
                'operatorId'             => 'op_1',
                'cardId'                 => 'card_1158',
                'terminalId'             => 't_2',
                'requestedAmount'        => 14250,
                'feeAmount'              => 0,
                'commissionAmount'       => 143,
                'netAmountToDestination' => 14107,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-14T11:31:00Z',
                'completedAt'            => '2026-05-14T11:31:02Z',
            ],
            [
                'id'                     => 'tx_m_a03',
                'type'                   => 'CARD_SALE',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => 'wal_cus_3',
                'destinationWalletId'    => self::WALLET_ID,
                'initiatorType'          => 'MERCHANT_OPERATOR',
                'initiatorId'            => 'op_2',
                'operatorId'             => 'op_2',
                'cardId'                 => 'card_7702',
                'terminalId'             => 't_3',
                'requestedAmount'        => 3800,
                'feeAmount'              => 0,
                'commissionAmount'       => 38,
                'netAmountToDestination' => 3762,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-14T10:56:00Z',
                'completedAt'            => '2026-05-14T10:56:01Z',
            ],
            [
                'id'                     => 'tx_m_a04',
                'type'                   => 'MERCHANT_TO_MERCHANT',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => self::WALLET_ID,
                'destinationWalletId'    => 'wal_pharmacie',
                'initiatorType'          => 'MERCHANT',
                'initiatorId'            => self::MERCHANT_ID,
                'requestedAmount'        => 45000,
                'feeAmount'              => 450,
                'commissionAmount'       => 0,
                'netAmountToDestination' => 44550,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-14T10:14:00Z',
                'completedAt'            => '2026-05-14T10:14:01Z',
            ],
            [
                'id'                     => 'tx_m_a05',
                'type'                   => 'CARD_SALE',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => 'wal_cus_4',
                'destinationWalletId'    => self::WALLET_ID,
                'initiatorType'          => 'MERCHANT_OPERATOR',
                'initiatorId'            => 'op_2',
                'operatorId'             => 'op_2',
                'cardId'                 => 'card_0214',
                'terminalId'             => 't_3',
                'requestedAmount'        => 22100,
                'feeAmount'              => 0,
                'commissionAmount'       => 221,
                'netAmountToDestination' => 21879,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-14T09:42:00Z',
                'completedAt'            => '2026-05-14T09:42:01Z',
            ],
            [
                'id'                     => 'tx_m_a06',
                'type'                   => 'CARD_SALE',
                'status'                 => 'DECLINED',
                'sourceWalletId'         => 'wal_cus_5',
                'destinationWalletId'    => self::WALLET_ID,
                'initiatorType'          => 'MERCHANT_OPERATOR',
                'initiatorId'            => 'op_1',
                'operatorId'             => 'op_1',
                'cardId'                 => 'card_4490',
                'terminalId'             => 't_2',
                'requestedAmount'        => 6200,
                'feeAmount'              => 0,
                'commissionAmount'       => 0,
                'netAmountToDestination' => 0,
                'currency'               => 'KMF',
                'declineReason'          => 'WALLET_FROZEN',
                'createdAt'              => '2026-05-14T09:18:00Z',
                'declinedAt'             => '2026-05-14T09:18:00Z',
            ],
            [
                'id'                     => 'tx_m_a07',
                'type'                   => 'CARD_SALE',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => 'wal_cus_6',
                'destinationWalletId'    => self::WALLET_ID,
                'initiatorType'          => 'MERCHANT_OPERATOR',
                'initiatorId'            => 'op_1',
                'operatorId'             => 'op_1',
                'cardId'                 => 'card_8821',
                'terminalId'             => 't_2',
                'requestedAmount'        => 11500,
                'feeAmount'              => 0,
                'commissionAmount'       => 115,
                'netAmountToDestination' => 11385,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-14T09:02:00Z',
                'completedAt'            => '2026-05-14T09:02:01Z',
            ],
            [
                'id'                     => 'tx_m_a08',
                'type'                   => 'COMMISSION_PAYOUT',
                'status'                 => 'COMPLETED',
                'sourceWalletId'         => self::WALLET_ID,
                'destinationWalletId'    => 'wal_platform',
                'initiatorType'          => 'SYSTEM',
                'initiatorId'            => 'system',
                'requestedAmount'        => 1840,
                'feeAmount'              => 0,
                'commissionAmount'       => 0,
                'netAmountToDestination' => 1840,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-14T08:00:00Z',
                'completedAt'            => '2026-05-14T08:00:01Z',
            ],
            [
                'id'                     => 'tx_m_a09',
                'type'                   => 'CARD_SALE',
                'status'                 => 'PENDING',
                'sourceWalletId'         => 'wal_cus_7',
                'destinationWalletId'    => self::WALLET_ID,
                'initiatorType'          => 'MERCHANT_OPERATOR',
                'initiatorId'            => 'op_3',
                'operatorId'             => 'op_3',
                'cardId'                 => 'card_3017',
                'terminalId'             => 't_1',
                'requestedAmount'        => 18900,
                'feeAmount'              => 0,
                'commissionAmount'       => 0,
                'netAmountToDestination' => 0,
                'currency'               => 'KMF',
                'createdAt'              => '2026-05-14T07:48:00Z',
            ],
        ];
    }

    /** @return list<array> MerchantStatementEntryResponse[] */
    public static function statements(): array
    {
        $txs = self::transactions();
        $balance = 2487600;
        $entries = [];
        foreach ($txs as $i => $tx) {
            $isCredit = $tx['destinationWalletId'] === self::WALLET_ID && $tx['status'] === 'COMPLETED';
            $amount = $tx['requestedAmount'];
            $entries[] = [
                'id'             => 'stmt_m_' . $i,
                'transactionId'  => $tx['id'],
                'entryType'      => $isCredit ? 'CREDIT' : 'DEBIT',
                'amount'         => $amount,
                'runningBalance' => $balance,
                'currency'       => 'KMF',
                'description'    => $tx['type'],
                'postedAt'       => $tx['completedAt'] ?? $tx['createdAt'],
                'globalSequence' => 2000 - $i,
            ];
            $balance += $isCredit ? -$amount : $amount;
        }
        return $entries;
    }

    /** @return list<array> OperatorResponse[] */
    public static function operators(): array
    {
        return [
            [
                'id'               => 'op_1',
                'merchantId'       => self::MERCHANT_ID,
                'fullName'         => 'Hadji Abdou',
                'phoneCountryCode' => '269',
                'phoneNumber'      => '32091480',
                'status'           => 'ACTIVE',
                'createdAt'        => '2025-08-12T00:00:00Z',
                'lastLoginAt'      => '2026-05-14T11:48:00Z',
            ],
            [
                'id'               => 'op_2',
                'merchantId'       => self::MERCHANT_ID,
                'fullName'         => 'Salim Nourdine',
                'phoneCountryCode' => '269',
                'phoneNumber'      => '33216702',
                'status'           => 'ACTIVE',
                'createdAt'        => '2025-09-04T00:00:00Z',
                'lastLoginAt'      => '2026-05-14T10:56:00Z',
            ],
            [
                'id'               => 'op_3',
                'merchantId'       => self::MERCHANT_ID,
                'fullName'         => 'Riziki Mhoumadi',
                'phoneCountryCode' => '269',
                'phoneNumber'      => '32870311',
                'status'           => 'ACTIVE',
                'createdAt'        => '2026-01-23T00:00:00Z',
                'lastLoginAt'      => '2026-05-14T07:48:00Z',
            ],
            [
                'id'               => 'op_4',
                'merchantId'       => self::MERCHANT_ID,
                'fullName'         => 'Toiouilou Saïd',
                'phoneCountryCode' => '269',
                'phoneNumber'      => '33094128',
                'status'           => 'SUSPENDED',
                'createdAt'        => '2024-11-30T00:00:00Z',
                'lastLoginAt'      => '2026-04-21T18:02:00Z',
            ],
            [
                'id'               => 'op_5',
                'merchantId'       => self::MERCHANT_ID,
                'fullName'         => 'Anrafati Bacar',
                'phoneCountryCode' => '269',
                'phoneNumber'      => '34112875',
                'status'           => 'REVOKED',
                'createdAt'        => '2024-06-04T00:00:00Z',
                'lastLoginAt'      => '2025-11-09T15:18:00Z',
            ],
        ];
    }

    /** @return list<array> MerchantTerminalResponse[] */
    public static function terminals(): array
    {
        return [
            [
                'id'             => 't_1',
                'serialNumber'   => 'KP-AND-00412',
                'deviceModel'    => 'Sunmi P2 Pro',
                'androidVersion' => '11',
                'appVersion'     => '2.4.1',
                'status'         => 'ACTIVE',
                'lastSeenAt'     => '2026-05-14T11:48:00Z',
                'registeredAt'   => '2024-04-01T00:00:00Z',
            ],
            [
                'id'             => 't_2',
                'serialNumber'   => 'KP-AND-00388',
                'deviceModel'    => 'Sunmi P2 Pro',
                'androidVersion' => '11',
                'appVersion'     => '2.4.1',
                'status'         => 'ACTIVE',
                'lastSeenAt'     => '2026-05-14T11:31:00Z',
                'registeredAt'   => '2024-04-01T00:00:00Z',
            ],
            [
                'id'             => 't_3',
                'serialNumber'   => 'KP-AND-00355',
                'deviceModel'    => 'PAX A920',
                'androidVersion' => '10',
                'appVersion'     => '2.3.9',
                'status'         => 'ACTIVE',
                'lastSeenAt'     => '2026-05-14T07:48:00Z',
                'registeredAt'   => '2024-02-15T00:00:00Z',
            ],
            [
                'id'             => 't_4',
                'serialNumber'   => 'KP-AND-00219',
                'deviceModel'    => 'PAX A920',
                'androidVersion' => '10',
                'appVersion'     => '2.3.4',
                'status'         => 'SUSPENDED',
                'lastSeenAt'     => '2026-04-18T16:12:00Z',
                'registeredAt'   => '2023-11-20T00:00:00Z',
            ],
        ];
    }
}
