<?php

namespace App\Livewire\Customer;

use App\Data\Mock\CustomerData;
use Livewire\Component;

class Statement extends Component
{
    public string $from = '';
    public string $to = '';

    public function render()
    {
        $entries = CustomerData::statements();
        return view('livewire.customer.statement', compact('entries'))
            ->layout('layouts.customer', ['title' => 'Lipa · Statement']);
    }
}
