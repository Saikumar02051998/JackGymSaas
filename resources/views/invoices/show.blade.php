<x-layouts.app
    title="{{ $invoice->invoice_no }}"
    description="Invoice details"
    :breadcrumbs="[['label' => 'Invoices', 'url' => route('invoices.index')], ['label' => $invoice->invoice_no]]">

    @error('invoice')
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-600 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ $message }}</div>
    @enderror

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-ink-900 dark:text-white">{{ $invoice->invoice_no }}</h2>
                        <p class="mt-0.5 text-sm text-ink-400">Issued {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}{{ $invoice->due_date ? ' · Due ' . \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '' }}</p>
                    </div>
                    <x-badge :color="match($invoice->status) { 'paid' => 'green', 'issued' => 'blue', 'draft' => 'gray', 'void' => 'gray', 'refunded' => 'purple', default => 'gray' }">{{ ucfirst($invoice->status) }}</x-badge>
                </div>
            </x-card>

            <x-card title="Items" :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="px-5 py-3 font-semibold">Description</th>
                                <th class="px-5 py-3 font-semibold">Qty</th>
                                <th class="px-5 py-3 font-semibold">Unit Price</th>
                                <th class="px-5 py-3 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="px-5 py-3 text-ink-900 dark:text-white">{{ $item->description }}</td>
                                    <td class="px-5 py-3">{{ $item->quantity }}</td>
                                    <td class="px-5 py-3">{{ money($item->unit_price) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-ink-900 dark:text-white">{{ money($item->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-ink-100 dark:border-ink-800">
                                <td colspan="3" class="px-5 py-3 text-right text-sm text-ink-500 dark:text-ink-400">Subtotal</td>
                                <td class="px-5 py-3 text-right text-sm font-semibold text-ink-900 dark:text-white">{{ money($invoice->subtotal) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-5 py-3 text-right text-sm text-ink-500 dark:text-ink-400">Discount</td>
                                <td class="px-5 py-3 text-right text-sm font-semibold text-red-500">-{{ money($invoice->discount) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-5 py-3 text-right text-sm text-ink-500 dark:text-ink-400">Tax</td>
                                <td class="px-5 py-3 text-right text-sm font-semibold text-ink-900 dark:text-white">{{ money($invoice->tax) }}</td>
                            </tr>
                            <tr class="bg-ink-50 dark:bg-ink-800">
                                <td colspan="3" class="px-5 py-4 text-right text-sm font-bold text-ink-900 dark:text-white">Grand Total</td>
                                <td class="px-5 py-4 text-right text-lg font-extrabold text-ink-900 dark:text-white">{{ money($invoice->grand_total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>

            @if ($invoice->notes)
                <x-card title="Notes">
                    <p class="whitespace-pre-line text-sm leading-relaxed text-ink-600 dark:text-ink-300">{{ $invoice->notes }}</p>
                </x-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-card title="Client">
                <a href="{{ route('clients.show', $invoice->client_id) }}" class="block rounded-xl bg-ink-50 p-4 transition-colors hover:bg-ink-100 dark:bg-ink-800 dark:hover:bg-ink-700">
                    <p class="font-semibold text-ink-900 dark:text-white">{{ $invoice->client->display_name }}</p>
                    <p class="mt-0.5 text-xs text-ink-400">{{ $invoice->client->member_id }} · {{ $invoice->client->phone ?? '—' }}</p>
                </a>
            </x-card>

            @if ($invoice->membership)
                <x-card title="Membership">
                    <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ $invoice->membership->membership_no }}</p>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">{{ $invoice->membership->plan?->name ?? '—' }}</p>
                </x-card>
            @endif

            @if ($invoice->payment)
                <x-card title="Payment">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ $invoice->payment->payment_no }}</p>
                            <p class="text-xs text-ink-400">{{ $invoice->payment->payment_method }} · {{ $invoice->payment->status }}</p>
                        </div>
                        <a href="{{ route('payments.show', $invoice->payment) }}" class="btn-outline btn-sm">View</a>
                    </div>
                </x-card>
            @endif

            @if (can_manage('invoices.manage') && in_array($invoice->status, ['draft', 'issued']))
                <x-card title="Actions">
                    <div class="space-y-3">
                        <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn-outline w-full">
                            <x-icon name="document-text" class="size-4" />
                            Print Invoice
                        </a>
                        <form method="POST" action="{{ route('invoices.email', $invoice) }}">
                            @csrf
                            <x-button type="submit" variant="outline" class="w-full">
                                <x-icon name="mail" class="size-4" />
                                Email to Client
                            </x-button>
                        </form>
                        <form method="POST" action="{{ route('invoices.paid', $invoice) }}">
                            @csrf
                            <x-button type="submit" variant="success" class="w-full">
                                <x-icon name="check" class="size-4" />
                                Mark as Paid
                            </x-button>
                        </form>
                        <form method="POST" action="{{ route('invoices.void', $invoice) }}"
                              x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Void invoice?', message: 'This will void {{ $invoice->invoice_no }}.', confirmText: 'Void' } })">
                            @csrf
                            <x-button type="submit" variant="ghost" class="w-full !text-red-500">Void Invoice</x-button>
                        </form>
                    </div>
                </x-card>
            @else
                <x-card title="Actions">
                    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn-outline w-full">
                        <x-icon name="document-text" class="size-4" />
                        Print Invoice
                    </a>
                </x-card>
            @endif
        </div>
    </div>
</x-layouts.app>
