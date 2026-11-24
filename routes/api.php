<?php

use App\Http\Controllers\MpesaCallbackController;
use Illuminate\Support\Facades\Route;

// Safaricom posts STK results here. The secret path segment keeps the URL
// unguessable (MPESA_CALLBACK_SECRET); the handler is idempotent.
Route::post('/mpesa/callback/{secret}', MpesaCallbackController::class)
    ->middleware('throttle:120,1')
    ->name('mpesa.callback');
