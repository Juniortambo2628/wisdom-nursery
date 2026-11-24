<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Services\Alerts\AlertDispatcher;
use App\Services\Alerts\AlertMessages;

class SendOrderPaidAlerts
{
    public function __construct(private AlertDispatcher $alerts) {}

    public function handle(OrderPaid $event): void
    {
        $order = $event->order->loadMissing('items', 'latestPayment');

        $this->alerts->toTeam('order_paid', $order, AlertMessages::orderPaid($order),
            "New order {$order->number} · ".ksh($order->total).' paid',
            config('nursery.whatsapp_alerts.templates.order_paid'), AlertMessages::orderPaidParams($order));

        $this->alerts->smsToCustomer($order->customer_phone, 'customer_paid', $order, AlertMessages::customerPaid($order));
    }
}
