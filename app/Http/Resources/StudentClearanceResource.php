<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentClearanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $record = $this->clearanceRecord ?? null;

        return [
            'student_id'   => $this->id,
            'reg_no'       => $this->reg_no,
            'name'         => $this->user?->name,
            'email'        => $this->user?->email,
            'grade_level'  => $this->grade_level,
            'section'      => $this->section,
            'clearance'    => $record ? new ClearanceResource($record) : null,
        ];
    }
}
