<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatchYearResource\Pages;
use App\Models\BatchYear;
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

class BatchYearResource extends Resource
{
    protected static ?string $model = BatchYear::class;

    protected static ?string $slug = 'batch-years';

    public static function getNavigationGroup(): ?string
    {
        return 'Setup'; // Groups it together with Academic Year Setup
    }

    public static function getNavigationLabel(): string
    {
        return 'Batch Year Setup';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationSort(): ?int
    {
        return 3; // Positions it right under Academic Year Setup
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nepali_year')
                    ->label('Batch Year (Nepali/BS) *')
                    ->placeholder('e.g., 2083')
                    ->required(),

                TextInput::make('title')
                    ->label('Title / Description')
                    ->placeholder('e.g., Batch of 2083'),

                Toggle::make('is_current')
                    ->label('Set as Current Active Batch')
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
                TextColumn::make('nepali_year')->label('Batch Year (BS)')->searchable()->sortable(),
                TextColumn::make('title')->label('Title')->searchable(),
                IconColumn::make('is_current')
                    ->label('Current Batch')
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
            'index' => Pages\ListBatchYears::route('/'),
            'create' => Pages\CreateBatchYear::route('/create'),
            'view' => Pages\ViewBatchYear::route('/{record}'),
            'edit' => Pages\EditBatchYear::route('/{record}/edit'),
        ];
    }
}