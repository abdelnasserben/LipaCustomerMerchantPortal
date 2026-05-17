@php use App\Services\FormatService; @endphp
<div class="px-5 lg:px-8 pt-5 lg:pt-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 mb-5 lg:mb-6">
        <div>
            <h1 class="font-bold lg:!text-2xl" style="font-size: 21px; letter-spacing: -0.02em;">{{ __('merchant.cashiers.title') }}</h1>
            <div style="font-size: 13px; color: var(--color-ink-low); margin-top: 2px;">{{ __('merchant.cashiers.subtitle') }}</div>
        </div>
        <div class="flex gap-2 sm:gap-3 flex-wrap">
            <div class="flex gap-1 flex-1 sm:flex-none overflow-x-auto">
                <button wire:click="$set('filterStatus', '')" class="btn btn-sm {{ $filterStatus === '' ? 'btn-primary' : 'btn-secondary' }}">{{ __('merchant.cashiers.all') }}</button>
                <button wire:click="$set('filterStatus', 'ACTIVE')" class="btn btn-sm {{ $filterStatus === 'ACTIVE' ? 'btn-primary' : 'btn-secondary' }}">{{ __('merchant.cashiers.active') }}</button>
                <button wire:click="$set('filterStatus', 'SUSPENDED')" class="btn btn-sm {{ $filterStatus === 'SUSPENDED' ? 'btn-primary' : 'btn-secondary' }}">{{ __('merchant.cashiers.suspended') }}</button>
            </div>
            <button wire:click="$set('showCreateDrawer', true)" class="btn btn-primary btn-sm sm:btn-md flex-shrink-0">
                <x-icon name="plus" class="w-4 h-4"/>{{ __('merchant.cashiers.add') }}
            </button>
        </div>
    </div>

    @if($success)
    <div class="alert alert-success mb-5">
        <x-icon name="check" class="w-4 h-4 flex-shrink-0"/>
        <span>{{ $success }}</span>
    </div>
    @endif

    @if($createdOperatorPin)
    <div class="alert alert-warn mb-5">
        <x-icon name="warn" class="w-4 h-4 flex-shrink-0 mt-0.5"/>
        <div>
            <div class="font-semibold mb-1">{{ __('merchant.cashiers.pin_save_title') }}</div>
            <div>{!! __('merchant.cashiers.pin_save_body', ['name' => '<strong>'.e($createdOperatorPin['operator']['fullName']).'</strong>', 'pin' => '<strong class="font-mono">'.e($createdOperatorPin['pin']).'</strong>']) !!}</div>
        </div>
    </div>
    @endif

    <div class="card overflow-hidden">
        <div class="hidden md:grid grid-cols-12 px-5 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
            <div class="col-span-4 section-title">{{ __('merchant.cashiers.col_name') }}</div>
            <div class="col-span-3 section-title">{{ __('merchant.cashiers.col_phone') }}</div>
            <div class="col-span-2 section-title">{{ __('merchant.cashiers.col_status') }}</div>
            <div class="col-span-2 section-title">{{ __('merchant.cashiers.col_last_login') }}</div>
            <div class="col-span-1 section-title text-right">{{ __('merchant.cashiers.col_actions') }}</div>
        </div>

        @forelse($operators as $op)
        <div class="flex flex-col md:grid md:grid-cols-12 md:items-center gap-3 md:gap-0 px-4 md:px-5 py-4" style="border-bottom: 1px solid var(--color-border);">
            <div class="md:col-span-4 flex items-center gap-3 min-w-0">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--color-brand-soft); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; color: var(--color-brand-deep); flex-shrink: 0;">
                    {{ FormatService::initials($op['fullName'] ?? '?') }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate" style="font-size: 14px; font-weight: 500;">{{ $op['fullName'] }}</div>
                    <div class="font-mono truncate" style="font-size: 11px; color: var(--color-ink-low);">+{{ $op['phoneCountryCode'] }} {{ $op['phoneNumber'] }}</div>
                </div>
                <div class="md:hidden flex-shrink-0">
                    <x-status-pill :status="$op['status']"/>
                </div>
            </div>
            <div class="hidden md:block md:col-span-3 font-mono" style="font-size: 13px;">+{{ $op['phoneCountryCode'] }} {{ $op['phoneNumber'] }}</div>
            <div class="hidden md:block md:col-span-2">
                <x-status-pill :status="$op['status']"/>
            </div>
            <div class="md:col-span-2 flex justify-between md:block items-center" style="font-size: 12px; color: var(--color-ink-low);">
                <span class="md:hidden section-title">{{ __('merchant.cashiers.last_login') }}</span>
                <span>{{ $op['lastLoginAt'] ? FormatService::relativeTime($op['lastLoginAt']) : __('merchant.cashiers.never') }}</span>
            </div>
            <div class="md:col-span-1 flex justify-end gap-2">
                @if($op['status'] === 'ACTIVE')
                <button wire:click="openAction('{{ $op['id'] }}', 'suspend')" class="btn btn-sm btn-secondary" title="{{ __('merchant.cashiers.suspend') }}">
                    <x-icon name="lock" class="w-3 h-3"/>
                </button>
                @elseif($op['status'] === 'SUSPENDED')
                <button wire:click="openAction('{{ $op['id'] }}', 'reactivate')" class="btn btn-sm btn-secondary" title="{{ __('merchant.cashiers.reactivate') }}">
                    <x-icon name="check" class="w-3 h-3"/>
                </button>
                @endif
                @if($op['status'] !== 'REVOKED')
                <button wire:click="openAction('{{ $op['id'] }}', 'revoke')" class="btn btn-sm btn-danger-outline" title="{{ __('merchant.cashiers.revoke') }}">
                    <x-icon name="x" class="w-3 h-3"/>
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state">
            <x-icon name="team" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
            <div style="font-size: 15px; font-weight: 600; margin-bottom: 6px;">{{ __('merchant.cashiers.no_cashiers_title') }}</div>
            <div style="font-size: 13px;">{{ __('merchant.cashiers.no_cashiers_sub') }}</div>
        </div>
        @endforelse
    </div>

    {{-- Create drawer --}}
    @if($showCreateDrawer)
    <div class="drawer-backdrop" wire:click="$set('showCreateDrawer', false)"></div>
    <div class="drawer">
        <div class="flex items-center justify-between px-6 py-5" style="border-bottom: 1px solid var(--color-border);">
            <h2 class="font-bold" style="font-size: 18px;">{{ __('merchant.cashiers.add_title') }}</h2>
            <button wire:click="$set('showCreateDrawer', false)" class="circle-btn">
                <x-icon name="x" class="w-4 h-4"/>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5">
            @if($error)
            <div class="alert alert-danger mb-4">
                <x-icon name="warn" class="w-4 h-4 flex-shrink-0"/>
                <span>{{ $error }}</span>
            </div>
            @endif

            <div class="alert alert-warn mb-5">
                <x-icon name="warn" class="w-4 h-4 flex-shrink-0 mt-0.5"/>
                <div>
                    <div class="font-semibold mb-1">{{ __('merchant.cashiers.pin_irretrievable_title') }}</div>
                    <div>{{ __('merchant.cashiers.pin_irretrievable_body') }}</div>
                </div>
            </div>

            <form wire:submit="createOperator" class="flex flex-col gap-5">
                <div>
                    <label class="label">{{ __('merchant.cashiers.full_name') }}</label>
                    <input wire:model="fullName" type="text" placeholder="{{ __('merchant.cashiers.full_name_ph') }}" class="input" style="font-family: var(--font-sans); font-size: 15px;"/>
                </div>
                <div>
                    <label class="label">{{ __('merchant.cashiers.phone_number') }}</label>
                    <div style="display: flex; height: 52px; border: 1px solid var(--color-border-hi); border-radius: 12px; background: #fff; overflow: hidden;">
                        <div style="width: 80px; background: var(--color-surface-alt); display: flex; align-items: center; justify-content: center; border-right: 1px solid var(--color-border); font-family: var(--font-mono); font-weight: 600; font-size: 14px; color: var(--color-ink-mid);">
                            +269
                        </div>
                        <input wire:model="phoneNumber" type="tel" placeholder="XXX XX XX"
                            style="flex: 1; border: none; outline: none; padding: 0 16px; font-family: var(--font-mono); font-size: 16px; color: var(--color-ink-hi); background: transparent;"/>
                    </div>
                </div>
                <div>
                    <label class="label">{{ __('merchant.cashiers.terminal_pin') }} <span style="font-size: 10px; color: var(--color-ink-low); text-transform: none; font-weight: 400; letter-spacing: normal; margin-left: 4px;">{{ __('merchant.cashiers.terminal_pin_hint') }}</span></label>
                    <input wire:model="pin" type="password" inputmode="numeric" placeholder="****" class="input" style="font-size: 20px; letter-spacing: 0.3em;"/>
                </div>
                <div>
                    <label class="label">{{ __('merchant.cashiers.confirm_pin') }}</label>
                    <input wire:model="confirmPin" type="password" inputmode="numeric" placeholder="{{ __('merchant.cashiers.repeat_pin') }}" class="input" style="font-size: 20px; letter-spacing: 0.3em;"/>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-full">{{ __('merchant.cashiers.create_cashier') }}</button>
            </form>
        </div>
    </div>
    @endif

    {{-- Action modal --}}
    @if($showActionModal)
    <div class="drawer-backdrop" wire:click="$set('showActionModal', false)"></div>
    <div class="sheet">
        @php
        $op = collect($operators)->firstWhere('id', $actionOperatorId);
        $actionLabel = match($actionType) {
            'suspend' => __('merchant.cashiers.btn_suspend'),
            'reactivate' => __('merchant.cashiers.btn_reactivate'),
            'revoke' => __('merchant.cashiers.btn_revoke'),
            default => '',
        };
        $actionVerb = match($actionType) {
            'suspend' => __('merchant.cashiers.action_suspend_label'),
            'reactivate' => __('merchant.cashiers.action_reactivate_label'),
            'revoke' => __('merchant.cashiers.action_revoke_label'),
            default => '',
        };
        $btnClass = $actionType === 'revoke' ? 'btn-danger' : ($actionType === 'reactivate' ? 'btn-primary' : 'btn-dark');
        @endphp
        <div class="font-bold mb-2" style="font-size: 18px;">{{ $actionLabel }}</div>
        @if($actionType === 'revoke')
        <div class="alert alert-danger mb-4">
            <x-icon name="warn" class="w-4 h-4 flex-shrink-0"/>
            <div>
                <div class="font-semibold mb-1">{{ __('merchant.cashiers.irreversible_title') }}</div>
                <div>{{ __('merchant.cashiers.irreversible_body') }}</div>
            </div>
        </div>
        @endif
        @if($op)
        <p class="mb-5" style="font-size: 14px; color: var(--color-ink-mid);">
            {!! __('merchant.cashiers.confirm_action', ['action' => e($actionVerb), 'name' => e($op['fullName'])]) !!}
        </p>
        @endif
        <button wire:click="confirmAction" class="btn {{ $btnClass }} btn-lg btn-full mb-3">{{ $actionLabel }}</button>
        <button wire:click="$set('showActionModal', false)" class="btn btn-secondary btn-lg btn-full">{{ __('common.cancel') }}</button>
    </div>
    @endif
</div>
