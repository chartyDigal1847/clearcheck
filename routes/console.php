<?php

use App\Jobs\GenerateAnalyticsJob;
use App\Models\EventOutbox;
use App\Jobs\PublishEventJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Manually trigger an analytics snapshot for a given date (or today).
 * Usage: php artisan clearcheck:analytics {date?}
 */
Artisan::command('clearcheck:analytics {date?}', function (string $date = null) {
    $date = $date ?? now()->toDateString();
    $this->info("Generating analytics snapshot for {$date}...");
    GenerateAnalyticsJob::dispatchSync($date);
    $this->info('Done.');
})->purpose('Generate a ClearCheck analytics snapshot');

/**
 * Retry all stuck pending outbox events.
 * Usage: php artisan clearcheck:retry-events
 */
Artisan::command('clearcheck:retry-events', function () {
    $stuck = EventOutbox::where('status', 'pending')
        ->where('attempts', '<', 5)
        ->get();

    $this->info("Retrying {$stuck->count()} stuck outbox event(s)...");

    foreach ($stuck as $outbox) {
        PublishEventJob::dispatch($outbox->id)
            ->onQueue(config('clearcheck.queues.events', 'events'));
    }

    $this->info('Dispatched.');
})->purpose('Retry stuck ClearCheck outbox events');
