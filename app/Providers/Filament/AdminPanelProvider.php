<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Sky,
            ])
            ->navigationGroups([
                NavigationGroup::make('Student'),
                NavigationGroup::make('Setup'),
                NavigationGroup::make('Academic Setup'),
                NavigationGroup::make('Subject Setup'),
                NavigationGroup::make('Exam Setup'),
                NavigationGroup::make('Mark Entry'),
                NavigationGroup::make('Exam Attendance'),
                NavigationGroup::make('Result'),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString('
                    <style>
                        /* 1. Sidebar Background & Width */
                        aside.fi-sidebar {
                            background-color: #1a2332 !important;
                            border-right: 1px solid #283548 !important;
                        }

                        /* 2. Group Headers (e.g. ACADEMIC SETUP) */
                        aside.fi-sidebar .fi-sidebar-group-label button span,
                        aside.fi-sidebar .fi-sidebar-group-label span {
                            color: #64748b !important;
                            font-size: 0.72rem !important;
                            font-weight: 700 !important;
                            letter-spacing: 0.05em !important;
                        }

                        /* 3. Text & Icons Visibility - High Contrast Silver/White */
                        aside.fi-sidebar span,
                        aside.fi-sidebar .fi-sidebar-item-label,
                        aside.fi-sidebar a,
                        aside.fi-sidebar button {
                            color: #cbd5e1 !important; /* High contrast silver-gray */
                            font-size: 0.83rem !important;
                        }

                        aside.fi-sidebar svg {
                            color: #94a3b8 !important;
                        }

                        /* 4. Kill bulky white rounded pill on hover & active */
                        aside.fi-sidebar .fi-sidebar-item-button,
                        aside.fi-sidebar a.fi-sidebar-item-button,
                        aside.fi-sidebar a {
                            border-radius: 0px !important;
                            margin: 1px 0 !important;
                            padding-top: 0.35rem !important;
                            padding-bottom: 0.35rem !important;
                        }

                        /* 5. Sleek Dark Steel Hover State */
                        aside.fi-sidebar .fi-sidebar-item-button:hover,
                        aside.fi-sidebar a:hover {
                            background-color: #2a3b50 !important; /* Crisp dark navy hover */
                            border-radius: 4px !important;
                        }

                        aside.fi-sidebar .fi-sidebar-item-button:hover span,
                        aside.fi-sidebar a:hover span,
                        aside.fi-sidebar .fi-sidebar-item-button:hover svg {
                            color: #ffffff !important;
                        }

                        /* 6. Active Item Highlight */
                        aside.fi-sidebar .fi-sidebar-item-active .fi-sidebar-item-button,
                        aside.fi-sidebar .fi-sidebar-item-active a {
                            background-color: #0f172a !important;
                            border-left: 3px solid #38bdf8 !important;
                            border-radius: 0px 4px 4px 0px !important;
                        }

                        aside.fi-sidebar .fi-sidebar-item-active span,
                        aside.fi-sidebar .fi-sidebar-item-active svg {
                            color: #38bdf8 !important;
                            font-weight: 600 !important;
                        }

                        /* Select dropdown option highlight */
                        .fi-fo-select .fi-select-input-option:not(.fi-disabled):not([disabled]):hover,
                        .fi-fo-select .fi-select-input-option:not(.fi-disabled):not([disabled]):focus-visible,
                        .fi-fo-select .fi-select-input-option:not(.fi-disabled):not([disabled]).fi-selected {
                            background-color: #3b82f6 !important;
                            color: #ffffff !important;
                        }
                    </style>
                ')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}