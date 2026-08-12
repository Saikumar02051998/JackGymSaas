<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Gym;
use App\Models\SaasPayment;
use App\Models\SubscriptionPlan;
use App\Services\SaasPaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('saas.payments.view'), 403);

        $query = SaasPayment::with(['gym', 'subscriptionPlan', 'recordedBy']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('gym_id')) {
            $query->where('gym_id', $request->input('gym_id'));
        }

        $payments = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $summary = [
            'paid' => (float) SaasPayment::where('status', 'paid')->sum('amount'),
            'pending' => (float) SaasPayment::where('status', 'pending')->sum('amount'),
            'failed' => (float) SaasPayment::where('status', 'failed')->sum('amount'),
        ];

        $gyms = Gym::orderBy('name')->get();

        return view('saas.payments', compact('payments', 'summary', 'gyms'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('saas.payments.create'), 403);

        $gyms = Gym::orderBy('name')->get();
        $plans = SubscriptionPlan::where('status', 'active')->orderBy('price_monthly')->get();

        return view('saas.payments-create', compact('gyms', 'plans'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('saas.payments.create'), 403);

        $data = $request->validate([
            'gym_id' => ['required', 'exists:gyms,id'],
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'in:manual,bank_transfer,cash,cheque,upi,razorpay'],
            'period_start' => ['nullable', 'date'],
            'invoice_ref' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['recorded_by'] = auth()->id();

        $payment = app(SaasPaymentService::class)->recordManualPayment($data);

        audit_log('saas.payment.recorded', 'saas', $payment->id, "SaaS payment recorded for gym #{$payment->gym_id}");

        return redirect()->route('saas.payments.index')->with('success', 'Payment recorded and subscription extended.');
    }
}
