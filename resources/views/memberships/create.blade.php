<x-layouts.app
    title="New Membership"
    description="Register a membership for a client."
    :breadcrumbs="[['label' => 'Memberships', 'url' => route('memberships.index')], ['label' => 'New Membership']]">

    <form method="POST" action="{{ route('memberships.store') }}">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Membership Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-select label="Client" name="client_id" required placeholder="Select a client">
                                @foreach ($clients as $c)
                                    <option value="{{ $c->id }}" {{ old('client_id', $client?->id) == $c->id ? 'selected' : '' }}>{{ $c->display_name }} ({{ $c->member_id }})</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="sm:col-span-2">
                            <x-select label="Membership plan" name="plan_id" required placeholder="Select a plan">
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} &middot; {{ $plan->duration_label }} &middot; {{ gym_setting('currency_symbol', '₹') }}{{ number_format($plan->final_amount, 2) }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>
                        <x-input label="Start date" type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" help="Leave as today for an immediate start." />
                        <x-input label="Discount" type="number" step="0.01" min="0" name="discount" value="{{ old('discount', 0) }}" help="Optional discount on top of plan price." />
                        <div class="sm:col-span-2">
                            <x-field label="Notes" name="notes">
                                <textarea name="notes" rows="3" class="input">{{ old('notes') }}</textarea>
                            </x-field>
                        </div>
                    </div>
                </x-card>

                <x-card title="Payment">
                    <label class="flex items-start gap-3 rounded-xl border border-ink-200 p-4 transition-colors has-[:checked]:border-gold-400 has-[:checked]:bg-gold-400/5 dark:border-ink-700">
                        <input type="checkbox" name="collect_payment" value="1" x-model="collectPayment" class="mt-0.5 size-4 rounded border-ink-300 text-gold-500 focus:ring-gold-400" {{ old('collect_payment') ? 'checked' : '' }}>
                        <span>
                            <span class="block text-sm font-semibold text-ink-900 dark:text-white">Collect payment now</span>
                            <span class="mt-0.5 block text-xs text-ink-400">Record the first payment for this membership.</span>
                        </span>
                    </label>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2" x-show="collectPayment" x-cloak>
                        <x-select label="Payment method" name="payment_method" placeholder="Select method">
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                            <option value="upi" {{ old('payment_method') === 'upi' ? 'selected' : '' }}>UPI</option>
                            <option value="online" {{ old('payment_method') === 'online' ? 'selected' : '' }}>Online Gateway</option>
                            <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        </x-select>
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Summary">
                    <div class="flex gap-3">
                        <x-button type="submit" class="flex-1 py-3">
                            <x-icon name="save" class="size-4" />
                            Create Membership
                        </x-button>
                        <a href="{{ route('memberships.index') }}" class="btn-outline">Cancel</a>
                    </div>
                    <p class="mt-4 text-xs leading-relaxed text-ink-400">
                        A membership number is generated automatically. If the start date is in the future, the membership will be marked as upcoming.
                    </p>
                </x-card>
            </div>
        </div>
    </form>
</x-layouts.app>
