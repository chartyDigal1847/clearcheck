<?php

namespace App\Http\Controllers;

use App\Models\DocumentUpload;
use App\Models\Student;
use Illuminate\View\View;

class CheckerDashboardController extends Controller
{
    private function getChecker(): array
    {
        return [
            'id'         => session('sso_id'),
            'name'       => session('sso_name', 'Checker'),
            'email'      => session('sso_email', ''),
            'department' => 'General',
            'role'       => 'clearance_checker',
        ];
    }

    // ✅ Extracted repeated stats into a helper to avoid duplication
    private function getStats(): array
    {
        return [
            'pending'        => DocumentUpload::where('status', 'pending')->count(),
            'under_review'   => 0,
            'approved_today' => DocumentUpload::where('status', 'approved')->whereDate('reviewed_at', today())->count(),
            'rejected_today' => DocumentUpload::where('status', 'rejected')->whereDate('reviewed_at', today())->count(),
        ];
    }

    // ✅ FIX: Extracted grouping logic into a shared helper.
    //    pending(), approved(), and rejected() were returning a flat per-document
    //    array, but checker.blade.php expects a grouped-by-student structure with
    //    pending_count, approved_count, total_count keys. This helper produces
    //    that structure consistently for all three methods.
    private function groupUploadsByStudent($uploads): \Illuminate\Support\Collection
    {
        // Fetch ALL uploads for each student so counts across statuses are accurate
        $studentIds = $uploads->pluck('student_id')->unique();
        $allUploads = DocumentUpload::with('student')
            ->whereIn('student_id', $studentIds)
            ->get();

        return $allUploads->groupBy('student_id')->map(function ($studentUploads) {
            $firstUpload = $studentUploads->first();
            $student     = $firstUpload->student;

            return [
                'student_id'     => $student->id,
                'student'        => $student->user->name ?? 'Unknown',
                'reg_no'         => $student->reg_no ?? 'N/A',
                'pending_count'  => $studentUploads->where('status', 'pending')->count(),
                'approved_count' => $studentUploads->where('status', 'approved')->count(),
                'rejected_count' => $studentUploads->where('status', 'rejected')->count(),
                'total_count'    => $studentUploads->count(),
                'uploaded_at'    => $firstUpload->created_at->format('Y-m-d'),
            ];
        })->values();
    }

    public function queue(): View
    {
        $checker = $this->getChecker();

        $uploads = DocumentUpload::with('student')
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        $queue = $this->groupUploadsByStudent($uploads);

        return view('clearanceport.dashboards.checker', [
            'checker'    => $checker,
            'queue'      => $queue,
            'stats'      => $this->getStats(),
            'currentTab' => 'queue'
        ]);
    }

    public function statistics(): View
    {
        $checker = $this->getChecker();

        $total = DocumentUpload::count();

        $statisticsData = [
            'total_reviewed' => $total,
            'total_approved' => DocumentUpload::where('status', 'approved')->count(),
            'total_rejected' => DocumentUpload::where('status', 'rejected')->count(),
            'approval_rate'  => $total > 0
                ? round((DocumentUpload::where('status', 'approved')->count() / $total) * 100, 2)
                : 0,
        ];

        return view('clearanceport.dashboards.checker-statistics', [
            'checker'        => $checker,
            'stats'          => $this->getStats(),
            'statisticsData' => $statisticsData,
            'currentTab'     => 'statistics'
        ]);
    }

    public function history(): View
    {
        $checker = $this->getChecker();

        $history = DocumentUpload::with('student')
            ->whereNotNull('reviewed_at')
            ->orderBy('reviewed_at', 'desc')
            ->paginate(15);

        return view('clearanceport.dashboards.checker-history', [
            'checker'    => $checker,
            'stats'      => $this->getStats(),
            'history'    => $history,
            'currentTab' => 'history'
        ]);
    }

    // ✅ FIX: Now groups uploads by student (same structure as queue())
    //    so checker.blade.php can access pending_count, approved_count, total_count
    public function pending(): View
    {
        $checker = $this->getChecker();

        $uploads = DocumentUpload::with('student')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $queue = $this->groupUploadsByStudent($uploads);

        return view('clearanceport.dashboards.checker', [
            'checker'    => $checker,
            'queue'      => $queue,
            'stats'      => $this->getStats(),
            'currentTab' => 'pending'
        ]);
    }

