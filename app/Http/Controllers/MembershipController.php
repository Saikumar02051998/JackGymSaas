<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Trial;
use App\Services\MembershipService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function __construct(
        protected MembershipService $memberships,
        protected PaymentService $payments,
    ) {}

    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = Membership::with(['client.user', 'plan'])
            ->where('gym_id', $gymId);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            if ($request->input('status') === 'expiring') {
                $query->where('status', 'active')
                    ->whereBetween('end_date', [now()->toDateString(), now()->addDays(30)->toDateString()]);
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('membership_no', 'like', "%{$search}%")
                    ->orWhereHas('client.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('client', fn ($cq) => $cq->where('member_id', 'like', "%{$search}%"));
            });
        }

        $memberships = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $counts = [
            'active' => Membership::where('gym_id', $gymId)->where('status', 'active')->count(),
            'expiring' => Membership::where('gym_id', $gymId)->where('status', 'active')->whereBetween('end_date', [now()->toDateString(), now()->addDays(30)->toDateString()])->count(),
            'expired' => Membership::where('gym_id', $gymId)->where('status', 'expired')->count(),
            'upcoming' => Membership::where('gym_id', $gymId)->where('status', 'upcoming')->count(),
        ];

        return view('memberships.index', compact('memberships', 'counts'));
    }

    public function expiring(Request $request)
    {
        $gymId = current_gym()?->id;

        $memberships = Membership::with(['client.user', 'plan'])
            ->where('gym_id', $gymId)
            ->where('status', 'active')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->orderBy('end_date')
            ->paginate(15);

        return view('memberships.expiring', compact('memberships'));
    }

    public function trials(Request $request)
    {
        $gymId = current_gym()?->id;

        $trials = Trial::with(['client.user', 'lead'])
            ->where('gym_id', $gymId)
            ->when($request->filled('status') && $request->input('status') !== 'all', fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('trial_end')
            ->paginate(15);

        return view('memberships.trials', compact('trials'));
    }

    public function create(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('memberships.create'), 403);

        $client = null;
        if ($request->filled('client_id')) {
            $client = Client::with('user')->findOrFail($request->input('client_id'));
        }

        $clients = Client::with('user')->where('gym_id', current_gym()?->id)
            ->orderBy('created_at', 'desc')->take(200)->get();
        $plans = MembershipPlan::where('gym_id', current_gym()?->id)->where('status', 'active')->get();

        return view('memberships.create', compact('clients', 'plans', 'client'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('memberships.create'), 403);

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'plan_id' => ['required', 'exists:membership_plans,id'],
            'start_date' => ['nullable', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string'],
            'collect_payment' => ['nullable', 'boolean'],
        ]);

        $client = Client::findOrFail($data['client_id']);
        $plan = MembershipPlan::findOrFail($data['plan_id']);

        $membership = $this->memberships->create($client, $plan, $data);

        if (($request->boolean('collect_payment')) && $request->filled('payment_method')) {
            $payment = $this->payments->createForMembership($client, $membership, [
                'payment_method' => $data['payment_method'],
                'discount' => $data['discount'] ?? 0,
            ]);

            $this->payments->completePayment($payment, [], 'reception');
        }

        return redirect()->route('clients.show', $client)->with('success', 'Membership created successfully.');
    }

    public function renew(Request $request, Membership $membership)
    {
        abort_unless(auth()->user()->hasPermission('memberships.renew'), 403);

        $data = $request->validate([
            'plan_id' => ['required', 'exists:membership_plans,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'collect_payment' => ['nullable', 'boolean'],
            'payment_method' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $client = $membership->client;
        $plan = MembershipPlan::findOrFail($data['plan_id']);

        $new = $this->memberships->renew($client, $plan, $data);

        $payment = $this->payments->createForMembership($client, $new, [
            'payment_method' => $data['payment_method'] ?? 'cash',
            'discount' => $data['discount'] ?? 0,
        ]);

        if ($request->boolean('collect_payment') && $request->filled('payment_method')) {
            $this->payments->completePayment($payment, [], 'reception');
        }

        return redirect()->route('clients.show', $client)->with('success', 'Membership renewed successfully.');
    }

    public function cancel(Membership $membership)
    {
        abort_unless(auth()->user()->hasPermission('memberships.renew'), 403);

        $this->memberships->setStatus($membership, 'cancelled', 'Cancelled by staff');

        return back()->with('success', 'Membership cancelled.');
    }
}
