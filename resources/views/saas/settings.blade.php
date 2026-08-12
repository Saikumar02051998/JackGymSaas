<x-layouts.app
    title="SaaS Settings"
    description="Global settings for the SaaS platform."
    :breadcrumbs="[['label' => 'SaaS', 'url' => route('saas.dashboard')], ['label' => 'Settings']]">

    <div class="mx-auto max-w-2xl space-y-6">
        <x-card title="SaaS Settings">
            <form method="POST" action="{{ route('saas.settings.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-input label="Trial days" name="trial_days" type="number" min="1" max="365" value="{{ old('trial_days', $settings['trial_days']) }}" help="Free trial period given to newly registered gyms." required />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Currency code" name="currency" value="{{ old('currency', $settings['currency']) }}" help="e.g. INR" required />
                    <x-input label="Currency symbol" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" help="e.g. ₹" required />
                </div>

                <div class="flex justify-end border-t border-ink-100 pt-4 dark:border-ink-800">
                    <x-button type="submit">
                        <x-icon name="save" class="size-4" />
                        Save Settings
                    </x-button>
                </div>
            </form>
        </x-card>

        <x-card title="Payment Gateway">
            @if ($settings['razorpay_configured'])
                <x-badge color="green">Razorpay configured</x-badge>
                <p class="mt-2 text-sm text-ink-500 dark:text-ink-400">Online subscription payments are available for gym owners.</p>
            @else
                <x-badge color="amber">Razorpay not configured</x-badge>
                <p class="mt-2 text-sm text-ink-500 dark:text-ink-400">Gym owners can only be billed manually. Add RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET to .env to enable online payments.</p>
            @endif
        </x-card>
    </div>
</x-layouts.app>
