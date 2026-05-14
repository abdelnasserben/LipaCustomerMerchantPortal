<?php

namespace App\Livewire\Customer;

use App\Data\Mock\CustomerData;
use Livewire\Component;

class Cards extends Component
{
    public ?array $selectedCard = null;
    public bool $showReportModal = false;
    public string $reportType = ''; // lost | stolen

    public function selectCard(string $id): void
    {
        foreach (CustomerData::cards() as $card) {
            if ($card['id'] === $id) {
                $this->selectedCard = $card;
                break;
            }
        }
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

    public function confirmReport(): void
    {
        if ($this->selectedCard) {
            $this->selectedCard['status'] = strtoupper($this->reportType);
        }
        $this->showReportModal = false;
    }

    public function render()
    {
        $cards = CustomerData::cards();
        return view('livewire.customer.cards', compact('cards'))
            ->layout('layouts.customer', ['title' => 'Lipa · Cards']);
    }
}
