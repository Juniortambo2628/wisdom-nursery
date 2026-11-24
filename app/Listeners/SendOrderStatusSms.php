<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Services\Alerts\AlertDispatcher;
use App\Services\Alerts\AlertMessages;

/** Customer SMS when staff mark an order ready for collection or dispatched. */
class SendOrderStatusSms
{
    public function __construct(private AlertDispatcher $alerts) {}

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        match ($order->status) {
            OrderStatus::Ready => $order->fulfilment->value === 'collect'
                ? $this->alerts->smsToCustomer($order->customer_phone, 'order_ready', $order, AlertMessages::customerReady($order))
                : null,
            OrderStatus::Dispatched => $this->alerts->smsToCustomer($order->customer_phone, 'order_dispatched', $order, AlertMessages::customerDispatched($order)),
            default => null,
        };
    }
}
