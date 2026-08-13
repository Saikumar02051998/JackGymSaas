<x-layouts.app
    title="{{ $workout->name }}"
    description="Workout plan details"
    :breadcrumbs="[['label' => 'Workout Plans', 'url' => route('workouts.index')], ['label' => $workout->name]]">

    <x-slot name="actions">
        @if (can_manage('workouts.manage'))
            <a href="{{ route('workouts.edit', $workout) }}" class="btn-outline btn-sm">
                <x-icon name="pencil" class="size-4" />
                Edit
            </a>
            <form method="POST" action="{{ route('workouts.toggle', $workout) }}" class="inline">
                @csrf
                <x-button type="submit" variant="outline" size="sm">
                    <x-icon name="refresh" class="size-4" />
                    {{ $workout->status === 'active' ? 'Set Draft' : 'Activate' }}
                </x-button>
            </form>
            <form method="POST" action="{{ route('workouts.destroy', $workout) }}"
                  x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Delete plan?', message: 'This will permanently delete this workout plan.', confirmText: 'Delete' } })">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="ghost" size="sm" class="!text-red-500">
                    <x-icon name="trash" class="size-4" />
                </x-button>
            </form>
        @endif
    </x-slot>

    <x-card>
        <div class="flex flex-wrap items-center gap-4">
            <span class="avatar-lg">
                <x-icon name="dumbbell" class="size-7" />
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-bold text-ink-900 dark:text-white">{{ $workout->name }}</h2>
                    <x-badge :color="match($workout->status) { 'active' => 'green', 'draft' => 'gray', 'completed' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst($workout->status) }}</x-badge>
                </div>
                <p class="mt-0.5 text-sm text-ink-400">
                    @if ($workout->client)
                        For <a href="{{ route('clients.show', $workout->client_id) }}" class="font-medium text-gold-600 hover:underline">{{ $workout->client->display_name }}</a>
                    @else
                        For Deleted client
                    @endif
                    @if ($workout->start_date)
                        &middot; {{ \Carbon\Carbon::parse($workout->start_date)->format('d M') }}{{ $workout->end_date ? ' → ' . \Carbon\Carbon::parse($workout->end_date)->format('d M Y') : '' }}
                    @endif
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-ink-400">Exercises</p>
                <p class="text-2xl font-extrabold text-ink-900 dark:text-white">{{ $workout->exercises->count() }}</p>
            </div>
        </div>
        @if ($workout->goal)
            <p class="mt-4 rounded-xl bg-gold-400/10 p-4 text-sm font-medium text-gold-700 dark:text-gold-300">
                <span class="font-bold">Goal:</span> {{ $workout->goal }}
            </p>
        @endif
    </x-card>

    @if ($workout->exercises->isEmpty())
        <x-card class="mt-6">
            <div class="p-8">
                <x-empty-state icon="dumbbell" title="No exercises" message="Add exercises to this plan." />
            </div>
        </x-card>
    @else
        @php
            $grouped = $workout->exercises->groupBy('day_of_week');
        @endphp
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            @foreach ($grouped as $day => $exercises)
                <x-card :title="$day ?: 'General'" :padding="false">
                    <div class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($exercises as $exercise)
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-ink-900 dark:text-white">{{ $exercise->exercise }}</p>
                                        <p class="mt-0.5 text-xs text-ink-400">{{ $exercise->muscle_group ?? '—' }}</p>
                                    </div>
                                    <div class="text-right text-sm">
                                        @if ($exercise->sets || $exercise->reps)
                                            <p class="font-bold text-ink-900 dark:text-white">{{ $exercise->sets ? $exercise->sets . ' × ' : '' }}{{ $exercise->reps ?? '' }}</p>
                                        @endif
                                        @if ($exercise->weight)
                                            <p class="text-xs text-ink-400">{{ $exercise->weight }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if ($exercise->duration_minutes || $exercise->rest_seconds)
                                    <p class="mt-2 text-xs text-ink-400">
                                        {{ $exercise->duration_minutes ? '⏱ ' . $exercise->duration_minutes . ' min' : '' }}{{ $exercise->duration_minutes && $exercise->rest_seconds ? ' · ' : '' }}{{ $exercise->rest_seconds ? 'Rest ' . $exercise->rest_seconds . 's' : '' }}
                                    </p>
                                @endif
                                @if ($exercise->instructions)
                                    <p class="mt-2 text-sm leading-relaxed text-ink-500 dark:text-ink-400">{{ $exercise->instructions }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif

    @if ($workout->notes)
        <x-card title="Notes" class="mt-6">
            <p class="whitespace-pre-line text-sm leading-relaxed text-ink-600 dark:text-ink-300">{{ $workout->notes }}</p>
        </x-card>
    @endif
</x-layouts.app>
