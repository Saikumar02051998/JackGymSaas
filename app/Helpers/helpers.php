<?php

use App\Models\AuditLog;
use App\Models\Gym;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

if (! function_exists('current_gym')) {
    function current_gym(): ?Gym
    {
        $user = Auth::user();
        if ($user) {
            if ($user->gym_id) {
                return $user->gym;
            }

            $gymId = (int) config('app.default_gym_id', 1);

            return Gym::find($gymId);
        }

        $gymId = (int) config('app.default_gym_id', 1);

        return Gym::find($gymId);
    }
}

if (! function_exists('gym_setting')) {
    function gym_setting(string $key, mixed $default = null): mixed
    {
        $gym = current_gym();

        if ($gym) {
            $setting = $gym->settings()->where('key', $key)->first();

            if ($setting && $setting->value !== null) {
                return $setting->value;
            }

            $fallbacks = [
                'tax_percent' => $gym->tax_percent,
                'currency' => $gym->currency,
                'currency_symbol' => $gym->currency_symbol,
                'invoice_prefix' => $gym->invoice_prefix,
                'timezone' => $gym->timezone,
            ];

            if (array_key_exists($key, $fallbacks)) {
                return $fallbacks[$key];
            }
        }

        $envDefaults = [
            'tax_percent' => (float) env('GST_TAX_PERCENT', 0),
            'currency' => env('CURRENCY', 'INR'),
            'currency_symbol' => env('CURRENCY_SYMBOL', '₹'),
            'invoice_prefix' => 'INV',
            'timezone' => 'Asia/Kolkata',
        ];

        return $default ?? ($envDefaults[$key] ?? $default);
    }
}

if (! function_exists('money')) {
    function money(float|int|string $amount, ?string $currencySymbol = null): string
    {
        $symbol = $currencySymbol ?? (string) gym_setting('currency_symbol', '₹');

        return $symbol . number_format((float) $amount, 2);
    }
}

if (! function_exists('is_saas')) {
    function is_saas(): bool
    {
        return config('app.project_mode', 'handover') === 'saas';
    }
}

if (! function_exists('can_manage')) {
    function can_manage(string $permission): bool
    {
        return Auth::check() && Auth::user()->hasPermission($permission);
    }
}

if (! function_exists('audit_log')) {
    function audit_log(
        string $action,
        ?string $module = null,
        ?int $recordId = null,
        ?string $description = null,
        array $before = [],
        array $after = []
    ): void {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'module' => $module,
                'record_id' => $recordId,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit(request()->userAgent() ?? '', 500),
                'before_data' => $before ?: null,
                'after_data' => $after ?: null,
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}

if (! function_exists('next_sequence')) {
    function next_sequence(string $model, string $column, string $prefix): string
    {
        $query = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model), true)
            ? $model::withTrashed()
            : $model::query();

        $last = $query->orderByDesc('id')->value($column);
        $number = $last ? ((int) preg_replace('/\D/', '', $last) + 1) : 1;

        return $prefix . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('permission_list')) {
    function permission_list(): array
    {
        return Permission::orderBy('module')->orderBy('name')->get()
            ->groupBy('module')
            ->toArray();
    }
}
