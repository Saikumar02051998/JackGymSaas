@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
    'size' => null,
    'loading' => false,
    'loadingText' => 'Loading...',
])

@php
$classes = match ($variant) {
    'primary' => 'btn-primary',
    'dark' => 'btn-dark',
    'outline' => 'btn-outline',
    'ghost' => 'btn-ghost',
    'danger' => 'btn-danger',
    'success' => 'btn-success',
    default => 'btn-primary',
};

if ($size === 'sm') {
    $classes .= ' btn-sm';
} elseif ($size === 'lg') {
    $classes .= ' btn-lg';
}
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}
        @if ($type === 'submit')
            x-data="{ submitting: false }"
            x-on:submit.window="if (! $event.defaultPrevented && $event.target === $el.form) submitting = true"
            :disabled="submitting"
        @endif>
        @if ($type === 'submit')
            <span x-show="!submitting" class="inline-flex items-center gap-2">{{ $slot }}</span>
            <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ $loadingText }}
            </span>
        @else
            @if ($loading)
                <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            @endif
            {{ $slot }}
        @endif
    </button>
@endif
