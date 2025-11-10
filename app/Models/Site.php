<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'title', 
        'slug', 
        'description', 
        'content', 
        'is_premium', 
        'image', 
        'preview_url', 
        'read_more_url',
        'uploads_url', 
        'dependencies', 
        'tags', 
        'colors', 
        'color_gradients', 
        'typographies', 
        'custom_typographies', 
        'pages'
    ];

    protected $casts = [
        'dependencies' => 'array',
        'tags' => 'array',
        'colors' => 'array',
        'color_gradients' => 'array',
        'typographies' => 'array',
        'custom_typographies' => 'array',
        'pages' => 'array',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_site');
    }
}
