<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'title',
        'type',
        'sites',
        'total_used_sites',
        'total_users',
        'license_key',
        'expire_date',
        'status',
        'subscription_id'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'subscription_user', 'subscription_id', 'user_id')
            ->withTimestamps();
    }
}
