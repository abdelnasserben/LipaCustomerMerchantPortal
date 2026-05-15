<?php

namespace App\Livewire\Merchant;

use App\Komopay\Contracts\MerchantApi;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Profile extends Component
{
    use HandlesAuthException;

    protected string $actor = 'merchant';

    public function render(MerchantApi $api)
    {
        $profile = $api->profile();
        $balance = $api->balance();
        return view('livewire.merchant.profile', compact('profile', 'balance'))
            ->layout('layouts.merchant', ['title' => __('merchant.title.profile')]);
    }
}
