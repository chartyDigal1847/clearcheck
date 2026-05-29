<?php

namespace App\Http\Controllers;

use App\Models\ClearanceChecker;
use App\Models\DocumentUpload;
use App\Models\Student;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    private function getAdminUser(): array
{
    return [
        'id'    => session('sso_id'),
        'name'  => session('sso_name', 'Admin'),
        'email' => session('sso_email', ''),
        'role'  => 'admin',
    ];
}

    public function overview(): View
    {
        $admin = $this->getAdminUser();

        $stats = [
            'total_students' => Student::count(),
            'pending_uploads' => DocumentUpload::where('status', 'pending')->count(),
            'approved_uploads' => DocumentUpload::where('status', 'approved')->count(),
            'rejected_uploads' => DocumentUpload::where('status', 'rejected')->count(),
            'active_checkers' => ClearanceChecker::count()
        ];

        return view('clearanceport.dashboards.admin', [
            'admin' => $admin,
            'adminName' => $admin['name'], // Pass admin name for greeting
            'stats' => $stats,
            'currentTab' => 'overview'
        ]);
    }

    public function uploads(): View
    {
        $admin = $this->getAdminUser();

        $query = DocumentUpload::with('student');
        
        // Search filter - search by student name or reg_no
        $search = request('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('reg_no', 'like', '%'.$search.'%')
                        ->orWhere('student_name', 'like', '%'.$search.'%');
                });
            });
        }
        
        // Grade level filter
        $gradeLevel = request('grade_level');
        if ($gradeLevel) {
            $query->whereHas('student', function($studentQuery) use ($gradeLevel) {
                $studentQuery->where('grade_level', $gradeLevel);
            });
        }
        
        // Status filter
        $status = request('status');
        if ($status) {
            $query->where('status', $status);
        }

        $uploads = $query->orderBy('created_at', 'desc')->get();

        $gradeLevels = Student::distinct()->pluck('grade_level')->sort()->values();

        $formattedUploads = $uploads->map(function($upload) {
            $reviewerName = $upload->reviewed_by_name;
            
            return [
                'id' => $upload->id,
                'student_id' => $upload->student_id,
                'student_name' => $upload->student->user->name ?? 'Unknown',
                'student_reg' => $upload->student->reg_no ?? 'N/A',
                'grade_level' => 'Grade ' . ($upload->student->grade_level ?? 'N/A'),
                'section' => $upload->student->section ?? 'N/A',
                'document_name' => $upload->document_type ?? 'Document',
                'file_url' => $upload->file_path ? asset('storage/' . $upload->file_path) : null,
                'thumbnail_url' => $upload->file_path ? asset('storage/' . $upload->file_path) : null,
                'comment' => '',
                'date' => $upload->created_at->format('M d, Y'),
                'status' => $upload->status,
                'reviewed_by' => $reviewerName,
            ];
        });

        $stats = [
            'total_students' => Student::count(),
            'pending_uploads' => DocumentUpload::where('status', 'pending')->count(),
            'approved_uploads' => DocumentUpload::where('status', 'approved')->count(),
            'rejected_uploads' => DocumentUpload::where('status', 'rejected')->count(),
            'active_checkers' => ClearanceChecker::count()
        ];

        return view('clearanceport.admin.uploads', [
            'admin' => $admin,
            'stats' => $stats,
            'uploads' => $formattedUploads,
            'gradeLevels' => $gradeLevels,
            'search' => $search,
            'selectedStatus' => $status,
            'selectedGrade' => $gradeLevel,
            'currentTab' => 'uploads'
        ]);
    }

    public function users(): View
    {
        $admin = $this->getAdminUser();

        $students = Student::orderBy('student_name')->paginate(10);
        $checkers = ClearanceChecker::orderBy('checker_name')->get();

        $stats = [
            'total_students' => Student::count(),
            'pending_uploads' => DocumentUpload::where('status', 'pending')->count(),
            'approved_uploads' => DocumentUpload::where('status', 'approved')->count(),
            'rejected_uploads' => DocumentUpload::where('status', 'rejected')->count(),
            'active_checkers' => ClearanceChecker::count()
        ];

        return view('clearanceport.dashboards.admin', [
            'admin' => $admin,
            'stats' => $stats,
            'students' => $students,
            'checkers' => $checkers,
            'currentTab' => 'users'
        ]);
    }

    public function reports(): View
    {
        $admin = $this->getAdminUser();

        $stats = [
            'total_students' => Student::count(),
            'pending_uploads' => DocumentUpload::where('status', 'pending')->count(),
            'approved_uploads' => DocumentUpload::where('status', 'approved')->count(),
            'rejected_uploads' => DocumentUpload::where('status', 'rejected')->count(),
            'active_checkers' => ClearanceChecker::count()
        ];

        $reportData = [
            'total_uploads' => DocumentUpload::count(),
            'completion_rate' => DocumentUpload::count() > 0 
                ? round((DocumentUpload::where('status', 'approved')->count() / DocumentUpload::count()) * 100, 2)
                : 0,
            'average_review_time' => 'N/A',
            'top_document' => DocumentUpload::selectRaw('document_type, count(*) as count')
                ->groupBy('document_type')
                ->orderByDesc('count')
                ->first()
        ];

        return view('clearanceport.admin.reports', [
            'admin' => $admin,
            'stats' => $stats,
            'reportData' => $reportData,
            'currentTab' => 'reports'
        ]);
    }

    public function approve($id)
    {
        $upload    = DocumentUpload::findOrFail($id);
        $adminUser = $this->getAdminUser();

        $upload->update([
            'status'      => 'approved',
            'reviewed_by_portal_id' => $adminUser['id'],
            'reviewed_by_name' => $adminUser['name'],
            'reviewed_at' => now(),
        ]);

        \App\Services\ActivityLogger::info('admin.document.approved', [], DocumentUpload::class, $upload->id);

        return redirect()->back()->with('success', 'Document approved successfully');
    }

    public function reject($id)
    {
        $validated = request()->validate([
            'reject_note' => 'nullable|string|max:500',
        ]);

        $upload    = DocumentUpload::findOrFail($id);
        $adminUser = $this->getAdminUser();

        $upload->update([
            'status'               => 'rejected',
            'rejection_reason'     => $validated['reject_note'] ?? null,
            'reviewed_by_portal_id' => $adminUser['id'],
            'reviewed_by_name'     => $adminUser['name'],
            'reviewed_at'          => now(),
        ]);

        \App\Services\ActivityLogger::info('admin.document.rejected', [], DocumentUpload::class, $upload->id);

        return redirect()->back()->with('success', 'Document rejected successfully');
    }

    public function students(): View
    {
        $admin = $this->getAdminUser();
        
        $query = Student::with('uploads');
        
        // Search filter - search by name, reg_no, or email
        $search = request('search');
        if ($search && strlen($search) >= 2) {
            $query->where(function($q) use ($search) {
                $q->where('reg_no', 'like', '%' . $search . '%')
                  ->orWhere('student_name', 'like', '%'.$search.'%')
                  ->orWhere('student_email', 'like', '%'.$search.'%');
            });
        }
        
        // Grade level filter
        $gradeLevel = request('grade');
        if ($gradeLevel) {
            $query->where('grade_level', $gradeLevel);
        }

        $studentsPaginated = $query->paginate(10);

        $stats = [
            'total_students' => Student::count(),
            'pending_uploads' => DocumentUpload::where('status', 'pending')->count(),
            'approved_uploads' => DocumentUpload::where('status', 'approved')->count(),
            'rejected_uploads' => DocumentUpload::where('status', 'rejected')->count(),
            'active_checkers' => ClearanceChecker::count()
        ];

        // Get unique grade levels for filter dropdown
        $gradeLevels = Student::distinct()->pluck('grade_level')->sort()->values();

        // Format students data and apply status filter
        $statusFilter = request('status');
        $students = $studentsPaginated->map(function($student) {
            $approvedCount = $student->uploads->where('status', 'approved')->count();
            $requiredDocuments = 6; // Total required documents
            
            return [
                'id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
                'email' => $student->user->email ?? 'N/A',
                'reg_no' => $student->reg_no,
                'program' => 'Grade ' . ($student->grade_level ?? 'N/A'),
                'grade_level' => $student->grade_level,
                'cleared_depts' => $approvedCount,
                'total_depts' => $requiredDocuments,
                'uploads' => $student->uploads,
                'status' => $approvedCount === $requiredDocuments ? 'cleared' : ($approvedCount > 0 ? 'partial' : 'pending')
            ];
        })->when($statusFilter, function($collection) use ($statusFilter) {
            return $collection->filter(function($student) use ($statusFilter) {
                return $student['status'] === $statusFilter;
            });
        });

        return view('clearanceport.admin.student', [
            'admin' => $admin,
            'stats' => $stats,
            'students' => $students,
            'gradeLevels' => $gradeLevels,
            'search' => $search,
            'selectedGrade' => $gradeLevel,
            'selectedStatus' => $statusFilter,
            'currentTab' => 'students'
        ]);
    }

    public function departments(): View
    {
        $admin = $this->getAdminUser();

        $stats = [
            'total_students' => Student::count(),
            'pending_uploads' => DocumentUpload::where('status', 'pending')->count(),
            'approved_uploads' => DocumentUpload::where('status', 'approved')->count(),
            'rejected_uploads' => DocumentUpload::where('status', 'rejected')->count(),
            'active_checkers' => ClearanceChecker::count()
        ];

        return view('clearanceport.admin.departments', [
            'admin' => $admin,
            'stats' => $stats,
            'currentTab' => 'departments'
        ]);
    }

    public function show($id): View
    {
        $admin = $this->getAdminUser();
        $student = Student::with('user', 'uploads')->findOrFail($id);

        $stats = [
            'total_students' => Student::count(),
            'pending_uploads' => DocumentUpload::where('status', 'pending')->count(),
            'approved_uploads' => DocumentUpload::where('status', 'approved')->count(),
            'rejected_uploads' => DocumentUpload::where('status', 'rejected')->count(),
            'active_checkers' => ClearanceChecker::count()
        ];

        return view('clearanceport.admin.student-detail', [
            'admin' => $admin,
            'stats' => $stats,
            'student' => $student,
            'currentTab' => 'students'
        ]);
    }

    public function certificate($id)
    {
        $student = Student::findOrFail($id);
        
        // Generate and return certificate PDF (placeholder)
        return redirect()->back()->with('success', 'Certificate generated for ' . $student->user->name);
    }

    public function dashboard(): View
    {
        return $this->overview();
    }
}
