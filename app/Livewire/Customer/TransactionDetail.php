<?php

namespace App\Livewire\Customer;

use App\Data\Mock\CustomerData;
use Livewire\Component;

class TransactionDetail extends Component
{
    public string $id;
    public ?array $tx = null;

    public function mount(string $id): void
    {
        $this->id = $id;
        $all = CustomerData::transactions();
        foreach ($all as $t) {
            if ($t['id'] === $id) {
                $this->tx = $t;
                break;
            }
        }
    }

    public function render()
    {
        return view('livewire.customer.transaction-detail', ['tx' => $this->tx])
            ->layout('layouts.customer', ['title' => 'Lipa · Transaction']);
    }
}
