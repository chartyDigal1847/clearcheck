<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentCleared implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $studentId,
        public readonly int    $clearanceRecordId,
        public readonly string $clearedAt,
        public readonly string $correlationId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('clearance.' . $this->studentId),
            new Channel(config('clearcheck.redis_channels.events', 'clearance.events')),
        ];
    }

    public function broadcastAs(): string
    {
        return 'student.cleared';
    }

    public function broadcastWith(): array
    {
        return [
            'student_id'          => $this->studentId,
            'clearance_record_id' => $this->clearanceRecordId,
            'cleared_at'          => $this->clearedAt,
            'correlation_id'      => $this->correlationId,
        ];
    }
}
