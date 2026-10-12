<?php

namespace App\Filament\Pages;

use App\Models\AcademicFiscalYear;
use App\Models\ExamClassSetup;
use App\Models\Faculty;
use App\Models\Level;
use App\Models\Program;
use App\Models\SchoolClass;
use App\Models\Section as SectionModel;
use App\Services\PrintResultService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrintResultWholeClass extends Page
{
    protected string $view = 'filament.pages.print-result-whole-class';

    protected static ?string $navigationLabel = 'Print Result (Whole Class)';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Result';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-printer';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'academic_year_id' => AcademicFiscalYear::where('is_current', true)->first()?->id,
            'issue_date' => now()->format('Y-m-d'),
            'result_template' => 'grade-sheet-batch',
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
                        ->afterStateUpdated(fn ($set) => $set('exam_class_setup_id', null)),

                    Select::make('faculty_id')
                        ->label('Select Faculty *')
                        ->placeholder('Select Faculty...')
                        ->options(fn () => Faculty::pluck('name', 'id'))
                        ->live()
                        ->required()
                        ->preload()
                        ->searchable()
                        ->afterStateUpdated(fn ($set) => [$set('level_id', null), $set('program_id', null), $set('school_class_id', null), $set('section_id', null), $set('exam_class_setup_id', null)]),

                    Select::make('level_id')
                        ->label('Select Level')
                        ->placeholder('Select Level...')
                        ->options(fn () => Level::pluck('name', 'id'))
                        ->live()
                        ->reactive()
                        ->preload()
                        ->searchable()
                        ->afterStateUpdated(fn ($set) => [$set('program_id', null), $set('school_class_id', null), $set('section_id', null), $set('exam_class_setup_id', null)]),

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
                        ->afterStateUpdated(fn ($set) => [$set('school_class_id', null), $set('section_id', null), $set('exam_class_setup_id', null)]),

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
                        ->afterStateUpdated(fn ($set) => [$set('exam_class_setup_id', null), $set('section_id', null)]),

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
                        ->searchable(),

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

                    DatePicker::make('issue_date')
                        ->label('Issue Date *')
                        ->required()
                        ->default(now()),
                ]),
            ])
            ->statePath('data');
    }

    public function print(): StreamedResponse
    {
        $state = $this->form->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;
        $sectionId = $state['section_id'] ?? null;
        $template = $state['result_template'] ?? 'grade-sheet-batch';

        if (! $examClassSetupId) {
            Notification::make()
                ->title('Please select an exam.')
                ->danger()
                ->send();

            abort(422);
        }

        $service = new PrintResultService();
        $pdfContent = $service->generateForClass($examClassSetupId, $sectionId, $template);

        $fileName = match ($template) {
            'class-summary' => 'class-summary.pdf',
            default => 'whole-class-grade-sheets.pdf',
        };

        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $fileName);
    }

    public function openPrint(): void
    {
        $state = $this->form->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;
        $sectionId = $state['section_id'] ?? null;
        $template = $state['result_template'] ?? 'grade-sheet-batch';

        if (! $examClassSetupId) {
            Notification::make()
                ->title('Please select an exam.')
                ->danger()
                ->send();

            return;
        }

        $url = route('print.result.class', array_filter([
            'examClassSetupId' => $examClassSetupId,
            'section_id' => $sectionId,
            'template' => $template,
        ]));

        $this->js("window.open('{$url}', '_blank')");
    }
}