<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\StaffAttendance;
use Carbon\Carbon;

class ReportService
{
    public function clientReport(?string $from = null, ?string $to = null): array
    {
        $gymId = current_gym()?->id;
        $from = $from ? Carbon::parse($from) : now()->startOfMonth();
        $to = $to ? Carbon::parse($to) : now();

        return [
            'total_clients' => Client::where('gym_id', $gymId)->count(),
            'new_clients' => Client::where('gym_id', $gymId)
                ->whereBetween('joining_date', [$from->toDateString(), $to->toDateString()])->count(),
            'active_members' => Membership::where('gym_id', $gymId)->where('status', 'active')
                ->whereDate('end_date', '>=', now()->toDateString())->count(),
            'expired_members' => Membership::where('gym_id', $gymId)->where('status', 'expired')->count(),
            'inactive_clients' => Client::where('gym_id', $gymId)->where('status', 'inactive')->count(),
            'recent_clients' => Client::with(['user', 'activeMembership'])
                ->where('gym_id', $gymId)
                ->whereBetween('joining_date', [$from->toDateString(), $to->toDateString()])
                ->orderByDesc('joining_date')->paginate(15),
        ];
    }

    public function revenueReport(?string $from = null, ?string $to = null): array
    {
        $gymId = current_gym()?->id;
        $from = $from ? Carbon::parse($from) : now()->startOfMonth();
        $to = $to ? Carbon::parse($to) : now();

        $payments = Payment::with(['client.user', 'plan'])
            ->where('gym_id', $gymId)->where('status', 'success')
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('payment_date');

        return [
            'total_revenue' => (clone $payments)->sum('final_amount'),
            'cash' => (clone $payments)->where('payment_method', 'cash')->sum('final_amount'),
            'upi' => (clone $payments)->where('payment_method', 'upi')->sum('final_amount'),
            'card' => (clone $payments)->where('payment_method', 'card')->sum('final_amount'),
            'bank' => (clone $payments)->where('payment_method', 'bank_transfer')->sum('final_amount'),
            'razorpay' => (clone $payments)->where('gateway', 'razorpay')->sum('final_amount'),
            'payment_count' => (clone $payments)->count(),
            'payments' => $payments->paginate(15),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    public function expenseReport(?string $from = null, ?string $to = null): array
    {
        $gymId = current_gym()?->id;
        $from = $from ? Carbon::parse($from) : now()->startOfMonth();
        $to = $to ? Carbon::parse($to) : now();

        $expenses = Expense::with('category')
            ->where('gym_id', $gymId)
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()]);

        return [
            'total_expenses' => (clone $expenses)->sum('amount'),
            'by_category' => (clone $expenses)
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')->with('category')->get()
                ->map(fn ($e) => ['category' => $e->category?->name ?? 'Unknown', 'total' => (float) $e->total]),
            'expenses' => $expenses->orderByDesc('expense_date')->paginate(15),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    public function attendanceReport(?string $from = null, ?string $to = null): array
    {
        $gymId = current_gym()?->id;
        $from = $from ? Carbon::parse($from) : now()->startOfMonth();
        $to = $to ? Carbon::parse($to) : now();

        $records = Attendance::with('client.user')
            ->where('gym_id', $gymId)
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('attendance_date');

        return [
            'total_visits' => (clone $records)->count(),
            'unique_clients' => (clone $records)->distinct('client_id')->count('client_id'),
            'avg_duration' => (int) round((clone $records)->avg('duration_minutes') ?? 0),
            'daily' => (clone $records)
                ->selectRaw('attendance_date, COUNT(*) as total')
                ->groupBy('attendance_date')->orderBy('attendance_date')->get(),
            'records' => $records->paginate(20),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    public function staffAttendanceReport(?string $from = null, ?string $to = null): array
    {
        $gymId = current_gym()?->id;
        $from = $from ? Carbon::parse($from) : now()->startOfMonth();
        $to = $to ? Carbon::parse($to) : now();

        $records = StaffAttendance::with('staff.user')
            ->where('gym_id', $gymId)
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('attendance_date');

        return [
            'records' => $records->paginate(20),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    public function leadReport(?string $from = null, ?string $to = null): array
    {
        $gymId = current_gym()?->id;
        $from = $from ? Carbon::parse($from) : now()->startOfMonth();
        $to = $to ? Carbon::parse($to) : now();

        $leads = Lead::where('gym_id', $gymId)
            ->whereBetween('created_at', [$from->startOfDay()->toDateTimeString(), $to->endOfDay()->toDateTimeString()]);

        $total = (clone $leads)->count();
        $converted = (clone $leads)->where('status', 'converted')->count();

        $bySource = (clone $leads)
            ->selectRaw('COALESCE(source, "other") as source, COUNT(*) as total')
            ->groupBy('source')->get()
            ->map(fn ($l) => ['source' => $l->source, 'total' => $l->total]);

        return [
            'total_leads' => $total,
            'converted' => $converted,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0,
            'by_source' => $bySource,
            'leads' => Lead::with('assignedTo')
                ->where('gym_id', $gymId)
                ->whereBetween('created_at', [$from->startOfDay()->toDateTimeString(), $to->endOfDay()->toDateTimeString()])
                ->orderByDesc('created_at')->paginate(15),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }
}
