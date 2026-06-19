<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    // 🌟 Unlocks the model so Filament can save all form fields without crashing
    protected $guarded = [];

    /**
     * Get the academic level this program belongs to.
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Get the faculty this program belongs to.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the sections assigned to this program.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}