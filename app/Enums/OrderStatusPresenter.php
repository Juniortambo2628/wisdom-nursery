<?php

namespace App\Enums;

/** Dashboard chip colours and action labels for order statuses. */
final class OrderStatusPresenter
{
    public static function color(OrderStatus $s): string
    {
        return match ($s) {
            OrderStatus::PendingPayment => 'gray',
            OrderStatus::Paid => 'success',
            OrderStatus::Ready => 'info',
            OrderStatus::Dispatched => 'warning',
            OrderStatus::Collected, OrderStatus::Delivered => 'primary',
            OrderStatus::Cancelled => 'danger',
        };
    }

    public static function action(OrderStatus $s): string
    {
        return match ($s) {
            OrderStatus::Ready => 'Mark ready',
            OrderStatus::Dispatched => 'Mark dispatched',
            OrderStatus::Collected => 'Mark collected',
            OrderStatus::Delivered => 'Mark delivered',
            default => $s->label(),
        };
    }

    public static function icon(OrderStatus $s): string
    {
        return match ($s) {
            OrderStatus::Ready => 'heroicon-o-check-circle',
            OrderStatus::Dispatched => 'heroicon-o-truck',
            default => 'heroicon-o-check-badge',
        };
    }
}
