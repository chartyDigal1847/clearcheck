<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClearanceAnalytics extends Model
{
    protected $fillable = [
        'snapshot_date',
        'total_students',
        'cleared_count',
        'pending_count',
        'partially_cleared_count',
        'disputed_count',
        'validating_count',
        'clearance_rate',
        'module_breakdown',
        'grade_breakdown',
    ];

    protected $casts = [
        'snapshot_date'    => 'date',
        'clearance_rate'   => 'decimal:2',
        'module_breakdown' => 'array',
        'grade_breakdown'  => 'array',
    ];
}
