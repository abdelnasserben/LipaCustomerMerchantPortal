<?php

namespace App\Livewire\Merchant;

use App\Data\Mock\MerchantData;
use Livewire\Component;

class Profile extends Component
{
    public function render()
    {
        $profile = MerchantData::profile();
        $balance = MerchantData::balance();
        return view('livewire.merchant.profile', compact('profile', 'balance'))
            ->layout('layouts.merchant', ['title' => 'Lipa Merchant · Profile']);
    }
}
