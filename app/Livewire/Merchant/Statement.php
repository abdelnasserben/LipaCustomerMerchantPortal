<?php

namespace App\Livewire\Merchant;

use App\Data\Mock\MerchantData;
use Livewire\Component;

class Statement extends Component
{
    public string $from = '';
    public string $to = '';

    public function render()
    {
        $entries = MerchantData::statements();
        return view('livewire.merchant.statement', compact('entries'))
            ->layout('layouts.merchant', ['title' => 'Lipa Merchant · Statement']);
    }
}
