<x-layouts.app
    title="Clients"
    description="Manage your members and their memberships."
    :breadcrumbs="[['label' => 'Clients']]">

    <x-slot name="actions">
        @if (can_manage('clients.create'))
            <x-button href="{{ route('clients.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                Add Client
            </x-button>
        @endif
        <x-button href="{{ route('clients.export', request()->query()) }}" variant="outline" size="sm">
            <x-icon name="download" class="size-4" />
            Export
        </x-button>
    </x-slot>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <a href="{{ route('clients.index') }}" class="card transition-colors hover:border-gold-300">
            <div class="card-body flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">All Clients</p>
                    <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-white">{{ $counts['all'] }}</p>
                </div>
                <span class="flex size-10 items-center justify-center rounded-xl bg-gold-400/15 text-gold-600 dark:text-gold-400"><x-icon name="users" class="size-5" /></span>
            </div>
        </a>
        <a href="{{ route('clients.index', ['status' => 'active']) }}" class="card transition-colors hover:border-gold-300">
            <div class="card-body flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Active</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-500">{{ $counts['active'] }}</p>
                </div>
                <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-400/15 text-emerald-600 dark:text-emerald-400"><x-icon name="check-badge" class="size-5" /></span>
            </div>
        </a>
        <a href="{{ route('clients.index', ['status' => 'expiring']) }}" class="card transition-colors hover:border-gold-300">
            <div class="card-body flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Expiring Soon</p>
                    <p class="mt-1 text-2xl font-bold text-amber-500">{{ $counts['expiring'] }}</p>
                </div>
                <span class="flex size-10 items-center justify-center rounded-xl bg-amber-400/15 text-amber-600 dark:text-amber-400"><x-icon name="clock" class="size-5" /></span>
            </div>
        </a>
        <a href="{{ route('clients.index', ['status' => 'expired']) }}" class="card transition-colors hover:border-gold-300">
            <div class="card-body flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Expired</p>
                    <p class="mt-1 text-2xl font-bold text-red-500">{{ $counts['expired'] }}</p>
                </div>
                <span class="flex size-10 items-center justify-center rounded-xl bg-red-400/15 text-red-600 dark:text-red-400"><x-icon name="trending-down" class="size-5" /></span>
            </div>
        </a>
    </div>

    <div class="mt-6">
        <x-card :padding="false">
            <div class="card-body flex flex-col gap-3 border-b border-ink-100 p-4 dark:border-ink-800 sm:flex-row sm:items-center">
                <form method="GET" class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone or member ID..."
                               class="input !pl-10">
                    </div>
                    <div class="flex gap-3">
                        <select name="status" class="input appearance-none sm:w-44" onchange="this.form.submit()">
                            <option value="all" {{ request('status') === 'all' || ! request('status') ? 'selected' : '' }}>All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expiring" {{ request('status') === 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <select name="coach" class="input appearance-none sm:w-44" onchange="this.form.submit()">
                            <option value="">All Coaches</option>
                            @foreach ($coaches as $coach)
                                <option value="{{ $coach->id }}" {{ request('coach') == $coach->id ? 'selected' : '' }}>{{ $coach->display_name }}</option>
                            @endforeach
                        </select>
                        <x-button type="submit">Filter</x-button>
                        @if (request()->hasAny(['search', 'status', 'coach']))
                            <x-button href="{{ route('clients.index') }}" variant="outline" size="sm">Clear</x-button>
                        @endif
                    </div>
                </form>
            </div>

            @if ($clients->isEmpty())
                <div class="p-8">
                    <x-empty-state icon="users" title="No clients found" message="Try adjusting your filters, or add your first client." action="{{ route('clients.create') }}" action-label="Add Client" />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="px-5 py-3 font-semibold">Member</th>
                                <th class="px-5 py-3 font-semibold">Member ID</th>
                                <th class="px-5 py-3 font-semibold">Contact</th>
                                <th class="px-5 py-3 font-semibold">Plan</th>
                                <th class="px-5 py-3 font-semibold">Expiry</th>
                                <th class="px-5 py-3 font-semibold">Trainer</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($clients as $client)
                                <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('clients.show', $client) }}" class="flex items-center gap-3">
                                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gold-400/15 text-xs font-bold text-gold-700 dark:text-gold-400">{{ $client->initials }}</span>
                                            <span class="font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $client->user?->name }}</span>
                                        </a>
                                    </td>
                                    <td class="px-5 py-4 text-ink-500 dark:text-ink-400">{{ $client->member_id }}</td>
                                    <td class="px-5 py-4">
                                        <p class="text-ink-600 dark:text-ink-300">{{ $client->phone ?? '—' }}</p>
                                        <p class="text-xs text-ink-400">{{ $client->user?->email ?? '' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $client->activeMembership?->plan?->name ?? 'No plan' }}</td>
                                    <td class="px-5 py-4">
                                        @if ($client->activeMembership)
                                            <span class="{{ $client->activeMembership->days_remaining <= 7 ? 'font-semibold text-red-500' : 'text-ink-600 dark:text-ink-300' }}">
                                                {{ \Carbon\Carbon::parse($client->activeMembership->end_date)->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-ink-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $client->trainer?->display_name ?? '—' }}</td>
                                    <td class="px-5 py-4">
                                        <x-badge :color="$client->status === 'active' ? 'green' : 'gray'">{{ ucfirst($client->status) }}</x-badge>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('clients.show', $client) }}" class="rounded-lg p-2 text-ink-400 transition-colors hover:bg-ink-100 hover:text-gold-600 dark:hover:bg-ink-800" title="View">
                                                <x-icon name="eye" class="size-4" />
                                            </a>
                                            @if (can_manage('clients.edit'))
                                                <a href="{{ route('clients.edit', $client) }}" class="rounded-lg p-2 text-ink-400 transition-colors hover:bg-ink-100 hover:text-gold-600 dark:hover:bg-ink-800" title="Edit">
                                                    <x-icon name="pencil" class="size-4" />
                                                </a>
                                            @endif
                                            @if (can_manage('clients.delete'))
                                                <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                                      x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Delete client?', message: 'This will permanently remove {{ $client->user?->name }} and their access.', confirmText: 'Delete' } })">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg p-2 text-ink-400 transition-colors hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10" title="Delete">
                                                        <x-icon name="trash" class="size-4" />
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    <x-pagination :model="$clients" />
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
