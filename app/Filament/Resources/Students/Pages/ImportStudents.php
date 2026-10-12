<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Imports\StudentImport;
use App\Models\AcademicFiscalYear;
use App\Models\BatchYear;
use App\Models\Faculty;
use App\Models\SchoolClass;
use App\Models\Section as SectionModel;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportStudents extends Page
{
    protected static string $resource = StudentResource::class;

    protected string $view = 'filament.resources.students.pages.import-students';

    public ?array $data = [];

    public array $importErrors = [];
    public ?int $importedCount = null;
    public ?int $skippedCount = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academic_year_id')
                    ->label('Academic Year *')
                    ->options(fn () => AcademicFiscalYear::pluck('name', 'id'))
                    ->default(fn () => AcademicFiscalYear::where('is_current', true)->first()?->id)
                    ->required()
                    ->preload()
                    ->searchable(),

                Select::make('batch_year_id')
                    ->label('Batch Year *')
                    ->options(fn () => BatchYear::pluck('nepali_year', 'id'))
                    ->default(fn () => BatchYear::where('is_current', true)->first()?->id)
                    ->required()
                    ->preload()
                    ->searchable(),

                Select::make('faculty_id')
                    ->label('Faculty *')
                    ->options(fn () => Faculty::pluck('name', 'id'))
                    ->preload()
                    ->live()
                    ->searchable()
                    ->required()
                    ->afterStateUpdated(fn (Set $set) => $set('class_id', null)),

                Select::make('class_id')
                    ->label('Class *')
                    ->options(function (Get $get) {
                        $facultyId = $get('faculty_id');

                        if (! $facultyId) {
                            return [];
                        }

                        return SchoolClass::where('faculty_id', $facultyId)->pluck('name', 'id')->toArray();
                    })
                    ->live()
                    ->preload()
                    ->searchable()
                    ->required()
                    ->afterStateUpdated(fn (Set $set) => $set('section_id', null)),

                Select::make('section_id')
                    ->label('Section')
                    ->options(function (Get $get) {
                        $classId = $get('class_id');

                        if (! $classId) {
                            return [];
                        }

                        return SectionModel::where('school_class_id', $classId)->pluck('name', 'id')->toArray();
                    })
                    ->preload()
                    ->searchable(),

                FileUpload::make('file')
                    ->label('Student Excel File')
                    ->disk('local')
                    ->directory('imports/students')
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function importAction(): Action
    {
        return Action::make('import')
            ->label('Import Students')
            ->color('success')
            ->action(function () {
                $state = $this->form->getState();

                $storedFile = $state['file'];
                $filename = is_array($storedFile) ? array_key_first($storedFile) : $storedFile;
                $path = Storage::disk('local')->path($filename);

                $import = new StudentImport(
                    academicYearId: $state['academic_year_id'],
                    batchYearId: $state['batch_year_id'],
                    facultyId: $state['faculty_id'],
                    classId: $state['class_id'],
                    sectionId: $state['section_id'] ?? null,
                );

                Excel::import($import, $path);

                $this->importedCount = $import->imported;
                $this->skippedCount = $import->skipped;
                $this->importErrors = $import->errors;

                Notification::make()
                    ->title("Imported {$import->imported} students, skipped {$import->skipped}")
                    ->color($import->skipped > 0 ? 'warning' : 'success')
                    ->send();
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->importAction(),
        ];
    }
}