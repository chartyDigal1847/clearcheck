<?php

namespace App\Providers;

use App\Jobs\GenerateAnalyticsJob;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind ClearanceValidationService as a singleton so the same
        // instance is reused within a single request lifecycle
        $this->app->singleton(
            \App\Services\ClearanceValidationService::class
        );
    }

    public function boot(): void
    {
        // ── Rate Limiters ─────────────────────────────────────────────────
        // 'api' throttle: 60 requests per minute per session/IP
        RateLimiter::for('api', function (Request $request) {
            $key = session('sso_id')
                ? 'sso_' . session('sso_id')
                : $request->ip();

            return Limit::perMinute(60)->by($key);
        });

        // Stricter limiter for the validate endpoint (prevent abuse)
        RateLimiter::for('validate', function (Request $request) {
            $key = session('sso_id')
                ? 'validate_' . session('sso_id')
                : $request->ip();

            return Limit::perMinute(10)->by($key);
        });

        // ── Scheduler ─────────────────────────────────────────────────────
        // Generate daily analytics snapshot at midnight
        Schedule::job(new GenerateAnalyticsJob(), config('clearcheck.queues.clearance', 'clearance'))
            ->dailyAt('00:05')
            ->name('generate-analytics-snapshot')
            ->withoutOverlapping();

        // Re-publish any stuck outbox events every 5 minutes
        Schedule::call(function () {
            \App\Models\EventOutbox::where('status', 'pending')
                ->where('attempts', '<', 5)
                ->where('created_at', '<', now()->subMinutes(5))
                ->each(function ($outbox) {
                    \App\Jobs\PublishEventJob::dispatch($outbox->id)
                        ->onQueue(config('clearcheck.queues.events', 'events'));
                });
        })->everyFiveMinutes()->name('retry-stuck-outbox-events');
    }

    private function repinEnvFromFile(): void
    {
        $envFile = base_path('.env');
        if (! is_readable($envFile)) { return; }
        $pin = ['APP_KEY', 'APP_ENV', 'SESSION_DRIVER', 'SESSION_COOKIE',
                'SESSION_DOMAIN', 'SESSION_SECURE_COOKIE', 'SESSION_SAME_SITE',
                'BROADCAST_CONNECTION', 'DB_CONNECTION', 'DB_DATABASE'];
        $map = [
            'APP_KEY'               => 'app.key',
            'APP_ENV'               => 'app.env',
            'SESSION_DRIVER'        => 'session.driver',
            'SESSION_COOKIE'        => 'session.cookie',
            'SESSION_SAME_SITE'     => 'session.same_site',
            'SESSION_SECURE_COOKIE' => 'session.secure',
            'BROADCAST_CONNECTION'  => 'broadcasting.default',
            'DB_DATABASE'           => 'database.connections.mysql.database',
        ];
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if ($line === '' || $line[0] === '#') { continue; }
            $eq = strpos($line, '=');
            if ($eq === false) { continue; }
            $key = trim(substr($line, 0, $eq));
            if (! in_array($key, $pin, true)) { continue; }
            $val = trim(substr($line, $eq + 1));
            if (strlen($val) >= 2 && $val[0] === '"' && $val[-1] === '"') { $val = substr($val, 1, -1); }
            elseif (strlen($val) >= 2 && $val[0] === "'" && $val[-1] === "'") { $val = substr($val, 1, -1); }
            $_SERVER[$key] = $val;
            if (isset($map[$key])) { config([$map[$key] => $val]); }
        }
    }
}
