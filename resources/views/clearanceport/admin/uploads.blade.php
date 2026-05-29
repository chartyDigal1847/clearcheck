{{-- resources/views/clearanceport/admin/uploads.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Uploads — Admin — Deor &amp; Dune High School</title>
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
        <a href="{{ route('admin.uploads') }}" class="nav-tab active" style="padding:15px 25px;background:none;border:none;cursor:pointer;font-size:15px;color:#000;text-decoration:none;font-weight:600;position:relative;transition:color 0.3s ease;">Document Uploads</a>
        <a href="{{ route('admin.students') }}" class="nav-tab" style="padding:15px 25px;background:none;border:none;cursor:pointer;font-size:15px;color:#666;text-decoration:none;font-weight:500;position:relative;transition:color 0.3s ease;">Students</a>
        <a href="{{ route('admin.reports') }}" class="nav-tab" style="padding:15px 25px;background:none;border:none;cursor:pointer;font-size:15px;color:#666;text-decoration:none;font-weight:500;position:relative;transition:color 0.3s ease;">Reports</a>
    </div>
</div>

<div class="dashboard-container" style="max-width:1400px;margin:0 auto;padding:30px 40px;">
    <div class="page-body">
            <h1 class="page-heading">Document Uploads</h1>
            <p class="page-subheading">Review, approve, or reject all submitted student documents</p>

            {{-- Filter Bar --}}
            <div class="card mb-4">
                <div class="card-body" style="padding:14px 20px;">
                    <form method="GET" action="{{ route('admin.uploads') }}">
                        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                            <div class="form-group" style="margin:0;flex:1;min-width:180px;">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Student name, reg no…" value="{{ request('search') }}">
                            </div>
                            <div class="form-group" style="margin:0;min-width:140px;">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;min-width:160px;">
                                <label class="form-label">Grade Level</label>
                                <select name="grade_level" class="form-control">
                                    <option value="">All Grades</option>
                                    @foreach($gradeLevels ?? [] as $grade)
                                        <option value="{{ $grade }}" {{ request('grade_level') === $grade ? 'selected' : '' }}>Grade {{ $grade }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn" style="background:#8B4453;color:white;margin-bottom:0;">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.uploads') }}" class="btn btn-ghost">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary badges --}}
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
                <span class="badge badge-neutral"><i class="fas fa-file"></i> Total: {{ count($uploads ?? []) }}</span>
                <span class="badge badge-pending"><i class="fas fa-clock"></i> Pending: {{ collect($uploads ?? [])->where('status','pending')->count() }}</span>
                <span class="badge badge-success"><i class="fas fa-check"></i> Approved: {{ collect($uploads ?? [])->where('status','approved')->count() }}</span>
                <span class="badge badge-danger"><i class="fas fa-times"></i> Rejected: {{ collect($uploads ?? [])->where('status','rejected')->count() }}</span>
            </div>

            <div class="card">
                <div class="table-wrap">
                    @if(count($uploads ?? []) > 0)
                    <table class="data-table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Reg No</th>
                                <th>Grade</th>
                                <th>Section</th>
                                <th>Approved</th>
                                <th>Pending</th>
                                <th>Rejected</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $groupedByStudent = collect($uploads ?? [])->groupBy('student_reg');
                            @endphp
                            @foreach($groupedByStudent as $regNo => $studentDocs)
                                @php
                                    $firstDoc = $studentDocs->first();
                                    $approvedCount = $studentDocs->where('status', 'approved')->count();
                                    $pendingCount = $studentDocs->where('status', 'pending')->count();
                                    $rejectedCount = $studentDocs->where('status', 'rejected')->count();
                                    $studentId = $firstDoc['student_id'] ?? null;
                                @endphp
                                <tr>
                                    <td style="font-weight:600;font-size:.87rem;">{{ $firstDoc['student_name'] }}</td>
                                    <td style="font-size:.78rem;color:var(--burgundy);font-weight:600;">{{ $regNo }}</td>
                                    <td style="font-size:.82rem;">{{ $firstDoc['grade_level'] ?? 'N/A' }}</td>
                                    <td style="font-size:.82rem;">{{ $firstDoc['section'] ?? 'N/A' }}</td>
                                    <td style="font-size:.82rem;">
                                        <span class="badge badge-success" style="font-size:.75rem;">
                                            <i class="fas fa-check"></i> {{ $approvedCount }}
                                        </span>
                                    </td>
                                    <td style="font-size:.82rem;">
                                        <span class="badge badge-pending" style="font-size:.75rem;">
                                            <i class="fas fa-clock"></i> {{ $pendingCount }}
                                        </span>
                                    </td>
                                    <td style="font-size:.82rem;">
                                        <span class="badge badge-danger" style="font-size:.75rem;">
                                            <i class="fas fa-times"></i> {{ $rejectedCount }}
                                        </span>
                                    </td>
                                    <td style="font-size:.82rem;">
                                        <a href="{{ route('admin.students.show', $studentId) }}" class="btn btn-ghost btn-xs" title="View All Documents" style="cursor:pointer;background:#8B4453;color:white;border:none;padding:6px 12px;border-radius:4px;text-decoration:none;display:inline-block;">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div style="padding:60px 20px;text-align:center;background:#fafafa;border-top:1px solid #e5e5e5;">
                        <i class="fas fa-inbox" style="font-size:48px;color:#ccc;margin-bottom:15px;display:block;"></i>
                        <h3 style="color:#666;margin:0 0 8px 0;font-size:18px;">No Documents Found</h3>
                        @if(request('search'))
                            <p style="color:#999;margin:0 0 15px 0;">
                                No documents match the search "<strong>{{ request('search') }}</strong>"
                            </p>
                        @endif
                        @if(request('grade_level'))
                            <p style="color:#999;margin:0 0 15px 0;">
                                No documents found in Grade {{ request('grade_level') }}
                            </p>
                        @endif
                        @if(request('status'))
                            <p style="color:#999;margin:0 0 15px 0;">
                                No documents with status "<strong>{{ ucfirst(request('status')) }}</strong>"
                            </p>
                        @endif
                        @if(!request('search') && !request('grade_level') && !request('status'))
                            <p style="color:#999;margin:0 0 15px 0;">
                                No documents uploaded yet
                            </p>
                        @endif
                        <a href="{{ route('admin.uploads') }}" class="btn" style="background:#8B4453;color:white;display:inline-block;margin-top:10px;border:none;padding:10px 20px;border-radius:4px;text-decoration:none;cursor:pointer;">
                            <i class="fas fa-redo"></i> Clear Filters
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<script src="{{ asset('js/clearanceport.js') }}"></script>
<script>
@if(session('success')) 
    window.addEventListener('load', function() {
        if (typeof showToast === 'function') {
            showToast('{{ session('success') }}', 'success');
        }
    });
@endif

@if(session('error'))   
    window.addEventListener('load', function() {
        if (typeof showToast === 'function') {
            showToast('{{ session('error') }}', 'error');
        }
    });
@endif
</script>
</body>
</html>