<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SitePageItem;

class SitePage extends Model
{
    protected $fillable = ['site_id', 'site_slug', 'pages'];

    protected $casts = [
        'pages' => 'array',
    ];
}
