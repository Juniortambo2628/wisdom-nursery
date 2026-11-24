<?php

namespace App\Http\Controllers;

use App\Enums\Fulfilment;
use App\Enums\OrderStatus;
use App\Http\Requests\CheckoutForm;
use App\Models\DeliveryArea;
use App\Models\Order;
use App\Support\Cart;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Cart $cart): View|RedirectResponse
    {
        $lines = $cart->lines();
        if ($lines->isEmpty()) {
            return redirect()->route('cart.show');
        }

        return view('pages.checkout', [
            'lines' => $lines,
            'subtotal' => $lines->sum('lineTotal'),
            'areas' => DeliveryArea::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Creates the order (awaiting payment) and opens its payment page.
     * The STK push to the customer's phone is added with the M-Pesa service.
     */
    public function store(CheckoutForm $request, Cart $cart): RedirectResponse
    {
        $lines = $cart->lines();
        if ($lines->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'Your cart is empty.');
        }

        $data = $request->validated();
        $area = $data['fulfilment'] === 'delivery' ? DeliveryArea::find($data['delivery_area_id']) : null;
        $subtotal = $lines->sum('lineTotal');
        $fee = $area?->fee ?? 0;

        $order = DB::transaction(function () use ($data, $area, $lines, $subtotal, $fee) {
            $order = Order::create([
                'customer_name' => $data['name'],
                'customer_phone' => Phone::normalise($data['phone']),
                'fulfilment' => Fulfilment::from($data['fulfilment']),
                'delivery_area_id' => $area?->id,
                'delivery_area_name' => $area?->name,
                'delivery_address' => $area ? $data['delivery_address'] : null,
                'delivery_fee' => $fee,
                'subtotal' => $subtotal,
                'total' => $subtotal + $fee,
                'status' => OrderStatus::PendingPayment,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'plant_id' => $line->plant->id,
                    'plant_name' => $line->label,
                    'buy_as' => $line->buyAs,
                    'units_per_item' => $line->unitsPerItem,
                    'unit_price' => $line->unitPrice,
                    'quantity' => $line->qty,
                    'line_total' => $line->lineTotal,
                ]);
            }

            return $order;
        });

        session(['checkout_mpesa_phone' => Phone::normalise($data['mpesa_phone'])]);
        $cart->clear();

        return redirect()->route('orders.show', $order);
    }
}
