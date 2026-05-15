@php use App\Services\FormatService; @endphp
<div class="px-5 lg:px-8 pt-5 lg:pt-8">
    @if($selectedTerminal)
    {{-- Terminal detail --}}
    <div class="lg:max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <button wire:click="back" class="circle-btn">
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </button>
            <h1 class="font-bold lg:!text-2xl" style="font-size: 19px; letter-spacing: -0.02em;">Terminal Detail</h1>
        </div>

        <div class="card overflow-hidden mb-5">
            <div class="flex items-center gap-3 px-4 md:px-5 py-4" style="border-bottom: 1px solid var(--color-border);">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--color-surface-alt); border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <x-icon name="device" class="w-6 h-6" style="color: var(--color-ink-mid);"/>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="font-bold truncate" style="font-size: 16px;">{{ $selectedTerminal['deviceModel'] }}</div>
                    <div class="font-mono truncate" style="font-size: 13px; color: var(--color-ink-low);">{{ $selectedTerminal['serialNumber'] }}</div>
                </div>
                <div class="flex-shrink-0">
                    <x-status-pill :status="$selectedTerminal['status']" size="lg"/>
                </div>
            </div>

            @php
            $rows = [
                ['Serial Number', $selectedTerminal['serialNumber']],
                ['Alias', $selectedTerminal['operatorAlias']],
                ['Model', $selectedTerminal['deviceModel'] ?? '—'],
                ['Android Version', $selectedTerminal['androidVersion'] ?? '—'],
                ['App Version', $selectedTerminal['appVersion'] ?? '—'],
                ['Status', null, $selectedTerminal['status']],
                ['Last Seen', FormatService::relativeTime($selectedTerminal['lastSeenAt'])],
                ['Registered', FormatService::date($selectedTerminal['registeredAt'])],
            ];
            @endphp
            @foreach($rows as $row)
            <div class="flex justify-between items-center gap-3 px-4 md:px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
                <span class="flex-shrink-0" style="font-size: 13px; color: var(--color-ink-mid);">{{ $row[0] }}</span>
                @if(isset($row[2]))
                    <x-status-pill :status="$row[2]"/>
                @else
                    <span class="font-mono truncate text-right" style="font-size: 13px;">{{ $row[1] }}</span>
                @endif
            </div>
            @endforeach
        </div>

        <div class="alert alert-info">
            <x-icon name="warn" class="w-4 h-4 flex-shrink-0"/>
            <div>
                <div class="font-semibold mb-1">Read-only view</div>
                <div>Terminal provisioning, suspension, and revocation is managed by the Lipa operations team. Contact support to make changes.</div>
            </div>
        </div>
    </div>
    @else
    {{-- Terminals list --}}
    <div class="mb-5 lg:mb-6">
        <h1 class="font-bold lg:!text-2xl" style="font-size: 21px; letter-spacing: -0.02em;">Terminals</h1>
        <div style="font-size: 13px; color: var(--color-ink-low); margin-top: 2px;">Read-only — provisioned by Lipa operations</div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($terminals as $t)
        <button wire:click="selectTerminal('{{ $t['id'] }}')" class="card p-5 text-left cursor-pointer hover:shadow-lg transition-shadow" style="background: var(--color-surface); border: none; font-family: inherit;">
            <div class="flex items-start justify-between mb-4">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--color-surface-alt); border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center;">
                    <x-icon name="device" class="w-5 h-5" style="color: var(--color-ink-mid);"/>
                </div>
                <x-status-pill :status="$t['status']"/>
            </div>
            <div class="font-bold mb-1" style="font-size: 15px;">{{ $t['operatorAlias'] }}</div>
            <div style="font-size: 13px; color: var(--color-ink-mid);">{{ $t['deviceModel'] }}</div>
            <div class="font-mono" style="font-size: 11px; color: var(--color-ink-low); margin-top: 2px;">{{ $t['serialNumber'] }}</div>
            <div class="flex items-center gap-2 mt-4 pt-4" style="border-top: 1px solid var(--color-border);">
                <div style="width: 7px; height: 7px; border-radius: 50%; background: {{ $t['status'] === 'ACTIVE' ? 'var(--color-brand)' : 'var(--color-ink-low)' }};"></div>
                <span style="font-size: 12px; color: var(--color-ink-low);">{{ FormatService::relativeTime($t['lastSeenAt']) }}</span>
            </div>
        </button>
        @endforeach
    </div>

    <div class="alert alert-info mt-5 max-w-xl">
        <x-icon name="warn" class="w-4 h-4 flex-shrink-0"/>
        <div>To provision a new terminal or modify an existing one, contact the Lipa operations team. Portal view is read-only.</div>
    </div>
    @endif
</div>
