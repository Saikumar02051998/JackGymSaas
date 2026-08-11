<x-layouts.app
    title="Record Payment"
    description="Record a payment for a member."
    :breadcrumbs="[['label' => 'Payments', 'url' => route('payments.index')], ['label' => 'Record Payment']]">

    @php
        $membershipMap = $memberships->mapWithKeys(fn ($m) => [$m->id => (float) $m->final_amount]);
    @endphp

    <form method="GET" action="{{ route('payments.create') }}" id="client-picker">
        <x-card title="1 · Select Client">
            <x-select label="Client" name="client_id" placeholder="Select a client to continue" x-on:change="$el.closest('form').submit()">
                <option value="" {{ ! $selectedClient ? 'selected' : '' }}>Select a client...</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" {{ $selectedClient?->id == $client->id ? 'selected' : '' }}>{{ $client->display_name }} ({{ $client->member_id }})</option>
                @endforeach
            </x-select>
        </x-card>
    </form>

    @if ($selectedClient)
        <form method="POST" action="{{ route('payments.store') }}" x-data="paymentForm(@js($membershipMap))">
            @csrf
            <input type="hidden" name="client_id" value="{{ $selectedClient->id }}">

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <x-card title="2 · Payment Details">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-select label="Membership" name="membership_id" placeholder="No membership selected" x-on:change="prefill($event.target.value)">
                                    <option value="">No membership (general payment)</option>
                                    @foreach ($memberships as $membership)
                                        <option value="{{ $membership->id }}" {{ old('membership_id') == $membership->id ? 'selected' : '' }}>
                                            {{ $membership->membership_no }} · {{ $membership->plan?->name ?? 'Plan' }} · {{ money($membership->final_amount) }}
                                        </option>
                                    @endforeach
                                </x-select>
                                <p class="mt-1 text-xs text-ink-400">Choosing a membership pre-fills the amount owed.</p>
                            </div>
                            <x-input label="Amount" type="number" step="0.01" min="0" name="amount" x-model.number="amount" value="{{ old('amount') }}" required />
                            <x-input label="Discount" type="number" step="0.01" min="0" name="discount" x-model.number="discount" value="{{ old('discount', 0) }}" />
                            <x-select label="Payment method" name="payment_method" required>
                                @foreach (['cash' => 'Cash', 'upi' => 'UPI', 'card' => 'Card', 'neft' => 'NEFT', 'cheque' => 'Cheque', 'wallet' => 'Wallet'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_method', 'cash') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </x-select>
                            <x-input label="Transaction ID" name="transaction_id" value="{{ old('transaction_id') }}" placeholder="Optional reference" />
                            <x-input label="Payment date" type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" />
                            <div class="sm:col-span-2">
                                <x-field label="Notes" name="notes">
                                    <textarea name="notes" rows="3" class="input">{{ old('notes') }}</textarea>
                                </x-field>
                            </div>
                        </div>
                    </x-card>
                </div>

                <div class="space-y-6">
                    <x-card title="Summary">
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-ink-400">Amount</dt>
                                <dd class="font-semibold text-ink-900 dark:text-white">{{ gym_setting('currency_symbol', '₹') }}<span x-text="amount.toFixed(2)"></span></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-ink-400">Discount</dt>
                                <dd class="font-semibold text-red-500">-{{ gym_setting('currency_symbol', '₹') }}<span x-text="discount.toFixed(2)"></span></dd>
                            </div>
                            <div class="flex justify-between border-t border-ink-100 pt-3 dark:border-ink-800">
                                <dt class="font-medium text-ink-600 dark:text-ink-300">Total</dt>
                                <dd class="text-lg font-bold text-ink-900 dark:text-white">{{ gym_setting('currency_symbol', '₹') }}<span x-text="total.toFixed(2)"></span></dd>
                            </div>
                        </dl>
                    </x-card>

                    <div class="flex gap-3">
                        <x-button type="submit" class="flex-1 py-3">
                            <x-icon name="save" class="size-4" />
                            Record Payment
                        </x-button>
                        <a href="{{ route('payments.index') }}" class="btn-outline">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    @else
        <x-card>
            <div class="p-6 text-center text-sm text-ink-400">Select a client above to continue recording the payment.</div>
        </x-card>
    @endif

    <script>
        function paymentForm(membershipMap) {
            return {
                membershipMap,
                amount: 0,
                discount: 0,
                prefill(id) {
                    const a = this.membershipMap[id];
                    if (a !== undefined) {
                        this.amount = a;
                    }
                },
                get total() {
                    return Math.max((this.amount || 0) - (this.discount || 0), 0);
                },
            };
        }
    </script>
</x-layouts.app>
