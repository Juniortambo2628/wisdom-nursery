<?php

namespace App\Filament\Pages;

use App\Support\MediaLibrary;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

/**
 * Settings → Photo library: every photo uploaded to the site in one place, with where
 * each is used. Photos added here can be picked in any photo box ("Choose from library").
 */
class MediaLibraryPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Photo library';

    protected static ?string $title = 'Photo library';

    protected static ?string $slug = 'photo-library';

    protected static string $view = 'filament.pages.media-library';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')->label('Upload photos')->icon('heroicon-o-arrow-up-tray')
                ->modalDescription('Photos added here can then be chosen in any photo box with "Choose from library".')
                ->form([
                    FileUpload::make('files')->hiddenLabel()->multiple()->image()->imageEditor()
                        ->disk('public')->directory('library')->maxSize(6144)->maxFiles(20)->required(),
                ])
                ->action(fn (array $data) => Notification::make()->title(count($data['files']).' photo(s) added to the library')->success()->send()),
        ];
    }

    /** Delete a photo that nothing on the site uses (photos in use are protected). */
    public function deletePhoto(string $path): void
    {
        if (array_key_exists($path, MediaLibrary::usage())) {
            Notification::make()->title('This photo is in use, so it was kept')->warning()->send();

            return;
        }
        $images = MediaLibrary::images()->pluck('path');
        abort_unless($images->contains($path), 404);

        $disk = Storage::disk('public');
        $disk->delete([$path, preg_replace('/(\.\w+)$/', '-sm$1', $path)]);
        Notification::make()->title('Photo deleted')->success()->send();
    }

    protected function getViewData(): array
    {
        return [
            'images' => MediaLibrary::images(),
            'usage' => MediaLibrary::usage(),
            'folders' => MediaLibrary::FOLDERS,
        ];
    }
}
