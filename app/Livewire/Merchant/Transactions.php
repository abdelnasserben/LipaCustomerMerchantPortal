<?php

namespace App\Livewire\Merchant;

use App\Data\Mock\MerchantData;
use Livewire\Component;

class Transactions extends Component
{
    public string $filterStatus = '';
    public string $filterType = '';
    public bool $showFilters = false;

    public function render()
    {
        $transactions = MerchantData::transactions();

        if ($this->filterStatus) {
            $transactions = array_filter($transactions, fn($t) => $t['status'] === $this->filterStatus);
        }
        if ($this->filterType) {
            $transactions = array_filter($transactions, fn($t) => $t['type'] === $this->filterType);
        }

        return view('livewire.merchant.transactions', compact('transactions'))
            ->layout('layouts.merchant', ['title' => 'Lipa Merchant · Transactions']);
    }
}
