<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class WorkoutController extends Controller
{
    public function index()
    {
        $client = auth()->user()->clientProfile;

        $plans = $client->workoutPlans()
            ->with(['exercises' => fn ($q) => $q->orderBy('day_of_week')->orderBy('sort_order')])
            ->orderByDesc('created_at')
            ->get();

        return view('client.workouts', compact('plans'));
    }
}
