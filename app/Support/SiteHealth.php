<?php

namespace App\Support;

use App\Enums\PaymentStatus;
use App\Models\AlertRecipient;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Services\Alerts\AfricasTalkingSms;
use App\Services\Mpesa\Daraja;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Checks behind Settings → Site health: is the site, checkout and alerting
 * working (SLA 9.2 monitoring), without anyone needing server access.
 */
class SiteHealth
{
    public const OK = 'ok';

    public const WARN = 'warn';

    public const FAIL = 'fail';

    /** @return list<array{group:string, label:string, status:string, detail:string}> */
    public static function checks(): array
    {
        return [
            self::scheduler(),
            self::queue(),
            self::failedJobs(),
            self::alertRecipients(),
            self::alertFailures(),
            self::mpesa(),
            self::stuckPayments(),
            self::paymentsToCheck(),
            self::sms(),
            self::whatsapp(),
            self::mail(),
            self::backups(),
            self::debugMode(),
        ];
    }

    /** Number of checks that are not OK, for the menu badge. */
    public static function problemCount(): int
    {
        return collect(self::checks())->where('status', '!=', self::OK)->count();
    }

    private static function row(string $group, string $label, string $status, string $detail): array
    {
        return compact('group', 'label', 'status', 'detail');
    }

    private static function scheduler(): array
    {
        $beat = Cache::get('health:scheduler');
        if (! $beat) {
            return self::row('Server', 'Scheduled jobs (cron)', self::FAIL, 'No heartbeat yet. Check the cPanel cron job runs "php artisan schedule:run" every minute.');
        }
        $mins = (int) Carbon::parse($beat)->diffInMinutes(now());

        return match (true) {
            $mins < 1 => self::row('Server', 'Scheduled jobs (cron)', self::OK, 'Last ran just now.'),
            $mins <= 5 => self::row('Server', 'Scheduled jobs (cron)', self::OK, "Last ran {$mins} min ago."),
            $mins <= 60 => self::row('Server', 'Scheduled jobs (cron)', self::WARN, "Last ran {$mins} min ago (should be every minute)."),
            default => self::row('Server', 'Scheduled jobs (cron)', self::FAIL, 'Last ran '.Carbon::parse($beat)->diffForHumans().'. Alerts, backups and M-Pesa checks are not running.'),
        };
    }

    private static function queue(): array
    {
        $count = DB::table('jobs')->count();
        $oldest = DB::table('jobs')->min('created_at');
        $age = $oldest ? (int) Carbon::createFromTimestamp($oldest)->diffInMinutes(now()) : 0;

        return match (true) {
            $count === 0 => self::row('Server', 'Alert queue', self::OK, 'Nothing waiting to send.'),
            $age <= 10 => self::row('Server', 'Alert queue', self::OK, "{$count} waiting, sending shortly."),
            default => self::row('Server', 'Alert queue', self::FAIL, "{$count} waiting, oldest {$age} min. The queue is not being processed (check the cron job)."),
        };
    }

    private static function failedJobs(): array
    {
        $n = DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count();

        return $n
            ? self::row('Server', 'Failed background jobs (24 h)', self::WARN, "{$n} failed after retries. See the alert log for details.")
            : self::row('Server', 'Failed background jobs (24 h)', self::OK, 'None.');
    }

    private static function alertRecipients(): array
    {
        $n = AlertRecipient::active()->count();

        return $n
            ? self::row('Alerts', 'People receiving order alerts', self::OK, "{$n} active.")
            : self::row('Alerts', 'People receiving order alerts', self::FAIL, 'Nobody. Add the team under Settings → Alert recipients.');
    }

    private static function alertFailures(): array
    {
        $since = now()->subDay();
        $failed = NotificationLog::where('created_at', '>=', $since)->where('status', 'failed')->count();
        $skipped = NotificationLog::where('created_at', '>=', $since)->where('status', 'skipped')->count();

        return match (true) {
            $failed > 0 => self::row('Alerts', 'Alerts sent (24 h)', self::FAIL, "{$failed} failed to send. Open Settings → Alert log."),
            $skipped > 0 => self::row('Alerts', 'Alerts sent (24 h)', self::WARN, "{$skipped} skipped because SMS is not set up."),
            default => self::row('Alerts', 'Alerts sent (24 h)', self::OK, NotificationLog::where('created_at', '>=', $since)->count().' sent, none failed.'),
        };
    }

