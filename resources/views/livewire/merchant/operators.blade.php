@php use App\Services\FormatService; @endphp
<div class="px-5 lg:px-8 pt-5 lg:pt-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 mb-5 lg:mb-6">
        <div>
            <h1 class="font-bold lg:!text-2xl" style="font-size: 21px; letter-spacing: -0.02em;">Cashiers</h1>
            <div style="font-size: 13px; color: var(--color-ink-low); margin-top: 2px;">Manage your terminal operators</div>
        </div>
        <div class="flex gap-2 sm:gap-3 flex-wrap">
            <div class="flex gap-1 flex-1 sm:flex-none overflow-x-auto">
                <button wire:click="$set('filterStatus', '')" class="btn btn-sm {{ $filterStatus === '' ? 'btn-primary' : 'btn-secondary' }}">All</button>
                <button wire:click="$set('filterStatus', 'ACTIVE')" class="btn btn-sm {{ $filterStatus === 'ACTIVE' ? 'btn-primary' : 'btn-secondary' }}">Active</button>
                <button wire:click="$set('filterStatus', 'SUSPENDED')" class="btn btn-sm {{ $filterStatus === 'SUSPENDED' ? 'btn-primary' : 'btn-secondary' }}">Suspended</button>
            </div>
            <button wire:click="$set('showCreateDrawer', true)" class="btn btn-primary btn-sm sm:btn-md flex-shrink-0">
                <x-icon name="plus" class="w-4 h-4"/>Add
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
            <div class="font-semibold mb-1">Save this PIN — it will never be shown again</div>
            <div>Cashier <strong>{{ $createdOperatorPin['operator']['fullName'] }}</strong> has been created with PIN: <strong class="font-mono">{{ $createdOperatorPin['pin'] }}</strong>. Share it securely out-of-band.</div>
        </div>
    </div>
    @endif

    <div class="card overflow-hidden">
        <div class="hidden md:grid grid-cols-12 px-5 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
            <div class="col-span-4 section-title">Name</div>
            <div class="col-span-3 section-title">Phone</div>
            <div class="col-span-2 section-title">Status</div>
            <div class="col-span-2 section-title">Last Login</div>
            <div class="col-span-1 section-title text-right">Actions</div>
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
                <span class="md:hidden section-title">Last login</span>
                <span>{{ $op['lastLoginAt'] ? FormatService::relativeTime($op['lastLoginAt']) : 'Never' }}</span>
            </div>
            <div class="md:col-span-1 flex justify-end gap-2">
                @if($op['status'] === 'ACTIVE')
                <button wire:click="openAction('{{ $op['id'] }}', 'suspend')" class="btn btn-sm btn-secondary" title="Suspend">
                    <x-icon name="lock" class="w-3 h-3"/>
                </button>
                @elseif($op['status'] === 'SUSPENDED')
                <button wire:click="openAction('{{ $op['id'] }}', 'reactivate')" class="btn btn-sm btn-secondary" title="Reactivate">
                    <x-icon name="check" class="w-3 h-3"/>
                </button>
                @endif
                @if($op['status'] !== 'REVOKED')
                <button wire:click="openAction('{{ $op['id'] }}', 'revoke')" class="btn btn-sm btn-danger-outline" title="Revoke">
                    <x-icon name="x" class="w-3 h-3"/>
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state">
            <x-icon name="team" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
            <div style="font-size: 15px; font-weight: 600; margin-bottom: 6px;">No cashiers yet</div>
            <div style="font-size: 13px;">Add a cashier to let them log in on a terminal.</div>
        </div>
        @endforelse
    </div>

    {{-- Create drawer --}}
    @if($showCreateDrawer)
    <div class="drawer-backdrop" wire:click="$set('showCreateDrawer', false)"></div>
    <div class="drawer">
        <div class="flex items-center justify-between px-6 py-5" style="border-bottom: 1px solid var(--color-border);">
            <h2 class="font-bold" style="font-size: 18px;">Add Cashier</h2>
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
                    <div class="font-semibold mb-1">PIN cannot be retrieved</div>
                    <div>The cashier's PIN is set once. It is never stored in plaintext and cannot be read back — communicate it out-of-band immediately after creation.</div>
                </div>
            </div>

            <form wire:submit="createOperator" class="flex flex-col gap-5">
                <div>
                    <label class="label">Full Name</label>
                    <input wire:model="fullName" type="text" placeholder="Cashier full name" class="input" style="font-family: var(--font-sans); font-size: 15px;"/>
                </div>
                <div>
                    <label class="label">Phone Number</label>
                    <div style="display: flex; height: 52px; border: 1px solid var(--color-border-hi); border-radius: 12px; background: #fff; overflow: hidden;">
                        <div style="width: 80px; background: var(--color-surface-alt); display: flex; align-items: center; justify-content: center; border-right: 1px solid var(--color-border); font-family: var(--font-mono); font-weight: 600; font-size: 14px; color: var(--color-ink-mid);">
                            +269
                        </div>
                        <input wire:model="phoneNumber" type="tel" placeholder="32 XX XX XX"
                            style="flex: 1; border: none; outline: none; padding: 0 16px; font-family: var(--font-mono); font-size: 16px; color: var(--color-ink-hi); background: transparent;"/>
                    </div>
                </div>
                <div>
                    <label class="label">Terminal PIN <span style="font-size: 10px; color: var(--color-ink-low); text-transform: none; font-weight: 400; letter-spacing: normal; margin-left: 4px;">4–8 digits · used on the terminal device</span></label>
                    <input wire:model="pin" type="password" inputmode="numeric" placeholder="••••" class="input" style="font-size: 20px; letter-spacing: 0.3em;"/>
                </div>
                <div>
                    <label class="label">Confirm PIN</label>
                    <input wire:model="confirmPin" type="password" inputmode="numeric" placeholder="Repeat PIN" class="input" style="font-size: 20px; letter-spacing: 0.3em;"/>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-full">Create Cashier</button>
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
        $actionLabel = match($actionType) { 'suspend' => 'Suspend', 'reactivate' => 'Reactivate', 'revoke' => 'Revoke', default => '' };
        $btnClass = $actionType === 'revoke' ? 'btn-danger' : ($actionType === 'reactivate' ? 'btn-primary' : 'btn-dark');
        @endphp
        <div class="font-bold mb-2" style="font-size: 18px;">{{ $actionLabel }} Cashier</div>
        @if($actionType === 'revoke')
        <div class="alert alert-danger mb-4">
            <x-icon name="warn" class="w-4 h-4 flex-shrink-0"/>
            <div>
                <div class="font-semibold mb-1">This action is irreversible</div>
                <div>Revoking a cashier permanently disables their terminal access. This cannot be undone.</div>
            </div>
        </div>
        @endif
        @if($op)
        <p class="mb-5" style="font-size: 14px; color: var(--color-ink-mid);">
            Are you sure you want to <strong>{{ strtolower($actionLabel) }}</strong> cashier <strong>{{ $op['fullName'] }}</strong>?
        </p>
        @endif
        <button wire:click="confirmAction" class="btn {{ $btnClass }} btn-lg btn-full mb-3">{{ $actionLabel }}</button>
        <button wire:click="$set('showActionModal', false)" class="btn btn-secondary btn-lg btn-full">Cancel</button>
    </div>
    @endif
</div>
