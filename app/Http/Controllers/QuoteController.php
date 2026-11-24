<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequestForm;
use App\Models\Plant;
use App\Models\QuoteRequest;
use App\Support\Phone;
use App\Support\QuoteList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function show(QuoteList $list): View
    {
        return view('pages.quote', ['lines' => $list->lines()]);
    }

    public function setItem(Request $request, QuoteList $list): RedirectResponse
    {
        $data = $request->validate([
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
            'qty' => ['required', 'integer', 'min:0', 'max:'.QuoteList::MAX_QTY],
        ]);
        $plant = Plant::findOrFail($data['plant_id']);
        $list->set($plant, (int) $data['qty']);

        return back()->with('status', $data['qty'] > 0 ? "{$plant->name} added to your quote list." : null);
    }

    public function store(QuoteRequestForm $request, QuoteList $list): RedirectResponse
    {
        $data = $request->validated();
        $lines = $list->lines();

        if ($lines->isEmpty() && empty($data['plants'])) {
            return back()->withInput()->withErrors(['plants' => 'Tell us which plants you need, or add some to your quote list.']);
        }

        DB::transaction(function () use ($data, $lines) {
            $quote = QuoteRequest::create([
                'name' => $data['name'],
                'phone' => Phone::normalise($data['phone']),
                'email' => $data['email'] ?? null,
                'organisation' => $data['organisation'] ?? null,
                'location' => $data['location'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $quote->items()->create(['plant_id' => $line->plant->id, 'plant_name' => $line->plant->name, 'quantity' => $line->qty]);
            }
            if (! empty($data['plants'])) {
                $quote->items()->create(['plant_name' => $data['plants'], 'quantity' => (int) ($data['quantity'] ?? 1)]);
            }
            // QuoteRequested alert to the team is dispatched here once alerts land (week 2).
        });

        $list->clear();

        return back()->with('quote_sent', true)
            ->with('status', "Thank you. We'll reply with prices and a ready date, usually the same day.");
    }
}
