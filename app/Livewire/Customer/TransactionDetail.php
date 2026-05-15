<?php

namespace App\Livewire\Customer;

use App\Komopay\Contracts\CustomerApi;
use App\Komopay\Exceptions\KomopayException;
use App\Komopay\Presenters\TransactionPresenter;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class TransactionDetail extends Component
{
    use HandlesAuthException;

    protected string $actor = 'customer';

    public string $id;
    public ?array $tx = null;

    public function mount(string $id, CustomerApi $api, TransactionPresenter $presenter): void
    {
        $this->id = $id;
        try {
            $walletId = $api->profile()['walletId'] ?? '';
            $this->tx = $presenter->present($api->transaction($id), $walletId);
        } catch (KomopayException) {
            $this->tx = null;
        }
    }

    public function render()
    {
        return view('livewire.customer.transaction-detail', ['tx' => $this->tx])
            ->layout('layouts.customer', ['title' => __('customer.title.transaction')]);
    }
}
