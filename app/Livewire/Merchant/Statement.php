<?php

namespace App\Livewire\Merchant;

use App\Komopay\Contracts\MerchantApi;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Statement extends Component
{
    use HandlesAuthException;

    protected string $actor = 'merchant';

    public string $from = '';
    public string $to = '';

    public function render(MerchantApi $api)
    {
        $entries = $api->statements(
            from: $this->from ?: null,
            to:   $this->to ?: null,
            limit: 100,
        )->items;

        return view('livewire.merchant.statement', compact('entries'))
            ->layout('layouts.merchant', ['title' => __('merchant.title.statement')]);
    }
}
