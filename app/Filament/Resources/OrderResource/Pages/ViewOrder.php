<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Enums\OrderStatusPresenter;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return 'Order '.$this->record->number;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];
        foreach ([OrderStatus::Ready, OrderStatus::Dispatched, OrderStatus::Collected, OrderStatus::Delivered] as $status) {
            $actions[] = Actions\Action::make('mark_'.$status->value)
                ->label(OrderStatusPresenter::action($status))
                ->icon(OrderStatusPresenter::icon($status))
                ->visible(fn () => in_array($status, $this->record->nextStatuses(), true))
                ->requiresConfirmation()
                ->action(function () use ($status) {
                    $this->record->transitionTo($status);
                    Notification::make()->title($status->label())->success()->send();
                });
        }
        $actions[] = Actions\Action::make('call')->label('Call customer')->icon('heroicon-o-phone')->color('gray')
            ->url(fn () => 'tel:+'.$this->record->customer_phone);
        $actions[] = Actions\Action::make('cancel')->label('Cancel order')->color('danger')->icon('heroicon-o-x-mark')
            ->visible(fn () => in_array($this->record->status, [OrderStatus::PendingPayment, OrderStatus::Paid], true))
            ->requiresConfirmation()
            ->modalDescription('Cancelling does not refund an M-Pesa payment; do that from the M-Pesa portal.')
            ->action(fn () => $this->record->transitionTo(OrderStatus::Cancelled));

        return $actions;
    }
}
