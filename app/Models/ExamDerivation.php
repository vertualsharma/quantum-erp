<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamDerivation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_main_exam' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function targetExamClassSetup(): BelongsTo
    {
        return $this->belongsTo(ExamClassSetup::class, 'target_exam_class_setup_id');
    }

    public function sourceExamClassSetup(): BelongsTo
    {
        return $this->belongsTo(ExamClassSetup::class, 'source_exam_class_setup_id');
    }

    public function subjectDerivations(): HasMany
    {
        return $this->hasMany(SubjectDerivation::class);
    }
}