<x-layouts.app
    title="Membership Plans"
    description="Define the membership packages you offer."
    :breadcrumbs="[['label' => 'Memberships', 'url' => route('memberships.index')], ['label' => 'Plans']]">

    <x-slot name="actions">
        @if (can_manage('memberships.manage'))
            <x-button href="{{ route('memberships.plans.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                New Plan
            </x-button>
        @endif
    </x-slot>

    @if ($plans->isEmpty())
        <x-card>
            <div class="p-8">
                <x-empty-state icon="ticket" title="No plans yet" message="Create your first membership plan." @if (can_manage('memberships.manage')) action="{{ route('memberships.plans.create') }}" action-label="Create Plan" @endif />
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
                                <p class="mt-0.5 text-xs text-ink-400">{{ $plan->duration_label }}</p>
                            </div>
                            <x-badge :color="$plan->status === 'active' ? 'green' : 'gray'">{{ ucfirst($plan->status) }}</x-badge>
                        </div>
                        <p class="mt-4 text-2xl font-extrabold text-ink-900 dark:text-white">
                            {{ gym_setting('currency_symbol', '₹') }}{{ number_format($plan->final_amount, 2) }}
                        </p>
                        @if ($plan->discount > 0)
                            <p class="mt-1 text-xs text-ink-400">
                                <s>{{ gym_setting('currency_symbol', '₹') }}{{ number_format($plan->price, 2) }}</s>
                                <span class="ml-1 font-semibold text-emerald-500">{{ $plan->discount }} off</span>
                            </p>
                        @endif
                        @if ($plan->description)
                            <p class="mt-3 text-sm leading-relaxed text-ink-500 dark:text-ink-400">{{ $plan->description }}</p>
                        @endif
                        @if (! empty($plan->features))
                            <ul class="mt-4 flex-1 space-y-2">
                                @foreach (collect($plan->features)->take(5) as $feature)
                                    <li class="flex items-start gap-2 text-sm text-ink-600 dark:text-ink-300">
                                        <span class="mt-0.5 flex size-4 shrink-0 items-center justify-center rounded-full bg-emerald-400/15 text-emerald-500"><x-icon name="check" class="size-3" /></span>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @if (can_manage('memberships.manage'))
                            <div class="mt-6 flex items-center gap-2 border-t border-ink-100 pt-4 dark:border-ink-800">
                                <a href="{{ route('memberships.plans.edit', $plan) }}" class="btn-outline btn-sm flex-1">Edit</a>
                                <form method="POST" action="{{ route('memberships.plans.toggle', $plan) }}">
                                    @csrf
                                    <x-button type="submit" :variant="$plan->status === 'active' ? 'danger' : 'outline'" size="sm">
                                        {{ $plan->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </x-button>
                                </form>
                                <form method="POST" action="{{ route('memberships.plans.destroy', $plan) }}"
                                      x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Delete plan?', message: 'This will permanently delete {{ $plan->name }}.', confirmText: 'Delete' } })">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="ghost" size="sm" class="!text-red-500 hover:!bg-red-50 dark:hover:!bg-red-500/10">
                                        <x-icon name="trash" class="size-4" />
                                    </x-button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">
            <x-pagination :model="$plans" />
        </div>
    @endif
</x-layouts.app>
