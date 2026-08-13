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
            'company_name' => saas_setting('company_name', config('app.saas_owner', 'TechNano')),
            'logo' => saas_setting('logo'),
            'razorpay_configured' => RazorpayService::forPlatform()->isConfigured(),
        ];

        return view('saas.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('saas.settings.manage'), 403);

        $data = $request->validate([
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'currency' => ['nullable', 'string', 'max:10'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if (isset($data['logo']) && $data['logo']) {
            $data['logo'] = $data['logo']->store('saas', 'public');
            Setting::updateOrCreate(['gym_id' => null, 'key' => 'saas_logo'], ['value' => $data['logo']]);
        }

        $mapping = [
            'trial_days' => 'saas_trial_days',
            'currency' => 'saas_currency',
            'currency_symbol' => 'saas_currency_symbol',
            'company_name' => 'saas_company_name',
        ];

        foreach ($mapping as $field => $key) {
            if (array_key_exists($field, $data) && $data[$field] !== null && $data[$field] !== '') {
                Setting::updateOrCreate(['gym_id' => null, 'key' => $key], ['value' => $data[$field]]);
            }
        }

        audit_log('saas.settings.updated', 'saas', null, 'SaaS settings updated');

        return back()->with('success', 'SaaS settings updated.');
    }
}
