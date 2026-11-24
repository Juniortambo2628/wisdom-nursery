<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Rules\KenyanPhone;
use App\Services\Mpesa\Daraja;
use App\Services\Mpesa\PaymentService;
use App\Support\Phone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The customer's own order page, reached by its unguessable token. */
class OrderController extends Controller
{
    public function show(Order $order, Daraja $daraja): View
    {
        $order->load('items.plant.primaryImage', 'latestPayment');

        return view('pages.order', [
            'order' => $order,
            'payment' => $order->latestPayment,
            'mpesaPhone' => Phone::display($order->latestPayment?->phone ?? $order->customer_phone),
            'till' => setting('till_number') ?: (config('nursery.mpesa.type') === 'till' ? config('nursery.mpesa.party_b') : null),
            'canRetry' => $daraja->isConfigured() && ! $order->isPaid(),
        ]);
    }

    /** "Send the prompt again", e.g. after the customer cancelled or it timed out. */
    public function retry(Request $request, Order $order, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate(['phone' => ['required', new KenyanPhone]]);
        if (! $order->isPaid()) {
            $payments->requestStk($order, Phone::normalise($data['phone']));
        }

        return redirect()->route('orders.show', $order);
    }

    /** Polled by the "check your phone" panel. */
    public function status(Order $order): JsonResponse
    {
        $payment = $order->latestPayment;

        return response()->json([
            'status' => $order->status->value,
            'paid' => $order->isPaid(),
            'number' => $order->number,
            'receipt' => $payment?->mpesa_receipt,
            'payment_status' => $payment?->status?->value,
        ]);
    }

    /** "No prompt? Pay to Till … and enter the M-Pesa code." The team verifies it. */
    public function submitCode(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'mpesa_code' => ['required', 'string', 'regex:/^[A-Za-z0-9]{10}$/'],
        ], ['mpesa_code.regex' => 'An M-Pesa code is 10 letters and numbers, like SKL4H7XXXX.']);

        if ($order->isPaid()) {
            return back();
        }
        if ($order->payments()->where('mpesa_receipt', strtoupper($data['mpesa_code']))->exists()) {
            return back()->with('status', 'We already have that code and are checking it.');
        }

        $order->payments()->create([
            'method' => 'manual_code',
            'phone' => $order->customer_phone,
            'amount' => $order->total,
            'mpesa_receipt' => strtoupper($data['mpesa_code']),
            'status' => PaymentStatus::PendingVerification,
        ]);

        return back()->with('status', 'Thank you. We will confirm your payment shortly and send you an SMS.');
    }
}
