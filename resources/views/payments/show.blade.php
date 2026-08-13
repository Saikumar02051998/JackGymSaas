<x-layouts.app
    title="{{ $payment->payment_no }}"
    description="Payment details"
    :breadcrumbs="[['label' => 'Payments', 'url' => route('payments.index')], ['label' => $payment->payment_no]]">

    @error('payment')
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-600 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ $message }}</div>
    @enderror

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card>
                <div class="flex flex-wrap items-center gap-4">
                    <span class="avatar-lg">{{ $payment->client ? collect(explode(' ', $payment->client->display_name))->take(2)->map(fn ($w) => strtoupper($w[0]))->join('') : '—' }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-ink-900 dark:text-white">{{ $payment->payment_no }}</h2>
                            <x-badge :color="match($payment->status) { 'success' => 'green', 'pending' => 'amber', 'processing' => 'blue', 'failed' => 'red', 'refunded' => 'purple', 'partially_refunded' => 'amber', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $payment->status)) }}</x-badge>
                        </div>
                        <p class="mt-0.5 text-sm text-ink-400">
                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }} &middot; {{ ucfirst($payment->payment_method) }}{{ $payment->transaction_id ? ' &middot; ' . $payment->transaction_id : '' }}
                        </p>
                    </div>
                    <p class="text-2xl font-extrabold text-ink-900 dark:text-white">{{ money($payment->final_amount) }}</p>
                </div>
            </x-card>

            <x-card title="Amount Breakdown" :padding="false">
                <div class="divide-y divide-ink-100 dark:divide-ink-800">
                    <div class="flex justify-between px-5 py-3.5 text-sm">
                        <span class="text-ink-500 dark:text-ink-400">Subtotal</span>
                        <span class="font-semibold text-ink-900 dark:text-white">{{ money($payment->amount) }}</span>
                    </div>
                    <div class="flex justify-between px-5 py-3.5 text-sm">
                        <span class="text-ink-500 dark:text-ink-400">Discount</span>
                        <span class="font-semibold text-red-500">-{{ money($payment->discount) }}</span>
                    </div>
                    <div class="flex justify-between px-5 py-3.5 text-sm">
                        <span class="text-ink-500 dark:text-ink-400">Tax</span>
                        <span class="font-semibold text-ink-900 dark:text-white">{{ money($payment->tax) }}</span>
                    </div>
                    <div class="flex justify-between bg-ink-50 px-5 py-4 dark:bg-ink-800">
                        <span class="text-sm font-semibold text-ink-700 dark:text-ink-200">Total Paid</span>
                        <span class="text-lg font-bold text-ink-900 dark:text-white">{{ money($payment->final_amount) }}</span>
                    </div>
                </div>
            </x-card>

            @if ($payment->transactions->isNotEmpty())
                <x-card title="Transactions" :padding="false">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                    <th class="px-5 py-3 font-semibold">Type</th>
                                    <th class="px-5 py-3 font-semibold">Amount</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                    <th class="px-5 py-3 font-semibold">Gateway</th>
                                    <th class="px-5 py-3 font-semibold">Reference</th>
                                    <th class="px-5 py-3 font-semibold">When</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                @foreach ($payment->transactions as $transaction)
                                    <tr>
                                        <td class="px-5 py-3 font-medium text-ink-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</td>
                                        <td class="px-5 py-3">{{ money($transaction->amount) }}</td>
                                        <td class="px-5 py-3">
                                            <x-badge :color="in_array($transaction->status, ['success', 'authorized', 'captured']) ? 'green' : ($transaction->status === 'failed' ? 'red' : 'amber')">{{ ucfirst($transaction->status) }}</x-badge>
                                        </td>
                                        <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ $transaction->gateway ?? '—' }}</td>
                                        <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ $transaction->gateway_reference ?? '—' }}</td>
                                        <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d M, g:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-card title="Client">
                @if ($payment->client)
                    <a href="{{ route('clients.show', $payment->client_id) }}" class="block rounded-xl bg-ink-50 p-4 transition-colors hover:bg-ink-100 dark:bg-ink-800 dark:hover:bg-ink-700">
                        <p class="font-semibold text-ink-900 dark:text-white">{{ $payment->client->display_name }}</p>
                        <p class="mt-0.5 text-xs text-ink-400">{{ $payment->client->member_id }} · {{ $payment->client->phone ?? '—' }}</p>
                    </a>
                @else
                    <p class="text-sm text-ink-400">Deleted client</p>
                @endif
            </x-card>

            @if ($payment->membership)
                <x-card title="Membership">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Membership</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ $payment->membership->membership_no }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Plan</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ $payment->plan?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Period</dt>
                            <dd class="text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($payment->membership->start_date)->format('d M') }} &rarr; {{ \Carbon\Carbon::parse($payment->membership->end_date)->format('d M Y') }}</dd>
                        </div>
                    </dl>
                </x-card>
            @endif

            @if ($payment->invoice)
                <x-card title="Invoice">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-ink-900 dark:text-white">{{ $payment->invoice->invoice_no }}</p>
                            <p class="text-xs text-ink-400">{{ $payment->invoice->status }}</p>
                        </div>
                        <a href="{{ route('invoices.show', $payment->invoice) }}" class="btn-outline btn-sm">View</a>
                    </div>
                </x-card>
            @endif

            @if (can_manage('payments.refund') && in_array($payment->status, ['success', 'partially_refunded']))
                <x-card title="Refund Payment">
                    <form method="POST" action="{{ route('payments.refund', $payment) }}" data-ajax class="space-y-3">
                        @csrf
                        <x-input label="Amount" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $payment->final_amount) }}" help="Max {{ money($payment->final_amount) }}" />
                        <x-field label="Notes" name="notes">
                            <textarea name="notes" rows="2" class="input">{{ old('notes') }}</textarea>
                        </x-field>
                        <x-button type="submit" variant="danger" class="w-full">
                            <x-icon name="refresh" class="size-4" />
                            Refund
                        </x-button>
                    </form>
                </x-card>
            @endif
        </div>
    </div>
</x-layouts.app>
