<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Launch helper: after copying the staging database to production, remove the
 * test orders, payments, quotes, messages and logs, keeping all content
 * (plants, prices, settings, photos, team, guides, alert recipients, users).
 */
class ClearTestData extends Command
{
    protected $signature = 'nursery:clear-test-data {--force : Skip the confirmation}';

    protected $description = 'Delete test orders, payments, quotes, messages and alert logs (keeps all content)';

    /** Child tables first. */
    private const TABLES = [
        'order_items', 'payments', 'orders',
        'quote_items', 'quote_requests',
        'contact_messages', 'stock_alerts', 'notification_logs',
        'jobs', 'failed_jobs', 'sessions', 'notifications',
    ];

    public function handle(): int
    {
        $counts = collect(self::TABLES)->filter(fn ($t) => Schema::hasTable($t))
            ->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()]);

        $this->table(['Table', 'Rows to delete'], $counts->map(fn ($n, $t) => [$t, $n])->values());
        $this->line('Kept: plants, prices, stock, categories, settings, photos, gallery, team, guides, delivery areas, alert recipients, users.');

        if (app()->isProduction() && DB::table('payments')->where('status', 'success')->whereNotNull('mpesa_receipt')->where('created_at', '<', now()->subDays(2))->exists()) {
            $this->error('This database has real-looking paid orders older than two days. Refusing: run this only right after copying staging.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Delete these rows permanently?')) {
            return self::FAILURE;
        }

        Schema::disableForeignKeyConstraints();
        foreach ($counts->keys() as $table) {
            DB::table($table)->truncate(); // also restarts ids, so the first real order is WN-1001
        }
        Schema::enableForeignKeyConstraints();

        $this->info('Test data cleared. The first real order will be WN-1001.');

        return self::SUCCESS;
    }
}
