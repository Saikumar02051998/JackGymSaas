<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutPlan extends Model
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

    public function trainer()
    {
        return $this->belongsTo(StaffProfile::class, 'trainer_id');
    }

    public function exercises()
    {
        return $this->hasMany(WorkoutPlanExercise::class)->orderBy('sort_order');
    }
}
