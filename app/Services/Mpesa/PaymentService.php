<?php

namespace App\Services\Mpesa;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Everything that changes a payment's state goes through here, so the STK
 * callback, the status query and a manual code confirmed in the dashboard
 * all behave the same way, and each payment is processed exactly once.
 */
class PaymentService
{
    public function __construct(private Daraja $daraja) {}

    /** Creates a payment and sends the STK prompt. Never throws: failures are stored on the payment. */
    public function requestStk(Order $order, string $phone): ?Payment
    {
        if (! $this->daraja->isConfigured()) {
            return null; // site runs with WhatsApp / till fallback until Daraja is approved
        }

        $payment = $order->payments()->create([
            'method' => 'stk',
            'phone' => $phone,
            'amount' => $order->total,
            'status' => PaymentStatus::Pending,
        ]);

        try {
            $res = $this->daraja->stkPush($phone, $order->total, $order->number, 'Plants '.$order->number);
            $payment->update([
                'checkout_request_id' => $res['CheckoutRequestID'],
                'merchant_request_id' => $res['MerchantRequestID'] ?? null,
            ]);
        } catch (Throwable $e) {
            Log::warning('STK push failed', ['order' => $order->number, 'error' => $e->getMessage()]);
            $payment->update([
                'status' => PaymentStatus::Failed,
                'result_desc' => mb_substr($e->getMessage(), 0, 250),
                'processed_at' => now(),
            ]);
        }

        return $payment;
    }

    /** Handles the JSON Safaricom posts to the callback URL. */
    public function handleCallback(array $payload): void
    {
        $cb = data_get($payload, 'Body.stkCallback');
        $checkoutId = $cb['CheckoutRequestID'] ?? null;
        if (! $checkoutId) {
            Log::warning('M-Pesa callback without CheckoutRequestID', ['payload' => $payload]);

            return;
        }

        $payment = Payment::where('checkout_request_id', $checkoutId)->first();
        if (! $payment) {
            Log::warning('M-Pesa callback for unknown CheckoutRequestID', ['id' => $checkoutId]);

            return;
        }

        $items = collect(data_get($cb, 'CallbackMetadata.Item', []))->pluck('Value', 'Name');
        $resultCode = (int) ($cb['ResultCode'] ?? -1);

        if ($resultCode === 0) {
            $this->markPaid($payment, $items->get('MpesaReceiptNumber'), (int) $items->get('Amount'), $payload, 0, $cb['ResultDesc'] ?? null);
        } else {
            $this->markFailed($payment, $resultCode, $cb['ResultDesc'] ?? 'Payment not completed', $payload);
        }
    }

    /**
     * Marks a payment successful: order paid, stock reduced, OrderPaid fired.
     * Idempotent: a payment already processed is never processed again (a
     * later callback may still add the receipt the status query lacked).
     */
    public function markPaid(Payment $payment, ?string $receipt, ?int $amount, ?array $raw = null, ?int $resultCode = 0, ?string $resultDesc = null): void
    {
        $paidOrder = DB::transaction(function () use ($payment, $receipt, $amount, $raw, $resultCode, $resultDesc) {
            /** @var Payment $payment */
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $order = Order::whereKey($payment->order_id)->lockForUpdate()->first();

            if ($payment->processed_at && $payment->status === PaymentStatus::Success) {
                if ($receipt && ! $payment->mpesa_receipt) {
                    $payment->update(['mpesa_receipt' => $receipt, 'raw_callback' => $raw ?? $payment->raw_callback]);
                }

                return null;
            }

            $amount ??= $payment->amount;
            $fields = [
                'mpesa_receipt' => $receipt ?? $payment->mpesa_receipt,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'raw_callback' => $raw ?? $payment->raw_callback,
                'processed_at' => now(),
            ];

            if ($amount < $order->total) {
                // Paid less than the order total: the team checks it in the dashboard.
                $payment->update($fields + [
                    'status' => PaymentStatus::PendingVerification,
                    'result_desc' => "Amount mismatch: paid KSh {$amount}, order total KSh {$order->total}",
                ]);
                Log::warning('M-Pesa amount mismatch', ['order' => $order->number, 'paid' => $amount, 'total' => $order->total]);

                return null;
            }

            $payment->update($fields + ['status' => PaymentStatus::Success]);

            if ($order->isPaid() || $order->status === OrderStatus::Cancelled) {
                Log::warning('Payment for an order already paid or cancelled', ['order' => $order->number, 'payment' => $payment->id]);

                return null;
            }

            $order->transitionTo(OrderStatus::Paid);

            foreach ($order->items as $item) {
                if ($item->plant_id) {
                    Plant::whereKey($item->plant_id)->update([
                        'stock_qty' => DB::raw('CASE WHEN stock_qty > '.(int) $item->stockUnits().' THEN stock_qty - '.(int) $item->stockUnits().' ELSE 0 END'),
                    ]);
                }
            }

            return $order;
        });

        if ($paidOrder) {
            OrderPaid::dispatch($paidOrder->fresh(['items', 'latestPayment']));
        }
    }

    public function markFailed(Payment $payment, int $resultCode, string $desc, ?array $raw = null): void
    {
        DB::transaction(function () use ($payment, $resultCode, $desc, $raw) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if ($payment->processed_at) {
                return;
            }
            $payment->update([
                'status' => $resultCode === 1032 ? PaymentStatus::Cancelled : PaymentStatus::Failed,
                'result_code' => $resultCode,
                'result_desc' => mb_substr($desc, 0, 250),
                'raw_callback' => $raw,
                'processed_at' => now(),
            ]);
        });
    }

    /** Asks Safaricom about an STK payment whose callback has not arrived. */
    public function reconcile(Payment $payment): void
    {
        if (! $payment->checkout_request_id || $payment->processed_at) {
            return;
        }

        $res = $this->daraja->query($payment->checkout_request_id);
        if (! array_key_exists('ResultCode', $res)) {
            return; // still processing, or a transient error: try again next run
        }

        $code = (int) $res['ResultCode'];
        $code === 0
            ? $this->markPaid($payment, null, $payment->amount, null, 0, $res['ResultDesc'] ?? 'Confirmed by status query')
            : $this->markFailed($payment, $code, $res['ResultDesc'] ?? 'Payment not completed');
    }
}
