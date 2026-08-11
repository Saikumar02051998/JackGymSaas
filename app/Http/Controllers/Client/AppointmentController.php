<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        $client = auth()->user()->clientProfile;

        $upcoming = $client->appointments()
            ->with('staff.user')
            ->where('status', 'scheduled')
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $past = $client->appointments()
            ->whereIn('status', ['completed', 'cancelled', 'no_show'])
            ->orWhere('appointment_date', '<', now()->toDateString())
            ->orderByDesc('appointment_date')
            ->take(20)
            ->get();

        return view('client.appointments', compact('upcoming', 'past'));
    }

    public function store(Request $request)
    {
        $client = auth()->user()->clientProfile;

        $data = $request->validate([
            'appointment_type' => ['required', 'in:pt,consultation,trial,followup,general'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['nullable'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'notes' => ['nullable', 'string'],
        ]);

        Appointment::create(array_merge($data, [
            'gym_id' => current_gym()?->id,
            'client_id' => $client->id,
            'status' => 'scheduled',
            'created_by' => auth()->id(),
        ]));

        return back()->with('success', 'Appointment requested.');
    }

    public function destroy(Appointment $appointment)
    {
        abort_unless($appointment->client_id === auth()->user()->clientProfile?->id, 403);

        if ($appointment->status === 'scheduled') {
            $appointment->update(['status' => 'cancelled']);
        }

        return back()->with('success', 'Appointment cancelled.');
    }
}
