<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? __('common.brand').' · '.__('nav.merchant_portal') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="background: var(--color-bg); min-height: 100vh;">
    <div class="flex flex-col lg:flex-row min-h-screen">
        {{-- Desktop sidebar --}}
        <aside id="merchant-sidebar"
            class="w-60 flex-shrink-0 flex-col hidden lg:flex"
            style="background: #0a0a0a; position: fixed; top: 0; left: 0; bottom: 0; z-index: 40; overflow-y: auto;">
            <div class="grid-bg"></div>
            <div class="relative flex flex-col h-full p-4">
                <div class="flex items-center gap-3 px-3 py-4 mb-4">
                    <x-lipa-mark size="36"/>
                    <div>
                        <div class="font-bold text-white" style="font-size: 17px; letter-spacing: -0.01em;">{{ __('common.brand') }}</div>
                        <div class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.45); margin-top: 1px;">{{ __('nav.merchant_portal') }}</div>
                    </div>
                </div>

                <nav class="flex flex-col gap-1 flex-1">
                    <a wire:navigate href="{{ route('merchant.dashboard') }}" class="nav-item {{ request()->routeIs('merchant.dashboard') ? 'active' : '' }}">
                        <x-icon name="home" class="w-5 h-5"/><span>{{ __('nav.dashboard') }}</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.transactions') }}" class="nav-item {{ request()->routeIs('merchant.transactions*') ? 'active' : '' }}">
                        <x-icon name="list" class="w-5 h-5"/><span>{{ __('nav.transactions') }}</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.statement') }}" class="nav-item {{ request()->routeIs('merchant.statement') ? 'active' : '' }}">
                        <x-icon name="doc" class="w-5 h-5"/><span>{{ __('nav.statement') }}</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.send') }}" class="nav-item {{ request()->routeIs('merchant.send*') ? 'active' : '' }}">
                        <x-icon name="send" class="w-5 h-5"/><span>{{ __('nav.send_money') }}</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.operators') }}" class="nav-item {{ request()->routeIs('merchant.operators*') ? 'active' : '' }}">
                        <x-icon name="team" class="w-5 h-5"/><span>{{ __('nav.cashiers') }}</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.terminals') }}" class="nav-item {{ request()->routeIs('merchant.terminals*') ? 'active' : '' }}">
                        <x-icon name="device" class="w-5 h-5"/><span>{{ __('nav.terminals') }}</span>
                    </a>
                    <div class="mt-auto">
                        <a wire:navigate href="{{ route('merchant.profile') }}" class="nav-item {{ request()->routeIs('merchant.profile') ? 'active' : '' }}">
                            <x-icon name="user" class="w-5 h-5"/><span>{{ __('nav.profile') }}</span>
                        </a>
                        <a wire:navigate href="{{ route('merchant.security') }}" class="nav-item {{ request()->routeIs('merchant.security') ? 'active' : '' }}">
                            <x-icon name="shield" class="w-5 h-5"/><span>{{ __('nav.security') }}</span>
                        </a>
                    </div>
                </nav>
            </div>
        </aside>

        {{-- "More" drawer for overflow nav on mobile --}}
        <div id="mobile-nav" class="lg:hidden hidden fixed inset-0 z-[60]" style="background: rgba(0,0,0,0.5);">
            <div class="w-72 h-full" style="background: #0a0a0a; overflow-y: auto; padding: 16px;">
                <div class="flex justify-between items-center mb-6 px-3">
                    <div class="font-bold text-white" style="font-size: 16px;">{{ __('nav.more') }}</div>
                    <button id="mobile-nav-close" class="text-white">
                        <x-icon name="x" class="w-5 h-5"/>
                    </button>
                </div>
                <nav class="flex flex-col gap-1">
                    <a wire:navigate href="{{ route('merchant.operators') }}" class="nav-item {{ request()->routeIs('merchant.operators*') ? 'active' : '' }}" data-mobile-nav-link>
                        <x-icon name="team" class="w-5 h-5"/><span>{{ __('nav.cashiers') }}</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.terminals') }}" class="nav-item {{ request()->routeIs('merchant.terminals*') ? 'active' : '' }}" data-mobile-nav-link>
                        <x-icon name="device" class="w-5 h-5"/><span>{{ __('nav.terminals') }}</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.profile') }}" class="nav-item {{ request()->routeIs('merchant.profile') ? 'active' : '' }}" data-mobile-nav-link>
                        <x-icon name="user" class="w-5 h-5"/><span>{{ __('nav.profile') }}</span>
                    </a>
                    <a wire:navigate href="{{ route('merchant.security') }}" class="nav-item {{ request()->routeIs('merchant.security') ? 'active' : '' }}" data-mobile-nav-link>
                        <x-icon name="shield" class="w-5 h-5"/><span>{{ __('nav.security') }}</span>
                    </a>
                </nav>
            </div>
        </div>

        {{-- Main content --}}
        <main class="flex-1 lg:ml-60 min-h-screen">
            <div class="mx-auto w-full max-w-xl lg:max-w-6xl relative" style="padding-bottom: 80px;">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- Bottom tab bar (mobile/tablet only) --}}
    <div class="lg:hidden">
        <x-merchant.tab-bar/>
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
