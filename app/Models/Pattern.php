<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'slug', 
        'description', 
        'preview_url',
        'is_premium', 
        'image', 
        'pattern_json', 
        'tags', 
        'categories'
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'tags' => 'array',
    ];

    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_pattern');
    }
}
