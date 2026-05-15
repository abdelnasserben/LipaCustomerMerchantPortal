@props(['status', 'size' => 'sm'])
@php
$classMap = [
    'COMPLETED'  => 'pill-success',
    'PENDING'    => 'pill-pending',
    'AUTHORIZED' => 'pill-info',
    'DECLINED'   => 'pill-declined',
    'EXPIRED'    => 'pill-pending',
    'REVERSED'   => 'pill-warn',
];
$class = $classMap[$status] ?? 'pill-neutral';
$key = "status.{$status}";
$translated = __($key);
$label = $translated === $key ? $status : $translated;
$sizeClass = $size === 'lg' ? 'pill-lg' : '';
@endphp
<span class="pill {{ $class }} {{ $sizeClass }}">{{ $label }}</span>
