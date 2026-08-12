<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gym extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tax_percent' => 'decimal:2',
            'subscription_expires_at' => 'datetime',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function staffProfiles()
    {
        return $this->hasMany(StaffProfile::class);
    }

    public function membershipPlans()
    {
        return $this->hasMany(MembershipPlan::class);
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function expenseCategories()
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function saasPayments()
    {
        return $this->hasMany(SaasPayment::class);
    }

    public function isSubscriptionActive(): bool
    {
        if (in_array($this->subscription_status, ['expired', 'suspended'], true)) {
            return false;
        }

        if (! $this->subscription_expires_at) {
            return in_array($this->subscription_status, ['active', 'trial'], true);
        }

        return in_array($this->subscription_status, ['active', 'trial'], true)
            && $this->subscription_expires_at->isFuture();
    }

    public function subscriptionStatusLabel(): string
    {
        return match ($this->subscription_status) {
            'trial' => 'Trial',
            'active' => 'Active',
            'expired' => 'Expired',
            'suspended' => 'Suspended',
            default => 'None',
        };
    }

    public function setting(string $key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public function setSetting(string $key, $value): void
    {
        $this->settings()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
