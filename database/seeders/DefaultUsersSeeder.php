<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        $gym = Gym::where('slug', 'jack-gym')->first() ?? Gym::first();

        $password = env('DEMO_OWNER_PASSWORD', 'password');

        $ownerRole = Role::where('slug', 'owner')->first();

        $owner = User::firstOrCreate(
            ['email' => env('DEMO_OWNER_EMAIL', 'owner@jackgym.test')],
            [
                'gym_id' => $gym?->id,
                'name' => 'Jack Owner',
                'phone' => '+91 90000 00001',
                'password' => $password,
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );

        if (! $owner->roles()->where('slug', 'owner')->exists() && $ownerRole) {
            $owner->roles()->attach($ownerRole->id);
        }

        $this->seedStaff($gym, 'coach', 'Coach', 'Coach Kumar', '+91 90000 00002', $password);
        $this->seedStaff($gym, 'receptionist', 'Receptionist', 'Reception Priya', '+91 90000 00003', $password);
        $this->seedStaff($gym, 'accountant', 'Accountant', 'Accounts Ravi', '+91 90000 00004', $password);
        $this->seedStaff($gym, 'manager', 'Manager', 'Manager Amit', '+91 90000 00005', $password);
        $this->seedStaff($gym, 'nutritionist', 'Nutritionist', 'Nutrition Neha', '+91 90000 00006', $password);
    }

    private function seedStaff(Gym $gym, string $roleSlug, string $designation, string $name, string $phone, string $password): void
    {
        $role = Role::where('slug', $roleSlug)->first();

        if (! $role) {
            return;
        }

        $slug = Str::slug($name);
        $email = $slug . '@jackgym.test';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'gym_id' => $gym?->id,
                'name' => $name,
                'phone' => $phone,
                'password' => $password,
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );

        if (! $user->roles()->where('slug', $roleSlug)->exists()) {
            $user->roles()->attach($role->id);
        }

        StaffProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'gym_id' => $gym?->id,
                'employee_id' => 'EMP-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                'designation' => $designation,
                'joining_date' => now()->subMonths(6)->toDateString(),
                'salary_type' => 'fixed',
                'basic_salary' => 25000,
                'allowances' => 5000,
                'status' => 'active',
            ]
        );
    }
}
