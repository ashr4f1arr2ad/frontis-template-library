<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pattern extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'is_premium', 'image', 'patterns'];

    protected $casts = [
        'is_premium' => 'boolean',
        'patterns' => 'array', // Cast JSON to array
    ];

    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
