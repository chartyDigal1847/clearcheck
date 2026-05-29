<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleValidationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'module_key'        => $this->module_key,
            'module_name'       => $this->module_name,
            'status'            => $this->status,
            'unresolved_issues' => $this->unresolved_issues,
            'response_time_ms'  => $this->response_time_ms,
            'validated_at'      => $this->validated_at?->toIso8601String(),
        ];
    }
}
