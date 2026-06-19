<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    // 🌟 Unlocks the model fields so Filament forms can save them smoothly
    protected $guarded = [];

    /**
     * Get the school class this section belongs to.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * Get the academic program this section belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}