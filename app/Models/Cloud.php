<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cloud extends Model
{
    protected $fillable = ['user_id', 'item_id', 'item_type', 'data', 'website'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->morphTo();
    }
}
