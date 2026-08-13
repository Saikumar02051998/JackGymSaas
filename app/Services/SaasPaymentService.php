<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Gym;
use App\Models\SaasPayment;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaasPaymentService
{
    public function nextPeriodStart(Gym $gym): Carbon
    {
        $current = $gym->subscription_expires_at;

        if ($current && $current->isFuture()) {
            return $current->copy()->addDay();
        }

        return now()->startOfDay();
    }

    public function periodEndDate(Carbon $start, string $cycle): Carbon
    {
        return $cycle === 'yearly'
            ? $start->copy()->addYear()->subDay()
            : $start->copy()->addMonth()->subDay();
    }

    public function createOrder(Gym $gym, SubscriptionPlan $plan, string $cycle): array
    {
        $amount = $plan->priceFor($cycle);

        $order = RazorpayService::forPlatform()->createOrder([
            'amount' => (int) round($amount * 100),
            'currency' => $gym->currency ?: 'INR',
            'receipt' => 'SAAS-'.Str::upper(Str::random(10)),
            'notes' => [
                'gym_id' => (string) $gym->id,
                'plan_id' => (string) $plan->id,
                'billing_cycle' => $cycle,
                'plan_name' => $plan->name,
            ],
        ]);

        if (! $order['success']) {
            return $order;
        }

        $periodStart = $this->nextPeriodStart($gym);
        $periodEnd = $this->periodEndDate($periodStart, $cycle);

        $payment = SaasPayment::create([
            'gym_id' => $gym->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => $cycle,
            'amount' => $amount,
            'currency' => $gym->currency ?: 'INR',
            'payment_method' => 'razorpay',
            'razorpay_order_id' => $order['order']['id'],
            'status' => 'pending',
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'notes' => "Subscription payment for {$plan->name} ({$cycle})",
        ]);

        return ['success' => true, 'order' => $order['order'], 'payment' => $payment];
    }

    public function verifyAndComplete(SaasPayment $payment, string $razorpayPaymentId, string $razorpayOrderId, string $signature): array
    {
        $valid = RazorpayService::forPlatform()->verifyPaymentSignature([
            'razorpay_payment_id' => $razorpayPaymentId,
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_signature' => $signature,
        ]);

        if (! $valid) {
            $payment->update([
                'status' => 'failed',
                'razorpay_payment_id' => $razorpayPaymentId,
            ]);

            return ['success' => false, 'message' => 'Payment signature verification failed. Please contact support.'];
        }

        if ($payment->status === 'paid') {
            return ['success' => true, 'payment' => $payment];
        }

        $payment->update([
            'status' => 'paid',
            'razorpay_payment_id' => $razorpayPaymentId,
        ]);

        $this->applyPayment($payment);

        return ['success' => true, 'payment' => $payment];
    }

    public function recordManualPayment(array $data): SaasPayment
    {
        return DB::transaction(function () use ($data) {
            $gym = Gym::findOrFail($data['gym_id']);
            $plan = SubscriptionPlan::findOrFail($data['subscription_plan_id']);
            $cycle = $data['billing_cycle'];
            $amount = $data['amount'] ?? $plan->priceFor($cycle);

            $periodStart = isset($data['period_start'])
                ? Carbon::parse($data['period_start'])
                : $this->nextPeriodStart($gym);

            $periodEnd = isset($data['period_end'])
                ? Carbon::parse($data['period_end'])
                : $this->periodEndDate($periodStart, $cycle);

            $payment = SaasPayment::create([
                'gym_id' => $gym->id,
                'subscription_plan_id' => $plan->id,
                'billing_cycle' => $cycle,
                'amount' => $amount,
                'currency' => $gym->currency ?: 'INR',
                'payment_method' => $data['payment_method'] ?? 'manual',
                'status' => 'paid',
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'invoice_ref' => $data['invoice_ref'] ?? null,
                'recorded_by' => $data['recorded_by'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->applyPayment($payment);

            return $payment;
        });
    }

    public function confirmFromWebhook(string $razorpayOrderId, string $razorpayPaymentId): ?SaasPayment
    {
        $payment = SaasPayment::where('razorpay_order_id', $razorpayOrderId)->where('status', 'pending')->first();

        if (! $payment) {
            return null;
        }

        $payment->update([
            'status' => 'paid',
            'razorpay_payment_id' => $razorpayPaymentId,
        ]);

        $this->applyPayment($payment);

        return $payment;
    }

    public function activateTrial(Gym $gym, SubscriptionPlan $plan): array
    {
        $trialDays = (int) saas_setting('trial_days', GymService::TRIAL_DAYS);

        $periodStart = now()->startOfDay();
        $periodEnd = $periodStart->copy()->addDays($trialDays);

        $payment = SaasPayment::create([
            'gym_id' => $gym->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => $gym->subscription_billing_cycle ?: 'monthly',
            'amount' => 0,
            'currency' => $gym->currency ?: 'INR',
            'payment_method' => 'trial',
            'status' => 'paid',
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'recorded_by' => auth()->id(),
            'notes' => "Free trial activated for {$gym->name}",
        ]);

        $gym->subscription_plan_id = $plan->id;
        $gym->subscription_status = 'trial';
        $gym->subscription_expires_at = $periodEnd;
        $gym->save();

        return ['success' => true, 'payment' => $payment];
    }

    public function applyPayment(SaasPayment $payment): void
    {
        $gym = $payment->gym;
        $plan = $payment->subscriptionPlan;

        if (! $gym || ! $plan) {
            return;
        }

        $currentExpiry = $gym->subscription_expires_at;

        $base = ($currentExpiry && $currentExpiry->isFuture())
            ? $currentExpiry->copy()
            : $payment->period_start->copy();

        $gym->subscription_plan_id = $plan->id;
        $gym->subscription_billing_cycle = $payment->billing_cycle;
        $gym->subscription_status = 'active';
        $gym->subscription_expires_at = $payment->billing_cycle === 'yearly' ? $base->addYear() : $base->addMonth();
        $gym->save();

        $this->recordExpense($payment);
    }

    protected function recordExpense(SaasPayment $payment): void
    {
        $gym = $payment->gym;

        $category = $gym->expenseCategories()->where('name', 'Software')->first();

        if (! $category) {
            $category = ExpenseCategory::create([
                'gym_id' => $gym->id,
                'name' => 'Software',
                'description' => 'Software and subscription expenses',
            ]);
        }

        Expense::create([
            'gym_id' => $gym->id,
            'category_id' => $category->id,
            'amount' => $payment->amount,
            'expense_date' => now()->toDateString(),
            'vendor' => (string) config('app.name', 'SaaS'),
            'description' => 'SaaS subscription – '.($payment->subscriptionPlan?->name ?? 'Subscription').' ('.$payment->billing_cycle.')',
            'created_by' => $payment->recorded_by ?? null,
        ]);
    }
}
