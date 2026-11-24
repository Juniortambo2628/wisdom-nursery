<?php

namespace App\Filament\Resources\PlantResource\Pages;

use App\Filament\Resources\PlantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlants extends ListRecords
{
    protected static string $resource = PlantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('whatsapp_catalogue')
                ->label('Export for WhatsApp catalogue')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => response()->streamDownload(
                    fn () => print(\App\Support\CatalogueExport::csv()),
                    'wisdom-propagation-nursery-catalogue.csv',
                    ['Content-Type' => 'text/csv'],
                )),
            Actions\CreateAction::make(),
        ];
    }
}
