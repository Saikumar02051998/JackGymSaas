<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
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
