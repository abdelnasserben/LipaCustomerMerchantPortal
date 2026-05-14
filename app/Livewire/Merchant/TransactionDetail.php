<?php

namespace App\Livewire\Merchant;

use App\Data\Mock\MerchantData;
use Livewire\Component;

class TransactionDetail extends Component
{
    public string $id;
    public ?array $tx = null;

    public function mount(string $id): void
    {
        $this->id = $id;
        foreach (MerchantData::transactions() as $t) {
            if ($t['id'] === $id) {
                $this->tx = $t;
                break;
            }
        }
    }

    public function render()
    {
        return view('livewire.merchant.transaction-detail', ['tx' => $this->tx])
            ->layout('layouts.merchant', ['title' => 'Lipa Merchant · Transaction']);
    }
}
