<x-layouts.app
    title="SaaS Payments"
    description="Track all subscription payments from gyms."
    :breadcrumbs="[['label' => 'SaaS', 'url' => route('saas.dashboard')], ['label' => 'Payments']]">

    <x-slot name="actions">
        @if (auth()->user()->hasPermission('saas.payments.create'))
            <x-button href="{{ route('saas.payments.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                Record Payment
            </x-button>
        @endif
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-stat label="Collected" :value="money($summary['paid'])" icon="banknotes" positive />
        <x-stat label="Pending" :value="money($summary['pending'])" icon="clock" />
        <x-stat label="Failed" :value="money($summary['failed'])" icon="trending-down" />
    </div>

    <x-card :padding="false" class="mt-6">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4 dark:border-ink-800">
            <form method="GET" action="{{ route('saas.payments.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
                <select name="gym_id" class="input w-auto max-w-64">
                    <option value="">All gyms</option>
                    @foreach ($gyms as $gym)
                        <option value="{{ $gym->id }}" {{ request('gym_id') == $gym->id ? 'selected' : '' }}>{{ $gym->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="input w-auto">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All statuses</option>
                    @foreach (['paid' => 'Paid', 'pending' => 'Pending', 'failed' => 'Failed', 'refunded' => 'Refunded'] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <x-button type="submit">Filter</x-button>
            </form>
        </div>

        @if ($payments->isEmpty())
            <div class="p-8">
                <x-empty-state icon="banknotes" title="No payments found" message="Recorded subscription payments will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Gym</th>
                            <th class="px-5 py-3 font-semibold">Plan</th>
                            <th class="px-5 py-3 font-semibold">Cycle</th>
                            <th class="px-5 py-3 font-semibold">Period</th>
                            <th class="px-5 py-3 font-semibold">Method</th>
                            <th class="px-5 py-3 font-semibold">Amount</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($payments as $payment)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4">
                                    <a href="{{ route('saas.gyms.show', $payment->gym_id) }}" class="font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $payment->gym?->name ?? '—' }}</a>
                                    <p class="text-xs text-ink-400">{{ $payment->invoice_ref ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $payment->subscriptionPlan?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ ucfirst($payment->billing_cycle) }}</td>
                                <td class="px-5 py-4 text-xs text-ink-600 dark:text-ink-300">{{ $payment->period_start->format('d M Y') }} – {{ $payment->period_end->format('d M Y') }}</td>
                                <td class="px-5 py-4"><x-badge color="gray">{{ ucfirst($payment->payment_method) }}</x-badge></td>
                                <td class="px-5 py-4 font-bold text-ink-900 dark:text-white">{{ money($payment->amount) }}</td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($payment->status) { 'paid' => 'green', 'pending' => 'amber', 'failed' => 'red', 'refunded' => 'purple', default => 'gray' }">{{ ucfirst($payment->status) }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $payment->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$payments" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
