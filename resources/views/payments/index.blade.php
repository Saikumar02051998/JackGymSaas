<x-layouts.app
    title="Payments"
    description="Track all member payments."
    :breadcrumbs="[['label' => 'Payments']]">

    <x-slot name="actions">
        @if (can_manage('payments.create'))
            <x-button href="{{ route('payments.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                Record Payment
            </x-button>
        @endif
        <x-button href="{{ route('payments.export', request()->only(['from', 'to'])) }}" variant="outline" size="sm">
            <x-icon name="download" class="size-4" />
            Export
        </x-button>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat label="Total Collected" :value="money($summary['success'])" icon="banknotes" positive />
        <x-stat label="Collected Today" :value="money($summary['today'])" icon="trending-up" positive />
        <x-stat label="Pending" :value="money($summary['pending'])" icon="clock" />
        <x-stat label="Refunded" :value="money($summary['refunded'])" icon="trending-down" />
    </div>

    <x-card :padding="false" class="mt-6">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4 dark:border-ink-800">
            <form method="GET" action="{{ route('payments.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
                <div class="relative min-w-52 flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-ink-400"><x-icon name="search" class="size-4" /></span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search payment no, client, member ID..." class="input pl-9">
                </div>
                <input type="date" name="from" value="{{ request('from') }}" class="input w-auto">
                <input type="date" name="to" value="{{ request('to') }}" class="input w-auto">
                <select name="method" class="input w-auto">
                    <option value="all" {{ request('method', 'all') === 'all' ? 'selected' : '' }}>All methods</option>
                    @foreach (['cash' => 'Cash', 'upi' => 'UPI', 'card' => 'Card', 'neft' => 'NEFT', 'cheque' => 'Cheque', 'razorpay' => 'Razorpay', 'wallet' => 'Wallet'] as $value => $label)
                        <option value="{{ $value }}" {{ request('method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="input w-auto">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All statuses</option>
                    @foreach (['success' => 'Success', 'pending' => 'Pending', 'processing' => 'Processing', 'failed' => 'Failed', 'refunded' => 'Refunded', 'partially_refunded' => 'Partially Refunded'] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <x-button type="submit">Filter</x-button>
            </form>
        </div>

        @if ($payments->isEmpty())
            <div class="p-8">
                <x-empty-state icon="banknotes" title="No payments found" message="Recorded payments will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Payment No</th>
                            <th class="px-5 py-3 font-semibold">Client</th>
                            <th class="px-5 py-3 font-semibold">Plan</th>
                            <th class="px-5 py-3 font-semibold">Method</th>
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Amount</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($payments as $payment)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4 font-semibold text-ink-900 dark:text-white">{{ $payment->payment_no }}</td>
                                <td class="px-5 py-4">
                                    @if ($payment->client)
                                        <a href="{{ route('clients.show', $payment->client_id) }}" class="font-medium text-ink-900 hover:text-gold-600 dark:text-white">{{ $payment->client->display_name }}</a>
                                        <p class="text-xs text-ink-400">{{ $payment->client->member_id }}</p>
                                    @else
                                        <span class="text-ink-400">Deleted client</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $payment->plan?->name ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <x-badge color="gray">{{ ucfirst($payment->payment_method) }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                <td class="px-5 py-4 font-bold text-ink-900 dark:text-white">{{ money($payment->final_amount) }}</td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($payment->status) { 'success' => 'green', 'pending' => 'amber', 'processing' => 'blue', 'failed' => 'red', 'refunded' => 'purple', 'partially_refunded' => 'amber', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $payment->status)) }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('payments.show', $payment) }}" class="btn-outline btn-sm">View</a>
                                </td>
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
