<x-layouts.app
    title="Complete Payment"
    description="Pay securely via Razorpay."
    :breadcrumbs="[['label' => 'My Payments', 'url' => route('client.payments')], ['label' => $payment->payment_no]]">

    <div class="mx-auto max-w-md">
        <x-card>
            <div class="text-center">
                <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-gold-400/10 text-gold-500">
                    <x-icon name="lock" class="size-7" />
                </div>
                <h2 class="mt-4 text-lg font-bold text-ink-900 dark:text-white">Secure Payment</h2>
                <p class="mt-1 text-sm text-ink-400">You are about to pay for a secure checkout session.</p>

                <div class="mt-6 rounded-2xl bg-ink-50 p-6 dark:bg-ink-800">
                    <p class="text-xs font-medium uppercase tracking-wider text-ink-400">Amount Due</p>
                    <p class="mt-1 text-3xl font-extrabold text-ink-900 dark:text-white">{{ gym_setting('currency_symbol', '₹') }}{{ number_format($payment->final_amount, 2) }}</p>
                    <p class="mt-2 text-sm text-ink-500 dark:text-ink-400">{{ $payment->payment_no }}</p>
                </div>

                <button type="button" id="pay-button" class="btn-primary mt-6 w-full py-3">
                    Pay {{ gym_setting('currency_symbol', '₹') }}{{ number_format($payment->final_amount, 2) }}
                </button>

                <p class="mt-4 text-xs leading-relaxed text-ink-400">
                    Your payment is processed securely by Razorpay. You will be redirected to complete the payment.
                </p>
            </div>
        </x-card>
    </div>

    <form id="razorpay-verify" method="POST" action="{{ route('client.payments.verify') }}">
        @csrf
        <input type="hidden" name="payment_id" value="{{ $payment->id }}">
        <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id">
        <input type="hidden" name="razorpay_order_id" id="rzp_order_id">
        <input type="hidden" name="razorpay_signature" id="rzp_signature">
    </form>

    @push('scripts')
        <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const btn = document.getElementById('pay-button');

                btn.addEventListener('click', function () {
                    if (typeof Razorpay === 'undefined') {
                        alert('Payment gateway could not be loaded. Please try again.');
                        return;
                    }

                    const options = {
                        key: @json($keyId),
                        amount: @json((int) round($payment->final_amount * 100)),
                        currency: @json(gym_setting('currency', 'INR')),
                        name: @json(auth()->user()->clientProfile?->gym?->name ?? 'Gym'),
                        description: @json('Payment ' . $payment->payment_no),
                        order_id: @json($order['id'] ?? null),
                        handler: function (response) {
                            document.getElementById('rzp_payment_id').value = response.razorpay_payment_id;
                            document.getElementById('rzp_order_id').value = response.razorpay_order_id;
                            document.getElementById('rzp_signature').value = response.razorpay_signature;
                            document.getElementById('razorpay-verify').submit();
                        },
                        modal: {
                            ondismiss: function () {
                                window.location.reload();
                            },
                        },
                        theme: {
                            color: '#a78b3c',
                        },
                    };

                    const rzp = new Razorpay(options);
                    rzp.open();
                });
            });
        </script>
    @endpush
</x-layouts.app>
