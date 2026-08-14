<x-layouts.app
    title="Support"
    description="Support tickets from clients and staff."
    :breadcrumbs="[['label' => 'Support']]">

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat label="Open" :value="$counts['open']" icon="support" :positive="false" />
        <x-stat label="In Progress" :value="$counts['in_progress']" icon="clock" />
        <x-stat label="Resolved" :value="$counts['resolved']" icon="check-badge" />
    </div>

    <div class="mt-6">
        <x-card title="Support Tickets">
            <form method="GET" action="{{ route('support.index') }}" data-ajax-filter data-target="[data-ajax-table='support-table']" class="mb-4 flex flex-wrap items-end gap-3 border-b border-ink-100 pb-4 dark:border-ink-800">
                <x-select label="Status" name="status">
                    <option value="all">All statuses</option>
                    @foreach (['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </x-select>
                <x-select label="Priority" name="priority">
                    <option value="all">All priorities</option>
                    @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                    @endforeach
                </x-select>
                <x-button type="submit">
                    <x-icon name="funnel" class="size-4" />
                    Filter
                </x-button>
            </form>

            <div data-ajax-table="support-table">
            @if ($tickets->isEmpty())
                <x-empty-state icon="support" title="No tickets" message="Support tickets submitted by users will appear here." />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="px-5 py-3 font-semibold">Ticket</th>
                                <th class="px-5 py-3 font-semibold">Subject</th>
                                <th class="px-5 py-3 font-semibold">User</th>
                                <th class="px-5 py-3 font-semibold">Priority</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Last Activity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($tickets as $ticket)
                                <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                    <td class="px-5 py-4 text-ink-400">#{{ $ticket->id }}</td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('support.show', $ticket) }}" class="font-semibold text-ink-900 hover:text-gold-600 dark:text-white dark:hover:text-gold-400">
                                            {{ $ticket->subject }}
                                        </a>
                                        <p class="max-w-xs !whitespace-normal text-xs text-ink-400">{{ $ticket->messages->first()?->message ?: $ticket->message }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <x-avatar :user="$ticket->user" size="size-7" />
                                            <span class="text-ink-600 dark:text-ink-300">{{ $ticket->user?->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <x-badge :color="match($ticket->priority) { 'urgent' => 'red', 'high' => 'amber', 'medium' => 'blue', default => 'gray' }">{{ ucfirst($ticket->priority) }}</x-badge>
                                    </td>
                                    <td class="px-5 py-4">
                                        <x-badge :color="match($ticket->status) { 'open' => 'red', 'in_progress' => 'amber', 'resolved' => 'green', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</x-badge>
                                    </td>
                                    <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $ticket->updated_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    <x-pagination :model="$tickets" />
                </div>
            @endif
            </div>
        </x-card>
    </div>
</x-layouts.app>
