<x-layouts.app
    title="Finance Report"
    description="Revenue and expenses overview."
    :breadcrumbs="[['label' => 'Reports'], ['label' => 'Finance']]">

    <x-card>
        <form method="GET" action="{{ route('reports.finance') }}" class="flex flex-wrap items-end gap-3">
            <x-input label="From" type="date" name="from" value="{{ request('from') }}" />
            <x-input label="To" type="date" name="to" value="{{ request('to') }}" />
            <x-button type="submit">
                <x-icon name="search" class="size-4" />
                Run Report
            </x-button>
            <x-button href="{{ route('reports.export', array_merge(['type' => 'finance'], request()->only(['from', 'to']))) }}" variant="outline">
                <x-icon name="download" class="size-4" />
                Export
            </x-button>
        </form>
    </x-card>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <x-stat label="Revenue" :value="money($revenue['total_revenue'])" icon="trending-up" positive />
        <x-stat label="Expenses" :value="money($expenses['total_expenses'])" icon="trending-down" />
        <x-stat label="Net Profit" :value="money($net)" icon="banknotes" :positive="$net >= 0" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-card title="Revenue by Method">
            <dl class="space-y-3 text-sm">
                @foreach (['cash' => 'Cash', 'upi' => 'UPI', 'card' => 'Card', 'bank' => 'Bank Transfer', 'razorpay' => 'Razorpay'] as $key => $label)
                    <div class="flex justify-between">
                        <dt class="text-ink-500 dark:text-ink-400">{{ $label }}</dt>
                        <dd class="font-semibold text-ink-900 dark:text-white">{{ money($revenue[$key] ?? 0) }}</dd>
                    </div>
                @endforeach
                <div class="flex justify-between border-t border-ink-100 pt-3 dark:border-ink-800">
                    <dt class="font-medium text-ink-600 dark:text-ink-300">Total ({!! $revenue['payment_count'] !!} payments)</dt>
                    <dd class="text-lg font-bold text-ink-900 dark:text-white">{{ money($revenue['total_revenue']) }}</dd>
                </div>
            </dl>
        </x-card>

        <x-card title="Expenses by Category">
            @if ($expenses['by_category']->isEmpty())
                <p class="text-sm text-ink-400">No expenses in this period.</p>
            @else
                <dl class="space-y-3 text-sm">
                    @foreach ($expenses['by_category'] as $row)
                        <div class="flex justify-between">
                            <dt class="text-ink-500 dark:text-ink-400">{{ $row['category'] }}</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ money($row['total']) }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </x-card>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-card :padding="false" title="Payments">
            @if ($revenue['payments']->isEmpty())
                <div class="p-6 text-center text-sm text-ink-400">No payments in this period.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="px-5 py-3 font-semibold">Payment</th>
                                <th class="px-5 py-3 font-semibold">Client</th>
                                <th class="px-5 py-3 font-semibold">Date</th>
                                <th class="px-5 py-3 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($revenue['payments'] as $payment)
                                <tr>
                                    <td class="px-5 py-3 font-semibold text-ink-900 dark:text-white">{{ $payment->payment_no }}</td>
                                    <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ $payment->client?->display_name ?? 'Deleted client' }}</td>
                                    <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M') }}</td>
                                    <td class="px-5 py-3 text-right font-bold text-ink-900 dark:text-white">{{ money($payment->final_amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    <x-pagination :model="$revenue['payments']" />
                </div>
            @endif
        </x-card>

        <x-card :padding="false" title="Expenses">
            @if ($expenses['expenses']->isEmpty())
                <div class="p-6 text-center text-sm text-ink-400">No expenses in this period.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="px-5 py-3 font-semibold">Date</th>
                                <th class="px-5 py-3 font-semibold">Category</th>
                                <th class="px-5 py-3 font-semibold">Description</th>
                                <th class="px-5 py-3 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($expenses['expenses'] as $expense)
                                <tr>
                                    <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M') }}</td>
                                    <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ $expense->category?->name ?? '—' }}</td>
                                    <td class="px-5 py-3 max-w-48 !whitespace-normal text-ink-500 dark:text-ink-400">{{ $expense->description ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right font-bold text-ink-900 dark:text-white">{{ money($expense->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    <x-pagination :model="$expenses['expenses']" />
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
