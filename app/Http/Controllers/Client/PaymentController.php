<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $payments,
    ) {}

    public function index()
    {
        $client = auth()->user()->clientProfile;

        $payments = $client->payments()
            ->with('plan', 'invoice')
            ->orderByDesc('payment_date')
            ->paginate(20);

        $stats = [
            'total_paid' => (float) $client->payments()->where('status', 'success')->sum('final_amount'),
            'payments_count' => $client->payments()->where('status', 'success')->count(),
            'pending' => (float) $client->payments()->whereIn('status', ['pending', 'processing'])->sum('final_amount'),
        ];

        return view('client.payments', compact('payments', 'stats'));
    }

    public function checkout()
    {
        $client = auth()->user()->clientProfile;
        $membership = $client->activeMembership;

        if (! $membership) {
            return redirect()->route('client.membership')->withErrors(['membership' => 'No active membership found.']);
        }

        $paid = (float) $membership->payments()->where('status', 'success')->sum('final_amount');
        $due = max((float) $membership->final_amount - $paid, 0);

        if ($due <= 0) {
            return redirect()->route('client.membership')->withErrors(['membership' => 'Your membership is already paid.']);
        }

        $razorpayConfigured = RazorpayService::forGym()->isConfigured();

        return view('client.payments-checkout', compact('membership', 'due', 'razorpayConfigured'));
    }

    public function store(Request $request)
    {
        $client = auth()->user()->clientProfile;
        $membership = $client->activeMembership;

        abort_unless($membership, 404, 'No active membership found.');

        abort_unless(RazorpayService::forGym()->isConfigured(), 503, 'Online payments are not configured yet.');

        $paid = (float) $membership->payments()->where('status', 'success')->sum('final_amount');
        $due = max((float) $membership->final_amount - $paid, 0);

        abort_if($due <= 0, 422, 'No outstanding amount to pay.');

        $payment = $this->payments->createForMembership($client, $membership, [
            'amount' => $due,
            'discount' => 0,
            'tax' => 0,
            'payment_method' => 'razorpay',
            'notes' => 'Online payment via Razorpay',
        ]);

        $result = $this->payments->createRazorpayOrder($payment);

        if (! $result['success']) {
            return back()->withErrors(['payment' => 'Could not create the payment order: ' . $result['message']]);
        }

        return view('client.razorpay-checkout', [
            'order' => $result['order'],
            'payment' => $payment,
            'keyId' => RazorpayService::forGym()->keyId(),
        ]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'payment_id' => ['required', 'exists:payments,id'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $payment = Payment::findOrFail($data['payment_id']);

        abort_unless($payment->client_id === auth()->user()->clientProfile?->id, 403);

        $result = $this->payments->verifyAndComplete(
            $payment,
            $data['razorpay_payment_id'],
            $data['razorpay_order_id'],
            $data['razorpay_signature']
        );

        if (! $result['success']) {
            return redirect()->route('client.payments')->withErrors(['payment' => $result['message']]);
        }

        return redirect()->route('client.invoices.show', $payment->invoice)
            ->with('status', 'Payment successful. Your invoice has been generated.');
    }
}
