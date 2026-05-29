<?php

namespace App\Services;

use App\Models\Student;
use App\Models\ClearanceRecord;
use App\Models\ModuleValidation;
use App\Models\ValidationStatus;
use App\Models\ClearanceRequest;
use App\Services\EventPublisher;
use App\Services\ValidationLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Core clearance validation service.
 *
 * Orchestrates validation requests to all required DEORIS modules
 * (EnrollEase, AssessPay, LibrarySys, GradeTrack), aggregates their
 * responses, computes the overall clearance status, and publishes
 * the appropriate events to the DEORIS Event Hub.
 *
 * SOA rules enforced here:
 *  - Never directly accesses another module's database
 *  - Communicates only through HTTP APIs
 *  - Caches module responses in Redis to reduce load
 *  - All validation activity is logged
 */
class ClearanceValidationService
{
    /**
     * Initiate or refresh clearance validation for a student.
     * Creates a ClearanceRecord + ModuleValidation rows, then
     * dispatches async jobs to query each module.
     */
    public function initiateValidation(Student $student, string $requestedBy = 'system'): ClearanceRecord
    {
        $correlationId = (string) Str::uuid();

        // Create or reuse the active clearance record
        $record = ClearanceRecord::updateOrCreate(
            ['student_id' => $student->id, 'status' => ['pending', 'validating', 'partially_cleared']],
            [
                'status'         => 'validating',
                'correlation_id' => $correlationId,
                'modules_total'  => count(config('clearcheck.required_modules', [])),
            ]
        );

        // Ensure one ModuleValidation row per required module
        foreach (config('clearcheck.required_modules', []) as $moduleKey) {
            $moduleCfg = config("clearcheck.modules.{$moduleKey}");
            ModuleValidation::updateOrCreate(
                [
                    'clearance_record_id' => $record->id,
                    'student_id'          => $student->id,
                    'module_key'          => $moduleKey,
                ],
                [
                    'module_name' => $moduleCfg['name'] ?? $moduleKey,
                    'status'      => 'pending',
                ]
            );
        }

        // Log the request
        ValidationLogger::log(
            'validate_request',
            $student->id,
            null,
            ['correlation_id' => $correlationId, 'requested_by' => $requestedBy],
            'info',
            $correlationId
        );

        // Track the clearance request
        ClearanceRequest::create([
            'student_id'          => $student->id,
            'clearance_record_id' => $record->id,
            'type'                => 'refresh',
            'status'              => 'queued',
            'requested_by'        => $requestedBy,
        ]);

        // Dispatch one job per module
        foreach (config('clearcheck.required_modules', []) as $moduleKey) {
            \App\Jobs\ValidateModuleJob::dispatch($record->id, $student->id, $moduleKey, $correlationId)
                ->onQueue(config('clearcheck.queues.validations', 'validations'));
        }

        // Publish PendingRequirementDetected event
        EventPublisher::queue(EventPublisher::PENDING_REQUIREMENT_DETECTED, [
            'student_id'    => $student->id,
            'student_reg'   => $student->reg_no,
            'record_id'     => $record->id,
            'modules'       => config('clearcheck.required_modules', []),
        ], $correlationId);

        return $record;
    }

