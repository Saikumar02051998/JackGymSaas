<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use App\Models\Gym;
use App\Models\MembershipPlan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GymService
{
    public function create(array $data): Gym
    {
        return DB::transaction(function () use ($data) {
            $gym = Gym::create([
                'name' => $data['gym_name'],
                'slug' => $this->uniqueSlug($data['gym_name']),
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'currency' => env('CURRENCY', 'INR'),
                'currency_symbol' => env('CURRENCY_SYMBOL', '₹'),
                'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),
                'tax_percent' => (float) env('GST_TAX_PERCENT', 0),
                'invoice_prefix' => 'INV',
                'status' => 'active',
            ]);

            $this->provisionDefaults($gym);

            $ownerRole = Role::where('slug', 'owner')->first();

            $user = User::create([
                'gym_id' => $gym->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            if ($ownerRole) {
                $user->roles()->attach($ownerRole->id);
            }

            return $gym;
        });
    }

    public function provisionDefaults(Gym $gym): void
    {
        $gym->settings()->updateOrCreate(['key' => 'project_mode'], ['value' => config('app.project_mode', 'handover')]);
        $gym->settings()->updateOrCreate(['key' => 'membership_reminder_days'], ['value' => json_encode([30, 15, 7, 3, 1])]);
        $gym->settings()->updateOrCreate(['key' => 'currency'], ['value' => $gym->currency]);
        $gym->settings()->updateOrCreate(['key' => 'currency_symbol'], ['value' => $gym->currency_symbol]);
        $gym->settings()->updateOrCreate(['key' => 'invoice_prefix'], ['value' => $gym->invoice_prefix]);
        $gym->settings()->updateOrCreate(['key' => 'tax_percent'], ['value' => (string) $gym->tax_percent]);
        $gym->settings()->updateOrCreate(['key' => 'timezone'], ['value' => $gym->timezone]);

        $this->seedPlans($gym);
        $this->seedExpenseCategories($gym);
    }

    private function seedPlans(Gym $gym): void
    {
        $taxPercent = (float) $gym->tax_percent;

        $plans = [
            ['name' => '1 Month', 'duration_days' => 30, 'price' => 1499, 'duration_label' => '1 Month', 'description' => 'Perfect for trying out the gym', 'features' => ['Full gym access', 'Locker facility', '1 free PT session']],
            ['name' => '3 Months', 'duration_days' => 90, 'price' => 3999, 'duration_label' => '3 Months', 'description' => 'Commit to your fitness journey', 'features' => ['Full gym access', 'Locker facility', '2 free PT sessions', 'Free diet plan']],
            ['name' => '6 Months', 'duration_days' => 180, 'price' => 6999, 'duration_label' => '6 Months', 'description' => 'Best value for regulars', 'features' => ['Full gym access', 'Locker facility', '4 free PT sessions', 'Free diet plan', 'Steam bath']],
            ['name' => '12 Months', 'duration_days' => 365, 'price' => 11999, 'duration_label' => '12 Months', 'description' => 'Our most popular annual plan', 'features' => ['Full gym access', 'Locker facility', '6 free PT sessions', 'Free diet plan', 'Steam bath', '2 months free on renewal']],
            ['name' => 'Personal Training - 12 Sessions', 'duration_days' => 45, 'price' => 6000, 'duration_label' => '45 Days', 'description' => '12 one-on-one personal training sessions', 'features' => ['12 PT sessions', 'Customized workout plan', 'Nutrition guidance']],
        ];

        foreach ($plans as $plan) {
            $taxable = $plan['price'];
            $tax = round($taxable * ($taxPercent / 100), 2);

            MembershipPlan::create(array_merge($plan, [
                'gym_id' => $gym->id,
                'discount' => 0,
                'tax' => $tax,
                'final_amount' => round($taxable + $tax, 2),
                'status' => 'active',
            ]));
        }
    }

    private function seedExpenseCategories(Gym $gym): void
    {
        $categories = ['Rent', 'Electricity', 'Water', 'Equipment', 'Maintenance', 'Salary', 'Marketing', 'Software', 'Cleaning', 'Supplies', 'Miscellaneous'];

        foreach ($categories as $category) {
            ExpenseCategory::create([
                'gym_id' => $gym->id,
                'name' => $category,
                'description' => $category . ' expenses',
            ]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'gym';
        $slug = $base;
        $i = 2;

        while (Gym::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
