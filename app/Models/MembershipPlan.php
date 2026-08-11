<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipPlan extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'final_amount' => 'decimal:2',
        ];
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function recalcFinalAmount(): void
    {
        $this->discount = min($this->discount, $this->price);
        $taxable = $this->price - $this->discount;
        $this->tax = round($taxable * (gym_setting('tax_percent', 0) / 100), 2);
        $this->final_amount = round($taxable + $this->tax, 2);
    }
}
