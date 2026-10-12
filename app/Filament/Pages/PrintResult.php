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
use App\Services\PrintResultService;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrintResult extends Page
{
    protected string $view = 'filament.pages.print-result';

    protected static ?string $navigationLabel = 'Print Result (Student)';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Result';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user';
    }

    public ?array $data = [];

    public ?array $previewData = null;

    public ?string $previewHtml = null;

    public function mount(): void
    {
        $this->form->fill([
            'academic_year_id' => AcademicFiscalYear::where('is_current', true)->first()?->id,
        ]);
    }

    public function form(Schema $schema): Schema
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
                        ->searchable()
                        ->afterStateUpdated(fn ($set) => [$set('exam_class_setup_id', null), $set('student_id', null)]),

                    Select::make('faculty_id')
                        ->label('Select Faculty *')
                        ->placeholder('Select Faculty...')
                        ->options(fn () => Faculty::pluck('name', 'id'))
                        ->live()
                        ->required()
                        ->preload()
                        ->searchable()
                        ->afterStateUpdated(fn ($set) => [$set('level_id', null), $set('program_id', null), $set('school_class_id', null), $set('section_id', null), $set('student_id', null)]),

                    Select::make('level_id')
                        ->label('Select Level')
                        ->placeholder('Select Level...')
                        ->options(fn () => Level::pluck('name', 'id'))
                        ->live()
                        ->reactive()
                        ->preload()
                        ->searchable()
                        ->afterStateUpdated(fn ($set) => [$set('program_id', null), $set('school_class_id', null), $set('section_id', null), $set('student_id', null)]),

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
                        ->afterStateUpdated(fn ($set) => [$set('school_class_id', null), $set('section_id', null), $set('student_id', null)]),

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
                        ->reactive()
                        ->required()
                        ->preload()
                        ->searchable()
                        ->afterStateUpdated(fn ($set) => [$set('exam_class_setup_id', null), $set('section_id', null), $set('student_id', null)]),

                    Select::make('section_id')
                        ->label('Select Section')
                        ->placeholder('Select Section...')
                        ->options(function (Get $get) {
                            $classId = $get('school_class_id');
                            if (! $classId) {
                                return [];
                            }

                            return SectionModel::where('school_class_id', $classId)
                                ->where('is_active', true)
                                ->pluck('name', 'id');
                        })
                        ->live()
                        ->preload()
                        ->searchable()
                        ->afterStateUpdated(fn ($set) => $set('student_id', null)),

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
                        ->afterStateUpdated(fn ($set) => $set('student_id', null)),

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
                        ->preload()
                        ->searchable(),
                ]),
            ])
            ->statePath('data');
    }

    public function preview(): void
    {
        $state = $this->form->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;
        $studentId = $state['student_id'] ?? null;

        if (! $examClassSetupId || ! $studentId) {
            Notification::make()
                ->title('Please select an exam and a student.')
                ->danger()
                ->send();

            return;
        }

        $studentResult = StudentResult::with([
            'student',
            'student.section',
            'subjectResults.subject',
            'examClassSetup.exam',
            'examClassSetup.schoolClass',
            'examClassSetup.academicYear',
        ])
            ->where('exam_class_setup_id', $examClassSetupId)
            ->where('student_id', $studentId)
            ->first();

        if (! $studentResult) {
            Notification::make()
                ->title('No processed result found. Run Process Result first.')
                ->danger()
                ->send();

            return;
        }

        $service = new PrintResultService();
        $data = $service->prepareStudentData($studentResult);

        $this->previewData = $data;
        $this->previewHtml = view('pdf._body', $data)->render();
    }

    public function downloadPdf(): StreamedResponse
    {
        $state = $this->form->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;
        $studentId = $state['student_id'] ?? null;

        if (! $examClassSetupId || ! $studentId) {
            abort(422);
        }

        $studentResult = StudentResult::where('exam_class_setup_id', $examClassSetupId)
            ->where('student_id', $studentId)
            ->first();

        if (! $studentResult) {
            abort(404);
        }

        $service = new PrintResultService();
        $pdfContent = $service->generate($studentResult->id);

        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, 'grade-sheet.pdf');
    }

    public function printDirect(): void
    {
        $state = $this->form->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;
        $studentId = $state['student_id'] ?? null;

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

        $url = route('print.result.single', ['studentResultId' => $studentResult->id]);
        $this->js("window.open('{$url}', '_blank')");
    }

    public function printClassSummary(): StreamedResponse
    {
        $state = $this->form->getState();

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
        $pdfContent = $service->generateForClass($examClassSetupId, $sectionId, 'class-summary');

        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, 'class-summary.pdf');
    }

    public function openClassSummary(): void
    {
        $state = $this->form->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;
        $sectionId = $state['section_id'] ?? null;

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
            'template' => 'class-summary',
        ]));

        $this->js("window.open('{$url}', '_blank')");
    }
}