<?php

namespace App\Jobs;

use App\Models\EventOutbox;
use App\Services\EventPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads a pending EventOutbox row and POSTs it to the DEORIS Event Hub.
 * Implements the transactional outbox pattern for reliable event delivery.
 * Runs on the 'events' queue.
 */
class PublishEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 5;
    public int $timeout = 15;
    public int $backoff = 30;

    public function __construct(public readonly int $outboxId) {}

    public function handle(): void
    {
        $outbox = EventOutbox::find($this->outboxId);

        if (!$outbox || $outbox->status === 'published') {
            return;
        }

        $hubUrl = config('clearcheck.event_hub_url');

        if (!$hubUrl) {
            Log::warning('[PublishEventJob] EVENT_HUB_URL not configured — skipping publish');
            return;
        }

        $outbox->increment('attempts');

        try {
            $response = Http::withHeaders([
                'X-Service-Key'      => config('clearcheck.service_key'),
                'X-Event-Signature'  => $outbox->hmac_signature,
                'X-Event-Nonce'      => $outbox->nonce,
                'Content-Type'       => 'application/json',
                'Accept'             => 'application/json',
            ])
            ->timeout(10)
            ->post($hubUrl, $outbox->payload);

            if ($response->successful()) {
                $outbox->update([
                    'status'       => 'published',
                    'published_at' => now(),
                    'last_error'   => null,
                ]);

                Log::info("[PublishEventJob] Published {$outbox->event_name}", [
                    'event_id' => $outbox->event_id,
                ]);
            } else {
                $outbox->update(['last_error' => "HTTP {$response->status()}: {$response->body()}"]);
                $this->fail(new \RuntimeException("Event Hub returned HTTP {$response->status()}"));
            }
        } catch (\Throwable $e) {
            $outbox->update(['last_error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        EventOutbox::where('id', $this->outboxId)->update([
            'status'     => 'failed',
            'last_error' => $exception->getMessage(),
        ]);

        Log::error("[PublishEventJob] Permanently failed for outbox #{$this->outboxId}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
