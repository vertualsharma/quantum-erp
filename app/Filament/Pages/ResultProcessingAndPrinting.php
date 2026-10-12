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
use App\Models\StudentResult;
use App\Services\DerivationService;
use App\Services\PrintResultService;
use App\Services\ResultCalculationService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultProcessingAndPrinting extends Page
{
    protected string $view = 'filament.pages.result-processing-and-printing';

    protected static ?string $navigationLabel = 'Result Processing & Printing (Unified)';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Result';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public ?array $processData = [];
    public ?array $printWholeClassData = [];
    public ?array $printStudentWiseData = [];

    public function mount(): void
    {
        $currentYearId = AcademicFiscalYear::where('is_current', true)->first()?->id;

        $this->processData = [
            'academic_year_id' => $currentYearId,
            'result_date' => now()->format('Y-m-d'),
        ];

        $this->printWholeClassData = [
            'academic_year_id' => $currentYearId,
            'issue_date' => now()->format('Y-m-d'),
            'result_template' => 'grade-sheet-batch',
        ];

        $this->printStudentWiseData = [
            'academic_year_id' => $currentYearId,
            'result_template' => 'grade-sheet',
        ];
    }

    protected function facultyField(): Select
    {
        return Select::make('faculty_id')
            ->label('Select Faculty *')
            ->placeholder('Select Faculty...')
            ->options(fn () => Faculty::pluck('name', 'id'))
            ->live()
            ->reactive()
            ->required()
            ->preload()
            ->searchable()
            ->afterStateUpdated(fn ($set) => [
                $set('level_id', null),
                $set('program_id', null),
                $set('school_class_id', null),
                $set('section_id', null),
                $set('exam_class_setup_id', null),
                $set('student_id', null),
            ]);
    }

    protected function levelField(): Select
    {
        return Select::make('level_id')
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
                $set('student_id', null),
            ]);
    }

    protected function programField(): Select
    {
        return Select::make('program_id')
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
            ->searchable()
            ->afterStateUpdated(fn ($set) => [
                $set('school_class_id', null),
                $set('section_id', null),
                $set('exam_class_setup_id', null),
                $set('student_id', null),
            ]);
    }

    protected function classField(array $resetFields = []): Select
    {
        return Select::make('school_class_id')
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
                    ->when($get('academic_year_id'), fn ($q) => $q->whereHas('examClassSetups', fn ($query) => $query->where('academic_year_id', $get('academic_year_id'))))
                    ->pluck('name', 'id');
            })
            ->live()
            ->reactive()
            ->required()
            ->searchable()
            ->afterStateUpdated(function ($set) use ($resetFields) {
                foreach ($resetFields as $field) {
                    $set($field, null);
                }

                $set('student_id', null);
            });
    }

    protected function sectionField(): Select
    {
        return Select::make('section_id')
            ->label('Select Section')
            ->placeholder('Select Section...')
            ->options(function (Get $get) {
                $classId = $get('school_class_id');
                if (! $classId) {
                    return [];
                }

                return SectionModel::where('school_class_id', $classId)
                    ->where('is_active', true)
                    ->when($get('program_id'), fn ($q) => $q->where('program_id', $get('program_id')))
                    ->orderBy('name')
                    ->pluck('name', 'id');
            })
            ->live()
            ->reactive()
            ->searchable()
            ->afterStateUpdated(fn ($set) => $set('student_id', null));
    }

    protected function examField(): Select
    {
        return Select::make('exam_class_setup_id')
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
                        $setup->id => $setup->exam->name . ' - ' . $setup->exam->exam_type,
                    ]);
            })
            ->live()
            ->reactive()
            ->required()
            ->searchable()
            ->afterStateUpdated(fn ($set) => $set('student_id', null));
    }

    protected function yearField(): Select
    {
        return Select::make('academic_year_id')
            ->label('Select Year *')
            ->placeholder('Select Year...')
            ->options(fn () => AcademicFiscalYear::pluck('name', 'id'))
            ->default(fn () => AcademicFiscalYear::where('is_current', true)->first()?->id)
            ->required()
            ->live()
            ->reactive()
            ->preload()
            ->searchable()
            ->afterStateUpdated(fn ($set) => $set('exam_class_setup_id', null));
    }

    // ---------- SECTION 1: Process Result ----------

    public function processForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)->schema([
                    $this->yearField(),
                    $this->facultyField(),
                    $this->levelField(),
                    $this->programField(),
                    $this->classField(['exam_class_setup_id']),
                    $this->examField(),
                    DatePicker::make('result_date')->label('Result Date')->required(),
                ]),
            ])
            ->statePath('processData');
    }

    public function processResult(): void
    {
        $state = $this->processForm->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;

        if (! $examClassSetupId) {
            Notification::make()->title('Please select an exam first')->danger()->send();

            return;
        }

        (new DerivationService())->deriveForClass($examClassSetupId);
        (new ResultCalculationService())->calculateForClass($examClassSetupId);

        Notification::make()->title('Result processed successfully.')->success()->send();
    }

    // ---------- SECTION 2: Print Result (Whole Class) ----------

    public function printWholeClassForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)->schema([
                    $this->yearField(),
                    $this->facultyField(),
                    $this->levelField(),
                    $this->programField(),
                    $this->classField(['exam_class_setup_id', 'section_id']),
                    $this->sectionField(),
                    $this->examField(),
                    Select::make('result_template')
                        ->label('Select Result Template')
                        ->options([
                            'grade-sheet-batch' => 'Grade Sheet Template',
                            'class-summary' => 'Class Summary Template',
                        ])
                        ->default('grade-sheet-batch')
                        ->required()
                        ->live()
                        ->preload()
                        ->searchable(),
                    DatePicker::make('issue_date')->label('Issue Date')->required(),
                ]),
            ])
            ->statePath('printWholeClassData');
    }

    public function printWholeClass(): StreamedResponse
    {
        $state = $this->printWholeClassForm->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;
        $sectionId = $state['section_id'] ?? null;

        if (! $examClassSetupId) {
            Notification::make()->title('Please select an exam first.')->danger()->send();

            abort(422);
        }

        $hasResults = StudentResult::where('exam_class_setup_id', $examClassSetupId)->exists();

        if (! $hasResults) {
            Notification::make()->title('No processed results found. Run Process Result first.')->danger()->send();

            abort(404);
        }

        $service = new PrintResultService();
        $pdfContent = $service->generateForClass(
            $examClassSetupId,
            $sectionId,
            $state['result_template'] ?? 'grade-sheet-batch'
        );

        $fileName = match ($state['result_template'] ?? 'grade-sheet-batch') {
            'class-summary' => 'class-summary.pdf',
            default => 'grade-sheets-whole-class.pdf',
        };

        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $fileName);
    }

    public function openPrintWholeClass(): void
    {
        $state = $this->printWholeClassForm->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;
        $sectionId = $state['section_id'] ?? null;
        $template = $state['result_template'] ?? 'grade-sheet-batch';

        if (! $examClassSetupId) {
            Notification::make()->title('Please select an exam first.')->danger()->send();

            return;
        }

        $hasResults = StudentResult::where('exam_class_setup_id', $examClassSetupId)->exists();

        if (! $hasResults) {
            Notification::make()->title('No processed results found. Run Process Result first.')->danger()->send();

            return;
        }

        $url = route('print.result.class', array_filter([
            'examClassSetupId' => $examClassSetupId,
            'section_id' => $sectionId,
            'template' => $template,
        ]));

        $this->js("window.open('{$url}', '_blank')");
    }

    // ---------- SECTION 3: Print Result (Student-wise) ----------

    public function printStudentWiseForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)->schema([
                    $this->yearField(),
                    $this->facultyField(),
                    $this->levelField(),
                    $this->programField(),
                    $this->classField(['exam_class_setup_id', 'section_id', 'student_id']),
                    $this->sectionField()->afterStateUpdated(fn ($set) => $set('student_id', null)),
                    $this->examField()->afterStateUpdated(fn ($set) => $set('student_id', null)),
                    Select::make('student_id')
                        ->label('Select Student *')
                        ->placeholder('Select Student...')
                        ->options(function (Get $get) {
                            $classId = $get('school_class_id');
                            $sectionId = $get('section_id');

                            if (! $classId) {
                                return [];
                            }

                            return Student::where('class_id', $classId)
                                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                                ->get()
                                ->mapWithKeys(fn ($s) => [$s->id => trim($s->first_name . ' ' . $s->last_name)]);
                        })
                        ->required()
                        ->reactive()
                        ->searchable(),
                    Select::make('result_template')
                        ->label('Select Result Template')
                        ->options([
                            'grade-sheet' => 'Grade Sheet Template',
                            'class-summary' => 'Class Summary Template',
                        ])
                        ->default('grade-sheet')
                        ->required()
                        ->live()
                        ->preload()
                        ->searchable(),
                ]),
            ])
            ->statePath('printStudentWiseData');
    }

    public function printStudentWise(): StreamedResponse
    {
        $state = $this->printStudentWiseForm->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;
        $studentId = $state['student_id'] ?? null;

        if (! $examClassSetupId || ! $studentId) {
            Notification::make()->title('Please select an exam and a student.')->danger()->send();

            abort(422);
        }

        $studentResult = StudentResult::where('exam_class_setup_id', $examClassSetupId)
            ->where('student_id', $studentId)
            ->first();

        if (! $studentResult) {
            Notification::make()->title('No processed result found. Run Process Result first.')->danger()->send();

            abort(404);
        }

        $service = new PrintResultService();
        $pdfContent = $service->generate(
            $studentResult->id,
            $state['result_template'] ?? 'grade-sheet'
        );

        $fileName = match ($state['result_template'] ?? 'grade-sheet') {
            'class-summary' => 'class-summary.pdf',
            default => 'grade-sheet.pdf',
        };

        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $fileName);
    }

    public function openPrintStudentWise(): void
    {
        $state = $this->printStudentWiseForm->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;
        $studentId = $state['student_id'] ?? null;
        $template = $state['result_template'] ?? 'grade-sheet';

        if (! $examClassSetupId || ! $studentId) {
            Notification::make()->title('Please select an exam and a student.')->danger()->send();

            return;
        }

        $studentResult = StudentResult::where('exam_class_setup_id', $examClassSetupId)
            ->where('student_id', $studentId)
            ->first();

        if (! $studentResult) {
            Notification::make()->title('No processed result found. Run Process Result first.')->danger()->send();

            return;
        }

        $url = route('print.result.single', [
            'studentResultId' => $studentResult->id,
            'template' => $template,
        ]);

        $this->js("window.open('{$url}', '_blank')");
    }
}