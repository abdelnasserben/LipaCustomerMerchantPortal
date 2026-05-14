<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Lipa · Merchant Portal' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="background: var(--color-bg); min-height: 100vh;">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside id="merchant-sidebar"
            class="w-60 flex-shrink-0 flex-col hidden lg:flex"
            style="background: #0a0a0a; position: fixed; top: 0; left: 0; bottom: 0; z-index: 40; overflow-y: auto;">
            <div class="grid-bg"></div>
            <div class="relative flex flex-col h-full p-4">
                {{-- Logo --}}
                <div class="flex items-center gap-3 px-3 py-4 mb-4">
                    <x-lipa-mark size="36"/>
                    <div>
                        <div class="font-bold text-white" style="font-size: 17px; letter-spacing: -0.01em;">Lipa</div>
                        <div class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.45); margin-top: 1px;">Merchant Portal</div>
                    </div>
                </div>

                {{-- Nav --}}
                <nav class="flex flex-col gap-1 flex-1">
                    <a wire:navigate href="{{ route('merchant.dashboard') }}" class="nav-item {{ request()->routeIs('merchant.dashboard') ? 'active' : '' }}">
                        <x-icon name="home" class="w-5 h-5"/>
                        <span>Dashboard</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.transactions') }}" class="nav-item {{ request()->routeIs('merchant.transactions*') ? 'active' : '' }}">
                        <x-icon name="list" class="w-5 h-5"/>
                        <span>Transactions</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.statement') }}" class="nav-item {{ request()->routeIs('merchant.statement') ? 'active' : '' }}">
                        <x-icon name="doc" class="w-5 h-5"/>
                        <span>Statement</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.send') }}" class="nav-item {{ request()->routeIs('merchant.send*') ? 'active' : '' }}">
                        <x-icon name="send" class="w-5 h-5"/>
                        <span>Send Money</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.operators') }}" class="nav-item {{ request()->routeIs('merchant.operators*') ? 'active' : '' }}">
                        <x-icon name="team" class="w-5 h-5"/>
                        <span>Cashiers</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.terminals') }}" class="nav-item {{ request()->routeIs('merchant.terminals*') ? 'active' : '' }}">
                        <x-icon name="device" class="w-5 h-5"/>
                        <span>Terminals</span>
                    </a>
                    <div class="mt-auto">
                        <a wire:navigate href="{{ route('merchant.profile') }}" class="nav-item {{ request()->routeIs('merchant.profile') ? 'active' : '' }}">
                            <x-icon name="user" class="w-5 h-5"/>
                            <span>Profile</span>
                        </a>
                        <a wire:navigate href="{{ route('merchant.security') }}" class="nav-item {{ request()->routeIs('merchant.security') ? 'active' : '' }}">
                            <x-icon name="shield" class="w-5 h-5"/>
                            <span>Security</span>
                        </a>
                    </div>
                </nav>

                {{-- Footer --}}
                <div class="px-3 pt-4 mt-auto" style="border-top: 1px solid rgba(255,255,255,0.08);">
                    <div class="text-xs" style="color: rgba(255,255,255,0.35); line-height: 1.5;">
                        <div class="font-semibold text-white opacity-60" style="font-size: 13px;">Boutique Karthala</div>
                        <div>M-2041 · Moroni</div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Mobile top bar --}}
        <div class="lg:hidden flex items-center justify-between px-4 py-3 sticky top-0 z-30"
             style="background: #0a0a0a; border-bottom: 1px solid rgba(255,255,255,0.08);">
            <div class="flex items-center gap-3">
                <x-lipa-mark size="30"/>
                <span class="font-bold text-white" style="font-size: 15px;">Lipa Merchant</span>
            </div>
            <button id="mobile-nav-toggle" class="text-white p-2">
                <x-icon name="list" class="w-5 h-5"/>
            </button>
        </div>

        {{-- Mobile nav dropdown --}}
        <div id="mobile-nav" class="lg:hidden hidden fixed inset-0 z-50" style="background: rgba(0,0,0,0.5);">
            <div class="w-72 h-full" style="background: #0a0a0a; overflow-y: auto; padding: 16px;">
                <div class="flex justify-between items-center mb-6 px-3">
                    <div class="font-bold text-white" style="font-size: 16px;">Navigation</div>
                    <button id="mobile-nav-close" class="text-white">
                        <x-icon name="x" class="w-5 h-5"/>
                    </button>
                </div>
                <nav class="flex flex-col gap-1">
                    <a wire:navigate href="{{ route('merchant.dashboard') }}" class="nav-item {{ request()->routeIs('merchant.dashboard') ? 'active' : '' }}" data-mobile-nav-link>Dashboard</a>
                    <a wire:navigate href="{{ route('merchant.transactions') }}" class="nav-item {{ request()->routeIs('merchant.transactions*') ? 'active' : '' }}" data-mobile-nav-link>Transactions</a>
                    <a wire:navigate href="{{ route('merchant.statement') }}" class="nav-item {{ request()->routeIs('merchant.statement') ? 'active' : '' }}" data-mobile-nav-link>Statement</a>
                    <a wire:navigate href="{{ route('merchant.send') }}" class="nav-item {{ request()->routeIs('merchant.send*') ? 'active' : '' }}" data-mobile-nav-link>Send Money</a>
                    <a wire:navigate href="{{ route('merchant.operators') }}" class="nav-item {{ request()->routeIs('merchant.operators*') ? 'active' : '' }}" data-mobile-nav-link>Cashiers</a>
                    <a wire:navigate href="{{ route('merchant.terminals') }}" class="nav-item {{ request()->routeIs('merchant.terminals*') ? 'active' : '' }}" data-mobile-nav-link>Terminals</a>
                    <a wire:navigate href="{{ route('merchant.profile') }}" class="nav-item {{ request()->routeIs('merchant.profile') ? 'active' : '' }}" data-mobile-nav-link>Profile</a>
                    <a wire:navigate href="{{ route('merchant.security') }}" class="nav-item {{ request()->routeIs('merchant.security') ? 'active' : '' }}" data-mobile-nav-link>Security</a>
                </nav>
            </div>
        </div>

        {{-- Main content --}}
        <main class="flex-1 lg:ml-60 min-h-screen">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
    <script data-navigate-once>
        (() => {
            const closeMobileNav = () => document.getElementById('mobile-nav')?.classList.add('hidden');

            const initMerchantMobileNav = () => {
                const toggle = document.getElementById('mobile-nav-toggle');
                const nav = document.getElementById('mobile-nav');
                const close = document.getElementById('mobile-nav-close');

                if (toggle && !toggle.dataset.bound) {
                    toggle.addEventListener('click', () => nav?.classList.toggle('hidden'));
                    toggle.dataset.bound = 'true';
                }

                if (close && !close.dataset.bound) {
                    close.addEventListener('click', closeMobileNav);
                    close.dataset.bound = 'true';
                }

                if (nav && !nav.dataset.bound) {
                    nav.addEventListener('click', e => {
                        if (e.target === nav) closeMobileNav();
                    });
                    nav.dataset.bound = 'true';
                }

                document.querySelectorAll('[data-mobile-nav-link]').forEach(link => {
                    if (link.dataset.bound) return;
                    link.addEventListener('click', closeMobileNav);
                    link.dataset.bound = 'true';
                });
            };

            initMerchantMobileNav();
            document.addEventListener('livewire:navigated', initMerchantMobileNav);
            document.addEventListener('livewire:navigating', closeMobileNav);
        })();
    </script>
</body>
</html>
