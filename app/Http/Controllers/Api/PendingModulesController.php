<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModuleValidation;
use App\Models\Student;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/v1/pending-modules                  — institution-wide pending modules
 * GET /api/v1/pending-modules/{student_id}     — pending modules for a student
 */
class PendingModulesController extends Controller
{
    /**
     * GET /api/v1/pending-modules
     * Returns all unresolved module validations across all students.
     */
    public function index(Request $request): JsonResponse
    {
        ActivityLogger::info('api.pending_modules.index');

        $query = ModuleValidation::with('student.user')
            ->whereIn('status', ['pending', 'failed', 'error', 'timeout'])
            ->whereNull('deleted_at');

        if ($moduleKey = $request->query('module')) {
            $query->where('module_key', $moduleKey);
        }

        if ($gradeLevel = $request->query('grade_level')) {
            $query->whereHas('student', fn($q) => $q->where('grade_level', $gradeLevel));
        }

        $pending = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $pending->map(fn($mv) => [
                'student_id'        => $mv->student_id,
                'student_name'      => $mv->student?->user?->name,
                'reg_no'            => $mv->student?->reg_no,
                'module_key'        => $mv->module_key,
                'module_name'       => $mv->module_name,
                'status'            => $mv->status,
                'unresolved_issues' => $mv->unresolved_issues,
                'last_checked'      => $mv->validated_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $pending->currentPage(),
                'last_page'    => $pending->lastPage(),
                'total'        => $pending->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/pending-modules/{student_id}
     * Returns pending modules for a specific student.
     */
    public function forStudent(int $studentId): JsonResponse
    {
        $student = Student::find($studentId);

        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        ActivityLogger::info('api.pending_modules.student', [], Student::class, $studentId);

        $record = $student->clearanceRecord()->latest()->first();

        if (!$record) {
            return response()->json([
                'data'    => [],
                'message' => 'No clearance record found. Trigger validation first.',
            ]);
        }

        $pending = ModuleValidation::where('clearance_record_id', $record->id)
            ->whereIn('status', ['pending', 'failed', 'error', 'timeout'])
            ->get()
            ->map(fn($mv) => [
                'module_key'        => $mv->module_key,
                'module_name'       => $mv->module_name,
                'status'            => $mv->status,
                'unresolved_issues' => $mv->unresolved_issues,
                'last_checked'      => $mv->validated_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $pending]);
    }
}
