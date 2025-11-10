<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
    protected $fillable = [
        'title', 
        'slug', 
        'description', 
        'content', 
        'preview_url',
        'read_more_url',
        'is_premium', 
        'dependencies', 
        'image', 
        'tags', 
        'page_json'
    ];

    protected $casts = [
        'dependencies' => 'array',
        'tags' => 'array',
        'image' => 'array',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_page');
    }
}
