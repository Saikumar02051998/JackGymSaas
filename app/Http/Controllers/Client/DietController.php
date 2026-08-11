<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class DietController extends Controller
{
    public function index()
    {
        $client = auth()->user()->clientProfile;

        $plans = $client->dietPlans()
            ->with(['meals' => fn ($q) => $q->orderBy('sort_order')])
            ->orderByDesc('created_at')
            ->get();

        return view('client.diets', compact('plans'));
    }
}
