<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Lipa · Customer' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="background: var(--color-bg); min-height: 100vh;">
    <div class="lg:flex min-h-screen">
        {{-- Desktop sidebar (lg+) --}}
        <aside class="hidden lg:flex w-60 flex-shrink-0 flex-col"
               style="background: #0a0a0a; position: fixed; top: 0; left: 0; bottom: 0; z-index: 40; overflow-y: auto;">
            <div class="grid-bg"></div>
            <div class="relative flex flex-col h-full p-4">
                <div class="flex items-center gap-3 px-3 py-4 mb-4">
                    <x-lipa-mark size="36"/>
                    <div>
                        <div class="font-bold text-white" style="font-size: 17px; letter-spacing: -0.01em;">Lipa</div>
                        <div class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.45); margin-top: 1px;">Customer</div>
                    </div>
                </div>

                <nav class="flex flex-col gap-1 flex-1">
                    <a wire:navigate href="{{ route('customer.dashboard') }}" class="nav-item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                        <x-icon name="home" class="w-5 h-5"/><span>Home</span>
                    </a>
                    <a wire:navigate href="{{ route('customer.transactions') }}" class="nav-item {{ request()->routeIs('customer.transactions*') ? 'active' : '' }}">
                        <x-icon name="list" class="w-5 h-5"/><span>Activity</span>
                    </a>
                    <a wire:navigate href="{{ route('customer.send') }}" class="nav-item {{ request()->routeIs('customer.send*') ? 'active' : '' }}">
                        <x-icon name="send" class="w-5 h-5"/><span>Send Money</span>
                    </a>
                    <a wire:navigate href="{{ route('customer.cards') }}" class="nav-item {{ request()->routeIs('customer.cards*') ? 'active' : '' }}">
                        <x-icon name="card" class="w-5 h-5"/><span>Cards</span>
                    </a>
                    <a wire:navigate href="{{ route('customer.statement') }}" class="nav-item {{ request()->routeIs('customer.statement') ? 'active' : '' }}">
                        <x-icon name="doc" class="w-5 h-5"/><span>Statement</span>
                    </a>
                    <div class="mt-auto">
                        <a wire:navigate href="{{ route('customer.profile') }}" class="nav-item {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
                            <x-icon name="user" class="w-5 h-5"/><span>Profile</span>
                        </a>
                        <a wire:navigate href="{{ route('customer.security') }}" class="nav-item {{ request()->routeIs('customer.security') ? 'active' : '' }}">
                            <x-icon name="shield" class="w-5 h-5"/><span>Security</span>
                        </a>
                    </div>
                </nav>
            </div>
        </aside>

        {{-- Main content. Mobile = single column with bottom-tab spacing; lg = offset right of sidebar. --}}
        <main class="flex-1 lg:ml-60 min-h-screen">
            <div class="mx-auto w-full max-w-xl lg:max-w-5xl relative" style="padding-bottom: 80px;">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- Bottom tab bar (mobile/tablet only) --}}
    <div class="lg:hidden">
        <x-customer.tab-bar/>
    </div>

    @livewireScripts
</body>
</html>
