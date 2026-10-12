<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'nepali_year',
        'english_year',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];
}