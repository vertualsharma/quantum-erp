<?php

namespace App\Filament\Resources\BatchYearResource\Pages;

use App\Filament\Resources\BatchYearResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListBatchYears extends ListRecords
{
    protected static string $resource = BatchYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}