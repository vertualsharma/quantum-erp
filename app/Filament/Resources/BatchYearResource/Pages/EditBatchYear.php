<?php

namespace App\Filament\Resources\BatchYearResource\Pages;

use App\Filament\Resources\BatchYearResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditBatchYear extends EditRecord
{
    protected static string $resource = BatchYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}