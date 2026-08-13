<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Services\MembershipService;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function show()
    {
        $client = auth()->user()->clientProfile;

        $client->load([
            'activeMembership.plan',
            'memberships.plan',
            'memberships.histories' => fn ($q) => $q->latest()->take(10),
        ]);

        $due = 0;

        if ($client->activeMembership) {
            $paid = (float) $client->activeMembership->payments()
                ->where('status', 'success')
                ->sum('final_amount');

            $due = max((float) $client->activeMembership->final_amount - $paid, 0);
        }

        $plans = MembershipPlan::where('gym_id', $client->gym_id)
            ->where('status', 'active')
            ->where('name', '!=', 'Free Trial')
            ->orderBy('final_amount')
            ->get();

        return view('client.membership', compact('client', 'due', 'plans'));
    }

    public function renew(Request $request, MembershipService $memberships)
    {
        $client = auth()->user()->clientProfile;

        $data = $request->validate([
            'plan_id' => ['required', 'exists:membership_plans,id'],
        ]);

        $plan = MembershipPlan::where('gym_id', $client->gym_id)
            ->where('id', $data['plan_id'])
            ->where('status', 'active')
            ->where('name', '!=', 'Free Trial')
            ->first();

        abort_unless($plan, 422, 'The selected plan is not available.');

        $memberships->renew($client, $plan, ['notes' => 'Plan chosen by client']);

        return redirect()->route('client.payments.checkout')
            ->with('status', 'Plan selected. Complete your payment below.');
    }
}
