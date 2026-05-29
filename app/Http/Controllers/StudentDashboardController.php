<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\DocumentUpload;
use App\Services\ActivityLogger;
use App\Services\ClearanceValidationService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StudentDashboardController extends Controller
{
    // Required documents for clearance
    private $requiredDocuments = [
        'Library Clearance Slip',
        'No Overdue Books Proof',
        'Official Receipt',
        'Balance Clearance',
        'Report Card Copy',
        'Enrollment/Exam Permit'
    ];

    public function __construct(private readonly ClearanceValidationService $validationService) {}

    private function getStudent()
    {
        $ssoId    = session('sso_id');
        $ssoEmail = session('sso_email');

        $student = Student::with('uploads')
            ->where(function ($q) use ($ssoId, $ssoEmail) {
                if ($ssoId) {
                    $q->where('deoris_user_id', $ssoId);
                }
                if ($ssoEmail) {
                    $q->orWhere('student_email', strtolower($ssoEmail));
                }
            })
            ->first();

        if (!$student) {
            // Fallback structure — student exists in portal but not yet in ClearCheck DB
            return (object)[
                'id'               => null,
                'user'             => (object)[
                    'name'  => session('sso_name', 'Student'),
                    'email' => $ssoEmail,
                ],
                'reg_no'           => 'N/A',
                'grade_level'      => 'N/A',
                'section'          => 'N/A',
                'clearance_status' => 'pending',
                'completed_steps'  => 0,
                'total_steps'      => 6,
                'uploads'          => collect([]),
            ];
        }

        return $student;
    }

    // ✅ Helper: count approved uploads live from the collection
    private function completedSteps($uploads): int
    {
        return $uploads->where('status', 'approved')->count();
    }

    public function clearanceStatus(): View
    {
        $student = $this->getStudent();

        $studentData = [
            'id'          => $student->id,
            'name'        => $student->user->name ?? 'Student',
            'email'       => $student->user->email ?? 'student@deor.edu',
            'reg_no'      => $student->reg_no,
            'grade_level' => $student->grade_level,
            'section'     => $student->section,
            'role'        => 'student',
        ];

        // Pull module-level clearance status from the validation service
        $moduleStatus = $student->id
            ? $this->validationService->getClearanceStatus(
                Student::find($student->id) ?? new Student()
              )
            : null;

        $clearance_status = [
            'overall'            => $moduleStatus['status'] ?? $student->clearance_status ?? 'pending',
            'completed_steps'    => $this->completedSteps($student->uploads),
            'total_steps'        => 6,
            'required_documents' => $this->requiredDocuments,
            'progress_percentage'=> $moduleStatus['progress_percentage'] ?? 0,
            'modules_cleared'    => $moduleStatus['modules_cleared']     ?? 0,
            'modules_total'      => $moduleStatus['modules_total']       ?? 4,
            'module_validations' => $moduleStatus['module_validations']  ?? [],
            'last_validated_at'  => $moduleStatus['last_validated_at']   ?? null,
        ];

        ActivityLogger::info('student.view.clearance', [], Student::class, $student->id);

        return view('clearanceport.dashboards.student', [
            'student'          => $studentData,
            'uploads'          => $student->uploads,
            'clearance_status' => $clearance_status,
            'currentTab'       => 'clearance',
        ]);
    }

    /**
     * POST /student/validate
     * Student-initiated validation refresh — queues a new validation cycle.
     */
    public function requestValidation(): RedirectResponse
    {
        $ssoEmail = session('sso_email');
        $student = Student::findByPortalId(session('sso_id'))
            ?? Student::findByPortalEmail($ssoEmail);

        if (!$student) {
            return redirect()->route('student.clearance')->with('error', 'Student record not found.');
        }

        $this->validationService->initiateValidation($student, $ssoEmail);

        ActivityLogger::info('student.validation.requested', [], Student::class, $student->id);

        return redirect()->route('student.clearance')
            ->with('success', 'Validation request submitted. Results will update shortly.');
    }

    public function documents(): View
    {
        $student = $this->getStudent();

        $studentData = [
            'id'          => $student->id,
            'name'        => $student->user->name ?? 'Student',
            'email'       => $student->user->email ?? 'student@deor.edu',
            'reg_no'      => $student->reg_no,
            'grade_level' => $student->grade_level,
            'section'     => $student->section,
            'role'        => 'student',
        ];

        $clearance_status = [
            'overall'            => $student->clearance_status ?? 'in_progress',
            'completed_steps'    => $this->completedSteps($student->uploads), // ✅ live count
            'total_steps'        => 6,
            'required_documents' => $this->requiredDocuments,
        ];

        return view('clearanceport.dashboards.student-documents', [
            'student'           => $studentData,
            'uploads'           => $student->uploads,
            'clearance_status'  => $clearance_status,
            'requiredDocuments' => $this->requiredDocuments,
            'currentTab'        => 'documents',
        ]);
    }

    public function uploadDocument(): RedirectResponse
    {
        try {
            $validated = request()->validate([
                'document_type' => 'required|string|in:' . implode(',', $this->requiredDocuments),
                'file'          => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
            ]);

            $student = $this->getStudent();

            if (!$student || !isset($student->id)) {
                return redirect()->route('student.documents')->with('error', 'Student not found');
            }

            // Store file
            $path = request()->file('file')->store('documents/' . $student->id, 'public');

            // ✅ Update existing record instead of always creating a new one.
            //    This ensures the status shown to the student reflects the latest submission.
            $existing = DocumentUpload::where('student_id', $student->id)
                ->where('document_type', $validated['document_type'])
                ->latest()
                ->first();

            if ($existing) {
                $existing->update([
                    'file_path'        => $path,
                    'status'           => 'pending',
                    'rejection_reason' => null, // clear old rejection reason
                ]);
            } else {
                DocumentUpload::create([
                    'student_id'    => $student->id,
                    'document_type' => $validated['document_type'],
                    'file_path'     => $path,
                    'status'        => 'pending',
                ]);
            }

            return redirect()->route('student.documents')->with('success', 'Document uploaded successfully! Pending review.');
        } catch (\Exception $e) {
            return redirect()->route('student.documents')->with('error', 'Error uploading document: ' . $e->getMessage());
        }
    }

    public function library(): View
    {
        $student = $this->getStudent();

        $studentData = [
            'id'          => $student->id,
            'name'        => $student->user->name ?? 'Student',
            'email'       => $student->user->email ?? 'student@deor.edu',
            'reg_no'      => $student->reg_no,
            'grade_level' => $student->grade_level,
            'section'     => $student->section,
            'role'        => 'student',
        ];

        $clearance_status = [
            'overall'         => $student->clearance_status ?? 'in_progress',
            'completed_steps' => $this->completedSteps($student->uploads), // ✅ live count
            'total_steps'     => 6,
        ];

        // Get library-specific uploads
        $libraryUploads = $student->uploads->whereIn('document_type', ['Library Clearance Slip', 'No Overdue Books Proof']);

        return view('clearanceport.dashboards.student-library', [
            'student'          => $studentData,
            'uploads'          => $libraryUploads,
            'clearance_status' => $clearance_status,
            'currentTab'       => 'library',
        ]);
    }

    public function finance(): View
    {
        $student = $this->getStudent();

        $studentData = [
            'id'          => $student->id,
            'name'        => $student->user->name ?? 'Student',
            'email'       => $student->user->email ?? 'student@deor.edu',
            'reg_no'      => $student->reg_no,
            'grade_level' => $student->grade_level,
            'section'     => $student->section,
            'role'        => 'student',
        ];

        $clearance_status = [
            'overall'         => $student->clearance_status ?? 'in_progress',
            'completed_steps' => $this->completedSteps($student->uploads), // ✅ live count
            'total_steps'     => 6,
        ];

        // Get finance-specific uploads
        $financeUploads = $student->uploads->whereIn('document_type', ['Official Receipt', 'Balance Clearance']);

        return view('clearanceport.dashboards.student-finance', [
            'student'          => $studentData,
            'uploads'          => $financeUploads,
            'clearance_status' => $clearance_status,
            'currentTab'       => 'finance',
        ]);
    }

    public function academic(): View
    {
        $student = $this->getStudent();

        $studentData = [
            'id'          => $student->id,
            'name'        => $student->user->name ?? 'Student',
            'email'       => $student->user->email ?? 'student@deor.edu',
            'reg_no'      => $student->reg_no,
            'grade_level' => $student->grade_level,
            'section'     => $student->section,
            'role'        => 'student',
        ];

        $clearance_status = [
            'overall'         => $student->clearance_status ?? 'in_progress',
            'completed_steps' => $this->completedSteps($student->uploads), // ✅ live count
            'total_steps'     => 6,
        ];

        // Get academic-specific uploads
        $academicUploads = $student->uploads->whereIn('document_type', ['Report Card Copy', 'Enrollment/Exam Permit']);

        return view('clearanceport.dashboards.student-academic', [
            'student'          => $studentData,
            'uploads'          => $academicUploads,
            'clearance_status' => $clearance_status,
            'currentTab'       => 'academic',
        ]);
    }

    public function certificate(): View
    {
        $student = $this->getStudent();

        $studentData = [
            'id'          => $student->id,
            'name'        => $student->user->name ?? 'Student',
            'email'       => $student->user->email ?? 'student@deor.edu',
            'reg_no'      => $student->reg_no,
            'grade_level' => $student->grade_level,
            'section'     => $student->section,
            'role'        => 'student',
        ];

        $clearance_status = [
            'overall'         => $student->clearance_status ?? 'in_progress',
            'completed_steps' => $this->completedSteps($student->uploads), // ✅ live count
            'total_steps'     => 6,
        ];

        return view('clearanceport.dashboards.student-certificate', [
            'student'          => $studentData,
            'uploads'          => $student->uploads,
            'clearance_status' => $clearance_status,
            'currentTab'       => 'certificate',
        ]);
    }
}