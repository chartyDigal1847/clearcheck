<?php

namespace App\Jobs;

use App\Models\ClearanceAnalytics;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Generates a daily analytics snapshot and upserts it into clearance_analytics.
 * Scheduled to run daily via the Laravel scheduler.
 * Runs on the 'clearance' queue.
 */
class GenerateAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public readonly ?string $date = null) {}

    public function handle(): void
    {
        $date = $this->date ?? now()->toDateString();

        Log::info("[GenerateAnalyticsJob] Generating analytics snapshot for {$date}");

        // Aggregate clearance status counts
        $statusCounts = Student::select('clearance_status', DB::raw('count(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('clearance_status')
            ->pluck('count', 'clearance_status')
            ->toArray();

        $total            = array_sum($statusCounts);
        $clearedCount     = $statusCounts['cleared']           ?? 0;
        $pendingCount     = $statusCounts['pending']           ?? 0;
        $partialCount     = $statusCounts['partially_cleared'] ?? 0;
        $disputedCount    = $statusCounts['disputed']          ?? 0;
        $validatingCount  = $statusCounts['validating']        ?? 0;
        $clearanceRate    = $total > 0 ? round($clearedCount * 100 / $total, 2) : 0.0;

        // Per-module breakdown from module_validations
        $moduleBreakdown = DB::table('module_validations')
            ->select('module_key', 'status', DB::raw('count(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('module_key', 'status')
            ->get()
            ->groupBy('module_key')
            ->map(fn($rows) => $rows->pluck('count', 'status')->toArray())
            ->toArray();

        // Per-grade breakdown
        $gradeBreakdown = Student::select('grade_level', 'clearance_status', DB::raw('count(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('grade_level', 'clearance_status')
            ->get()
            ->groupBy('grade_level')
            ->map(fn($rows) => $rows->pluck('count', 'clearance_status')->toArray())
            ->toArray();

        ClearanceAnalytics::updateOrCreate(
            ['snapshot_date' => $date],
            [
                'total_students'          => $total,
                'cleared_count'           => $clearedCount,
                'pending_count'           => $pendingCount,
                'partially_cleared_count' => $partialCount,
                'disputed_count'          => $disputedCount,
                'validating_count'        => $validatingCount,
                'clearance_rate'          => $clearanceRate,
                'module_breakdown'        => $moduleBreakdown,
                'grade_breakdown'         => $gradeBreakdown,
            ]
        );

        Log::info("[GenerateAnalyticsJob] Snapshot saved for {$date}", [
            'total'         => $total,
            'cleared'       => $clearedCount,
            'clearance_rate'=> $clearanceRate,
        ]);
    }
}