    /**
     * Process a single module's validation response.
     * Called by ValidateModuleJob after receiving the API response.
     */
    public function processModuleResponse(
        int    $recordId,
        int    $studentId,
        string $moduleKey,
        string $status,
        array  $responsePayload,
        ?string $unresolvedIssues,
        int    $responseTimeMs,
        string $correlationId
    ): void {
        $validation = ModuleValidation::where('clearance_record_id', $recordId)
            ->where('student_id', $studentId)
            ->where('module_key', $moduleKey)
            ->first();

        if (!$validation) {
            Log::warning("[ClearanceValidationService] ModuleValidation not found", [
                'record_id'  => $recordId,
                'module_key' => $moduleKey,
            ]);
            return;
        }

        $validation->update([
            'status'            => $status,
            'response_payload'  => $responsePayload,
            'unresolved_issues' => $unresolvedIssues,
            'validated_at'      => now(),
            'response_time_ms'  => $responseTimeMs,
        ]);

        // Log the module response
        ValidationLogger::log(
            'module_response',
            $studentId,
            $moduleKey,
            [
                'status'          => $status,
                'response_time_ms'=> $responseTimeMs,
                'correlation_id'  => $correlationId,
            ],
            $status === 'error' ? 'error' : 'info',
            $correlationId
        );

        // Publish event for this module result
        $eventName = $status === 'cleared'
            ? EventPublisher::CLEARANCE_VALIDATED
            : EventPublisher::VALIDATION_FAILED;

        EventPublisher::queue($eventName, [
            'student_id'        => $studentId,
            'module_key'        => $moduleKey,
            'status'            => $status,
            'unresolved_issues' => $unresolvedIssues,
        ], $correlationId);

        // Check if all modules are now resolved — if so, compute final status
        $this->checkAndFinalise($recordId, $studentId, $correlationId);
    }

    /**
     * After each module response, check if all modules are resolved.
     * If so, compute the final clearance status and publish events.
     */
    private function checkAndFinalise(int $recordId, int $studentId, string $correlationId): void
    {
        $record = ClearanceRecord::with('moduleValidations')->find($recordId);
        if (!$record) {
            return;
        }

        $validations = $record->moduleValidations;
        $total       = $validations->count();
        $cleared     = $validations->where('status', 'cleared')->count();
        $pending     = $validations->whereIn('status', ['pending'])->count();

        // Still waiting for some modules
        if ($pending > 0) {
            return;
        }

        $newStatus = ($cleared === $total) ? 'cleared' : 'partially_cleared';

        $oldStatus = $record->status;

        $record->update([
            'status'              => $newStatus,
            'progress_percentage' => (int) round($cleared * 100 / max($total, 1)),
            'modules_cleared'     => $cleared,
            'last_validated_at'   => now(),
            'cleared_at'          => $newStatus === 'cleared' ? now() : $record->cleared_at,
        ]);

        // Mirror onto students table
        Student::where('id', $studentId)->update(['clearance_status' => $newStatus]);

        // Record status transition
        ValidationStatus::create([
            'clearance_record_id' => $recordId,
            'student_id'          => $studentId,
            'previous_status'     => $oldStatus,
            'new_status'          => $newStatus,
            'triggered_by'        => 'system',
            'notes'               => "Modules cleared: {$cleared}/{$total}",
        ]);

        // Invalidate cached clearance status
        Cache::forget("clearance_status_{$studentId}");

        // Publish ClearanceUpdated
        EventPublisher::queue(EventPublisher::CLEARANCE_UPDATED, [
            'student_id'          => $studentId,
            'clearance_record_id' => $recordId,
            'old_status'          => $oldStatus,
            'new_status'          => $newStatus,
            'progress_percentage' => $record->progress_percentage,
            'modules_cleared'     => $cleared,
            'modules_total'       => $total,
        ], $correlationId);

        // If fully cleared, also publish StudentCleared
        if ($newStatus === 'cleared') {
            EventPublisher::queue(EventPublisher::STUDENT_CLEARED, [
                'student_id'          => $studentId,
                'clearance_record_id' => $recordId,
                'cleared_at'          => now()->toIso8601String(),
            ], $correlationId);
        }

        ValidationLogger::log(
            'clearance_computed',
            $studentId,
            null,
            [
                'new_status'      => $newStatus,
                'modules_cleared' => $cleared,
                'modules_total'   => $total,
                'correlation_id'  => $correlationId,
            ],
            'info',
            $correlationId
        );
    }

