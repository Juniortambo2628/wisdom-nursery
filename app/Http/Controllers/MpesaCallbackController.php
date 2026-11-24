<?php

namespace App\Http\Controllers;

use App\Services\Mpesa\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaCallbackController extends Controller
{
    public function __invoke(Request $request, string $secret, PaymentService $payments): JsonResponse
    {
        $expected = (string) config('nursery.mpesa.callback_secret');
        if ($expected === '' || ! hash_equals($expected, $secret)) {
            abort(404);
        }

        Log::channel('daily')->info('M-Pesa callback', ['payload' => $request->all()]);
        $payments->handleCallback($request->all());

        // Safaricom only needs to know we received it.
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
