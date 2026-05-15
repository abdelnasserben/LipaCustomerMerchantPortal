<?php

namespace App\Komopay\Presenters;

/**
 * Augments a CustomerTransactionResponse / MerchantTransactionResponse
 * (spec 7.3 / 7.6) with derived UI-only fields:
 *
 *   direction       — "in" | "out", relative to the active wallet
 *   counterparty    — human label resolved via CounterpartyDirectory
 *                     (falls back to the raw wallet id in real-API mode)
 *   operatorName    — resolved from operatorId, if present
 *   terminalSerial  — resolved from terminalId, if present
 *   cardLast4       — resolved from cardId, if present
 *
 * The original spec keys are kept untouched.
 */
final class TransactionPresenter
{
    public function __construct(
        private readonly CounterpartyDirectory $directory,
    ) {}

    public function present(array $tx, string $activeWalletId): array
    {
        $direction = $tx['destinationWalletId'] === $activeWalletId ? 'in' : 'out';

        $otherWalletId = $direction === 'in'
            ? ($tx['sourceWalletId'] ?? '')
            : ($tx['destinationWalletId'] ?? '');

        $tx['direction']    = $direction;
        $tx['counterparty'] = $this->directory->walletLabel($otherWalletId);

        if (!empty($tx['operatorId'])) {
            $tx['operatorName'] = $this->directory->operatorName($tx['operatorId']) ?? $tx['operatorId'];
        }
        if (!empty($tx['terminalId'])) {
            $tx['terminalSerial'] = $this->directory->terminalSerial($tx['terminalId']) ?? $tx['terminalId'];
        }
        if (!empty($tx['cardId'])) {
            $tx['cardLast4'] = $this->directory->cardLast4($tx['cardId']);
            if ($tx['cardLast4']) {
                $tx['counterparty'] = 'Card •• ' . $tx['cardLast4']
                    . ($direction === 'in' && $this->directory->walletLabel($otherWalletId)
                        ? ' · ' . $this->directory->walletLabel($otherWalletId)
                        : '');
            }
        }

        return $tx;
    }

    /** @param list<array> $rows */
    public function presentMany(array $rows, string $activeWalletId): array
    {
        return array_map(fn(array $r) => $this->present($r, $activeWalletId), $rows);
    }
}
