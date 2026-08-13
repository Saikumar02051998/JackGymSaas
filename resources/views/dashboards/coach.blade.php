<x-layouts.app
    title="Coach Dashboard"
    description="Welcome back, {{ auth()->user()->name }}. Here is your coaching day at a glance."
    :breadcrumbs="[['label' => 'Dashboard']]">

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="My Clients" :value="$coachStats['assigned_clients']" icon="users" />
        <x-stat label="Sessions Today" :value="$coachStats['today_sessions']" icon="calendar-check" />
        <x-stat label="Follow-ups Today" :value="$coachStats['today_followups']" icon="chat" />
        <x-stat label="Overdue Follow-ups" :value="$coachStats['overdue_followups']" icon="clock" :positive="false" />
    </div>

    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Active Plans" :value="$coachStats['active_plans']" icon="dumbbell" />
        <x-stat label="PT Sessions" :value="$coachStats['completed_pt_sessions'] . ' / ' . $coachStats['total_pt_sessions']" icon="bolt" />
        <x-stat label="Commission Rate" :value="$coachStats['commission'] . '%'" icon="trending-up" />
        <x-stat label="Gym Members" :value="$stats['active_members']" icon="check-badge" />
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <x-card title="Salary & Leave Summary">
            <ul class="space-y-3">
                <li class="flex items-center justify-between gap-2">
                    <span class="flex items-center gap-2 text-sm text-ink-500 dark:text-ink-400">
                        <x-icon name="banknotes" class="size-4" />
                        Basic Salary
                    </span>
                    <span class="text-sm font-bold text-ink-900 dark:text-white">{{ money($coachStats['basic_salary']) }}</span>
                </li>
                <li class="flex items-center justify-between gap-2">
                    <span class="flex items-center gap-2 text-sm text-ink-500 dark:text-ink-400">
                        <x-icon name="trending-up" class="size-4" />
                        Allowances
                    </span>
                    <span class="text-sm font-bold text-ink-900 dark:text-white">{{ money($coachStats['allowances']) }}</span>
                </li>
                <li class="flex items-center justify-between gap-2">
                    <span class="flex items-center gap-2 text-sm text-ink-500 dark:text-ink-400">
                        <x-icon name="bolt" class="size-4" />
                        Commission rate
                    </span>
                    <span class="text-sm font-bold text-ink-900 dark:text-white">{{ $coachStats['commission'] }}%</span>
                </li>
                <li class="flex items-center justify-between gap-2">
                    <span class="flex items-center gap-2 text-sm text-ink-500 dark:text-ink-400">
                        <x-icon name="clock" class="size-4" />
                        Paid leave allowance
                    </span>
                    <span class="text-sm font-bold text-ink-900 dark:text-white">{{ $coachStats['paid_leave_days'] }} full / {{ $coachStats['paid_half_days'] }} half</span>
                </li>
            </ul>
            <div class="mt-4 rounded-xl border border-gold-300/60 bg-gold-400/10 px-4 py-3 dark:border-gold-500/30 dark:bg-gold-400/10">
                <p class="text-xs font-semibold uppercase tracking-wider text-gold-700 dark:text-gold-400">Net Payable This Month</p>
                <p class="mt-1 text-2xl font-bold tracking-tight text-ink-900 dark:text-white">{{ money($coachStats['expected_salary']) }}</p>
                <p class="mt-0.5 text-xs text-ink-500 dark:text-ink-400">
                    {{ money($coachStats['gross_salary']) }} gross &minus; {{ money($coachStats['leave_deduction']) }} leave deduction
                </p>
            </div>
            <div class="mt-3 rounded-xl bg-ink-50 px-3 py-2.5 text-xs leading-relaxed text-ink-500 dark:bg-ink-800/60 dark:text-ink-400">
                Salary is divided across {{ $coachStats['calendar_days'] }} calendar days; leaves beyond the paid allowance are deducted at the per-day rate.
            </div>
        </x-card>

        <x-card title="My Leaves — {{ now()->format('F Y') }}" class="lg:col-span-2">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Pending</p>
                    <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-white">{{ $coachStats['pending_leaves'] }}</p>
                    <p class="text-xs text-ink-500 dark:text-ink-400">{{ $coachStats['pending_leave_days'] }} day{{ $coachStats['pending_leave_days'] == 1 ? '' : 's' }} this month</p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Taken</p>
                    <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-white">{{ $coachStats['taken_leaves'] }}</p>
                    <p class="text-xs text-ink-500 dark:text-ink-400">{{ $coachStats['taken_leave_days'] }} day{{ $coachStats['taken_leave_days'] == 1 ? '' : 's' }} approved</p>
                </div>
                <div class="rounded-xl border border-ink-100 bg-ink-50 px-4 py-3 dark:border-ink-800 dark:bg-ink-800/60">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-500 dark:text-ink-400">Total Requests</p>
                    <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-white">{{ $coachStats['total_leaves'] }}</p>
                    <p class="text-xs text-ink-500 dark:text-ink-400">All requests this month</p>
                </div>
            </div>
            <a href="{{ route('staff.leaves.index') }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-gold-600 hover:text-gold-700 dark:text-gold-400">
                Manage my leaves
                <x-icon name="chevron-right" class="size-4" />
            </a>
        </x-card>
    </div>

    @if (auth()->user()->hasPermission('dashboard.revenue.view'))
        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <x-card title="Revenue vs Expenses" class="lg:col-span-2">
                <div class="h-72">
                    <canvas x-data="chart($el)" x-init="init()"
                            data-chart='{!! json_encode([
                                'type' => 'line',
                                'data' => [
                                    'labels' => $revenueChart['labels'],
                                    'datasets' => [
                                        ['label' => 'Revenue', 'data' => $revenueChart['revenue'], 'borderColor' => '#d4a63c', 'backgroundColor' => 'rgba(212,166,60,0.12)', 'fill' => true, 'tension' => 0.35],
                                        ['label' => 'Expenses', 'data' => $revenueChart['expenses'], 'borderColor' => '#ef4444', 'backgroundColor' => 'rgba(239,68,68,0.08)', 'fill' => true, 'tension' => 0.35],
                                    ],
                                ],
                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'></canvas>
                </div>
            </x-card>

            <x-card title="Today at a Glance">
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-gold-400/15 text-gold-600 dark:text-gold-400">
                            <x-icon name="calendar-check" class="size-5" />
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ $coachStats['today_sessions'] }} scheduled sessions</p>
                            <p class="text-xs text-ink-400">For today, {{ now()->format('l, d M Y') }}</p>
                        </div>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-emerald-400/15 text-emerald-600 dark:text-emerald-400">
                            <x-icon name="chat" class="size-5" />
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ $coachStats['today_followups'] }} follow-ups due</p>
                            <p class="text-xs text-ink-400">Keep your clients motivated</p>
                        </div>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-red-400/15 text-red-600 dark:text-red-400">
                            <x-icon name="clock" class="size-5" />
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ $coachStats['overdue_followups'] }} overdue follow-ups</p>
                            <p class="text-xs text-ink-400">Waiting for your attention</p>
                        </div>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-blue-400/15 text-blue-600 dark:text-blue-400">
                            <x-icon name="dumbbell" class="size-5" />
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ $coachStats['active_plans'] }} active workout plans</p>
                            <p class="text-xs text-ink-400">Across your assigned clients</p>
                        </div>
                    </li>
                </ul>
            </x-card>
        </div>
    @endif

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <x-card title="Quick Actions">
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('workouts.create') }}" class="flex flex-col items-center gap-2 rounded-2xl border border-ink-200 p-4 text-center transition-colors hover:border-gold-300 hover:bg-gold-400/5 dark:border-ink-700">
                    <x-icon name="dumbbell" class="size-6 text-gold-600 dark:text-gold-400" />
                    <span class="text-xs font-semibold text-ink-700 dark:text-ink-300">New Workout Plan</span>
                </a>
                <a href="{{ route('diets.create') }}" class="flex flex-col items-center gap-2 rounded-2xl border border-ink-200 p-4 text-center transition-colors hover:border-gold-300 hover:bg-gold-400/5 dark:border-ink-700">
                    <x-icon name="apple" class="size-6 text-gold-600 dark:text-gold-400" />
                    <span class="text-xs font-semibold text-ink-700 dark:text-ink-300">New Diet Plan</span>
                </a>
                <a href="{{ route('progress.index') }}" class="flex flex-col items-center gap-2 rounded-2xl border border-ink-200 p-4 text-center transition-colors hover:border-gold-300 hover:bg-gold-400/5 dark:border-ink-700">
                    <x-icon name="trending-up" class="size-6 text-gold-600 dark:text-gold-400" />
                    <span class="text-xs font-semibold text-ink-700 dark:text-ink-300">Log Progress</span>
                </a>
                <a href="{{ route('appointments.index') }}" class="flex flex-col items-center gap-2 rounded-2xl border border-ink-200 p-4 text-center transition-colors hover:border-gold-300 hover:bg-gold-400/5 dark:border-ink-700">
                    <x-icon name="calendar" class="size-6 text-gold-600 dark:text-gold-400" />
                    <span class="text-xs font-semibold text-ink-700 dark:text-ink-300">Appointments</span>
                </a>
            </div>
        </x-card>

        @if (auth()->user()->hasPermission('dashboard.revenue.view'))
            <x-card title="Recent Revenue" class="lg:col-span-2">
                @forelse ($stats['recent_payments'] as $payment)
                    <div class="flex items-center justify-between border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                        <div class="min-w-0">
                            <a href="{{ route('payments.show', $payment) }}" class="block truncate text-sm font-semibold text-ink-900 hover:text-gold-600 dark:text-white">
                                {{ $payment->client?->display_name }}
                            </a>
                            <p class="text-xs text-ink-400">{{ $payment->plan?->name ?? 'Membership' }} &middot; {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-emerald-500">{{ money($payment->final_amount) }}</p>
                            <x-badge :color="$payment->status === 'success' ? 'green' : ($payment->status === 'pending' ? 'amber' : 'red')">{{ ucfirst($payment->status) }}</x-badge>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-ink-400">No recent payments.</div>
                @endforelse
            </x-card>
        @endif
    </div>
</x-layouts.app>
