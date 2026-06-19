<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width; // Use Width

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full; 
    }
}
