<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClearanceRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'status',
        'progress_percentage',
        'modules_cleared',
        'modules_total',
        'last_validated_at',
        'cleared_at',
        'expires_at',
        'correlation_id',
    ];

    protected $casts = [
        'last_validated_at'  => 'datetime',
        'cleared_at'         => 'datetime',
        'expires_at'         => 'datetime',
        'progress_percentage'=> 'integer',
        'modules_cleared'    => 'integer',
        'modules_total'      => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function moduleValidations(): HasMany
    {
        return $this->hasMany(ModuleValidation::class);
    }

    public function validationStatuses(): HasMany
    {
        return $this->hasMany(ValidationStatus::class);
    }

    public function clearanceRequests(): HasMany
    {
        return $this->hasMany(ClearanceRequest::class);
    }

    public function isCleared(): bool
    {
        return $this->status === 'cleared';
    }
}
