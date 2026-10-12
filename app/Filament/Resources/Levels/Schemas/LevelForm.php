<?php

namespace App\Filament\Resources\Levels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                    
                // ✅ FIXED: Added default(true) so new levels are active automatically
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}