@props(['color' => 'gray'])

@php
$classes = match ($color) {
    'green' => 'badge-green',
    'red' => 'badge-red',
    'amber' => 'badge-amber',
    'blue' => 'badge-blue',
    'gold' => 'badge-gold',
    'purple' => 'badge-purple',
    default => 'badge-gray',
};
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
