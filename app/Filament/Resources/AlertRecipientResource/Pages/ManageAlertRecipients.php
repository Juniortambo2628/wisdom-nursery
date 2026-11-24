<?php

namespace App\Filament\Resources\AlertRecipientResource\Pages;

use App\Filament\Resources\AlertRecipientResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAlertRecipients extends ManageRecords
{
    protected static string $resource = AlertRecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
