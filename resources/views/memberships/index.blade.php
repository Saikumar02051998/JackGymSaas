<x-layouts.app
    title="Memberships"
    description="Track all active, upcoming and expired memberships."
    :breadcrumbs="[['label' => 'Memberships']]">

    <x-slot name="actions">
        @if (can_manage('memberships.create'))
            <x-button href="{{ route('memberships.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                New Membership
            </x-button>
        @endif
    </x-slot>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <a href="{{ route('memberships.index', ['status' => 'active']) }}" class="card transition-colors hover:border-gold-300">
            <div class="card-body">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Active</p>
                <p class="mt-1 text-2xl font-bold text-emerald-500">{{ $counts['active'] }}</p>
            </div>
        </a>
        <a href="{{ route('memberships.index', ['status' => 'expiring']) }}" class="card transition-colors hover:border-gold-300">
            <div class="card-body">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Expiring (30d)</p>
                <p class="mt-1 text-2xl font-bold text-amber-500">{{ $counts['expiring'] }}</p>
            </div>
        </a>
        <a href="{{ route('memberships.index', ['status' => 'upcoming']) }}" class="card transition-colors hover:border-gold-300">
            <div class="card-body">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Upcoming</p>
                <p class="mt-1 text-2xl font-bold text-blue-500">{{ $counts['upcoming'] }}</p>
            </div>
        </a>
        <a href="{{ route('memberships.index', ['status' => 'expired']) }}" class="card transition-colors hover:border-gold-300">
            <div class="card-body">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Expired</p>
                <p class="mt-1 text-2xl font-bold text-red-500">{{ $counts['expired'] }}</p>
            </div>
        </a>
    </div>

    <div class="mt-6">
        <x-card :padding="false">
            <div class="card-body flex flex-col gap-3 border-b border-ink-100 p-4 dark:border-ink-800 sm:flex-row sm:items-center">
                <form method="GET" class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by member, ID or membership no..." class="input !pl-10">
                    </div>
                    <select name="status" class="input appearance-none sm:w-44" onchange="this.form.submit()">
                        <option value="all" {{ request('status') === 'all' || ! request('status') ? 'selected' : '' }}>All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expiring" {{ request('status') === 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                        <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="frozen" {{ request('status') === 'frozen' ? 'selected' : '' }}>Frozen</option>
                    </select>
                    <x-button type="submit">Filter</x-button>
                    @if (request()->hasAny(['search', 'status']))
                        <x-button href="{{ route('memberships.index') }}" variant="outline" size="sm">Clear</x-button>
                    @endif
                </form>
            </div>

            @if ($memberships->isEmpty())
                <div class="p-8">
                    <x-empty-state icon="card" title="No memberships found" message="Create a membership to get your clients started." @if (can_manage('memberships.create')) action="{{ route('memberships.create') }}" action-label="New Membership" @endif />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="px-5 py-3 font-semibold">Member</th>
                                <th class="px-5 py-3 font-semibold">Membership</th>
                                <th class="px-5 py-3 font-semibold">Plan</th>
                                <th class="px-5 py-3 font-semibold">Period</th>
                                <th class="px-5 py-3 font-semibold">Amount</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Payment</th>
                                <th class="px-5 py-3 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($memberships as $membership)
                                <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('clients.show', $membership->client_id) }}" class="flex items-center gap-3">
                                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-gold-400/15 text-xs font-bold text-gold-700 dark:text-gold-400">{{ $membership->client?->initials }}</span>
                                            <span class="font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $membership->client?->user?->name }}</span>
                                        </a>
                                    </td>
                                    <td class="px-5 py-4 text-ink-500 dark:text-ink-400">{{ $membership->membership_no }}</td>
                                    <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $membership->plan?->name ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        <p class="text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($membership->start_date)->format('d M Y') }}</p>
                                        <p class="text-xs text-ink-400">{{ \Carbon\Carbon::parse($membership->end_date)->format('d M Y') }}</p>
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-ink-900 dark:text-white">{{ money($membership->final_amount) }}</td>
                                    <td class="px-5 py-4">
                                        <x-badge :color="match($membership->status) { 'active' => 'green', 'upcoming' => 'blue', 'expired' => 'red', 'cancelled' => 'red', 'frozen' => 'purple', 'suspended' => 'amber', default => 'gray' }">{{ ucfirst($membership->status) }}</x-badge>
                                    </td>
                                    <td class="px-5 py-4">
                                        <x-badge :color="$membership->payment_status === 'paid' ? 'green' : ($membership->payment_status === 'pending' ? 'amber' : 'gray')">{{ ucfirst(str_replace('_', ' ', $membership->payment_status)) }}</x-badge>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        @if ($membership->status === 'active' && can_manage('memberships.renew'))
                                            <form method="POST" action="{{ route('memberships.renew', $membership) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="plan_id" value="{{ $membership->plan_id }}">
                                                <button type="submit" class="rounded-lg p-2 text-ink-400 transition-colors hover:bg-ink-100 hover:text-gold-600 dark:hover:bg-ink-800" title="Renew">
                                                    <x-icon name="refresh" class="size-4" />
                                                </button>
                                            </form>
                                        @endif
                                        @if (in_array($membership->status, ['active', 'upcoming', 'frozen']) && can_manage('memberships.renew'))
                                            <form method="POST" action="{{ route('memberships.cancel', $membership) }}" class="inline"
                                                  x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Cancel membership?', message: 'This will cancel {{ $membership->client?->display_name }}'s membership.', confirmText: 'Cancel' } })">
                                                @csrf
                                                <button type="submit" class="rounded-lg p-2 text-ink-400 transition-colors hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10" title="Cancel">
                                                    <x-icon name="x" class="size-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    <x-pagination :model="$memberships" />
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
