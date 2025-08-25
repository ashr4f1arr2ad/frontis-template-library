<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::creating(function ($tag) {
            $tag->slug = Str::slug($tag->name);
        });
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'site_tag');
    }

    public function patterns(): BelongsToMany
    {
        return $this->belongsToMany(Pattern::class);
    }
}
