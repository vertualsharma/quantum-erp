<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RollNoSetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_fiscal_year_id',
        'school_class_id',
        'start_from',
    ];

    /**
     * Relationship to the Academic Fiscal Year model
     */
    public function academicFiscalYear()
    {
        return $this->belongsTo(AcademicFiscalYear::class);
    }

    /**
     * Relationship to the School Class model
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }
}