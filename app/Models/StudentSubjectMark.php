<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSubjectMark extends Model
{
    protected $guarded = [];

    protected $casts = [
        'theory_absent' => 'boolean',
        'practical_absent' => 'boolean',
    ];

    public function examSubjectMarkSetup(): BelongsTo
    {
        return $this->belongsTo(ExamSubjectMarkSetup::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}