<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSubject extends Model
{
    protected $guarded = [];

    protected $casts = [
        'th_credit_hour' => 'decimal:2',
        'pr_credit_hour' => 'decimal:2',
        'total_credit_hour' => 'decimal:2',
        'has_practical' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Coerce null credit hours to 0 to prevent integrity constraint violations
            if ($model->th_credit_hour === null) {
                $model->th_credit_hour = 0.00;
            }
            if ($model->pr_credit_hour === null) {
                $model->pr_credit_hour = 0.00;
            }
        });
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

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function subjectGroup(): BelongsTo
    {
        return $this->belongsTo(SubjectGroup::class);
    }

    public function electiveGroup(): BelongsTo
    {
        return $this->belongsTo(ElectiveSubjectGroup::class, 'elective_group_id');
    }
}