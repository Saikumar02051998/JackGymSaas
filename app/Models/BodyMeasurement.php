<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BodyMeasurement extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['other' => 'array'];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
