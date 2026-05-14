@props(['status'])
@php
$map = [
    'COMPLETED'  => ['pill-success', 'Completed'],
    'PENDING'    => ['pill-pending', 'Pending'],
    'AUTHORIZED' => ['pill-info', 'Authorized'],
    'DECLINED'   => ['pill-declined', 'Declined'],
    'EXPIRED'    => ['pill-pending', 'Expired'],
    'REVERSED'   => ['pill-warn', 'Reversed'],
];
[$class, $label] = $map[$status] ?? ['pill-neutral', $status];
@endphp
<span class="pill {{ $class }}">{{ $label }}</span>
