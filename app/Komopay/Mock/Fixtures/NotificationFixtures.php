<?php

namespace App\Komopay\Mock\Fixtures;

/**
 * Static mock notifications for both the Customer and Merchant inboxes.
 *
 * Shapes mirror NotificationResponse (spec 7.8). The `data` payload is a
 * JSON string the UI parses to deep-link; transactionIds intentionally
 * reuse ids from CustomerFixtures / MerchantFixtures so taps in the mock
 * land on a real (mock) transaction detail page.
 */
final class NotificationFixtures
{
    /** @return list<array> */
    public static function forCustomer(): array
    {
        return [
            [
                'id'        => 'ntf_cus_001',
                'category'  => 'TRANSACTION',
                'title'     => 'Transfert reçu',
                'body'      => 'Vous avez reçu 12 000 KMF de Mariam.',
                'data'      => json_encode(['transactionId' => 'tx_8d24fa12e7', 'type' => 'P2P_TRANSFER']),
                'status'    => 'UNREAD',
                'createdAt' => '2026-05-14T10:43:00Z',
                'readAt'    => null,
            ],
            [
                'id'        => 'ntf_cus_002',
                'category'  => 'TRANSACTION',
                'title'     => 'Paiement effectué',
                'body'      => 'Paiement de 5 300 KMF à Pharmacie.',
                'data'      => json_encode(['transactionId' => 'tx_551c2f8b40', 'type' => 'PAYMENT']),
                'status'    => 'UNREAD',
                'createdAt' => '2026-05-12T18:22:30Z',
                'readAt'    => null,
            ],
            [
                'id'        => 'ntf_cus_003',
                'category'  => 'TRANSACTION',
                'title'     => 'Dépôt confirmé',
                'body'      => 'Dépôt de 60 000 KMF crédité sur votre portefeuille.',
                'data'      => json_encode(['transactionId' => 'tx_a17029fc01', 'type' => 'CASH_IN']),
                'status'    => 'READ',
                'createdAt' => '2026-05-13T17:55:30Z',
                'readAt'    => '2026-05-13T18:01:00Z',
            ],
            [
                'id'        => 'ntf_cus_004',
                'category'  => 'TRANSACTION',
                'title'     => 'Transfert envoyé',
                'body'      => 'Vous avez envoyé 25 000 KMF à Faiza.',
                'data'      => json_encode(['transactionId' => 'tx_b91f2d4e3a', 'type' => 'P2P_TRANSFER']),
                'status'    => 'READ',
                'createdAt' => '2026-05-14T10:42:30Z',
                'readAt'    => '2026-05-14T10:50:00Z',
            ],
        ];
    }

    /** @return list<array> */
    public static function forMerchant(): array
    {
        return [
            [
                'id'        => 'ntf_mer_001',
                'category'  => 'TRANSACTION',
                'title'     => 'Encaissement reçu',
                'body'      => 'Vente carte de 8 400 KMF encaissée (Terminal T-1).',
                'data'      => json_encode(['transactionId' => 'tx_3c8810eaab', 'type' => 'PAYMENT']),
                'status'    => 'UNREAD',
                'createdAt' => '2026-05-14T09:12:30Z',
                'readAt'    => null,
            ],
            [
                'id'        => 'ntf_mer_002',
                'category'  => 'TRANSACTION',
                'title'     => 'Retrait effectué',
                'body'      => 'Retrait commerçant de 200 000 KMF confirmé.',
                'data'      => json_encode(['transactionId' => 'tx_mer_cashout_01', 'type' => 'CASH_OUT']),
                'status'    => 'UNREAD',
                'createdAt' => '2026-05-13T15:30:00Z',
                'readAt'    => null,
            ],
            [
                'id'        => 'ntf_mer_003',
                'category'  => 'TRANSACTION',
                'title'     => 'Transfert inter-marchand reçu',
                'body'      => 'Vous avez reçu 75 000 KMF de Boutique Karthala.',
                'data'      => json_encode(['transactionId' => 'tx_mer_m2m_01', 'type' => 'MERCHANT_TO_MERCHANT']),
                'status'    => 'READ',
                'createdAt' => '2026-05-12T11:15:00Z',
                'readAt'    => '2026-05-12T11:20:00Z',
            ],
        ];
    }
}
