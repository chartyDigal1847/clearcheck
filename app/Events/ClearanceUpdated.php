<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClearanceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $studentId,
        public readonly string $status,
        public readonly int    $progressPercentage,
        public readonly int    $modulesCleared,
        public readonly int    $modulesTotal,
        public readonly string $correlationId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('clearance.' . $this->studentId),
            new Channel(config('clearcheck.redis_channels.updates', 'clearance.updates')),
        ];
    }

    public function broadcastAs(): string
    {
        return 'clearance.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'student_id'          => $this->studentId,
            'status'              => $this->status,
            'progress_percentage' => $this->progressPercentage,
            'modules_cleared'     => $this->modulesCleared,
            'modules_total'       => $this->modulesTotal,
            'correlation_id'      => $this->correlationId,
            'timestamp'           => now()->toIso8601String(),
        ];
    }
}
