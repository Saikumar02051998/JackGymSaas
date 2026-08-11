@props([
    'label' => null,
    'required' => false,
    'hint' => null,
    'icon' => null,
    'help' => null,
])

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    @if ($label)
        <label class="label" for="{{ $attributes->get('id') }}">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <x-icon :name="$icon" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
        @endif
        {{ $slot }}
    </div>

    @if ($help)
        <p class="mt-1.5 text-xs text-ink-400">{{ $help }}</p>
    @endif

    @error($attributes->get('name'))
        <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
    @enderror
</div>
