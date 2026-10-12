<?php

namespace App\Filament\Resources\SubjectGroups\Pages;

use App\Filament\Resources\SubjectGroups\SubjectGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubjectGroup extends EditRecord
{
    protected static string $resource = SubjectGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
