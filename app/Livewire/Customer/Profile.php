<?php

namespace App\Livewire\Customer;

use App\Data\Mock\CustomerData;
use Livewire\Component;

class Profile extends Component
{
    public function render()
    {
        $profile = CustomerData::profile();
        $balance = CustomerData::balance();
        $limits = CustomerData::limits();
        return view('livewire.customer.profile', compact('profile', 'balance', 'limits'))
            ->layout('layouts.customer', ['title' => 'Lipa · Profile']);
    }
}
