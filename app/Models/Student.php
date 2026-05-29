<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'deoris_user_id',
        'student_name',
        'student_email',
        'reg_no',
        'grade_level',
        'section',
        'program',
        'clearance_status',
        'completed_steps',
        'total_steps',
    ];

    protected $casts = [
        'deoris_user_id' => 'string',
    ];

    /**
     * Blade/controllers legacy accessor ($student->user->name).
     */
    public function getUserAttribute(): object
    {
        return (object) [
            'name' => $this->display_name,
            'email' => $this->display_email,
        ];
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->student_name ?? 'Student';
    }

    public function getDisplayEmailAttribute(): string
    {
        return $this->student_email ?? '';
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(DocumentUpload::class);
    }

    public function clearanceRecord(): HasOne
    {
        return $this->hasOne(ClearanceRecord::class)->latestOfMany();
    }

    public function clearanceRecords(): HasMany
    {
        return $this->hasMany(ClearanceRecord::class);
    }

    public function moduleValidations(): HasMany
    {
        return $this->hasMany(ModuleValidation::class);
    }

    public function clearanceRequests(): HasMany
    {
        return $this->hasMany(ClearanceRequest::class);
    }

    public function validationLogs(): HasMany
    {
        return $this->hasMany(ValidationLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ClearCheckNotification::class, 'portal_user_id', 'deoris_user_id');
    }

    public function isCleared(): bool
    {
        return $this->clearance_status === 'cleared';
    }

    public function getProgressPercentageAttribute(): int
    {
        $record = $this->clearanceRecord;

        return $record ? (int) $record->progress_percentage : 0;
    }

    public static function findByPortalId(?string $portalId): ?self
    {
        if (! $portalId) {
            return null;
        }

        return static::where('deoris_user_id', $portalId)->first();
    }

    public static function findByPortalEmail(?string $email): ?self
    {
        if (! $email) {
            return null;
        }

        return static::where('student_email', strtolower($email))->first();
    }
}
