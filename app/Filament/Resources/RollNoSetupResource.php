<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RollNoSetupResource\Pages;
use App\Models\RollNoSetup; // Assumes your model is named RollNoSetup
use App\Models\AcademicFiscalYear;
use App\Models\SchoolClass;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class RollNoSetupResource extends Resource
{
    protected static ?string $model = RollNoSetup::class;

    protected static ?string $slug = 'roll-no-setups';

    public static function getNavigationGroup(): ?string
    {
        return 'Setup'; // Groups it together under the same dropdown menu
    }

    public static function getNavigationLabel(): string
    {
        return 'Roll No Setup';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationSort(): ?int
    {
        return 1; // Places it at the top of the Setup menu group
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academic_fiscal_year_id')
                    ->label('Academic Year *')
                    ->options(fn () => AcademicFiscalYear::pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Select::make('school_class_id')
                    ->label('Class *')
                    ->options(fn () => SchoolClass::pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                TextInput::make('start_from')
                    ->label('Start Roll No From *')
                    ->numeric()
                    ->default(1)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('academicFiscalYear.name')->label('Academic Year')->sortable(),
                TextColumn::make('schoolClass.name')->label('Class')->sortable(),
                TextColumn::make('start_from')->label('Starts From')->sortable(),
            ])
            ->filters([])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRollNoSetups::route('/'),
            'create' => Pages\CreateRollNoSetup::route('/create'),
            'view' => Pages\ViewRollNoSetup::route('/{record}'),
            'edit' => Pages\EditRollNoSetup::route('/{record}/edit'),
        ];
    }
}