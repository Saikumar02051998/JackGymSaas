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

        $gym = current_gym();
        $rules = [
            'calendar_days' => (int) $gym?->setting('salary_calendar_days', 30),
            'paid_leave_days' => (int) $gym?->setting('salary_paid_leave_days', 2),
            'paid_half_days' => (int) $gym?->setting('salary_paid_half_days', 4),
        ];

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $monthLeaves = StaffLeave::where('gym_id', $gymId)
            ->where('start_date', '<=', $monthEnd)
            ->where('end_date', '>=', $monthStart)
            ->get();

        $overallLeaves = [
            'pending' => $monthLeaves->where('status', 'pending')->count(),
            'pending_days' => round($monthLeaves->where('status', 'pending')->sum('days'), 1),
            'approved' => $monthLeaves->where('status', 'approved')->count(),
            'approved_days' => round($monthLeaves->where('status', 'approved')->sum('days'), 1),
            'total' => $monthLeaves->count(),
        ];

        $myLeaves = null;
        if ($profile = auth()->user()->staffProfile) {
            $myMonthLeaves = $monthLeaves->where('staff_id', $profile->id);

            $myLeaves = [
                'pending' => $myMonthLeaves->where('status', 'pending')->count(),
                'pending_days' => round($myMonthLeaves->where('status', 'pending')->sum('days'), 1),
                'approved' => $myMonthLeaves->where('status', 'approved')->count(),
                'approved_days' => round($myMonthLeaves->where('status', 'approved')->sum('days'), 1),
                'total' => $myMonthLeaves->count(),
            ];
        }

        return view('staff.leaves', compact('leaves', 'rules', 'myLeaves', 'overallLeaves'));
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
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
            'is_half_day' => ['nullable'],
        ]);

        $halfDay = (bool) ($data['is_half_day'] ?? false);

        $start = \Carbon\Carbon::parse($data['start_date']);
        $end = $halfDay
            ? $start->copy()
            : \Carbon\Carbon::parse($data['end_date'] ?? $data['start_date']);
        $days = $halfDay ? 0.5 : ($start->diffInDays($end) + 1);

        StaffLeave::create([
            'gym_id' => current_gym()->id,
            'staff_id' => $staff->id,
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $end->toDateString(),
            'is_half_day' => $halfDay,
            'days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

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
