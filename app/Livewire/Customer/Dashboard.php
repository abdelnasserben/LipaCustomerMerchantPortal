<?php

namespace App\Livewire\Customer;

use App\Data\Mock\CustomerData;
use App\Services\FormatService;
use Livewire\Component;

class Dashboard extends Component
{
    public bool $balanceHidden = false;

    public function toggleBalance(): void
    {
        $this->balanceHidden = !$this->balanceHidden;
    }

    public function render()
    {
        $profile = CustomerData::profile();
        $balance = CustomerData::balance();
        $activity = CustomerData::activity(10);
        $beneficiaries = CustomerData::beneficiaries();

        // Group activity by date
        $grouped = [];
        foreach ($activity as $tx) {
            $date = date('Y-m-d', strtotime($tx['createdAt']));
            $grouped[$date][] = $tx;
        }

        return view('livewire.customer.dashboard', compact('profile', 'balance', 'activity', 'beneficiaries', 'grouped'))
            ->layout('layouts.customer', ['title' => 'Lipa · Home']);
    }
}
