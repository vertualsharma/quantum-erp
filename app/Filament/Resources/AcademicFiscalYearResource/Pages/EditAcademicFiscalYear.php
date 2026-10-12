<?php

namespace App\Filament\Resources\AcademicFiscalYearResource\Pages;

use App\Filament\Resources\AcademicFiscalYearResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditAcademicFiscalYear extends EditRecord
{
    protected static string $resource = AcademicFiscalYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}