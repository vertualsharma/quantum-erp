<?php

namespace App\Filament\Resources\Students\Forms;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Set;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(1)
                ->schema([

                    // ==========================================
                    // SECTION 1: CORE STUDENT PROFILE & ACADEMICS
                    // ==========================================
                    Section::make('New Student Registration')
                        ->schema([
                            // Row 1: Administrative Identifiers (Optimized for standard display scaling)
                            Grid::make()
                                ->columns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 3,
                                    'xl' => 5, // 5 columns on extra-wide screens, cleanly collapsing down on smaller monitors
                                ])->schema([
                                    TextInput::make('batch_year')->label('Batch Year *')->required(),
                                    TextInput::make('fiscal_year')->label('Fiscal Year *')->required(),
                                    TextInput::make('joined_batch_year')->label('Joined Batch Year *')->required(),
                                    TextInput::make('regd_no')->label('Regd. Number'),
                                    TextInput::make('iemis_code')->label('IEMIS Code'),
                                ]),

                            // Sub-Grid Split: Left side for details, Right side for Image Upload
                            Grid::make()
                                ->columns([
                                    'default' => 1,
                                    'lg' => 4,
                                ])->schema([
                                    Grid::make(1)->columnSpan(['default' => 1, 'lg' => 3])->schema([
                                        // Row 2: Names (English)
                                        Grid::make()
                                            ->columns([
                                                'default' => 1,
                                                'sm' => 3,
                                            ])->schema([
                                                TextInput::make('first_name')->label('First Name *')->required(),
                                                TextInput::make('middle_name')->label('Middle Name'),
                                                TextInput::make('last_name')->label('Last Name *')->required(),
                                            ]),
                                        // Row 3: Names (Nepali)
                                        Grid::make()
                                            ->columns([
                                                'default' => 1,
                                                'sm' => 3,
                                            ])->schema([
                                                TextInput::make('first_name_np')->label('First Name (Nepali) *')->required(),
                                                TextInput::make('middle_name_np')->label('Middle Name (Nepali)'),
                                                TextInput::make('last_name_np')->label('Last Name (Nepali) *')->required(),
                                            ]),
                                    ]),
                                    
                                    // Profile Photo Placement
                                    Grid::make(1)->columnSpan(['default' => 1, 'lg' => 1])->schema([
                                        FileUpload::make('student_image')
                                            ->label('Student Image')
                                            ->image()
                                            ->avatar() // Handles circular layout and cropper automatically
                                            ->imageEditor(),
                                    ]),
                                ]),

                            // Row 4: Core Demographics & Identifiers
                            Grid::make()
                                ->columns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 4,
                                ])->schema([
                                    TextInput::make('mobile_no')->label('Mobile No. *')->tel()->required(),
                                    Select::make('gender')
                                        ->label('Gender *')
                                        ->options(['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'])
                                        ->required(),
                                    TextInput::make('nationality')->label('Nationality *')->default('Nepalese')->required(),
                                    TextInput::make('national_id_no')->label('National ID No.'),
                                ]),

                            // Row 5: Dates & Secondary Info (Automated Age Calculation)
                            Grid::make()
                                ->columns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 3,
                                    'xl' => 5,
                                ])->schema([
                                    TextInput::make('email')->label('Email')->email(),
                                    TextInput::make('campus_email')->label('Campus Email')->email(),
                                    TextInput::make('dob_bs')->label('Date of Birth (Nepali) *')->required(),
                                    
                                    DatePicker::make('dob_ad')
                                        ->label('Date of Birth (English) *')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (?string $state, Set $set) {
                                            if (blank($state)) {
                                                $set('age', null);
                                                return;
                                            }

                                            try {
                                                $age = \Carbon\Carbon::parse($state)->age;
                                                $set('age', $age);
                                            } catch (\Exception $e) {
                                                $set('age', null);
                                            }
                                        }),

                                    TextInput::make('age')
                                        ->label('Age')
                                        ->numeric()
                                        ->readOnly()
                                        ->extraAttributes(['class' => 'bg-gray-50 dark:bg-gray-800/50']),
                                ]),

                            // Row 6: Structural Hierarchy Alignment
                            Grid::make()
                                ->columns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 4,
                                ])->schema([
                                    Select::make('faculty')->relationship('faculty', 'name')->label('Select Faculty *')->required(),
                                    Select::make('level')->relationship('level', 'name')->label('Select Level *')->required(),
                                    Select::make('program')->relationship('program', 'name')->label('Select Program'),
                                    Select::make('schoolClass')->relationship('schoolClass', 'name')->label('Select Class'),
                                ]),

                            // Row 7: Sectioning & Characteristics
                            Grid::make()
                                ->columns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 3,
                                    'xl' => 5,
                                ])->schema([
                                    Select::make('section')->relationship('section', 'name')->label('Select Section'),
                                    Select::make('blood_group')
                                        ->label('Blood Group')
                                        ->options(['A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-']),
                                    TextInput::make('medical_condition')->label('Medical Condition'),
                                    Select::make('religion')
                                        ->label('Religion *')
                                        ->options(['Hinduism' => 'Hinduism', 'Buddhism' => 'Buddhism', 'Islam' => 'Islam', 'Christianity' => 'Christianity', 'Other' => 'Other'])
                                        ->required(),
                                    TextInput::make('ethnicity')->label('Ethnicity *')->required(),
                                ]),

                            // Row 8: Societal/Background Filters & Indicators
                            Grid::make()
                                ->columns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 3,
                                    'xl' => 5,
                                ])->schema([
                                    TextInput::make('student_caste')->label('Student Caste'),
                                    TextInput::make('mother_tongue')->label('Mother Tongue')->default('Nepali'),
                                    DatePicker::make('admission_date')->label('Select Admission Date'),
                                    Select::make('is_mukta_kamaiya')
                                        ->label('Is Mukta Kamaiya? *')
                                        ->options(['No' => 'No', 'Yes' => 'Yes'])->required()->default('No'),
                                    Select::make('is_martyr_family')
                                        ->label('Is From Martyr Family? *')
                                        ->options(['No' => 'No', 'Yes' => 'Yes'])->required()->default('No'),
                                ]),

                            // Row 9: Prior Academic Records
                            Grid::make()
                                ->columns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 3,
                                    'xl' => 5,
                                ])->schema([
                                    Select::make('shift')->label('Shift')->options(['Morning' => 'Morning', 'Day' => 'Day']),
                                    Select::make('medium')->label('Medium')->options(['English' => 'English', 'Nepali' => 'Nepali']),
                                    TextInput::make('entrance_score')->label('Entrance Score')->numeric(),
                                    TextInput::make('passed_level')->label('Passed Level'),
                                    TextInput::make('passed_institution')->label('Passed Institution'),
                                ]),
                            
                            Grid::make()
                                ->columns([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 4,
                                ])->schema([
                                    TextInput::make('passed_gpa')->label('Passed GPA'),
                                    Select::make('fatherUser')->relationship('fatherUser', 'name')->label('Select Father Lookup (Optional)'),
                                    Select::make('motherUser')->relationship('motherUser', 'name')->label('Select Mother Lookup (Optional)'),
                                    Select::make('guardianUser')->relationship('guardianUser', 'name')->label('Select Local Guardian Lookup (Optional)'),
                                ]),
                        ]),

                    // ==========================================
                    // SECTION 2: PARENT & GUARDIAN DETAILS TRIO
                    // ==========================================
                    Grid::make()
                        ->columns([
                            'default' => 1,
                            'xl' => 3, // Displays Father, Mother, and Guardian as 3 side-by-side balanced cards on wide screens
                        ])->schema([
                        Section::make('Father Info')
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('father_name')->label('Name *')->required(),
                                TextInput::make('father_mobile')->label('Mobile No. *')->required(),
                                TextInput::make('father_email')->label('Email')->email(),
                                TextInput::make('father_occupation')->label('Occupation'),
                                TextInput::make('father_office_name')->label('Office Name'),
                                TextInput::make('father_designation')->label('Designation'),
                                TextInput::make('father_office_contact')->label('Office Contact'),
                                TextInput::make('father_address')->label('Address'),
                            ]),

                        Section::make('Mother Info')
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('mother_name')->label('Name *')->required(),
                                TextInput::make('mother_mobile')->label('Mobile No.')->required(),
                                TextInput::make('mother_email')->label('Email')->email(),
                                TextInput::make('mother_occupation')->label('Occupation'),
                                TextInput::make('mother_office_name')->label('Office Name'),
                                TextInput::make('mother_designation')->label('Designation'),
                                TextInput::make('mother_office_contact')->label('Office Contact'),
                                TextInput::make('mother_address')->label('Address'),
                            ]),

                        Section::make('Local Guardian Info')
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('guardian_name')->label('Name *')->required(),
                                Select::make('guardian_gender')->label('Gender')->options(['Male'=>'Male', 'Female'=>'Female', 'Other'=>'Other']),
                                TextInput::make('guardian_mobile')->label('Mobile No. *')->required(),
                                TextInput::make('guardian_email')->label('Email')->email(),
                                TextInput::make('guardian_occupation')->label('Occupation'),
                                TextInput::make('guardian_relation')->label('Relation'),
                                TextInput::make('guardian_office_name')->label('Office Name'),
                                TextInput::make('guardian_address')->label('Address'),
                            ]),
                    ]),

                    // ==========================================
                    // SECTION 3: LOCATION ADDRESS GEOMETRY
                    // ==========================================
                    Grid::make()
                        ->columns([
                            'default' => 1,
                            'lg' => 2, // split Permanent and Temporary addresses into 2 clean columns
                        ])->schema([
                        Section::make('Permanent Address')
                            ->columnSpan(1)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('perm_country')->label('Country')->default('Nepal'),
                                    TextInput::make('perm_state')->label('Select State'),
                                    TextInput::make('perm_district')->label('Select District'),
                                    TextInput::make('perm_municipality')->label('Select Municipality'),
                                    TextInput::make('perm_ward')->label('Ward Number')->numeric(),
                                    TextInput::make('perm_google_code')->label('Google Plus Code'),
                                    TextInput::make('perm_zip')->label('Postcode / Zip / PIN'),
                                    TextInput::make('perm_tole')->label('Tole / Street Address'),
                                ]),
                                TextInput::make('perm_house_no')->label('House Number'),
                            ]),

                        Section::make('Temporary Address')
                            ->columnSpan(1)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('temp_country')->label('Country')->default('Nepal'),
                                    TextInput::make('temp_state')->label('Select State'),
                                    TextInput::make('temp_district')->label('Select District'),
                                    TextInput::make('temp_municipality')->label('Select Municipality'),
                                    TextInput::make('temp_ward')->label('Ward Number')->numeric(),
                                    TextInput::make('temp_google_code')->label('Google Plus Code'),
                                    TextInput::make('temp_zip')->label('Postcode / Zip / PIN'),
                                    TextInput::make('temp_tole')->label('Tole / Street Address'),
                                ]),
                                TextInput::make('temp_house_no')->label('House Number'),
                            ]),
                    ]),

                    // ==========================================
                    // SECTION 4: SYSTEM DOCUMENTATION & ARCHIVE
                    // ==========================================
                    Section::make('Document & Save')
                        ->schema([
                            Grid::make()
                                ->columns([
                                    'default' => 1,
                                    'sm' => 2,
                                ])->schema([
                                    Select::make('document_type')
                                        ->label('Select Document Type')
                                        ->options(['Mark Sheet' => 'Mark Sheet', 'Transfer Certificate' => 'Transfer Certificate', 'Citizenship' => 'Citizenship Copy']),
                                    FileUpload::make('attached_document')
                                        ->label('Upload File')
                                        ->disk('public')
                                        ->directory('student-documents'),
                                ]),
                        ]),

                ]),
        ]);
    }
}