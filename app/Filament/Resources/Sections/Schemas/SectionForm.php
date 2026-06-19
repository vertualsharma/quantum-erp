<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Forms\Components\Select; // Added this import
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Section Name')
                    ->placeholder('e.g., A, B, Lotus, Sagarmatha')
                    ->required()
                    ->maxLength(255),

                // 🌟 Dynamic Class Selector
                Select::make('school_class_id')
                    ->relationship('schoolClass', 'name') // Maps to schoolClass() method in Section model
                    ->preload()
                    ->searchable()
                    ->required(),

                // 🌟 Dynamic Program Selector
                Select::make('program_id')
                    ->relationship('program', 'name') // Maps to program() method in Section model
                    ->preload()
                    ->searchable()
                    ->required(),

                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}