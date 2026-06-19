<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    // 🌟 Add this right here to prevent mass assignment crashes!
    protected $guarded = [];

    // A level has many programs
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    // A level has many school classes
    public function schoolClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }
}