<?php

namespace App\Filament\Pages;

use App\Models\AcademicFiscalYear;
use App\Models\ClassSubject;
use App\Models\ExamClassSetup;
use App\Models\ExamSubjectMarkSetup;
use App\Models\Faculty;
use App\Models\Level;
use App\Models\Program;
use App\Models\SchoolClass;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FullMarkPassMarkSetup extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.full-mark-pass-mark-setup';

    protected static ?string $navigationLabel = 'Full/Pass Mark Setup';

    public static function getNavigationGroup(): ?string
    {
        return 'Exam Setup';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calculator';
    }

    public ?array $data = [];

    public ?int $selectedAcademicYearId = null;
    public ?int $selectedFacultyId = null;
    public ?int $selectedLevelId = null;
    public ?int $selectedProgramId = null;
    public ?int $selectedSchoolClassId = null;
    public ?int $selectedExamClassSetupId = null;

    public bool $loaded = false;

    public function mount(): void
    {
        $this->selectedAcademicYearId = AcademicFiscalYear::where('is_current', true)->first()?->id;
        $this->filterForm->fill([
            'academic_year_id' => $this->selectedAcademicYearId,
        ]);
    }

    public function filterForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)->schema([
                    Select::make('academic_year_id')
                        ->label('Select Year *')
                        ->placeholder('Select Academic Year...')
                        ->options(fn () => AcademicFiscalYear::pluck('name', 'id'))
                        ->default(fn () => AcademicFiscalYear::where('is_current', true)->first()?->id)
                        ->required()
                        ->preload()
                        ->searchable(),

                    Select::make('faculty_id')
                        ->label('Select Faculty *')
                        ->placeholder('Select Faculty...')
                        ->options(fn () => Faculty::pluck('name', 'id'))
                        ->live()
                        ->required()
                        ->preload()
                        ->searchable()
                        ->afterStateUpdated(fn ($set) => $set('level_id', null)),

                    Select::make('level_id')
                        ->label('Select Level')
                        ->placeholder('Select Level...')
                        ->options(fn () => Level::pluck('name', 'id'))
                        ->live()
                        ->preload()
                        ->searchable(),

                    Select::make('program_id')
                        ->label('Select Program')
                        ->placeholder('Select Program...')
                        ->options(function (Get $get) {
                            $facultyId = $get('faculty_id');
                            if (! $facultyId) {
                                return [];
                            }

                            return Program::where('faculty_id', $facultyId)
                                ->when($get('level_id'), fn ($q) => $q->where('level_id', $get('level_id')))
                                ->pluck('name', 'id');
                        })
                        ->live()
                        ->preload()
                        ->searchable(),

                    Select::make('school_class_id')
                        ->label('Select Class *')
                        ->placeholder('Select Class...')
                        ->options(function (Get $get) {
                            $facultyId = $get('faculty_id');
                            if (! $facultyId) {
                                return [];
                            }

                            return SchoolClass::where('faculty_id', $facultyId)
                                ->when($get('level_id'), fn ($q) => $q->where('level_id', $get('level_id')))
                                ->pluck('name', 'id');
                        })
                        ->live()
                        ->required()
                        ->preload()
                        ->searchable()
                        ->afterStateUpdated(fn ($set) => $set('exam_class_setup_id', null)),

                    Select::make('exam_class_setup_id')
                        ->label('Select Exam *')
                        ->placeholder('Select Exam...')
                        ->options(function (Get $get) {
                            $classId = $get('school_class_id');
                            $yearId = $get('academic_year_id');

                            if (! $classId || ! $yearId) {
                                return [];
                            }

                            return ExamClassSetup::with('exam')
                                ->where('school_class_id', $classId)
                                ->where('academic_year_id', $yearId)
                                ->get()
                                ->mapWithKeys(fn ($setup) => [
                                    $setup->id => $setup->exam->name . '-' . $setup->exam->exam_type,
                                ]);
                        })
                        ->required()
                        ->preload()
                        ->searchable(),
                ]),
            ])
            ->statePath('data');
    }

    public function loadAction(): void
    {
        $state = $this->filterForm->getState();

        $this->selectedAcademicYearId = $state['academic_year_id'] ?? null;
        $this->selectedFacultyId = $state['faculty_id'] ?? null;
        $this->selectedLevelId = $state['level_id'] ?? null;
        $this->selectedProgramId = $state['program_id'] ?? null;
        $this->selectedSchoolClassId = $state['school_class_id'] ?? null;
        $this->selectedExamClassSetupId = $state['exam_class_setup_id'] ?? null;

        if ($this->selectedExamClassSetupId && $this->selectedSchoolClassId) {
            $this->ensureRowsExist();
        }

        $this->loaded = true;
        $this->resetTable();
    }

    protected function ensureRowsExist(): void
    {
        $subjectIds = ClassSubject::where('school_class_id', $this->selectedSchoolClassId)
            ->where('is_active', true)
            ->pluck('subject_id');

        $existingSubjectIds = ExamSubjectMarkSetup::where('exam_class_setup_id', $this->selectedExamClassSetupId)
            ->pluck('subject_id');

        $missingSubjectIds = $subjectIds->diff($existingSubjectIds);

        foreach ($missingSubjectIds as $subjectId) {
            ExamSubjectMarkSetup::create([
                'exam_class_setup_id' => $this->selectedExamClassSetupId,
                'subject_id' => $subjectId,
                'theory_full_marks' => 0,
                'theory_pass_marks' => 0,
                'practical_full_marks' => 0,
                'practical_pass_marks' => 0,
                'total_full_marks' => 0,
                'total_pass_marks' => 0,
            ]);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = ExamSubjectMarkSetup::query()
                    ->with(['subject', 'examClassSetup.exam', 'examClassSetup.academicYear', 'examClassSetup.program', 'examClassSetup.schoolClass']);

                if (! $this->loaded || ! $this->selectedExamClassSetupId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where('exam_class_setup_id', $this->selectedExamClassSetupId);
            })
            ->columns([
                TextColumn::make('examClassSetup.academicYear.name')->label('Academic Year'),
                TextColumn::make('examClassSetup.program.name')->label('Program'),
                TextColumn::make('examClassSetup.schoolClass.name')->label('Class'),
                TextColumn::make('subject.name')->label('Subject'),
                TextColumn::make('subject.code')->label('Subject Code'),
                TextColumn::make('examClassSetup.exam.name')->label('Exam'),
                TextInputColumn::make('theory_full_marks')->label('Theory FM')->type('number'),
                TextInputColumn::make('theory_pass_marks')->label('Theory PM')->type('number'),
                TextInputColumn::make('practical_full_marks')->label('Practical FM')->type('number'),
                TextInputColumn::make('practical_pass_marks')->label('Practical PM')->type('number'),
            ])
            ->emptyStateHeading($this->loaded ? 'No subjects found for this class' : 'Select filters and click Load');
    }
}