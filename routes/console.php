<?php

use Illuminate\Support\Facades\Schedule;

/*
 * Everything here runs from ONE cPanel cron entry (shared hosting has no
 * supervisor):
 *
 *   * * * * * cd <DEPLOY_PATH>/current && php artisan schedule:run >> /dev/null 2>&1
 */

// Queue: alerts, SMS and emails. Each run stops when the queue is empty or
// after 50s, so it never overlaps the next minute's run.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3 --backoff=30')
    ->everyMinute()
    ->withoutOverlapping(5);

// Re-check STK payments whose callback never arrived (M-Pesa status query).
// Registered once the M-Pesa service lands (week 2).

// Backups: daily database + uploads; retention per config/backup.php
// (SLA 9.2: weekly backups kept 4 weeks).
Schedule::command('backup:clean')->dailyAt('01:30');
Schedule::command('backup:run --only-db')->dailyAt('02:00');
Schedule::command('backup:run --only-files')->weeklyOn(0, '02:30');
Schedule::command('backup:monitor')->dailyAt('08:00');

// Housekeeping.
Schedule::command('model:prune')->daily();               // notification logs older than a year
Schedule::command('queue:prune-failed --hours=720')->daily();
Schedule::command('auth:clear-resets')->daily();
