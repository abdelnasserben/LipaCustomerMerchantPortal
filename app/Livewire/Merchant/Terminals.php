<?php

namespace App\Livewire\Merchant;

use App\Data\Mock\MerchantData;
use Livewire\Component;

class Terminals extends Component
{
    public ?array $selectedTerminal = null;

    public function selectTerminal(string $id): void
    {
        foreach (MerchantData::terminals() as $t) {
            if ($t['id'] === $id) {
                $this->selectedTerminal = $t;
                break;
            }
        }
    }

    public function back(): void
    {
        $this->selectedTerminal = null;
    }

    public function render()
    {
        $terminals = MerchantData::terminals();
        return view('livewire.merchant.terminals', compact('terminals'))
            ->layout('layouts.merchant', ['title' => 'Lipa Merchant · Terminals']);
    }
}
