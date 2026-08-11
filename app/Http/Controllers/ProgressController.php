<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\WeightRecord;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = Client::with(['user', 'healthProfile'])
            ->where('gym_id', $gymId)
            ->where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('member_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if (auth()->user()->hasRole('coach') && ! auth()->user()->isOwner()) {
            $query->where('assigned_trainer_id', auth()->user()->staffProfile?->id);
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        foreach ($clients as $client) {
            $client->latest_weight = $client->weightRecords()->latest('record_date')->first()?->weight;
            $client->first_weight = $client->weightRecords()->oldest('record_date')->first()?->weight;
            $client->change = $client->first_weight !== null && $client->latest_weight !== null
                ? round($client->latest_weight - $client->first_weight, 1)
                : null;
            $client->last_weight_date = $client->weightRecords()->latest('record_date')->first()?->record_date;
        }

        return view('progress.index', compact('clients'));
    }

    public function logWeight(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('progress.manage'), 403);

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'weight' => ['required', 'numeric', 'min:1', 'max:500'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'body_fat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'record_date' => ['nullable', 'date'],
        ]);

        $client = Client::findOrFail($data['client_id']);

        $height = $data['height'] ?? $client->healthProfile?->height;

        WeightRecord::create([
            'gym_id' => current_gym()->id,
            'client_id' => $client->id,
            'weight' => $data['weight'],
            'height' => $height,
            'bmi' => ($height && $height > 0) ? round($data['weight'] / (($height / 100) ** 2), 1) : null,
            'body_fat' => $data['body_fat'] ?? null,
            'record_date' => $data['record_date'] ?? now()->toDateString(),
            'created_by' => auth()->id(),
        ]);

        audit_log('progress.weight_logged', 'fitness', $client->id, "Weight logged for {$client->display_name}");

        return back()->with('success', 'Weight recorded.');
    }

    public function destroy(WeightRecord $progress)
    {
        abort_unless(auth()->user()->hasPermission('progress.manage'), 403);

        $progress->delete();

        return back()->with('success', 'Weight record deleted.');
    }
}
