<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\QuoteRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** "Today": sales today and what needs doing (concepts deck, order alerts page). */
class TodayStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $sales = Order::whereDate('paid_at', today())->sum('total');
        $toPrepare = Order::whereIn('status', [OrderStatus::Paid, OrderStatus::Ready])->count();
        $quotes = QuoteRequest::where('status', QuoteStatus::New)->count();
        $verify = Payment::where('status', PaymentStatus::PendingVerification)->count();

        return [
            Stat::make('Sales today', ksh($sales))
                ->description(Order::whereDate('paid_at', today())->count().' paid orders')->color('success'),
            Stat::make('To prepare', $toPrepare.' '.str('order')->plural($toPrepare))
                ->description('Paid or ready, not yet collected or delivered')
                ->url(route('filament.admin.resources.orders.index', ['tableFilters[to_prepare][isActive]' => 1])),
            Stat::make('New quote requests', (string) $quotes)
                ->url(route('filament.admin.resources.quote-requests.index')),
            Stat::make('Payments to check', (string) $verify)
                ->description('M-Pesa codes entered by customers')->color($verify ? 'warning' : 'gray')
                ->url(route('filament.admin.resources.orders.index', ['tableFilters[to_verify][isActive]' => 1])),
        ];
    }
}
