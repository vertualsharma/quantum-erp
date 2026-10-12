<?php

namespace App\Filament\Resources\RollNoSetupResource\Pages;

use App\Filament\Resources\RollNoSetupResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListRollNoSetups extends ListRecords
{
    protected static string $resource = RollNoSetupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}