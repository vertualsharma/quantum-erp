<?php

namespace App\Filament\Pages;

use App\Models\AcademicFiscalYear;
use App\Models\Exam;
use App\Models\ExamClassSetup;
use App\Models\Faculty;
use App\Models\Level;
use App\Models\Program;
use App\Models\SchoolClass;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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

class ProgramClassExamSetup extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.program-class-exam-setup';

    protected static ?string $navigationLabel = 'Program Class Exam Setup';

    public static function getNavigationGroup(): ?string
    {
        return 'Exam Setup';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-adjustments-horizontal';
    }

    public ?array $data = [];

    public ?int $selectedAcademicYearId = null;
    public ?int $selectedFacultyId = null;
    public ?int $selectedLevelId = null;
    public ?int $selectedProgramId = null;
    public ?int $selectedSchoolClassId = null;

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
                        ->afterStateUpdated(fn (Get $get, $set) => $set('level_id', null)),

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
                            $levelId = $get('level_id');

                            if (! $facultyId) {
                                return [];
                            }

                            return Program::where('faculty_id', $facultyId)
                                ->when($levelId, fn ($q) => $q->where('level_id', $levelId))
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

        $this->loaded = true;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = ExamClassSetup::query()->with(['exam', 'academicYear']);

                if (! $this->loaded || ! $this->selectedSchoolClassId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query
                    ->where('academic_year_id', $this->selectedAcademicYearId)
                    ->where('faculty_id', $this->selectedFacultyId)
                    ->when($this->selectedLevelId, fn ($q) => $q->where('level_id', $this->selectedLevelId))
                    ->when($this->selectedProgramId, fn ($q) => $q->where('program_id', $this->selectedProgramId))
                    ->where('school_class_id', $this->selectedSchoolClassId);
            })
            ->columns([
                TextColumn::make('id')->label('#')->rowIndex(),
                TextColumn::make('exam.name')->label('Name'),
                TextColumn::make('exam.exam_type')->label('Type'),
                TextInputColumn::make('position')->label('Position')->type('number'),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                TextColumn::make('mark_entry_locked')
                    ->label('Mark Entry')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Locked' : 'Unlocked')
                    ->color(fn ($state) => $state ? 'danger' : 'info'),
                TextColumn::make('academicYear.name')->label('Fiscal Year'),
            ])
            ->headerActions([
                Action::make('addExam')
                    ->label('Add Exam')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->visible(fn () => $this->loaded)
                    ->form([
                        Select::make('exam_id')
                            ->label('Select Exam')
                            ->options(function () {
                                $alreadyAssigned = ExamClassSetup::where('academic_year_id', $this->selectedAcademicYearId)
                                    ->where('school_class_id', $this->selectedSchoolClassId)
                                    ->pluck('exam_id');

                                return Exam::whereNotIn('id', $alreadyAssigned)->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (array $data) {
                        ExamClassSetup::create([
                            'exam_id' => $data['exam_id'],
                            'academic_year_id' => $this->selectedAcademicYearId,
                            'faculty_id' => $this->selectedFacultyId,
                            'level_id' => $this->selectedLevelId,
                            'program_id' => $this->selectedProgramId,
                            'school_class_id' => $this->selectedSchoolClassId,
                            'is_active' => true,
                            'mark_entry_locked' => false,
                        ]);

                        $this->resetTable();
                    }),
            ])
            ->recordActions([
                Action::make('toggleActive')
                    ->label(fn (ExamClassSetup $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->color(fn (ExamClassSetup $record) => $record->is_active ? 'danger' : 'success')
                    ->icon(fn (ExamClassSetup $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->action(fn (ExamClassSetup $record) => $record->update(['is_active' => ! $record->is_active])),

                Action::make('toggleLock')
                    ->label(fn (ExamClassSetup $record) => $record->mark_entry_locked ? 'Unlock Mark Entry' : 'Lock Mark Entry')
                    ->color(fn (ExamClassSetup $record) => $record->mark_entry_locked ? 'success' : 'warning')
                    ->icon(fn (ExamClassSetup $record) => $record->mark_entry_locked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->action(fn (ExamClassSetup $record) => $record->update(['mark_entry_locked' => ! $record->mark_entry_locked])),

                Action::make('editFiscalYear')
                    ->label('Edit Fiscal Year')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->form([
                        Select::make('academic_year_id')
                            ->label('Fiscal Year')
                            ->options(fn () => AcademicFiscalYear::pluck('name', 'id'))
                            ->required()
                            ->preload()
                            ->searchable(),
                    ])
                    ->fillForm(fn (ExamClassSetup $record) => ['academic_year_id' => $record->academic_year_id])
                    ->action(fn (ExamClassSetup $record, array $data) => $record->update(['academic_year_id' => $data['academic_year_id']])),
            ])
            ->emptyStateHeading($this->loaded ? 'No exams assigned yet' : 'Select filters and click Load');
    }
}