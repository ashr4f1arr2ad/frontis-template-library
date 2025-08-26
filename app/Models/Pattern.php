<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pattern extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'is_premium', 'image', 'pattern_json', 'tags'];

    protected $casts = [
        'is_premium' => 'boolean',
        'tags' => 'array',
    ];
}
