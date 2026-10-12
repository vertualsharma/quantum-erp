<?php

namespace App\Filament\Pages;

use App\Models\AcademicFiscalYear;
use App\Models\ExamClassSetup;
use App\Models\Faculty;
use App\Models\Level;
use App\Models\Program;
use App\Models\SchoolClass;
use App\Models\StudentResult;
use App\Services\DerivationService;
use App\Services\ResultCalculationService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProcessResult extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.process-result';

    protected static ?string $navigationLabel = 'Process Result';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Result';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calculator';
    }

    public ?array $data = [];
    public ?int $processedExamClassSetupId = null;

    public function mount(): void
    {
        $this->form->fill([
            'academic_year_id' => AcademicFiscalYear::where('is_current', true)->first()?->id,
            'result_date' => now()->format('Y-m-d'),
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
                        ->afterStateUpdated(fn ($set) => [
                            $set('level_id', null),
                            $set('program_id', null),
                            $set('school_class_id', null),
                            $set('exam_class_setup_id', null),
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
                            $set('exam_class_setup_id', null),
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
                            $set('exam_class_setup_id', null),
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
                        ->reactive()
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
                                    $setup->id => $setup->exam->name . ' - ' . $setup->exam->exam_type,
                                ]);
                        })
                        ->live()
                        ->reactive()
                        ->required()
                        ->preload()
                        ->searchable(),

                    DatePicker::make('result_date')
                        ->label('Result Date')
                        ->required(),
                ]),
            ])
            ->statePath('data');
    }

    public function processResult(): void
    {
        $state = $this->form->getState();

        $examClassSetupId = $state['exam_class_setup_id'] ?? null;

        if (! $examClassSetupId) {
            Notification::make()
                ->title('Please select an exam first')
                ->danger()
                ->send();

            return;
        }

        $derivationService = new DerivationService();
        $derivationService->deriveForClass($examClassSetupId);

        $service = new ResultCalculationService();
        $service->calculateForClass($examClassSetupId);

        $this->processedExamClassSetupId = $examClassSetupId;
        $this->resetTable();

        Notification::make()
            ->title('Result processed successfully.')
            ->success()
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = StudentResult::query()->with(['student']);

                if (! $this->processedExamClassSetupId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where('exam_class_setup_id', $this->processedExamClassSetupId);
            })
            ->columns([
                TextColumn::make('student.roll_no')->label('Roll No')->placeholder('-'),
                TextColumn::make('student.first_name')
                    ->label('Student Name')
                    ->formatStateUsing(fn ($record) => trim($record->student->first_name . ' ' . $record->student->last_name)),
                TextColumn::make('gpa')->label('GPA'),
                TextColumn::make('result')
                    ->label('Result')
                    ->badge()
                    ->color(fn ($state) => $state === 'PASS' ? 'success' : ($state === 'FAIL' ? 'danger' : 'gray')),
                TextColumn::make('rank')->label('Rank')->placeholder('-'),
                TextColumn::make('attendance_present')->label('Attendance')
                    ->formatStateUsing(fn ($record) => $record->attendance_present !== null
                        ? $record->attendance_present . ' / ' . $record->attendance_total
                        : '-'),
            ])
            ->emptyStateHeading('Click Process Result to calculate results');
    }
}