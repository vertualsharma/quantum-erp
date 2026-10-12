<?php

namespace App\Filament\Pages;

use App\Models\AcademicFiscalYear;
use App\Models\ExamClassSetup;
use App\Models\ExamSubjectMarkSetup;
use App\Models\Faculty;
use App\Models\Level;
use App\Models\Program;
use App\Models\SchoolClass;
use App\Models\Section as SectionModel;
use App\Models\Student;
use App\Models\StudentSubjectMark;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubjectwiseMarkEntry extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.subjectwise-mark-entry';

    protected static ?string $navigationLabel = 'Subjectwise';

    public static function getNavigationGroup(): ?string
    {
        return 'Mark Entry';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-pencil-square';
    }

    public ?array $data = [];

    public ?int $selectedAcademicYearId = null;
    public ?int $selectedFacultyId = null;
    public ?int $selectedLevelId = null;
    public ?int $selectedProgramId = null;
    public ?int $selectedSchoolClassId = null;
    public ?int $selectedSectionId = null;
    public ?int $selectedExamClassSetupId = null;
    public ?int $selectedExamSubjectMarkSetupId = null;

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
                        ->live()
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
                    ->afterStateUpdated(fn ($set) => [
                        $set('level_id', null),
                        $set('program_id', null),
                        $set('school_class_id', null),
                        $set('section_id', null),
                        $set('exam_class_setup_id', null),
                        $set('subject_id', null),
                    ]),

                Select::make('level_id')
                    ->label('Select Level')
                    ->placeholder('Select Level...')
                    ->options(fn () => Level::pluck('name', 'id'))
                    ->live()
                    ->reactive()
                    ->preload()
                    ->searchable()
                    ->afterStateUpdated(fn ($set) => [
                        $set('program_id', null),
                        $set('school_class_id', null),
                        $set('section_id', null),
                        $set('exam_class_setup_id', null),
                        $set('subject_id', null),
                    ]),

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
                    ->reactive()
                    ->preload()
                    ->searchable()
                    ->afterStateUpdated(fn ($set) => [
                        $set('school_class_id', null),
                        $set('section_id', null),
                        $set('exam_class_setup_id', null),
                        $set('subject_id', null),
                    ]),

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
                            ->when($get('program_id'), fn ($q) => $q->whereHas('programs', fn ($query) => $query->where('programs.id', $get('program_id'))))
                            ->pluck('name', 'id');
                    })
                    ->live()
                    ->required()
                    ->preload()
                    ->searchable()
                    ->afterStateUpdated(fn ($set) => [
                        $set('exam_class_setup_id', null),
                        $set('section_id', null),
                        $set('subject_id', null),
                    ]),

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
                                $setup->id => ($setup->exam->name ?? 'Exam') . ' - ' . ($setup->exam->exam_type ?? ''),
                            ]);
                    })
                    ->live()
                    ->required()
                    ->preload()
                    ->searchable()
                    ->afterStateUpdated(fn ($set) => $set('subject_id', null)),

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
                    ->live()
                    ->preload()
                    ->searchable(),

                Select::make('subject_id')
                    ->label('Select Subject *')
                    ->placeholder('Select Subject...')
                    ->options(function (Get $get) {
                        $examClassSetupId = $get('exam_class_setup_id');
                        if (! $examClassSetupId) {
                            return [];
                        }

                        return ExamSubjectMarkSetup::with('subject')
                            ->where('exam_class_setup_id', $examClassSetupId)
                            ->get()
                            ->mapWithKeys(fn ($row) => [
                                $row->subject_id => ($row->subject->name ?? 'Subject') . ' - (' . ($row->subject->code ?? 'N/A') . ')',
                            ]);
                    })
                    ->live()
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
        $this->selectedSectionId = $state['section_id'] ?? null;
        $this->selectedExamClassSetupId = $state['exam_class_setup_id'] ?? null;

        $subjectId = $state['subject_id'] ?? null;

        $this->selectedExamSubjectMarkSetupId = null;

        if ($this->selectedExamClassSetupId && $subjectId) {
            $markSetup = ExamSubjectMarkSetup::where('exam_class_setup_id', $this->selectedExamClassSetupId)
                ->where('subject_id', $subjectId)
                ->first();

            $this->selectedExamSubjectMarkSetupId = $markSetup?->id;

            if ($this->selectedExamSubjectMarkSetupId) {
                $this->ensureRowsExist();
            }
        }

        $this->loaded = true;
        $this->resetTable();
    }

    protected function ensureRowsExist(): void
    {
        $studentIds = Student::where('class_id', $this->selectedSchoolClassId)
            ->when($this->selectedSectionId, fn ($q) => $q->where('section_id', $this->selectedSectionId))
            ->pluck('id');

        $existingStudentIds = StudentSubjectMark::where('exam_subject_mark_setup_id', $this->selectedExamSubjectMarkSetupId)
            ->pluck('student_id');

        $missingStudentIds = $studentIds->diff($existingStudentIds);

        foreach ($missingStudentIds as $studentId) {
            StudentSubjectMark::create([
                'exam_subject_mark_setup_id' => $this->selectedExamSubjectMarkSetupId,
                'student_id' => $studentId,
                'theory_obtained' => null,
                'theory_absent' => false,
                'practical_obtained' => null,
                'practical_absent' => false,
            ]);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = StudentSubjectMark::query()
                    ->with(['student', 'examSubjectMarkSetup']);

                if (! $this->loaded || ! $this->selectedExamSubjectMarkSetupId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query
                    ->where('exam_subject_mark_setup_id', $this->selectedExamSubjectMarkSetupId)
                    ->when($this->selectedSectionId, fn ($q) => $q->whereHas('student', fn ($sq) => $sq->where('section_id', $this->selectedSectionId)));
            })
            ->columns([
                TextColumn::make('student.roll_no')->label('Roll No')->placeholder('-'),
                TextColumn::make('student.first_name')
                    ->label('Student Name')
                    ->formatStateUsing(fn ($record) => trim($record->student->first_name . ' ' . $record->student->last_name)),
                TextColumn::make('examSubjectMarkSetup.theory_full_marks')->label('Full Mark (TH)'),
                TextColumn::make('examSubjectMarkSetup.theory_pass_marks')->label('Pass Mark (TH)'),
                TextColumn::make('examSubjectMarkSetup.practical_full_marks')->label('Full Mark (PR)'),
                TextColumn::make('examSubjectMarkSetup.practical_pass_marks')->label('Pass Mark (PR)'),
                TextInputColumn::make('theory_obtained')
                    ->label('Obtained Mark (TH)')
                    ->type('number')
                    ->disabled(fn ($record) => $record->theory_absent)
                    ->rules(fn ($record) => ['nullable', 'numeric', 'min:0', 'max:' . $record->examSubjectMarkSetup->theory_full_marks]),
                CheckboxColumn::make('theory_absent')
                    ->label('Absent (TH)')
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state) {
                            $record->update(['theory_obtained' => null]);
                        }
                    }),
                TextInputColumn::make('practical_obtained')
                    ->label('Obtained Mark (PR)')
                    ->type('number')
                    ->disabled(fn ($record) => $record->practical_absent)
                    ->rules(fn ($record) => ['nullable', 'numeric', 'min:0', 'max:' . $record->examSubjectMarkSetup->practical_full_marks]),
                CheckboxColumn::make('practical_absent')
                    ->label('Absent (PR)')
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state) {
                            $record->update(['practical_obtained' => null]);
                        }
                    }),
            ])
            ->headerActions([
                Action::make('update')
                    ->label('Update')
                    ->icon('heroicon-o-check')
                    ->color('primary')
                    ->visible(fn () => $this->loaded && $this->selectedExamSubjectMarkSetupId)
                    ->action(function () {
                        Notification::make()
                            ->title('Marks updated successfully')
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),
            ])
            ->emptyStateHeading($this->loaded ? 'No students found' : 'Select filters and click Load');
    }
}