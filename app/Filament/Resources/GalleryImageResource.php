<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryImageResource\Pages;
use App\Models\GalleryImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GalleryImageResource extends Resource
{
    protected static ?string $model = GalleryImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Gallery';

    public static function form(Form $form): Form
    {
        return $form->schema([
            \App\Filament\Forms\MediaPicker::attach(Forms\Components\FileUpload::make('path')->label('Photo')->image()->disk('public')->directory('gallery')->columnSpanFull()),
            Forms\Components\TextInput::make('caption')->required(),
            Forms\Components\Select::make('category')->options(GalleryImage::CATEGORIES)->required(),
            Forms\Components\Toggle::make('is_active')->label('Show on site')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('path')->disk('public')->label('')->square(),
                Tables\Columns\TextColumn::make('caption')->wrap(),
                Tables\Columns\TextColumn::make('category')->badge()->formatStateUsing(fn ($state) => GalleryImage::CATEGORIES[$state] ?? $state),
                Tables\Columns\ToggleColumn::make('is_active')->label('Shown'),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageGalleryImages::route('/')];
    }
}
