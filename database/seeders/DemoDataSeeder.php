<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Client;
use App\Models\ClientHealthProfile;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $gym = \App\Models\Gym::where('slug', 'jack-gym')->first() ?? \App\Models\Gym::first();
        $plans = MembershipPlan::where('gym_id', $gym?->id)->get();
        $clientRole = Role::where('slug', 'client')->first();

        if ($plans->isEmpty()) {
            return;
        }

        $sampleClients = [
            ['name' => 'Rohan Sharma', 'phone' => '+91 98111 00001', 'email' => 'rohan@example.com', 'gender' => 'male', 'weight' => 84, 'height' => 178, 'goal' => 'weight_loss'],
            ['name' => 'Ananya Gupta', 'phone' => '+91 98111 00002', 'email' => 'ananya@example.com', 'gender' => 'female', 'weight' => 62, 'height' => 165, 'goal' => 'muscle_gain'],
            ['name' => 'Vikram Singh', 'phone' => '+91 98111 00003', 'email' => 'vikram@example.com', 'gender' => 'male', 'weight' => 95, 'height' => 182, 'goal' => 'weight_loss'],
            ['name' => 'Priya Patel', 'phone' => '+91 98111 00004', 'email' => 'priya@example.com', 'gender' => 'female', 'weight' => 55, 'height' => 158, 'goal' => 'general_fitness'],
            ['name' => 'Arjun Mehta', 'phone' => '+91 98111 00005', 'email' => 'arjun@example.com', 'gender' => 'male', 'weight' => 78, 'height' => 175, 'goal' => 'strength'],
            ['name' => 'Sneha Reddy', 'phone' => '+91 98111 00006', 'email' => 'sneha@example.com', 'gender' => 'female', 'weight' => 68, 'height' => 170, 'goal' => 'body_recomposition'],
            ['name' => 'Karan Malhotra', 'phone' => '+91 98111 00007', 'email' => 'karan@example.com', 'gender' => 'male', 'weight' => 72, 'height' => 172, 'goal' => 'endurance'],
            ['name' => 'Neha Verma', 'phone' => '+91 98111 00008', 'email' => 'neha@example.com', 'gender' => 'female', 'weight' => 58, 'height' => 162, 'goal' => 'general_fitness'],
        ];

        foreach ($sampleClients as $index => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'gym_id' => $gym?->id,
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]
            );

            if ($clientRole && ! $user->roles()->where('slug', 'client')->exists()) {
                $user->roles()->attach($clientRole->id);
            }

            $client = Client::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'gym_id' => $gym?->id,
                    'member_id' => 'JG' . str_pad((string) (20 + $index), 5, '0', STR_PAD_LEFT),
                    'joining_date' => now()->subMonths(rand(1, 10))->toDateString(),
                    'gender' => $data['gender'],
                    'phone' => $data['phone'],
                    'status' => 'active',
                    'referral_code' => 'REF' . strtoupper(substr(md5($data['email']), 0, 6)),
                ]
            );

            $client->healthProfile()?->create([
                'height' => $data['height'],
                'weight' => $data['weight'],
                'bmi' => round($data['weight'] / (($data['height'] / 100) ** 2), 1),
                'goal_weight' => $data['weight'] - 8,
                'fitness_goal' => $data['goal'],
                'activity_level' => 'moderate',
            ]);

            $plan = $plans[rand(0, min(3, $plans->count() - 1))];

            $start = now()->subDays(rand(10, 40));
            $end = $start->copy()->addDays($plan->duration_days)->subDay();

            $membership = Membership::firstOrCreate(
                ['client_id' => $client->id, 'plan_id' => $plan->id],
                [
                    'gym_id' => $gym?->id,
                    'membership_no' => 'MS-' . str_pad((string) (100 + $index), 6, '0', STR_PAD_LEFT),
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'status' => $end->isPast() ? 'expired' : 'active',
                    'amount' => $plan->price,
                    'discount' => 0,
                    'tax' => $plan->tax,
                    'final_amount' => $plan->final_amount,
                    'payment_status' => 'paid',
                    'created_by' => 1,
                ]
            );

            if (! $membership->histories()->exists()) {
                $membership->histories()->create([
                    'client_id' => $client->id,
                    'plan_id' => $plan->id,
                    'action' => 'created',
                    'new_end_date' => $membership->end_date,
                    'amount' => $plan->final_amount,
                    'changed_by' => 1,
                    'notes' => 'Demo membership',
                ]);
            }

            for ($d = 0; $d < rand(10, 40); $d++) {
                $date = now()->subDays($d);

                if ($date->isSunday()) {
                    continue;
                }

                Attendance::firstOrCreate(
                    ['client_id' => $client->id, 'attendance_date' => $date->toDateString()],
                    [
                        'gym_id' => $gym?->id,
                        'check_in' => '06:' . str_pad((string) rand(0, 59), 2, '0', STR_PAD_LEFT),
                        'check_out' => '07:' . str_pad((string) rand(0, 59), 2, '0', STR_PAD_LEFT),
                        'duration_minutes' => rand(45, 120),
                        'status' => 'present',
                        'marked_by' => 1,
                        'source' => 'reception',
                    ]
                );
            }

            if (! $client->payments()->exists()) {
                Payment::create([
                    'gym_id' => $gym?->id,
                    'payment_no' => 'PAY-' . str_pad((string) (500 + $index), 6, '0', STR_PAD_LEFT),
                    'client_id' => $client->id,
                    'membership_id' => $membership->id,
                    'plan_id' => $plan->id,
                    'amount' => $plan->price,
                    'discount' => 0,
                    'tax' => $plan->tax,
                    'final_amount' => $plan->final_amount,
                    'payment_method' => collect(['cash', 'upi', 'card', 'razorpay'])->random(),
                    'status' => 'success',
                    'payment_date' => $start->toDateString(),
                    'created_by' => 1,
                ]);
            }
        }

        $leads = [
            ['name' => 'Aman Joshi', 'phone' => '+91 98222 00001', 'source' => 'website', 'status' => 'new'],
            ['name' => 'Ritika Sood', 'phone' => '+91 98222 00002', 'source' => 'instagram', 'status' => 'interested'],
            ['name' => 'Deepak Kumar', 'phone' => '+91 98222 00003', 'source' => 'walk_in', 'status' => 'contacted'],
            ['name' => 'Sara Khan', 'phone' => '+91 98222 00004', 'source' => 'referral', 'status' => 'converted'],
            ['name' => 'Mohit Bansal', 'phone' => '+91 98222 00005', 'source' => 'facebook', 'status' => 'trial'],
        ];

        foreach ($leads as $leadData) {
            Lead::firstOrCreate(
                ['gym_id' => $gym?->id, 'phone' => $leadData['phone']],
                array_merge($leadData, ['interested_plan_id' => $plans->random()->id, 'assigned_to' => 2])
            );
        }

        $categories = \App\Models\ExpenseCategory::where('gym_id', $gym?->id)->get();

        if ($categories->isNotEmpty()) {
            foreach (range(0, 8) as $i) {
                Expense::create([
                    'gym_id' => $gym?->id,
                    'category_id' => $categories->random()->id,
                    'amount' => rand(1000, 50000),
                    'expense_date' => now()->subDays($i * 3)->toDateString(),
                    'vendor' => collect(['Vendor A', 'BESCOM', 'Water Corp', 'Amazon', 'Local Shop'])->random(),
                    'description' => 'Demo expense entry',
                    'created_by' => 1,
                ]);
            }
        }
    }
}
