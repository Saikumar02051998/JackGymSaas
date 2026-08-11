<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DietPlanMeal extends Model
{
    protected $guarded = [];

    public function dietPlan()
    {
        return $this->belongsTo(DietPlan::class);
    }
}
