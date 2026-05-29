<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\ActivityLogger;
use App\Services\ClearanceValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/v1/progress                — progress for the authenticated student
 * GET /api/v1/progress/{student_id}   — progress for a specific student (admin/checker)
 */
class ProgressController extends Controller
{
    public function __construct(private readonly ClearanceValidationService $service) {}

    /**
     * GET /api/v1/progress
     * Returns clearance progress for the currently authenticated student.
     */
    public function mine(): JsonResponse
    {
        $ssoEmail = session('sso_email');

        $student = Student::whereHas('user', fn($q) => $q->where('email', $ssoEmail))->first();

        if (!$student) {
            return response()->json(['error' => 'Student record not found.'], 404);
        }

        return $this->buildProgressResponse($student);
    }

    /**
     * GET /api/v1/progress/{student_id}
     */
    public function show(int $studentId): JsonResponse
    {
        $student = Student::find($studentId);

        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        ActivityLogger::info('api.progress.show', [], Student::class, $studentId);

        return $this->buildProgressResponse($student);
    }

    private function buildProgressResponse(Student $student): JsonResponse
    {
        $status = $this->service->getClearanceStatus($student);

        $requiredModules = config('clearcheck.required_modules', []);
        $moduleDetails   = collect($requiredModules)->map(function ($key) use ($status) {
            $validation = collect($status['module_validations'])->firstWhere('module_key', $key);
            $cfg        = config("clearcheck.modules.{$key}");
            return [
                'module_key'        => $key,
                'module_name'       => $cfg['name'] ?? $key,
                'status'            => $validation['status']            ?? 'pending',
                'unresolved_issues' => $validation['unresolved_issues'] ?? null,
                'validated_at'      => $validation['validated_at']      ?? null,
            ];
        });

        return response()->json([
            'data' => [
                'student_id'          => $student->id,
                'reg_no'              => $student->reg_no,
                'overall_status'      => $status['status'],
                'progress_percentage' => $status['progress_percentage'],
                'modules_cleared'     => $status['modules_cleared'],
                'modules_total'       => $status['modules_total'],
                'modules'             => $moduleDetails,
                'last_validated_at'   => $status['last_validated_at'],
                'cleared_at'          => $status['cleared_at'] ?? null,
            ],
        ]);
    }
}
