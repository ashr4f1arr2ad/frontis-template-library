<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dependency extends Model
{
    protected $fillable = ['site_id', 'name', 'version'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
