<x-layouts.app
    title="My Workout Plans"
    description="Your assigned workout routines and exercises."
    :breadcrumbs="[['label' => 'My Workout']]">

    @forelse ($plans as $plan)
        <x-card class="mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-gold-400/15 text-gold-600 dark:text-gold-400">
                        <x-icon name="dumbbell" class="size-6" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-ink-900 dark:text-white">{{ $plan->name }}</h2>
                            <x-badge :color="match($plan->status) { 'active' => 'green', 'draft' => 'gray', 'completed' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst($plan->status) }}</x-badge>
                        </div>
                        <p class="text-sm text-ink-400">
                            {{ $plan->goal ?? 'General fitness' }}
                            @if ($plan->start_date || $plan->end_date)
                                · {{ $plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->format('d M Y') : '—' }} &rarr; {{ $plan->end_date ? \Carbon\Carbon::parse($plan->end_date)->format('d M Y') : 'Open' }}
                            @endif
                        </p>
                    </div>
                </div>
                @if ($plan->trainer)
                    <div class="text-right">
                        <p class="text-xs text-ink-400">Assigned by</p>
                        <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ $plan->trainer->display_name }}</p>
                    </div>
                @endif
            </div>

            @if ($plan->notes)
                <p class="mt-4 rounded-xl bg-ink-100/60 px-4 py-3 text-sm text-ink-600 dark:bg-ink-800/60 dark:text-ink-300">{{ $plan->notes }}</p>
            @endif

            @if ($plan->exercises->isEmpty())
                <div class="mt-4 rounded-2xl border border-dashed border-ink-300 px-4 py-8 text-center text-sm text-ink-400 dark:border-ink-700">
                    No exercises added to this plan yet.
                </div>
            @else
                <div class="mt-4 overflow-x-auto rounded-xl border border-ink-100 dark:border-ink-800">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 bg-ink-50/60 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800 dark:bg-ink-800/40">
                                <th class="px-4 py-2.5 font-semibold">Day</th>
                                <th class="px-4 py-2.5 font-semibold">Exercise</th>
                                <th class="px-4 py-2.5 font-semibold">Muscle</th>
                                <th class="px-4 py-2.5 font-semibold">Sets</th>
                                <th class="px-4 py-2.5 font-semibold">Reps</th>
                                <th class="px-4 py-2.5 font-semibold">Weight</th>
                                <th class="px-4 py-2.5 font-semibold">Rest</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($plan->exercises as $exercise)
                                <tr>
                                    <td class="px-4 py-2.5 font-semibold text-ink-900 dark:text-white">{{ ucfirst($exercise->day_of_week ?? '—') }}</td>
                                    <td class="px-4 py-2.5">
                                        <p class="font-medium text-ink-900 dark:text-white">{{ $exercise->exercise }}</p>
                                        @if ($exercise->instructions)
                                            <p class="!whitespace-normal text-xs text-ink-400">{{ $exercise->instructions }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-ink-600 dark:text-ink-300">{{ ucfirst($exercise->muscle_group ?? '—') }}</td>
                                    <td class="px-4 py-2.5">{{ $exercise->sets ?? '—' }}</td>
                                    <td class="px-4 py-2.5">{{ $exercise->reps ?? '—' }}</td>
                                    <td class="px-4 py-2.5">{{ $exercise->weight ? $exercise->weight . ' kg' : '—' }}</td>
                                    <td class="px-4 py-2.5">{{ $exercise->rest_seconds ? $exercise->rest_seconds . 's' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    @empty
        <x-empty-state icon="dumbbell" title="No workout plans yet" message="Your trainer will assign workout plans that appear here." />
    @endforelse
</x-layouts.app>
