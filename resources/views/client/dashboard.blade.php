<x-layouts.app
    title="My Dashboard"
    description="Welcome back, {{ $client->display_name }}. Here is your fitness overview."
    :breadcrumbs="[['label' => 'Dashboard']]">

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Total Check-ins" :value="$stats['check_ins']" icon="calendar-check" />
        <x-stat label="This Month" :value="$stats['this_month']" icon="trending-up" />
        <x-stat label="Active Workouts" :value="$stats['active_workouts']" icon="dumbbell" />
        <x-stat label="Active Diets" :value="$stats['active_diets']" icon="apple" />
    </div>

    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Upcoming Appointments" :value="$stats['upcoming_appointments']" icon="calendar" />
        <x-stat label="Pending Due" :value="money($stats['pending_due'])" icon="banknotes" />
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <x-card title="Weight Trend" class="lg:col-span-2">
            @if ($weightRecords->isEmpty())
                <div class="flex h-64 items-center justify-center text-sm text-ink-400">Log your weight on the Progress page to see your trend here.</div>
            @else
                <div class="h-64">
                    <canvas x-data="chart($el)" x-init="init()"
                            data-chart='{!! json_encode([
                                'type' => 'line',
                                'data' => [
                                    'labels' => $weightRecords->pluck('record_date')->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M')),
                                    'datasets' => [
                                        ['label' => 'Weight (kg)', 'data' => $weightRecords->pluck('weight')->map(fn ($w) => (float) $w), 'borderColor' => '#d4a63c', 'backgroundColor' => 'rgba(212,166,60,0.12)', 'fill' => true, 'tension' => 0.35],
                                    ],
                                ],
                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'></canvas>
                </div>
            @endif
        </x-card>

        <x-card title="Membership">
            @if ($client->activeMembership)
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">{{ $client->activeMembership->plan?->name ?? 'Membership' }}</p>
                <p class="mt-1 text-sm text-ink-600 dark:text-ink-300">
                    {{ \Carbon\Carbon::parse($client->activeMembership->start_date)->format('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($client->activeMembership->end_date)->format('d M Y') }}
                </p>
                <p class="mt-3 text-3xl font-bold text-ink-900 dark:text-white">
                    {{ $client->activeMembership->days_remaining }} <span class="text-base font-semibold text-ink-400">days left</span>
                </p>
                <div class="mt-2">
                    <x-badge :color="$client->activeMembership->payment_status === 'paid' ? 'green' : 'amber'">{{ ucfirst(str_replace('_', ' ', $client->activeMembership->payment_status)) }}</x-badge>
                </div>
                <div class="mt-5 border-t border-ink-100 pt-4 dark:border-ink-800">
                    <a href="{{ route('client.membership') }}" class="btn-outline w-full justify-center">
                        <x-icon name="card" class="size-4" />
                        View Membership
                    </a>
                </div>
            @else
                <div class="py-6 text-center text-sm text-ink-400">No active membership.</div>
                <a href="{{ route('client.membership') }}" class="btn-outline w-full justify-center">
                    <x-icon name="card" class="size-4" />
                    View Membership
                </a>
            @endif
        </x-card>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <x-card title="Today's Check-in">
            @if ($todayAttendance)
                <div class="flex items-center gap-4">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-500">
                        <x-icon name="check-badge" class="size-6" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-ink-900 dark:text-white">Checked in today</p>
                        <p class="text-xs text-ink-400">
                            In: {{ $todayAttendance->check_in ?? '—' }}
                            @if ($todayAttendance->check_out)
                                · Out: {{ $todayAttendance->check_out }}
                            @endif
                            @if ($todayAttendance->duration_minutes)
                                · {{ $todayAttendance->duration_minutes }} min
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-4">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-ink-100 text-ink-400 dark:bg-ink-800">
                        <x-icon name="clock" class="size-6" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-ink-900 dark:text-white">No check-in yet today</p>
                        <p class="text-xs text-ink-400">Present your membership card at reception on your next visit.</p>
                    </div>
                </div>
            @endif
        </x-card>

        <x-card title="Announcements">
            @forelse ($announcements as $announcement)
                <div class="border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                    <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ $announcement->title }}</p>
                    <p class="mt-0.5 line-clamp-2 text-xs text-ink-500 dark:text-ink-400">{{ $announcement->message }}</p>
                </div>
            @empty
                <div class="py-6 text-center text-sm text-ink-400">No announcements right now.</div>
            @endforelse
        </x-card>
    </div>
</x-layouts.app>
