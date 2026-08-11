<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $guarded = [];

    public function staff()
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function items()
    {
        return $this->hasMany(SalaryItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
