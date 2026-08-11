<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientHealthProfile extends Model
{
    protected $guarded = [];

    protected $table = 'client_health_profiles';

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getBmiAttribute($value): ?string
    {
        return $value ? number_format((float) $value, 1) : null;
    }
}
