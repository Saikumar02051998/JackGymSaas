<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaasSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['name' => 'Trial', 'slug' => 'trial', 'description' => 'Free trial to explore the platform. Expires at the end of the trial period.', 'price_monthly' => 0, 'price_yearly' => 0],
            ['name' => 'Basic', 'slug' => 'basic', 'description' => 'For small gyms starting out.', 'price_monthly' => 499, 'price_yearly' => 4990],
            ['name' => 'Standard', 'slug' => 'standard', 'description' => 'Most popular for growing gyms.', 'price_monthly' => 999, 'price_yearly' => 9990],
            ['name' => 'Premium', 'slug' => 'premium', 'description' => 'Everything for established fitness businesses.', 'price_monthly' => 1499, 'price_yearly' => 14990],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan + ['status' => 'active']
            );
        }

        foreach ([
            'saas_trial_days' => '14',
            'saas_currency' => env('CURRENCY', 'INR'),
            'saas_currency_symbol' => env('CURRENCY_SYMBOL', '₹'),
        ] as $key => $value) {
            Setting::firstOrCreate(['gym_id' => null, 'key' => $key], ['value' => $value]);
        }

        $email = env('SAAS_ADMIN_EMAIL', 'saas@jackgym.test');

        if (! User::where('email', $email)->exists()) {
            $role = Role::where('slug', 'saas_owner')->first();

            $user = User::create([
                'name' => 'SaaS Admin',
                'email' => $email,
                'password' => env('SAAS_ADMIN_PASSWORD', 'password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            if ($role) {
                $user->roles()->attach($role->id);
            }
        }
    }
}
