<?php

namespace App\Jobs;

use App\Models\Student;
use App\Services\ClearanceValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Calls a single external module's clearance API and stores the result.
 * Runs on the 'validations' queue.
 */
class ValidateModuleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int    $tries   = 3;
    public int    $timeout = 30;
    public int    $backoff = 10;

    public function __construct(
        public readonly int    $recordId,
        public readonly int    $studentId,
        public readonly string $moduleKey,
        public readonly string $correlationId
    ) {}

    public function handle(ClearanceValidationService $service): void
    {
        $student = Student::find($this->studentId);

        if (!$student) {
            Log::warning("[ValidateModuleJob] Student not found", ['student_id' => $this->studentId]);
            return;
        }

        Log::info("[ValidateModuleJob] Calling {$this->moduleKey} for student {$this->studentId}");

        $result = $service->callModuleApi($this->moduleKey, $student);

        $service->processModuleResponse(
            $this->recordId,
            $this->studentId,
            $this->moduleKey,
            $result['status'],
            $result['payload'],
            $result['issues'],
            $result['ms'],
            $this->correlationId
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[ValidateModuleJob] Failed for {$this->moduleKey}", [
            'student_id' => $this->studentId,
            'error'      => $exception->getMessage(),
        ]);

        // Mark the module validation as error so the record can still finalise
        \App\Models\ModuleValidation::where('clearance_record_id', $this->recordId)
            ->where('module_key', $this->moduleKey)
            ->update([
                'status'            => 'error',
                'unresolved_issues' => 'Job failed: ' . $exception->getMessage(),
                'validated_at'      => now(),
            ]);
    }
}
