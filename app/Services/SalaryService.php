<?php

namespace App\Services;

use App\Models\Salary;
use App\Models\StaffLeave;
use App\Models\StaffProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalaryService
{
    public function computeNet(array $data): array
    {
        $basic = (float) ($data['basic'] ?? 0);
        $allowances = (float) ($data['allowances'] ?? 0);
        $deductions = (float) ($data['deductions'] ?? 0);
        $bonus = (float) ($data['bonus'] ?? 0);
        $commission = (float) ($data['commission'] ?? 0);
        $incentives = (float) ($data['incentives'] ?? 0);
        $advance = (float) ($data['advance'] ?? 0);

        $net = $basic + $allowances + $bonus + $commission + $incentives - $deductions - $advance;

        return [
            'basic' => $basic,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'bonus' => $bonus,
            'commission' => $commission,
            'incentives' => $incentives,
            'advance' => $advance,
            'net_salary' => round($net, 2),
        ];
    }

    public function leaveDeduction(StaffProfile $staff, string $period, float $gross = 0): array
    {
        $gym = $staff->gym ?? current_gym();

        $calendarDays = (int) ($gym?->setting('salary_calendar_days', 30) ?? 30);
        $paidLeaveDays = (int) ($gym?->setting('salary_paid_leave_days', 2) ?? 2);
        $paidHalfDays = (int) ($gym?->setting('salary_paid_half_days', 4) ?? 4);

        $month = Carbon::parse($period.'-01');
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $leaves = $staff->leaves()
            ->where('status', 'approved')
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->get();

        $fullDays = 0.0;
        $halfDays = 0.0;

        foreach ($leaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date);
            $leaveEnd = Carbon::parse($leave->end_date);
            $overlapStart = $leaveStart->greaterThan($start) ? $leaveStart : $start;
            $overlapEnd = $leaveEnd->lessThan($end) ? $leaveEnd : $end;

            if ($overlapEnd->lt($overlapStart)) {
                continue;
            }

            $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;

            if ($leave->is_half_day) {
                $halfDays += $overlapDays * 0.5;
            } else {
                $fullDays += $overlapDays;
            }
        }

        $excessFull = max(0.0, $fullDays - $paidLeaveDays);
        $excessHalf = max(0.0, $halfDays - $paidHalfDays);

        $perDay = $calendarDays > 0 ? $gross / $calendarDays : 0;

        $deduction = round($excessFull * $perDay + $excessHalf * $perDay * 0.5, 2);

        return [
            'full_days' => round($fullDays, 2),
            'half_days' => round($halfDays, 2),
            'paid_leave_days' => $paidLeaveDays,
            'paid_half_days' => $paidHalfDays,
            'excess_full_days' => round($excessFull, 2),
            'excess_half_days' => round($excessHalf, 2),
            'calendar_days' => $calendarDays,
            'per_day' => round($perDay, 2),
            'deduction' => $deduction,
        ];
    }

    public function createOrUpdate(StaffProfile $staff, array $data, bool $notify = true): Salary
    {
        return DB::transaction(function () use ($staff, $data, $notify) {
            $amounts = $this->computeNet($data);

            $salary = Salary::updateOrCreate(
                ['staff_id' => $staff->id, 'period' => $data['period']],
                array_merge($amounts, [
                    'gym_id' => $staff->gym_id,
                    'payment_date' => $data['payment_date'] ?? null,
                    'payment_status' => $data['payment_status'] ?? 'pending',
                    'notes' => $data['notes'] ?? null,
                    'created_by' => auth()->id(),
                ])
            );

            $salary->items()->delete();

            $items = [];
            if ($amounts['allowances'] > 0) {
                $items[] = ['label' => 'Allowances', 'type' => 'allowance', 'amount' => $amounts['allowances']];
            }
            if ($amounts['bonus'] > 0) {
                $items[] = ['label' => 'Bonus', 'type' => 'bonus', 'amount' => $amounts['bonus']];
            }
            if ($amounts['commission'] > 0) {
                $items[] = ['label' => 'Commission', 'type' => 'commission', 'amount' => $amounts['commission']];
            }
            if ($amounts['incentives'] > 0) {
                $items[] = ['label' => 'Incentives', 'type' => 'incentive', 'amount' => $amounts['incentives']];
            }
            if ($amounts['deductions'] > 0) {
                $items[] = ['label' => 'Deductions', 'type' => 'deduction', 'amount' => $amounts['deductions']];
            }
            if ($amounts['advance'] > 0) {
                $items[] = ['label' => 'Advance', 'type' => 'advance', 'amount' => $amounts['advance']];
            }

            foreach ($items as $item) {
                $salary->items()->create($item);
            }

            if ($notify) {
                $notif = app(NotificationService::class);
                $notif->salaryProcessed($staff->user, $data['period']);
            }

            audit_log('salary.created', 'salaries', $salary->id, "Salary processed for {$staff->display_name} for {$data['period']}");

            return $salary;
        });
    }

    public function updateStatus(Salary $salary, string $status): Salary
    {
        if ($status === 'paid' && ! $salary->payment_date) {
            $salary->update(['payment_date' => now()->toDateString()]);
        }

        $salary->update(['payment_status' => $status]);

        audit_log('salary.status_changed', 'salaries', $salary->id, "Salary for {$salary->staff?->display_name} marked {$status}");

        return $salary;
    }
}
