<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicFiscalYearResource\Pages;
use App\Models\AcademicFiscalYear;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class AcademicFiscalYearResource extends Resource
{
    protected static ?string $model = AcademicFiscalYear::class;

    protected static ?string $slug = 'academic-fiscal-years';

    public static function getNavigationGroup(): ?string
    {
        return 'Setup';
    }

    public static function getNavigationLabel(): string
    {
        return 'Academic Year Setup';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Academic Year (Nepali/BS) *')
                    ->placeholder('e.g., 2083/84')
                    ->required(),

                TextInput::make('english_name')
                    ->label('English Year (AD)')
                    ->placeholder('e.g., 2026/27'),

                Toggle::make('is_current')
                    ->label('Set as Current Active Year')
                    ->default(false),

                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('name')->label('Academic Year')->searchable()->sortable(),
                TextColumn::make('english_name')->label('English Year')->searchable(),
                IconColumn::make('is_current')
                    ->label('Current Year')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
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
            'index' => Pages\ListAcademicFiscalYears::route('/'),
            'create' => Pages\CreateAcademicFiscalYear::route('/create'),
            'view' => Pages\ViewAcademicFiscalYear::route('/{record}'),
            'edit' => Pages\EditAcademicFiscalYear::route('/{record}/edit'),
        ];
    }
}