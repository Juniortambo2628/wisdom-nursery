<?php

namespace App\Services\Alerts;

use App\Enums\Fulfilment;
use App\Models\Order;
use App\Models\QuoteRequest;
use App\Support\Phone;

/** The words of every alert and customer SMS, in one place. */
class AlertMessages
{
    /** Team alert, as in the concepts deck ("NEW ORDER WN-1042 · PAID"). */
    public static function orderPaid(Order $order): string
    {
        $receipt = $order->latestPayment?->mpesa_receipt;
        $lines = [
            "NEW ORDER {$order->number} · PAID",
            $order->customer_name.', '.Phone::display($order->customer_phone),
            ...$order->items->map(fn ($i) => "{$i->quantity} × {$i->plant_name}")->all(),
            ksh($order->total).' via M-Pesa'.($receipt ? " {$receipt}" : ''),
            self::fulfilment($order),
            'Open order: '.$order->adminUrl(),
        ];

        return implode("\n", $lines);
    }

    /** Parameters for the approved WhatsApp template "new_order_alert". */
    public static function orderPaidParams(Order $order): array
    {
        return [
            $order->number,
            ksh($order->total),
            $order->latestPayment?->mpesa_receipt ?? '-',
            $order->customer_name.' '.Phone::display($order->customer_phone),
            $order->items->map(fn ($i) => "{$i->quantity} × {$i->plant_name}")->implode(', '),
            self::fulfilment($order),
            $order->adminUrl(),
        ];
    }

    public static function quoteRequested(QuoteRequest $quote): string
    {
        return implode("\n", array_filter([
            "NEW QUOTE Q-{$quote->id}",
            trim($quote->name.($quote->organisation ? " ({$quote->organisation})" : '')).', '.Phone::display($quote->phone),
            ...$quote->items->map(fn ($i) => number_format($i->quantity)." × {$i->plant_name}")->all(),
            $quote->location ? "Deliver to: {$quote->location}" : null,
            $quote->notes ? 'Notes: '.$quote->notes : null,
            'Open quote: '.url('/admin/quote-requests/'.$quote->id.'/edit'),
        ]));
    }

    public static function quoteRequestedParams(QuoteRequest $quote): array
    {
        return [
            "Q-{$quote->id}",
            trim($quote->name.($quote->organisation ? " ({$quote->organisation})" : '')).' '.Phone::display($quote->phone),
            $quote->items->map(fn ($i) => number_format($i->quantity)." × {$i->plant_name}")->implode(', '),
            $quote->location ?: '-',
            url('/admin/quote-requests/'.$quote->id.'/edit'),
        ];
    }

    /** Customer SMS after payment. */
    public static function customerPaid(Order $order): string
    {
        $first = strtok($order->customer_name, ' ');
        $next = $order->fulfilment === Fulfilment::Delivery
            ? "We'll SMS you when your plants are on the way."
            : "We'll SMS you when your plants are ready to collect.";

        return "Hi {$first}, thank you! We've received ".ksh($order->total)." for order {$order->number}. {$next}".self::help();
    }

    public static function customerReady(Order $order): string
    {
        $where = ($address = setting('address')) && ! str_starts_with($address, '[') ? " at {$address}" : ' at the nursery';

        return "Your order {$order->number} is ready for collection{$where}. See you soon!".self::help();
    }

    public static function customerDispatched(Order $order): string
    {
        return "Your order {$order->number} is on its way".($order->delivery_area_name ? " to {$order->delivery_area_name}" : '').'.'.self::help();
    }

    private static function fulfilment(Order $order): string
    {
        return $order->fulfilment === Fulfilment::Delivery
            ? 'Deliver: '.trim(($order->delivery_area_name ?? '').', '.($order->delivery_address ?? ''), ', ')
            : 'Collect at nursery.';
    }

    private static function help(): string
    {
        $wa = config('nursery.whatsapp');

        return $wa ? ' Questions? WhatsApp '.Phone::display($wa).'.' : '';
    }
}
