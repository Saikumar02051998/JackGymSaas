<x-layouts.app
    title="Record SaaS Payment"
    description="Manually record a subscription payment for a gym."
    :breadcrumbs="[['label' => 'SaaS', 'url' => route('saas.dashboard')], ['label' => 'Payments', 'url' => route('saas.payments.index')], ['label' => 'Record Payment']]">

    <div class="mx-auto max-w-2xl">
        <x-card>
            <form method="POST" action="{{ route('saas.payments.store') }}" class="space-y-4" x-data="{ planId: '', cycle: 'monthly' }">
                @csrf

                <x-field label="Gym" name="gym_id">
                    <select name="gym_id" class="input" required>
                        <option value="">Select gym...</option>
                        @foreach ($gyms as $gym)
                            <option value="{{ $gym->id }}" {{ old('gym_id') == $gym->id ? 'selected' : '' }}>
                                {{ $gym->name }} ({{ $gym->subscriptionStatusLabel() }})
                            </option>
                        @endforeach
                    </select>
                </x-field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Subscription plan" name="subscription_plan_id">
                        <select name="subscription_plan_id" class="input" x-model="planId" required>
                            <option value="">Select plan...</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('subscription_plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </x-field>
                    <x-field label="Billing cycle" name="billing_cycle">
                        <select name="billing_cycle" class="input" x-model="cycle" required>
                            <option value="monthly" {{ old('billing_cycle', 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly" {{ old('billing_cycle') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </x-field>
                </div>

                <x-input label="Amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}"
                         help="Leave blank to use the plan's price for the selected cycle." />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Period start" name="period_start" type="date" value="{{ old('period_start') }}" help="Optional; defaults to the day after the current expiry." />
                    <x-input label="Invoice ref" name="invoice_ref" value="{{ old('invoice_ref') }}" />
                </div>

                <x-field label="Payment method" name="payment_method">
                    <select name="payment_method" class="input">
                        @foreach (['manual' => 'Manual', 'bank_transfer' => 'Bank transfer', 'cash' => 'Cash', 'cheque' => 'Cheque', 'upi' => 'UPI', 'razorpay' => 'Razorpay'] as $value => $label)
                            <option value="{{ $value }}" {{ old('payment_method', 'manual') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-field>

                <x-field label="Notes" name="notes">
                    <textarea name="notes" rows="3" class="input">{{ old('notes') }}</textarea>
                </x-field>

                <div class="flex justify-end border-t border-ink-100 pt-4 dark:border-ink-800">
                    <x-button type="submit">
                        <x-icon name="save" class="size-4" />
                        Record Payment
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
