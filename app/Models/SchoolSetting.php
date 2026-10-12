<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'established_date' => 'date',
    ];
}