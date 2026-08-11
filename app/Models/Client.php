<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
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

    public function trainer()
    {
        return $this->belongsTo(StaffProfile::class, 'assigned_trainer_id');
    }

    public function referrer()
    {
        return $this->belongsTo(Client::class, 'referred_by');
    }

    public function healthProfile()
    {
        return $this->hasOne(ClientHealthProfile::class);
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function activeMembership()
    {
        return $this->hasOne(Membership::class)->where('status', 'active')->latestOfMany('end_date');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function weightRecords()
    {
        return $this->hasMany(WeightRecord::class);
    }

    public function bodyMeasurements()
    {
        return $this->hasMany(BodyMeasurement::class);
    }

    public function fitnessGoals()
    {
        return $this->hasMany(FitnessGoal::class);
    }

    public function workoutPlans()
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function dietPlans()
    {
        return $this->hasMany(DietPlan::class);
    }

    public function followups()
    {
        return $this->hasMany(Followup::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function ptSessions()
    {
        return $this->hasMany(PersonalTrainingSession::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_client_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name ?? 'Client';
    }

    public function getInitialsAttribute(): string
    {
        $name = $this->display_name;

        return collect(explode(' ', $name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
    }

    public function getDaysSinceLastVisitAttribute(): ?int
    {
        $last = $this->attendance()->latest('attendance_date')->first();

        return $last ? (int) \Carbon\Carbon::parse($last->attendance_date)->diffInDays(now()) : null;
    }
}
