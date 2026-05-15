<?php

namespace App\Livewire\Customer;

use App\Komopay\Contracts\CustomerApi;
use App\Komopay\Presenters\TransactionPresenter;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Transactions extends Component
{
    use HandlesAuthException;

    protected string $actor = 'customer';

    public string $filterStatus = '';
    public string $filterType = '';
    public string $filterFrom = '';
    public string $filterTo = '';
    public bool $showFilters = false;

    public function render(CustomerApi $api, TransactionPresenter $presenter)
    {
        $page = $api->transactions(
            filters: array_filter([
                'status' => $this->filterStatus ?: null,
                'type'   => $this->filterType ?: null,
                'from'   => $this->filterFrom ?: null,
                'to'     => $this->filterTo ?: null,
            ]),
            limit: 50,
        );

        $walletId = $api->profile()['walletId'] ?? '';
        $transactions = $presenter->presentMany($page->items, $walletId);

        $grouped = [];
        foreach ($transactions as $tx) {
            $date = date('Y-m-d', strtotime($tx['createdAt']));
            $grouped[$date][] = $tx;
        }

        return view('livewire.customer.transactions', compact('transactions', 'grouped'))
            ->layout('layouts.customer', ['title' => __('customer.title.activity')]);
    }
}
