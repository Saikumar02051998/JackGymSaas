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
