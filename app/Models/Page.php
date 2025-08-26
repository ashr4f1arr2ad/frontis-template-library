<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'slug', 'description', 'content', 'is_premium', 'image', 'tags', 'page_json'];
    protected $casts = [
        'tags' => 'array',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_page');
    }
}
