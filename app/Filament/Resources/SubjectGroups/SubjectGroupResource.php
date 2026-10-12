<?php

namespace App\Filament\Resources\SubjectGroups;

use App\Filament\Resources\SubjectGroups\Pages;
use App\Models\SubjectGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class SubjectGroupResource extends Resource
{
    protected static ?string $model = SubjectGroup::class;

    protected static ?string $slug = 'subject-groups';

    public static function getNavigationGroup(): ?string
    {
        return 'Subject Setup';
    }

    public static function getNavigationSort(): ?int
    {
        return 0; // appears above Subject Master, matching the reference order
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-rectangle-stack';
    }

    public static function getNavigationLabel(): string
    {
        return 'Subject Group';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('code')
                        ->label('Code *')
                        ->required()
                        ->maxLength(20)
                        ->unique(ignoreRecord: true),

                    TextInput::make('name')
                        ->label('Name *')
                        ->required()
                        ->maxLength(100),
                ]),

                Textarea::make('description')
                    ->label('Description')
                    ->maxLength(255)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('code')->label('Code')->searchable()->sortable(),
                TextColumn::make('name')->label('Name')->searchable()->sortable(),
                TextColumn::make('description')->label('Description')->limit(40),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('id')
            ->filters([])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('toggleActive')
                    ->label(fn (SubjectGroup $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->color(fn (SubjectGroup $record) => $record->is_active ? 'danger' : 'success')
                    ->icon(fn (SubjectGroup $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->action(fn (SubjectGroup $record) => $record->update(['is_active' => ! $record->is_active])),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSubjectGroups::route('/'),
            'create' => Pages\CreateSubjectGroup::route('/create'),
            'edit' => Pages\EditSubjectGroup::route('/{record}/edit'),
        ];
    }
}
