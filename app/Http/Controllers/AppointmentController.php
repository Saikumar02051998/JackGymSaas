<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;
        $date = $request->input('date', now()->toDateString());

        $query = Appointment::with(['client.user'])
            ->where('gym_id', $gymId);

        if (auth()->user()->hasRole('coach') && ! auth()->user()->isOwner()) {
            $query->where('staff_id', auth()->user()->staffProfile?->id);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $date);
        } else {
            $query->whereDate('appointment_date', '>=', now()->toDateString());
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $appointments = $query->orderBy('appointment_date')->orderBy('appointment_time')->paginate(20)->withQueryString();

        return view('appointments.index', compact('appointments', 'date'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('appointments.manage'), 403);

        $clients = Client::with('user')->where('gym_id', current_gym()?->id)
            ->orderBy('created_at', 'desc')->take(200)->get();

        $staff = StaffProfile::with('user')->where('gym_id', current_gym()?->id)->get();

        return view('appointments.create', compact('clients', 'staff'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('appointments.manage'), 403);

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'appointment_type' => ['required', 'in:pt,consultation,trial,followup,general'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['nullable'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'staff_id' => ['nullable', 'exists:staff_profiles,id'],
            'notes' => ['nullable', 'string'],
        ]);

        Appointment::create(array_merge($data, [
            'gym_id' => current_gym()->id,
            'staff_id' => $data['staff_id'] ?? auth()->user()->staffProfile?->id,
            'status' => 'scheduled',
            'created_by' => auth()->id(),
        ]));

        audit_log('appointment.created', 'schedule', null, 'Appointment scheduled');

        return redirect()->route('appointments.index')->with('success', 'Appointment scheduled.');
    }

    public function complete(Appointment $appointment)
    {
        abort_unless(auth()->user()->hasPermission('appointments.manage'), 403);

        $appointment->update(['status' => 'completed']);

        return back()->with('success', 'Appointment completed.');
    }

    public function cancel(Appointment $appointment)
    {
        abort_unless(auth()->user()->hasPermission('appointments.manage'), 403);

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Appointment cancelled.');
    }
}
