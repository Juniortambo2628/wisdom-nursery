<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\OrderStatusPresenter;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Support\Phone;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Orders to act on, with one-tap status buttons. */
class TodayOrders extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Orders to prepare';

    protected static ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->whereIn('status', [OrderStatus::Paid, OrderStatus::Ready, OrderStatus::Dispatched])->oldest('paid_at'))
            ->columns([
                // On phones the customer and total fold under the number so status and buttons stay visible.
                Tables\Columns\TextColumn::make('number')->weight('bold')->wrap()
                    ->description(fn (Order $o) => $o->status->label().' · '.$o->customer_name.' · '.ksh($o->total)),
                Tables\Columns\TextColumn::make('customer_name')->label('Customer')->visibleFrom('md')->description(fn (Order $o) => Phone::display($o->customer_phone)),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn ($state) => ksh($state))->visibleFrom('md'),
                Tables\Columns\TextColumn::make('status')->badge()->visibleFrom('sm')->formatStateUsing(fn (OrderStatus $state) => $state->label())
                    ->color(fn (OrderStatus $state) => OrderStatusPresenter::color($state)),
            ])
            ->actions(OrderResource::statusActions())
            ->recordUrl(fn (Order $o) => OrderResource::getUrl('view', ['record' => $o]))
            ->emptyStateHeading('Nothing to prepare')
            ->emptyStateDescription('New paid orders appear here and on the team\'s phones.')
            ->paginated([10]);
    }
}
