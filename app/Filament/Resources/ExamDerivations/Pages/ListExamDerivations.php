<?php

namespace App\Filament\Resources\ExamDerivations\Pages;

use App\Filament\Resources\ExamDerivations\ExamDerivationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExamDerivations extends ListRecords
{
    protected static string $resource = ExamDerivationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}