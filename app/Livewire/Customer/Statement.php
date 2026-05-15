<?php

namespace App\Livewire\Customer;

use App\Komopay\Contracts\CustomerApi;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Statement extends Component
{
    use HandlesAuthException;

    protected string $actor = 'customer';

    public string $from = '';
    public string $to = '';

    public function render(CustomerApi $api)
    {
        $entries = $api->statements(
            from: $this->from ?: null,
            to:   $this->to ?: null,
            limit: 100,
        )->items;

        return view('livewire.customer.statement', compact('entries'))
            ->layout('layouts.customer', ['title' => 'Lipa · Statement']);
    }
}
