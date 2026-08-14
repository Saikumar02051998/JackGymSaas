<x-layouts.app
    title="{{ $invoice->invoice_no }}"
    description="Invoice details"
    :breadcrumbs="[['label' => 'My Invoices', 'url' => route('client.invoices')], ['label' => $invoice->invoice_no]]">

    <x-slot name="actions">
        <div class="no-print flex items-center gap-2">
            <x-button href="{{ route('client.invoices') }}" variant="ghost" size="sm">
                <x-icon name="arrow-left" class="size-4" />
                Back
            </x-button>
            <x-button variant="outline" size="sm" onclick="window.print()">
                <x-icon name="print" class="size-4" />
                Print
            </x-button>
        </div>
    </x-slot>

    @php
        $gym = $invoice->gym ?? current_gym();
        $client = $invoice->client;
    @endphp

    <div class="card print-sheet">
        <div class="card-body sm:p-8">
            <x-partials.print-header :title="'INVOICE'" :gym="$gym">
                <p class="mt-2 text-base font-bold text-ink-900">{{ $invoice->invoice_no }}</p>
                <p class="text-xs text-ink-500">Issued: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</p>
                @if ($invoice->due_date)
                    <p class="text-xs text-ink-500">Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</p>
                @endif
                <div class="mt-2 print-block">
                    <x-badge :color="match($invoice->status) { 'paid' => 'green', 'issued' => 'blue', 'draft' => 'gray', 'void' => 'red', 'refunded' => 'purple', default => 'gray' }">{{ ucfirst($invoice->status) }}</x-badge>
                </div>
            </x-partials.print-header>

            <div class="mt-6 flex flex-wrap items-start justify-between gap-6 print-block">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Billed To</p>
                    <p class="mt-1.5 text-base font-semibold text-ink-900">{{ $client?->display_name }}</p>
                    <p class="text-sm text-ink-500">Member ID: {{ $client?->member_id }}</p>
                    @if ($client?->address)
                        <p class="text-sm text-ink-500">{{ $client->address }}</p>
                    @endif
                    @if ($client?->phone)
                        <p class="text-sm text-ink-500">{{ $client->phone }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Membership</p>
                    @if ($invoice->membership?->plan)
                        <p class="mt-1.5 text-sm font-bold text-ink-900">{{ $invoice->membership->plan->name }}</p>
                        <p class="text-sm text-ink-500">
                            {{ \Carbon\Carbon::parse($invoice->membership->start_date)->format('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($invoice->membership->end_date)->format('d M Y') }}
                        </p>
                    @else
                        <p class="mt-1.5 text-sm text-ink-500">—</p>
                    @endif
                </div>
            </div>

            @if ($invoice->items->isNotEmpty())
                <div class="mt-6 overflow-hidden rounded-xl border border-ink-200 print-block print-avoid-break">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-200 bg-ink-50 text-xs uppercase tracking-wider text-ink-500">
                                <th class="px-4 py-2.5 font-semibold">Description</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Qty</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Unit Price</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="px-4 py-2.5 !whitespace-normal font-medium text-ink-900">{{ $item->description }}</td>
                                    <td class="px-4 py-2.5 text-right">{{ $item->quantity }}</td>
                                    <td class="px-4 py-2.5 text-right">{{ money($item->unit_price) }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold">{{ money($item->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-6 flex justify-end print-block">
                <div class="w-full max-w-64 space-y-1.5 border-t-2 border-ink-900/10 pt-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-ink-500">Subtotal</span>
                        <span class="font-medium text-ink-900">{{ money($invoice->subtotal) }}</span>
                    </div>
                    @if ((float) $invoice->discount > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500">Discount</span>
                            <span class="font-medium text-red-500">-{{ money($invoice->discount) }}</span>
                        </div>
                    @endif
                    @if ((float) $invoice->tax > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500">Tax</span>
                            <span class="font-medium text-ink-900">{{ money($invoice->tax) }}</span>
                        </div>
                    @endif
                    <div class="mt-2 flex justify-between border-t border-ink-200 pt-2 print-block">
                        <span class="font-bold text-ink-900">Grand Total</span>
                        <span class="text-lg font-extrabold text-ink-900">{{ money($invoice->grand_total) }}</span>
                    </div>
                </div>
            </div>

            @if ($invoice->payment)
                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-emerald-500/10 px-4 py-3 text-sm print-block">
                    <p class="font-semibold text-emerald-700">
                        Paid via {{ ucfirst($invoice->payment->payment_method) }} on {{ \Carbon\Carbon::parse($invoice->payment->payment_date)->format('d M Y') }}
                    </p>
                    <p class="font-bold text-emerald-700">{{ money($invoice->payment->final_amount) }}</p>
                </div>
            @endif

            @if ($invoice->notes)
                <div class="mt-6 rounded-lg bg-ink-50 px-4 py-3 text-sm text-ink-600 print-block print-avoid-break">
                    <p class="font-semibold">Notes</p>
                    <p class="mt-0.5 whitespace-pre-line">{{ $invoice->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
