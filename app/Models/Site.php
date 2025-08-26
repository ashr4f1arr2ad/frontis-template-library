<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'content', 'is_premium', 'image', 'dependencies', 'tags'];

    protected $casts = [
        'dependencies' => 'array',
        'tags' => 'array',
    ];
}
