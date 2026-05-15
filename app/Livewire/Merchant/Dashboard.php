<?php

namespace App\Livewire\Merchant;

use App\Komopay\Contracts\MerchantApi;
use App\Komopay\Presenters\TransactionPresenter;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class Dashboard extends Component
{
    use HandlesAuthException;

    protected string $actor = 'merchant';

    public function render(MerchantApi $api, TransactionPresenter $presenter)
    {
        $profile      = $api->profile();
        $balance      = $api->balance();
        $page         = $api->transactions(limit: 8);
        $rawTx        = $page->items;
        $transactions = $presenter->presentMany($rawTx, $profile['walletId'] ?? '');
        $terminals    = $api->terminals();

        $activeTerminals = count(array_filter($terminals, fn($t) => $t['status'] === 'ACTIVE'));

        $walletId = $profile['walletId'] ?? '';
        $balance += $this->periodStats($rawTx, $walletId);

        // Hourly chart is a UI-only roll-up — derive from completed credits today.
        $chart = $this->hourlyRollup($rawTx, $walletId);

        return view('livewire.merchant.dashboard', compact(
            'profile', 'balance', 'transactions', 'terminals', 'activeTerminals', 'chart',
        ))->layout('layouts.merchant', ['title' => 'Lipa Merchant · Dashboard']);
    }

    /** @param list<array> $rows */
    private function periodStats(array $rows, string $walletId): array
    {
        $todayStart = strtotime('today');
        $weekStart  = strtotime('-6 days 00:00');
        $todayIn = $todayCount = $weekIn = 0;
        foreach ($rows as $tx) {
            if ($tx['status'] !== 'COMPLETED') continue;
            if (($tx['destinationWalletId'] ?? '') !== $walletId) continue;
            $ts = strtotime($tx['createdAt'] ?? 'now');
            $amt = (int) ($tx['netAmountToDestination'] ?? $tx['requestedAmount'] ?? 0);
            if ($ts >= $todayStart) {
                $todayIn += $amt;
                $todayCount++;
            }
            if ($ts >= $weekStart) {
                $weekIn += $amt;
            }
        }
        return compact('todayIn', 'todayCount', 'weekIn');
    }

    /** @param list<array> $rows */
    private function hourlyRollup(array $rows, string $walletId): array
    {
        $buckets = [];
        foreach ($rows as $tx) {
            if ($tx['status'] !== 'COMPLETED') continue;
            if (($tx['destinationWalletId'] ?? '') !== $walletId) continue;
            if (empty($tx['createdAt'])) continue;
            $ts = strtotime($tx['createdAt']);
            if ($ts === false) continue;
            $hour = date('H:00', $ts);
            $buckets[$hour] = ($buckets[$hour] ?? 0) + (int) ($tx['requestedAmount'] ?? 0);
        }
        ksort($buckets);
        $out = [];
        foreach ($buckets as $h => $a) {
            $out[] = ['hour' => $h, 'amount' => $a];
        }
        return $out;
    }
}
