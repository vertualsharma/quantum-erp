<?php

namespace App\Filament\Resources\ClassSubjects;

use App\Filament\Resources\ClassSubjects\Pages;
use App\Models\ClassSubject;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class ClassSubjectResource extends Resource
{
    protected static ?string $model = ClassSubject::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static string|UnitEnum|null $navigationGroup = 'Subject Setup';
    
    protected static ?string $navigationLabel = 'Program Class Subject Setup';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Academic Mapping')
                    ->schema([
                        Forms\Components\TextInput::make('academic_year')
                            ->default('2083')
                            ->required(),
                        Forms\Components\Select::make('faculty_id')
                            ->relationship('faculty', 'name')
                            ->searchable(),
                        Forms\Components\Select::make('level_id')
                            ->relationship('level', 'name')
                            ->searchable(),
                        Forms\Components\Select::make('program_id')
                            ->relationship('program', 'name')
                            ->searchable(),
                        Forms\Components\Select::make('school_class_id')
                            ->relationship('schoolClass', 'name')
                            ->label('Class')
                            ->required(),
                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->required(),
                        Forms\Components\Select::make('subject_group_id')
                            ->relationship('subjectGroup', 'name')
                            ->label('Group'),
                        Forms\Components\Select::make('elective_group_id')
                            ->relationship('electiveGroup', 'name')
                            ->label('Elective Group'),
                    ])->columns(4),

                Section::make('Subject Configuration')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'Mark' => 'Mark',
                                'Grade' => 'Grade',
                            ])
                            ->default('Mark')
                            ->required(),
                        Forms\Components\TextInput::make('grading_type')
                            ->default('Regular'),
                        Forms\Components\TextInput::make('th_credit_hour')
                            ->label('TH Credit Hour')
                            ->numeric()
                            ->default(0.00)
                            ->required(),
                        Forms\Components\TextInput::make('pr_credit_hour')
                            ->label('PR Credit Hour')
                            ->numeric()
                            ->default(0.00)
                            ->required(),
                        Forms\Components\Toggle::make('has_practical')
                            ->label('Has Practical?'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Active')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('academic_year')
                    ->label('Academic Year')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('program.name')
                    ->label('Program')
                    ->default('School')
                    ->sortable(),
                Tables\Columns\TextColumn::make('group.name')
                    ->label('Group')
                    ->default('Compulsory')
                    ->sortable(),
                Tables\Columns\TextColumn::make('schoolClass.name')
                    ->label('Class')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject.code')
                    ->label('Code'),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('grading_type')
                    ->label('Grading'),
                Tables\Columns\TextColumn::make('th_credit_hour')
                    ->label('TH Credit Hour')
                    ->numeric(2),
                Tables\Columns\TextColumn::make('pr_credit_hour')
                    ->label('PR Credit Hour')
                    ->numeric(2),
                Tables\Columns\TextColumn::make('total_credit_hour')
                    ->label('Total Credit Hour')
                    ->state(fn (ClassSubject $record): float => (float)$record->th_credit_hour + (float)$record->pr_credit_hour)
                    ->numeric(2),
                Tables\Columns\IconColumn::make('has_practical')
                    ->label('Has Practical?')
                    ->boolean(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year'),
                Tables\Filters\SelectFilter::make('school_class_id')
                    ->relationship('schoolClass', 'name')
                    ->label('Class'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassSubjects::route('/'),
            'create' => Pages\CreateClassSubject::route('/create'),
            'edit' => Pages\EditClassSubject::route('/{record}/edit'),
        ];
    }
}
