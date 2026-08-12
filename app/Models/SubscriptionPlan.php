<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
        ];
    }

    public function gyms()
    {
        return $this->hasMany(Gym::class, 'subscription_plan_id');
    }

    public function saasPayments(): HasMany
    {
        return $this->hasMany(SaasPayment::class);
    }

    public function priceFor(string $cycle): float
    {
        return $cycle === 'yearly' ? (float) $this->price_yearly : (float) $this->price_monthly;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTrial(): bool
    {
        return $this->slug === 'trial'
            || ((float) $this->price_monthly === 0.0 && (float) $this->price_yearly === 0.0);
    }
}
