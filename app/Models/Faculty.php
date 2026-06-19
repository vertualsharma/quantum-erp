<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    // 🌟 Unlocks the fields so Filament can write new faculties to the database
    protected $guarded = [];

    // A faculty has many academic programs
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}