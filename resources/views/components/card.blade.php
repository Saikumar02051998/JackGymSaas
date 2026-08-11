@props([
    'title' => null,
    'footer' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title)
        <div class="flex items-center justify-between border-b border-ink-100 px-5 py-4 dark:border-ink-800">
            <h3 class="text-sm font-bold text-ink-900 dark:text-white">{{ $title }}</h3>
            {{ $header ?? '' }}
        </div>
    @endif
    <div @class(['card-body' => $padding])>
        {{ $slot }}
    </div>
    @if ($footer)
        <div class="border-t border-ink-100 px-5 py-3 dark:border-ink-800">
            {{ $footer }}
        </div>
    @endif
</div>
