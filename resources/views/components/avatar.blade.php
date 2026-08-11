@props(['user' => null, 'name' => null, 'size' => 'size-10', 'class' => ''])

@php
$user = $user ?? auth()->user();
$name = $name ?? $user?->name ?? '';
$initials = collect(explode(' ', $name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
@endphp

<div {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-gold-400/20 text-sm font-bold text-gold-700 dark:text-gold-400 $size $class"]) }}>
    @if ($user && $user->avatar)
        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $name }}" class="size-full object-cover">
    @else
        {{ $initials }}
    @endif
</div>
