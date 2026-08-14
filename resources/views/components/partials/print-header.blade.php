@props(['title' => '', 'gym' => null])

@php
    $gym = $gym ?? current_gym();
    $logo = $gym?->logo;
    $name = $gym?->name ?: config('app.name');
@endphp

<div class="flex flex-wrap items-start justify-between gap-6 border-b-2 border-ink-200 pb-6 print-block print-avoid-break">
    <div class="flex items-start gap-4">
        @if ($logo)
            <img src="{{ asset('storage/' . $logo) }}" alt="{{ $name }}" class="size-16 rounded-xl object-cover print-block">
        @else
            <div class="flex size-16 items-center justify-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-500 text-2xl font-extrabold text-ink-950 print-block">
                {{ substr($name, 0, 1) }}
            </div>
        @endif
        <div>
            <p class="text-xl font-extrabold tracking-tight text-ink-900">{{ $name }}</p>
            @if ($gym?->address)
                <p class="mt-0.5 text-xs text-ink-500">{{ $gym->address }}</p>
            @endif
            @if ($gym?->phone || $gym?->email)
                <p class="text-xs text-ink-500">
                    {{ $gym->phone }}{{ $gym->phone && $gym->email ? ' · ' : '' }}{{ $gym->email }}
                </p>
            @endif
        </div>
    </div>
    <div class="text-right">
        <p class="text-3xl font-extrabold tracking-tight text-gold-600">{{ $title }}</p>
        {{ $slot }}
    </div>
</div>
