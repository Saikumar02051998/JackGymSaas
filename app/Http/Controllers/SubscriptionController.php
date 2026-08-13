<?php

namespace App\Http\Controllers;

use App\Models\SaasPayment;
use App\Models\SubscriptionPlan;
use App\Services\RazorpayService;
use App\Services\SaasPaymentService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        abort_unless(is_saas(), 404);

        $user = auth()->user();
        $gym = current_gym();

        if (! $user->isOwner() && $gym->isSubscriptionActive()) {
            abort(403);
        }

        $plans = SubscriptionPlan::where('status', 'active')
            ->where('slug', '!=', 'trial')
            ->orderBy('price_monthly')
            ->get();
        $payments = $gym->saasPayments()->with('subscriptionPlan')->orderByDesc('created_at')->paginate(15);

        return view('subscription.index', compact('gym', 'plans', 'payments'));
    }

    public function createOrder(Request $request)
    {
        abort_unless(is_saas(), 404);
        abort_unless(auth()->user()->isOwner(), 403);

        $data = $request->validate([
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
        ]);

        $plan = SubscriptionPlan::findOrFail($data['subscription_plan_id']);
        $gym = current_gym();

        if ($plan->isTrial()) {
            abort(403, 'Free trial is managed by the platform owner.');
        }

        $result = app(SaasPaymentService::class)->createOrder($gym, $plan, $data['billing_cycle']);

        if (! $result['success']) {
            return back()->withErrors(['payment' => 'Could not create Razorpay order: '.$result['message']]);
        }

        $result['payment']->update(['recorded_by' => auth()->id()]);

        return view('subscription.checkout', [
            'order' => $result['order'],
            'payment' => $result['payment'],
            'keyId' => RazorpayService::forPlatform()->keyId(),
        ]);
    }

    public function verify(Request $request)
    {
        abort_unless(is_saas(), 404);
        abort_unless(auth()->user()->isOwner(), 403);

        $data = $request->validate([
            'payment_id' => ['required', 'exists:saas_payments,id'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $payment = SaasPayment::findOrFail($data['payment_id']);

        if ((int) $payment->gym_id !== current_gym()->id) {
            abort(403);
        }

        $result = app(SaasPaymentService::class)->verifyAndComplete(
            $payment,
            $data['razorpay_payment_id'],
            $data['razorpay_order_id'],
            $data['razorpay_signature']
        );

        if (! $result['success']) {
            return redirect()->route('subscription.index')->withErrors(['payment' => $result['message']]);
        }

        audit_log('saas.payment.paid', 'saas', $payment->id, "Subscription renewed via Razorpay for {$payment->gym->name}");

        return redirect()->route('subscription.index')->with('success', 'Subscription renewed successfully.');
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        if (! RazorpayService::forPlatform()->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $orderId = data_get($request->input('payload.payment.entity.order_id'));
        $paymentId = data_get($request->input('payload.payment.entity.id'));

        if ($orderId && $paymentId) {
            app(SaasPaymentService::class)->confirmFromWebhook($orderId, $paymentId);
        }

        return response()->json(['status' => 'ok']);
    }
}
