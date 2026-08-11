<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function isMaintenanceDue(): bool
    {
        return $this->next_maintenance
            && $this->next_maintenance <= now()->addDays(7)->toDateString();
    }
}
