<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\BodyMeasurement;
use App\Models\FitnessGoal;
use App\Models\WeightRecord;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index()
    {
        $client = auth()->user()->clientProfile;

        $client->load([
            'healthProfile',
            'weightRecords' => fn ($q) => $q->orderByDesc('record_date')->take(30),
            'bodyMeasurements' => fn ($q) => $q->orderByDesc('record_date')->take(10),
            'fitnessGoals' => fn ($q) => $q->orderByDesc('created_at'),
        ]);

        return view('client.progress', compact('client'));
    }

    public function store(Request $request)
    {
        $client = auth()->user()->clientProfile;

        $data = $request->validate([
            'weight' => ['required', 'numeric', 'min:1', 'max:500'],
            'record_date' => ['nullable', 'date'],
        ]);

        $height = $client->healthProfile?->height;

        WeightRecord::create([
            'gym_id' => current_gym()?->id,
            'client_id' => $client->id,
            'weight' => $data['weight'],
            'height' => $height,
            'bmi' => ($height && $height > 0) ? round($data['weight'] / (($height / 100) ** 2), 1) : null,
            'record_date' => $data['record_date'] ?? now()->toDateString(),
            'created_by' => auth()->id(),
        ]);

        $client->healthProfile?->update(['weight' => $data['weight']]);

        if ($request->filled('chest') || $request->filled('waist')) {
            BodyMeasurement::create([
                'gym_id' => current_gym()?->id,
                'client_id' => $client->id,
                'chest' => $request->input('chest'),
                'waist' => $request->input('waist'),
                'hip' => $request->input('hip'),
                'arms' => $request->input('arms'),
                'thigh' => $request->input('thigh'),
                'record_date' => $data['record_date'] ?? now()->toDateString(),
                'created_by' => auth()->id(),
            ]);
        }

        if ($request->filled('goal_type')) {
            FitnessGoal::create([
                'gym_id' => current_gym()?->id,
                'client_id' => $client->id,
                'type' => $request->input('goal_type'),
                'starting_value' => $request->input('starting_value'),
                'target_value' => $request->input('target_value'),
                'target_date' => $request->input('target_date'),
                'status' => 'active',
            ]);
        }

        return back()->with('success', 'Progress recorded.');
    }

    public function destroy(WeightRecord $progress)
    {
        abort_unless($progress->client_id === auth()->user()->clientProfile?->id, 403);

        $progress->delete();

        return back()->with('success', 'Record deleted.');
    }
}
