<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DietPlan;
use Illuminate\Http\Request;

class DietController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = DietPlan::with(['client.user'])
            ->where('gym_id', $gymId);

        if (auth()->user()->hasRole('nutritionist') && ! auth()->user()->isOwner()) {
            $query->where('nutritionist_id', auth()->user()->staffProfile?->id);
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $plans = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('diets.index', compact('plans'));
    }

    public function create()
    {
        $clients = Client::with('user')->where('gym_id', current_gym()?->id)
            ->where('status', 'active')->orderBy('created_at', 'desc')->take(200)->get();

        return view('diets.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'goal' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:draft,active,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $plan = DietPlan::create(array_merge($data, [
            'gym_id' => current_gym()->id,
            'nutritionist_id' => auth()->user()->staffProfile?->id,
            'status' => $data['status'] ?? 'active',
        ]));

        $meals = $request->input('meals', []);

        foreach ($meals as $meal) {
            if (empty($meal['food'])) {
                continue;
            }

            $plan->meals()->create([
                'meal' => $meal['meal'] ?? 'General',
                'meal_time' => $meal['meal_time'] ?? null,
                'food' => $meal['food'],
                'quantity' => $meal['quantity'] ?? null,
                'calories' => $meal['calories'] ?? null,
                'protein' => $meal['protein'] ?? null,
                'carbs' => $meal['carbs'] ?? null,
                'fat' => $meal['fat'] ?? null,
                'notes' => $meal['notes'] ?? null,
                'sort_order' => (int) ($meal['sort_order'] ?? 0),
            ]);
        }

        audit_log('diet.created', 'fitness', $plan->id, "Created diet plan {$plan->name}");

        return redirect()->route('diets.show', $plan)->with('success', 'Diet plan created.');
    }

    public function show(DietPlan $diet)
    {
        $diet->load(['client.user', 'meals' => fn ($q) => $q->orderBy('sort_order')]);

        return view('diets.show', compact('diet'));
    }

    public function edit(DietPlan $diet)
    {
        $clients = Client::with('user')->where('gym_id', current_gym()?->id)
            ->where('status', 'active')->orderBy('created_at', 'desc')->take(200)->get();

        $diet->load('meals');

        return view('diets.edit', compact('diet', 'clients'));
    }

    public function update(Request $request, DietPlan $diet)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'goal' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:draft,active,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $diet->update($data);

        $diet->meals()->delete();

        $meals = $request->input('meals', []);

        foreach ($meals as $meal) {
            if (empty($meal['food'])) {
                continue;
            }

            $diet->meals()->create([
                'meal' => $meal['meal'] ?? 'General',
                'meal_time' => $meal['meal_time'] ?? null,
                'food' => $meal['food'],
                'quantity' => $meal['quantity'] ?? null,
                'calories' => $meal['calories'] ?? null,
                'protein' => $meal['protein'] ?? null,
                'carbs' => $meal['carbs'] ?? null,
                'fat' => $meal['fat'] ?? null,
                'notes' => $meal['notes'] ?? null,
                'sort_order' => (int) ($meal['sort_order'] ?? 0),
            ]);
        }

        return redirect()->route('diets.show', $diet)->with('success', 'Diet plan updated.');
    }

    public function destroy(DietPlan $diet)
    {
        $diet->meals()->delete();
        $diet->delete();

        return redirect()->route('diets.index')->with('success', 'Diet plan deleted.');
    }

    public function toggle(DietPlan $diet)
    {
        $diet->update(['status' => $diet->status === 'active' ? 'draft' : 'active']);

        return back()->with('success', 'Diet plan status updated.');
    }
}
