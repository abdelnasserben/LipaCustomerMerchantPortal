@php use App\Services\FormatService; @endphp
<div class="px-5 lg:px-8 pt-5 lg:pt-8 lg:max-w-2xl">
    <h1 class="font-bold mb-5 lg:mb-6 lg:!text-2xl" style="font-size: 21px; letter-spacing: -0.02em;">Security</h1>

    @if($success)
    <div class="alert alert-success mb-5">
        <x-icon name="check" class="w-4 h-4 flex-shrink-0"/>
        <span>{{ $success }}</span>
    </div>
    @endif

    {{-- PIN management --}}
    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
            <div class="section-title">PIN & Authentication</div>
        </div>
        <button wire:click="openPanel('changePIN')" class="flex items-center justify-between px-5 py-3 w-full text-left" style="background: none; border: none; border-bottom: 1px solid var(--color-border); cursor: pointer;">
            <div class="flex items-center gap-3">
                <x-icon name="lock" class="w-5 h-5" style="color: var(--color-ink-mid);"/>
                <div>
                    <div style="font-size: 14px; font-weight: 500;">Change PIN</div>
                    <div style="font-size: 12px; color: var(--color-ink-low);">Update your 4–8 digit auth PIN</div>
                </div>
            </div>
            <x-icon name="chev-right" class="w-4 h-4" style="color: var(--color-ink-low);"/>
        </button>
        <div class="flex items-center justify-between px-5 py-3">
            <div class="flex items-center gap-3">
                <x-icon name="shield" class="w-5 h-5" style="color: var(--color-ink-mid);"/>
                <div>
                    <div style="font-size: 14px; font-weight: 500;">Two-Factor Authentication</div>
                    <div style="font-size: 12px; color: var(--color-ink-low);">{{ $totpEnrolled ? 'Authenticator app enabled' : 'Add an authenticator app' }}</div>
                </div>
            </div>
            @if($totpEnrolled)
                <span class="pill pill-success">Enabled</span>
            @else
                <button wire:click="openPanel('enrollTOTP')" class="btn btn-secondary btn-sm">Enable</button>
            @endif
        </div>
        @if($totpEnrolled)
        <div class="px-5 pb-3">
            <button wire:click="openPanel('revokeTOTP')" class="btn btn-danger-outline btn-sm">Remove 2FA</button>
        </div>
        @endif
    </div>

    {{-- Session info --}}
    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-2" style="background: var(--color-surface-alt); border-bottom: 1px solid var(--color-border);">
            <div class="section-title">Session</div>
        </div>
        <div class="flex justify-between items-center px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
            <span style="font-size: 13px; color: var(--color-ink-mid);">Access token TTL</span>
            <span class="font-mono" style="font-size: 13px;">8 hours</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3" style="border-bottom: 1px solid var(--color-border);">
            <span style="font-size: 13px; color: var(--color-ink-mid);">Refresh endpoint</span>
            <span class="pill pill-declined">Not available</span>
        </div>
        <div class="flex justify-between items-center px-5 py-3">
            <span style="font-size: 13px; color: var(--color-ink-mid);">Server-side logout</span>
            <span class="pill pill-declined">Not available</span>
        </div>
    </div>

    {{-- Change PIN drawer --}}
    @if($activePanel === 'changePIN')
    <div class="drawer-backdrop" wire:click="openPanel('')"></div>
    <div class="drawer">
        <div class="flex items-center justify-between px-6 py-5" style="border-bottom: 1px solid var(--color-border);">
            <h2 class="font-bold" style="font-size: 18px;">Change PIN</h2>
            <button wire:click="openPanel('')" class="circle-btn"><x-icon name="x" class="w-4 h-4"/></button>
        </div>
        <div class="flex-1 overflow-y-auto px-6 py-5">
            @if($error)
            <div class="alert alert-danger mb-4"><x-icon name="warn" class="w-4 h-4 flex-shrink-0"/><span>{{ $error }}</span></div>
            @endif
            <form wire:submit="changePin" class="flex flex-col gap-5">
                <div><label class="label">Current PIN</label><input wire:model="currentPin" type="password" inputmode="numeric" placeholder="Current PIN" class="input"/></div>
                <div><label class="label">New PIN</label><input wire:model="newPin" type="password" inputmode="numeric" placeholder="4–8 digits" class="input"/></div>
                <div><label class="label">Confirm New PIN</label><input wire:model="confirmPin" type="password" inputmode="numeric" placeholder="Repeat" class="input"/></div>
                <button type="submit" class="btn btn-primary btn-lg btn-full">Update PIN</button>
                <button type="button" wire:click="openPanel('')" class="btn btn-ghost btn-md btn-full" style="color: var(--color-ink-mid);">Cancel</button>
            </form>
        </div>
    </div>
    @endif

    {{-- Enroll TOTP drawer --}}
    @if($activePanel === 'enrollTOTP')
    <div class="drawer-backdrop" wire:click="openPanel('')"></div>
    <div class="drawer">
        <div class="flex items-center justify-between px-6 py-5" style="border-bottom: 1px solid var(--color-border);">
            <h2 class="font-bold" style="font-size: 18px;">Enable 2FA</h2>
            <button wire:click="openPanel('')" class="circle-btn"><x-icon name="x" class="w-4 h-4"/></button>
        </div>
        <div class="flex-1 overflow-y-auto px-6 py-5">
            <p style="font-size: 14px; color: var(--color-ink-mid); margin-bottom: 20px;">Scan the QR code with your authenticator app, then enter the 6-digit code.</p>
            <div class="mb-5 flex justify-center">
                @if($totpQrSvg)
                <div style="width: 200px; height: 200px; padding: 10px; border: 1px solid var(--color-border); border-radius: 12px; background: #fff;">
                    {!! $totpQrSvg !!}
                </div>
                @else
                <div style="width: 200px; height: 200px; border: 1px solid var(--color-border); border-radius: 12px; background: var(--color-surface-alt); display: flex; align-items: center; justify-content: center;">
                    <div style="font-size: 11px; color: var(--color-ink-low);">Loading QR…</div>
                </div>
                @endif
            </div>
            <div class="card-flat p-3 mb-5">
                <div class="section-title mb-1">Manual key</div>
                <div class="font-mono text-sm" style="color: var(--color-ink-mid);">{{ $totpSecret }}</div>
            </div>
            @if($error)
            <div class="alert alert-danger mb-4"><x-icon name="warn" class="w-4 h-4 flex-shrink-0"/><span>{{ $error }}</span></div>
            @endif
            <form wire:submit="enrollTotp" class="flex flex-col gap-4">
                <div>
                    <label class="label">Verification Code</label>
                    <input wire:model="totpCode" type="text" inputmode="numeric" maxlength="6" placeholder="******" class="input" style="text-align: center; font-size: 22px; letter-spacing: 0.3em;"/>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-full">Enable 2FA</button>
                <button type="button" wire:click="openPanel('')" class="btn btn-ghost btn-md btn-full" style="color: var(--color-ink-mid);">Cancel</button>
            </form>
        </div>
    </div>
    @endif

    {{-- Revoke TOTP drawer --}}
    @if($activePanel === 'revokeTOTP')
    <div class="drawer-backdrop" wire:click="openPanel('')"></div>
    <div class="drawer">
        <div class="flex items-center justify-between px-6 py-5" style="border-bottom: 1px solid var(--color-border);">
            <h2 class="font-bold" style="font-size: 18px;">Remove 2FA</h2>
            <button wire:click="openPanel('')" class="circle-btn"><x-icon name="x" class="w-4 h-4"/></button>
        </div>
        <div class="flex-1 overflow-y-auto px-6 py-5">
            <div class="alert alert-danger mb-5">
                <x-icon name="warn" class="w-4 h-4 flex-shrink-0"/>
                <span>Disabling 2FA reduces your account security. Enter your current TOTP code to confirm.</span>
            </div>
            @if($error)
            <div class="alert alert-danger mb-4"><x-icon name="warn" class="w-4 h-4 flex-shrink-0"/><span>{{ $error }}</span></div>
            @endif
            <form wire:submit="revokeTotp" class="flex flex-col gap-4">
                <div>
                    <label class="label">Current Code</label>
                    <input wire:model="totpCode" type="text" inputmode="numeric" maxlength="6" placeholder="******" class="input" style="text-align: center; font-size: 22px; letter-spacing: 0.3em;"/>
                </div>
                <button type="submit" class="btn btn-danger btn-lg btn-full">Disable 2FA</button>
                <button type="button" wire:click="openPanel('')" class="btn btn-ghost btn-md btn-full" style="color: var(--color-ink-mid);">Cancel</button>
            </form>
        </div>
    </div>
    @endif
</div>
