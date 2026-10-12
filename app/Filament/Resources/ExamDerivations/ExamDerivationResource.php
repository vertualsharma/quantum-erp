<?php

namespace App\Filament\Resources\ExamDerivations;

use App\Filament\Resources\ExamDerivations\Pages;
use App\Models\ExamClassSetup;
use App\Models\ExamDerivation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ExamDerivationResource extends Resource
{
    protected static ?string $model = ExamDerivation::class;

    protected static ?string $slug = 'exam-derivations';

    public static function getNavigationGroup(): ?string
    {
        return 'Exam Setup';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrows-right-left';
    }

    public static function getNavigationLabel(): string
    {
        return 'Exam Derivation';
    }

    protected static function examOptions(): \Illuminate\Support\Collection
    {
        return ExamClassSetup::with(['exam', 'schoolClass'])
            ->get()
            ->mapWithKeys(fn ($setup) => [
                $setup->id => $setup->exam->name . '-' . $setup->exam->exam_type . ' (' . $setup->schoolClass->name . ')',
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make('target_exam_class_setup_id')
                        ->label('Target Exam (e.g. ResultPublish) *')
                        ->options(fn () => self::examOptions())
                        ->required()
                        ->live()
                        ->preload()
                        ->searchable(),

                    Select::make('source_exam_class_setup_id')
                        ->label('Source Exam (e.g. Entry) *')
                        ->options(fn () => self::examOptions())
                        ->required()
                        ->preload()
                        ->searchable()
                        ->unique(
                            modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, Get $get) {
                                return $rule->where('target_exam_class_setup_id', $get('target_exam_class_setup_id'));
                            },
                            ignoreRecord: true
                        )
                        ->validationMessages([
                            'unique' => 'This source exam is already linked to the selected target exam.',
                        ]),

                    TextInput::make('percentage')
                        ->label('Percentage *')
                        ->numeric()
                        ->default(100)
                        ->minValue(0)
                        ->maxValue(100)
                        ->required(),

                    Toggle::make('is_main_exam')
                        ->label('Main Exam?')
                        ->default(true),
                ]),

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
                TextColumn::make('targetExamClassSetup.exam.name')->label('Target Exam'),
                TextColumn::make('sourceExamClassSetup.exam.name')
                    ->label('Source Exam')
                    ->formatStateUsing(fn ($record) => $record->sourceExamClassSetup->exam->name . '-' . $record->sourceExamClassSetup->exam->exam_type),
                TextColumn::make('percentage')->label('Percentage')->suffix('%'),
                IconColumn::make('is_main_exam')->label('Main Exam?')->boolean(),
                IconColumn::make('is_active')->label('Status')->boolean(),
            ])
            ->filters([])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('toggleActive')
                    ->label(fn (ExamDerivation $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->color(fn (ExamDerivation $record) => $record->is_active ? 'danger' : 'success')
                    ->icon(fn (ExamDerivation $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->action(fn (ExamDerivation $record) => $record->update(['is_active' => ! $record->is_active])),
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
            'index' => Pages\ListExamDerivations::route('/'),
            'create' => Pages\CreateExamDerivation::route('/create'),
            'edit' => Pages\EditExamDerivation::route('/{record}/edit'),
        ];
    }
}