<?php

namespace App\Filament\Resources\SubjectGroups;

use App\Filament\Resources\SubjectGroups\Pages;
use App\Models\Subject;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $slug = 'subjects';

    public static function getNavigationGroup(): ?string
    {
        return 'Subject Setup';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-book-open';
    }

    public static function getNavigationLabel(): string
    {
        return 'Subject Master';
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
                        ->label('Subject Name *')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('short_name')
                        ->label('Short Name')
                        ->maxLength(30),

                    TextInput::make('position')
                        ->label('Position')
                        ->numeric()
                        ->default(fn () => (Subject::max('position') ?? 0) + 1),
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
                TextColumn::make('name')->label('Subject')->searchable()->sortable(),
                TextColumn::make('short_name')->label('Short Name')->searchable(),
                TextColumn::make('position')->label('Position')->sortable(),
                TextColumn::make('description')->label('Description')->limit(40),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('position')
            ->filters([])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('toggleActive')
                    ->label(fn (Subject $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->color(fn (Subject $record) => $record->is_active ? 'danger' : 'success')
                    ->icon(fn (Subject $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->action(fn (Subject $record) => $record->update(['is_active' => ! $record->is_active])),
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
