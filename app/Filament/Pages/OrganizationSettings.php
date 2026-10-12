<?php

namespace App\Filament\Pages;

use App\Models\SchoolSetting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrganizationSettings extends Page
{
    protected string $view = 'filament.pages.organization-settings';

    protected static ?string $navigationLabel = 'Organization Settings';

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function getNavigationSort(): ?int
    {
        return -100;
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-building-office-2';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $setting = SchoolSetting::firstOrCreate(['id' => 1]);

        $this->form->fill($setting->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Section::make('Organization Information')->schema([
                        TextInput::make('organization_name')->label('Organization Name'),
                        TextInput::make('pan_number')->label('PAN Number'),
                        TextInput::make('organization_code')->label('Organization Code'),
                        TextInput::make('country')->label('Select Country'),
                        TextInput::make('state')->label('Select State'),
                        TextInput::make('district')->label('Select District'),
                        TextInput::make('municipality')->label('Select Municipality'),
                        TextInput::make('ward_number')->label('Ward Number'),
                        TextInput::make('street_address')->label('Street Address'),
                        TextInput::make('google_plus_code')->label('Google Plus Code'),
                        TextInput::make('postcode')->label('Postcode / Zip / PIN'),
                    ]),

                    Section::make('Contact Information')->schema([
                        TextInput::make('contact_number')->label('Contact Number'),
                        TextInput::make('short_address')
                            ->label('Short Address')
                            ->helperText('This address will be used in reports, invoices, due bills, admit cards, and other documents.'),
                        TextInput::make('email')->label('Email')->email(),
                        TextInput::make('website')->label('Website')->url(),
                        TextInput::make('school_slogan')->label('School Slogan'),
                        DatePicker::make('established_date')->label('Established Date'),
                        TextInput::make('longitude')->label('Longitude'),
                        TextInput::make('latitude')->label('Latitude'),
                    ]),

                    Section::make('Social Settings')->schema([
                        TextInput::make('facebook_link')->label('Facebook Link')->url(),
                        TextInput::make('twitter_link')->label('Twitter Link')->url(),
                        TextInput::make('instagram_link')->label('Instagram Link')->url(),
                        TextInput::make('student_app_link')->label('Student Android App Link')->url(),
                        TextInput::make('teacher_app_link')->label('Teacher Android App Link')->url(),
                        TextInput::make('guardian_app_link')->label('Guardian Android App Link')->url(),
                        TextInput::make('youtube_link')->label('Youtube Channel Link')->url(),
                    ]),
                ]),

                Grid::make(4)->schema([
                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->disk('public')
                        ->directory('school/logo')
                        ->helperText('Size ratio must be 100px width and 100px height.'),

                    FileUpload::make('favicon_path')
                        ->label('Favicon Upload')
                        ->image()
                        ->disk('public')
                        ->directory('school/favicon'),

                    FileUpload::make('principal_sign_path')
                        ->label('Principal/Campus Chief Sign Upload')
                        ->image()
                        ->disk('public')
                        ->directory('school/signatures')
                        ->helperText('Size ratio must be 100px width and 50px height.'),

                    FileUpload::make('seal_path')
                        ->label('Seal/Stamp Upload')
                        ->image()
                        ->disk('public')
                        ->directory('school/seal')
                        ->helperText('Size ratio must be 100px width and 50px height.'),
                ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SchoolSetting::where('id', 1)->update($state);

        Notification::make()
            ->title('Organization settings saved successfully.')
            ->success()
            ->send();
    }
}