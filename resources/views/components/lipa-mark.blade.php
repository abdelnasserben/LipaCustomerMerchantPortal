@props(['size' => 36, 'dark' => false])
<div style="width: {{ $size }}px; height: {{ $size }}px; border-radius: {{ $size * 0.24 }}px; background: #0c7a3e; display: flex; align-items: center; justify-content: center; flex-shrink: 0; {{ $dark ? 'box-shadow: inset 0 0 0 1px rgba(255,255,255,0.06);' : '' }}">
    <svg width="{{ $size * 0.52 }}" height="{{ $size * 0.52 }}" viewBox="0 0 24 24" fill="none">
        <path d="M7 7h10v2.5a4 4 0 0 1-4 4h-2a4 4 0 0 1-4-4V7z" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
        <path d="M10 7V5.5a2 2 0 0 1 4 0V7" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
        <path d="M9.5 16.5l2 2 3.5-4" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</div>
