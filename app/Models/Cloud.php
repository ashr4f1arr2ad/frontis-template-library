<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cloud extends Model
{
    protected $fillable = ['user_id', 'item_id', 'item_type', 'data', 'website', 'sub_user'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
