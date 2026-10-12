<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicFiscalYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'english_name',
        'is_current',
        'status',
        'created_by',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];
}