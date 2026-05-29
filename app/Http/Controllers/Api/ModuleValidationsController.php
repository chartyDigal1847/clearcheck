<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModuleValidationResource;
use App\Models\ModuleValidation;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/v1/module-validations                  — all module validations (paginated)
 * GET /api/v1/module-validations/{student_id}     — validations for a student
 */
class ModuleValidationsController extends Controller
{
    /**
     * GET /api/v1/module-validations
     */
    public function index(Request $request): JsonResponse
    {
        ActivityLogger::info('api.module_validations.index');

        $query = ModuleValidation::with('student.user')
            ->whereNull('deleted_at');

        if ($moduleKey = $request->query('module')) {
            $query->where('module_key', $moduleKey);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $validations = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => ModuleValidationResource::collection($validations),
            'meta' => [
                'current_page' => $validations->currentPage(),
                'last_page'    => $validations->lastPage(),
                'total'        => $validations->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/module-validations/{student_id}
     */
    public function forStudent(int $studentId): JsonResponse
    {
        ActivityLogger::info('api.module_validations.student', [], \App\Models\Student::class, $studentId);

        $validations = ModuleValidation::where('student_id', $studentId)
            ->whereNull('deleted_at')
            ->latest()
            ->get();

        return response()->json([
            'data' => ModuleValidationResource::collection($validations),
        ]);
    }
}
