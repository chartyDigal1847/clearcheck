<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClearanceResource;
use App\Http\Resources\StudentClearanceResource;
use App\Models\ClearanceRecord;
use App\Models\Student;
use App\Services\ActivityLogger;
use App\Services\ClearanceValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * GET  /api/v1/clearance                  — paginated list of all clearance records
 * GET  /api/v1/clearance/{student_id}     — clearance status for a specific student
 * POST /api/v1/clearance/validate         — trigger validation for a student
 * GET  /api/v1/clearance/{student_id}/history — status transition history
 */
class ClearanceController extends Controller
{
    public function __construct(private readonly ClearanceValidationService $service) {}

    /**
     * GET /api/v1/clearance
     * Returns paginated clearance records with optional status/grade filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        ActivityLogger::info('api.clearance.index', ['filters' => $request->only('status', 'grade_level', 'search')]);

        $query = ClearanceRecord::with(['student.user', 'moduleValidations'])
            ->whereNull('deleted_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($gradeLevel = $request->query('grade_level')) {
            $query->whereHas('student', fn($q) => $q->where('grade_level', $gradeLevel));
        }

        if ($search = $request->query('search')) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('reg_no', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $records = $query->latest()->paginate($request->integer('per_page', 15));

        return ClearanceResource::collection($records);
    }

    /**
     * GET /api/v1/clearance/{student_id}
     * Returns the latest clearance record for a student.
     */
    public function show(int $studentId): JsonResponse
    {
        $student = Student::with(['user', 'clearanceRecord.moduleValidations'])->find($studentId);

        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        ActivityLogger::info('api.clearance.show', [], Student::class, $studentId);

        $status = $this->service->getClearanceStatus($student);

        return response()->json([
            'data' => [
                'student_id'  => $student->id,
                'reg_no'      => $student->reg_no,
                'name'        => $student->user?->name,
                'clearance'   => $status,
            ],
        ]);
    }

    /**
     * POST /api/v1/clearance/validate
     * Triggers a fresh validation for a student.
     * Body: { "student_id": 1 }
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
        ]);

        $student = Student::find($validated['student_id']);

        ActivityLogger::info('api.clearance.validate', [], Student::class, $student->id);

        $record = $this->service->initiateValidation(
            $student,
            session('sso_email', 'api')
        );

        return response()->json([
            'message'    => 'Validation initiated.',
            'data'       => new ClearanceResource($record),
        ], 202);
    }

    /**
     * GET /api/v1/clearance/{student_id}/history
     * Returns the status transition history for a student's clearance.
     */
    public function history(int $studentId): JsonResponse
    {
        $student = Student::find($studentId);

        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        $history = \App\Models\ValidationStatus::where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => $history->map(fn($h) => [
                'previous_status' => $h->previous_status,
                'new_status'      => $h->new_status,
                'triggered_by'    => $h->triggered_by,
                'notes'           => $h->notes,
                'changed_at'      => $h->created_at->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $history->currentPage(),
                'last_page'    => $history->lastPage(),
                'total'        => $history->total(),
            ],
        ]);
    }
}
