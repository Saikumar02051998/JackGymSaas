<x-layouts.app
    title="Subscription & Billing"
    description="Manage your gym's SaaS subscription."
    :breadcrumbs="[['label' => 'Subscription & Billing']]">

    @php
        $symbol = $gym->currency_symbol;
        $active = $gym->isSubscriptionActive();
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card title="Current Subscription">
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Status</span>
                    <x-badge :color="$active ? ($gym->subscription_status === 'trial' ? 'blue' : 'green') : 'red'">{{ $gym->subscriptionStatusLabel() }}</x-badge>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Plan</span>
                    <span class="font-semibold text-ink-900 dark:text-white">{{ $gym->subscriptionPlan?->name ?? 'No plan' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Billing cycle</span>
                    <span class="font-medium text-ink-900 dark:text-white">{{ $gym->subscription_billing_cycle ? ucfirst($gym->subscription_billing_cycle) : '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Expires</span>
                    <span class="font-medium text-ink-900 dark:text-white">{{ $gym->subscription_expires_at ? $gym->subscription_expires_at->format('d M Y') : '—' }}</span>
                </div>

                @if ($active)
                    <div class="rounded-2xl bg-emerald-400/10 p-3 text-sm text-emerald-600 dark:text-emerald-400">
                        Your subscription is active. Renew to extend it beyond {{ $gym->subscription_expires_at?->format('d M Y') }}.
                    </div>
                @else
                    <div class="rounded-2xl bg-red-400/10 p-3 text-sm text-red-600 dark:text-red-400">
                        Your subscription is no longer active. Renew now to restore access for your gym.
                    </div>
                @endif
            </div>
        </x-card>

        <div class="lg:col-span-2">
            @if (auth()->user()->isOwner())
                <x-card title="Renew / Upgrade Subscription">
                    <form method="POST" action="{{ route('subscription.order') }}" class="space-y-4" x-data="{ planId: '', cycle: '{{ $gym->subscription_billing_cycle ?? 'monthly' }}' }">
                        @csrf

                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($plans as $plan)
                                <label class="cursor-pointer rounded-2xl border border-ink-200 p-4 transition-colors has-[:checked]:border-gold-400 has-[:checked]:bg-gold-400/5 dark:border-ink-700"
                                       :class="planId === '{{ $plan->id }}' ? 'border-gold-400 bg-gold-400/5' : ''">
                                    <input type="radio" name="subscription_plan_id" value="{{ $plan->id }}" class="hidden" x-model="planId">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-ink-900 dark:text-white">{{ $plan->name }}</span>
                                        <span x-show="planId === '{{ $plan->id }}'" x-cloak><x-icon name="check" class="size-5 text-gold-500" /></span>
                                    </div>
                                    <p class="mt-1 text-xs text-ink-400">
                                        @if ($plan->isTrial())
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">Free</span>
                                            · expires after trial period
                                        @else
                                            <span class="font-semibold text-ink-600 dark:text-ink-300">{{ $symbol }}{{ number_format($plan->price_monthly, 2) }}/mo</span>
                                            · {{ $symbol }}{{ number_format($plan->price_yearly, 2) }}/yr
                                        @endif
                                    </p>
                                    @if ($plan->description)
                                        <p class="mt-2 text-xs leading-relaxed text-ink-400">{{ $plan->description }}</p>
                                    @endif
                                </label>
                            @endforeach
                        </div>

                        <x-field label="Billing cycle" name="billing_cycle" x-show="!isTrial(planId)" x-cloak>
                            <select name="billing_cycle" class="input" x-model="cycle">
                                <option value="monthly" :selected="cycle === 'monthly'">Monthly</option>
                                <option value="yearly" :selected="cycle === 'yearly'">Yearly</option>
                            </select>
                        </x-field>

                        <div class="flex items-center justify-between rounded-2xl bg-ink-50 p-4 dark:bg-ink-800">
                            <span class="text-sm font-semibold text-ink-900 dark:text-white">Amount due</span>
                            <span class="text-xl font-extrabold text-ink-900 dark:text-white" x-show="!isTrial(planId)" x-text="'{{ $symbol }}' + amount(planId, cycle)">{{ $symbol }}0.00</span>
                            <span class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400" x-show="isTrial(planId)" x-cloak>Free</span>
                        </div>

                        <div class="flex justify-end border-t border-ink-100 pt-4 dark:border-ink-800">
                            <x-button type="submit">
                                <x-icon name="card" class="size-4" />
                                <span x-show="isTrial(planId)" x-cloak>Start Free Trial</span>
                                <span x-show="!isTrial(planId)">Proceed to Payment</span>
                            </x-button>
                        </div>
                    </form>
                </x-card>
            @else
                <x-card title="Subscription Renewal">
                    <div class="rounded-2xl bg-amber-400/10 p-4 text-sm text-amber-700 dark:text-amber-400">
                        Your gym's subscription has expired. Please contact your gym owner or administrator to renew the subscription.
                    </div>
                </x-card>
            @endif
        </div>
    </div>

    @if (auth()->user()->isOwner())
        <x-card :padding="false" class="mt-6">
            <div class="border-b border-ink-100 p-4 dark:border-ink-800">
                <h3 class="font-bold text-ink-900 dark:text-white">Payment History</h3>
            </div>
            @if ($payments->isEmpty())
                <div class="p-8">
                    <x-empty-state icon="banknotes" title="No payments yet" message="Your subscription payments will appear here." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="px-5 py-3 font-semibold">Plan</th>
                                <th class="px-5 py-3 font-semibold">Cycle</th>
                                <th class="px-5 py-3 font-semibold">Period</th>
                                <th class="px-5 py-3 font-semibold">Method</th>
                                <th class="px-5 py-3 font-semibold">Amount</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($payments as $payment)
                                <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                    <td class="px-5 py-4 font-semibold text-ink-900 dark:text-white">{{ $payment->subscriptionPlan?->name ?? '—' }}</td>
                                    <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ ucfirst($payment->billing_cycle) }}</td>
                                    <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $payment->period_start->format('d M Y') }} – {{ $payment->period_end->format('d M Y') }}</td>
                                    <td class="px-5 py-4"><x-badge color="gray">{{ ucfirst($payment->payment_method) }}</x-badge></td>
                                    <td class="px-5 py-4 font-bold text-ink-900 dark:text-white">{{ $symbol }}{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-5 py-4">
                                        <x-badge :color="match($payment->status) { 'paid' => 'green', 'pending' => 'amber', 'failed' => 'red', 'refunded' => 'purple', default => 'gray' }">{{ ucfirst($payment->status) }}</x-badge>
                                    </td>
                                    <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $payment->created_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    @endif

    @push('scripts')
        <script>
            const prices = @json($plans->mapWithKeys(fn ($plan) => [$plan->id => ['monthly' => (float) $plan->price_monthly, 'yearly' => (float) $plan->price_yearly, 'trial' => $plan->isTrial()]]));

            function isTrial(planId) {
                const id = Number(planId);
                return Boolean(prices[id]?.trial);
            }

            function amount(planId, cycle) {
                if (!planId || !prices[planId]) return '0.00';
                return prices[planId][cycle].toFixed(2);
            }
        </script>
    @endpush
</x-layouts.app>
