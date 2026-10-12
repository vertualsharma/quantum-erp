<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $guarded = [];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'school_class_id');
    }

    public function examClassSetups(): HasMany
    {
        return $this->hasMany(ExamClassSetup::class, 'school_class_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_school_class');
    }

    public function rollNoSetups(): HasMany
    {
        return $this->hasMany(RollNoSetup::class);
    }
}
