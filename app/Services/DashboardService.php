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

        $clientStats = Client::where('gym_id', $gymId)
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status = 'active' then 1 else 0 end) as active")
            ->first();

        $memberStats = Membership::where('gym_id', $gymId)
            ->selectRaw("sum(case when status = 'active' and end_date >= ? then 1 else 0 end) as active", [$today])
            ->selectRaw("sum(case when status = 'expired' then 1 else 0 end) as expired")
            ->selectRaw("sum(case when status = 'upcoming' then 1 else 0 end) as upcoming")
            ->selectRaw("sum(case when status = 'cancelled' then 1 else 0 end) as cancelled")
            ->selectRaw("sum(case when status = 'frozen' then 1 else 0 end) as frozen")
            ->selectRaw("sum(case when status = 'active' and end_date >= ? and end_date <= ? then 1 else 0 end) as expiring", [$today, now()->addDays(30)->toDateString()])
            ->selectRaw("sum(case when start_date between ? and ? then 1 else 0 end) as new_month", [$monthStart, $monthEnd])
            ->first();

        $paymentStats = Payment::where('gym_id', $gymId)
            ->selectRaw("sum(case when status = 'success' then final_amount else 0 end) as revenue_total")
            ->selectRaw("sum(case when status = 'success' and payment_date = ? then final_amount else 0 end) as revenue_today", [$today])
            ->selectRaw("sum(case when status = 'success' and payment_date between ? and ? then final_amount else 0 end) as revenue_month", [$monthStart, $monthEnd])
            ->selectRaw("sum(case when status in ('pending', 'processing') then final_amount else 0 end) as pending_payments")
            ->first();

        $expenseStats = Expense::where('gym_id', $gymId)
            ->selectRaw('sum(amount) as total')
            ->selectRaw("sum(case when expense_date between ? and ? then amount else 0 end) as month", [$monthStart, $monthEnd])
            ->first();

        $leadStats = Lead::where('gym_id', $gymId)
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status = 'converted' then 1 else 0 end) as converted")
            ->selectRaw("sum(case when created_at between ? and ? then 1 else 0 end) as new_month", [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])
            ->first();

        return [
            'total_clients' => (int) ($clientStats->total ?? 0),
            'active_members' => (int) ($memberStats->active ?? 0),
            'expiring_members' => (int) ($memberStats->expiring ?? 0),
            'expired_members' => (int) ($memberStats->expired ?? 0),
            'new_members_month' => (int) ($memberStats->new_month ?? 0),
            'revenue_month' => (float) ($paymentStats->revenue_month ?? 0),
            'revenue_today' => (float) ($paymentStats->revenue_today ?? 0),
            'revenue_total' => (float) ($paymentStats->revenue_total ?? 0),
            'pending_payments' => (float) ($paymentStats->pending_payments ?? 0),
            'expenses_month' => (float) ($expenseStats->month ?? 0),
            'expenses_total' => (float) ($expenseStats->total ?? 0),
            'net_income_month' => (float) (($paymentStats->revenue_month ?? 0) - ($expenseStats->month ?? 0)),
            'staff_count' => StaffProfile::where('gym_id', $gymId)->count(),
            'today_attendance' => Attendance::where('gym_id', $gymId)->whereDate('attendance_date', $today)->count(),
            'total_leads' => (int) ($leadStats->total ?? 0),
            'new_leads_month' => (int) ($leadStats->new_month ?? 0),
            'converted_leads' => (int) ($leadStats->converted ?? 0),
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

        $start = now()->startOfMonth()->subMonths($months - 1);
        $labels = [];
        $revenue = [];
        $expenses = [];

        $revenueRows = Payment::where('gym_id', $gymId)->where('status', 'success')
            ->where('payment_date', '>=', $start->toDateString())
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as ym, SUM(final_amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym')
            ->all();

        $expenseRows = Expense::where('gym_id', $gymId)
            ->where('expense_date', '>=', $start->toDateString())
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym')
            ->all();

        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = now()->startOfMonth()->subMonths($i)->format('Y-m');

            $labels[] = now()->startOfMonth()->subMonths($i)->format('M Y');
            $revenue[] = (float) ($revenueRows[$ym] ?? 0);
            $expenses[] = (float) ($expenseRows[$ym] ?? 0);
        }

        return ['labels' => $labels, 'revenue' => $revenue, 'expenses' => $expenses];
    }

    public function attendanceChart(int $days = 14): array
    {
        $gymId = current_gym()?->id;

        $start = now()->subDays($days - 1)->toDateString();
        $labels = [];
        $counts = [];

        $rows = Attendance::where('gym_id', $gymId)
            ->where('attendance_date', '>=', $start)
            ->selectRaw('attendance_date as d, count(*) as c')
            ->groupBy('attendance_date')
            ->pluck('c', 'd')
            ->all();

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d M');
            $counts[] = (int) ($rows[$date] ?? 0);
        }

        return ['labels' => $labels, 'counts' => $counts];
    }

    public function membershipChart(): array
    {
        $gymId = current_gym()?->id;

        $rows = Membership::where('gym_id', $gymId)
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();

        return [
            'active' => (int) ($rows['active'] ?? 0),
            'upcoming' => (int) ($rows['upcoming'] ?? 0),
            'expired' => (int) ($rows['expired'] ?? 0),
            'cancelled' => (int) ($rows['cancelled'] ?? 0),
            'frozen' => (int) ($rows['frozen'] ?? 0),
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
