<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\WorkoutPlan;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = WorkoutPlan::with(['client.user'])
            ->where('gym_id', $gymId);

        if (auth()->user()->hasRole('coach') && ! auth()->user()->isOwner()) {
            $query->where('trainer_id', auth()->user()->staffProfile?->id);
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $plans = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('workouts.index', compact('plans'));
    }

    public function create()
    {
        $clients = Client::with('user')->where('gym_id', current_gym()?->id)
            ->where('status', 'active')->orderBy('created_at', 'desc')->take(200)->get();

        return view('workouts.create', compact('clients'));
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

        $plan = WorkoutPlan::create(array_merge($data, [
            'gym_id' => current_gym()->id,
            'trainer_id' => auth()->user()->staffProfile?->id,
            'status' => $data['status'] ?? 'active',
        ]));

        $exercises = $request->input('exercises', []);

        foreach ($exercises as $exercise) {
            if (empty($exercise['exercise'])) {
                continue;
            }

            $plan->exercises()->create([
                'day_of_week' => $exercise['day_of_week'] ?? null,
                'exercise' => $exercise['exercise'],
                'muscle_group' => $exercise['muscle_group'] ?? null,
                'sets' => $exercise['sets'] ?? null,
                'reps' => $exercise['reps'] ?? null,
                'weight' => $exercise['weight'] ?? null,
                'duration_minutes' => $exercise['duration_minutes'] ?? null,
                'rest_seconds' => $exercise['rest_seconds'] ?? null,
                'instructions' => $exercise['instructions'] ?? null,
                'sort_order' => (int) ($exercise['sort_order'] ?? 0),
            ]);
        }

        audit_log('workout.created', 'fitness', $plan->id, "Created workout plan {$plan->name}");

        return redirect()->route('workouts.show', $plan)->with('success', 'Workout plan created.');
    }

    public function show(WorkoutPlan $workout)
    {
        $workout->load(['client.user', 'exercises' => fn ($q) => $q->orderBy('sort_order')]);

        return view('workouts.show', compact('workout'));
    }

    public function edit(WorkoutPlan $workout)
    {
        $clients = Client::with('user')->where('gym_id', current_gym()?->id)
            ->where('status', 'active')->orderBy('created_at', 'desc')->take(200)->get();

        $workout->load('exercises');

        return view('workouts.edit', compact('workout', 'clients'));
    }

    public function update(Request $request, WorkoutPlan $workout)
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

        $workout->update($data);

        $workout->exercises()->delete();

        $exercises = $request->input('exercises', []);

        foreach ($exercises as $exercise) {
            if (empty($exercise['exercise'])) {
                continue;
            }

            $workout->exercises()->create([
                'day_of_week' => $exercise['day_of_week'] ?? null,
                'exercise' => $exercise['exercise'],
                'muscle_group' => $exercise['muscle_group'] ?? null,
                'sets' => $exercise['sets'] ?? null,
                'reps' => $exercise['reps'] ?? null,
                'weight' => $exercise['weight'] ?? null,
                'duration_minutes' => $exercise['duration_minutes'] ?? null,
                'rest_seconds' => $exercise['rest_seconds'] ?? null,
                'instructions' => $exercise['instructions'] ?? null,
                'sort_order' => (int) ($exercise['sort_order'] ?? 0),
            ]);
        }

        return redirect()->route('workouts.show', $workout)->with('success', 'Workout plan updated.');
    }

    public function destroy(WorkoutPlan $workout)
    {
        $workout->exercises()->delete();
        $workout->delete();

        return redirect()->route('workouts.index')->with('success', 'Workout plan deleted.');
    }

    public function toggle(WorkoutPlan $workout)
    {
        $workout->update(['status' => $workout->status === 'active' ? 'draft' : 'active']);

        return back()->with('success', 'Workout plan status updated.');
    }
}
