<?php

namespace App\Filament\Resources\SchoolClasses\Schemas;

use Filament\Forms\Components\Select; // Added this import
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SchoolClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Class Name')
                    ->placeholder('e.g., Class 9, Class 10, Nursery')
                    ->required()
                    ->maxLength(255),

                TextInput::make('ugc_name')
                    ->label('UGC Name')
                    ->default(null),

                TextInput::make('position')
                    ->label('Display Order / Position')
                    ->helperText('Used to sort classes correctly in reports (e.g., Nursery = 1, Class 1 = 5, Class 9 = 13)')
                    ->required()
                    ->numeric()
                    ->default(0),

                // 🌟 Dynamic Level Selector
                Select::make('level_id')
                    ->relationship('level', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),

                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}