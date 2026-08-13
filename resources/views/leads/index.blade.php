<x-layouts.app
    title="Leads"
    description="Track and convert prospective members."
    :breadcrumbs="[['label' => 'Leads']]">

    <x-slot name="actions">
        @if (can_manage('leads.create'))
            <x-button href="{{ route('leads.create') }}" size="sm">
                <x-icon name="user-plus" class="size-4" />
                Add Lead
            </x-button>
        @endif
    </x-slot>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
        <a href="{{ route('leads.index') }}" data-ajax-link data-target="[data-ajax-table='leads-table']" class="rounded-2xl border border-ink-100 bg-white p-4 transition-colors hover:border-gold-400/60 dark:border-ink-800 dark:bg-ink-900">
            <p class="text-2xl font-extrabold text-ink-900 dark:text-white">{{ $counts['new'] + $counts['contacted'] }}</p>
            <p class="text-xs font-medium uppercase tracking-wide text-ink-400">New & Contacted</p>
        </a>
        <a href="{{ route('leads.index', ['status' => 'interested']) }}" data-ajax-link data-target="[data-ajax-table='leads-table']" class="rounded-2xl border border-ink-100 bg-white p-4 transition-colors hover:border-gold-400/60 dark:border-ink-800 dark:bg-ink-900">
            <p class="text-2xl font-extrabold text-amber-500">{{ $counts['interested'] }}</p>
            <p class="text-xs font-medium uppercase tracking-wide text-ink-400">Interested</p>
        </a>
        <a href="{{ route('leads.index', ['status' => 'trial']) }}" data-ajax-link data-target="[data-ajax-table='leads-table']" class="rounded-2xl border border-ink-100 bg-white p-4 transition-colors hover:border-gold-400/60 dark:border-ink-800 dark:bg-ink-900">
            <p class="text-2xl font-extrabold text-blue-500">{{ $counts['trial'] }}</p>
            <p class="text-xs font-medium uppercase tracking-wide text-ink-400">In Trial</p>
        </a>
        <a href="{{ route('leads.index', ['status' => 'converted']) }}" data-ajax-link data-target="[data-ajax-table='leads-table']" class="rounded-2xl border border-ink-100 bg-white p-4 transition-colors hover:border-gold-400/60 dark:border-ink-800 dark:bg-ink-900">
            <p class="text-2xl font-extrabold text-emerald-500">{{ $counts['converted'] }}</p>
            <p class="text-xs font-medium uppercase tracking-wide text-ink-400">Converted</p>
        </a>
        <a href="{{ route('leads.index', ['status' => 'lost']) }}" data-ajax-link data-target="[data-ajax-table='leads-table']" class="rounded-2xl border border-ink-100 bg-white p-4 transition-colors hover:border-gold-400/60 dark:border-ink-800 dark:bg-ink-900">
            <p class="text-2xl font-extrabold text-red-500">{{ $counts['lost'] }}</p>
            <p class="text-xs font-medium uppercase tracking-wide text-ink-400">Lost</p>
        </a>
    </div>

    <x-card :padding="false" class="mt-6">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4 dark:border-ink-800">
            <form method="GET" action="{{ route('leads.index') }}" data-ajax-filter data-target="[data-ajax-table='leads-table']" class="flex flex-1 flex-wrap items-center gap-2">
                <div class="relative min-w-52 flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-ink-400"><x-icon name="search" class="size-4" /></span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email..." class="input pl-9">
                </div>
                <select name="status" class="input w-auto">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All statuses</option>
                    @foreach (['new' => 'New', 'contacted' => 'Contacted', 'interested' => 'Interested', 'trial' => 'Trial', 'converted' => 'Converted', 'not_interested' => 'Not Interested', 'lost' => 'Lost'] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <x-button type="submit">Filter</x-button>
            </form>
        </div>

        <div data-ajax-table="leads-table">
        @if ($leads->isEmpty())
            <div class="p-8">
                <x-empty-state icon="funnel" title="No leads found" message="Leads you capture will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Lead</th>
                            <th class="px-5 py-3 font-semibold">Source</th>
                            <th class="px-5 py-3 font-semibold">Interested Plan</th>
                            <th class="px-5 py-3 font-semibold">Assigned To</th>
                            <th class="px-5 py-3 font-semibold">Follow-Up</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($leads as $lead)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4">
                                    <a href="{{ route('leads.show', $lead) }}" class="font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $lead->name }}</a>
                                    <p class="text-xs text-ink-400">{{ $lead->phone }}{{ $lead->email ? ' · ' . $lead->email : '' }}</p>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $lead->source ?? '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $lead->interestedPlan?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $lead->assignedTo?->name ?? 'Unassigned' }}</td>
                                <td class="px-5 py-4">
                                    @if ($lead->follow_up_date)
                                        @php $overdue = \Carbon\Carbon::parse($lead->follow_up_date)->isBefore(now()->startOfDay()) && ! in_array($lead->status, ['converted', 'lost', 'not_interested']); @endphp
                                        <span class="{{ $overdue ? 'font-bold text-red-500' : 'text-ink-600 dark:text-ink-300' }}">
                                            {{ \Carbon\Carbon::parse($lead->follow_up_date)->format('d M') }}
                                        </span>
                                    @else
                                        <span class="text-ink-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($lead->status) { 'new' => 'blue', 'contacted' => 'purple', 'interested' => 'amber', 'trial' => 'gold', 'converted' => 'green', 'not_interested' => 'gray', 'lost' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</x-badge>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('leads.show', $lead) }}" class="btn-outline btn-sm">View</a>
                                        @if (can_manage('leads.edit'))
                                            <a href="{{ route('leads.edit', $lead) }}" class="btn-outline btn-sm">
                                                <x-icon name="pencil" class="size-3.5" />
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$leads" />
            </div>
        @endif
        </div>
    </x-card>
</x-layouts.app>
