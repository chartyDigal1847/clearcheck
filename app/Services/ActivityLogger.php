<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogger
{
    /**
     * Log an action to the activity_logs table.
     */
    public static function log(
        string  $action,
        string  $level       = 'info',
        array   $context     = [],
        ?string $subjectType = null,
        ?int    $subjectId   = null,
        ?array  $oldValues   = null,
        ?array  $newValues   = null
    ): void {
        try {
            $request = app(Request::class);

            ActivityLog::create([
                'actor'        => session('sso_email') ?? 'system',
                'actor_role'   => session('sso_role')  ?? 'system',
                'action'       => $action,
                'subject_type' => $subjectType,
                'subject_id'   => $subjectId,
                'old_values'   => $oldValues,
                'new_values'   => $newValues,
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'url'          => $request->fullUrl(),
                'method'       => $request->method(),
                'level'        => $level,
                'occurred_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // Never let logging break the main flow
            \Illuminate\Support\Facades\Log::error('[ActivityLogger] Failed: ' . $e->getMessage());
        }
    }

    public static function info(string $action, array $context = [], ?string $subjectType = null, ?int $subjectId = null): void
    {
        self::log($action, 'info', $context, $subjectType, $subjectId);
    }

    public static function warning(string $action, array $context = [], ?string $subjectType = null, ?int $subjectId = null): void
    {
        self::log($action, 'warning', $context, $subjectType, $subjectId);
    }

    public static function critical(string $action, array $context = [], ?string $subjectType = null, ?int $subjectId = null): void
    {
        self::log($action, 'critical', $context, $subjectType, $subjectId);
    }
}
