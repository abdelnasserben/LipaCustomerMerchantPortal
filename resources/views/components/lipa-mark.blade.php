@props(['size' => 36, 'dark' => false])
@php
    $ink = $dark ? '#faf6ec' : '#0a0a0a';
    $accent = $dark ? '#4f8a6a' : '#4f8a6a';
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 100 100" fill="none" role="img" aria-label="Lipa" style="flex-shrink: 0;">
    <circle cx="26" cy="50" r="9" fill="{{ $ink }}"/>
    <path d="M44 32 Q60 50 44 68" stroke="{{ $ink }}" stroke-width="7" stroke-linecap="round" fill="none"/>
    <path d="M58 22 Q80 50 58 78" stroke="{{ $accent }}" stroke-width="7" stroke-linecap="round" fill="none"/>
</svg>
