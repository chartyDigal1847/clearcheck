<?php

namespace App\Jobs;

use App\Models\ClearCheckNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Persists a notification record and broadcasts it via Redis/Reverb.
 * Runs on the 'notifications' queue.
 */
class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 15;

    public function __construct(
        public readonly string|int|null $portalUserId,
        public readonly string $type,
        public readonly string $title,
        public readonly string $body,
        public readonly array $data = []
    ) {}

    public function handle(): void
    {
        $notification = ClearCheckNotification::create([
            'portal_user_id' => $this->portalUserId,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'is_read' => false,
        ]);

        Log::info("[SendNotificationJob] Notification created #{$notification->id}", [
            'portal_user_id' => $this->portalUserId,
            'type' => $this->type,
        ]);

        try {
            $channel = config('clearcheck.redis_channels.notifications', 'clearance.notifications');
            \Illuminate\Support\Facades\Redis::publish($channel, json_encode([
                'notification_id' => $notification->id,
                'portal_user_id' => $this->portalUserId,
                'type' => $this->type,
                'title' => $this->title,
                'body' => $this->body,
                'data' => $this->data,
                'created_at' => $notification->created_at->toIso8601String(),
            ]));
        } catch (\Throwable $e) {
            Log::warning('[SendNotificationJob] Redis publish failed: '.$e->getMessage());
        }
    }
}
