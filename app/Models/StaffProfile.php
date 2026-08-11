<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffProfile extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function assignedClients()
    {
        return $this->hasMany(Client::class, 'assigned_trainer_id');
    }

    public function attendance()
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function leaves()
    {
        return $this->hasMany(StaffLeave::class);
    }

    public function ptSessions()
    {
        return $this->hasMany(PersonalTrainingSession::class, 'trainer_id');
    }

    public function followups()
    {
        return $this->hasMany(Followup::class, 'staff_id');
    }

    public function role()
    {
        return $this->user?->roles()->first();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name ?? 'Staff';
    }

    public function getInitialsAttribute(): string
    {
        $name = $this->display_name;

        return collect(explode(' ', $name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
    }
}
