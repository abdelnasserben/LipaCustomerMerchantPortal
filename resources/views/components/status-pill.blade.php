@props(['status', 'size' => 'sm'])
@php
use App\Services\FormatService;
$class = FormatService::statusPillClass($status);
$label = FormatService::statusLabel($status);
$sizeClass = $size === 'lg' ? 'pill-lg' : '';
@endphp
<span class="pill {{ $class }} {{ $sizeClass }}">{{ $label }}</span>
