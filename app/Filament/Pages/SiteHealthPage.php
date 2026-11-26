<?php

namespace App\Filament\Pages;

use App\Support\SiteHealth;
use Filament\Pages\Page;

/** Settings → Site health: is checkout, alerting and the server working? */
class SiteHealthPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Site health';

    protected static ?string $title = 'Site health';

    protected static ?string $slug = 'site-health';

    protected static string $view = 'filament.pages.site-health';

    public static function getNavigationBadge(): ?string
    {
        // Shown on every dashboard page: re-check at most once a minute.
        $n = cache()->remember('health:problems', 60, fn () => SiteHealth::problemCount());

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return cache()->remember('health:has-fail', 60, fn () => collect(SiteHealth::checks())->contains('status', SiteHealth::FAIL)) ? 'danger' : 'warning';
    }

    protected function getViewData(): array
    {
        return ['groups' => collect(SiteHealth::checks())->groupBy('group')];
    }
}
