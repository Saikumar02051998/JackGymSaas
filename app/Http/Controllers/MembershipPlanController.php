<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    public function index()
    {
        $plans = MembershipPlan::where('gym_id', current_gym()?->id)
            ->orderBy('duration_days')
            ->paginate(15);

        return view('membership-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('membership-plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'duration_label' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
        ]);

        $price = (float) $data['price'];
        $discount = (float) ($data['discount'] ?? 0);
        $taxPercent = (float) gym_setting('tax_percent', 0);
        $taxable = max(0, $price - $discount);
        $tax = round($taxable * ($taxPercent / 100), 2);

        MembershipPlan::create(array_merge($data, [
            'gym_id' => current_gym()->id,
            'discount' => $discount,
            'tax' => $tax,
            'final_amount' => round($taxable + $tax, 2),
            'status' => 'active',
            'features' => $data['features'] ?? [],
        ]));

        audit_log('plan.created', 'memberships', null, "Created membership plan {$data['name']}");

        return redirect()->route('memberships.plans.index')->with('success', 'Plan created successfully.');
    }

    public function edit(MembershipPlan $plan)
    {
        return view('membership-plans.edit', compact('plan'));
    }

    public function update(Request $request, MembershipPlan $plan)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'duration_label' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
        ]);

        $price = (float) $data['price'];
        $discount = (float) ($data['discount'] ?? 0);
        $taxPercent = (float) gym_setting('tax_percent', 0);
        $taxable = max(0, $price - $discount);
        $tax = round($taxable * ($taxPercent / 100), 2);

        $plan->update(array_merge($data, [
            'discount' => $discount,
            'tax' => $tax,
            'final_amount' => round($taxable + $tax, 2),
            'features' => $data['features'] ?? [],
        ]));

        audit_log('plan.updated', 'memberships', $plan->id, "Updated membership plan {$plan->name}");

        return redirect()->route('memberships.plans.index')->with('success', 'Plan updated successfully.');
    }

    public function toggle(MembershipPlan $plan)
    {
        $plan->update(['status' => $plan->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'Plan status updated.');
    }

    public function destroy(MembershipPlan $plan)
    {
        if ($plan->memberships()->exists()) {
            return back()->withErrors(['plan' => 'This plan has memberships and cannot be deleted. Deactivate it instead.']);
        }

        $plan->delete();

        audit_log('plan.deleted', 'memberships', $plan->id, "Deleted membership plan {$plan->name}");

        return back()->with('success', 'Plan deleted.');
    }
}
