@php use App\Services\FormatService; @endphp
<div class="px-5 lg:px-8 pt-5 lg:pt-8 lg:max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        @if($step !== 'form')
        <button wire:click="$set('step', 'form')" class="circle-btn">
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </button>
        @endif
        <div class="min-w-0 flex-1">
            <h1 class="font-bold lg:!text-2xl truncate" style="font-size: 19px; letter-spacing: -0.02em;">
                @if($step === 'form') Send to Merchant (M2M)
                @elseif($step === 'confirm') Confirm Transfer
                @elseif($step === 'receipt') Transfer Complete
                @endif
            </h1>
        </div>
    </div>

    @if(!$profile['canReceiveFromMerchant'])
    <div class="alert alert-warn mb-5">
        <x-icon name="warn" class="w-4 h-4 flex-shrink-0"/>
        <div>
            <div class="font-semibold mb-1">M2M transfers not enabled</div>
            <div>Your account is not configured to send merchant-to-merchant transfers. Contact support to enable this feature.</div>
        </div>
    </div>
    @endif

    @if($error)
    <div class="alert alert-danger mb-5">
        <x-icon name="warn" class="w-4 h-4 flex-shrink-0"/>
        <span>{{ $error }}</span>
    </div>
    @endif

    @if($step === 'form')
    <form wire:submit="proceedToConfirm" class="flex flex-col gap-5">
        <div class="card p-4 flex items-center justify-between">
            <div>
                <div class="section-title mb-1">Available Balance</div>
                <div class="font-mono font-bold" style="font-size: 20px;">{{ FormatService::kmf($balance['availableBalance']) }}</div>
            </div>
            <x-status-pill :status="$balance['walletStatus']"/>
        </div>

        <div>
            <label class="label">Recipient Merchant Phone</label>
            <div style="display: flex; height: 52px; border: 1px solid var(--color-border-hi); border-radius: 12px; background: #fff; overflow: hidden;">
                <div style="width: 80px; background: var(--color-surface-alt); display: flex; align-items: center; justify-content: center; border-right: 1px solid var(--color-border); font-family: var(--font-mono); font-weight: 600; font-size: 14px; color: var(--color-ink-mid);">
                    +269
                </div>
                <input wire:model="recipientPhone" type="tel" placeholder="33 XX XX XX"
                    style="flex: 1; border: none; outline: none; padding: 0 16px; font-family: var(--font-mono); font-size: 16px; color: var(--color-ink-hi); background: transparent;"/>
            </div>
        </div>

        <div>
            <label class="label">Amount (KMF)</label>
            <div style="display: flex; height: 64px; border: 1px solid var(--color-border-hi); border-radius: 12px; background: #fff; align-items: center; padding: 0 18px; gap: 8px;">
                <input wire:model="amountInput" type="text" inputmode="numeric" placeholder="0"
                    style="flex: 1 1 auto; min-width: 0; border: none; outline: none; font-family: var(--font-mono); font-size: 26px; font-weight: 600; color: var(--color-ink-hi); background: transparent; letter-spacing: -0.02em;"/>
                <span style="flex-shrink: 0; font-family: var(--font-mono); font-size: 14px; color: var(--color-ink-low);">KMF</span>
            </div>
        </div>

        <div>
            <label class="label">Note (optional)</label>
            <input wire:model="description" type="text" placeholder="What's this for?" class="input" style="font-family: var(--font-sans); font-size: 15px;"/>
        </div>

        <div class="alert alert-info">
            <x-icon name="warn" class="w-4 h-4 flex-shrink-0"/>
            <div>
                <div class="font-semibold mb-1">No PIN gate on M2M</div>
                <div>Merchant-to-merchant transfers execute immediately. An idempotency key is generated automatically.</div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-full" {{ !$profile['canReceiveFromMerchant'] ? 'disabled' : '' }}>
            Review Transfer
        </button>
    </form>
    @endif

    @if($step === 'confirm')
    <div class="flex flex-col gap-5">
        <div class="card p-6 text-center">
            <div style="font-size: 13px; color: var(--color-ink-mid); margin-bottom: 6px;">Sending</div>
            <div class="font-mono font-bold" style="font-size: 36px; letter-spacing: -0.025em;">{{ FormatService::kmf($amount) }}</div>
            <div style="font-size: 13px; color: var(--color-ink-mid); margin-top: 6px;">to +269 {{ $recipientPhone }}</div>
        </div>

        <div class="card-flat overflow-hidden">
            <div class="flex justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                <span style="font-size: 13px; color: var(--color-ink-mid);">Recipient phone</span>
                <span class="font-mono" style="font-size: 13px;">+269 {{ $recipientPhone }}</span>
            </div>
            <div class="flex justify-between px-4 py-3">
                <span style="font-size: 13px; color: var(--color-ink-mid);">Amount</span>
                <span class="font-mono font-semibold" style="font-size: 13px;">{{ FormatService::kmf($amount) }}</span>
            </div>
        </div>

        <div class="card-flat p-4">
            <div class="section-title mb-1">Idempotency Key</div>
            <div class="font-mono text-sm" style="color: var(--color-ink-mid); word-break: break-all;">{{ $idempotencyKey }}</div>
        </div>

        <button wire:click="submit" class="btn btn-primary btn-lg btn-full">Execute Transfer</button>
        <button wire:click="$set('step', 'form')" class="btn btn-ghost btn-md btn-full" style="color: var(--color-ink-mid);">Edit</button>
    </div>
    @endif

    @if($step === 'receipt' && $receipt)
    <div class="flex flex-col gap-5">
        <div class="card p-6 text-center">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--color-brand-soft); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <x-icon name="check" class="w-7 h-7" style="color: var(--color-brand);"/>
            </div>
            <div class="font-bold" style="font-size: 18px;">Transfer Complete</div>
            <div class="font-mono font-bold" style="font-size: 28px; letter-spacing: -0.02em; color: var(--color-brand); margin: 8px 0;">{{ FormatService::kmf($receipt['requestedAmount']) }}</div>
        </div>

        <div class="card-flat overflow-hidden">
            <div class="flex justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                <span style="font-size: 13px; color: var(--color-ink-mid);">Fee</span>
                <span class="font-mono" style="font-size: 13px;">{{ FormatService::kmf($receipt['feeAmount']) }}</span>
            </div>
            <div class="flex justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                <span style="font-size: 13px; color: var(--color-ink-mid);">Net received</span>
                <span class="font-mono font-semibold" style="font-size: 13px;">{{ FormatService::kmf($receipt['netAmountToDestination']) }}</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3">
                <span style="font-size: 13px; color: var(--color-ink-mid);">Transaction ID</span>
                <span class="font-mono" style="font-size: 12px; color: var(--color-ink-low);">{{ $receipt['transactionId'] }}</span>
            </div>
        </div>

        <button wire:click="resetForm" class="btn btn-primary btn-lg btn-full">Send Again</button>
        <a wire:navigate href="{{ route('merchant.dashboard') }}" class="btn btn-secondary btn-lg btn-full">Back to Dashboard</a>
    </div>
    @endif
</div>
