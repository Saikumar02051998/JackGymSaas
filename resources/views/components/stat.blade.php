@props([
    'label' => 'Statistic',
    'value' => '0',
    'icon' => 'chart',
    'change' => null,
    'changeLabel' => null,
    'positive' => true,
])

<div {{ $attributes->merge(['class' => 'card group relative overflow-hidden']) }}>
    <div class="absolute -right-6 -top-6 size-24 rounded-full bg-gold-400/10 transition-transform duration-300 group-hover:scale-125"></div>
    <div class="card-body relative">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400 dark:text-ink-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold tracking-tight text-ink-900 dark:text-white sm:text-3xl">{{ $value }}</p>
                @if ($change !== null)
                    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium {{ $positive ? 'text-emerald-500' : 'text-red-500' }}">
                        <x-icon :name="$positive ? 'trending-up' : 'trending-down'" class="size-3.5" />
                        {{ $change }} {{ $changeLabel }}
                    </p>
                @endif
            </div>
            <div class="flex size-10 items-center justify-center rounded-xl bg-gold-400/15 text-gold-600 dark:text-gold-400">
                <x-icon :name="$icon" class="size-5" />
            </div>
        </div>
    </div>
</div>
