<?php

namespace App\Filament\Resources\Exams;

use App\Filament\Resources\Exams\Pages;
use App\Models\Exam;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $slug = 'exams';

    public static function getNavigationGroup(): ?string
    {
        return 'Exam Setup';
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public static function getNavigationLabel(): string
    {
        return 'Exam Master';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Exam Name *')
                        ->required()
                        ->maxLength(150),

                    TextInput::make('print_title')
                        ->label('Exam Print Title')
                        ->maxLength(150),

                    Select::make('scope')
                        ->label('Scope *')
                        ->options([
                            'Internal' => 'Internal',
                            'External' => 'External',
                        ])
                        ->default('Internal')
                        ->required(),

                    Select::make('exam_type')
                        ->label('Exam Type *')
                        ->options([
                            'Entry' => 'Entry',
                            'ResultPublish' => 'ResultPublish',
                        ])
                        ->required(),
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
                TextColumn::make('name')->label('Exam Name')->searchable()->sortable(),
                TextColumn::make('print_title')->label('Exam Print Title')->searchable(),
                TextColumn::make('scope')->label('Scope')->sortable(),
                TextColumn::make('exam_type')->label('Exam Type')->sortable(),
                TextColumn::make('description')->label('Description')->limit(30),
                IconColumn::make('is_active')->label('Status')->boolean()->sortable(),
                TextColumn::make('creator.name')->label('Created By')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('toggleActive')
                    ->label(fn (Exam $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->color(fn (Exam $record) => $record->is_active ? 'danger' : 'success')
                    ->icon(fn (Exam $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->action(fn (Exam $record) => $record->update(['is_active' => ! $record->is_active])),
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
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
        ];
    }
}