    /**
     * Call a single external module's clearance API.
     * Returns ['status' => 'cleared'|'failed'|'error'|'timeout', 'payload' => [...], 'issues' => '...', 'ms' => int]
     */
    public function callModuleApi(string $moduleKey, Student $student): array
    {
        $cfg     = config("clearcheck.modules.{$moduleKey}");
        $baseUrl = $cfg['url']     ?? '';
        $apiKey  = $cfg['api_key'] ?? '';
        $timeout = $cfg['timeout'] ?? 10;

        if (!$baseUrl) {
            return $this->mockModuleResponse($moduleKey, $student);
        }

        $cacheKey = "module_validation_{$moduleKey}_{$student->id}";
        $ttl      = config('clearcheck.validation_cache_ttl', 300);

        // Return cached response if available
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $start = microtime(true);

        try {
            $response = Http::withHeaders([
                'X-Service-Key'    => config('clearcheck.service_key'),
                'X-Api-Key'        => $apiKey,
                'Accept'           => 'application/json',
            ])
            ->timeout($timeout)
            ->get("{$baseUrl}/api/v1/clearance/student/{$student->reg_no}");

            $ms = (int) ((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $data   = $response->json();
                $status = ($data['cleared'] ?? false) ? 'cleared' : 'failed';
                $result = [
                    'status'  => $status,
                    'payload' => $data,
                    'issues'  => $data['unresolved_issues'] ?? null,
                    'ms'      => $ms,
                ];
                Cache::put($cacheKey, $result, $ttl);
                return $result;
            }

            return [
                'status'  => 'error',
                'payload' => ['http_status' => $response->status()],
                'issues'  => "HTTP {$response->status()} from {$moduleKey}",
                'ms'      => $ms,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $ms = (int) ((microtime(true) - $start) * 1000);
            Log::warning("[ClearanceValidationService] Timeout calling {$moduleKey}", ['error' => $e->getMessage()]);
            return ['status' => 'timeout', 'payload' => [], 'issues' => 'Connection timeout', 'ms' => $ms];
        } catch (\Throwable $e) {
            $ms = (int) ((microtime(true) - $start) * 1000);
            Log::error("[ClearanceValidationService] Error calling {$moduleKey}", ['error' => $e->getMessage()]);
            return ['status' => 'error', 'payload' => [], 'issues' => $e->getMessage(), 'ms' => $ms];
        }
    }

    /**
     * Mock module response for local/dev environments where external
     * modules are not running. Returns 'cleared' for all modules.
     */
    private function mockModuleResponse(string $moduleKey, Student $student): array
    {
        Log::info("[ClearanceValidationService] Using mock response for {$moduleKey}");
        return [
            'status'  => 'cleared',
            'payload' => [
                'cleared'            => true,
                'module'             => $moduleKey,
                'student_reg'        => $student->reg_no,
                'validated_at'       => now()->toIso8601String(),
                'unresolved_issues'  => null,
                '_mock'              => true,
            ],
            'issues' => null,
            'ms'     => rand(50, 200),
        ];
    }

    /**
     * Get the current clearance status for a student, with Redis caching.
     */
    public function getClearanceStatus(Student $student): array
    {
        $cacheKey = "clearance_status_{$student->id}";
        $ttl      = config('clearcheck.validation_cache_ttl', 300);

        return Cache::remember($cacheKey, $ttl, function () use ($student) {
            $record = ClearanceRecord::with('moduleValidations')
                ->where('student_id', $student->id)
                ->latest()
                ->first();

            if (!$record) {
                return [
                    'status'              => 'pending',
                    'progress_percentage' => 0,
                    'modules_cleared'     => 0,
                    'modules_total'       => count(config('clearcheck.required_modules', [])),
                    'module_validations'  => [],
                    'last_validated_at'   => null,
                ];
            }

            return [
                'status'              => $record->status,
                'progress_percentage' => $record->progress_percentage,
                'modules_cleared'     => $record->modules_cleared,
                'modules_total'       => $record->modules_total,
                'module_validations'  => $record->moduleValidations->map(fn($mv) => [
                    'module_key'        => $mv->module_key,
                    'module_name'       => $mv->module_name,
                    'status'            => $mv->status,
                    'unresolved_issues' => $mv->unresolved_issues,
                    'validated_at'      => $mv->validated_at?->toIso8601String(),
                ])->values()->toArray(),
                'last_validated_at'   => $record->last_validated_at?->toIso8601String(),
                'cleared_at'          => $record->cleared_at?->toIso8601String(),
            ];
        });
    }
}
