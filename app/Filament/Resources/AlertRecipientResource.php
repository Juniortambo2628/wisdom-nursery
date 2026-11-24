<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlertRecipientResource\Pages;
use App\Models\AlertRecipient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlertRecipientResource extends Resource
{
    protected static ?string $model = AlertRecipient::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('phone')->tel()->rule(new \App\Rules\KenyanPhone)->requiredIf('channels', ['sms'])->dehydrateStateUsing(fn ($state) => \App\Support\Phone::normalise($state))->formatStateUsing(fn ($state) => \App\Support\Phone::display($state))->helperText('Used for SMS and WhatsApp.'),
            Forms\Components\TextInput::make('email')->email(),
            Forms\Components\CheckboxList::make('channels')->options(AlertRecipient::CHANNELS)->required()->columns(3),
            Forms\Components\Toggle::make('is_active')->label('Receives alerts')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('phone')->formatStateUsing(fn ($state) => \App\Support\Phone::display($state))->placeholder('-'),
                Tables\Columns\TextColumn::make('email')->placeholder('-'),
                Tables\Columns\TextColumn::make('channels')->badge()->formatStateUsing(fn ($state) => AlertRecipient::CHANNELS[$state] ?? $state),
                Tables\Columns\ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([])
            ->actions([
    Tables\Actions\Action::make('test')->label('Send test')->icon('heroicon-o-paper-airplane')->color('gray')
        ->requiresConfirmation()->modalDescription('Sends a short test message on each of this person\'s channels. SMS costs about KSh 1.')
        ->action(function (AlertRecipient $r) {
            $text = 'Test alert from the Wisdom Nursery website. Order alerts will arrive like this.';
            foreach ($r->channels ?? [] as $ch) {
                $to = $ch === 'email' ? $r->email : $r->phone;
                if ($to) {
                    \App\Jobs\SendAlert::dispatch($ch, $to, 'test', $text, $r, config('nursery.whatsapp_alerts.templates.order_paid'), ['TEST', 'KSh 0', '-', $r->name, 'Test', 'Test', url('/admin')], 'Test alert');
                }
            }
            \Filament\Notifications\Notification::make()->title('Test queued: check the alert log in a minute')->success()->send();
        }),
    Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make(),
]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageAlertRecipients::route('/')];
    }
}
