<?php

namespace Database\Seeders;

use App\Models\Gym;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GymSeeder extends Seeder
{
    public function run(): void
    {
        $gym = Gym::firstOrCreate(
            ['slug' => 'jack-gym'],
            [
                'name' => 'Jack Gym',
                'logo' => null,
                'address' => '123 Fitness Street, Main Road, Mumbai, Maharashtra 400001',
                'phone' => '+91 90000 00000',
                'email' => 'info@jackgym.test',
                'website' => 'https://jackgym.test',
                'currency' => 'INR',
                'currency_symbol' => '₹',
                'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),
                'tax_percent' => (float) env('GST_TAX_PERCENT', 0),
                'invoice_prefix' => 'INV',
                'status' => 'active',
                'subscription_status' => 'trial',
                'subscription_expires_at' => now()->addDays((int) \App\Services\GymService::TRIAL_DAYS),
            ]
        );

        $gym->settings()->updateOrCreate(['key' => 'project_mode'], ['value' => config('app.project_mode', 'handover')]);
        $gym->settings()->updateOrCreate(['key' => 'membership_reminder_days'], ['value' => json_encode([30, 15, 7, 3, 1])]);
        $gym->settings()->updateOrCreate(['key' => 'currency'], ['value' => $gym->currency]);
        $gym->settings()->updateOrCreate(['key' => 'currency_symbol'], ['value' => $gym->currency_symbol]);
        $gym->settings()->updateOrCreate(['key' => 'invoice_prefix'], ['value' => $gym->invoice_prefix]);
        $gym->settings()->updateOrCreate(['key' => 'tax_percent'], ['value' => (string) $gym->tax_percent]);
        $gym->settings()->updateOrCreate(['key' => 'timezone'], ['value' => $gym->timezone]);
    }
}
