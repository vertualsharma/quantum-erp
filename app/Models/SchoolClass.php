<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    // 🌟 Unlocks the model fields so Filament forms can write to them safely
    protected $guarded = [];

    // The backward link we talked about
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    // The forward link to its sections
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'school_class_id');
    }
}