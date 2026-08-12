<x-layouts.app
    title="Gyms"
    description="Manage all gyms and their subscriptions."
    :breadcrumbs="[['label' => 'SaaS', 'url' => route('saas.dashboard')], ['label' => 'Gyms']]">

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat label="Total Gyms" :value="$summary['total']" icon="building" positive />
        <x-stat label="Active" :value="$summary['active']" icon="check-badge" positive />
        <x-stat label="Trial" :value="$summary['trial']" icon="gift" />
        <x-stat label="Expired / Suspended" :value="$summary['expired']" icon="clock" />
    </div>

    <x-card :padding="false" class="mt-6">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4 dark:border-ink-800">
            <form method="GET" action="{{ route('saas.gyms.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
                <div class="relative min-w-52 flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-ink-400"><x-icon name="search" class="size-4" /></span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search gym name, email, slug..." class="input pl-9">
                </div>
                <select name="status" class="input w-auto">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All statuses</option>
                    @foreach (['active' => 'Active', 'trial' => 'Trial', 'expired' => 'Expired', 'suspended' => 'Suspended'] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <x-button type="submit">Filter</x-button>
            </form>
        </div>

        @if ($gyms->isEmpty())
            <div class="p-8">
                <x-empty-state icon="building" title="No gyms found" message="Registered gyms will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Gym</th>
                            <th class="px-5 py-3 font-semibold">Plan</th>
                            <th class="px-5 py-3 font-semibold">Cycle</th>
                            <th class="px-5 py-3 font-semibold">Expires</th>
                            <th class="px-5 py-3 font-semibold">Users</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($gyms as $gym)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4">
                                    <a href="{{ route('saas.gyms.show', $gym) }}" class="font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $gym->name }}</a>
                                    <p class="text-xs text-ink-400">{{ $gym->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $gym->subscriptionPlan?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $gym->subscription_billing_cycle ? ucfirst($gym->subscription_billing_cycle) : '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $gym->subscription_expires_at ? $gym->subscription_expires_at->format('d M Y') : '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $gym->users_count }}</td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($gym->subscription_status) { 'active' => 'green', 'trial' => 'blue', 'expired' => 'red', 'suspended' => 'purple', default => 'gray' }">{{ $gym->subscriptionStatusLabel() }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('saas.gyms.show', $gym) }}" class="btn-outline btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$gyms" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
