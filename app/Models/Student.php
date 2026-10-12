<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AcademicFiscalYear;
use App\Models\BatchYear; // ✅ Imported to fetch current academic session
use App\Models\SchoolClass;
use App\Models\Level;

class Student extends Model
{
    protected $guarded = [];

    protected $casts = [
        'document_path' => 'array',
    ];

    /**
     * Model Boot Mechanics
     */
    protected static function booted(): void
    {
        static::saving(function ($student) {
            if (! $student->level_id && $student->class_id) {
                $student->level_id = SchoolClass::find($student->class_id)?->level_id;
            }

            if ($student->batch_year_id) {
                $batch = BatchYear::find($student->batch_year_id);
                $student->batch_year = $batch?->nepali_year;
                $student->joined_batch_year = $batch?->nepali_year;
            }

            if ($student->academic_year_id) {
                $academic = AcademicFiscalYear::find($student->academic_year_id);
                $student->fiscal_year = $academic?->name;
            }
        });

        static::creating(function ($student) {
            if (! $student->level_id && $student->class_id) {
                $student->level_id = SchoolClass::find($student->class_id)?->level_id;
            }

            // 1. Fetch the active batch year (e.g., 2083 BS) from your system
            $currentBatch = BatchYear::where('is_current', true)->first();
            $yearPrefix = $currentBatch ? $currentBatch->nepali_year : '2083';

            // 2. Look up the last registered student for this specific batch year
            $latestStudent = static::where('regd_no', 'like', "REG-{$yearPrefix}-%")
                ->orderBy('id', 'desc')
                ->first();

            if ($latestStudent) {
                // Extract the trailing numeric sequence (e.g., "0001" out of "REG-2083-0001")
                $parts = explode('-', $latestStudent->regd_no);
                $latestSequence = (int) end($parts);
                $nextSequence = $latestSequence + 1;
            } else {
                // First student of the new academic session year
                $nextSequence = 1;
            }

            // 3. Assemble and pad the final registration string safely (padded to 4 digits: 0001)
            $student->regd_no = 'REG-' . $yearPrefix . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Geographic Relationships
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'perm_state_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'perm_district_id');
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'perm_municipality_id');
    }

    /**
     * Academic Relationships
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    /**
     * Academic Relationships (continued)
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }
}