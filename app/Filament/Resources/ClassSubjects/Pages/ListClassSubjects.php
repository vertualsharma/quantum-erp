<?php

namespace App\Filament\Resources\ClassSubjects\Pages;

use App\Filament\Resources\ClassSubjects\ClassSubjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClassSubjects extends ListRecords
{
    protected static string $resource = ClassSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
