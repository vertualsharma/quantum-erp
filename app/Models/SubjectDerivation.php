<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectDerivation extends Model
{
    protected $guarded = [];

    public function examDerivation(): BelongsTo
    {
        return $this->belongsTo(ExamDerivation::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}