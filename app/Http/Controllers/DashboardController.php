<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboard) {}

    public function index()
    {
        $user = auth()->user();

        if ($user->isClient()) {
            return redirect()->route('client.dashboard');
        }

        $stats = $this->dashboard->ownerStats();
        $revenueChart = $this->dashboard->revenueChart(6);
        $attendanceChart = $this->dashboard->attendanceChart(14);
        $membershipChart = $this->dashboard->membershipChart();
        $paymentMethodChart = $this->dashboard->paymentMethodChart();

        if ($user->hasRole('coach')) {
            $staff = $user->staffProfile;
            $coachStats = $this->dashboard->coachStats($staff?->id, $staff);

            return view('dashboards.coach', compact('coachStats', 'stats', 'revenueChart'));
        }

        return view('dashboards.owner', compact('stats', 'revenueChart', 'attendanceChart', 'membershipChart', 'paymentMethodChart'));
    }
}
