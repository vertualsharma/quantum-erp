<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentExamAttendance extends Model
{
    protected $guarded = [];

    public function examClassSetup(): BelongsTo
    {
        return $this->belongsTo(ExamClassSetup::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}