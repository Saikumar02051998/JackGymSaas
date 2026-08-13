<x-layouts.app
    title="Pay Now"
    description="Settle your outstanding membership payment securely online."
    :breadcrumbs="[['label' => 'My Membership', 'url' => route('client.membership')], ['label' => 'Pay Now']]">

    <div class="mx-auto max-w-lg">
        <x-card title="Pay with Razorpay">
            @if ($razorpayConfigured)
                <p class="text-sm text-ink-500 dark:text-ink-400">
                    You will be redirected to the secure Razorpay gateway to complete your payment.
                    An invoice is generated automatically once the payment succeeds.
                </p>

                <dl class="mt-5 space-y-3 rounded-2xl bg-ink-50 p-5 text-sm dark:bg-ink-800">
                    <div class="flex justify-between">
                        <dt class="text-ink-400">Membership</dt>
                        <dd class="font-semibold text-ink-900 dark:text-white">{{ $membership->membership_no }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-400">Plan</dt>
                        <dd class="font-semibold text-ink-900 dark:text-white">{{ $membership->plan?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink-400">Valid Until</dt>
                        <dd class="font-semibold text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($membership->end_date)->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-ink-200 pt-3 dark:border-ink-700">
                        <dt class="font-medium text-ink-600 dark:text-ink-300">Amount Due</dt>
                        <dd class="text-lg font-bold text-ink-900 dark:text-white">{{ money($due) }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('client.payments.store') }}" class="mt-6">
                    @csrf
                    <x-button type="submit" class="w-full py-3">
                        <x-icon name="lock" class="size-4" />
                        Pay {{ money($due) }} Securely
                    </x-button>
                </form>

                <p class="mt-4 text-center text-xs leading-relaxed text-ink-400">
                    Powered by Razorpay — Card, UPI, NetBanking and more. You will be redirected to complete the payment.
                </p>
            @else
                <div class="py-4 text-center">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-ink-100 text-ink-400 dark:bg-ink-800">
                        <x-icon name="lock" class="size-7" />
                    </div>
                    <h3 class="mt-4 text-sm font-bold text-ink-900 dark:text-white">Online payments unavailable</h3>
                    <p class="mt-1 text-sm text-ink-400">The gym has not enabled online payments yet. Please contact the front desk to complete your payment.</p>
                    <a href="{{ route('client.membership') }}" class="btn-outline mt-5">Back to Membership</a>
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
