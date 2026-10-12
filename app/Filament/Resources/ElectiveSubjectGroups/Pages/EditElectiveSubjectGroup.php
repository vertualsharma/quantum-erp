<?php

namespace App\Filament\Resources\ElectiveSubjectGroups\Pages;

use App\Filament\Resources\ElectiveSubjectGroups\ElectiveSubjectGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditElectiveSubjectGroup extends EditRecord
{
    protected static string $resource = ElectiveSubjectGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
