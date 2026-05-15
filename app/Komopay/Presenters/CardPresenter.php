<?php

namespace App\Komopay\Presenters;

/** Adds `last4` (UI-only) to a CustomerCardResponse, preferring the API field. */
final class CardPresenter
{
    public function __construct(
        private readonly CounterpartyDirectory $directory,
    ) {}

    public function present(array $card): array
    {
        $card['last4'] = $card['internalCardLast4']
            ?? $this->directory->cardLast4($card['id']);
        return $card;
    }

    /** @param list<array> $cards */
    public function presentMany(array $cards): array
    {
        return array_map(fn(array $c) => $this->present($c), $cards);
    }
}
