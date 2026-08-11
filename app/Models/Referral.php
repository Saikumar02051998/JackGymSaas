<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $guarded = [];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function referrer()
    {
        return $this->belongsTo(Client::class, 'referrer_client_id');
    }

    public function referredClient()
    {
        return $this->belongsTo(Client::class, 'referred_client_id');
    }
}
