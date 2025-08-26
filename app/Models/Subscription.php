<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'title',
        'type',
        'total_sites',
        'total_users',
        'license_key',
        'license_expiry',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'subscription_user', 'subscription_id', 'user_id')
            ->withTimestamps();
    }
}
