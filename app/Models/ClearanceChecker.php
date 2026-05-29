<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClearanceChecker extends Model
{
    protected $fillable = [
        'deoris_user_id',
        'checker_name',
        'checker_email',
        'department',
        'documents_reviewed',
        'documents_approved',
        'documents_rejected',
    ];

    protected $casts = [
        'deoris_user_id' => 'string',
    ];

    public function getUserAttribute(): object
    {
        return (object) [
            'name' => $this->checker_name ?? 'Checker',
            'email' => $this->checker_email ?? '',
        ];
    }
}
