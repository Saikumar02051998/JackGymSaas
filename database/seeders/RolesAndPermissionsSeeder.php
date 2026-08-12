<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionRegistry;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissionMap = [];

        foreach (PermissionRegistry::all() as $perm) {
            $permissionMap[$perm['slug']] = Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                ['name' => $perm['name'], 'module' => $perm['module']]
            );
        }

        $roles = [
            'owner' => [
                'name' => 'Owner',
                'permissions' => array_map(fn ($perm) => $perm['slug'], PermissionRegistry::all()),
            ],
            'manager' => [
                'name' => 'Manager',
                'permissions' => array_map(fn ($perm) => $perm['slug'], PermissionRegistry::all()),
            ],
            'coach' => [
                'name' => 'Coach',
                'permissions' => [
                    'dashboard.view',
                    'clients.view',
                    'clients.health',
                    'attendance.view',
                    'attendance.manage',
                    'followups.view',
                    'followups.create',
                    'followups.manage',
                    'workouts.manage',
                    'diets.manage',
                    'progress.view',
                    'progress.manage',
                    'appointments.view',
                    'appointments.manage',
                    'pt.view',
                    'pt.manage',
                    'salary.view',
                    'announcements.view',
                    'documents.view',
                ],
            ],
            'receptionist' => [
                'name' => 'Receptionist',
                'permissions' => [
                    'dashboard.view',
                    'clients.view',
                    'clients.create',
                    'clients.edit',
                    'memberships.view',
                    'attendance.view',
                    'attendance.manage',
                    'payments.view',
                    'payments.create',
                    'invoices.view',
                    'leads.view',
                    'leads.create',
                    'leads.edit',
                    'followups.view',
                    'followups.create',
                    'appointments.view',
                    'appointments.manage',
                    'announcements.view',
                ],
            ],
            'accountant' => [
                'name' => 'Accountant',
                'permissions' => [
                    'dashboard.view',
                    'clients.view',
                    'memberships.view',
                    'payments.view',
                    'payments.create',
                    'invoices.view',
                    'invoices.create',
                    'invoices.manage',
                    'expenses.view',
                    'expenses.create',
                    'expenses.manage',
                    'salary.view',
                    'salary.manage',
                    'reports.view',
                ],
            ],
            'sales' => [
                'name' => 'Sales Staff',
                'permissions' => [
                    'dashboard.view',
                    'clients.view',
                    'clients.create',
                    'leads.view',
                    'leads.create',
                    'leads.edit',
                    'leads.manage',
                    'followups.view',
                    'followups.create',
                    'followups.manage',
                    'announcements.view',
                ],
            ],
            'nutritionist' => [
                'name' => 'Nutritionist',
                'permissions' => [
                    'dashboard.view',
                    'clients.view',
                    'clients.health',
                    'diets.manage',
                    'progress.view',
                    'progress.manage',
                    'followups.view',
                    'followups.create',
                    'appointments.view',
                    'appointments.manage',
                    'announcements.view',
                ],
            ],
            'support_staff' => [
                'name' => 'Support Staff',
                'permissions' => [
                    'dashboard.view',
                    'clients.view',
                    'support.view',
                    'support.reply',
                    'announcements.view',
                ],
            ],
            'client' => [
                'name' => 'Client',
                'permissions' => [],
            ],
            'saas_owner' => [
                'name' => 'SaaS Owner',
                'permissions' => [
                    'saas.dashboard.view',
                    'saas.gyms.view',
                    'saas.gyms.manage',
                    'saas.plans.view',
                    'saas.plans.manage',
                    'saas.payments.view',
                    'saas.payments.create',
                    'saas.payments.refund',
                    'saas.settings.view',
                    'saas.settings.manage',
                ],
            ],
        ];

        foreach ($roles as $slug => $config) {
            $role = Role::firstOrCreate(
                ['slug' => $slug, 'gym_id' => null],
                ['name' => $config['name'], 'is_system' => true, 'description' => ucfirst($config['name']) . ' role']
            );

            $permissionIds = array_map(fn ($permSlug) => $permissionMap[$permSlug]->id, $config['permissions']);

            $role->permissions()->sync($permissionIds);
        }
    }
}
