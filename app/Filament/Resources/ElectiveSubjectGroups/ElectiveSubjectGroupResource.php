<?php

namespace App\Filament\Resources\ElectiveSubjectGroups;

use App\Filament\Resources\ElectiveSubjectGroups\Pages\CreateElectiveSubjectGroup;
use App\Filament\Resources\ElectiveSubjectGroups\Pages\EditElectiveSubjectGroup;
use App\Filament\Resources\ElectiveSubjectGroups\Pages\ListElectiveSubjectGroups;
use App\Filament\Resources\ElectiveSubjectGroups\Schemas\ElectiveSubjectGroupForm;
use App\Filament\Resources\ElectiveSubjectGroups\Tables\ElectiveSubjectGroupsTable;
use App\Models\ElectiveSubjectGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ElectiveSubjectGroupResource extends Resource
{
    protected static ?string $model = ElectiveSubjectGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ElectiveSubjectGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ElectiveSubjectGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListElectiveSubjectGroups::route('/'),
            'create' => CreateElectiveSubjectGroup::route('/create'),
            'edit' => EditElectiveSubjectGroup::route('/{record}/edit'),
        ];
    }
}
