<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Client;
use App\Models\StaffAttendance;
use App\Models\StaffProfile;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $attendance) {}

    public function index(Request $request)
    {
        $gymId = current_gym()?->id;
        $date = $request->input('date', now()->toDateString());

        $query = Attendance::with(['client.user', 'client.activeMembership.plan'])
            ->where('gym_id', $gymId)
            ->whereDate('attendance_date', $date);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('member_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $records = $query->orderByDesc('check_in')->paginate(20)->withQueryString();

        $todayCount = Attendance::where('gym_id', $gymId)->whereDate('attendance_date', $date)->count();
        $checkedIn = Attendance::where('gym_id', $gymId)->whereDate('attendance_date', $date)->whereNotNull('check_in')->count();
        $checkedOut = Attendance::where('gym_id', $gymId)->whereDate('attendance_date', $date)->whereNotNull('check_out')->count();

        $clients = Client::with('user')
            ->where('gym_id', $gymId)
            ->where('status', 'active')
            ->orderBy('member_id')
            ->get();

        return view('attendance.index', compact('records', 'date', 'todayCount', 'checkedIn', 'checkedOut', 'clients'));
    }

    public function checkIn(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('attendance.manage'), 403);

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'source' => ['nullable', 'string', 'in:manual,reception,member_id'],
            'notes' => ['nullable', 'string'],
        ]);

        $client = Client::findOrFail($data['client_id']);

        $result = $this->attendance->checkInClient($client, $data['source'] ?? 'reception', $data['notes'] ?? null);

        if (! $result['success']) {
            return back()->withErrors(['client' => $result['message']]);
        }

        return back()->with('success', 'Checked in: ' . $client->display_name);
    }

    public function checkOut(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('attendance.manage'), 403);

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
        ]);

        $client = Client::findOrFail($data['client_id']);

        $result = $this->attendance->checkOutClient($client);

        if (! $result['success']) {
            return back()->withErrors(['client' => $result['message']]);
        }

        return back()->with('success', 'Checked out: ' . $client->display_name);
    }

    public function checkoutAll()
    {
        abort_unless(auth()->user()->hasPermission('attendance.manage'), 403);

        $gymId = current_gym()?->id;
        $today = now()->toDateString();

        $open = Attendance::where('gym_id', $gymId)
            ->whereDate('attendance_date', $today)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->get();

        foreach ($open as $record) {
            $record->update([
                'check_out' => now()->format('H:i'),
                'duration_minutes' => $this->diffMinutes($record->check_in, now()->format('H:i')),
            ]);
        }

        return back()->with('success', "Checked out {$open->count()} members.");
    }

    public function staff(Request $request)
    {
        $gymId = current_gym()?->id;
        $date = $request->input('date', now()->toDateString());

        $records = StaffAttendance::with('staff.user')
            ->where('gym_id', $gymId)
            ->whereDate('attendance_date', $date)
            ->orderByDesc('check_in')
            ->paginate(20)->withQueryString();

        $present = StaffAttendance::where('gym_id', $gymId)->whereDate('attendance_date', $date)->whereNotNull('check_in')->count();
        $totalStaff = StaffProfile::where('gym_id', $gymId)->count();

        $myStaff = auth()->user()->staffProfile;
        $myRecord = $myStaff
            ? StaffAttendance::where('staff_id', $myStaff->id)->whereDate('attendance_date', $date)->first()
            : null;

        return view('attendance.staff', compact('records', 'date', 'present', 'totalStaff', 'myRecord'));
    }

    public function staffCheckIn()
    {
        abort_unless(auth()->user()->hasPermission('attendance.staff'), 403);

        $staff = auth()->user()->staffProfile;

        if (! $staff) {
            return back()->withErrors(['staff' => 'No staff profile found for your account.']);
        }

        $result = $this->attendance->staffCheckIn($staff);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function staffCheckOut()
    {
        abort_unless(auth()->user()->hasPermission('attendance.staff'), 403);

        $staff = auth()->user()->staffProfile;

        if (! $staff) {
            return back()->withErrors(['staff' => 'No staff profile found for your account.']);
        }

        $result = $this->attendance->staffCheckOut($staff);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function diffMinutes(string $start, string $end): int
    {
        return (int) round((strtotime($end) - strtotime($start)) / 60);
    }
}
