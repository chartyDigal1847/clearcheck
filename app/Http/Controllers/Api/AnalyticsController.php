<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnalyticsResource;
use App\Jobs\GenerateAnalyticsJob;
use App\Models\ClearanceAnalytics;
use App\Models\Student;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * GET  /api/v1/analytics              — latest analytics snapshot
 * GET  /api/v1/analytics/history      — historical snapshots (paginated)
 * POST /api/v1/analytics/generate     — trigger a fresh snapshot (admin only)
 * GET  /api/v1/analytics/modules      — per-module clearance rates
 * GET  /api/v1/analytics/grades       — per-grade clearance breakdown
 */
class AnalyticsController extends Controller
{
    /**
     * GET /api/v1/analytics
     * Returns the most recent analytics snapshot plus live counts.
     */
    public function index(): JsonResponse
    {
        ActivityLogger::info('api.analytics.index');

        $latest = ClearanceAnalytics::latest('snapshot_date')->first();

        // Live counts from the DB (always fresh)
        $live = Student::select('clearance_status', DB::raw('count(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('clearance_status')
            ->pluck('count', 'clearance_status');

        $total = $live->sum();

        return response()->json([
            'data' => [
                'snapshot'       => $latest ? new AnalyticsResource($latest) : null,
                'live'           => [
                    'total_students'          => $total,
                    'cleared'                 => $live->get('cleared', 0),
                    'pending'                 => $live->get('pending', 0),
                    'partially_cleared'       => $live->get('partially_cleared', 0),
                    'validating'              => $live->get('validating', 0),
                    'disputed'                => $live->get('disputed', 0),
                    'clearance_rate'          => $total > 0
                        ? round($live->get('cleared', 0) * 100 / $total, 2)
                        : 0,
                ],
            ],
        ]);
    }

    /**
     * GET /api/v1/analytics/history
     * Returns paginated historical snapshots.
     */
    public function history(Request $request): JsonResponse
    {
        $snapshots = ClearanceAnalytics::orderByDesc('snapshot_date')
            ->paginate($request->integer('per_page', 30));

        return response()->json([
            'data' => AnalyticsResource::collection($snapshots),
            'meta' => [
                'current_page' => $snapshots->currentPage(),
                'last_page'    => $snapshots->lastPage(),
                'total'        => $snapshots->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/analytics/generate
     * Triggers a fresh analytics snapshot generation (admin only).
     */
    public function generate(): JsonResponse
    {
        ActivityLogger::info('api.analytics.generate');

        GenerateAnalyticsJob::dispatch()
            ->onQueue(config('clearcheck.queues.clearance', 'clearance'));

        return response()->json(['message' => 'Analytics generation queued.'], 202);
    }

    /**
     * GET /api/v1/analytics/modules
     * Returns per-module clearance rates from the module_validation_summary view.
     */
    public function modules(): JsonResponse
    {
        ActivityLogger::info('api.analytics.modules');

        $rows = DB::table('module_validation_summary')->get();

        return response()->json(['data' => $rows]);
    }

    /**
     * GET /api/v1/analytics/grades
     * Returns per-grade clearance breakdown from the pending_clearance_stats view.
     */
    public function grades(): JsonResponse
    {
        ActivityLogger::info('api.analytics.grades');

        $rows = DB::table('pending_clearance_stats')->get();

        return response()->json(['data' => $rows]);
    }
}
