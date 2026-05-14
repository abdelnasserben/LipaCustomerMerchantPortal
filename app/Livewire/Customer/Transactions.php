<?php

namespace App\Livewire\Customer;

use App\Data\Mock\CustomerData;
use Livewire\Component;

class Transactions extends Component
{
    public string $filterStatus = '';
    public string $filterType = '';
    public string $filterFrom = '';
    public string $filterTo = '';
    public bool $showFilters = false;

    public function render()
    {
        $transactions = CustomerData::transactions();

        if ($this->filterStatus) {
            $transactions = array_filter($transactions, fn($t) => $t['status'] === $this->filterStatus);
        }
        if ($this->filterType) {
            $transactions = array_filter($transactions, fn($t) => $t['type'] === $this->filterType);
        }

        $grouped = [];
        foreach ($transactions as $tx) {
            $date = date('Y-m-d', strtotime($tx['createdAt']));
            $grouped[$date][] = $tx;
        }

        return view('livewire.customer.transactions', compact('transactions', 'grouped'))
            ->layout('layouts.customer', ['title' => 'Lipa · Activity']);
    }
}
