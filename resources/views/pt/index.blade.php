<x-layouts.app
    title="PT Sessions"
    description="Personal training sessions schedule."
    :breadcrumbs="[['label' => 'PT Sessions']]">

    <x-slot name="actions">
        @if (can_manage('pt.manage'))
            <x-button href="{{ route('pt.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                New PT Session
            </x-button>
        @endif
    </x-slot>

    <x-card title="PT Sessions">
        <form method="GET" action="{{ route('pt.index') }}" data-ajax-filter data-target="[data-ajax-table='pt-table']" class="mb-4 grid gap-3 border-b border-ink-100 pb-4 dark:border-ink-800 sm:grid-cols-2 lg:grid-cols-4">
            <x-select label="Status" name="status">
                <option value="all">All statuses</option>
                @foreach (['scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </x-select>
            <x-input label="From" name="from" type="date" value="{{ request('from') }}" />
            <x-input label="To" name="to" type="date" value="{{ request('to') }}" />
            <div class="flex items-end gap-2">
                <x-button type="submit">
                    <x-icon name="funnel" class="size-4" />
                    Filter
                </x-button>
                @if (request()->hasAny(['status', 'from', 'to']))
                    <x-button href="{{ route('pt.index') }}" variant="outline" size="sm" data-ajax-clear data-target="[data-ajax-table='pt-table']">Clear</x-button>
                @endif
            </div>
        </form>

        <div data-ajax-table="pt-table">
        @if ($sessions->isEmpty())
            <x-empty-state icon="dumbbell" title="No PT sessions" message="Schedule personal training sessions for clients." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Time</th>
                            <th class="px-5 py-3 font-semibold">Client</th>
                            <th class="px-5 py-3 font-semibold">Trainer</th>
                            <th class="px-5 py-3 font-semibold">Duration</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            @if (can_manage('pt.manage'))
                                <th class="px-5 py-3 text-right font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($sessions as $session)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="whitespace-nowrap px-5 py-4 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($session->session_date)->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $session->session_time ? \Carbon\Carbon::parse($session->session_time)->format('H:i') : '—' }}</td>
                                <td class="px-5 py-4">
                                    @if ($session->client)
                                        <div class="flex items-center gap-2.5">
                                            <x-avatar :user="$session->client->user" size="size-7" />
                                            <span class="font-medium text-ink-900 dark:text-white">{{ $session->client->display_name }}</span>
                                        </div>
                                    @else
                                        <span class="text-ink-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $session->trainer?->user?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $session->duration_minutes ? $session->duration_minutes . ' min' : '—' }}</td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($session->status) { 'scheduled' => 'blue', 'completed' => 'green', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst($session->status) }}</x-badge>
                                </td>
                                @if (can_manage('pt.manage'))
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            @if ($session->status === 'scheduled')
                                                <form method="POST" action="{{ route('pt.complete', $session) }}" data-ajax>
                                                    @csrf
                                                    <x-button type="submit" variant="success" size="sm">
                                                        <x-icon name="check" class="size-3.5" />
                                                        Complete
                                                    </x-button>
                                                </form>
                                                <form method="POST" action="{{ route('pt.cancel', $session) }}" data-ajax>
                                                    @csrf
                                                    <x-button type="submit" variant="ghost" size="sm" class="!text-red-500">
                                                        <x-icon name="x" class="size-3.5" />
                                                        Cancel
                                                    </x-button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$sessions" />
            </div>
        @endif
        </div>
    </x-card>
</x-layouts.app>
