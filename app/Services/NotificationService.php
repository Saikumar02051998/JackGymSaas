<?php

namespace App\Services;

use App\Notifications\InAppNotification;
use App\Models\User;

class NotificationService
{
    public function toUser(User|int $user, string $title, string $message, string $type = 'info', ?string $url = null): void
    {
        $user = $user instanceof User ? $user : User::find($user);

        if ($user) {
            $user->notify(new InAppNotification($title, $message, $type, $url));
        }
    }

    public function membershipExpiringSoon(User $user, string $clientName, string $endDate): void
    {
        $this->toUser(
            $user,
            'Membership expiring',
            "The membership for {$clientName} expires on {$endDate}.",
            'warning',
            route('client.membership')
        );
    }

    public function paymentReceived(User $user, string $clientName, string $amount, string $paymentNo): void
    {
        $this->toUser(
            $user,
            'Payment received',
            "Payment {$paymentNo} of {$amount} received from {$clientName}.",
            'success',
            route('payments.show', ['payment' => $paymentNo])
        );
    }

    public function followupDue(User $user, string $clientName): void
    {
        $this->toUser(
            $user,
            'Follow-up due',
            "Follow-up scheduled for {$clientName}.",
            'info',
            route('followups.index')
        );
    }

    public function salaryProcessed(User $user, string $period): void
    {
        $this->toUser(
            $user,
            'Salary processed',
            "Your salary for {$period} has been processed. View and print your payslip.",
            'success',
            route('staff.my-payslips')
        );
    }

    public function appointmentReminder(User $user, string $clientName, string $date): void
    {
        $this->toUser(
            $user,
            'Appointment reminder',
            "Appointment with {$clientName} on {$date}.",
            'info',
            route('appointments.index')
        );
    }

    public function announcementPublished(User $user, string $title): void
    {
        $this->toUser(
            $user,
            'New announcement',
            $title,
            'info'
        );
    }
}
