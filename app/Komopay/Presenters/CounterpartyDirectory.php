<?php

namespace App\Komopay\Presenters;

/**
 * Optional UI-only address book that maps wallet/card/operator/terminal ids
 * to human labels. The spec does NOT include such a service — when the
 * portal is wired to the real API, the bound implementation should be
 * `NullCounterpartyDirectory` and views must accept raw ids as fallback.
 */
interface CounterpartyDirectory
{
    public function walletLabel(string $walletId): ?string;

    public function cardLast4(string $cardId): ?string;

    public function operatorName(string $operatorId): ?string;

    public function terminalSerial(string $terminalId): ?string;
}
