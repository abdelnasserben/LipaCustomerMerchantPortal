<?php

namespace App\Livewire\Merchant;

use App\Komopay\Contracts\MerchantApi;
use App\Komopay\Exceptions\KomopayException;
use App\Komopay\Presenters\TransactionPresenter;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class TransactionDetail extends Component
{
    use HandlesAuthException;

    protected string $actor = 'merchant';

    public string $id;
    public ?array $tx = null;

    public function mount(string $id, MerchantApi $api, TransactionPresenter $presenter): void
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
        return view('livewire.merchant.transaction-detail', ['tx' => $this->tx])
            ->layout('layouts.merchant', ['title' => __('merchant.title.transaction')]);
    }
}
