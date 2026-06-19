<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select; // Added this import
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ugc_id')
                    ->label('UGC ID')
                    ->default(null),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('short_name')
                    ->required()
                    ->maxLength(50),

                TextInput::make('university')
                    ->placeholder('e.g., Tribhuvan University')
                    ->default(null),

                // 🌟 Dynamic Level Selector
                Select::make('level_id')
                    ->relationship('level', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),

                // 🌟 Dynamic Faculty Selector
                Select::make('faculty_id')
                    ->relationship('faculty', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),

                // 🌟 Clean UI Dropdown for Program Evaluation Style
                Select::make('type')
                    ->options([
                        'YEAR' => 'Yearly Basis',
                        'SEMESTER' => 'Semester Basis',
                    ])
                    ->default('YEAR')
                    ->required(),

                TextInput::make('no_of_years')
                    ->label('Duration (Years)')
                    ->required()
                    ->numeric()
                    ->default(0),

                TextInput::make('start_date_bs')
                    ->label('Start Date (BS)')
                    ->placeholder('YYYY-MM-DD')
                    ->default(null),

                DatePicker::make('start_date_ad')
                    ->label('Start Date (AD)'),

                TextInput::make('approval_date')
                    ->default(null),

                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}