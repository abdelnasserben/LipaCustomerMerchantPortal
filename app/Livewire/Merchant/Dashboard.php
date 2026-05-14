<?php

namespace App\Livewire\Merchant;

use App\Data\Mock\MerchantData;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $profile = MerchantData::profile();
        $balance = MerchantData::balance();
        $transactions = array_slice(MerchantData::transactions(), 0, 8);
        $terminals = MerchantData::terminals();
        $chart = MerchantData::hourlyChart();

        $activeTerminals = count(array_filter($terminals, fn($t) => $t['status'] === 'ACTIVE'));

        return view('livewire.merchant.dashboard', compact('profile', 'balance', 'transactions', 'terminals', 'activeTerminals', 'chart'))
            ->layout('layouts.merchant', ['title' => 'Lipa Merchant · Dashboard']);
    }
}
