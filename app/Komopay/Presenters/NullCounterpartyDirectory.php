<?php

namespace App\Komopay\Presenters;

/** Used in real-API mode — no enrichment, presenters fall back to raw ids. */
final class NullCounterpartyDirectory implements CounterpartyDirectory
{
    public function walletLabel(string $walletId): ?string { return null; }
    public function cardLast4(string $cardId): ?string { return null; }
    public function operatorName(string $operatorId): ?string { return null; }
    public function terminalSerial(string $terminalId): ?string { return null; }
}
