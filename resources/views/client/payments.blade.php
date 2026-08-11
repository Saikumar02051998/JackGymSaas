<x-layouts.app
    title="My Payments"
    description="Your payment history and outstanding dues."
    :breadcrumbs="[['label' => 'My Payments']]">

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
        <x-stat label="Total Paid" :value="money($stats['total_paid'])" icon="banknotes" />
        <x-stat label="Payments Made" :value="$stats['payments_count']" icon="check-badge" />
        <x-stat label="Pending Due" :value="money($stats['pending'])" icon="clock" />
    </div>

    <x-card title="Payment History" class="mt-6">
        @if ($payments->isEmpty())
            <x-empty-state icon="banknotes" title="No payments yet" message="Your payment records will appear here." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Payment No.</th>
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Plan</th>
                            <th class="px-5 py-3 font-semibold">Method</th>
                            <th class="px-5 py-3 text-right font-semibold">Amount</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($payments as $payment)
                            <tr>
                                <td class="px-5 py-3 font-medium text-ink-900 dark:text-white">{{ $payment->payment_no }}</td>
                                <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                <td class="px-5 py-3">{{ $payment->plan?->name ?? '—' }}</td>
                                <td class="px-5 py-3 capitalize">{{ $payment->payment_method }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-ink-900 dark:text-white">{{ money($payment->final_amount) }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :color="match($payment->status) { 'success' => 'green', 'pending' => 'amber', 'processing' => 'blue', 'failed' => 'red', 'refunded', 'partially_refunded' => 'purple', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $payment->status)) }}</x-badge>
                                </td>
                                <td class="px-5 py-3">
                                    @if ($payment->invoice)
                                        <a href="{{ route('client.invoices.show', $payment->invoice) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gold-600 hover:text-gold-500">
                                            <x-icon name="document" class="size-4" />
                                            {{ $payment->invoice->invoice_no }}
                                        </a>
                                    @else
                                        <span class="text-ink-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-pagination :model="$payments" />
        @endif
    </x-card>
</x-layouts.app>
