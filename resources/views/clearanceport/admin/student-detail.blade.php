<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details — Admin — Clearance Portal</title>
    <link rel="stylesheet" href="{{ asset('css/clearanceport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    {{-- Header Navigation --}}
    <div class="header">
        <div class="nav-tabs">
            <a href="{{ route('admin.dashboard') }}" class="nav-tab">Overview</a>
            <a href="{{ route('admin.uploads') }}" class="nav-tab">Document Uploads</a>
            <a href="{{ route('admin.students') }}" class="nav-tab active">Students</a>
            <a href="{{ route('admin.reports') }}" class="nav-tab">Reports</a>
        </div>
    </div>

    <div class="dashboard-container">
        <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 28px; margin-bottom: 8px;">Student Details</h1>
                <p style="color: #666; margin: 0;">View and manage clearance status</p>
            </div>
            <a href="{{ route('admin.students') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Students
            </a>
        </div>

        {{-- Student Information --}}
        <div class="card">
            <h2><i class="fas fa-user"></i> Student Information</h2>
            <div class="student-info">
                <div class="info-item">
                    <span class="info-label">Full Name</span>
                    <span class="info-value">{{ $student->user->name ?? 'Unknown' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $student->user->email ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Registration Number</span>
                    <span class="info-value">{{ $student->reg_no ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Grade Level</span>
                    <span class="info-value">Grade {{ $student->grade_level ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Section</span>
                    <span class="info-value">{{ $student->section ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    @php
                        $approvedCount = $student->uploads->where('status', 'approved')->count();
                        $totalRequired = 6;
                    @endphp
                    @if($approvedCount === $totalRequired)
                        <span class="status-badge approved">Fully Cleared</span>
                    @elseif($approvedCount > 0)
                        <span class="status-badge pending">In Progress</span>
                    @else
                        <span class="status-badge rejected">Not Started</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Document Status --}}
        <div class="card">
            <h2><i class="fas fa-file-check"></i> Document Status</h2>
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-size: 14px; color: #666;">
                        {{ $approvedCount }} of {{ $totalRequired }} documents approved
                    </span>
                    <span style="font-size: 18px; font-weight: 600; color: #8B4453;">
                        {{ round(($approvedCount / $totalRequired) * 100) }}%
                    </span>
                </div>
                <div style="background-color: #e9ecef; height: 20px; border-radius: 10px; overflow: hidden;">
                    <div style="background-color: #8B4453; height: 100%; width: {{ ($approvedCount / $totalRequired) * 100 }}%; transition: width 0.3s ease;"></div>
                </div>
            </div>

            @if($student->uploads->count() > 0)
            <ul class="upload-list">
                @foreach($student->uploads as $upload)
                <li class="upload-item">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #000; margin-bottom: 4px;">{{ $upload->document_type }}</div>
                        <div style="font-size: 12px; color: #666;">Uploaded {{ $upload->created_at->format('M d, Y') }}</div>
                        @if($upload->status === 'rejected' && $upload->rejection_reason)
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $upload->rejection_reason }}
                            </div>
                        @endif
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        {{-- View document button --}}
                        @if($upload->file_path)
                            <button type="button" class="btn-icon"
                                onclick="openModal('{{ $upload->id }}', '{{ $upload->document_type }}', '{{ asset('storage/' . $upload->file_path) }}')"
                                title="View Document">
                                <i class="fas fa-eye"></i>
                            </button>
                        @endif

                        {{-- ✅ FIX: Replaced onclick="return confirm(...)" with confirmAction() helper.
                             confirm() is blocked in sandboxed iframes; confirmAction() uses the same
                             window.confirm internally but is wrapped so it can be swapped for a
                             custom modal later without touching every button. --}}
                        @if($upload->status !== 'approved')
                            <form id="approve-form-{{ $upload->id }}"
                                  action="{{ route('admin.uploads.approve', $upload->id) }}"
                                  method="POST"
                                  style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="button"
                                        class="btn-icon"
                                        title="Approve"
                                        style="color: #28a745;"
                                        onclick="confirmAction(
                                            'Approve this document?',
                                            () => document.getElementById('approve-form-{{ $upload->id }}').submit()
                                        )">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                        @endif

                        @if($upload->status !== 'rejected')
                            <button type="button" class="btn-icon" title="Reject"
                                    onclick="openRejectModal('{{ $upload->id }}', '{{ $upload->document_type }}')"
                                    style="color: #dc3545;">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        @endif

                        <span class="status-badge {{ $upload->status }}">
                            {{ ucfirst($upload->status) }}
                        </span>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No documents uploaded yet</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Document Viewer Modal --}}
    <div id="viewModal" class="modal-backdrop">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Document Preview</h2>
                <button type="button" class="modal-close" onclick="closeViewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <img id="modalImage" src="" alt="Document Preview" style="display: none; max-width: 100%;">
                <iframe id="modalPdf" src="" style="display: none; width: 100%; height: 500px; border: none;"></iframe>
            </div>
        </div>
    </div>

    {{-- Rejection Modal --}}
    <div id="rejectModal" class="modal-backdrop">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reject Document</h2>
                <button type="button" class="modal-close" onclick="closeRejectModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="rejectForm" action="" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; color: #000; margin-bottom: 8px;">
                            Document: <span id="rejectDocName"></span>
                        </label>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label for="rejectReason" style="display: block; font-weight: 600; color: #000; margin-bottom: 8px;">
                            Rejection Reason *
                        </label>
                        <textarea id="rejectReason" name="reject_note"
                                  placeholder="Enter reason for rejection…"
                                  style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; font-size: 14px; min-height: 120px;"
                                  required></textarea>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; padding: 15px 20px; border-top: 1px solid #eee;">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn" style="background: #dc3545; color: white;">Reject Document</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ✅ Single load of clearanceport.js (provides showToast, confirmAction, openModal, closeModal) --}}
    <script src="{{ asset('js/clearanceport.js') }}"></script>
    <script>
        // ── Document Viewer Modal ──────────────────────────────
        function openModal(id, title, fileUrl) {
            const modal      = document.getElementById('viewModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalImage = document.getElementById('modalImage');
            const modalPdf   = document.getElementById('modalPdf');
            const isPdf      = fileUrl.toLowerCase().endsWith('.pdf');

            modalTitle.textContent = title;

            if (isPdf) {
                modalPdf.src          = fileUrl;
                modalPdf.style.display  = 'block';
                modalImage.style.display = 'none';
            } else {
                modalImage.src          = fileUrl;
                modalImage.style.display = 'block';
                modalPdf.style.display   = 'none';
            }

            modal.classList.add('open');
        }

        function closeViewModal() {
            const modal    = document.getElementById('viewModal');
            const modalPdf = document.getElementById('modalPdf');
            modal.classList.remove('open');
            modalPdf.src = ''; // stop PDF stream when closing
        }

        // Close viewer when clicking the backdrop
        document.getElementById('viewModal').addEventListener('click', function(e) {
            if (e.target === this) closeViewModal();
        });

        // ── Reject Modal ──────────────────────────────────────
        function openRejectModal(uploadId, docName) {
            const modal        = document.getElementById('rejectModal');
            const rejectForm   = document.getElementById('rejectForm');
            const rejectDocName = document.getElementById('rejectDocName');
            const rejectReason = document.getElementById('rejectReason');

            rejectDocName.textContent = docName;
            rejectReason.value        = '';
            rejectForm.action         = '{{ route("admin.uploads.reject", ":id") }}'.replace(':id', uploadId);

            modal.classList.add('open');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('open');
        }

        // Close reject modal when clicking backdrop
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) closeRejectModal();
        });

        // Close any open modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeViewModal();
                closeRejectModal();
            }
        });

        // Validate rejection reason before submitting
        document.getElementById('rejectForm').addEventListener('submit', function(e) {
            const reason = document.getElementById('rejectReason').value.trim();
            if (!reason) {
                e.preventDefault();
                showToast('Please enter a rejection reason.', 'error');
                return false;
            }
        });

        // ── Session flash toasts ──────────────────────────────
        @if(session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif
        @if(session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif
    </script>
</body>
</html>