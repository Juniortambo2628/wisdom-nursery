<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationLogResource\Pages;
use App\Models\NotificationLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationLogResource extends Resource
{
    protected static ?string $model = NotificationLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Alert log';

    public static function form(Form $form): Form
    {
        return $form->schema([

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')->poll('30s')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime('j M, H:i')->label('When')->sortable(),
                Tables\Columns\TextColumn::make('event')->formatStateUsing(fn ($state) => str_replace('_', ' ', ucfirst($state))),
                Tables\Columns\TextColumn::make('channel')->badge(),
                Tables\Columns\TextColumn::make('recipient')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'sent' => 'success', 'failed' => 'danger', 'skipped' => 'warning', default => 'gray' }),
                Tables\Columns\TextColumn::make('error')->wrap()->placeholder('-')->toggleable(),
                Tables\Columns\TextColumn::make('body')->wrap()->limit(80)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
    Tables\Filters\SelectFilter::make('status')->options(['sent' => 'Sent', 'failed' => 'Failed', 'skipped' => 'Skipped', 'logged' => 'Logged only']),
    Tables\Filters\SelectFilter::make('channel')->options(['sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'email' => 'Email']),
])
            ->actions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageNotificationLogs::route('/')];
    }
}
