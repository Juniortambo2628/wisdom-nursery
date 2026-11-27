<?php

namespace App\Filament\Forms;

use App\Support\ImageOptimizer;
use App\Support\MediaLibrary;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;

/**
 * Photo boxes in the dashboard:
 *  - "Choose from library": tap any photo already on the site to use it (no second upload);
 *  - uploads are compressed on save (ImageOptimizer).
 */
class MediaPicker
{
    public static function attach(FileUpload $field): FileUpload
    {
        return ImageOptimizer::onUpload($field)->hintAction(
            Action::make('chooseFromLibrary')
                ->label('Choose from library')
                ->icon('heroicon-o-photo')
                ->modalHeading('Choose a photo from the library')
                ->modalDescription('Tap a photo to use it here.')
                ->modalWidth('6xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                // Rendered in the browser from a small list (not ~120 server-rendered tiles), and a tap
                // calls the action directly, so there is no extra form or validation step to fail.
                ->modalContent(fn () => view('filament.forms.media-picker', [
                    'images' => MediaLibrary::images()->map(fn ($i) => [
                        'path' => $i['path'], 'thumb' => $i['thumb'], 'name' => $i['name'], 'folder' => $i['folder'],
                    ])->values(),
                    'folders' => MediaLibrary::FOLDERS,
                ]))
                ->action(function (array $arguments, FileUpload $component) {
                    $path = $arguments['path'] ?? null;
                    if (! $path || ! MediaLibrary::images()->pluck('path')->contains($path)) {
                        return; // not a photo in the library
                    }
                    // FileUpload keeps its files keyed by an id; one photo per box.
                    $component->state([(string) Str::uuid() => $path]);
                    $component->callAfterStateUpdated();
                }),
        );
    }
}
