<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Membership;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $payments,
        protected RazorpayService $razorpay,
    ) {}

    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = Payment::with(['client.user', 'plan', 'membership'])
            ->where('gym_id', $gymId);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('method') && $request->input('method') !== 'all') {
            $query->where('payment_method', $request->input('method'));
        }

        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->input('to'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_no', 'like', "%{$search}%")
                    ->orWhereHas('client.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('client', fn ($cq) => $cq->where('member_id', 'like', "%{$search}%"));
            });
        }

        $payments = $query->orderByDesc('payment_date')->paginate(15)->withQueryString();

        $summary = [
            'success' => (float) Payment::where('gym_id', $gymId)->where('status', 'success')->sum('final_amount'),
            'pending' => (float) Payment::where('gym_id', $gymId)->whereIn('status', ['pending', 'processing'])->sum('final_amount'),
            'refunded' => (float) Payment::where('gym_id', $gymId)->where('status', 'refunded')->sum('final_amount'),
            'today' => (float) Payment::where('gym_id', $gymId)->where('status', 'success')->whereDate('payment_date', now()->toDateString())->sum('final_amount'),
        ];

        return view('payments.index', compact('payments', 'summary'));
    }

    public function create(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('payments.create'), 403);

        $clients = Client::with('user')->where('gym_id', current_gym()?->id)
            ->orderBy('created_at', 'desc')->take(200)->get();

        $selectedClient = null;
        $memberships = collect();

        if ($request->filled('client_id')) {
            $selectedClient = Client::with('user')->findOrFail($request->input('client_id'));
            $memberships = Membership::with('plan')
                ->where('client_id', $selectedClient->id)
                ->where('payment_status', '!=', 'paid')
                ->orderByDesc('created_at')
                ->get();
        }

        return view('payments.create', compact('clients', 'selectedClient', 'memberships'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('payments.create'), 403);

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'membership_id' => ['nullable', 'exists:memberships,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,upi,card,neft,cheque,razorpay,wallet'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $client = Client::findOrFail($data['client_id']);
        $membership = isset($data['membership_id']) ? Membership::find($data['membership_id']) : $client->activeMembership;

        if ($membership) {
            $payment = $this->payments->createForMembership($client, $membership, [
                'amount' => $data['amount'],
                'discount' => $data['discount'] ?? 0,
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_date' => $data['payment_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        } else {
            $payment = Payment::create([
                'gym_id' => current_gym()->id,
                'payment_no' => next_sequence(Payment::class, 'payment_no', 'PAY-'),
                'client_id' => $client->id,
                'amount' => $data['amount'],
                'discount' => $data['discount'] ?? 0,
                'tax' => 0,
                'final_amount' => ($data['amount'] - ($data['discount'] ?? 0)),
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'status' => 'pending',
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'created_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);
        }

        if ($request->input('payment_method') === 'razorpay') {
            $result = $this->payments->createRazorpayOrder($payment);

            if (! $result['success']) {
                return back()->withErrors(['payment' => 'Could not create Razorpay order: ' . $result['message']]);
            }

            return view('payments.checkout', [
                'order' => $result['order'],
                'payment' => $payment,
                'keyId' => config('services.razorpay.key_id', env('RAZORPAY_KEY_ID')),
            ]);
        }

        $this->payments->completePayment($payment, [], 'manual');

        return redirect()->route('payments.show', $payment)->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['client.user', 'plan', 'membership', 'transactions', 'invoice']);

        return view('payments.show', compact('payment'));
    }

    public function verify(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('payments.verify'), 403);

        $data = $request->validate([
            'payment_id' => ['required', 'exists:payments,id'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $payment = Payment::findOrFail($data['payment_id']);

        $result = $this->payments->verifyAndComplete(
            $payment,
            $data['razorpay_payment_id'],
            $data['razorpay_order_id'],
            $data['razorpay_signature']
        );

        if (! $result['success']) {
            return redirect()->route('payments.show', $payment)->withErrors(['payment' => $result['message']]);
        }

        return redirect()->route('payments.show', $payment)->with('success', 'Payment verified successfully.');
    }

    public function refund(Request $request, Payment $payment)
    {
        abort_unless(auth()->user()->hasPermission('payments.refund'), 403);

        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01', 'max:' . $payment->final_amount],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->payments->refund($payment, $data['amount'] ?? null, $data['notes'] ?? '');

        if (! $result['success']) {
            return back()->withErrors(['payment' => $result['message']]);
        }

        return back()->with('success', 'Payment refunded.');
    }

    public function export(Request $request)
    {
        $gymId = current_gym()?->id;

        $payments = Payment::with('client.user')->where('gym_id', $gymId)
            ->when($request->filled('from'), fn ($q) => $q->whereDate('payment_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('payment_date', '<=', $request->input('to')))
            ->get();

        $filename = 'payments-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Payment No', 'Client', 'Method', 'Amount', 'Discount', 'Tax', 'Total', 'Status', 'Date']);

            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->payment_no,
                    $payment->client?->display_name,
                    $payment->payment_method,
                    $payment->amount,
                    $payment->discount,
                    $payment->tax,
                    $payment->final_amount,
                    $payment->status,
                    $payment->payment_date,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        if (! $this->razorpay->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $event = $request->input('event');
        $this->payments->handleWebhook($event, $request->input());

        return response()->json(['status' => 'ok']);
    }
}
