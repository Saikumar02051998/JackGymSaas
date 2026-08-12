<x-layouts.app
    title="SaaS Dashboard"
    description="Overview of all gyms and subscription revenue."
    :breadcrumbs="[['label' => 'SaaS']]">

    @php $symbol = saas_setting('currency_symbol', env('CURRENCY_SYMBOL', '₹')); @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat label="Total Gyms" :value="$stats['gyms']" icon="building" positive />
        <x-stat label="Active Subscriptions" :value="$stats['active']" icon="check-badge" positive />
        <x-stat label="On Trial" :value="$stats['trial']" icon="gift" />
        <x-stat label="Expired / Suspended" :value="$stats['expired']" icon="clock" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-card title="Subscriptions Expiring Soon">
            @if ($expiringSoon->isEmpty())
                <p class="py-6 text-center text-sm text-ink-400">No subscriptions expiring in the next 15 days.</p>
            @else
                <ul class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($expiringSoon as $gym)
                        <li class="flex items-center justify-between gap-4 py-3">
                            <div class="min-w-0">
                                <a href="{{ route('saas.gyms.show', $gym) }}" class="font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $gym->name }}</a>
                                <p class="text-xs text-ink-400">{{ $gym->subscriptionStatusLabel() }} · {{ $gym->subscriptionPlan?->name ?? 'No plan' }}</p>
                            </div>
                            <div class="text-right">
                                <x-badge :color="$gym->subscription_expires_at->isPast() ? 'red' : 'amber'">
                                    {{ $gym->subscription_expires_at->diffForHumans() }}
                                </x-badge>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <x-card title="Recent Payments">
            @if ($recentPayments->isEmpty())
                <p class="py-6 text-center text-sm text-ink-400">No subscription payments yet.</p>
            @else
                <ul class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($recentPayments as $payment)
                        <li class="flex items-center justify-between gap-4 py-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-ink-900 dark:text-white">{{ $payment->gym?->name ?? 'Unknown gym' }}</p>
                                <p class="text-xs text-ink-400">{{ $payment->subscriptionPlan?->name }} · {{ ucfirst($payment->billing_cycle) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-ink-900 dark:text-white">{{ $symbol }}{{ number_format($payment->amount, 2) }}</p>
                                <x-badge :color="match($payment->status) { 'paid' => 'green', 'pending' => 'amber', 'failed' => 'red', default => 'gray' }">{{ ucfirst($payment->status) }}</x-badge>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>
</x-layouts.app>
