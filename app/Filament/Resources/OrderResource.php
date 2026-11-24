<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Enums\OrderStatusPresenter;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Models\Order;
use App\Support\Phone;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'number';

    public static function getNavigationBadge(): ?string
    {
        $n = Order::where('status', OrderStatus::Paid)->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Paid orders to prepare';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('number')->label('Order')->searchable()->weight('bold')->wrap()
                    ->description(fn (Order $o) => $o->status->label().' · '.$o->customer_name.' · '.ksh($o->total)),
                Tables\Columns\TextColumn::make('customer_name')->label('Customer')->searchable()->visibleFrom('md')
                    ->description(fn (Order $o) => Phone::display($o->customer_phone)),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn ($state) => ksh($state))->sortable()->visibleFrom('md'),
                Tables\Columns\TextColumn::make('fulfilment')->formatStateUsing(fn ($state) => $state->value === 'delivery' ? 'Delivery' : 'Collect')
                    ->description(fn (Order $o) => $o->delivery_area_name)->toggleable()->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('status')->badge()->visibleFrom('sm')
                    ->formatStateUsing(fn (OrderStatus $state) => $state->label())
                    ->color(fn (OrderStatus $state) => OrderStatusPresenter::color($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                Tables\Filters\Filter::make('to_prepare')->label('To prepare')->query(fn (Builder $query) => $query->whereIn('status', [OrderStatus::Paid, OrderStatus::Ready])),
                Tables\Filters\Filter::make('today')->query(fn (Builder $query) => $query->whereDate('created_at', today())),
                Tables\Filters\Filter::make('to_verify')->label('Payment to verify')
                    ->query(fn (Builder $query) => $query->whereHas('payments', fn ($p) => $p->where('status', PaymentStatus::PendingVerification))),
            ])
            ->actions(static::statusActions())
            ->recordUrl(fn (Order $o) => static::getUrl('view', ['record' => $o]));
    }

    /** One-tap status buttons plus "Call customer"; shared by the table, the view page and the Today widget. */
    public static function statusActions(): array
    {
        $actions = [];
        foreach ([OrderStatus::Ready, OrderStatus::Dispatched, OrderStatus::Collected, OrderStatus::Delivered] as $status) {
            $actions[] = Tables\Actions\Action::make('mark_'.$status->value)
                ->label(OrderStatusPresenter::action($status))
                ->icon(OrderStatusPresenter::icon($status))
                ->button()->size('sm')->color('primary')
                ->visible(fn (Order $o) => in_array($status, $o->nextStatuses(), true))
                ->requiresConfirmation(fn () => in_array($status, [OrderStatus::Ready, OrderStatus::Dispatched], true))
                ->modalDescription(fn (Order $o) => match ($status) {
                    OrderStatus::Ready => 'The customer gets an SMS that the order is ready to collect.',
                    OrderStatus::Dispatched => 'The customer gets an SMS that the order is on its way.',
                    default => null,
                })
                ->action(function (Order $o) use ($status) {
                    $o->transitionTo($status);
                    Notification::make()->title("{$o->number}: ".$status->label())->success()->send();
                });
        }
        $actions[] = Tables\Actions\Action::make('call')->label('Call customer')->tooltip('Call customer')->icon('heroicon-o-phone')->color('gray')->iconButton()
            ->url(fn (Order $o) => 'tel:+'.$o->customer_phone);

        return $actions;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make()->columns(['default' => 2, 'md' => 4])->schema([
                TextEntry::make('status')->badge()->formatStateUsing(fn (OrderStatus $state) => $state->label())
                    ->color(fn (OrderStatus $state) => OrderStatusPresenter::color($state)),
                TextEntry::make('total')->formatStateUsing(fn ($state) => ksh($state))->weight('bold'),
                TextEntry::make('customer_name')->label('Customer'),
                TextEntry::make('customer_phone')->label('Phone')->formatStateUsing(fn ($state) => Phone::display($state))
                    ->url(fn (Order $o) => 'tel:+'.$o->customer_phone),
                TextEntry::make('fulfilment')->formatStateUsing(fn ($state) => $state->value === 'delivery' ? 'Delivery' : 'Collect at nursery'),
                TextEntry::make('delivery_area_name')->label('Area')->placeholder('-'),
                TextEntry::make('delivery_address')->label('Address')->placeholder('-')->columnSpan(2),
            ]),
            Section::make('Items')->schema([
                RepeatableEntry::make('items')->hiddenLabel()->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('plant_name')->hiddenLabel(),
                        TextEntry::make('quantity')->hiddenLabel()->formatStateUsing(fn ($state) => "× {$state}"),
                        TextEntry::make('line_total')->hiddenLabel()->formatStateUsing(fn ($state) => ksh($state))->alignEnd(),
                    ]),
                ]),
                Grid::make(2)->schema([
                    TextEntry::make('delivery_fee')->formatStateUsing(fn ($state) => $state ? ksh($state) : 'Free'),
                    TextEntry::make('paid_at')->dateTime('j M Y, H:i')->placeholder('Not paid'),
                ]),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [PaymentsRelationManager::class];
    }

    public static function canCreate(): bool
    {
        return false; // orders come from the website
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
