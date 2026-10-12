<?php

namespace App\Filament\Resources\ElectiveSubjectGroups\Pages;

use App\Filament\Resources\ElectiveSubjectGroups\ElectiveSubjectGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListElectiveSubjectGroups extends ListRecords
{
    protected static string $resource = ElectiveSubjectGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
