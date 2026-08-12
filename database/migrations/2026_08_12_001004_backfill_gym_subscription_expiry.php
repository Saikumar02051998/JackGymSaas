<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $trialDays = (int) DB::table('settings')
            ->whereNull('gym_id')
            ->where('key', 'saas_trial_days')
            ->value('value') ?: 14;

        $trialPlanId = DB::table('subscription_plans')->where('slug', 'trial')->value('id');

        foreach (DB::table('gyms')->whereIn('subscription_status', ['trial', 'active'])->get() as $gym) {
            $gymId = $gym->id;
            $update = [];

            if (! $gym->subscription_expires_at) {
                $update['subscription_expires_at'] = now()->copy()->addDays($trialDays);
            }

            if ((string) $gym->subscription_status === 'trial' && ! $gym->subscription_plan_id && $trialPlanId) {
                $update['subscription_plan_id'] = $trialPlanId;
            }

            if ($update) {
                DB::table('gyms')->where('id', $gymId)->update($update);
            }
        }
    }

    public function down(): void
    {
        //
    }
};