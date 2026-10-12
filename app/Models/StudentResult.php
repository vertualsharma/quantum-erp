<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentResult extends Model
{
    protected $guarded = [];

    protected $casts = [
        'calculated_at' => 'datetime',
    ];

    public function examClassSetup(): BelongsTo
    {
        return $this->belongsTo(ExamClassSetup::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subjectResults(): HasMany
    {
        return $this->hasMany(StudentSubjectResult::class);
    }
}