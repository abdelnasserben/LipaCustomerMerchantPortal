<?php

namespace App\Livewire\Customer;

use App\Komopay\Contracts\CustomerApi;
use App\Komopay\Presenters\CardPresenter;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Cards extends Component
{
    use HandlesAuthException;

    protected string $actor = 'customer';

    public ?array $selectedCard = null;
    public bool $showReportModal = false;
    public string $reportType = ''; // lost | stolen

    public function selectCard(string $id, CustomerApi $api, CardPresenter $presenter): void
    {
        $this->selectedCard = $presenter->present($api->card($id));
    }

    public function back(): void
    {
        $this->selectedCard = null;
        $this->showReportModal = false;
    }

    public function openReportModal(string $type): void
    {
        $this->reportType = $type;
        $this->showReportModal = true;
    }

    public function confirmReport(CustomerApi $api, CardPresenter $presenter): void
    {
        if ($this->selectedCard) {
            $card = $this->reportType === 'stolen'
                ? $api->reportCardStolen($this->selectedCard['id'])
                : $api->reportCardLost($this->selectedCard['id']);
            $this->selectedCard = $presenter->present($card);
        }
        $this->showReportModal = false;
    }

    public function render(CustomerApi $api, CardPresenter $presenter)
    {
        $cards = $presenter->presentMany($api->cards());

        return view('livewire.customer.cards', compact('cards'))
            ->layout('layouts.customer', ['title' => 'Lipa · Cards']);
    }
}
