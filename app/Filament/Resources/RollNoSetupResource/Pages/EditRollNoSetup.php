<?php

namespace App\Filament\Resources\RollNoSetupResource\Pages;

use App\Filament\Resources\RollNoSetupResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditRollNoSetup extends EditRecord
{
    protected static string $resource = RollNoSetupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}