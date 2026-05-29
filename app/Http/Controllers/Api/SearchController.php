<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClearanceRecord;
use App\Models\ModuleValidation;
use App\Models\Student;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/v1/search
 * Federated search endpoint — searchable by the DEORIS Portal's federated search service.
 * Searches across clearance records, students, and validation history.
 */
class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'q'    => 'required|string|min:2|max:100',
            'type' => 'nullable|in:clearance,students,validations,all',
        ]);

        $query = $request->input('q');
        $type  = $request->input('type', 'all');

        ActivityLogger::info('api.search', ['query' => $query, 'type' => $type]);

        $results = [];

        if (in_array($type, ['students', 'all'])) {
            $students = Student::with('user')
                ->where('reg_no', 'like', "%{$query}%")
                ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%"))
                ->limit(10)
                ->get()
                ->map(fn($s) => [
                    'type'         => 'student',
                    'id'           => $s->id,
                    'label'        => $s->user?->name . ' (' . $s->reg_no . ')',
                    'reg_no'       => $s->reg_no,
                    'status'       => $s->clearance_status,
                    'grade_level'  => $s->grade_level,
                    'url'          => route('admin.students.show', $s->id),
                ]);

            $results['students'] = $students;
        }

        if (in_array($type, ['clearance', 'all'])) {
            $records = ClearanceRecord::with('student.user')
                ->whereHas('student', fn($q) => $q->where('reg_no', 'like', "%{$query}%"))
                ->orWhere('correlation_id', 'like', "%{$query}%")
                ->limit(10)
                ->get()
                ->map(fn($r) => [
                    'type'           => 'clearance_record',
                    'id'             => $r->id,
                    'label'          => 'Clearance #' . $r->id . ' — ' . ($r->student?->reg_no ?? 'N/A'),
                    'status'         => $r->status,
                    'progress'       => $r->progress_percentage . '%',
                    'correlation_id' => $r->correlation_id,
                ]);

            $results['clearance_records'] = $records;
        }

        if (in_array($type, ['validations', 'all'])) {
            $validations = ModuleValidation::with('student.user')
                ->whereHas('student', fn($q) => $q->where('reg_no', 'like', "%{$query}%"))
                ->orWhere('module_key', 'like', "%{$query}%")
                ->limit(10)
                ->get()
                ->map(fn($mv) => [
                    'type'        => 'module_validation',
                    'id'          => $mv->id,
                    'label'       => $mv->module_name . ' — ' . ($mv->student?->reg_no ?? 'N/A'),
                    'module_key'  => $mv->module_key,
                    'status'      => $mv->status,
                    'student_id'  => $mv->student_id,
                ]);

            $results['validations'] = $validations;
        }

        return response()->json([
            'query'   => $query,
            'results' => $results,
        ]);
    }
}
