<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Municipality extends Model
{
    protected $guarded = [];

    // Links backward to its parent District
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}