<x-layouts.app
    title="Workout Plans"
    description="Manage client workout plans."
    :breadcrumbs="[['label' => 'Workout Plans']]">

    <x-slot name="actions">
        @if (can_manage('workouts.manage'))
            <x-button href="{{ route('workouts.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                New Workout Plan
            </x-button>
        @endif
    </x-slot>

    <div class="mb-6 flex flex-wrap items-center gap-2">
        <a href="{{ route('workouts.index') }}" @class(['btn-ghost btn-sm', 'btn-outline' => ! request('status')])>All</a>
        <a href="{{ route('workouts.index', ['status' => 'active']) }}" @class(['btn-ghost btn-sm', 'btn-outline' => request('status') === 'active'])>Active</a>
        <a href="{{ route('workouts.index', ['status' => 'draft']) }}" @class(['btn-ghost btn-sm', 'btn-outline' => request('status') === 'draft'])>Draft</a>
        <a href="{{ route('workouts.index', ['status' => 'completed']) }}" @class(['btn-ghost btn-sm', 'btn-outline' => request('status') === 'completed'])>Completed</a>
    </div>

    @if ($plans->isEmpty())
        <x-card>
            <div class="p-8">
                <x-empty-state icon="dumbbell" title="No workout plans" message="Create workout plans to get started." @if (can_manage('workouts.manage')) action="{{ route('workouts.create') }}" action-label="New Workout Plan" @endif />
            </div>
        </x-card>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($plans as $plan)
                <a href="{{ route('workouts.show', $plan) }}" class="card transition-colors hover:border-gold-400/60">
                    <div class="card-body">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-base font-bold text-ink-900 dark:text-white">{{ $plan->name }}</h3>
                                <p class="mt-0.5 text-xs text-ink-400">{{ $plan->client?->display_name ?? 'Deleted client' }}</p>
                            </div>
                            <x-badge :color="match($plan->status) { 'active' => 'green', 'draft' => 'gray', 'completed' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst($plan->status) }}</x-badge>
                        </div>
                        @if ($plan->goal)
                            <p class="mt-3 text-sm text-ink-500 dark:text-ink-400">{{ $plan->goal }}</p>
                        @endif
                        <div class="mt-4 flex items-center justify-between border-t border-ink-100 pt-3 text-xs text-ink-400 dark:border-ink-800">
                            <span>{{ $plan->exercises()->count() }} exercises</span>
                            @if ($plan->start_date)
                                <span>{{ \Carbon\Carbon::parse($plan->start_date)->format('d M') }}{{ $plan->end_date ? ' → ' . \Carbon\Carbon::parse($plan->end_date)->format('d M') : '' }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">
            <x-pagination :model="$plans" />
        </div>
    @endif
</x-layouts.app>
