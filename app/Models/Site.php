<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'content'];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'site_tag');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(Dependency::class);
    }
}
