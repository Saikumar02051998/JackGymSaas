<x-layouts.app
    title="My Membership"
    description="Your current plan, history and membership status."
    :breadcrumbs="[['label' => 'My Membership']]">

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            @php $active = $client->activeMembership; @endphp

            <x-card>
                @if ($active)
                    <div class="rounded-2xl bg-gradient-to-br from-gold-300 to-gold-500 p-6 text-ink-950 shadow-sm shadow-gold-400/30">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold uppercase tracking-widest text-ink-900/70">Active Plan</p>
                            <x-icon name="sparkles" class="size-5" />
                        </div>
                        <p class="mt-3 text-2xl font-extrabold tracking-tight">{{ $active->plan?->name ?? 'Membership' }}</p>
                        <p class="mt-1 text-sm font-medium text-ink-900/70">
                            {{ \Carbon\Carbon::parse($active->start_date)->format('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($active->end_date)->format('d M Y') }}
                        </p>
                        <p class="mt-4 text-4xl font-extrabold tracking-tight">
                            {{ $active->days_remaining }} <span class="text-base font-bold text-ink-900/70">days left</span>
                        </p>
                    </div>

                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-ink-400">Membership No.</span>
                            <span class="font-semibold text-ink-900 dark:text-white">{{ $active->membership_no }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-ink-400">Amount</span>
                            <span class="font-semibold text-ink-900 dark:text-white">{{ money($active->final_amount) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-ink-400">Payment</span>
                            @if (($active->plan?->name ?? '') === 'Free Trial')
                                <x-badge color="green">Free</x-badge>
                            @else
                                <x-badge :color="match($active->payment_status) { 'paid' => 'green', 'partial' => 'amber', 'pending' => 'blue', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $active->payment_status)) }}</x-badge>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-ink-400">Status</span>
                            <x-badge :color="match($active->status) { 'active' => 'green', 'suspended' => 'amber', 'frozen' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst($active->status) }}</x-badge>
                        </div>
                    </div>

                    @if ($due > 0)
                        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/30">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Amount Due</p>
                                    <p class="mt-1 text-xl font-extrabold text-ink-900 dark:text-white">{{ money($due) }}</p>
                                </div>
                                <x-button href="{{ route('client.payments.checkout') }}">
                                    <x-icon name="banknotes" class="size-4" />
                                    Pay Now
                                </x-button>
                            </div>
                        </div>
                    @endif

                    @if ($active->plan?->features)
                        <div class="mt-5 border-t border-ink-100 pt-4 dark:border-ink-800">
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-ink-400">Plan Features</p>
                            <ul class="space-y-1.5">
                                @foreach ($active->plan->features as $feature)
                                    <li class="flex items-start gap-2 text-sm text-ink-600 dark:text-ink-300">
                                        <x-icon name="check" class="mt-0.5 size-4 shrink-0 text-emerald-500" />
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @else
                    <div class="flex flex-col items-center py-10 text-center">
                        <div class="flex size-14 items-center justify-center rounded-2xl bg-ink-100 text-ink-400 dark:bg-ink-800">
                            <x-icon name="card" class="size-7" />
                        </div>
                        <h3 class="mt-4 text-sm font-bold text-ink-900 dark:text-white">No active membership</h3>
                        <p class="mt-1 max-w-sm text-sm text-ink-400">Contact the front desk to subscribe or renew a plan.</p>
                    </div>
                @endif
            </x-card>
        </div>

        <div class="space-y-6 lg:col-span-2">
            @php
                $history = $client->memberships
                    ->flatMap(fn ($m) => $m->histories->map(fn ($h) => ['membership' => $m, 'history' => $h]))
                    ->sortByDesc(fn ($row) => $row['history']->created_at)
                    ->values();
            @endphp

            @if ($history->isNotEmpty())
                <x-card title="Membership History" :padding="false">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                    <th class="px-5 py-3 font-semibold">Date</th>
                                    <th class="px-5 py-3 font-semibold">Action</th>
                                    <th class="px-5 py-3 font-semibold">Plan</th>
                                    <th class="px-5 py-3 font-semibold">Amount</th>
                                    <th class="px-5 py-3 font-semibold">Valid Until</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                @foreach ($history as $row)
                                    <tr>
                                        <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($row['history']->created_at)->format('d M Y') }}</td>
                                        <td class="px-5 py-3">
                                            <x-badge :color="match($row['history']->action) { 'created', 'activated', 'renewed', 'resumed', 'extended' => 'green', 'cancelled', 'expired', 'suspended' => 'red', 'upgraded' => 'blue', 'downgraded' => 'amber', 'frozen' => 'purple', default => 'gray' }">{{ ucfirst($row['history']->action) }}</x-badge>
                                        </td>
                                        <td class="px-5 py-3 font-medium text-ink-900 dark:text-white">{{ $row['history']->plan?->name ?? $row['membership']->plan?->name ?? '—' }}</td>
                                        <td class="px-5 py-3">{{ $row['history']->amount ? money($row['history']->amount) : '—' }}</td>
                                        <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ $row['history']->new_end_date ? \Carbon\Carbon::parse($row['history']->new_end_date)->format('d M Y') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            @endif

            @if ($client->memberships->isNotEmpty())
                <x-card title="All Memberships" :padding="false">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                    <th class="px-5 py-3 font-semibold">Membership No.</th>
                                    <th class="px-5 py-3 font-semibold">Plan</th>
                                    <th class="px-5 py-3 font-semibold">Period</th>
                                    <th class="px-5 py-3 font-semibold">Amount</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                @foreach ($client->memberships as $membership)
                                    <tr>
                                        <td class="px-5 py-3 font-medium text-ink-900 dark:text-white">{{ $membership->membership_no }}</td>
                                        <td class="px-5 py-3">{{ $membership->plan?->name ?? '—' }}</td>
                                        <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($membership->start_date)->format('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($membership->end_date)->format('d M Y') }}</td>
                                        <td class="px-5 py-3">{{ money($membership->final_amount) }}</td>
                                        <td class="px-5 py-3">
                                            <x-badge :color="match($membership->status) { 'active' => 'green', 'upcoming' => 'blue', 'expired' => 'gray', 'suspended' => 'amber', 'frozen' => 'purple', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst($membership->status) }}</x-badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <x-card title="Available Plans" :padding="false">
            @if ($plans->isEmpty())
                <div class="p-6 text-sm text-ink-400">No plans available right now. Contact the front desk.</div>
            @else
                <div class="grid auto-rows-fr gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($plans as $plan)
                        <div class="flex h-full flex-col rounded-2xl border border-ink-100 p-5 dark:border-ink-800">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-bold text-ink-900 dark:text-white">{{ $plan->name }}</h4>
                                    <p class="mt-0.5 text-xs text-ink-400">{{ $plan->duration_label }}</p>
                                </div>
                                <p class="text-lg font-extrabold text-ink-900 dark:text-white">{{ money($plan->final_amount) }}</p>
                            </div>

                            @if ($plan->description)
                                <p class="mt-2 text-xs leading-relaxed text-ink-500 dark:text-ink-400">{{ $plan->description }}</p>
                            @endif

                            @if ($plan->features)
                                <ul class="mt-3 space-y-1">
                                    @foreach ($plan->features as $feature)
                                        <li class="flex items-start gap-1.5 text-xs text-ink-600 dark:text-ink-300">
                                            <x-icon name="check" class="mt-0.5 size-3.5 shrink-0 text-emerald-500" />
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="mt-auto pt-4">
                                <form method="POST" action="{{ route('client.membership.renew') }}">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <x-button type="submit" class="w-full">
                                        <x-icon name="refresh" class="size-4" />
                                        {{ $active?->plan_id === $plan->id ? 'Renew Plan' : 'Choose Plan' }}
                                    </x-button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
