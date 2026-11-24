<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Mpesa\PaymentService;
use Illuminate\Console\Command;
use Throwable;

/** Asks Safaricom about STK payments whose callback never arrived. */
class ReconcileMpesa extends Command
{
    protected $signature = 'mpesa:reconcile';

    protected $description = 'Check pending M-Pesa STK payments with a status query';

    public function handle(PaymentService $payments): int
    {
        Payment::where('method', 'stk')
            ->where('status', PaymentStatus::Pending)
            ->whereNull('processed_at')
            ->whereNotNull('checkout_request_id')
            ->whereBetween('created_at', [now()->subDay(), now()->subSeconds(90)])
            ->limit(20)
            ->get()
            ->each(function (Payment $p) use ($payments) {
                try {
                    $payments->reconcile($p);
                } catch (Throwable $e) {
                    report($e);
                }
            });

        // Prompts nobody answered within a day are closed so they stop being checked.
        Payment::where('method', 'stk')->where('status', PaymentStatus::Pending)->whereNull('processed_at')
            ->where('created_at', '<', now()->subDay())
            ->update(['status' => PaymentStatus::Failed, 'result_desc' => 'No response from M-Pesa', 'processed_at' => now()]);

        return self::SUCCESS;
    }
}
