<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('saas.plans.view'), 403);

        $plans = SubscriptionPlan::withCount('gyms')->orderBy('price_monthly')->get();

        return view('saas.plans', compact('plans'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('saas.plans.manage'), 403);

        return view('saas.plans-create');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('saas.plans.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(4));

        SubscriptionPlan::create($data);

        audit_log('saas.plan.created', 'saas', null, "Subscription plan created: {$data['name']}");

        return redirect()->route('saas.plans.index')->with('success', 'Subscription plan created.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        abort_unless(auth()->user()->hasPermission('saas.plans.manage'), 403);

        return view('saas.plans-edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        abort_unless(auth()->user()->hasPermission('saas.plans.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $plan->update($data);

        audit_log('saas.plan.updated', 'saas', $plan->id, "Subscription plan updated: {$plan->name}");

        return redirect()->route('saas.plans.index')->with('success', 'Subscription plan updated.');
    }

    public function toggle(SubscriptionPlan $plan)
    {
        abort_unless(auth()->user()->hasPermission('saas.plans.manage'), 403);

        $plan->update(['status' => $plan->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'Subscription plan ' . ($plan->status === 'active' ? 'activated' : 'deactivated') . '.');
    }
}