    private static function mpesa(): array
    {
        if (! app(Daraja::class)->isConfigured()) {
            return self::row('Payments', 'M-Pesa', self::WARN, 'Not set up: customers see the till/code fallback instead of the M-Pesa prompt.');
        }
        $env = config('nursery.mpesa.env');

        return $env === 'production'
            ? self::row('Payments', 'M-Pesa', self::OK, 'Live ('.config('nursery.mpesa.type').').')
            : self::row('Payments', 'M-Pesa', app()->isProduction() ? self::FAIL : self::OK, 'Sandbox: test payments only.');
    }

    private static function stuckPayments(): array
    {
        $n = Payment::where('method', 'stk')->where('status', PaymentStatus::Pending)
            ->where('created_at', '<', now()->subMinutes(15))->where('created_at', '>', now()->subDay())->count();

        return $n
            ? self::row('Payments', 'M-Pesa prompts without an answer', self::WARN, "{$n} older than 15 min. Usually customers who did not finish; many in a row can mean Safaricom cannot reach the site.")
            : self::row('Payments', 'M-Pesa prompts without an answer', self::OK, 'None.');
    }

    private static function paymentsToCheck(): array
    {
        $n = Payment::where('status', PaymentStatus::PendingVerification)->count();

        return $n
            ? self::row('Payments', 'Payments to check', self::WARN, "{$n} M-Pesa code(s) entered by customers. Confirm them on the order.")
            : self::row('Payments', 'Payments to check', self::OK, 'None.');
    }

    private static function sms(): array
    {
        if (! app(AfricasTalkingSms::class)->isConfigured()) {
            return self::row('Alerts', 'SMS (Africa\'s Talking)', self::WARN, 'Not set up: no SMS alerts or customer SMS.');
        }

        return config('nursery.sms.env') === 'production'
            ? self::row('Alerts', 'SMS (Africa\'s Talking)', self::OK, 'Live'.(config('nursery.sms.sender_id') ? ', sender '.config('nursery.sms.sender_id') : '').'.')
            : self::row('Alerts', 'SMS (Africa\'s Talking)', app()->isProduction() ? self::FAIL : self::OK, 'Sandbox: messages go to the simulator only.');
    }

    private static function whatsapp(): array
    {
        return config('nursery.whatsapp_alerts.driver') === 'cloud'
            ? self::row('Alerts', 'WhatsApp alerts', self::OK, 'Sending through the WhatsApp Cloud API.')
            : self::row('Alerts', 'WhatsApp alerts', self::WARN, 'Logged only, until Meta approves the templates. SMS and email still go out.');
    }

    private static function mail(): array
    {
        return in_array(config('mail.default'), ['log', 'array'], true)
            ? self::row('Alerts', 'Email', app()->isProduction() ? self::FAIL : self::WARN, 'Not sending (mailer is "'.config('mail.default').'").')
            : self::row('Alerts', 'Email', self::OK, 'Sending through '.config('mail.default').' as '.config('mail.from.address').'.');
    }

    private static function backups(): array
    {
        $disk = Storage::disk('local');
        $files = collect($disk->files(config('backup.backup.name')))->filter(fn ($f) => str_ends_with($f, '.zip'));
        if ($files->isEmpty()) {
            return self::row('Server', 'Backups', self::FAIL, 'No backup found yet. The daily backup runs at 02:00 through the cron job.');
        }
        $newest = Carbon::createFromTimestamp($files->map(fn ($f) => $disk->lastModified($f))->max());
        $hours = (int) $newest->diffInHours(now());

        return $hours <= 26
            ? self::row('Server', 'Backups', self::OK, 'Latest '.$newest->diffForHumans().' ('.$files->count().' kept).')
            : self::row('Server', 'Backups', $hours <= 24 * 8 ? self::WARN : self::FAIL, 'Latest '.$newest->diffForHumans().'. Daily backups are not running.');
    }

    private static function debugMode(): array
    {
        return app()->isProduction() && config('app.debug')
            ? self::row('Server', 'Debug mode', self::FAIL, 'APP_DEBUG is on in production: error details are shown to visitors. Set APP_DEBUG=false.')
            : self::row('Server', 'Debug mode', self::OK, 'Off for visitors.');
    }
}
