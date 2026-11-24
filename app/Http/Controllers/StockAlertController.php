<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Rules\KenyanPhone;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StockAlertController extends Controller
{
    public function store(Request $request, Plant $plant): RedirectResponse
    {
        $data = $request->validate(['phone' => ['required', new KenyanPhone]]);
        $plant->stockAlerts()->firstOrCreate(['phone' => Phone::normalise($data['phone'])]);

        return back()->with('status', "We'll send you an SMS when {$plant->name} is back in stock.");
    }
}
