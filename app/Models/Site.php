<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'content', 'is_premium', 'image', 'dependencies', 'tags', 'colors', 'typographies', 'custom_typographies', 'pages'];

    protected $casts = [
        'dependencies' => 'array',
        'tags' => 'array',
        'colors' => 'array',
        'typographies' => 'array',
        'custom_typographies' => 'array',
        'pages' => 'array',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_site');
    }
}
