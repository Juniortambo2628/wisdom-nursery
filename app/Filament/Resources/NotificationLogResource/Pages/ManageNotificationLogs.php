<?php

namespace App\Filament\Resources\NotificationLogResource\Pages;

use App\Filament\Resources\NotificationLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNotificationLogs extends ManageRecords
{
    protected static string $resource = NotificationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
