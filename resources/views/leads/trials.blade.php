<x-layouts.app
    title="Trial Members"
    description="Track leads on free trials."
    :breadcrumbs="[['label' => 'Leads', 'url' => route('leads.index')], ['label' => 'Trials']]">

    <div class="mb-6 flex flex-wrap items-center gap-2">
        <a href="{{ route('leads.trials') }}" @class(['btn-ghost btn-sm', 'btn-outline' => ! request('status')])>All</a>
        <a href="{{ route('leads.trials', ['status' => 'active']) }}" @class(['btn-ghost btn-sm', 'btn-outline' => request('status') === 'active'])>
            Active
            <x-badge color="green" class="ml-1">{{ $trials->where('status', 'active')->count() }}</x-badge>
        </a>
        <a href="{{ route('leads.trials', ['status' => 'converted']) }}" @class(['btn-ghost btn-sm', 'btn-outline' => request('status') === 'converted'])>
            Converted
            <x-badge color="blue" class="ml-1">{{ $trials->where('status', 'converted')->count() }}</x-badge>
        </a>
        <a href="{{ route('leads.trials', ['status' => 'expired']) }}" @class(['btn-ghost btn-sm', 'btn-outline' => request('status') === 'expired'])>
            Expired
            <x-badge color="red" class="ml-1">{{ $trials->where('status', 'expired')->count() }}</x-badge>
        </a>
    </div>

    @if ($trials->isEmpty())
        <x-card>
            <div class="p-8">
                <x-empty-state icon="target" title="No trial members" message="Leads given a trial will appear here." />
            </div>
        </x-card>
    @else
        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Member</th>
                            <th class="px-5 py-3 font-semibold">Source</th>
                            <th class="px-5 py-3 font-semibold">Trial Period</th>
                            <th class="px-5 py-3 font-semibold">Trainer</th>
                            <th class="px-5 py-3 font-semibold">Days Left</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($trials as $trial)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-ink-900 dark:text-white">{{ $trial->client?->display_name ?? $trial->lead?->name ?? 'Walk-in' }}</p>
                                    <p class="text-xs text-ink-400">{{ $trial->client?->phone ?? $trial->lead?->phone ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $trial->lead ? 'Lead' : 'Direct' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">
                                    {{ \Carbon\Carbon::parse($trial->trial_start)->format('d M') }} &rarr; {{ \Carbon\Carbon::parse($trial->trial_end)->format('d M Y') }}
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $trial->assignedTrainer?->display_name ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    @if ($trial->status === 'active')
                                        @php
                                            $daysLeft = (int) \Carbon\Carbon::parse($trial->trial_end)->diffInDays(now()->startOfDay(), false);
                                        @endphp
                                        <span class="{{ $daysLeft <= 2 ? 'font-bold text-red-500' : 'font-bold text-amber-500' }}">{{ $daysLeft }} days</span>
                                    @else
                                        <span class="text-ink-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($trial->status) { 'active' => 'green', 'converted' => 'blue', 'expired' => 'red', 'cancelled' => 'red', 'completed' => 'gray', default => 'gray' }">{{ ucfirst($trial->status) }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if ($trial->client)
                                            <a href="{{ route('clients.show', $trial->client_id) }}" class="btn-outline btn-sm">View</a>
                                        @endif
                                        @if ($trial->lead)
                                            <a href="{{ route('leads.show', $trial->lead_id) }}" class="btn-outline btn-sm">Lead</a>
                                        @endif
                                        @if ($trial->status === 'active' && $trial->client && can_manage('memberships.create'))
                                            <a href="{{ route('memberships.create', ['client' => $trial->client_id]) }}" class="btn-primary btn-sm">Convert</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$trials" />
            </div>
        </x-card>
    @endif
</x-layouts.app>
