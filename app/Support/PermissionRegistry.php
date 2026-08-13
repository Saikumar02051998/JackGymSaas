<?php

namespace App\Support;

class PermissionRegistry
{
    public const PERMISSIONS = [
        'dashboard' => [
            'dashboard.view' => 'View dashboard',
            'dashboard.revenue.view' => 'View Revenue vs Expenses & Recent Revenue',
        ],
        'clients' => [
            'clients.view' => 'View clients',
            'clients.create' => 'Create clients',
            'clients.edit' => 'Edit clients',
            'clients.delete' => 'Delete clients',
            'clients.health' => 'Manage health profiles',
        ],
        'memberships' => [
            'memberships.view' => 'View memberships',
            'memberships.create' => 'Create memberships',
            'memberships.renew' => 'Renew memberships',
            'memberships.manage' => 'Manage membership plans',
        ],
        'attendance' => [
            'attendance.view' => 'View attendance',
            'attendance.manage' => 'Manage attendance',
            'attendance.staff' => 'Manage staff attendance',
        ],
        'staff' => [
            'staff.view' => 'View staff',
            'staff.create' => 'Create staff',
            'staff.edit' => 'Edit staff',
            'staff.delete' => 'Delete staff',
            'staff.roles' => 'Manage roles and permissions',
        ],
        'payments' => [
            'payments.view' => 'View payments',
            'payments.create' => 'Create payments',
            'payments.refund' => 'Process refunds',
            'payments.verify' => 'Verify payments',
        ],
        'invoices' => [
            'invoices.view' => 'View invoices',
            'invoices.create' => 'Create invoices',
            'invoices.manage' => 'Manage invoices',
        ],
        'expenses' => [
            'expenses.view' => 'View expenses',
            'expenses.create' => 'Create expenses',
            'expenses.manage' => 'Manage expenses',
        ],
        'salaries' => [
            'salary.view' => 'View salaries',
            'salary.manage' => 'Manage salaries',
        ],
        'leads' => [
            'leads.view' => 'View leads',
            'leads.create' => 'Create leads',
            'leads.edit' => 'Edit leads',
            'leads.manage' => 'Manage leads',
        ],
        'followups' => [
            'followups.view' => 'View follow-ups',
            'followups.create' => 'Create follow-ups',
            'followups.manage' => 'Manage follow-ups',
        ],
        'fitness' => [
            'workouts.manage' => 'Manage workout plans',
            'diets.manage' => 'Manage diet plans',
            'progress.view' => 'View progress',
            'progress.manage' => 'Manage progress records',
        ],
        'schedule' => [
            'appointments.view' => 'View appointments',
            'appointments.manage' => 'Manage appointments',
            'pt.view' => 'View PT sessions',
            'pt.manage' => 'Manage PT sessions',
        ],
        'reports' => [
            'reports.view' => 'View reports',
        ],
        'equipment' => [
            'equipment.view' => 'View equipment',
            'equipment.manage' => 'Manage equipment',
        ],
        'inventory' => [
            'inventory.view' => 'View inventory',
            'inventory.manage' => 'Manage inventory',
        ],
        'support' => [
            'support.view' => 'View support tickets',
            'support.reply' => 'Reply to support tickets',
        ],
        'announcements' => [
            'announcements.view' => 'View announcements',
            'announcements.manage' => 'Manage announcements',
        ],
        'settings' => [
            'settings.view' => 'View settings',
            'settings.manage' => 'Manage settings',
        ],
        'audit' => [
            'audit.view' => 'View audit logs',
        ],
        'subscription' => [
            'subscription.view' => 'View subscription & billing',
        ],
        'documents' => [
            'documents.view' => 'View documents',
            'documents.manage' => 'Manage documents',
        ],
        'saas' => [
            'saas.dashboard.view' => 'View SaaS dashboard',
            'saas.gyms.view' => 'View all gyms',
            'saas.gyms.manage' => 'Manage gyms',
            'saas.plans.view' => 'View subscription plans',
            'saas.plans.manage' => 'Manage subscription plans',
            'saas.payments.view' => 'View SaaS payments',
            'saas.payments.create' => 'Record SaaS payments',
            'saas.payments.refund' => 'Refund SaaS payments',
            'saas.settings.view' => 'View SaaS settings',
            'saas.settings.manage' => 'Manage SaaS settings',
        ],
    ];

    public static function all(): array
    {
        $permissions = [];

        foreach (self::PERMISSIONS as $module => $perms) {
            foreach ($perms as $slug => $name) {
                $permissions[] = [
                    'slug' => $slug,
                    'name' => $name,
                    'module' => $module,
                ];
            }
        }

        return $permissions;
    }
}
