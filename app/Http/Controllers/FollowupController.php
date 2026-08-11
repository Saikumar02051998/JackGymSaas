<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Followup;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

class FollowupController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $filter = $request->input('filter', 'today');

        $query = Followup::with(['client.user'])
            ->where('gym_id', $gymId);

        if (auth()->user()->hasRole('coach') && ! auth()->user()->isOwner()) {
            $query->where('staff_id', auth()->user()->staffProfile?->id);
        }

        switch ($filter) {
            case 'overdue':
                $query->whereDate('follow_up_date', '<', now()->toDateString())->whereIn('status', ['pending', 'overdue']);
                break;
            case 'upcoming':
                $query->whereDate('follow_up_date', '>', now()->toDateString())->where('status', 'pending');
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
            default:
                $query->whereDate('follow_up_date', now()->toDateString())->whereIn('status', ['pending', 'overdue']);
        }

        $followups = $query->orderBy('follow_up_date')->orderBy('follow_up_time')->paginate(15)->withQueryString();

        return view('followups.index', compact('followups', 'filter'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('followups.create'), 403);

        $clients = Client::with('user')->where('gym_id', current_gym()?->id)->orderBy('created_at', 'desc')->take(200)->get();
        $staff = StaffProfile::with('user')->where('gym_id', current_gym()?->id)->get();

        return view('followups.create', compact('clients', 'staff'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('followups.create'), 403);

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'follow_up_date' => ['required', 'date'],
            'follow_up_time' => ['nullable'],
            'type' => ['required', 'string', 'max:50'],
            'staff_id' => ['nullable', 'exists:staff_profiles,id'],
            'notes' => ['nullable', 'string'],
        ]);

        Followup::create(array_merge($data, [
            'gym_id' => current_gym()->id,
            'staff_id' => $data['staff_id'] ?? auth()->user()->staffProfile?->id,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]));

        audit_log('followup.created', 'followups', null, 'Follow-up scheduled');

        return redirect()->route('followups.index')->with('success', 'Follow-up scheduled.');
    }

    public function complete(Request $request, Followup $followup)
    {
        abort_unless(auth()->user()->hasPermission('followups.manage'), 403);

        $data = $request->validate(['outcome' => ['nullable', 'string', 'max:255']]);

        $followup->update([
            'status' => 'completed',
            'outcome' => $data['outcome'] ?? null,
        ]);

        return back()->with('success', 'Follow-up completed.');
    }

    public function reschedule(Request $request, Followup $followup)
    {
        abort_unless(auth()->user()->hasPermission('followups.manage'), 403);

        $data = $request->validate([
            'follow_up_date' => ['required', 'date'],
            'follow_up_time' => ['nullable'],
        ]);

        $followup->update(array_merge($data, ['status' => 'rescheduled']));

        return back()->with('success', 'Follow-up rescheduled.');
    }

    public function cancel(Followup $followup)
    {
        abort_unless(auth()->user()->hasPermission('followups.manage'), 403);

        $followup->update(['status' => 'cancelled']);

        return back()->with('success', 'Follow-up cancelled.');
    }

    public function destroy(Followup $followup)
    {
        abort_unless(auth()->user()->hasPermission('followups.manage'), 403);

        $followup->delete();

        return back()->with('success', 'Follow-up deleted.');
    }
}
