@php use App\Services\FormatService; @endphp
<div>
    @if($selectedCard)
    {{-- Card detail view --}}
    <div class="px-5 pt-5">
        <div class="flex items-center gap-3 mb-6">
            <button wire:click="back" class="circle-btn">
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </button>
            <h1 class="font-bold" style="font-size: 19px; letter-spacing: -0.02em;">Card Detail</h1>
        </div>

        {{-- Card visual --}}
        <div class="chip-card mb-6 {{ $selectedCard['status'] !== 'ACTIVE' ? 'chip-card-blocked' : '' }}" style="min-height: 180px;">
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: rgba(255,255,255,0.6);">{{ $selectedCard['cardType'] }}</div>
                        <x-tx-status-pill :status="$selectedCard['status']"/>
                    </div>
                    <div style="width: 32px; height: 24px; border: 2px solid rgba(255,255,255,0.5); border-radius: 4px; opacity: 0.7;"></div>
                </div>
                <div class="font-mono font-semibold" style="font-size: 18px; letter-spacing: 0.12em; color: rgba(255,255,255,0.9);">
                    •••• •••• •••• {{ $selectedCard['last4'] ?? '****' }}
                </div>
                <div class="flex justify-between mt-4">
                    <div>
                        <div style="font-size: 9px; text-transform: uppercase; color: rgba(255,255,255,0.5); letter-spacing: 0.06em; margin-bottom: 2px;">Expires</div>
                        <div class="font-mono" style="font-size: 13px; color: rgba(255,255,255,0.8);">
                            @php $exp = explode('-', $selectedCard['expiresAt']); @endphp
                            {{ $exp[1] ?? '' }}/{{ substr($exp[0] ?? '', 2) }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div style="font-size: 9px; text-transform: uppercase; color: rgba(255,255,255,0.5); letter-spacing: 0.06em; margin-bottom: 2px;">Lipa</div>
                        <div style="font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.8);">Lipa</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info rows --}}
        <div class="card overflow-hidden mb-5">
            <div class="flex justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                <span style="font-size: 13px; color: var(--color-ink-mid);">Status</span>
                <x-status-pill :status="$selectedCard['status']"/>
            </div>
            <div class="flex justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                <span style="font-size: 13px; color: var(--color-ink-mid);">Type</span>
                <span style="font-size: 13px; font-weight: 500;">{{ $selectedCard['cardType'] }}</span>
            </div>
            <div class="flex justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                <span style="font-size: 13px; color: var(--color-ink-mid);">Issued</span>
                <span style="font-size: 13px;">{{ FormatService::date($selectedCard['issuedAt']) }}</span>
            </div>
            <div class="flex justify-between px-4 py-3" style="border-bottom: 1px solid var(--color-border);">
                <span style="font-size: 13px; color: var(--color-ink-mid);">Expires</span>
                <span style="font-size: 13px;">{{ FormatService::date($selectedCard['expiresAt']) }}</span>
            </div>
            <div class="flex justify-between px-4 py-3">
                <span style="font-size: 13px; color: var(--color-ink-mid);">Last Used</span>
                <span style="font-size: 13px;">{{ isset($selectedCard['lastUsedAt']) ? FormatService::relativeTime($selectedCard['lastUsedAt']) : 'Never' }}</span>
            </div>
        </div>

        @if(in_array($selectedCard['status'], ['ACTIVE', 'BLOCKED']))
        <div class="flex flex-col gap-3">
            @if($selectedCard['status'] === 'ACTIVE')
            <button wire:click="openReportModal('lost')" class="btn btn-danger-outline btn-lg btn-full">
                Report Lost
            </button>
            <button wire:click="openReportModal('stolen')" class="btn btn-danger btn-lg btn-full">
                Report Stolen
            </button>
            @endif
            <div class="alert alert-info text-sm">
                <x-icon name="warn" class="w-4 h-4 flex-shrink-0"/>
                <span>To order a new card or request a replacement, please visit a Lipa agent.</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Report modal --}}
    @if($showReportModal)
    <div class="drawer-backdrop" wire:click="$set('showReportModal', false)"></div>
    <div style="position: fixed; bottom: 0; left: 0; right: 0; background: var(--color-surface); border-radius: 24px 24px 0 0; padding: 24px; z-index: 101; box-shadow: 0 -12px 32px rgba(0,0,0,0.15);">
        <div class="font-bold text-center mb-2" style="font-size: 18px;">Report Card {{ ucfirst($reportType) }}</div>
        <p class="text-center mb-5" style="font-size: 14px; color: var(--color-ink-mid);">
            Are you sure you want to report this card as {{ $reportType }}? The card will be blocked immediately.
        </p>
        <button wire:click="confirmReport" class="btn btn-danger btn-lg btn-full mb-3">
            Confirm — Report as {{ ucfirst($reportType) }}
        </button>
        <button wire:click="$set('showReportModal', false)" class="btn btn-secondary btn-lg btn-full">Cancel</button>
    </div>
    @endif

    @else
    {{-- Cards list --}}
    <div class="px-5 pt-5">
        <h1 class="font-bold mb-5" style="font-size: 21px; letter-spacing: -0.02em;">My Cards</h1>

        @if(empty($cards))
        <div class="empty-state">
            <x-icon name="card" class="w-10 h-10 mb-3" style="color: var(--color-border-hi);"/>
            <div style="font-size: 15px; font-weight: 600; margin-bottom: 6px;">No cards yet</div>
            <div style="font-size: 13px;">Visit a Lipa agent to get your first card.</div>
        </div>
        @else
        <div class="flex flex-col gap-4">
            @foreach($cards as $card)
            <button wire:click="selectCard('{{ $card['id'] }}')" class="text-left" style="background: none; border: none; cursor: pointer; padding: 0;">
                <div class="chip-card {{ $card['status'] !== 'ACTIVE' ? 'chip-card-blocked' : '' }}" style="min-height: 160px;">
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wider mb-1" style="color: rgba(255,255,255,0.6);">{{ $card['cardType'] }}</div>
                                <x-tx-status-pill :status="$card['status']"/>
                            </div>
                            <div style="width: 28px; height: 20px; border: 1.5px solid rgba(255,255,255,0.4); border-radius: 3px; opacity: 0.6;"></div>
                        </div>
                        <div class="font-mono" style="font-size: 17px; letter-spacing: 0.1em; color: rgba(255,255,255,0.85);">
                            •••• •••• •••• {{ $card['last4'] ?? '****' }}
                        </div>
                        <div class="flex justify-between mt-3">
                            <div class="font-mono" style="font-size: 12px; color: rgba(255,255,255,0.6);">
                                @php $exp = explode('-', $card['expiresAt']); @endphp
                                {{ $exp[1] ?? '' }}/{{ substr($exp[0] ?? '', 2) }}
                            </div>
                            <div style="font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.8);">Lipa</div>
                        </div>
                    </div>
                </div>
            </button>
            @endforeach
        </div>

        <div class="alert alert-info mt-5">
            <x-icon name="warn" class="w-4 h-4 flex-shrink-0"/>
            <span>Need a card? Visit a Lipa agent to get one issued.</span>
        </div>
        @endif
    </div>
    @endif
    <div class="h-6"></div>
</div>
