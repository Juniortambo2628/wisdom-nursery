<?php

use App\Models\AlertRecipient;
use App\Models\NotificationLog;
use App\Models\User;
use App\Support\SiteHealth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

function check(string $label): array
{
    return collect(SiteHealth::checks())->firstWhere('label', $label);
}

it('reports the cron job from the scheduler heartbeat', function () {
    expect(check('Scheduled jobs (cron)')['status'])->toBe('fail');

    Cache::forever('health:scheduler', now()->subMinutes(2)->toIso8601String());
    expect(check('Scheduled jobs (cron)')['status'])->toBe('ok');

    Cache::forever('health:scheduler', now()->subHours(3)->toIso8601String());
    expect(check('Scheduled jobs (cron)')['status'])->toBe('fail');
});

it('runs the heartbeat from the scheduler', function () {
    $this->artisan('schedule:run')->assertSuccessful();
    expect(Cache::get('health:scheduler'))->not->toBeNull();
});

it('flags missing alert recipients and failed alerts', function () {
    expect(check('People receiving order alerts')['status'])->toBe('fail');
    AlertRecipient::create(['name' => 'Manager', 'phone' => '254700111222', 'channels' => ['sms']]);
    expect(check('People receiving order alerts')['status'])->toBe('ok');

    NotificationLog::create(['channel' => 'sms', 'recipient' => '254700111222', 'event' => 'order_paid', 'status' => 'failed']);
    expect(check('Alerts sent (24 h)')['status'])->toBe('fail');
});

it('finds the latest backup', function () {
    Storage::fake('local');
    expect(check('Backups')['status'])->toBe('fail');
    Storage::disk('local')->put(config('backup.backup.name').'/2026-11-26-02-00-00.zip', 'x');
    expect(check('Backups')['status'])->toBe('ok');
});

it('shows the site health page in the dashboard', function () {
    $this->actingAs(User::factory()->create())->get('/admin/site-health')
        ->assertOk()->assertSee('Scheduled jobs (cron)')->assertSee('Backups');
});
