<?php

namespace App\Komopay\Presenters;

/** Mock-only address book wired in `KOMOPAY_USE_MOCK_API=true` mode. */
final class MockCounterpartyDirectory implements CounterpartyDirectory
{
    private const WALLETS = [
        'wal_5d2a91'      => 'Saïd Ahamadi',
        'wal_faiza_01'    => 'Faïza Mohamed',
        'wal_mariam_01'   => 'Mariam Soilihi',
        'wal_mer_e1'      => 'Boutique Karthala',
        'wal_pharmacie'   => 'Pharmacie El Maarouf',
        'wal_shop'        => 'Librairie Al Kamar',
        'wal_agent_204'   => 'Agent · Anjouan Centre',
        'wal_agent_118'   => 'Agent · Moroni Volo Volo',
        'wal_platform'    => 'Lipa platform',
        'wal_said'        => 'Saïd Ahamadi',
        'wal_cus_2'       => 'Card customer',
        'wal_cus_3'       => 'Card customer',
        'wal_cus_4'       => 'Card customer',
        'wal_cus_5'       => 'Card customer',
        'wal_cus_6'       => 'Card customer',
        'wal_cus_7'       => 'Card customer',
    ];

    private const CARDS = [
        'card_5f1e22' => '4271',
        'card_91b302' => '0863',
        'card_1158'   => '1158',
        'card_7702'   => '7702',
        'card_0214'   => '0214',
        'card_4490'   => '4490',
        'card_8821'   => '8821',
        'card_3017'   => '3017',
    ];

    private const OPERATORS = [
        'op_1' => 'Hadji A.',
        'op_2' => 'Salim N.',
        'op_3' => 'Riziki M.',
    ];

    private const TERMINALS = [
        't_1' => 'TERM-04',
        't_2' => 'TERM-03',
        't_3' => 'TERM-02',
        't_4' => 'TERM-01',
    ];

    public function walletLabel(string $walletId): ?string { return self::WALLETS[$walletId] ?? null; }
    public function cardLast4(string $cardId): ?string { return self::CARDS[$cardId] ?? null; }
    public function operatorName(string $operatorId): ?string { return self::OPERATORS[$operatorId] ?? null; }
    public function terminalSerial(string $terminalId): ?string { return self::TERMINALS[$terminalId] ?? null; }
}
