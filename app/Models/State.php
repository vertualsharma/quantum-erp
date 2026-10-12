<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    protected $guarded = [];

    // One state has many districts (e.g., Koshi Province has Jhapa, Morang, etc.)
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}