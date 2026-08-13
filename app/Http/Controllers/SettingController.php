<?php

namespace App\Http\Controllers;

use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SettingController extends Controller
{
    public function index()
    {
        $gym = current_gym();
        $gym->load('settings');

        $razorpayConfigured = RazorpayService::forGym()->isConfigured();

        $salaryRules = [
            'calendar_days' => (int) $gym->setting('salary_calendar_days', 30),
            'paid_leave_days' => (int) $gym->setting('salary_paid_leave_days', 2),
            'paid_half_days' => (int) $gym->setting('salary_paid_half_days', 4),
        ];

        return view('settings.index', compact('gym', 'razorpayConfigured', 'salaryRules'));
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('settings.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'currency' => ['nullable', 'string', 'max:10'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'membership_reminder_days' => ['nullable', 'array'],
        ]);

        $gym = current_gym();

        $logo = $gym->logo;
        if (isset($data['logo']) && $data['logo']) {
            $logo = $data['logo']->store('gym', 'public');
        }

        $gym->update(array_merge(Arr::except($data, ['membership_reminder_days']), [
            'logo' => $logo,
            'tax_percent' => $data['tax_percent'] ?? $gym->tax_percent,
            'invoice_prefix' => $data['invoice_prefix'] ?? $gym->invoice_prefix,
        ]));

        $gym->settings()->updateOrCreate(['key' => 'tax_percent'], ['value' => (string) ($data['tax_percent'] ?? $gym->tax_percent)]);
        $gym->settings()->updateOrCreate(['key' => 'currency'], ['value' => $data['currency'] ?? $gym->currency]);
        $gym->settings()->updateOrCreate(['key' => 'currency_symbol'], ['value' => $data['currency_symbol'] ?? $gym->currency_symbol]);
        $gym->settings()->updateOrCreate(['key' => 'timezone'], ['value' => $data['timezone'] ?? $gym->timezone]);
        $gym->settings()->updateOrCreate(['key' => 'invoice_prefix'], ['value' => $data['invoice_prefix'] ?? $gym->invoice_prefix]);

        if (isset($data['membership_reminder_days'])) {
            $gym->settings()->updateOrCreate(
                ['key' => 'membership_reminder_days'],
                ['value' => json_encode(array_map('intval', $data['membership_reminder_days']))]
            );
        }

        audit_log('settings.updated', 'settings', $gym->id, 'Gym settings updated');

        return back()->with('success', 'Settings saved.');
    }

    public function updatePaymentGateway(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('settings.manage'), 403);

        $data = $request->validate([
            'key_id' => ['nullable', 'string', 'max:100'],
            'key_secret' => ['nullable', 'string', 'max:100'],
            'webhook_secret' => ['nullable', 'string', 'max:100'],
        ]);

        $gym = current_gym();

        foreach ($data as $key => $value) {
            if ($value !== null) {
                $gym->settings()->updateOrCreate(['key' => 'razorpay_' . $key], ['value' => $value]);
            }
        }

        audit_log('settings.payment_gateway', 'settings', $gym->id, 'Payment gateway settings updated');

        return back()->with('success', 'Payment gateway settings saved.');
    }

    public function updateSalaryRules(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('settings.manage'), 403);

        $data = $request->validate([
            'calendar_days' => ['required', 'in:28,30'],
            'paid_leave_days' => ['required', 'integer', 'min:0', 'max:31'],
            'paid_half_days' => ['required', 'integer', 'min:0', 'max:62'],
        ]);

        $gym = current_gym();

        $gym->setSetting('salary_calendar_days', (string) $data['calendar_days']);
        $gym->setSetting('salary_paid_leave_days', (string) $data['paid_leave_days']);
        $gym->setSetting('salary_paid_half_days', (string) $data['paid_half_days']);

        audit_log('salary.rules_updated', 'settings', $gym->id, 'Salary rules updated');

        return back()->with('success', 'Salary rules saved.');
    }
}
