<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentUpload extends Model
{
    protected $fillable = [
        'student_id',
        'document_type',
        'file_path',
        'status',
        'rejection_reason',
        'reviewed_by_portal_id',
        'reviewed_by_name',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'reviewed_by_portal_id' => 'string',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
