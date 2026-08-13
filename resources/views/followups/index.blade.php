<x-layouts.app
    title="Follow-Ups"
    description="Track scheduled follow-ups with members."
    :breadcrumbs="[['label' => 'Follow-Ups']]">

    <x-slot name="actions">
        @if (can_manage('followups.create'))
            <x-button href="{{ route('followups.create') }}" size="sm">
                <x-icon name="calendar-check" class="size-4" />
                Schedule Follow-Up
            </x-button>
        @endif
    </x-slot>

    <div class="mb-6 flex flex-wrap items-center gap-2">
        <a href="{{ route('followups.index', ['filter' => 'today']) }}" data-ajax-link data-target="[data-ajax-table='followups-table']" @class(['btn-ghost btn-sm', 'btn-outline' => $filter === 'today'])>
            Today
            <x-badge color="gold" class="ml-1">{{ $filter === 'today' ? $followups->total() : '' }}</x-badge>
        </a>
        <a href="{{ route('followups.index', ['filter' => 'upcoming']) }}" data-ajax-link data-target="[data-ajax-table='followups-table']" @class(['btn-ghost btn-sm', 'btn-outline' => $filter === 'upcoming'])>Upcoming</a>
        <a href="{{ route('followups.index', ['filter' => 'overdue']) }}" data-ajax-link data-target="[data-ajax-table='followups-table']" @class(['btn-ghost btn-sm', 'btn-outline' => $filter === 'overdue'])>Overdue</a>
        <a href="{{ route('followups.index', ['filter' => 'completed']) }}" data-ajax-link data-target="[data-ajax-table='followups-table']" @class(['btn-ghost btn-sm', 'btn-outline' => $filter === 'completed'])>Completed</a>
    </div>

    @php $canManage = can_manage('followups.manage'); @endphp

    <x-card :padding="false" x-data="{ reschedule: null, rsDate: '', rsTime: '' }">
        <div data-ajax-table="followups-table">
        @if ($followups->isEmpty())
            <div class="p-8">
                <x-empty-state icon="calendar-check" title="No follow-ups" message="Scheduled follow-ups will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Client</th>
                            <th class="px-5 py-3 font-semibold">Type</th>
                            <th class="px-5 py-3 font-semibold">Date & Time</th>
                            <th class="px-5 py-3 font-semibold">Staff</th>
                            <th class="px-5 py-3 font-semibold">Notes</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            @if ($canManage)
                                <th class="px-5 py-3 text-right font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($followups as $followup)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4">
                                    @if ($followup->client)
                                        <a href="{{ route('clients.show', $followup->client_id) }}" class="font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $followup->client->display_name }}</a>
                                    @else
                                        <span class="text-ink-400">Deleted client</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge color="gold">{{ $followup->type }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">
                                    {{ \Carbon\Carbon::parse($followup->follow_up_date)->format('d M Y') }}
                                    @if ($followup->follow_up_time)
                                        · {{ \Carbon\Carbon::parse($followup->follow_up_time)->format('g:i A') }}
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $followup->staff?->display_name ?? '—' }}</td>
                                <td class="px-5 py-4 max-w-48 truncate text-ink-500 dark:text-ink-400" title="{{ $followup->notes }}">{{ $followup->notes ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($followup->status) { 'completed' => 'green', 'pending' => 'amber', 'overdue' => 'red', 'rescheduled' => 'blue', 'cancelled' => 'gray', default => 'gray' }">{{ ucfirst($followup->status) }}</x-badge>
                                </td>
                                @if ($canManage)
                                    <td class="px-5 py-4 text-right">
                                        @if (in_array($followup->status, ['pending', 'overdue', 'rescheduled']))
                                            <div class="flex justify-end gap-2">
                                                <form method="POST" action="{{ route('followups.complete', $followup) }}" class="inline" data-ajax>
                                                    @csrf
                                                    <x-button type="submit" variant="success" size="sm">Complete</x-button>
                                                </form>
                                                <x-button type="button" variant="outline" size="sm"
                                                          x-on:click="reschedule = @js(['id' => $followup->id, 'date' => $followup->follow_up_date, 'time' => $followup->follow_up_time]); rsDate = reschedule.date; rsTime = reschedule.time; $dispatch('open-modal', 'reschedule-modal')">Reschedule</x-button>
                                                <form method="POST" action="{{ route('followups.cancel', $followup) }}" class="inline" data-ajax>
                                                    @csrf
                                                    <x-button type="submit" variant="ghost" size="sm" class="!text-red-500">Cancel</x-button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-ink-400">—</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$followups" />
            </div>
        @endif
        </div>

        @if ($canManage)
            <x-modal id="reschedule-modal" title="Reschedule Follow-Up">
                <form method="POST" :action="reschedule ? `/followups/${reschedule.id}/reschedule` : '#'" id="reschedule-form"
                      data-ajax data-ajax-dispatch="close-modal" data-refresh="[data-ajax-table='followups-table']">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Date" type="date" name="follow_up_date" x-model="rsDate" required />
                        <x-input label="Time" type="time" name="follow_up_time" x-model="rsTime" />
                    </div>
                    <x-slot name="footer">
                        <div class="flex justify-end gap-3">
                            <x-button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'reschedule-modal')">Cancel</x-button>
                            <x-button type="submit" form="reschedule-form">
                                <x-icon name="refresh" class="size-4" />
                                Reschedule
                            </x-button>
                        </div>
                    </x-slot>
                </form>
            </x-modal>
        @endif
    </x-card>
</x-layouts.app>
