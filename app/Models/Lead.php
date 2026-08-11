<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function interestedPlan()
    {
        return $this->belongsTo(MembershipPlan::class, 'interested_plan_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function followups()
    {
        return $this->hasMany(LeadFollowup::class);
    }

    public function trial()
    {
        return $this->hasOne(Trial::class);
    }
}
