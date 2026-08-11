<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Followup extends Model
{
    protected $guarded = [];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function staff()
    {
        return $this->belongsTo(StaffProfile::class, 'staff_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('follow_up_date', now()->toDateString());
    }

    public function scopeOverdue($query)
    {
        return $query->whereDate('follow_up_date', '<', now()->toDateString())
            ->whereIn('status', ['pending', 'overdue']);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereDate('follow_up_date', '>', now()->toDateString())
            ->where('status', 'pending');
    }
}
