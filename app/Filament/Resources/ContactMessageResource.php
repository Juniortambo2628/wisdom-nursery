<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Messages';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->disabled(),
            Forms\Components\TextInput::make('phone')->disabled(),
            Forms\Components\Textarea::make('message')->disabled()->rows(5)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\IconColumn::make('read_at')->label('')->boolean()->trueIcon('heroicon-o-envelope-open')->falseIcon('heroicon-s-envelope')->falseColor('warning'),
                Tables\Columns\TextColumn::make('name')->searchable()->description(fn ($record) => \App\Support\Phone::display($record->phone)),
                Tables\Columns\TextColumn::make('message')->wrap()->limit(120),
                Tables\Columns\TextColumn::make('created_at')->since()->label('When'),
            ])
            ->filters([])
            ->actions([
    Tables\Actions\ViewAction::make()->after(fn ($record) => $record->read_at ?? $record->update(['read_at' => now()])),
    Tables\Actions\Action::make('call')->icon('heroicon-o-phone')->color('gray')->iconButton()->url(fn ($record) => 'tel:+'.$record->phone),
    Tables\Actions\DeleteAction::make()->iconButton(),
]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $n = ContactMessage::whereNull('read_at')->count();

        return $n ? (string) $n : null;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageContactMessages::route('/')];
    }
}
