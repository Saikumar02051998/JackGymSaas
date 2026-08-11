<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\PersonalTrainingSession;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

class PtSessionController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = PersonalTrainingSession::with(['client.user', 'trainer.user'])
            ->where('gym_id', $gymId);

        if (auth()->user()->hasRole('coach') && ! auth()->user()->isOwner()) {
            $query->where('trainer_id', auth()->user()->staffProfile?->id);
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from')) {
            $query->whereDate('session_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('session_date', '<=', $request->input('to'));
        }

        $sessions = $query->orderByDesc('session_date')->orderByDesc('session_time')->paginate(15)->withQueryString();

        return view('pt.index', compact('sessions'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('pt.manage'), 403);

        $clients = Client::with('user')->where('gym_id', current_gym()?->id)
            ->where('status', 'active')->orderBy('created_at', 'desc')->take(200)->get();

        $trainers = StaffProfile::with('user')->where('gym_id', current_gym()?->id)
            ->whereHas('user.roles', fn ($q) => $q->where('slug', 'coach'))
            ->get();

        return view('pt.create', compact('clients', 'trainers'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('pt.manage'), 403);

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'trainer_id' => ['required', 'exists:staff_profiles,id'],
            'session_date' => ['required', 'date'],
            'session_time' => ['nullable'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'session_no' => ['nullable', 'integer', 'min:1'],
            'package_sessions' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        PersonalTrainingSession::create(array_merge($data, [
            'gym_id' => current_gym()->id,
            'status' => 'scheduled',
        ]));

        audit_log('pt.created', 'schedule', null, 'PT session scheduled');

        return redirect()->route('pt.index')->with('success', 'PT session scheduled.');
    }

    public function complete(PersonalTrainingSession $ptSession)
    {
        abort_unless(auth()->user()->hasPermission('pt.manage'), 403);

        $ptSession->update(['status' => 'completed']);

        return back()->with('success', 'PT session marked completed.');
    }

    public function cancel(PersonalTrainingSession $ptSession)
    {
        abort_unless(auth()->user()->hasPermission('pt.manage'), 403);

        $ptSession->update(['status' => 'cancelled']);

        return back()->with('success', 'PT session cancelled.');
    }
}
