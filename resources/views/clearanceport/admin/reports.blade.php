{{-- resources/views/clearanceport/admin/reports.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports — Admin — Deor &amp; Dune High School</title>
    <link rel="stylesheet" href="{{ asset('css/clearanceport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div id="toast-stack" class="toast-stack"></div>

{{-- Header Navigation --}}
<div class="header" style="background:white;box-shadow:0 2px 4px rgba(0,0,0,0.1);padding:0;">
    <div class="nav-tabs" style="display:flex;padding:0 40px;border-bottom:2px solid #e5e5e5;gap:0;">
        <a href="{{ route('admin.dashboard') }}" class="nav-tab" style="padding:15px 25px;background:none;border:none;cursor:pointer;font-size:15px;color:#666;text-decoration:none;font-weight:500;position:relative;transition:color 0.3s ease;">Overview</a>
        <a href="{{ route('admin.uploads') }}" class="nav-tab" style="padding:15px 25px;background:none;border:none;cursor:pointer;font-size:15px;color:#666;text-decoration:none;font-weight:500;position:relative;transition:color 0.3s ease;">Document Uploads</a>
        <a href="{{ route('admin.students') }}" class="nav-tab" style="padding:15px 25px;background:none;border:none;cursor:pointer;font-size:15px;color:#666;text-decoration:none;font-weight:500;position:relative;transition:color 0.3s ease;">Students</a>
        <a href="{{ route('admin.reports') }}" class="nav-tab active" style="padding:15px 25px;background:none;border:none;cursor:pointer;font-size:15px;color:#000;text-decoration:none;font-weight:600;position:relative;transition:color 0.3s ease;">Reports</a>
    </div>
</div>

<div class="dashboard-container" style="max-width:1400px;margin:0 auto;padding:30px 40px;">
    <div class="page-body">
            <h1 class="page-heading">System Reports</h1>
            <p class="page-subheading">Overview of clearance system statistics and performance</p>

            {{-- Summary Stats --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
                <div class="card">
                    <div class="card-body" style="text-align:center;padding:20px;">
                        <div style="font-size:2rem;font-weight:700;color:var(--burgundy);margin-bottom:8px;">
                            {{ $stats['total_students'] ?? 0 }}
                        </div>
                        <div style="font-size:.82rem;color:var(--ink-light);text-transform:uppercase;letter-spacing:.5px;">
                            Total Students
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align:center;padding:20px;">
                        <div style="font-size:2rem;font-weight:700;color:#666;margin-bottom:8px;">
                            {{ $stats['pending_uploads'] ?? 0 }}
                        </div>
                        <div style="font-size:.82rem;color:var(--ink-light);text-transform:uppercase;letter-spacing:.5px;">
                            Pending Reviews
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align:center;padding:20px;">
                        <div style="font-size:2rem;font-weight:700;color:#28a745;margin-bottom:8px;">
                            {{ $stats['approved_uploads'] ?? 0 }}
                        </div>
                        <div style="font-size:.82rem;color:var(--ink-light);text-transform:uppercase;letter-spacing:.5px;">
                            Approved
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align:center;padding:20px;">
                        <div style="font-size:2rem;font-weight:700;color:#dc3545;margin-bottom:8px;">
                            {{ $stats['rejected_uploads'] ?? 0 }}
                        </div>
                        <div style="font-size:.82rem;color:var(--ink-light);text-transform:uppercase;letter-spacing:.5px;">
                            Rejected
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body" style="text-align:center;padding:20px;">
                        <div style="font-size:2rem;font-weight:700;color:var(--burgundy);margin-bottom:8px;">
                            {{ $stats['active_checkers'] ?? 0 }}
                        </div>
                        <div style="font-size:.82rem;color:var(--ink-light);text-transform:uppercase;letter-spacing:.5px;">
                            Active Checkers
                        </div>
                    </div>
                </div>
            </div>

            {{-- Report Details --}}
            <div class="card">
                <div class="card-body">
                    <h2 style="font-size:1.1rem;font-weight:600;color:var(--ink);margin-bottom:20px;">
                        <i class="fas fa-chart-line"></i> Clearance Statistics
                    </h2>
                    <div style="display:grid;gap:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border);">
                            <span style="font-size:.9rem;color:var(--ink);">Total Uploads</span>
                            <span style="font-size:1.1rem;font-weight:600;color:var(--burgundy);">{{ $reportData['total_uploads'] ?? 0 }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border);">
                            <span style="font-size:.9rem;color:var(--ink);">Completion Rate</span>
                            <span style="font-size:1.1rem;font-weight:600;color:#28a745;">{{ $reportData['completion_rate'] ?? 0 }}%</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border);">
                            <span style="font-size:.9rem;color:var(--ink);">Average Review Time</span>
                            <span style="font-size:1.1rem;font-weight:600;color:var(--ink);">{{ $reportData['average_review_time'] ?? 'N/A' }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;">
                            <span style="font-size:.9rem;color:var(--ink);">Most Uploaded Document</span>
                            <span style="font-size:1.1rem;font-weight:600;color:var(--ink);">
                                {{ $reportData['top_document']->document_type ?? 'N/A' }}
                                @if(isset($reportData['top_document']->count))
                                    <span style="font-size:.8rem;color:var(--ink-light);">({{ $reportData['top_document']->count }})</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Office Performance --}}
            <div class="card">
                <div class="card-body">
                    <h2 style="font-size:1.1rem;font-weight:600;color:var(--ink);margin-bottom:20px;">
                        <i class="fas fa-building"></i> Office Performance
                    </h2>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;">
                        <div style="padding:16px;background:var(--bg-light);border-radius:8px;border-left:4px solid var(--burgundy);">
                            <div style="font-size:.82rem;color:var(--ink-light);margin-bottom:8px;">Library</div>
                            <div style="font-size:1.5rem;font-weight:700;color:var(--burgundy);">-</div>
                            <div style="font-size:.75rem;color:var(--ink-light);margin-top:4px;">Documents Reviewed</div>
                        </div>
                        <div style="padding:16px;background:var(--bg-light);border-radius:8px;border-left:4px solid #28a745;">
                            <div style="font-size:.82rem;color:var(--ink-light);margin-bottom:8px;">Finance</div>
                            <div style="font-size:1.5rem;font-weight:700;color:#28a745;">-</div>
                            <div style="font-size:.75rem;color:var(--ink-light);margin-top:4px;">Documents Reviewed</div>
                        </div>
                        <div style="padding:16px;background:var(--bg-light);border-radius:8px;border-left:4px solid #007bff;">
                            <div style="font-size:.82rem;color:var(--ink-light);margin-bottom:8px;">Exams & Records</div>
                            <div style="font-size:1.5rem;font-weight:700;color:#007bff;">-</div>
                            <div style="font-size:.75rem;color:var(--ink-light);margin-top:4px;">Documents Reviewed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/clearanceport.js') }}"></script>
</body>
</html>
