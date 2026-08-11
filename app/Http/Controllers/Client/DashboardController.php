<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index()
    {
        $client = auth()->user()->clientProfile;

        if (! $client) {
            return redirect()->route('dashboard');
        }

        $client->load([
            'activeMembership.plan',
            'memberships' => fn ($q) => $q->orderByDesc('start_date')->take(3),
        ]);

        $todayAttendance = $client->attendance()->whereDate('attendance_date', now()->toDateString())->first();

        $stats = [
            'check_ins' => $client->attendance()->where('status', 'present')->count(),
            'this_month' => $client->attendance()
                ->where('status', 'present')
                ->whereBetween('attendance_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->count(),
            'active_workouts' => $client->workoutPlans()->where('status', 'active')->count(),
            'active_diets' => $client->dietPlans()->where('status', 'active')->count(),
            'upcoming_appointments' => $client->appointments()
                ->where('status', 'scheduled')
                ->whereDate('appointment_date', '>=', now()->toDateString())
                ->count(),
            'pending_due' => (float) $client->payments()->whereIn('status', ['pending', 'processing'])->sum('final_amount'),
        ];

        $weightRecords = $client->weightRecords()->orderByDesc('record_date')->take(10)->get()->reverse()->values();

        $announcements = Announcement::where('gym_id', current_gym()?->id)
            ->where('status', 'published')
            ->whereIn('audience', ['all', 'clients', 'client'])
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString()))
            ->latest()
            ->take(5)
            ->get();

        return view('client.dashboard', compact('client', 'stats', 'todayAttendance', 'weightRecords', 'announcements'));
    }
}
