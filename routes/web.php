<?php

use App\Http\Controllers\SsoController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CheckerDashboardController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\AuditController;
use Illuminate\Support\Facades\Route;

// ── Entry point — always returns 200, flushes stale session
Route::get('/', function (\Illuminate\Http\Request $request) {
    $request->session()->forget(['sso_role','sso_name','sso_email','sso_id','user']);
    $request->session()->regenerateToken();
    return view('clearcheck');
});

// ── Logout route (used by some portal bridges or manual access)
Route::any('/logout', function (\Illuminate\Http\Request $request) {
    $request->session()->flush();
    return redirect('/');
})->name('logout');

// ── SSO exchange (called by clearcheck.js after module:ready)
// Receives the short-lived token, validates it against the Auth Service,
// hydrates the session, and returns a redirect URL — mirrors carrerConnect.
Route::post('/sso/exchange', [SsoController::class, 'exchange'])->name('sso.exchange');

// ── Admin routes
Route::prefix('admin')->name('admin.')->middleware('sso.session:admin')->group(function () {
    Route::get('/dashboard',                 [AdminDashboardController::class, 'overview'])->name('dashboard');
    Route::get('/uploads',                   [AdminDashboardController::class, 'uploads'])->name('uploads');
    Route::get('/students',                  [AdminDashboardController::class, 'students'])->name('students');
    Route::get('/students/{id}',             [AdminDashboardController::class, 'show'])->name('students.show');
    Route::get('/students/{id}/certificate', [AdminDashboardController::class, 'certificate'])->name('students.certificate');
    Route::get('/users',                     [AdminDashboardController::class, 'users'])->name('users');
    Route::get('/reports',                   [AdminDashboardController::class, 'reports'])->name('reports');
    Route::get('/departments',               [AdminDashboardController::class, 'departments'])->name('departments');
    Route::get('/audit',                     [AuditController::class, 'index'])->name('audit');
    Route::patch('/uploads/{id}/approve',    [AdminDashboardController::class, 'approve'])->name('uploads.approve');
    Route::patch('/uploads/{id}/reject',     [AdminDashboardController::class, 'reject'])->name('uploads.reject');
});

// ── Checker routes
Route::prefix('checker')->name('checker.')->middleware('sso.session:clearance_checker,admin')->group(function () {
    Route::get('/queue',        [CheckerDashboardController::class, 'queue'])->name('queue');
    Route::get('/pending',      [CheckerDashboardController::class, 'pending'])->name('pending');
    Route::get('/approved',     [CheckerDashboardController::class, 'approved'])->name('approved');
    Route::get('/rejected',     [CheckerDashboardController::class, 'rejected'])->name('rejected');
    Route::get('/history',      [CheckerDashboardController::class, 'history'])->name('history');
    Route::get('/statistics',   [CheckerDashboardController::class, 'statistics'])->name('statistics');
    Route::get('/review/{id}',  [CheckerDashboardController::class, 'review'])->name('review');
    Route::get('/student/{id}', [CheckerDashboardController::class, 'viewStudent'])->name('student');

    // Used in queue/list views: route('checker.uploads.approve', $id)
    Route::patch('/uploads/{id}/approve', [CheckerDashboardController::class, 'approve'])->name('uploads.approve');
    Route::patch('/uploads/{id}/reject',  [CheckerDashboardController::class, 'reject'])->name('uploads.reject');

    // ✅ FIX: checker-review.blade.php uses route('checker.approve') and route('checker.reject')
    //    without the 'uploads.' prefix. These shorthand aliases point to the same
    //    controller methods so both naming styles resolve correctly.
    Route::patch('/approve/{id}', [CheckerDashboardController::class, 'approve'])->name('approve');
    Route::patch('/reject/{id}',  [CheckerDashboardController::class, 'reject'])->name('reject');
});

// ── Student routes
Route::prefix('student')->name('student.')->middleware('sso.session:student')->group(function () {
    Route::get('/clearance',   [StudentDashboardController::class, 'clearanceStatus'])->name('clearance');
    Route::get('/documents',   [StudentDashboardController::class, 'documents'])->name('documents');
    Route::get('/library',     [StudentDashboardController::class, 'library'])->name('library');
    Route::get('/finance',     [StudentDashboardController::class, 'finance'])->name('finance');
    Route::get('/academic',    [StudentDashboardController::class, 'academic'])->name('academic');
    Route::get('/certificate', [StudentDashboardController::class, 'certificate'])->name('certificate');
    Route::post('/upload',     [StudentDashboardController::class, 'uploadDocument'])->name('upload');
    Route::post('/validate',   [StudentDashboardController::class, 'requestValidation'])->name('validate');
});

// ── Notifications (all authenticated roles) ───────────────────────────────
Route::prefix('notifications')->name('notifications.')->middleware('sso.session')->group(function () {
    Route::get('/',              [NotificationsController::class, 'index'])->name('index');
    Route::patch('/{id}/read',   [NotificationsController::class, 'markRead'])->name('read')->whereNumber('id');
    Route::patch('/read-all',    [NotificationsController::class, 'markAllRead'])->name('read-all');
    Route::delete('/{id}',       [NotificationsController::class, 'destroy'])->name('destroy')->whereNumber('id');
});
