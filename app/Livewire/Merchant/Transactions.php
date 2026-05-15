<?php

namespace App\Livewire\Merchant;

use App\Komopay\Contracts\MerchantApi;
use App\Komopay\Presenters\TransactionPresenter;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Transactions extends Component
{
    use HandlesAuthException;

    protected string $actor = 'merchant';

    public string $filterStatus = '';
    public string $filterType = '';
    public bool $showFilters = false;

    public function render(MerchantApi $api, TransactionPresenter $presenter)
    {
        $page = $api->transactions(
            filters: array_filter([
                'status' => $this->filterStatus ?: null,
                'type'   => $this->filterType ?: null,
            ]),
            limit: 50,
        );

        $walletId = $api->profile()['walletId'] ?? '';
        $transactions = $presenter->presentMany($page->items, $walletId);

        return view('livewire.merchant.transactions', compact('transactions'))
            ->layout('layouts.merchant', ['title' => __('merchant.title.transactions')]);
    }
}
