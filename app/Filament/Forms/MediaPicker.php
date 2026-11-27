<?php

namespace App\Filament\Forms;

use App\Support\MediaLibrary;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ViewField;
use Illuminate\Support\Str;

/**
 * Adds "Choose from library" to a photo upload box: pick any photo already on the site
 * instead of uploading the same file again.
 */
class MediaPicker
{
    public static function attach(FileUpload $field): FileUpload
    {
        return $field->hintAction(
            Action::make('chooseFromLibrary')
                ->label('Choose from library')
                ->icon('heroicon-o-photo')
                ->modalHeading('Choose a photo from the library')
                ->modalDescription('Every photo already uploaded to the site. Tap one, then press Use this photo.')
                ->modalSubmitActionLabel('Use this photo')
                ->modalWidth('6xl')
                ->form([
                    ViewField::make('path')->hiddenLabel()->required()
                        ->view('filament.forms.media-picker')
                        ->viewData(fn () => ['images' => MediaLibrary::images(), 'folders' => MediaLibrary::FOLDERS]),
                ])
                ->action(function (array $data, FileUpload $component) {
                    // FileUpload keeps its files keyed by an id; one photo per box.
                    $component->state([(string) Str::uuid() => $data['path']]);
                    $component->callAfterStateUpdated();
                }),
        );
    }
}
