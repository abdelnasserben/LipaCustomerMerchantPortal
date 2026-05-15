<div class="tab-bar">
    <a wire:navigate href="{{ route('merchant.dashboard') }}" class="tab-item {{ request()->routeIs('merchant.dashboard') ? 'active' : '' }}">
        <x-icon name="home" class="w-6 h-6"/>
        <span>{{ __('nav.home') }}</span>
    </a>
    <a wire:navigate href="{{ route('merchant.transactions') }}" class="tab-item {{ request()->routeIs('merchant.transactions*') ? 'active' : '' }}">
        <x-icon name="list" class="w-6 h-6"/>
        <span>{{ __('nav.sales') }}</span>
    </a>
    <a wire:navigate href="{{ route('merchant.send') }}" class="tab-item {{ request()->routeIs('merchant.send*') ? 'active' : '' }}" style="position: relative;">
        <span style="width: 48px; height: 48px; border-radius: 50%; background: #0c7a3e; color: #fff; display: flex; align-items: center; justify-content: center; margin-top: -16px; box-shadow: 0 6px 16px rgba(12,122,62,0.35);">
            <x-icon name="send" class="w-6 h-6"/>
        </span>
        <span>{{ __('nav.send') }}</span>
    </a>
    <a wire:navigate href="{{ route('merchant.statement') }}" class="tab-item {{ request()->routeIs('merchant.statement') ? 'active' : '' }}">
        <x-icon name="doc" class="w-6 h-6"/>
        <span>{{ __('nav.statement') }}</span>
    </a>
    <button type="button" id="mobile-nav-toggle" class="tab-item {{ request()->routeIs('merchant.operators*') || request()->routeIs('merchant.terminals*') || request()->routeIs('merchant.profile') || request()->routeIs('merchant.security') ? 'active' : '' }}">
        <x-icon name="list" class="w-6 h-6"/>
        <span>{{ __('nav.more') }}</span>
    </button>
</div>
