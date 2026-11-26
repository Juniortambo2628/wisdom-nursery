<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * For uptime monitors (e.g. UptimeRobot): 200 "ok" when the database answers
 * and the cron job ran in the last 10 minutes, 503 otherwise. No secrets.
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('select 1');
            $db = true;
        } catch (Throwable) {
            $db = false;
        }

        $beat = $db ? Cache::get('health:scheduler') : null;
        $cron = $beat && Carbon::parse($beat)->gt(now()->subMinutes(10));
        $ok = $db && $cron;

        return response()->json([
            'status' => $ok ? 'ok' : 'down',
            'database' => $db ? 'ok' : 'down',
            'cron' => $cron ? 'ok' : 'late',
        ], $ok ? 200 : 503)->header('Cache-Control', 'no-store');
    }
}
