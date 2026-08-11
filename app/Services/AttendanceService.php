<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Client;
use App\Models\StaffAttendance;
use App\Models\StaffProfile;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function checkInClient(Client $client, string $source = 'reception', ?string $notes = null): array
    {
        $today = now()->toDateString();

        $existing = Attendance::where('client_id', $client->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existing && $existing->check_in) {
            return ['success' => false, 'message' => "{$client->display_name} has already checked in today at {$existing->check_in}.", 'duplicate' => true];
        }

        $checkIn = now()->format('H:i');

        $record = $existing ?? new Attendance;

        $record->fill([
            'gym_id' => $client->gym_id,
            'client_id' => $client->id,
            'attendance_date' => $today,
            'check_in' => $checkIn,
            'status' => 'present',
            'marked_by' => auth()->id(),
            'source' => $source,
            'notes' => $notes,
        ])->save();

        audit_log('attendance.checkin', 'attendance', $record->id, "Check-in for {$client->display_name}");

        return ['success' => true, 'record' => $record];
    }

    public function checkOutClient(Client $client, ?string $notes = null): array
    {
        $today = now()->toDateString();

        $record = Attendance::where('client_id', $client->id)
            ->whereDate('attendance_date', $today)
            ->whereNotNull('check_in')
            ->first();

        if (! $record) {
            return ['success' => false, 'message' => 'No check-in found for today.', 'duplicate' => false];
        }

        if ($record->check_out) {
            return ['success' => false, 'message' => "{$client->display_name} has already checked out today at {$record->check_out}.", 'duplicate' => true];
        }

        $checkOut = now()->format('H:i');
        $record->update([
            'check_out' => $checkOut,
            'duration_minutes' => $this->diffMinutes($record->check_in, $checkOut),
            'notes' => $notes ?? $record->notes,
        ]);

        audit_log('attendance.checkout', 'attendance', $record->id, "Check-out for {$client->display_name}");

        return ['success' => true, 'record' => $record];
    }

    public function staffCheckIn(StaffProfile $staff): array
    {
        $today = now()->toDateString();

        $existing = StaffAttendance::where('staff_id', $staff->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existing && $existing->check_in) {
            return ['success' => false, 'message' => 'Already checked in today.', 'duplicate' => true];
        }

        $checkIn = now()->format('H:i');

        $record = $existing ?? new StaffAttendance;

        $record->fill([
            'gym_id' => $staff->gym_id,
            'staff_id' => $staff->id,
            'attendance_date' => $today,
            'check_in' => $checkIn,
            'status' => 'present',
            'marked_by' => auth()->id(),
        ])->save();

        audit_log('staff_attendance.checkin', 'staff_attendance', $record->id, "Staff check-in for {$staff->display_name}");

        return ['success' => true, 'record' => $record];
    }

    public function staffCheckOut(StaffProfile $staff): array
    {
        $today = now()->toDateString();

        $record = StaffAttendance::where('staff_id', $staff->id)
            ->whereDate('attendance_date', $today)
            ->whereNotNull('check_in')
            ->first();

        if (! $record) {
            return ['success' => false, 'message' => 'No check-in found for today.', 'duplicate' => false];
        }

        if ($record->check_out) {
            return ['success' => false, 'message' => 'Already checked out today.', 'duplicate' => true];
        }

        $checkOut = now()->format('H:i');
        $record->update([
            'check_out' => $checkOut,
            'working_minutes' => $this->diffMinutes($record->check_in, $checkOut),
        ]);

        audit_log('staff_attendance.checkout', 'staff_attendance', $record->id, "Staff check-out for {$staff->display_name}");

        return ['success' => true, 'record' => $record];
    }

    private function diffMinutes(string $start, string $end): int
    {
        $startTime = strtotime($start);
        $endTime = strtotime($end);

        return (int) round(($endTime - $startTime) / 60);
    }
}
