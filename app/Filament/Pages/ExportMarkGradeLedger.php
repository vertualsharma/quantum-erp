<?php

namespace App\Filament\Pages;

use App\Models\AcademicFiscalYear;
use App\Models\ExamClassSetup;
use App\Models\Faculty;
use App\Models\Level;
use App\Models\Program;
use App\Models\SchoolClass;
use App\Models\Section as SectionModel;
use App\Services\LedgerExportService;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportMarkGradeLedger extends Page
{
    protected string $view = 'filament.pages.export-mark-grade-ledger';

    protected static ?string $navigationLabel = 'Export Mark / Grade Ledger';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return 'Result';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-arrow-down';
    }

    public ?array $data = [];

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
                                ->when($get('program_id'), fn ($q) => $q->whereHas('programs', fn ($query) => $query->where('programs.id', $get('program_id'))))
                                ->pluck('name', 'id');
                        })
                        ->live()
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
                                    $setup->id => $setup->exam->name . '-' . $setup->exam->exam_type,
                                ]);
                        })
                        ->required()
                        ->preload()
                        ->searchable(),

                    Select::make('ledger_type')
                        ->label('Select Ledger Type *')
                        ->placeholder('Select Ledger Type...')
                        ->options([
                            'mark' => 'Mark',
                            'grade' => 'Grade',
                        ])
                        ->required(),
                ]),
            ])
            ->statePath('data');
    }

    public function export(): StreamedResponse
    {
        $state = $this->form->getState();

        $examClassSetupId = $state['exam_class_setup_id'];
        $sectionId = $state['section_id'] ?? null;
        $type = $state['ledger_type'];

        $service = new LedgerExportService();
        $spreadsheet = $service->build($examClassSetupId, $sectionId, $type);

        $filename = $type === 'grade' ? 'grade-ledger.xlsx' : 'mark-ledger.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename);
    }
}