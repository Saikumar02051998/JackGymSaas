<x-layouts.app
    title="Client Report"
    description="Membership and client overview."
    :breadcrumbs="[['label' => 'Reports'], ['label' => 'Clients']]">

    <x-card>
        <form method="GET" action="{{ route('reports.clients') }}" class="flex flex-wrap items-end gap-3">
            <x-input label="From" type="date" name="from" value="{{ request('from') }}" />
            <x-input label="To" type="date" name="to" value="{{ request('to') }}" />
            <x-button type="submit">
                <x-icon name="search" class="size-4" />
                Run Report
            </x-button>
            <x-button href="{{ route('reports.export', array_merge(['type' => 'clients'], request()->only(['from', 'to']))) }}" variant="outline">
                <x-icon name="download" class="size-4" />
                Export
            </x-button>
        </form>
    </x-card>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-stat label="Total Clients" :value="$report['total_clients']" icon="users" />
        <x-stat label="New (Period)" :value="$report['new_clients']" icon="user-plus" positive />
        <x-stat label="Active Members" :value="$report['active_members']" icon="check-badge" positive />
        <x-stat label="Expired Members" :value="$report['expired_members']" icon="trending-down" />
        <x-stat label="Inactive Clients" :value="$report['inactive_clients']" icon="clock" />
    </div>

    <x-card :padding="false" title="New Clients in Period" class="mt-6">
        @if ($report['recent_clients']->isEmpty())
            <div class="p-8">
                <x-empty-state icon="users" title="No new clients" message="Clients joining in this period will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Member</th>
                            <th class="px-5 py-3 font-semibold">Member ID</th>
                            <th class="px-5 py-3 font-semibold">Plan</th>
                            <th class="px-5 py-3 font-semibold">Joined</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($report['recent_clients'] as $client)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-ink-900 dark:text-white">{{ $client->display_name }}</td>
                                <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ $client->member_id }}</td>
                                <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ $client->activeMembership?->plan?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($client->joining_date)->format('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :color="$client->status === 'active' ? 'green' : 'gray'">{{ ucfirst($client->status) }}</x-badge>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('clients.show', $client) }}" class="btn-outline btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$report['recent_clients']" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
