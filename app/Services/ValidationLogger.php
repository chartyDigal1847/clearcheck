<?php

namespace App\Services;

use App\Models\ValidationLog;

class ValidationLogger
{
    public static function log(
        string  $action,
        ?int    $studentId    = null,
        ?string $moduleKey    = null,
        array   $context      = [],
        string  $level        = 'info',
        ?string $correlationId = null
    ): void {
        try {
            ValidationLog::create([
                'student_id'     => $studentId,
                'action'         => $action,
                'module_key'     => $moduleKey,
                'actor'          => session('sso_email') ?? 'system',
                'ip_address'     => request()->ip(),
                'context'        => $context,
                'level'          => $level,
                'correlation_id' => $correlationId,
                'logged_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[ValidationLogger] Failed: ' . $e->getMessage());
        }
    }
}
