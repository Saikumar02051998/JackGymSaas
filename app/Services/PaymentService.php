<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\PaymentGatewayLog;
use App\Models\PaymentTransaction;
use App\Notifications\InAppNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        protected RazorpayService $razorpay,
        protected MembershipService $memberships,
        protected NotificationService $notifications,
    ) {}

    public function createForMembership(Client $client, Membership $membership, array $data): Payment
    {
        return DB::transaction(function () use ($client, $membership, $data) {
            $amount = $data['amount'] ?? $membership->final_amount;

            $payment = Payment::create([
                'gym_id' => $client->gym_id,
                'payment_no' => next_sequence(Payment::class, 'payment_no', 'PAY-'),
                'client_id' => $client->id,
                'membership_id' => $membership->id,
                'plan_id' => $membership->plan_id,
                'amount' => $amount,
                'discount' => $data['discount'] ?? $membership->discount,
                'tax' => $data['tax'] ?? $membership->tax,
                'final_amount' => $amount - ($data['discount'] ?? 0),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'transaction_id' => $data['transaction_id'] ?? null,
                'gateway' => $data['gateway'] ?? null,
                'status' => 'pending',
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'created_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $payment->transactions()->create([
                'transaction_type' => 'create',
                'amount' => $payment->final_amount,
                'status' => 'pending',
                'gateway' => $data['gateway'] ?? null,
            ]);

            audit_log('payment.created', 'payments', $payment->id, "Payment {$payment->payment_no} created for {$client->display_name}");

            return $payment;
        });
    }

    public function createRazorpayOrder(Payment $payment, array $extra = []): array
    {
        if ($payment->status !== 'pending') {
            return ['success' => false, 'message' => 'Payment is not in pending state.'];
        }

        $receipt = Str::limit($payment->payment_no, 40, '');

        $result = $this->razorpay->createOrder([
            'amount' => (int) round($payment->final_amount * 100),
            'currency' => gym_setting('currency', 'INR'),
            'receipt' => $receipt,
            'notes' => [
                'payment_no' => $payment->payment_no,
                'client' => $payment->client?->display_name ?? '',
                'gym' => current_gym()?->name ?? '',
            ],
        ]);

        if (! $result['success']) {
            return $result;
        }

        $order = $result['order'];

        $payment->update([
            'gateway' => 'razorpay',
            'gateway_reference' => $order['id'],
            'status' => 'processing',
        ]);

        $payment->transactions()->create([
            'transaction_type' => 'authorize',
            'amount' => $payment->final_amount,
            'status' => 'processing',
            'gateway' => 'razorpay',
            'gateway_reference' => $order['id'],
            'payload' => $order,
        ]);

        PaymentGatewayLog::create([
            'payment_id' => $payment->id,
            'gateway' => 'razorpay',
            'event' => 'order.created',
            'payload' => $order,
        ]);

        return ['success' => true, 'order' => $order, 'payment' => $payment];
    }

    public function verifyAndComplete(Payment $payment, string $razorpayPaymentId, string $razorpayOrderId, string $signature): array
    {
        return DB::transaction(function () use ($payment, $razorpayPaymentId, $razorpayOrderId, $signature) {
            if ($payment->status === 'success') {
                return ['success' => true, 'payment' => $payment, 'already' => true];
            }

            $valid = $this->razorpay->verifyPaymentSignature([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $signature,
            ]);

            if (! $valid) {
                $payment->update(['status' => 'failed']);

                $payment->transactions()->create([
                    'transaction_type' => 'verify',
                    'amount' => $payment->final_amount,
                    'status' => 'failed',
                    'gateway' => 'razorpay',
                    'gateway_reference' => $razorpayPaymentId,
                    'payload' => ['razorpay_payment_id' => $razorpayPaymentId],
                ]);

                PaymentGatewayLog::create([
                    'payment_id' => $payment->id,
                    'gateway' => 'razorpay',
                    'event' => 'signature.verification_failed',
                    'payload' => ['razorpay_payment_id' => $razorpayPaymentId],
                ]);

                audit_log('payment.failed', 'payments', $payment->id, "Payment {$payment->payment_no} failed signature verification");

                return ['success' => false, 'message' => 'Payment verification failed.'];
            }

            $gatewayPayment = $this->razorpay->fetchPayment($razorpayPaymentId);

            if (! $gatewayPayment || ($gatewayPayment['status'] ?? '') !== 'captured') {
                $payment->update(['status' => 'failed']);

                return ['success' => false, 'message' => 'Payment was not captured by gateway.'];
            }

            $this->completePayment($payment, [
                'transaction_id' => $razorpayPaymentId,
                'gateway_reference' => $razorpayPaymentId,
                'gateway' => 'razorpay',
            ], 'verified');

            PaymentGatewayLog::create([
                'payment_id' => $payment->id,
                'gateway' => 'razorpay',
                'event' => 'payment.captured',
                'payload' => $gatewayPayment,
            ]);

            return ['success' => true, 'payment' => $payment];
        });
    }

    public function handleWebhook(string $event, array $payload): array
    {
        $paymentId = $payload['payment']['entity']['id'] ?? null;

        PaymentGatewayLog::create([
            'gateway' => 'razorpay',
            'event' => $event,
            'payload' => $payload,
        ]);

        if ($event === 'payment.captured' && $paymentId) {
            $payment = Payment::where('transaction_id', $paymentId)
                ->orWhere('gateway_reference', $paymentId)
                ->first();

            if ($payment && $payment->status === 'processing') {
                $this->completePayment($payment, [
                    'transaction_id' => $paymentId,
                    'gateway_reference' => $paymentId,
                    'gateway' => 'razorpay',
                ], 'webhook');

                return ['handled' => true, 'payment' => $payment->payment_no];
            }
        }

        if ($event === 'payment.failed' && $paymentId) {
            Payment::where('transaction_id', $paymentId)
                ->orWhere('gateway_reference', $paymentId)
                ->update(['status' => 'failed']);
        }

        return ['handled' => false];
    }

    public function completePayment(Payment $payment, array $gatewayData = [], string $source = 'manual'): Payment
    {
        return DB::transaction(function () use ($payment, $gatewayData, $source) {
            $payment->update(array_merge([
                'status' => 'success',
                'transaction_id' => $gatewayData['transaction_id'] ?? $payment->transaction_id,
                'gateway_reference' => $gatewayData['gateway_reference'] ?? $payment->gateway_reference,
                'gateway' => $gatewayData['gateway'] ?? $payment->gateway,
            ]));

            $payment->transactions()->create([
                'transaction_type' => 'capture',
                'amount' => $payment->final_amount,
                'status' => 'success',
                'gateway' => $payment->gateway,
                'gateway_reference' => $payment->gateway_reference,
                'payload' => $gatewayData,
            ]);

            $invoice = $this->generateInvoice($payment);

            $payment->update(['invoice_id' => $invoice->id]);

            if ($payment->membership) {
                $this->memberships->activateOnPayment($payment->membership);
            }

            $user = $payment->client?->user;

            if ($user) {
                $user->notify(new InAppNotification(
                    'Payment successful',
                    "Payment {$payment->payment_no} of " . money($payment->final_amount) . " received. Invoice {$invoice->invoice_no} generated.",
                    'success',
                    route('client.invoices.show', $invoice)
                ));
            }

            audit_log('payment.completed', 'payments', $payment->id, "Payment {$payment->payment_no} completed ({$source})");

            return $payment;
        });
    }

    public function markManualUpiPending(Payment $payment, string $transactionId): Payment
    {
        $payment->update([
            'transaction_id' => $transactionId,
            'status' => 'processing',
            'payment_method' => 'upi',
        ]);

        audit_log('payment.upi_submitted', 'payments', $payment->id, "UPI transaction {$transactionId} submitted for {$payment->payment_no}");

        return $payment;
    }

    public function refund(Payment $payment, ?float $amount = null, string $notes = ''): array
    {
        return DB::transaction(function () use ($payment, $amount, $notes) {
            $refundAmount = $amount ?? $payment->final_amount;
            $refundReference = null;

            if ($payment->gateway === 'razorpay' && $payment->transaction_id) {
                $result = $this->razorpay->refund($payment->transaction_id, $refundAmount);

                if (! $result['success']) {
                    return $result;
                }

                $refundReference = $result['refund']['id'] ?? null;
            }

            $isFull = $refundAmount >= $payment->final_amount;
            $payment->update([
                'status' => $isFull ? 'refunded' : 'partially_refunded',
                'notes' => ($payment->notes ? $payment->notes . "\n" : '') . 'Refund: ' . money($refundAmount) . ' - ' . $notes,
            ]);

            $payment->transactions()->create([
                'transaction_type' => 'refund',
                'amount' => $refundAmount,
                'status' => 'success',
                'gateway' => $payment->gateway,
                'gateway_reference' => $refundReference,
            ]);

            if ($payment->invoice) {
                $payment->invoice->update(['status' => $isFull ? 'refunded' : 'issued']);
            }

            audit_log('payment.refunded', 'payments', $payment->id, "Payment {$payment->payment_no} refunded " . money($refundAmount));

            return ['success' => true, 'payment' => $payment];
        });
    }

    public function generateInvoice(Payment $payment): Invoice
    {
        $prefix = (string) gym_setting('invoice_prefix', 'INV');

        $invoice = Invoice::create([
            'gym_id' => $payment->gym_id,
            'invoice_no' => $prefix . '-' . str_pad((string) Invoice::max('id') + 1, 6, '0', STR_PAD_LEFT),
            'client_id' => $payment->client_id,
            'membership_id' => $payment->membership_id,
            'payment_id' => $payment->id,
            'subtotal' => $payment->amount,
            'discount' => $payment->discount,
            'tax' => $payment->tax,
            'grand_total' => $payment->final_amount,
            'status' => 'paid',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'created_by' => auth()->id(),
            'notes' => 'Auto-generated from payment ' . $payment->payment_no,
        ]);

        $invoice->items()->create([
            'description' => $payment->plan?->name ?? 'Gym membership',
            'quantity' => 1,
            'unit_price' => $payment->amount,
            'amount' => $payment->amount,
        ]);

        audit_log('invoice.created', 'invoices', $invoice->id, "Invoice {$invoice->invoice_no} generated");

        return $invoice;
    }
}
