<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSubjectResult extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_gpa_counted' => 'boolean',
    ];

    public function studentResult(): BelongsTo
    {
        return $this->belongsTo(StudentResult::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}