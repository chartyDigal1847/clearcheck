<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalyticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'snapshot_date'           => $this->snapshot_date?->toDateString(),
            'total_students'          => $this->total_students,
            'cleared_count'           => $this->cleared_count,
            'pending_count'           => $this->pending_count,
            'partially_cleared_count' => $this->partially_cleared_count,
            'disputed_count'          => $this->disputed_count,
            'validating_count'        => $this->validating_count,
            'clearance_rate'          => $this->clearance_rate,
            'module_breakdown'        => $this->module_breakdown,
            'grade_breakdown'         => $this->grade_breakdown,
            'created_at'              => $this->created_at->toIso8601String(),
        ];
    }
}
