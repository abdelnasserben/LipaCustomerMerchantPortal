<?php

namespace App\Livewire\Merchant;

use App\Komopay\Contracts\MerchantApi;
use App\Komopay\Exceptions\KomopayException;
use App\Komopay\Presenters\TerminalPresenter;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Terminals extends Component
{
    use HandlesAuthException;

    protected string $actor = 'merchant';

    public ?array $selectedTerminal = null;

    public function selectTerminal(string $id, MerchantApi $api, TerminalPresenter $presenter): void
    {
        try {
            $this->selectedTerminal = $presenter->present($api->terminal($id));
        } catch (KomopayException) {
            $this->selectedTerminal = null;
        }
    }

    public function back(): void
    {
        $this->selectedTerminal = null;
    }

    public function render(MerchantApi $api, TerminalPresenter $presenter)
    {
        $terminals = $presenter->presentMany($api->terminals());
        return view('livewire.merchant.terminals', compact('terminals'))
            ->layout('layouts.merchant', ['title' => __('merchant.title.terminals')]);
    }
}
