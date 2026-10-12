<?php

namespace App\Filament\Resources\Students\Forms;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

use App\Models\AcademicFiscalYear;
use App\Models\BatchYear;
use App\Models\SchoolClass;
use App\Models\Section as SectionModel;
use App\Models\District;
use App\Models\Municipality;
use App\Models\State;

class StudentForm
{
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    
                    Step::make('Student Info')
                        ->description('Core identification')
                        ->icon('heroicon-m-user')
                        ->schema([
                            Grid::make(3)->schema([
                                Select::make('academic_year_id')
                                    ->label('Academic Year *')
                                    ->options(fn () => AcademicFiscalYear::pluck('name', 'id'))
                                    ->default(fn () => AcademicFiscalYear::where('is_current', true)->first()?->id)
                                    ->required()
                                    ->preload()
                                    ->searchable(),

                                Select::make('batch_year_id')
                                    ->label('Batch Year *')
                                    ->options(fn () => BatchYear::pluck('nepali_year', 'id'))
                                    ->default(fn () => BatchYear::where('is_current', true)->first()?->id)
                                    ->required()
                                    ->preload()
                                    ->searchable(),

                                TextInput::make('roll_no')
                                    ->label('Roll No')
                                    ->disabled() // Kept safe for automatic roll configuration triggers
                                    ->placeholder('Auto-assigned via Roll No Setup'),
                            ]),

                            Grid::make(3)->schema([
                                TextInput::make('first_name')
                                    ->label('First Name *')
                                    ->maxLength(50)
                                    ->required(),
                                    
                                TextInput::make('middle_name')
                                    ->label('Middle Name')
                                    ->maxLength(50),
                                    
                                TextInput::make('last_name')
                                    ->label('Last Name *')
                                    ->maxLength(50)
                                    ->required(),
                            ]),

                            Grid::make(3)->schema([
                                Select::make('gender')
                                    ->label('Gender *')
                                    ->options(['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'])
                                    ->required(),
                                    
                                DatePicker::make('dob_ad')
                                    ->label('Date of Birth (AD) *')
                                    ->maxDate(now()) // ✅ FIXED: Prevents futuristic dates entries
                                    ->required(),
                                    
                                TextInput::make('mobile_no')
                                    ->label('Mobile No. *')
                                    ->required()
                                    ->tel()
                                    ->maxLength(15), // ✅ FIXED: Safe boundary constraint
                            ]),

                            Grid::make(3)->schema([
                                Select::make('faculty_id')
                                    ->label('Faculty *')
                                    ->relationship('faculty', 'name')
                                    ->preload() // ✅ FIXED: Optimizes database lookup load
                                    ->live()
                                    ->searchable()
                                    ->required()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('class_id', null);
                                        $set('section_id', null);
                                    }),

                                Select::make('class_id')
                                    ->label('Class *')
                                    ->options(function (Get $get) {
                                        $facultyId = $get('faculty_id');
                                        
                                        if (! $facultyId) {
                                            return [];
                                        }

                                        return SchoolClass::where('faculty_id', $facultyId)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->live()
                                    ->preload() // ✅ FIXED: Smoothly caches local options array
                                    ->searchable()
                                    ->required()
                                    ->afterStateUpdated(fn (Set $set) => $set('section_id', null)),

                                Select::make('section_id')
                                    ->label('Section')
                                    ->options(function (Get $get) {
                                        $classId = $get('class_id');
                                        
                                        if (! $classId) {
                                            return [];
                                        }

                                        return SectionModel::where('school_class_id', $classId)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->preload()
                                    ->searchable(),
                            ]),
                        ]),

                    Step::make('Guardian & Address')
                        ->description('Contact and home location')
                        ->icon('heroicon-m-home')
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('guardian_name')
                                    ->label('Guardian Name *')
                                    ->maxLength(100)
                                    ->required(),
                                    
                                Select::make('guardian_relation')
                                    ->label('Relation *')
                                    ->options(['Father' => 'Father', 'Mother' => 'Mother', 'Brother' => 'Brother', 'Sister' => 'Sister', 'Other' => 'Other'])
                                    ->required(),
                                    
                                TextInput::make('guardian_phone')
                                    ->label('Guardian Mobile *')
                                    ->required()
                                    ->tel()
                                    ->maxLength(15),
                            ]),

                            Grid::make(3)->schema([
                                Select::make('perm_state_id')
                                    ->label('State')
                                    ->relationship('state', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('perm_district_id', null);
                                        $set('perm_municipality_id', null);
                                    }),

                                Select::make('perm_district_id')
                                    ->label('District')
                                    ->options(function (Get $get) {
                                        $stateId = $get('perm_state_id');
                                        
                                        if (! $stateId) {
                                            return [];
                                        }

                                        return District::where('state_id', $stateId)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('perm_municipality_id', null)),

                                Select::make('perm_municipality_id')
                                    ->label('Municipality')
                                    ->options(function (Get $get) {
                                        $districtId = $get('perm_district_id');
                                        
                                        if (! $districtId) {
                                            return [];
                                        }

                                        return Municipality::where('district_id', $districtId)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->preload()
                                    ->searchable(),
                            ]),
                            
                            Grid::make(2)->schema([
                                TextInput::make('perm_ward')
                                    ->label('Ward No')
                                    ->maxLength(5),
                                    
                                TextInput::make('perm_tole')
                                    ->label('Tole / Village')
                                    ->maxLength(100),
                            ]),
                        ]),

                    Step::make('Attachments')
                        ->description('Photos and paperwork')
                        ->icon('heroicon-m-document-text')
                        ->schema([
                            Grid::make(2)->schema([
                                FileUpload::make('student_image')
                                    ->label('Student Profile Image')
                                    ->image()
                                    ->maxFiles(1)
                                    ->avatar()
                                    ->columnSpan(1),
                                    
                                FileUpload::make('document_path')
                                    ->label('Other Certificates / Documents')
                                    ->multiple()
                                    ->maxFiles(3)
                                    ->columnSpan(1),
                            ]),
                        ]),
                        
                ])->columnSpanFull()
            ]);
    }
}