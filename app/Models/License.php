<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $fillable = ['license_key', 'expiration_date'];

    /**
     * Get the users associated with this license.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'license_id');
    }
}
