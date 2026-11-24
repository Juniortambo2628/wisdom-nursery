<?php

namespace App\Filament\Resources;

use App\Enums\QuoteStatus;
use App\Filament\Resources\QuoteRequestResource\Pages;
use App\Models\QuoteRequest;
use App\Support\Phone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Quote requests';

    private static function statusOptions(): array
    {
        return [QuoteStatus::New->value => 'New', QuoteStatus::Replied->value => 'Replied', QuoteStatus::Won->value => 'Won', QuoteStatus::Lost->value => 'Lost'];
    }

    public static function getNavigationBadge(): ?string
    {
        $n = QuoteRequest::where('status', QuoteStatus::New)->count();

        return $n ? (string) $n : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('who')->label('From')
                ->content(fn (QuoteRequest $r) => trim($r->name.($r->organisation ? " · {$r->organisation}" : '')).' · '.Phone::display($r->phone).($r->email ? " · {$r->email}" : '')),
            Forms\Components\Placeholder::make('items_list')->label('Plants')
                ->content(fn (QuoteRequest $r) => $r->items->map(fn ($i) => number_format($i->quantity).' × '.$i->plant_name)->implode(', ')),
            Forms\Components\Placeholder::make('location')->label('Deliver to')->content(fn (QuoteRequest $r) => $r->location ?: '-'),
            Forms\Components\Placeholder::make('notes')->content(fn (QuoteRequest $r) => $r->notes ?: '-'),
            Forms\Components\Select::make('status')->options(self::statusOptions())->required(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('items'))
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Quote')->formatStateUsing(fn ($state) => "Q-{$state}")->weight('bold')
                    ->description(fn (QuoteRequest $r) => $r->created_at->diffForHumans()),
                Tables\Columns\TextColumn::make('name')->searchable()
                    ->description(fn (QuoteRequest $r) => trim(($r->organisation ? $r->organisation.' · ' : '').Phone::display($r->phone))),
                Tables\Columns\TextColumn::make('items_summary')->label('Plants')->wrap()
                    ->state(fn (QuoteRequest $r) => $r->items->map(fn ($i) => number_format($i->quantity).' × '.$i->plant_name)->implode(', ')),
                Tables\Columns\TextColumn::make('location')->placeholder('-')->toggleable(),
                Tables\Columns\SelectColumn::make('status')->options(self::statusOptions())->selectablePlaceholder(false),
            ])
            ->filters([Tables\Filters\SelectFilter::make('status')->options(self::statusOptions())])
            ->actions([
                Tables\Actions\EditAction::make()->label('Open'),
                Tables\Actions\Action::make('call')->icon('heroicon-o-phone')->color('gray')->iconButton()->url(fn (QuoteRequest $r) => 'tel:+'.$r->phone),
                Tables\Actions\Action::make('whatsapp')->icon('heroicon-o-chat-bubble-left-right')->color('success')->iconButton()
                    ->url(fn (QuoteRequest $r) => 'https://wa.me/'.$r->phone.'?text='.rawurlencode("Hello {$r->name}, thank you for your quote request Q-{$r->id} to Wisdom Propagation Nursery. "))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageQuoteRequests::route('/'),
            'edit' => Pages\EditQuoteRequest::route('/{record}/edit'),
        ];
    }
}
