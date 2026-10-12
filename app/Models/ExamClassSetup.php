<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamClassSetup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'mark_entry_locked' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicFiscalYear::class, 'academic_year_id');
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function subjectMarkSetups(): HasMany
    {
        return $this->hasMany(ExamSubjectMarkSetup::class);
    }
}