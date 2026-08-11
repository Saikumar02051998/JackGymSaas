<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        $gym = Gym::where('slug', 'jack-gym')->first() ?? Gym::first();

        $taxPercent = $gym ? (float) $gym->tax_percent : 0;

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

            MembershipPlan::firstOrCreate(
                ['gym_id' => $gym?->id, 'name' => $plan['name']],
                array_merge($plan, [
                    'discount' => 0,
                    'tax' => $tax,
                    'final_amount' => round($taxable + $tax, 2),
                    'status' => 'active',
                ])
            );
        }
    }
}
