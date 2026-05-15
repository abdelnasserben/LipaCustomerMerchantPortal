@php use App\Services\FormatService; @endphp
<div>
    {{-- Header --}}
    <div class="sticky top-0 z-10 flex items-center gap-3 px-5 lg:px-8 pt-5 lg:pt-8 pb-4" style="background: var(--color-bg);">
        @if($step !== 'recipient')
        <button wire:click="$set('step', 'recipient')" class="circle-btn">
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </button>
        @endif
        <h1 class="font-bold flex-1 lg:!text-2xl" style="font-size: 19px; letter-spacing: -0.02em;">
            @if($step === 'recipient') {{ __('customer.send.title_recipient') }}
            @elseif($step === 'amount') {{ __('customer.send.title_amount') }}
            @elseif($step === 'confirm') {{ __('customer.send.title_confirm') }}
            @elseif($step === 'pin') {{ __('customer.send.title_pin') }}
            @elseif($step === 'threshold') {{ __('customer.send.title_threshold') }}
            @elseif($step === 'receipt') {{ __('customer.send.title_receipt') }}
            @endif
        </h1>
    </div>

    {{-- Progress steps --}}
    @if(!in_array($step, ['receipt', 'locked']))
    <div class="px-5 lg:px-8 lg:max-w-xl mb-5">
        @php
        $steps = ['recipient', 'amount', 'confirm', 'receipt'];
        $idx = array_search($step, $steps) ?? 0;
        @endphp
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ max(10, ($idx / (count($steps) - 1)) * 100) }}%;"></div>
        </div>
    </div>
    @endif

    <div class="px-5 lg:px-8 lg:max-w-xl">
        @if($error)
        <div class="alert alert-danger mb-4">
            <x-icon name="warn" class="w-4 h-4 flex-shrink-0 mt-0.5"/>
            <span>{{ $error }}</span>
        </div>
        @endif

        {{-- Step 1: Recipient --}}
        @if($step === 'recipient')
        <form wire:submit="proceedToAmount" class="flex flex-col gap-5">
            <div>
                <label class="label">{{ __('customer.send.recipient_phone') }}</label>
                <div style="display: flex; height: 52px; border: 1px solid var(--color-border-hi); border-radius: 12px; background: #fff; overflow: hidden;">
                    <div style="width: 80px; background: var(--color-surface-alt); display: flex; align-items: center; justify-content: center; border-right: 1px solid var(--color-border); font-family: var(--font-mono); font-weight: 600; font-size: 14px; color: var(--color-ink-mid);">
                        +269
                    </div>
                    <input wire:model="recipientPhone" type="tel" placeholder="{{ __('auth.phone_placeholder_customer') }}"
                        style="flex: 1; border: none; outline: none; padding: 0 16px; font-family: var(--font-mono); font-size: 16px; color: var(--color-ink-hi); background: transparent;"/>
                </div>
            </div>

            @if(count($beneficiaries) > 0)
            <div>
                <div class="section-title mb-3">{{ __('customer.send.recent_recipients') }}</div>
                <div class="card-flat overflow-hidden">
                    @foreach($beneficiaries as $b)
                    <button type="button" wire:click="selectBeneficiary('{{ $b['phoneNumber'] }}', '{{ $b['fullName'] }}')"
                        class="tx-row w-full text-left" style="border-bottom: 1px solid var(--color-border);">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--color-brand-soft); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; color: var(--color-brand-deep); flex-shrink: 0;">
                            {{ FormatService::initials($b['fullName']) }}
                        </div>
                        <div class="flex-1">
                            <div style="font-size: 14px; font-weight: 500;">{{ $b['fullName'] }}</div>
                            <div class="font-mono" style="font-size: 12px; color: var(--color-ink-low);">+{{ $b['phoneCountryCode'] }} {{ $b['phoneNumber'] }}</div>
                        </div>
                        <x-icon name="chev-right" class="w-4 h-4" style="color: var(--color-ink-low);"/>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            <button type="submit" class="btn btn-primary btn-lg btn-full">{{ __('common.continue') }}</button>
        </form>
        @endif

        {{-- Step 2: Amount --}}
        @if($step === 'amount')
        <form wire:submit="proceedToConfirm" class="flex flex-col gap-5">
            {{-- Recipient summary --}}
            <div class="card-flat p-4 flex items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-brand-soft); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; color: var(--color-brand-deep);">
                    {{ FormatService::initials($recipientName) }}
                </div>
                <div>
                    <div class="font-semibold" style="font-size: 15px;">{{ $recipientName }}</div>
                    <div class="font-mono text-sm" style="color: var(--color-ink-mid);">+269 {{ $recipientPhone }}</div>
                </div>
            </div>

            <div>
                <label class="label">{{ __('customer.send.amount_kmf') }}</label>
                <div style="display: flex; height: 64px; border: 1px solid var(--color-border-hi); border-radius: 12px; background: #fff; align-items: center; padding: 0 18px; gap: 8px;">
                    <input wire:model.live="amountInput" type="text" inputmode="numeric" placeholder="0"
                        style="flex: 1 1 auto; min-width: 0; border: none; outline: none; font-family: var(--font-mono); font-size: 26px; font-weight: 600; color: var(--color-ink-hi); background: transparent; letter-spacing: -0.02em;"/>
                    <span style="flex-shrink: 0; font-family: var(--font-mono); font-size: 14px; color: var(--color-ink-low); font-weight: 500;">KMF</span>
                </div>
                <div class="flex justify-between mt-2">
                    <span style="font-size: 12px; color: var(--color-ink-low);">{{ __('customer.send.min_amount') }}</span>
                    <span style="font-size: 12px; color: var(--color-ink-low);">{{ __('customer.send.available', ['amount' => FormatService::kmf($balance['availableBalance'])]) }}</span>
                </div>
            </div>

            <div>
                <label class="label">{{ __('customer.send.note_optional') }}</label>
                <input wire:model="description" type="text" placeholder="{{ __('customer.send.note_placeholder') }}" class="input" style="font-family: var(--font-sans); font-size: 15px;"/>
            </div>

            <div class="flex gap-2">
                @foreach([1000, 5000, 10000, 25000] as $preset)
                <button type="button" wire:click="$set('amountInput', '{{ $preset }}')" class="btn btn-secondary btn-sm flex-1">{{ number_format($preset/1000) }}K</button>
                @endforeach
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-full">{{ __('common.continue') }}</button>
        </form>
        @endif

        {{-- Step 3: Confirm --}}
        @if($step === 'confirm')
        <div class="flex flex-col gap-5">
            <div class="card p-5 text-center">
                <div style="font-size: 13px; color: var(--color-ink-mid); margin-bottom: 6px;">{{ __('customer.send.youre_sending') }}</div>
                <div class="font-mono font-bold" style="font-size: 36px; letter-spacing: -0.025em; color: var(--color-ink-hi);">{{ FormatService::kmf($amount) }}</div>
                <div style="font-size: 13px; color: var(--color-ink-mid); margin-top: 6px;">{{ __('customer.send.to_recipient', ['name' => $recipientName]) }}</div>
            </div>

            <div class="card-flat overflow-hidden">
                <div class="flex justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                    <span style="font-size: 13px; color: var(--color-ink-mid);">{{ __('customer.send.recipient') }}</span>
                    <span style="font-size: 13px; font-weight: 500;">{{ $recipientName }}</span>
                </div>
                <div class="flex justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                    <span style="font-size: 13px; color: var(--color-ink-mid);">{{ __('customer.send.phone') }}</span>
                    <span class="font-mono" style="font-size: 13px;">+269 {{ $recipientPhone }}</span>
                </div>
                <div class="flex justify-between px-4 py-3">
                    <span style="font-size: 13px; color: var(--color-ink-mid);">{{ __('customer.send.amount') }}</span>
                    <span class="font-mono font-semibold" style="font-size: 13px;">{{ FormatService::kmf($amount) }}</span>
                </div>
            </div>

            @if($description)
            <div class="alert alert-info text-sm">
                <x-icon name="doc" class="w-4 h-4 flex-shrink-0"/>
                <span>{{ $description }}</span>
            </div>
            @endif

            <button wire:click="submitTransfer" class="btn btn-primary btn-lg btn-full">{{ __('customer.send.confirm_send') }}</button>
            <button wire:click="$set('step', 'amount')" class="btn btn-ghost btn-md btn-full" style="color: var(--color-ink-mid);">{{ __('customer.send.edit_amount') }}</button>
        </div>
        @endif

        {{-- Step 4: PIN --}}
        @if($step === 'pin')
        <form wire:submit="submitWithPin" class="flex flex-col gap-5">
            <div class="card p-5 text-center">
                <div style="font-size: 13px; color: var(--color-ink-mid); margin-bottom: 4px;">{{ __('customer.send.confirm_sending') }}</div>
                <div class="font-mono font-bold" style="font-size: 28px; letter-spacing: -0.02em;">{{ FormatService::kmf($amount) }}</div>
            </div>
            <div class="alert alert-info">
                <x-icon name="lock" class="w-4 h-4 flex-shrink-0 mt-0.5"/>
                <span>{{ __('customer.send.pin_alert') }}</span>
            </div>
            <div>
                <label class="label">{{ __('customer.send.your_pin') }}</label>
                <input wire:model="pin" type="password" inputmode="numeric" placeholder="••••" class="input" style="font-size: 22px; letter-spacing: 0.3em; text-align: center;"/>
            </div>
            <button type="submit" class="btn btn-primary btn-lg btn-full">{{ __('customer.send.authorize') }}</button>
        </form>
        @endif

        {{-- Step 5: Threshold confirmation --}}
        @if($step === 'threshold')
        <div class="flex flex-col gap-5">
            <div class="alert alert-warn">
                <x-icon name="warn" class="w-4 h-4 flex-shrink-0 mt-0.5"/>
                <div>
                    <div class="font-semibold mb-1">{{ __('customer.send.large_heading') }}</div>
                    <div>{{ __('customer.send.large_body', ['amount' => FormatService::kmf($thresholdAmount)]) }}</div>
                </div>
            </div>
            <div class="card p-5 text-center">
                <div style="font-size: 13px; color: var(--color-ink-mid); margin-bottom: 4px;">{{ __('customer.send.transfer_amount') }}</div>
                <div class="font-mono font-bold" style="font-size: 30px; letter-spacing: -0.02em; color: var(--color-warn);">{{ FormatService::kmf($amount) }}</div>
                <div style="font-size: 13px; color: var(--color-ink-mid); margin-top: 4px;">{{ __('customer.send.to_recipient', ['name' => $recipientName]) }}</div>
            </div>
            <button wire:click="confirmThreshold" class="btn btn-primary btn-lg btn-full">{{ __('customer.send.yes_confirm') }}</button>
            <button wire:click="$set('step', 'confirm')" class="btn btn-ghost btn-md btn-full" style="color: var(--color-ink-mid);">{{ __('common.cancel') }}</button>
        </div>
        @endif

        {{-- Step 6: Receipt --}}
        @if($step === 'receipt' && $receipt)
        <div class="flex flex-col gap-5">
            <div class="card p-6 text-center">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--color-brand-soft); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <x-icon name="check" class="w-7 h-7" style="color: var(--color-brand);"/>
                </div>
                <div class="font-bold" style="font-size: 18px; margin-bottom: 4px;">{{ __('customer.send.transfer_complete') }}</div>
                <div class="font-mono font-bold" style="font-size: 28px; letter-spacing: -0.02em; color: var(--color-brand); margin: 8px 0;">{{ FormatService::kmf($receipt['requestedAmount']) }}</div>
                <div style="font-size: 13px; color: var(--color-ink-mid);">{{ __('customer.send.sent_to', ['name' => $receipt['recipientName']]) }}</div>
            </div>

            <div class="card-flat overflow-hidden">
                <div class="flex justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                    <span style="font-size: 13px; color: var(--color-ink-mid);">{{ __('customer.send.fee') }}</span>
                    <span class="font-mono" style="font-size: 13px;">{{ FormatService::kmf($receipt['feeAmount']) }}</span>
                </div>
                <div class="flex justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                    <span style="font-size: 13px; color: var(--color-ink-mid);">{{ __('customer.send.net_to_recipient') }}</span>
                    <span class="font-mono font-semibold" style="font-size: 13px;">{{ FormatService::kmf($receipt['netAmountToDestination']) }}</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span style="font-size: 13px; color: var(--color-ink-mid);">{{ __('customer.send.reference') }}</span>
                    <span class="font-mono" style="font-size: 12px; color: var(--color-ink-low);">{{ $receipt['transactionId'] }}</span>
                </div>
            </div>

            <button wire:click="resetForm" class="btn btn-primary btn-lg btn-full">{{ __('customer.send.send_another') }}</button>
            <a wire:navigate href="{{ route('customer.dashboard') }}" class="btn btn-secondary btn-lg btn-full">{{ __('customer.send.back_home') }}</a>
        </div>
        @endif
    </div>
    <div class="h-6"></div>
</div>
