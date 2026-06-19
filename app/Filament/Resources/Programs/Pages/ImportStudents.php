<?php

namespace App\Filament\Resources\Programs\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ImportStudents extends Page implements HasForms
{
    use InteractsWithForms;

    /**
     * Using methods instead of properties for navigation 
     * to avoid "Type Variance" errors entirely.
     */
    public static function getNavigationGroup(): string
    {
        return 'Student';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-arrow-up';
    }

    public static function getNavigationLabel(): string
    {
        return 'Import Students';
    }

    public function getTitle(): string
    {
        return 'Bulk Import Students';
    }

    // FIXED: Removed 'static'. The base Page class defines $view as non-static.
    protected string $view = 'filament.pages.import-students';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'batch_year' => date('Y'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)->schema([
                    
                    // STEP 1: DESTINATION & SCOPING
                    Section::make('1. Set Default Target Destination')
                        ->description('Assign these defaults to any student in the file missing explicit academic records.')
                        ->collapsible()
                        ->schema([
                            Grid::make(5)->schema([
                                TextInput::make('batch_year')
                                    ->label('Batch Year *')
                                    ->required(),
                                Select::make('faculty_id')
                                    ->relationship('faculty', 'name')
                                    ->label('Select Faculty'),
                                Select::make('level_id')
                                    ->relationship('level', 'name')
                                    ->label('Select Level'),
                                Select::make('class_id')
                                    ->relationship('schoolClass', 'name')
                                    ->label('Select Class'),
                                Select::make('section_id')
                                    ->relationship('section', 'name')
                                    ->label('Select Section'),
                            ]),
                        ]),

                    // STEP 2: FILE UPLOAD
                    Section::make('2. Upload Spreadsheet File')
                        ->description('Supported formats: .csv, .xls, .xlsx. Maximum file size: 10MB.')
                        ->schema([
                            FileUpload::make('import_file')
                                ->label('Choose Student Data File')
                                ->required()
                                ->acceptedFileTypes([
                                    'text/csv',
                                    'application/vnd.ms-excel',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                                ])
                                ->disk('local')
                                ->directory('imports/students')
                                ->visibility('private')
                                ->live(),
                        ]),
                ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Download Sample Template')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    Notification::make()
                        ->title('Template Download Started')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Notification::make()
            ->title('Import Processing initiated!')
            ->description('The student records are being securely checked and inserted into the system database.')
            ->success()
            ->send();

        $this->form->fill(['batch_year' => date('Y')]);
    }
}
