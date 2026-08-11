<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            GymSeeder::class,
            DefaultUsersSeeder::class,
            MembershipPlanSeeder::class,
            ExpenseCategorySeeder::class,
        ]);
    }
}
