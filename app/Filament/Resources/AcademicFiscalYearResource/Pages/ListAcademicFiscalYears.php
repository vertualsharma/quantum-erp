<?php

namespace App\Filament\Resources\AcademicFiscalYearResource\Pages;

use App\Filament\Resources\AcademicFiscalYearResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListAcademicFiscalYears extends ListRecords
{
    protected static string $resource = AcademicFiscalYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}