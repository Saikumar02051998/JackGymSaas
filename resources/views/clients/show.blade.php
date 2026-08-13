<x-layouts.app
    :title="$client->display_name"
    :description="'Member since ' . ($client->joining_date ? \Carbon\Carbon::parse($client->joining_date)->format('d M Y') : '—') . ' &middot; ' . $client->member_id"
    :breadcrumbs="[['label' => 'Clients', 'url' => route('clients.index')], ['label' => $client->display_name]]">

    <x-slot name="actions">
        <form method="POST" action="{{ route('clients.toggle-status', $client) }}" class="inline">
            @csrf
            <x-button type="submit" variant="{{ $client->status === 'active' ? 'outline' : 'success' }}" size="sm">
                {{ $client->status === 'active' ? 'Mark Inactive' : 'Mark Active' }}
            </x-button>
        </form>
        @if (can_manage('clients.edit'))
            <x-button href="{{ route('clients.edit', $client) }}" variant="outline" size="sm">
                <x-icon name="pencil" class="size-4" />
                Edit
            </x-button>
        @endif
        @if (can_manage('memberships.create'))
            <x-button href="{{ route('memberships.create', ['client' => $client->id]) }}" size="sm">
                <x-icon name="plus" class="size-4" />
                New Membership
            </x-button>
        @endif
    </x-slot>

    @if (session('client_password'))
        @push('head')
            <style>
                @media print {
                    .no-print { display: none !important; }
                    body { background: #fff !important; }
                    aside, header, footer, #page-loader { display: none !important; }
                    main > * { display: none !important; }
                    main > .welcome-card { display: block !important; margin: 0 !important; border-width: 1px !important; box-shadow: none !important; }
                }
            </style>
        @endpush

        <div class="welcome-card mb-6 rounded-2xl border-2 border-gold-400/60 bg-gold-400/10 p-6" x-data="{ show: true }" x-show="show">
            @php
                $credential = $client->user?->email ?? $client->phone;
                $loginUrl = rtrim(route('login'), '/');
            @endphp
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gold-500 text-ink-950">
                        <x-icon name="identification" class="size-5" />
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-ink-900 dark:text-white">Welcome card — client login credentials</h2>
                        <p class="mt-0.5 text-sm text-ink-500 dark:text-ink-400">Share these details with the client so they can log in to their portal.</p>
                    </div>
                </div>
                <div class="no-print flex items-center gap-2">
                    <button type="button"
                            x-data="{ copied: false }"
                            x-on:click="navigator.clipboard.writeText(@js('Login URL: ' . $loginUrl . PHP_EOL . 'Username: ' . $credential . PHP_EOL . 'Password: ' . session('client_password'))).then(() => { copied = true; setTimeout(() => copied = false, 2000); })"
                            class="btn-primary btn-sm">
                        <x-icon name="check" class="size-4" x-show="copied" x-cloak />
                        <x-icon name="document-text" class="size-4" x-show="!copied" />
                        <span x-text="copied ? 'Copied!' : 'Copy all'"></span>
                    </button>
                    <button type="button" onclick="window.print()" class="btn-outline btn-sm">
                        <x-icon name="print" class="size-4" />
                        Print
                    </button>
                    <button type="button" @click="show = false" class="btn-ghost btn-sm" title="Dismiss">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-gold-400/40 bg-white p-4 dark:bg-ink-900">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Login URL</p>
                    <p class="mt-1 truncate font-mono text-sm font-semibold text-ink-900 dark:text-white">{{ $loginUrl }}</p>
                </div>
                <div class="rounded-xl border border-gold-400/40 bg-white p-4 dark:bg-ink-900">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Username (email or phone)</p>
                    <p class="mt-1 truncate font-mono text-sm font-semibold text-ink-900 dark:text-white">{{ $credential }}</p>
                </div>
                <div class="rounded-xl border border-gold-400/40 bg-white p-4 dark:bg-ink-900">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Password</p>
                    <p class="mt-1 font-mono text-sm font-bold text-gold-600 dark:text-gold-400">{{ session('client_password') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="flex flex-col gap-6 p-6 sm:flex-row sm:items-center">
            <div class="flex size-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-gold-300 to-gold-500 text-2xl font-extrabold text-ink-950">
                {{ $client->initials }}
            </div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-xl font-bold text-ink-900 dark:text-white">{{ $client->user?->name }}</h2>
                    <x-badge :color="$client->status === 'active' ? 'green' : 'gray'">{{ ucfirst($client->status) }}</x-badge>
                    @if ($client->activeMembership)
                        <x-badge color="gold">Active until {{ \Carbon\Carbon::parse($client->activeMembership->end_date)->format('d M Y') }}</x-badge>
                    @else
                        <x-badge color="red">No active membership</x-badge>
                    @endif
                </div>
                <div class="mt-2 grid gap-x-8 gap-y-1 text-sm text-ink-500 dark:text-ink-400 sm:grid-cols-2 lg:grid-cols-4">
                    <p><span class="font-semibold text-ink-600 dark:text-ink-300">ID:</span> {{ $client->member_id }}</p>
                    <p><span class="font-semibold text-ink-600 dark:text-ink-300">Phone:</span> {{ $client->phone ?? '—' }}</p>
                    <p><span class="font-semibold text-ink-600 dark:text-ink-300">Email:</span> {{ $client->user?->email ?? '—' }}</p>
                    <p><span class="font-semibold text-ink-600 dark:text-ink-300">Coach:</span> {{ $client->trainer?->display_name ?? 'Not assigned' }}</p>
                    <p><span class="font-semibold text-ink-600 dark:text-ink-300">Referral:</span> {{ $client->referral_code ?? '—' }}</p>
                    <p><span class="font-semibold text-ink-600 dark:text-ink-300">Source:</span> {{ $client->lead_source ? ucfirst(str_replace('_', ' ', $client->lead_source)) : '—' }}</p>
                    <p><span class="font-semibold text-ink-600 dark:text-ink-300">Gender:</span> {{ $client->gender ? ucfirst($client->gender) : '—' }}</p>
                    <p><span class="font-semibold text-ink-600 dark:text-ink-300">Age:</span> {{ $client->dob ? \Carbon\Carbon::parse($client->dob)->age . ' yrs' : '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Total Visits" :value="$totalAttendance" icon="calendar-check" />
        <x-stat label="Last Visit" :value="$lastVisit ? \Carbon\Carbon::parse($lastVisit->attendance_date)->format('d M') : '—'" icon="clock" />
        <x-stat label="Current Weight" :value="$client->healthProfile?->weight ? $client->healthProfile->weight . ' kg' : '—'" icon="trending-down" />
        <x-stat label="Goal Weight" :value="$client->healthProfile?->goal_weight ? $client->healthProfile->goal_weight . ' kg' : '—'" icon="target" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Membership History">
                @forelse ($memberships as $membership)
                    <div class="flex items-center justify-between border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                        <div>
                            <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ $membership->plan?->name ?? 'Membership' }}</p>
                            <p class="text-xs text-ink-400">
                                {{ \Carbon\Carbon::parse($membership->start_date)->format('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($membership->end_date)->format('d M Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-ink-900 dark:text-white">{{ money($membership->final_amount) }}</p>
                            <x-badge :color="match($membership->status) { 'active' => 'green', 'expired' => 'red', 'cancelled' => 'red', 'frozen' => 'blue', 'suspended' => 'amber', default => 'gray' }">{{ ucfirst($membership->status) }}</x-badge>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-ink-400">No memberships yet.</div>
                @endforelse
                @if ($memberships->hasPages())
                    <x-pagination :model="$memberships" />
                @endif
            </x-card>

            <x-card title="Recent Attendance">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="py-2.5 pr-4 font-semibold">Date</th>
                                <th class="py-2.5 pr-4 font-semibold">Check-in</th>
                                <th class="py-2.5 pr-4 font-semibold">Check-out</th>
                                <th class="py-2.5 font-semibold">Duration</th>
                                <th class="py-2.5 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @forelse ($attendance as $attendance)
                                <tr>
                                    <td class="py-3 pr-4 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}</td>
                                    <td class="py-3 pr-4 text-ink-600 dark:text-ink-300">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : '—' }}</td>
                                    <td class="py-3 pr-4 text-ink-600 dark:text-ink-300">{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : '—' }}</td>
                                    <td class="py-3 pr-4 text-ink-600 dark:text-ink-300">{{ $attendance->duration_minutes ? $attendance->duration_minutes . ' min' : '—' }}</td>
                                    <td class="py-3">
                                        <x-badge :color="$attendance->status === 'present' ? 'green' : ($attendance->status === 'late' ? 'amber' : ($attendance->status === 'absent' ? 'red' : 'gray'))">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</x-badge>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-sm text-ink-400">No attendance recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($attendance->hasPages())
                    <x-pagination :model="$attendance" />
                @endif
            </x-card>

            <div class="grid gap-6 md:grid-cols-2">
                <x-card title="Recent Payments">
                    @forelse ($client->payments as $payment)
                        <div class="flex items-center justify-between border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                            <div>
                                <a href="{{ route('payments.show', $payment) }}" class="text-sm font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $payment->payment_no }}</a>
                                <p class="text-xs text-ink-400">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-emerald-500">{{ money($payment->final_amount) }}</p>
                                <x-badge :color="$payment->status === 'success' ? 'green' : ($payment->status === 'pending' ? 'amber' : 'red')">{{ ucfirst($payment->status) }}</x-badge>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-sm text-ink-400">No payments yet.</div>
                    @endforelse
                </x-card>

                <x-card title="Recent Invoices">
                    @forelse ($client->invoices as $invoice)
                        <div class="flex items-center justify-between border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                            <div>
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-sm font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $invoice->invoice_no }}</a>
                                <p class="text-xs text-ink-400">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-ink-900 dark:text-white">{{ money($invoice->grand_total) }}</p>
                                <x-badge :color="match($invoice->status) { 'paid' => 'green', 'issued' => 'blue', 'draft' => 'gray', 'void' => 'red', default => 'gray' }">{{ ucfirst($invoice->status) }}</x-badge>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-sm text-ink-400">No invoices yet.</div>
                    @endforelse
                </x-card>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <x-card title="Weight Progress">
                    @forelse ($weightRecords as $record)
                        <div class="flex items-center justify-between border-b border-ink-100 py-2.5 last:border-0 dark:border-ink-800">
                            <p class="text-sm text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($record->record_date)->format('d M Y') }}</p>
                            <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ $record->weight }} kg</p>
                        </div>
                    @empty
                        <div class="py-8 text-center text-sm text-ink-400">No weight records yet.</div>
                    @endforelse
                    @if ($weightRecords->hasPages())
                        <x-pagination :model="$weightRecords" />
                    @endif
                </x-card>

                <x-card title="Body Measurements">
                    @forelse ($client->bodyMeasurements as $measurement)
                        <div class="border-b border-ink-100 py-2.5 last:border-0 dark:border-ink-800">
                            <p class="text-xs font-semibold text-ink-400">{{ \Carbon\Carbon::parse($measurement->record_date)->format('d M Y') }}</p>
                            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-ink-600 dark:text-ink-300">
                                <span>Chest: {{ $measurement->chest ?: '—' }}</span>
                                <span>Waist: {{ $measurement->waist ?: '—' }}</span>
                                <span>Hip: {{ $measurement->hip ?: '—' }}</span>
                                <span>Arms: {{ $measurement->arms ?: '—' }}</span>
                                <span>Thigh: {{ $measurement->thigh ?: '—' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-sm text-ink-400">No measurements yet.</div>
                    @endforelse
                </x-card>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <x-card title="Active Workout Plans">
                    @forelse ($client->workoutPlans as $plan)
                        <div class="flex items-center justify-between border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                            <a href="{{ route('workouts.show', $plan) }}" class="text-sm font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $plan->name }}</a>
                            <x-badge color="green">Active</x-badge>
                        </div>
                    @empty
                        <div class="py-8 text-center text-sm text-ink-400">No active workout plans.</div>
                    @endforelse
                </x-card>

                <x-card title="Active Diet Plans">
                    @forelse ($client->dietPlans as $plan)
                        <div class="flex items-center justify-between border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                            <a href="{{ route('diets.show', $plan) }}" class="text-sm font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $plan->name }}</a>
                            <x-badge color="green">Active</x-badge>
                        </div>
                    @empty
                        <div class="py-8 text-center text-sm text-ink-400">No active diet plans.</div>
                    @endforelse
                </x-card>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <x-card title="Appointments">
                    @forelse ($client->appointments as $appointment)
                        <div class="flex items-center justify-between border-b border-ink-100 py-2.5 last:border-0 dark:border-ink-800">
                            <div>
                                <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ ucfirst($appointment->appointment_type) }}</p>
                                <p class="text-xs text-ink-400">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }} {{ $appointment->appointment_time ? '· ' . \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : '' }}</p>
                            </div>
                            <x-badge :color="$appointment->status === 'scheduled' ? 'blue' : ($appointment->status === 'completed' ? 'green' : 'gray')">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</x-badge>
                        </div>
                    @empty
                        <div class="py-8 text-center text-sm text-ink-400">No appointments.</div>
                    @endforelse
                </x-card>

                <x-card title="PT Sessions">
                    @forelse ($client->ptSessions as $session)
                        <div class="flex items-center justify-between border-b border-ink-100 py-2.5 last:border-0 dark:border-ink-800">
                            <div>
                                <p class="text-sm font-semibold text-ink-900 dark:text-white">Session #{{ $session->session_no }}</p>
                                <p class="text-xs text-ink-400">{{ \Carbon\Carbon::parse($session->session_date)->format('d M Y') }}</p>
                            </div>
                            <x-badge :color="$session->status === 'completed' ? 'green' : ($session->status === 'scheduled' ? 'blue' : 'gray')">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</x-badge>
                        </div>
                    @empty
                        <div class="py-8 text-center text-sm text-ink-400">No PT sessions.</div>
                    @endforelse
                </x-card>
            </div>

            <x-card title="Fitness Goals">
                @forelse ($client->fitnessGoals as $goal)
                    <div class="border-b border-ink-100 py-3 last:border-0 dark:border-ink-800">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $goal->type)) }}</p>
                            <x-badge :color="$goal->status === 'achieved' ? 'green' : 'blue'">{{ ucfirst($goal->status) }}</x-badge>
                        </div>
                        @if ($goal->target_value)
                            <p class="mt-1 text-xs text-ink-400">Progress: {{ $goal->current_value ?? $goal->starting_value }} / {{ $goal->target_value }} ({{ $goal->progress_percent }}%)</p>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-ink-100 dark:bg-ink-800">
                                <div class="h-full rounded-full bg-gold-400" style="width: {{ min(100, (float) $goal->progress_percent) }}%"></div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-ink-400">No active goals.</div>
                @endforelse
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Health Profile">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-ink-400">Height</dt>
                        <dd class="font-semibold text-ink-900 dark:text-white">{{ $client->healthProfile?->height ? $client->healthProfile->height . ' cm' : '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-400">Weight</dt>
                        <dd class="font-semibold text-ink-900 dark:text-white">{{ $client->healthProfile?->weight ? $client->healthProfile->weight . ' kg' : '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-400">BMI</dt>
                        <dd class="font-semibold text-ink-900 dark:text-white">{{ $client->healthProfile?->bmi ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-400">Body Fat</dt>
                        <dd class="font-semibold text-ink-900 dark:text-white">{{ $client->healthProfile?->body_fat ? $client->healthProfile->body_fat . '%' : '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-400">Goal</dt>
                        <dd class="font-semibold text-ink-900 dark:text-white">{{ $client->healthProfile?->fitness_goal ? ucfirst(str_replace('_', ' ', $client->healthProfile->fitness_goal)) : '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-400">Activity</dt>
                        <dd class="font-semibold text-ink-900 dark:text-white">{{ $client->healthProfile?->activity_level ? ucfirst(str_replace('_', ' ', $client->healthProfile->activity_level)) : '—' }}</dd>
                    </div>
                </dl>

                @if ($client->healthProfile?->medical_notes || $client->healthProfile?->injuries || $client->healthProfile?->allergies)
                    <div class="mt-4 space-y-3 border-t border-ink-100 pt-4 dark:border-ink-800">
                        @if ($client->healthProfile?->medical_notes)
                            <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Medical Notes</p>
                            <p class="text-sm text-ink-600 dark:text-ink-300">{{ $client->healthProfile->medical_notes }}</p>
                        @endif
                        @if ($client->healthProfile?->injuries)
                            <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Injuries</p>
                            <p class="text-sm text-ink-600 dark:text-ink-300">{{ $client->healthProfile->injuries }}</p>
                        @endif
                        @if ($client->healthProfile?->allergies)
                            <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Allergies</p>
                            <p class="text-sm text-ink-600 dark:text-ink-300">{{ $client->healthProfile->allergies }}</p>
                        @endif
                    </div>
                @endif

                @if (can_manage('clients.health'))
                    <div class="mt-4">
                        <button @click="$dispatch('open-modal')" class="btn-outline w-full btn-sm">
                            <x-icon name="pencil" class="size-4" />
                            Update Health
                        </button>
                    </div>
                @endif
            </x-card>

            <x-card title="Recent Follow-ups">
                @forelse ($followups as $followup)
                    <div class="flex items-center justify-between border-b border-ink-100 py-2.5 last:border-0 dark:border-ink-800">
                        <div>
                            <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ ucfirst($followup->type) }}</p>
                            <p class="text-xs text-ink-400">{{ \Carbon\Carbon::parse($followup->follow_up_date)->format('d M Y') }}</p>
                        </div>
                        <x-badge :color="match($followup->status) { 'pending' => 'amber', 'completed' => 'green', 'cancelled' => 'red', 'overdue' => 'red', 'rescheduled' => 'blue', default => 'gray' }">{{ ucfirst($followup->status) }}</x-badge>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-ink-400">No follow-ups.</div>
                @endforelse
                @if ($followups->hasPages())
                    <x-pagination :model="$followups" />
                @endif
            </x-card>

            @if ($client->notes)
                <x-card title="Notes">
                    <p class="text-sm leading-relaxed text-ink-600 dark:text-ink-300">{{ $client->notes }}</p>
                </x-card>
            @endif

            @if ($client->documents->isNotEmpty())
                <x-card title="Documents">
                    <ul class="space-y-2">
                        @foreach ($client->documents as $document)
                            <li class="flex items-center gap-2 text-sm">
                                <x-icon name="paper-clip" class="size-4 text-ink-400" />
                                <a href="{{ asset('storage/' . $document->path) }}" target="_blank" class="text-ink-600 hover:text-gold-600 dark:text-ink-300">{{ $document->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </div>
    </div>

    <x-modal title="Update Health Profile">
        <form method="POST" action="{{ route('clients.health', $client) }}" id="health-form" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <x-input label="Height (cm)" type="number" step="0.1" name="height" value="{{ $client->healthProfile?->height }}" />
            <x-input label="Weight (kg)" type="number" step="0.1" name="weight" value="{{ $client->healthProfile?->weight }}" />
            <x-input label="Body Fat (%)" type="number" step="0.1" name="body_fat" value="{{ $client->healthProfile?->body_fat }}" />
            <x-input label="Goal Weight (kg)" type="number" step="0.1" name="goal_weight" value="{{ $client->healthProfile?->goal_weight }}" />
            <x-select label="Fitness Goal" name="fitness_goal" placeholder="Select a goal">
                @foreach (['weight_loss' => 'Weight Loss', 'muscle_gain' => 'Muscle Gain', 'strength' => 'Strength', 'endurance' => 'Endurance', 'general_fitness' => 'General Fitness'] as $value => $label)
                    <option value="{{ $value }}" {{ $client->healthProfile?->fitness_goal === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </x-select>
            <x-select label="Activity Level" name="activity_level" placeholder="Select level">
                @foreach (['sedentary' => 'Sedentary', 'light' => 'Light', 'moderate' => 'Moderate', 'active' => 'Active', 'very_active' => 'Very Active'] as $value => $label)
                    <option value="{{ $value }}" {{ $client->healthProfile?->activity_level === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </x-select>
            <div class="sm:col-span-2">
                <x-field label="Medical Notes" name="medical_notes">
                    <textarea name="medical_notes" rows="2" class="input">{{ $client->healthProfile?->medical_notes }}</textarea>
                </x-field>
            </div>
            <div class="sm:col-span-2">
                <x-field label="Injuries" name="injuries">
                    <textarea name="injuries" rows="2" class="input">{{ $client->healthProfile?->injuries }}</textarea>
                </x-field>
            </div>
            <div class="sm:col-span-2">
                <x-field label="Limitations" name="limitations">
                    <textarea name="limitations" rows="2" class="input">{{ $client->healthProfile?->limitations }}</textarea>
                </x-field>
            </div>
            <div class="sm:col-span-2">
                <x-field label="Allergies" name="allergies">
                    <textarea name="allergies" rows="2" class="input">{{ $client->healthProfile?->allergies }}</textarea>
                </x-field>
            </div>
            <div class="sm:col-span-2">
                <x-field label="Important Notes" name="important_notes">
                    <textarea name="important_notes" rows="2" class="input">{{ $client->healthProfile?->important_notes }}</textarea>
                </x-field>
            </div>
            <x-slot name="footer">
                <x-button type="button" variant="ghost" @click="$dispatch('close-modal')">Cancel</x-button>
                <x-button type="submit" form="health-form">Save Health</x-button>
            </x-slot>
        </form>
    </x-modal>
</x-layouts.app>
