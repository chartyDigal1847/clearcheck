<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidationStatus extends Model
{
    protected $fillable = [
        'clearance_record_id',
        'student_id',
        'previous_status',
        'new_status',
        'triggered_by',
        'notes',
    ];

    public function clearanceRecord(): BelongsTo
    {
        return $this->belongsTo(ClearanceRecord::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
