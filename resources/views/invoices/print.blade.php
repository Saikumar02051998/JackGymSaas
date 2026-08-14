<x-layouts.guest title="Invoice {{ $invoice->invoice_no }}">
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="no-print mb-6 flex justify-end">
            <button type="button" onclick="window.print()" class="btn-primary">
                <x-icon name="download" class="size-4" />
                Print
            </button>
        </div>

        <div class="card print-sheet">
            <div class="card-body sm:p-8">
                <x-partials.print-header :title="'INVOICE'" :gym="$invoice->gym">
                    <p class="mt-2 text-base font-bold text-ink-900">{{ $invoice->invoice_no }}</p>
                    <p class="text-xs text-ink-500">Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</p>
                    @if ($invoice->due_date)
                        <p class="text-xs text-ink-500">Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</p>
                    @endif
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-ink-500">Status: {{ ucfirst($invoice->status) }}</p>
                </x-partials.print-header>

                <div class="mt-6 flex flex-wrap items-start justify-between gap-6 print-block">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Billed To</p>
                        @if ($invoice->client)
                            <p class="mt-1.5 text-base font-semibold text-ink-900">{{ $invoice->client->display_name }}</p>
                            <p class="mt-0.5 text-sm text-ink-500">{{ $invoice->client->member_id }}</p>
                            @if ($invoice->client->phone)
                                <p class="text-sm text-ink-500">{{ $invoice->client->phone }}</p>
                            @endif
                        @else
                            <p class="mt-1.5 text-base font-semibold text-ink-400">Deleted client</p>
                        @endif
                    </div>
                    @if ($invoice->membership?->plan)
                        <div class="text-right">
                            <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Membership</p>
                            <p class="mt-1.5 text-sm font-semibold text-ink-900">{{ $invoice->membership->plan->name }}</p>
                            <p class="text-xs text-ink-500">
                                {{ \Carbon\Carbon::parse($invoice->membership->start_date)->format('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($invoice->membership->end_date)->format('d M Y') }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="mt-6 overflow-hidden rounded-xl border border-ink-200 print-block print-avoid-break">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-200 bg-ink-50 text-xs uppercase tracking-wider text-ink-500">
                                <th class="px-4 py-2.5 font-semibold">Description</th>
                                <th class="px-4 py-2.5 text-center font-semibold">Qty</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Unit Price</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="px-4 py-2.5 text-ink-900">{{ $item->description }}</td>
                                    <td class="px-4 py-2.5 text-center">{{ $item->quantity }}</td>
                                    <td class="px-4 py-2.5 text-right">{{ money($item->unit_price) }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-ink-900">{{ money($item->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 ml-auto w-full max-w-64 space-y-1.5 text-sm print-block print-avoid-break">
                    <div class="flex justify-between">
                        <span class="text-ink-500">Subtotal</span>
                        <span class="font-semibold text-ink-900">{{ money($invoice->subtotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-500">Discount</span>
                        <span class="font-semibold text-red-500">-{{ money($invoice->discount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-500">Tax</span>
                        <span class="font-semibold text-ink-900">{{ money($invoice->tax) }}</span>
                    </div>
                    <div class="mt-2 flex justify-between border-t-2 border-ink-900/10 pt-2 print-block">
                        <span class="text-base font-bold text-ink-900">Grand Total</span>
                        <span class="text-xl font-extrabold text-ink-900">{{ money($invoice->grand_total) }}</span>
                    </div>
                </div>

                @if ($invoice->notes)
                    <div class="mt-6 rounded-lg bg-ink-50 px-4 py-3 text-sm text-ink-600 print-block print-avoid-break">
                        <p class="font-semibold">Notes</p>
                        <p class="mt-0.5 whitespace-pre-line">{{ $invoice->notes }}</p>
                    </div>
                @endif

                <div class="mt-10 flex items-end justify-between text-xs text-ink-500 print-block">
                    <div>
                        <p class="border-t border-ink-300 pt-1">Customer Signature</p>
                    </div>
                    <div class="text-right">
                        <p class="border-t border-ink-300 pt-1">Authorized Signature</p>
                    </div>
                </div>

                <p class="mt-8 border-t border-ink-100 pt-4 text-center text-xs text-ink-400">
                    Thank you for training with {{ $invoice->gym?->name ?? 'us' }}! &middot; {{ now()->format('Y') }}
                </p>
            </div>
        </div>
    </div>
</x-layouts.guest>