    // ✅ FIX: Same as pending() — now returns grouped structure
    public function approved(): View
    {
        $checker = $this->getChecker();

        $uploads = DocumentUpload::with('student')
            ->where('status', 'approved')
            ->orderBy('reviewed_at', 'desc')
            ->get();

        $queue = $this->groupUploadsByStudent($uploads);

        return view('clearanceport.dashboards.checker', [
            'checker'    => $checker,
            'queue'      => $queue,
            'stats'      => $this->getStats(),
            'currentTab' => 'approved'
        ]);
    }

    // ✅ FIX: Same as pending() — now returns grouped structure
    public function rejected(): View
    {
        $checker = $this->getChecker();

        $uploads = DocumentUpload::with('student')
            ->where('status', 'rejected')
            ->orderBy('reviewed_at', 'desc')
            ->get();

        $queue = $this->groupUploadsByStudent($uploads);

        return view('clearanceport.dashboards.checker', [
            'checker'    => $checker,
            'queue'      => $queue,
            'stats'      => $this->getStats(),
            'currentTab' => 'rejected'
        ]);
    }

    public function review($id): View
    {
        $checker = $this->getChecker();

        $upload = DocumentUpload::with('student')->findOrFail($id);

        $documentData = [
            'id'               => $upload->id,
            'student_name'     => $upload->student->user->name ?? 'Unknown',
            'student_email'    => $upload->student->user->email ?? 'Unknown',
            'student_reg'      => $upload->student->reg_no ?? 'N/A',
            'grade_level'      => 'Grade ' . ($upload->student->grade_level ?? 'N/A'),
            'section'          => $upload->student->section ?? 'N/A',
            'document_type'    => $upload->document_type,
            'file_url'         => $upload->file_path ? asset('storage/' . $upload->file_path) : null,
            'date_uploaded'    => $upload->created_at->format('M d, Y'),
            'status'           => $upload->status,
            'rejection_reason' => $upload->rejection_reason,
        ];

        return view('clearanceport.dashboards.checker-review', [
            'checker'    => $checker,
            'document'   => $documentData,
            'stats'      => $this->getStats(),
        ]);
    }

    public function viewStudent($id): View
    {
        $checker = $this->getChecker();

        $student = Student::with('user', 'uploads')->findOrFail($id);

        $studentData = [
            'id'          => $student->id,
            'name'        => $student->user->name ?? 'Unknown',
            'email'       => $student->user->email ?? 'Unknown',
            'reg_no'      => $student->reg_no ?? 'N/A',
            'grade_level' => 'Grade ' . ($student->grade_level ?? 'N/A'),
            'section'     => $student->section ?? 'N/A',
        ];

        $documents = $student->uploads->map(function ($upload) {
            return [
                'id'               => $upload->id,
                'document_type'    => $upload->document_type,
                'file_url'         => $upload->file_path ? asset('storage/' . $upload->file_path) : null,
                'status'           => $upload->status,
                'uploaded_at'      => $upload->created_at->format('M d, Y'),
                'rejection_reason' => $upload->rejection_reason,
            ];
        });

        return view('clearanceport.dashboards.checker-student', [
            'checker'    => $checker,
            'student'    => $studentData,
            'documents'  => $documents,
            'stats'      => $this->getStats(),
        ]);
    }

    public function approve($id)
    {
        $upload  = DocumentUpload::findOrFail($id);
        $checker = $this->getChecker();

        $upload->update([
            'status'                => 'approved',
            'reviewed_by_portal_id' => $checker['id'],
            'reviewed_by_name'      => $checker['name'],
            'reviewed_at'           => now(),
        ]);

        \App\Services\ActivityLogger::info('checker.document.approved', [], DocumentUpload::class, $upload->id);

        return redirect()->back()->with('success', 'Document approved successfully');
    }

    public function reject($id)
    {
        $validated = request()->validate([
            'reject_note' => 'nullable|string|max:500',
        ]);

        $upload  = DocumentUpload::findOrFail($id);
        $checker = $this->getChecker();

        $upload->update([
            'status'                => 'rejected',
            'rejection_reason'      => $validated['reject_note'] ?? null,
            'reviewed_by_portal_id' => $checker['id'],
            'reviewed_by_name'      => $checker['name'],
            'reviewed_at'           => now(),
        ]);

        \App\Services\ActivityLogger::info('checker.document.rejected', [], DocumentUpload::class, $upload->id);

        return redirect()->back()->with('success', 'Document rejected successfully');
    }
}