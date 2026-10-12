<?php

namespace App\Filament\Pages;

use App\Models\AcademicFiscalYear;
use App\Models\ExamClassSetup;
use App\Models\Faculty;
use App\Models\Level;
use App\Models\Program;
use App\Models\SchoolClass;
use App\Models\Section as SectionModel;
use App\Models\Student;
use App\Models\StudentExamAttendance;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManualAttendanceEntry extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.manual-attendance-entry';

    protected static ?string $navigationLabel = 'Manual Attendance Entry';

    public static function getNavigationGroup(): ?string
    {
        return 'Exam Attendance';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public ?array $data = [];
    public ?array $totalAttendanceData = [];

    public ?int $selectedAcademicYearId = null;
    public ?int $selectedFacultyId = null;
    public ?int $selectedLevelId = null;
    public ?int $selectedProgramId = null;
    public ?int $selectedSchoolClassId = null;
    public ?int $selectedSectionId = null;
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
                    ->afterStateUpdated(fn ($set) => [$set('exam_class_setup_id', null), $set('section_id', null)]),

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

                Select::make('section_id')
                    ->label('Select Section')
                    ->placeholder('Select Section...')
                    ->options(function () {
                        return SectionModel::with('schoolClass')
                            ->where('is_active', true)
                            ->get()
                            ->mapWithKeys(fn ($section) => [
                                $section->id => $section->name . ' (' . ($section->schoolClass->name ?? 'No class') . ')',
                            ]);
                    })
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
        $this->selectedSectionId = $state['section_id'] ?? null;
        $this->selectedExamClassSetupId = $state['exam_class_setup_id'] ?? null;

        if ($this->selectedExamClassSetupId && $this->selectedSchoolClassId) {
            $this->ensureRowsExist();

            $setup = ExamClassSetup::find($this->selectedExamClassSetupId);
            $this->totalAttendanceData = ['total_attendance_days' => $setup?->total_attendance_days];
        }

        $this->loaded = true;
        $this->resetTable();
    }

    protected function ensureRowsExist(): void
    {
        $studentIds = Student::where('class_id', $this->selectedSchoolClassId)
            ->when($this->selectedSectionId, fn ($q) => $q->where('section_id', $this->selectedSectionId))
            ->pluck('id');

        $existingStudentIds = StudentExamAttendance::where('exam_class_setup_id', $this->selectedExamClassSetupId)
            ->pluck('student_id');

        $missingStudentIds = $studentIds->diff($existingStudentIds);

        foreach ($missingStudentIds as $studentId) {
            StudentExamAttendance::create([
                'exam_class_setup_id' => $this->selectedExamClassSetupId,
                'student_id' => $studentId,
                'days_present' => 0,
            ]);
        }
    }

    public function updateTotalAttendance(): void
    {
        if (! $this->selectedExamClassSetupId) {
            return;
        }

        ExamClassSetup::where('id', $this->selectedExamClassSetupId)
            ->update(['total_attendance_days' => $this->totalAttendanceData['total_attendance_days'] ?? 0]);

        Notification::make()
            ->title('Total attendance updated')
            ->success()
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = StudentExamAttendance::query()
                    ->with(['student', 'examClassSetup.exam', 'examClassSetup.academicYear', 'examClassSetup.program', 'examClassSetup.schoolClass']);

                if (! $this->loaded || ! $this->selectedExamClassSetupId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query
                    ->where('exam_class_setup_id', $this->selectedExamClassSetupId)
                    ->when($this->selectedSectionId, fn ($q) => $q->whereHas('student', fn ($sq) => $sq->where('section_id', $this->selectedSectionId)));
            })
            ->columns([
                TextColumn::make('examClassSetup.exam.name')->label('Exam'),
                TextColumn::make('examClassSetup.academicYear.name')->label('Year'),
                TextColumn::make('examClassSetup.program.name')->label('Program'),
                TextColumn::make('examClassSetup.schoolClass.name')->label('Class'),
                TextColumn::make('student.section.name')->label('Section'),
                TextColumn::make('student.roll_no')->label('Roll No')->placeholder('-'),
                TextColumn::make('student.first_name')
                    ->label('Name')
                    ->formatStateUsing(fn ($record) => trim($record->student->first_name . ' ' . $record->student->last_name)),
                TextInputColumn::make('days_present')->label('Attendance')->type('number'),
            ])
            ->emptyStateHeading($this->loaded ? 'No students found' : 'Select filters and click Load');
    }
}