<?php

namespace App\Livewire\Customer;

use App\Komopay\Contracts\CustomerApi;
use App\Komopay\Exceptions\BusinessException;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Profile extends Component
{
    use HandlesAuthException;

    protected string $actor = 'customer';

    public function render(CustomerApi $api)
    {
        $profile = $api->profile();
        $balance = $api->balance();

        // Spec 5.2 / 12.4: 404 on /me/limits = "limits not configured", not an error.
        try {
            $limits = $api->limits();
        } catch (BusinessException $e) {
            $limits = $e->httpStatus() === 404 ? null : throw $e;
        }

        return view('livewire.customer.profile', compact('profile', 'balance', 'limits'))
            ->layout('layouts.customer', ['title' => __('customer.title.profile')]);
    }
}
