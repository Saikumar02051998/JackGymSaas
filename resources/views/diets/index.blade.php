<x-layouts.app
    title="Diet Plans"
    description="Manage client diet plans."
    :breadcrumbs="[['label' => 'Diet Plans']]">

    <x-slot name="actions">
        @if (can_manage('diets.manage'))
            <x-button href="{{ route('diets.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                New Diet Plan
            </x-button>
        @endif
    </x-slot>

    <div class="mb-6 flex flex-wrap items-center gap-2">
        <a href="{{ route('diets.index') }}" @class(['btn-ghost btn-sm', 'btn-outline' => ! request('status')])>All</a>
        <a href="{{ route('diets.index', ['status' => 'active']) }}" @class(['btn-ghost btn-sm', 'btn-outline' => request('status') === 'active'])>Active</a>
        <a href="{{ route('diets.index', ['status' => 'draft']) }}" @class(['btn-ghost btn-sm', 'btn-outline' => request('status') === 'draft'])>Draft</a>
        <a href="{{ route('diets.index', ['status' => 'completed']) }}" @class(['btn-ghost btn-sm', 'btn-outline' => request('status') === 'completed'])>Completed</a>
    </div>

    <div data-ajax-table="diets-table">
    @if ($plans->isEmpty())
        <x-card>
            <div class="p-8">
                <x-empty-state icon="utensils" title="No diet plans" message="Create diet plans to get started." @if (can_manage('diets.manage')) action="{{ route('diets.create') }}" action-label="New Diet Plan" @endif />
            </div>
        </x-card>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($plans as $diet)
                <a href="{{ route('diets.show', $diet) }}" class="card transition-colors hover:border-gold-400/60">
                    <div class="card-body">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-base font-bold text-ink-900 dark:text-white">{{ $diet->name }}</h3>
                                <p class="mt-0.5 text-xs text-ink-400">{{ $diet->client?->display_name ?? 'Deleted client' }}</p>
                            </div>
                            <x-badge :color="match($diet->status) { 'active' => 'green', 'draft' => 'gray', 'completed' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst($diet->status) }}</x-badge>
                        </div>
                        @if ($diet->goal)
                            <p class="mt-3 text-sm text-ink-500 dark:text-ink-400">{{ $diet->goal }}</p>
                        @endif
                        <div class="mt-4 flex items-center justify-between border-t border-ink-100 pt-3 text-xs text-ink-400 dark:border-ink-800">
                            <span>{{ $diet->meals()->count() }} meals</span>
                            @if ($diet->start_date)
                                <span>{{ \Carbon\Carbon::parse($diet->start_date)->format('d M') }}{{ $diet->end_date ? ' → ' . \Carbon\Carbon::parse($diet->end_date)->format('d M') : '' }}</span>
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
    </div>
</x-layouts.app>
