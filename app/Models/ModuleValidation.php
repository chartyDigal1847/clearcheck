<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleValidation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'clearance_record_id',
        'student_id',
        'module_key',
        'module_name',
        'status',
        'response_payload',
        'unresolved_issues',
        'validated_at',
        'response_time_ms',
    ];

    protected $casts = [
        'response_payload' => 'array',
        'validated_at'     => 'datetime',
        'response_time_ms' => 'integer',
    ];

    public function clearanceRecord(): BelongsTo
    {
        return $this->belongsTo(ClearanceRecord::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isCleared(): bool
    {
        return $this->status === 'cleared';
    }
}
