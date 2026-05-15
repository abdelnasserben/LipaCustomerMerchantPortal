<?php

namespace App\Komopay\Presenters;

/** Adds `operatorAlias` (UI-only) to a MerchantTerminalResponse via the directory. */
final class TerminalPresenter
{
    public function __construct(
        private readonly CounterpartyDirectory $directory,
    ) {}

    public function present(array $terminal): array
    {
        $terminal['operatorAlias'] = $this->directory->terminalSerial($terminal['id'])
            ?? $terminal['serialNumber']
            ?? $terminal['id'];
        return $terminal;
    }

    /** @param list<array> $terminals */
    public function presentMany(array $terminals): array
    {
        return array_map(fn(array $t) => $this->present($t), $terminals);
    }
}
