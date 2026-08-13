<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Followup;
use App\Models\Lead;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\StaffProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function ownerStats(): array
    {
        $gymId = current_gym()?->id;

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        return [
            'total_clients' => Client::where('gym_id', $gymId)->count(),
            'active_members' => Membership::where('gym_id', $gymId)->where('status', 'active')
                ->whereDate('end_date', '>=', $today)->count(),
            'expiring_members' => Membership::where('gym_id', $gymId)->where('status', 'active')
                ->whereBetween('end_date', [$today, now()->addDays(30)->toDateString()])->count(),
            'expired_members' => Membership::where('gym_id', $gymId)->where('status', 'expired')->count(),
            'new_members_month' => Membership::where('gym_id', $gymId)
                ->whereBetween('start_date', [$monthStart, $monthEnd])->count(),
            'revenue_month' => (float) Payment::where('gym_id', $gymId)->where('status', 'success')
                ->whereBetween('payment_date', [$monthStart, $monthEnd])->sum('final_amount'),
            'revenue_today' => (float) Payment::where('gym_id', $gymId)->where('status', 'success')
                ->where('payment_date', $today)->sum('final_amount'),
            'revenue_total' => (float) Payment::where('gym_id', $gymId)->where('status', 'success')->sum('final_amount'),
            'pending_payments' => (float) Payment::where('gym_id', $gymId)
                ->whereIn('status', ['pending', 'processing'])->sum('final_amount'),
            'expenses_month' => (float) Expense::where('gym_id', $gymId)
                ->whereBetween('expense_date', [$monthStart, $monthEnd])->sum('amount'),
            'expenses_total' => (float) Expense::where('gym_id', $gymId)->sum('amount'),
            'net_income_month' => 0,
            'staff_count' => StaffProfile::where('gym_id', $gymId)->count(),
            'today_attendance' => Attendance::where('gym_id', $gymId)->whereDate('attendance_date', $today)->count(),
            'total_leads' => Lead::where('gym_id', $gymId)->count(),
            'new_leads_month' => Lead::where('gym_id', $gymId)
                ->whereBetween('created_at', [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])->count(),
            'converted_leads' => Lead::where('gym_id', $gymId)->where('status', 'converted')->count(),
            'lead_conversion_rate' => 0,
            'upcoming_renewals' => Membership::with(['client.user', 'plan'])
                ->where('gym_id', $gymId)->where('status', 'active')
                ->whereBetween('end_date', [$today, now()->addDays(30)->toDateString()])
                ->orderBy('end_date')->limit(8)->get(),
            'recent_payments' => Payment::with(['client.user', 'plan'])
                ->where('gym_id', $gymId)
                ->orderByDesc('payment_date')->limit(8)->get(),
            'today_attendance_list' => Attendance::with('client.user')
                ->where('gym_id', $gymId)->whereDate('attendance_date', $today)
                ->orderByDesc('check_in')->limit(8)->get(),
            'recent_leads' => Lead::where('gym_id', $gymId)->orderByDesc('created_at')->limit(6)->get(),
            'expiring_trials' => \App\Models\Trial::with('client.user')
                ->where('gym_id', $gymId)->where('status', 'active')
                ->whereBetween('trial_end', [$today, now()->addDays(5)->toDateString()])
                ->orderBy('trial_end')->get(),
        ];
    }

    public function revenueChart(string $months = '6'): array
    {
        $gymId = current_gym()?->id;
        $labels = [];
        $revenue = [];
        $expenses = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->startOfMonth()->subMonths($i);
            $end = now()->startOfMonth()->subMonths($i)->endOfMonth();

            $labels[] = $start->format('M Y');
            $revenue[] = (float) Payment::where('gym_id', $gymId)->where('status', 'success')
                ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()])
                ->sum('final_amount');
            $expenses[] = (float) Expense::where('gym_id', $gymId)
                ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
                ->sum('amount');
        }

        return ['labels' => $labels, 'revenue' => $revenue, 'expenses' => $expenses];
    }

    public function attendanceChart(int $days = 14): array
    {
        $gymId = current_gym()?->id;
        $labels = [];
        $counts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d M');
            $counts[] = Attendance::where('gym_id', $gymId)->whereDate('attendance_date', $date)->count();
        }

        return ['labels' => $labels, 'counts' => $counts];
    }

    public function membershipChart(): array
    {
        $gymId = current_gym()?->id;

        return [
            'active' => Membership::where('gym_id', $gymId)->where('status', 'active')->count(),
            'upcoming' => Membership::where('gym_id', $gymId)->where('status', 'upcoming')->count(),
            'expired' => Membership::where('gym_id', $gymId)->where('status', 'expired')->count(),
            'cancelled' => Membership::where('gym_id', $gymId)->where('status', 'cancelled')->count(),
            'frozen' => Membership::where('gym_id', $gymId)->where('status', 'frozen')->count(),
        ];
    }

    public function paymentMethodChart(?string $from = null, ?string $to = null): array
    {
        $gymId = current_gym()?->id;

        $rows = Payment::where('gym_id', $gymId)->where('status', 'success')
            ->when($from, fn ($q) => $q->whereDate('payment_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('payment_date', '<=', $to))
            ->select('payment_method', DB::raw('SUM(final_amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->all();

        return [
            'labels' => array_map('ucfirst', array_keys($rows)),
            'values' => array_values($rows),
        ];
    }

    public function coachStats(int $staffId, ?StaffProfile $staff = null): array
    {
        $today = now()->toDateString();

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $monthLeaves = $staff
            ? $staff->leaves()
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date', '>=', $monthStart)
                ->get()
            : collect();

        $basic = (float) ($staff?->basic_salary ?? 0);
        $allowances = (float) ($staff?->allowances ?? 0);
        $period = now()->format('Y-m');
        $leaveDeduction = $staff
            ? app(SalaryService::class)->leaveDeduction($staff, $period, $basic + $allowances)['deduction']
            : 0.0;
        $grossSalary = $basic + $allowances;

        return [
            'assigned_clients' => Client::where('assigned_trainer_id', $staffId)->count(),
            'today_sessions' => \App\Models\PersonalTrainingSession::where('trainer_id', $staffId)
                ->whereDate('session_date', $today)->where('status', 'scheduled')->count(),
            'today_followups' => Followup::where('staff_id', $staffId)
                ->whereDate('follow_up_date', $today)->where('status', 'pending')->count(),
            'overdue_followups' => Followup::where('staff_id', $staffId)
                ->whereDate('follow_up_date', '<', $today)->whereIn('status', ['pending', 'overdue'])->count(),
            'active_plans' => \App\Models\WorkoutPlan::where('trainer_id', $staffId)->where('status', 'active')->count(),
            'total_pt_sessions' => \App\Models\PersonalTrainingSession::where('trainer_id', $staffId)->count(),
            'completed_pt_sessions' => \App\Models\PersonalTrainingSession::where('trainer_id', $staffId)->where('status', 'completed')->count(),
            'commission' => (float) ($staff?->commission_rate ?? 0),
            'basic_salary' => $basic,
            'allowances' => $allowances,
            'gross_salary' => round($grossSalary, 2),
            'leave_deduction' => round($leaveDeduction, 2),
            'expected_salary' => round($grossSalary - $leaveDeduction, 2),
            'pending_leaves' => $monthLeaves->where('status', 'pending')->count(),
            'pending_leave_days' => round($monthLeaves->where('status', 'pending')->sum('days'), 1),
            'taken_leaves' => $monthLeaves->where('status', 'approved')->count(),
            'taken_leave_days' => round($monthLeaves->where('status', 'approved')->sum('days'), 1),
            'total_leaves' => $monthLeaves->count(),
            'paid_leave_days' => (int) (current_gym()?->setting('salary_paid_leave_days', 2) ?? 2),
            'paid_half_days' => (int) (current_gym()?->setting('salary_paid_half_days', 4) ?? 4),
            'calendar_days' => (int) (current_gym()?->setting('salary_calendar_days', 30) ?? 30),
        ];
    }
}
