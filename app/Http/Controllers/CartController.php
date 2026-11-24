<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Support\Cart;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function show(Cart $cart): View
    {
        $lines = $cart->lines();

        return view('pages.cart', [
            'lines' => $lines,
            'subtotal' => $lines->sum('lineTotal'),
            'whatsapp' => $lines->isNotEmpty() ? WhatsApp::forCart($lines, $lines->sum('lineTotal')) : null,
        ]);
    }

    public function add(Request $request, Cart $cart): RedirectResponse
    {
        $data = $request->validate([
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:'.Cart::MAX_QTY],
            'buy_as' => ['nullable', 'in:single,set'],
        ]);
        $plant = Plant::findOrFail($data['plant_id']);

        if (! $plant->isBuyable()) {
            return back()->with('error', "{$plant->name} is out of stock right now.");
        }

        $cart->add($plant, (int) ($data['qty'] ?? 1), $data['buy_as'] ?? 'single');

        return back()->with('status', "{$plant->name} added to your cart.");
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:40'],
            'qty' => ['required', 'integer', 'min:0', 'max:'.Cart::MAX_QTY],
        ]);
        $cart->update($data['key'], (int) $data['qty']);

        return back();
    }
}
