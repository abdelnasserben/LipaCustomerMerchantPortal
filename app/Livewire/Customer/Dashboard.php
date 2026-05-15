<?php

namespace App\Livewire\Customer;

use App\Komopay\Contracts\CustomerApi;
use App\Komopay\Presenters\TransactionPresenter;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Dashboard extends Component
{
    use HandlesAuthException;

    protected string $actor = 'customer';

    public bool $balanceHidden = false;

    public function toggleBalance(): void
    {
        $this->balanceHidden = !$this->balanceHidden;
    }

    public function render(CustomerApi $api, TransactionPresenter $presenter)
    {
        $profile       = $api->profile();
        $balance       = $api->balance();
        $beneficiaries = $api->beneficiaries(20);
        $activity      = $presenter->presentMany($api->activity(10), $profile['walletId'] ?? '');

        $grouped = [];
        foreach ($activity as $tx) {
            $date = date('Y-m-d', strtotime($tx['createdAt']));
            $grouped[$date][] = $tx;
        }

        return view('livewire.customer.dashboard', compact('profile', 'balance', 'activity', 'beneficiaries', 'grouped'))
            ->layout('layouts.customer', ['title' => __('customer.title.home')]);
    }
}
