<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $client = auth()->user()->clientProfile;

        $query = $client->attendance()->orderByDesc('attendance_date');

        if ($request->filled('from')) {
            $query->whereDate('attendance_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('attendance_date', '<=', $request->input('to'));
        }

        $records = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => $client->attendance()->count(),
            'this_month' => $client->attendance()
                ->whereBetween('attendance_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->count(),
            'avg_duration' => (int) round($client->attendance()->avg('duration_minutes') ?? 0),
        ];

        return view('client.attendance', compact('records', 'stats'));
    }
}
