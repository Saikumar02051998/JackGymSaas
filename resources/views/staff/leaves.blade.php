<x-layouts.app
    title="Leave Requests"
    description="Review and manage staff leave requests."
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => 'Leaves']]">

    <x-slot name="actions">
        @if (auth()->user()->staffProfile)
            <x-button href="{{ route('staff.leaves.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                Request Leave
            </x-button>
        @endif
    </x-slot>

    @error('leave')
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-600 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ $message }}</div>
    @enderror

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        <x-card title="Leave Policy">
            <ul class="space-y-3">
                <li class="flex items-center justify-between gap-2">
                    <span class="flex items-center gap-2 text-sm text-ink-500 dark:text-ink-400">
                        <x-icon name="calendar" class="size-4" />
                        Calendar days / month
                    </span>
                    <span class="text-sm font-bold text-ink-900 dark:text-white">{{ $rules['calendar_days'] }} days</span>
                </li>
                <li class="flex items-center justify-between gap-2">
                    <span class="flex items-center gap-2 text-sm text-ink-500 dark:text-ink-400">
                        <x-icon name="sun" class="size-4" />
                        Paid full-day leaves
                    </span>
                    <span class="text-sm font-bold text-ink-900 dark:text-white">{{ $rules['paid_leave_days'] }} / month</span>
                </li>
                <li class="flex items-center justify-between gap-2">
                    <span class="flex items-center gap-2 text-sm text-ink-500 dark:text-ink-400">
                        <x-icon name="clock" class="size-4" />
                        Paid half-day leaves
                    </span>
                    <span class="text-sm font-bold text-ink-900 dark:text-white">{{ $rules['paid_half_days'] }} / month</span>
                </li>
            </ul>
            <div class="mt-4 rounded-xl bg-ink-50 px-3 py-2.5 text-xs leading-relaxed text-ink-500 dark:bg-ink-800/60 dark:text-ink-400">
                Leave taken beyond the paid allowance is deducted from the monthly salary at the per-day rate (monthly salary &divide; {{ $rules['calendar_days'] }}).
            </div>
        </x-card>

        <x-card title="Overall Staffs Leave — {{ now()->format('F Y') }}" class="lg:col-span-2">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-amber-200 bg-amber-100/60 px-4 py-3 dark:border-amber-500/40 dark:bg-amber-500/15">
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Pending</p>
                    <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-white">{{ $overallLeaves['pending'] }}</p>
                    <p class="text-xs text-ink-500 dark:text-ink-400">{{ $overallLeaves['pending_days'] }} day{{ $overallLeaves['pending_days'] == 1 ? '' : 's' }} this month</p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Taken</p>
                    <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-white">{{ $overallLeaves['approved'] }}</p>
                    <p class="text-xs text-ink-500 dark:text-ink-400">{{ $overallLeaves['approved_days'] }} day{{ $overallLeaves['approved_days'] == 1 ? '' : 's' }} approved</p>
                </div>
                <div class="rounded-xl border border-ink-100 bg-ink-50 px-4 py-3 dark:border-ink-800 dark:bg-ink-800/60">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-500 dark:text-ink-400">Total Requests</p>
                    <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-white">{{ $overallLeaves['total'] }}</p>
                    <p class="text-xs text-ink-500 dark:text-ink-400">All requests this month</p>
                </div>
            </div>
        </x-card>

        @if ($myLeaves)
            <x-card title="My Leave Summary — {{ now()->format('F Y') }}" class="lg:col-span-3">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-amber-200 bg-amber-100/60 px-4 py-3 dark:border-amber-500/40 dark:bg-amber-500/15">
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Pending</p>
                        <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-white">{{ $myLeaves['pending'] }}</p>
                        <p class="text-xs text-ink-500 dark:text-ink-400">{{ $myLeaves['pending_days'] }} day{{ $myLeaves['pending_days'] == 1 ? '' : 's' }} this month</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Taken</p>
                        <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-white">{{ $myLeaves['approved'] }}</p>
                        <p class="text-xs text-ink-500 dark:text-ink-400">{{ $myLeaves['approved_days'] }} day{{ $myLeaves['approved_days'] == 1 ? '' : 's' }} approved</p>
                    </div>
                    <div class="rounded-xl border border-ink-100 bg-ink-50 px-4 py-3 dark:border-ink-800 dark:bg-ink-800/60">
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink-500 dark:text-ink-400">Total Requests</p>
                        <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-white">{{ $myLeaves['total'] }}</p>
                        <p class="text-xs text-ink-500 dark:text-ink-400">All requests this month</p>
                    </div>
                </div>
            </x-card>
        @endif
    </div>

    <x-card :padding="false">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4 dark:border-ink-800">
            <form method="GET" action="{{ route('staff.leaves.index') }}" data-ajax-filter data-target="[data-ajax-table='staff-leaves-table']" class="flex items-center gap-2">
                <select name="status" class="input w-auto">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <x-button type="submit">Filter</x-button>
            </form>
        </div>

        <div data-ajax-table="staff-leaves-table">
        @if ($leaves->isEmpty())
            <div class="p-8">
                <x-empty-state icon="calendar" title="No leave requests" message="Leave requests will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Staff</th>
                            <th class="px-5 py-3 font-semibold">Type</th>
                            <th class="px-5 py-3 font-semibold">Period</th>
                            <th class="px-5 py-3 font-semibold">Days</th>
                            <th class="px-5 py-3 font-semibold">Reason</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            @if (auth()->user()->isOwner() || auth()->user()->hasRole('manager'))
                                <th class="px-5 py-3 text-right font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($leaves as $leave)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-ink-900 dark:text-white">{{ $leave->staff?->display_name ?? 'Removed staff' }}</p>
                                    <p class="text-xs text-ink-400">{{ $leave->staff?->designation ?? '' }}</p>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $leave->leave_type }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} &rarr; {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                <td class="px-5 py-4">
                                    {{ $leave->days }}
                                    @if ($leave->is_half_day)
                                        <x-badge color="blue" class="ml-1">Half</x-badge>
                                    @endif
                                </td>
                                <td class="px-5 py-4 max-w-56 truncate text-ink-500 dark:text-ink-400" title="{{ $leave->reason }}">{{ $leave->reason ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($leave->status) { 'approved' => 'green', 'pending' => 'amber', 'rejected' => 'red', 'cancelled' => 'gray', default => 'gray' }">{{ ucfirst($leave->status) }}</x-badge>
                                </td>
                                @if (auth()->user()->isOwner() || auth()->user()->hasRole('manager'))
                                    <td class="px-5 py-4 text-right">
                                        @if ($leave->status === 'pending')
                                            <div class="flex justify-end gap-2">
                                                <form method="POST" action="{{ route('staff.leaves.approve', $leave) }}" data-ajax>
                                                    @csrf
                                                    <x-button type="submit" variant="success" size="sm">Approve</x-button>
                                                </form>
                                                <form method="POST" action="{{ route('staff.leaves.reject', $leave) }}" data-ajax>
                                                    @csrf
                                                    <x-button type="submit" variant="ghost" size="sm" class="!text-red-500">Reject</x-button>
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
                <x-pagination :model="$leaves" />
            </div>
        @endif
        </div>
    </x-card>
</x-layouts.app>
