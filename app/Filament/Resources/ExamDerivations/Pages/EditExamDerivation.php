<?php

namespace App\Filament\Resources\ExamDerivations\Pages;

use App\Filament\Resources\ExamDerivations\ExamDerivationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExamDerivation extends EditRecord
{
    protected static string $resource = ExamDerivationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}