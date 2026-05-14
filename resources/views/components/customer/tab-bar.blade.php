<div class="tab-bar">
    <a href="{{ route('customer.dashboard') }}" class="tab-item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
        <x-icon name="home" class="w-6 h-6"/>
        <span>Home</span>
    </a>
    <a href="{{ route('customer.transactions') }}" class="tab-item {{ request()->routeIs('customer.transactions*') ? 'active' : '' }}">
        <x-icon name="list" class="w-6 h-6"/>
        <span>Activity</span>
    </a>
    <a href="{{ route('customer.send') }}" class="tab-item" style="position: relative;">
        <span style="width: 48px; height: 48px; border-radius: 50%; background: #0c7a3e; color: #fff; display: flex; align-items: center; justify-content: center; margin-top: -16px; box-shadow: 0 6px 16px rgba(12,122,62,0.35);">
            <x-icon name="send" class="w-6 h-6"/>
        </span>
        <span>Send</span>
    </a>
    <a href="{{ route('customer.cards') }}" class="tab-item {{ request()->routeIs('customer.cards*') ? 'active' : '' }}">
        <x-icon name="card" class="w-6 h-6"/>
        <span>Cards</span>
    </a>
    <a href="{{ route('customer.profile') }}" class="tab-item {{ request()->routeIs('customer.profile') || request()->routeIs('customer.security') || request()->routeIs('customer.statement') ? 'active' : '' }}">
        <x-icon name="user" class="w-6 h-6"/>
        <span>Profile</span>
    </a>
</div>
