<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class Menu
{
    public static function items(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        return $user->isClient() ? self::clientMenu() : self::managementMenu();
    }

    protected static function managementMenu(): array
    {
        $menu = [
            'overview' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'permission' => 'dashboard.view'],
            ],
            'clients' => [
                ['route' => 'clients.index', 'label' => 'All Clients', 'icon' => 'users', 'permission' => 'clients.view'],
                ['url' => route('clients.index', ['status' => 'active']), 'label' => 'Active Members', 'icon' => 'check-badge', 'permission' => 'clients.view'],
                ['url' => route('clients.index', ['status' => 'expired']), 'label' => 'Expired Members', 'icon' => 'clock', 'permission' => 'clients.view'],
                ['route' => 'memberships.trials', 'label' => 'Trial Members', 'icon' => 'gift', 'permission' => 'clients.view'],
            ],
            'memberships' => [
                ['route' => 'memberships.index', 'label' => 'Memberships', 'icon' => 'card', 'permission' => 'memberships.view'],
                ['route' => 'memberships.plans.index', 'label' => 'Membership Plans', 'icon' => 'identification', 'permission' => 'memberships.manage'],
                ['route' => 'memberships.expiring', 'label' => 'Expiring Soon', 'icon' => 'clock', 'permission' => 'memberships.view'],
            ],
            'attendance' => [
                ['route' => 'attendance.index', 'label' => 'Client Attendance', 'icon' => 'calendar-check', 'permission' => 'attendance.view'],
                ['route' => 'attendance.staff', 'label' => 'Staff Attendance', 'icon' => 'users', 'permission' => 'attendance.staff'],
            ],
            'staff' => [
                ['route' => 'staff.index', 'label' => 'All Staff', 'icon' => 'briefcase', 'permission' => 'staff.view'],
                ['route' => 'staff.roles.index', 'label' => 'Roles & Permissions', 'icon' => 'shield', 'permission' => 'staff.roles'],
                ['route' => 'staff.salaries.index', 'label' => 'Salaries', 'icon' => 'banknotes', 'permission' => 'salary.view'],
                ['route' => 'staff.leaves.index', 'label' => 'Leaves', 'icon' => 'sun', 'permission' => 'attendance.staff'],
            ],
            'crm' => [
                ['route' => 'leads.index', 'label' => 'Leads', 'icon' => 'funnel', 'permission' => 'leads.view'],
                ['route' => 'followups.index', 'label' => 'Follow-ups', 'icon' => 'chat', 'permission' => 'followups.view'],
            ],
            'finance' => [
                ['route' => 'payments.index', 'label' => 'Payments', 'icon' => 'banknotes', 'permission' => 'payments.view'],
                ['route' => 'invoices.index', 'label' => 'Invoices', 'icon' => 'document', 'permission' => 'invoices.view'],
                ['route' => 'expenses.index', 'label' => 'Expenses', 'icon' => 'trending-down', 'permission' => 'expenses.view'],
                ['route' => 'reports.finance', 'label' => 'Financial Reports', 'icon' => 'chart', 'permission' => 'reports.view'],
            ],
            'fitness' => [
                ['route' => 'workouts.index', 'label' => 'Workout Plans', 'icon' => 'dumbbell', 'permission' => 'workouts.manage'],
                ['route' => 'diets.index', 'label' => 'Diet Plans', 'icon' => 'utensils', 'permission' => 'diets.manage'],
                ['route' => 'progress.index', 'label' => 'Progress Tracking', 'icon' => 'trending-up', 'permission' => 'progress.view'],
                ['route' => 'pt.index', 'label' => 'PT Sessions', 'icon' => 'bolt', 'permission' => 'pt.view'],
            ],
            'schedule' => [
                ['route' => 'appointments.index', 'label' => 'Appointments', 'icon' => 'calendar', 'permission' => 'appointments.view'],
            ],
            'operations' => [
                ['route' => 'equipment.index', 'label' => 'Equipment', 'icon' => 'wrench', 'permission' => 'equipment.view'],
                ['route' => 'inventory.index', 'label' => 'Inventory', 'icon' => 'box', 'permission' => 'inventory.view'],
                ['route' => 'announcements.index', 'label' => 'Announcements', 'icon' => 'megaphone', 'permission' => 'announcements.view'],
                ['route' => 'support.index', 'label' => 'Support Tickets', 'icon' => 'support', 'permission' => 'support.view'],
            ],
            'reports' => [
                ['route' => 'reports.clients', 'label' => 'Client Reports', 'icon' => 'chart', 'permission' => 'reports.view'],
                ['route' => 'reports.attendance', 'label' => 'Attendance Reports', 'icon' => 'calendar-check', 'permission' => 'reports.view'],
                ['route' => 'reports.leads', 'label' => 'Lead Reports', 'icon' => 'funnel', 'permission' => 'reports.view'],
                ['route' => 'reports.staff', 'label' => 'Staff Reports', 'icon' => 'users', 'permission' => 'reports.view'],
            ],
            'system' => [
                ['route' => 'settings.index', 'label' => 'Settings', 'icon' => 'settings', 'permission' => 'settings.view'],
                ['route' => 'audit.index', 'label' => 'Audit Logs', 'icon' => 'document-text', 'permission' => 'audit.view'],
            ],
        ];

        $filtered = [];

        foreach ($menu as $group => $items) {
            $visible = array_values(array_filter($items, function ($item) {
                return can_manage($item['permission']);
            }));

            if ($visible) {
                $filtered[$group] = $visible;
            }
        }

        return $filtered;
    }

    protected static function clientMenu(): array
    {
        return [
            'overview' => [
                ['route' => 'client.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
                ['route' => 'client.membership', 'label' => 'My Membership', 'icon' => 'card'],
            ],
            'fitness' => [
                ['route' => 'client.workouts', 'label' => 'Workout', 'icon' => 'dumbbell'],
                ['route' => 'client.diets', 'label' => 'Diet', 'icon' => 'utensils'],
                ['route' => 'client.progress', 'label' => 'Progress', 'icon' => 'trending-up'],
            ],
            'activity' => [
                ['route' => 'client.attendance', 'label' => 'My Attendance', 'icon' => 'calendar-check'],
                ['route' => 'client.appointments', 'label' => 'Appointments', 'icon' => 'calendar'],
            ],
            'billing' => [
                ['route' => 'client.payments', 'label' => 'My Payments', 'icon' => 'banknotes'],
                ['route' => 'client.invoices', 'label' => 'My Invoices', 'icon' => 'document'],
            ],
            'support' => [
                ['route' => 'client.support', 'label' => 'Support', 'icon' => 'support'],
            ],
        ];
    }

    public static function roleLabel(): string
    {
        $user = Auth::user();

        if (! $user) {
            return 'Guest';
        }

        $role = $user->roles()->orderBy('id')->first();

        return $role ? ucfirst($role->name) : 'User';
    }
}
