<x-layouts.app
    title="My Invoices"
    description="View your invoices and payment status."
    :breadcrumbs="[['label' => 'My Invoices']]">

    <x-card>
        @if ($invoices->isEmpty())
            <x-empty-state icon="document" title="No invoices yet" message="Invoices generated for your memberships will appear here." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Invoice No.</th>
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Due Date</th>
                            <th class="px-5 py-3 font-semibold">Membership</th>
                            <th class="px-5 py-3 text-right font-semibold">Total</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td class="px-5 py-3 font-medium text-ink-900 dark:text-white">{{ $invoice->invoice_no }}</td>
                                <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '—' }}</td>
                                <td class="px-5 py-3">{{ $invoice->membership?->plan?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-ink-900 dark:text-white">{{ money($invoice->grand_total) }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :color="match($invoice->status) { 'paid' => 'green', 'issued' => 'blue', 'draft' => 'gray', 'void' => 'red', 'refunded' => 'purple', default => 'gray' }">{{ ucfirst($invoice->status) }}</x-badge>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('client.invoices.show', $invoice) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gold-600 hover:text-gold-500">
                                        View
                                        <x-icon name="chevron-right" class="size-3.5" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-pagination :model="$invoices" />
        @endif
    </x-card>
</x-layouts.app>
