<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->brandName('Wisdom Propagation Nursery')
            ->brandLogo(fn () => view('filament.brand', ['dark' => false]))
            ->darkModeBrandLogo(fn () => view('filament.brand', ['dark' => true]))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('favicon-32x32.png'))
            ->colors([
                'primary' => Color::hex('#2D6A3E'), // leaf
                'success' => Color::hex('#3D9B35'),
                'warning' => Color::hex('#B9822F'), // clay
                'gray' => Color::Stone,
            ])
            ->navigationGroups(['Orders', 'Plants', 'Content', 'Settings'])
            // Self-hosted Inter (same as the public site) instead of a font CDN.
            ->font('Inter Variable', provider: \Filament\FontProviders\LocalFontProvider::class)
            ->renderHook('panels::head.end', fn () => \Illuminate\Support\Facades\Vite::withEntryPoints(['resources/css/admin.css'])->toHtml()
                // On phones, start with the menu closed so the orders show first (Filament defaults to open).
                .'<script>try{if(window.innerWidth<1024)localStorage.setItem("isOpen","false")}catch(e){}</script>')
            ->renderHook('panels::user-menu.before', fn () => view('filament.view-site-link'))
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
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
