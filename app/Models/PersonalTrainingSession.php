<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalTrainingSession extends Model
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
}
