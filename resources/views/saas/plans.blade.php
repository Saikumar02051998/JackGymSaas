<x-layouts.app
    title="Subscription Plans"
    description="Define the SaaS subscription plans offered to gyms."
    :breadcrumbs="[['label' => 'SaaS', 'url' => route('saas.dashboard')], ['label' => 'Plans']]">

    <x-slot name="actions">
        @if (auth()->user()->hasPermission('saas.plans.manage'))
            <x-button href="{{ route('saas.plans.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                New Plan
            </x-button>
        @endif
    </x-slot>

    @php $symbol = saas_setting('currency_symbol', env('CURRENCY_SYMBOL', '₹')); @endphp

    @if ($plans->isEmpty())
        <x-card>
            <div class="p-8">
                <x-empty-state icon="identification" title="No plans yet" message="Create your first subscription plan." @if (auth()->user()->hasPermission('saas.plans.manage')) action="{{ route('saas.plans.create') }}" action-label="Create Plan" @endif />
            </div>
        </x-card>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($plans as $plan)
                <div class="card flex flex-col">
                    <div class="card-body flex flex-1 flex-col">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-base font-bold text-ink-900 dark:text-white">{{ $plan->name }}</h3>
                                <p class="mt-0.5 text-xs text-ink-400">{{ $plan->gyms_count }} gym{{ $plan->gyms_count === 1 ? '' : 's' }} subscribed</p>
                            </div>
                            <x-badge :color="$plan->status === 'active' ? 'green' : 'gray'">{{ ucfirst($plan->status) }}</x-badge>
                        </div>
                        <div class="mt-4 space-y-1">
                            <p class="text-2xl font-extrabold text-ink-900 dark:text-white">
                                {{ $symbol }}{{ number_format($plan->price_monthly, 2) }}<span class="text-sm font-medium text-ink-400">/month</span>
                            </p>
                            <p class="text-lg font-bold text-ink-900 dark:text-white">
                                {{ $symbol }}{{ number_format($plan->price_yearly, 2) }}<span class="text-sm font-medium text-ink-400">/year</span>
                            </p>
                        </div>
                        @if ($plan->description)
                            <p class="mt-3 text-sm leading-relaxed text-ink-500 dark:text-ink-400">{{ $plan->description }}</p>
                        @endif
                        @if (auth()->user()->hasPermission('saas.plans.manage'))
                            <div class="mt-6 flex items-center gap-2 border-t border-ink-100 pt-4 dark:border-ink-800">
                                <a href="{{ route('saas.plans.edit', $plan) }}" class="btn-outline btn-sm flex-1">Edit</a>
                                <form method="POST" action="{{ route('saas.plans.toggle', $plan) }}">
                                    @csrf
                                    <x-button type="submit" :variant="$plan->status === 'active' ? 'danger' : 'outline'" size="sm">
                                        {{ $plan->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </x-button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
