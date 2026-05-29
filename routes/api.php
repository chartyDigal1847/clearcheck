<?php

use App\Http\Controllers\Api\ClearanceController;
use App\Http\Controllers\Api\PendingModulesController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\ModuleValidationsController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\NotificationsApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ClearCheck REST API — v1
|--------------------------------------------------------------------------
| All routes require an active SSO session (RequireSsoSession middleware).
| Rate limiting is applied via the 'api' throttle limiter defined in
| AppServiceProvider (60 requests/minute per session).
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['sso.session', 'throttle:api'])->group(function () {

    // ── Clearance Records ─────────────────────────────────────────────────
    Route::prefix('clearance')->group(function () {
        // GET  /api/v1/clearance
        Route::get('/', [ClearanceController::class, 'index'])
            ->middleware('sso.session:admin,clearance_checker');

        // POST /api/v1/clearance/validate
        Route::post('/validate', [ClearanceController::class, 'validate'])
            ->middleware('sso.session:admin,clearance_checker,student');

        // GET  /api/v1/clearance/{student_id}
        Route::get('/{student_id}', [ClearanceController::class, 'show'])
            ->middleware('sso.session:admin,clearance_checker,student')
            ->whereNumber('student_id');

        // GET  /api/v1/clearance/{student_id}/history
        Route::get('/{student_id}/history', [ClearanceController::class, 'history'])
            ->middleware('sso.session:admin,clearance_checker')
            ->whereNumber('student_id');
    });

    // ── Pending Modules ───────────────────────────────────────────────────
    Route::prefix('pending-modules')->group(function () {
        // GET /api/v1/pending-modules
        Route::get('/', [PendingModulesController::class, 'index'])
            ->middleware('sso.session:admin,clearance_checker');

        // GET /api/v1/pending-modules/{student_id}
        Route::get('/{student_id}', [PendingModulesController::class, 'forStudent'])
            ->middleware('sso.session:admin,clearance_checker,student')
            ->whereNumber('student_id');
    });

    // ── Analytics ─────────────────────────────────────────────────────────
    Route::prefix('analytics')->middleware('sso.session:admin')->group(function () {
        Route::get('/',          [AnalyticsController::class, 'index']);
        Route::get('/history',   [AnalyticsController::class, 'history']);
        Route::post('/generate', [AnalyticsController::class, 'generate']);
        Route::get('/modules',   [AnalyticsController::class, 'modules']);
        Route::get('/grades',    [AnalyticsController::class, 'grades']);
    });

    // ── Progress ──────────────────────────────────────────────────────────
    Route::prefix('progress')->group(function () {
        // GET /api/v1/progress  (authenticated student's own progress)
        Route::get('/', [ProgressController::class, 'mine'])
            ->middleware('sso.session:student');

        // GET /api/v1/progress/{student_id}
        Route::get('/{student_id}', [ProgressController::class, 'show'])
            ->middleware('sso.session:admin,clearance_checker')
            ->whereNumber('student_id');
    });

    // ── Module Validations ────────────────────────────────────────────────
    Route::prefix('module-validations')->middleware('sso.session:admin,clearance_checker')->group(function () {
        Route::get('/',              [ModuleValidationsController::class, 'index']);
        Route::get('/{student_id}',  [ModuleValidationsController::class, 'forStudent'])
            ->whereNumber('student_id');
    });

    // ── Federated Search ──────────────────────────────────────────────────
    Route::get('/search', SearchController::class)
        ->middleware('sso.session:admin,clearance_checker');

    // ── Notifications ─────────────────────────────────────────────────────
    Route::prefix('notifications')->middleware('sso.session')->group(function () {
        Route::get('/',                  [NotificationsApiController::class, 'index']);
        Route::patch('/{id}/read',       [NotificationsApiController::class, 'markRead'])->whereNumber('id');
        Route::patch('/read-all',        [NotificationsApiController::class, 'markAllRead']);
        Route::delete('/{id}',           [NotificationsApiController::class, 'destroy'])->whereNumber('id');
    });

    // ── Service Health ────────────────────────────────────────────────────
    Route::get('/health', function () {
        return response()->json([
            'service'     => config('clearcheck.service_name'),
            'service_key' => config('clearcheck.service_key'),
            'api_version' => config('clearcheck.api_version'),
            'status'      => 'ok',
            'timestamp'   => now()->toIso8601String(),
        ]);
    });
});
