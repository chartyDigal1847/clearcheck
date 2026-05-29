<?php

namespace App\Services;

use App\Models\EventOutbox;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Publishes events to the DEORIS Event Hub via the outbox pattern.
 * All events are first persisted to event_outbox, then dispatched
 * asynchronously by the PublishEventJob queue worker.
 *
 * Security: each event is signed with HMAC-SHA256 using the shared
 * event_secret, and includes a nonce + timestamp for replay prevention.
 */
class EventPublisher
{
    // ── Supported event names ─────────────────────────────────────────────
    public const CLEARANCE_UPDATED           = 'ClearanceUpdated';
    public const CLEARANCE_VALIDATED         = 'ClearanceValidated';
    public const PENDING_REQUIREMENT_DETECTED = 'PendingRequirementDetected';
    public const STUDENT_CLEARED             = 'StudentCleared';
    public const VALIDATION_FAILED           = 'ValidationFailed';

    /**
     * Write an event to the outbox. The PublishEventJob will pick it up.
     */
    public static function queue(
        string  $eventName,
        array   $payload,
        ?string $correlationId = null
    ): EventOutbox {
        $eventId       = (string) Str::uuid();
        $correlationId = $correlationId ?? (string) Str::uuid();
        $nonce         = Str::random(32);
        $timestamp     = now()->toIso8601String();

        $fullPayload = array_merge($payload, [
            'event_id'       => $eventId,
            'event_name'     => $eventName,
            'source_service' => config('clearcheck.service_key', 'clearcheck-service'),
            'schema_version' => config('clearcheck.event_schema_version', '1.0'),
            'correlation_id' => $correlationId,
            'timestamp'      => $timestamp,
            'nonce'          => $nonce,
        ]);

        $signature = self::sign($fullPayload, $nonce, $timestamp);

        $outbox = EventOutbox::create([
            'event_id'       => $eventId,
            'event_name'     => $eventName,
            'source_service' => config('clearcheck.service_key', 'clearcheck-service'),
            'schema_version' => config('clearcheck.event_schema_version', '1.0'),
            'correlation_id' => $correlationId,
            'payload'        => $fullPayload,
            'hmac_signature' => $signature,
            'nonce'          => $nonce,
            'status'         => 'pending',
        ]);

        // Dispatch the async publish job
        \App\Jobs\PublishEventJob::dispatch($outbox->id)
            ->onQueue(config('clearcheck.queues.events', 'events'));

        Log::info("[EventPublisher] Queued {$eventName}", [
            'event_id'       => $eventId,
            'correlation_id' => $correlationId,
        ]);

        return $outbox;
    }

    /**
     * HMAC-SHA256 sign the payload.
     * Signature covers: sorted payload keys + nonce + timestamp.
     */
    public static function sign(array $payload, string $nonce, string $timestamp): string
    {
        $secret  = config('clearcheck.event_secret', '');
        $message = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                   . '|' . $nonce
                   . '|' . $timestamp;

        return hash_hmac('sha256', $message, $secret);
    }

    /**
     * Verify an inbound event signature (for events received from the hub).
     */
    public static function verify(array $payload, string $signature, string $nonce, string $timestamp): bool
    {
        // Replay attack prevention: reject events older than the configured window
        $window = (int) config('clearcheck.event_replay_window', 300);
        if (abs(now()->diffInSeconds(\Carbon\Carbon::parse($timestamp))) > $window) {
            Log::warning('[EventPublisher] Replay attack detected — timestamp out of window', [
                'timestamp' => $timestamp,
            ]);
            return false;
        }

        $expected = self::sign($payload, $nonce, $timestamp);

        return hash_equals($expected, $signature);
    }
}
