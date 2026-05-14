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
    <div class="max-w-xl mx-auto min-h-screen relative" style="padding-bottom: 80px;">
        {{ $slot }}
    </div>
    <x-customer.tab-bar/>
    @livewireScripts
</body>
</html>
