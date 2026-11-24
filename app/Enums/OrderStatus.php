<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Ready = 'ready';
    case Dispatched = 'dispatched';
    case Collected = 'collected';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Awaiting payment',
            self::Paid => 'Paid',
            self::Ready => 'Ready',
            self::Dispatched => 'Dispatched',
            self::Collected => 'Collected',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    /** Timestamp column set when an order enters this status. */
    public function timestampColumn(): ?string
    {
        return match ($this) {
            self::PendingPayment => null,
            default => $this->value.'_at',
        };
    }
}
