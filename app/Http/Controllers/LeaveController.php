<?php

namespace App\Http\Controllers;

use App\Models\StaffLeave;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = StaffLeave::with(['staff.user'])
            ->where('gym_id', $gymId);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if (auth()->user()->hasRole('coach') && ! auth()->user()->isOwner()) {
            $query->where('staff_id', auth()->user()->staffProfile?->id);
        }

        $leaves = $query->orderByDesc('start_date')->paginate(15)->withQueryString();

        return view('staff.leaves', compact('leaves'));
    }

    public function create()
    {
        return view('staff.leave-create');
    }

    public function store(Request $request)
    {
        $staff = auth()->user()->staffProfile;

        if (! $staff) {
            return back()->withErrors(['leave' => 'Only staff members can request leave.']);
        }

        $data = $request->validate([
            'leave_type' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
        ]);

        $start = \Carbon\Carbon::parse($data['start_date']);
        $end = \Carbon\Carbon::parse($data['end_date']);
        $days = $start->diffInDays($end) + 1;

        StaffLeave::create(array_merge($data, [
            'gym_id' => current_gym()->id,
            'staff_id' => $staff->id,
            'days' => $days,
            'status' => 'pending',
        ]));

        audit_log('leave.requested', 'staff', null, "Leave requested by {$staff->display_name}");

        return redirect()->route('staff.leaves.index')->with('success', 'Leave request submitted.');
    }

    public function approve(StaffLeave $leave)
    {
        abort_unless(auth()->user()->isOwner() || auth()->user()->hasRole('manager'), 403);

        $leave->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        audit_log('leave.approved', 'staff', $leave->id, "Leave approved for {$leave->staff?->display_name}");

        return back()->with('success', 'Leave approved.');
    }

    public function reject(Request $request, StaffLeave $leave)
    {
        abort_unless(auth()->user()->isOwner() || auth()->user()->hasRole('manager'), 403);

        $data = $request->validate(['notes' => ['nullable', 'string']]);

        $leave->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'notes' => $data['notes'] ?? $leave->notes,
        ]);

        audit_log('leave.rejected', 'staff', $leave->id, "Leave rejected for {$leave->staff?->display_name}");

        return back()->with('success', 'Leave rejected.');
    }
}
