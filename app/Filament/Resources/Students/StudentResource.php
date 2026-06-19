<?php

namespace App\Filament\Resources\Students;

use App\Filament\Resources\Students\Pages;
use App\Models\Student;
use App\Filament\Resources\Students\Forms\StudentForm; 
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

// Updated Unified Action Imports
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

// Unified Schema Structural Components
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;

// Corrected Data Display Entry Components
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $slug = 'students';

    /**
     * Using methods to avoid Type Variance errors
     */
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationLabel(): string
    {
        return 'Student Registration';
    }

    /**
     * We use the FULL PATH (\Filament\Schemas\Schema) 
     * to prevent PHP from looking in the local folder.
     */
    public static function form(Schema $schema): Schema
    {
        return StudentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('regd_no')->label('Regd No')->searchable()->sortable(),
                TextColumn::make('first_name')->label('First Name')->searchable()->sortable(),
                TextColumn::make('last_name')->label('Last Name')->searchable()->sortable(),
                TextColumn::make('batch_year')->label('Batch')->sortable(),
                TextColumn::make('level.name')->label('Level')->sortable(),
                TextColumn::make('faculty.name')->label('Faculty')->sortable(),
            ])
            ->filters([])
            ->actions([
                ViewAction::make(), // Added row view dashboard action
                EditAction::make(), 
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Profile View layout split into a quick sidebar 
     * and a main content area using tab components
     */
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(12)
                    ->schema([
                        // Left Sidebar: Quick Profile Info & Photo Card
                        Section::make('Student Card')
                            ->columnSpan(['lg' => 4])
                            ->schema([
                                ImageEntry::make('student_image') // Maps to image attribute/column
                                    ->label('')
                                    ->circular()
                                    ->alignment('center')
                                    ->defaultImageUrl(url('/images/default-avatar.png')),
                                    
                                TextEntry::make('regd_no')
                                    ->label('Registration No.')
                                    ->weight('bold')
                                    ->alignment('center')
                                    ->copyable(),
                                    
                                TextEntry::make('first_name')
                                    ->label('Full Name')
                                    ->alignment('center')
                                    ->formatStateUsing(fn ($record) => "{$record->first_name} {$record->last_name}"),
                            ]),

                        // Right Area: Profile Details separated into clear Tabs
                        Tabs::make('Profile Information')
                            ->columnSpan(['lg' => 8])
                            ->tabs([
                                // Tab 1: Academic Records
                                Tabs\Tab::make('Academic Info')
                                    ->icon('heroicon-m-academic-cap')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextEntry::make('level.name')->label('Level / Class')->placeholder('N/A'),
                                            TextEntry::make('faculty.name')->label('Faculty / Stream')->placeholder('N/A'),
                                            TextEntry::make('batch_year')->label('Batch Year')->placeholder('N/A'),
                                            TextEntry::make('created_at')->label('Enrollment Date')->date()->placeholder('N/A'),
                                        ]),
                                    ]),

                                // Tab 2: Personal Profile Records
                                Tabs\Tab::make('Personal Profile')
                                    ->icon('heroicon-m-user')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextEntry::make('first_name')->label('First Name'),
                                            TextEntry::make('last_name')->label('Last Name'),
                                            TextEntry::make('gender')->label('Gender')->placeholder('N/A'),
                                            TextEntry::make('dob')->label('Date of Birth')->date()->placeholder('N/A'), // Fixed Carbon crash here
                                        ]),
                                    ]),

                                // Tab 3: Parents & Emergency Contacts
                                Tabs\Tab::make('Guardians & Contacts')
                                    ->icon('heroicon-m-home')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextEntry::make('father_name')->label("Father's Name")->placeholder('N/A'),
                                            TextEntry::make('mother_name')->label("Mother's Name")->placeholder('N/A'),
                                            TextEntry::make('guardian_phone')->label('Emergency Contact')->placeholder('N/A'),
                                        ]),
                                    ]),
                            ]),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'), // Added view router mapping
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}