<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\GymService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('saas.settings.view'), 403);

        $settings = [
            'trial_days' => saas_setting('trial_days', GymService::TRIAL_DAYS),
            'currency' => saas_setting('currency', env('CURRENCY', 'INR')),
            'currency_symbol' => saas_setting('currency_symbol', env('CURRENCY_SYMBOL', '₹')),
            'razorpay_configured' => RazorpayService::forPlatform()->isConfigured(),
        ];

        return view('saas.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('saas.settings.manage'), 403);

        $data = $request->validate([
            'trial_days' => ['required', 'integer', 'min:1', 'max:365'],
            'currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
        ]);

        Setting::updateOrCreate(['gym_id' => null, 'key' => 'saas_trial_days'], ['value' => $data['trial_days']]);
        Setting::updateOrCreate(['gym_id' => null, 'key' => 'saas_currency'], ['value' => $data['currency']]);
        Setting::updateOrCreate(['gym_id' => null, 'key' => 'saas_currency_symbol'], ['value' => $data['currency_symbol']]);

        audit_log('saas.settings.updated', 'saas', null, 'SaaS settings updated');

        return back()->with('success', 'SaaS settings updated.');
    }
}
