<x-layouts.app
    title="Dashboard"
    description="Welcome back, {{ auth()->user()->name }}. Here is what is happening at {{ current_gym()?->name ?? 'your gym' }} today."
    :breadcrumbs="[['label' => 'Dashboard']]">

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Total Clients" :value="$stats['total_clients']" icon="users" />
        <x-stat label="Active Members" :value="$stats['active_members']" icon="check-badge" change="{{ $stats['new_members_month'] }}" changeLabel="new this month" />
        <x-stat label="Expiring (30d)" :value="$stats['expiring_members']" icon="clock" />
        <x-stat label="Revenue This Month" :value="money($stats['revenue_month'])" icon="banknotes" />
    </div>

    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Revenue Today" :value="money($stats['revenue_today'])" icon="trending-up" />
        <x-stat label="Today's Attendance" :value="$stats['today_attendance']" icon="calendar-check" />
        <x-stat label="Pending Payments" :value="money($stats['pending_payments'])" icon="clock" />
        <x-stat label="Expenses This Month" :value="money($stats['expenses_month'])" icon="trending-down" />
    </div>

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

        <x-card title="Membership Status">
            <div class="h-72">
                <canvas x-data="chart($el)" x-init="init()"
                        data-chart='{!! json_encode([
                            'type' => 'doughnut',
                            'data' => [
                                'labels' => ['Active', 'Upcoming', 'Expired', 'Cancelled', 'Frozen'],
                                'datasets' => [
                                    ['data' => [$membershipChart['active'], $membershipChart['upcoming'], $membershipChart['expired'], $membershipChart['cancelled'], $membershipChart['frozen']], 'backgroundColor' => ['#10b981', '#3b82f6', '#6b7280', '#ef4444', '#8b5cf6']],
                                ],
                            ],
                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'></canvas>
            </div>
        </x-card>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <x-card title="Attendance (14 days)">
            <div class="h-64">
                <canvas x-data="chart($el)" x-init="init()"
                        data-chart='{!! json_encode([
                            'type' => 'bar',
                            'data' => [
                                'labels' => $attendanceChart['labels'],
                                'datasets' => [
                                    ['label' => 'Check-ins', 'data' => $attendanceChart['counts'], 'backgroundColor' => 'rgba(212,166,60,0.7)', 'borderRadius' => 6],
                                ],
                            ],
                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'></canvas>
            </div>
        </x-card>

        <x-card title="Payments by Method">
            @if (empty($paymentMethodChart['values']))
                <div class="flex h-64 items-center justify-center text-sm text-ink-400">No payment data yet.</div>
            @else
                <div class="h-64">
                    <canvas x-data="chart($el)" x-init="init()"
                            data-chart='{!! json_encode([
                                'type' => 'bar',
                                'data' => [
                                    'labels' => $paymentMethodChart['labels'],
                                    'datasets' => [
                                        ['label' => 'Amount', 'data' => $paymentMethodChart['values'], 'backgroundColor' => ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899'], 'borderRadius' => 6],
                                    ],
                                ],
                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'></canvas>
                </div>
            @endif
        </x-card>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <x-card title="Upcoming Renewals" class="lg:col-span-1">
            @forelse ($stats['upcoming_renewals'] as $membership)
                <div class="flex items-center justify-between border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                    <div class="min-w-0">
                        <a href="{{ route('clients.show', $membership->client_id) }}" class="block truncate text-sm font-semibold text-ink-900 hover:text-gold-600 dark:text-white">
                            {{ $membership->client?->display_name }}
                        </a>
                        <p class="text-xs text-ink-400">{{ $membership->plan?->name ?? 'Membership' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($membership->end_date)->format('d M Y') }}</p>
                        <p class="text-xs {{ $membership->days_remaining <= 3 ? 'text-red-500' : 'text-amber-500' }}">{{ $membership->days_remaining }} days left</p>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-ink-400">No renewals in the next 30 days.</div>
            @endforelse
            <div class="pt-2 text-right">
                <a href="{{ route('memberships.expiring') }}" class="text-xs font-semibold text-gold-600 hover:text-gold-500">View all</a>
            </div>
        </x-card>

        <x-card title="Recent Payments" class="lg:col-span-1">
            @forelse ($stats['recent_payments'] as $payment)
                <div class="flex items-center justify-between border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                    <div class="min-w-0">
                        <a href="{{ route('payments.show', $payment) }}" class="block truncate text-sm font-semibold text-ink-900 hover:text-gold-600 dark:text-white">
                            {{ $payment->client?->display_name }}
                        </a>
                        <p class="text-xs text-ink-400">{{ $payment->payment_no }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-emerald-500">{{ money($payment->final_amount) }}</p>
                        <x-badge :color="$payment->status === 'success' ? 'green' : ($payment->status === 'pending' ? 'amber' : 'red')">{{ ucfirst($payment->status) }}</x-badge>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-ink-400">No payments recorded yet.</div>
            @endforelse
        </x-card>

        <x-card title="Today's Check-ins" class="lg:col-span-1">
            @forelse ($stats['today_attendance_list'] as $attendance)
                <div class="flex items-center justify-between border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                    <div class="min-w-0">
                        <a href="{{ route('clients.show', $attendance->client_id) }}" class="block truncate text-sm font-semibold text-ink-900 hover:text-gold-600 dark:text-white">
                            {{ $attendance->client?->display_name }}
                        </a>
                        <p class="text-xs text-ink-400">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-ink-900 dark:text-white">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : '—' }}</p>
                        <x-badge :color="$attendance->status === 'present' ? 'green' : 'amber'">{{ ucfirst($attendance->status) }}</x-badge>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-ink-400">No check-ins yet today.</div>
            @endforelse
        </x-card>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <x-card title="Recent Leads">
            @forelse ($stats['recent_leads'] as $lead)
                <div class="flex items-center justify-between border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                    <div class="min-w-0">
                        <a href="{{ route('leads.show', $lead) }}" class="block truncate text-sm font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $lead->name }}</a>
                        <p class="text-xs text-ink-400">{{ $lead->phone }}</p>
                    </div>
                    <div class="text-right">
                        <x-badge :color="match($lead->status) { 'converted' => 'green', 'new' => 'blue', 'interested' => 'gold', 'not_interested', 'lost' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</x-badge>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-ink-400">No leads yet.</div>
            @endforelse
        </x-card>

        <x-card title="Trials Expiring Soon">
            @forelse ($stats['expiring_trials'] as $trial)
                <div class="flex items-center justify-between border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-ink-900 dark:text-white">{{ $trial->client?->display_name ?? 'Walk-in trial' }}</p>
                        <p class="text-xs text-ink-400">Ends {{ \Carbon\Carbon::parse($trial->trial_end)->format('d M Y') }}</p>
                    </div>
                    <div>
                        <a href="{{ route('leads.trials') }}" class="text-xs font-semibold text-gold-600 hover:text-gold-500">Follow up</a>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-ink-400">No trials expiring soon.</div>
            @endforelse
        </x-card>
    </div>
</x-layouts.app>
