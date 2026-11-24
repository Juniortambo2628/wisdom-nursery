<?php

use App\Enums\OrderStatus;
use App\Events\OrderPaid;
use App\Jobs\SendAlert;
use App\Models\AlertRecipient;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->seed();
    AlertRecipient::create(['name' => 'Manager', 'phone' => '254700111222', 'email' => 'manager@example.com', 'channels' => ['sms', 'whatsapp', 'email']]);
    AlertRecipient::create(['name' => 'Off', 'phone' => '254700333444', 'channels' => ['sms'], 'is_active' => false]);
});

function paidOrder(): Order
{
    $order = Order::factory()->create(['status' => OrderStatus::Paid, 'paid_at' => now(), 'total' => 5100]);
    $order->items()->create(['plant_name' => 'Swiss Cheese Plant', 'unit_price' => 1600, 'quantity' => 1, 'line_total' => 1600]);
    $order->payments()->create(['method' => 'stk', 'amount' => 5100, 'mpesa_receipt' => 'SKL4H7XXXX', 'status' => 'success']);

    return $order->fresh(['items', 'latestPayment']);
}

it('alerts every active recipient on their channels when an order is paid', function () {
    Queue::fake();
    OrderPaid::dispatch(paidOrder());

    Queue::assertPushed(SendAlert::class, 4); // sms + whatsapp + email to the manager, sms to the customer
    Queue::assertPushed(SendAlert::class, fn ($j) => $j->channel === 'sms' && $j->recipient === '254700111222'
        && str_contains($j->body, 'PAID') && str_contains($j->body, 'KSh 5,100 via M-Pesa SKL4H7XXXX'));
    Queue::assertPushed(SendAlert::class, fn ($j) => $j->channel === 'whatsapp' && $j->template === 'new_order_alert');
    Queue::assertNotPushed(SendAlert::class, fn ($j) => $j->recipient === '254700333444');
});

it('alerts the team about a quote request', function () {
    Queue::fake();
    $this->post('/quote', ['name' => 'School', 'phone' => '0712345678', 'plants' => 'Grevillea', 'quantity' => 2000, '_ts' => time() - 10]);

    Queue::assertPushed(SendAlert::class, fn ($j) => $j->event === 'quote_requested' && str_contains($j->body, '2,000 × Grevillea'));
});

it('sends the SMS through Africa\'s Talking and logs it', function () {
    config()->set('nursery.sms', array_merge(config('nursery.sms'), ['api_key' => 'k', 'username' => 'sandbox', 'sender_id' => 'WISDOM']));
    Http::fake(['*/version1/messaging' => Http::response(['SMSMessageData' => ['Recipients' => [['status' => 'Success', 'messageId' => 'ATX1']]]], 201)]);

    SendAlert::dispatchSync('sms', '254700111222', 'order_paid', 'NEW ORDER', paidOrder());

    Http::assertSent(fn ($r) => $r['to'] === '+254700111222' && $r['from'] === 'WISDOM' && $r->hasHeader('apiKey', 'k'));
    expect(NotificationLog::sole())->status->toBe('sent')->provider_message_id->toBe('ATX1');
});

it('logs a failed SMS so missed alerts can be found', function () {
    config()->set('nursery.sms', array_merge(config('nursery.sms'), ['api_key' => 'k']));
    Http::fake(['*' => Http::response(['SMSMessageData' => ['Message' => 'InvalidSenderId', 'Recipients' => []]], 400)]);

    expect(fn () => SendAlert::dispatchSync('sms', '254700111222', 'order_paid', 'X'))->toThrow(RuntimeException::class);
    expect(NotificationLog::sole())->status->toBe('failed');
});

it('logs WhatsApp alerts with the log driver until Meta approves the template', function () {
    SendAlert::dispatchSync('whatsapp', '254700111222', 'order_paid', 'X', null, 'new_order_alert', ['WN-1001']);
    expect(NotificationLog::sole())->channel->toBe('whatsapp')->status->toBe('logged');
});

it('sends WhatsApp template messages through the Cloud API when switched on', function () {
    config()->set('nursery.whatsapp_alerts', array_merge(config('nursery.whatsapp_alerts'), ['driver' => 'cloud', 'token' => 't', 'phone_number_id' => '123']));
    Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]])]);

    SendAlert::dispatchSync('whatsapp', '254700111222', 'order_paid', 'X', null, 'new_order_alert', ["WN-1001\nline"]);

    Http::assertSent(fn ($r) => $r['template']['name'] === 'new_order_alert' && $r['template']['components'][0]['parameters'][0]['text'] === 'WN-1001 line');
    expect(NotificationLog::sole())->status->toBe('sent');
});

it('emails alerts', function () {
    Mail::fake();
    SendAlert::dispatchSync('email', 'manager@example.com', 'order_paid', 'NEW ORDER', null, emailSubject: 'New order');
    expect(NotificationLog::sole())->status->toBe('sent');
});

it('texts the customer when the order is ready or dispatched', function () {
    Queue::fake();
    $order = paidOrder();

    $order->transitionTo(OrderStatus::Ready);
    Queue::assertPushed(SendAlert::class, fn ($j) => $j->event === 'order_ready' && str_contains($j->body, "{$order->number} is ready for collection"));

    $delivery = Order::factory()->create(['status' => OrderStatus::Ready, 'fulfilment' => 'delivery', 'delivery_area_name' => 'Kitui']);
    $delivery->transitionTo(OrderStatus::Dispatched);
    Queue::assertPushed(SendAlert::class, fn ($j) => $j->event === 'order_dispatched' && str_contains($j->body, 'on its way to Kitui'));
});
