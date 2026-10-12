<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $guarded = [];

    // Links backward to its parent State
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    // Links forward to its child Municipalities
    public function municipalities(): HasMany
    {
        return $this->hasMany(Municipality::class);
    }
}