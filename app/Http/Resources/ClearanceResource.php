<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClearanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'student_id'          => $this->student_id,
            'status'              => $this->status,
            'progress_percentage' => $this->progress_percentage,
            'modules_cleared'     => $this->modules_cleared,
            'modules_total'       => $this->modules_total,
            'last_validated_at'   => $this->last_validated_at?->toIso8601String(),
            'cleared_at'          => $this->cleared_at?->toIso8601String(),
            'expires_at'          => $this->expires_at?->toIso8601String(),
            'correlation_id'      => $this->correlation_id,
            'module_validations'  => ModuleValidationResource::collection(
                $this->whenLoaded('moduleValidations')
            ),
            'created_at'          => $this->created_at->toIso8601String(),
            'updated_at'          => $this->updated_at->toIso8601String(),
        ];
    }
}
