<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DietPlan extends Model
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

    public function nutritionist()
    {
        return $this->belongsTo(StaffProfile::class, 'nutritionist_id');
    }

    public function meals()
    {
        return $this->hasMany(DietPlanMeal::class)->orderBy('sort_order');
    }
}
