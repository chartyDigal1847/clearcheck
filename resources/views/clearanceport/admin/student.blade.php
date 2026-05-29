{{-- resources/views/clearanceport/admin/students.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students — Admin — Deor &amp; Dune High School</title>
    <link rel="stylesheet" href="{{ asset('css/clearanceport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- ✅ FIX: Removed duplicate <script src="clearanceport.js"> that was here in <head>.
         The script is loaded once at the bottom of <body> instead. --}}
</head>
<body>
<div id="toast-stack" class="toast-stack"></div>

{{-- Header Navigation --}}
<div class="header" style="background:white;box-shadow:0 2px 4px rgba(0,0,0,0.1);padding:0;">
    <div class="nav-tabs" style="display:flex;padding:0 40px;border-bottom:2px solid #e5e5e5;gap:0;">
        <a href="{{ route('admin.dashboard') }}" class="nav-tab" style="padding:15px 25px;background:none;border:none;cursor:pointer;font-size:15px;color:#666;text-decoration:none;font-weight:500;position:relative;transition:color 0.3s ease;">Overview</a>
        <a href="{{ route('admin.uploads') }}" class="nav-tab" style="padding:15px 25px;background:none;border:none;cursor:pointer;font-size:15px;color:#666;text-decoration:none;font-weight:500;position:relative;transition:color 0.3s ease;">Document Uploads</a>
        <a href="{{ route('admin.students') }}" class="nav-tab active" style="padding:15px 25px;background:none;border:none;cursor:pointer;font-size:15px;color:#000;text-decoration:none;font-weight:600;position:relative;transition:color 0.3s ease;">Students</a>
        <a href="{{ route('admin.reports') }}" class="nav-tab" style="padding:15px 25px;background:none;border:none;cursor:pointer;font-size:15px;color:#666;text-decoration:none;font-weight:500;position:relative;transition:color 0.3s ease;">Reports</a>
    </div>
</div>

<div class="dashboard-container" style="max-width:1400px;margin:0 auto;padding:30px 40px;">
    <div class="page-body">
            <div class="d-flex justify-between align-center mb-3">
                <div>
                    <h1 class="page-heading">Students</h1>
                    <p class="page-subheading">Manage all registered students and their clearance status</p>
                </div>
            </div>

            {{-- Search/Filter Row --}}
            <div class="card mb-4">
                <div class="card-body" style="padding:16px 22px;">
                    <form method="GET" action="{{ route('admin.students') }}" onsubmit="validateSearch(event)">
                        <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                            <div class="form-group" style="margin:0;flex:1;min-width:200px;">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" id="searchInput" class="form-control" placeholder="Name, reg no, email…"
                                       value="{{ request('search') }}">
                                <small id="searchError" style="color:#dc3545;display:none;margin-top:4px;">
                                    <i class="fas fa-exclamation-circle"></i> Search must be at least 2 characters
                                </small>
                            </div>
                            <div class="form-group" style="margin:0;min-width:160px;">
                                <label class="form-label">Grade Level</label>
                                <select name="grade" class="form-control">
                                    <option value="">All</option>
                                    @foreach($gradeLevels ?? [] as $level)
                                        <option value="{{ $level }}" {{ request('grade') === (string)$level ? 'selected' : '' }}>
                                            Grade {{ $level }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;min-width:160px;">
                                <label class="form-label">Clearance Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="cleared"  {{ request('status') === 'cleared'  ? 'selected' : '' }}>Fully Cleared</option>
                                    <option value="partial"  {{ request('status') === 'partial'  ? 'selected' : '' }}>In Progress</option>
                                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Not Started</option>
                                </select>
                            </div>
                            <button type="submit" class="btn" style="background:#8B4453;color:white;margin-bottom:0;">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.students') }}" class="btn btn-ghost" style="margin-bottom:0;">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            @if(request('search') && strlen(request('search')) < 2)
                <div style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:12px;border-radius:4px;margin-bottom:15px;">
                    <i class="fas fa-info-circle"></i> Search must be at least 2 characters long
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <span class="card-header-title"><i class="fas fa-users" style="color:#8B4453;"></i> All Students</span>
                    <span class="badge badge-neutral">{{ count($students ?? []) }} students</span>
                </div>
                
                @if(count($students ?? []) > 0)
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Reg No</th>
                                <th>Programme</th>
                                <th>Cleared</th>
                                <th>Progress</th>
                                <th>Overall Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students ?? [] as $i => $student)
                                @php
                                    $pct = $student['cleared_depts'] > 0
                                        ? round($student['cleared_depts'] / $student['total_depts'] * 100)
                                        : 0;
                                    $allClear = $pct === 100;
                                @endphp
                                <tr>
                                    <td style="color:var(--ink-light);font-size:.75rem;">{{ $i + 1 }}</td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div class="user-avatar" style="background:var(--parchment-dark);color:var(--burgundy);width:34px;height:34px;font-size:.8rem;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">
                                                {{ strtoupper(substr($student['name'],0,2)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight:600;font-size:.87rem;">{{ $student['name'] }}</div>
                                                <div style="font-size:.72rem;color:var(--ink-light);">{{ $student['email'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-weight:600;font-size:.82rem;color:var(--burgundy);">{{ $student['reg_no'] }}</td>
                                    <td style="font-size:.82rem;">{{ $student['program'] }}</td>
                                    <td style="font-size:.82rem;">{{ $student['cleared_depts'] }} / {{ $student['total_depts'] }}</td>
                                    <td style="min-width:100px;">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div class="progress-track" style="flex:1;height:8px;">
                                                <div class="progress-fill" data-width="{{ $pct }}" style="width:0%;"></div>
                                            </div>
                                            <span style="font-size:.72rem;color:var(--ink-light);">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($allClear)
                                            <span class="badge badge-success"><i class="fas fa-check-double"></i> Cleared</span>
                                        @elseif($student['cleared_depts'] > 0)
                                            <span class="badge badge-pending"><i class="fas fa-spinner"></i> In Progress</span>
                                        @else
                                            <span class="badge badge-neutral"><i class="fas fa-clock"></i> Not Started</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:6px;">
                                            <a href="{{ route('admin.students.show', $student['id']) }}" class="btn btn-ghost btn-xs">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($allClear)
                                                <a href="{{ route('admin.students.certificate', $student['id']) }}" class="btn btn-xs" style="background:#8B4453;color:white;">
                                                    <i class="fas fa-certificate"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div style="padding:60px 20px;text-align:center;background:#fafafa;border-top:1px solid #e5e5e5;">
                    <i class="fas fa-inbox" style="font-size:48px;color:#ccc;margin-bottom:15px;display:block;"></i>
                    <h3 style="color:#666;margin:0 0 8px 0;font-size:18px;">No Students Found</h3>
                    @if(request('search'))
                        <p style="color:#999;margin:0 0 15px 0;">
                            No students match the search "<strong>{{ request('search') }}</strong>"
                        </p>
                    @endif
                    @if(request('grade'))
                        <p style="color:#999;margin:0 0 15px 0;">
                            No students found in Grade {{ request('grade') }}
                        </p>
                    @endif
                    @if(request('status'))
                        <p style="color:#999;margin:0 0 15px 0;">
                            No students with status "<strong>{{ ucfirst(request('status')) }}</strong>"
                        </p>
                    @endif
                    @if(!request('search') && !request('grade') && !request('status'))
                        <p style="color:#999;margin:0 0 15px 0;">
                            No students registered yet
                        </p>
                    @endif
                    <a href="{{ route('admin.students') }}" class="btn" style="background:#8B4453;color:white;display:inline-block;margin-top:10px;">
                        <i class="fas fa-redo"></i> Clear Filters
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ✅ Single load of clearanceport.js — only here, not in <head> --}}
<script src="{{ asset('js/clearanceport.js') }}"></script>
<script>
function validateSearch(event) {
    const searchInput = document.getElementById('searchInput').value.trim();
    const searchError = document.getElementById('searchError');
    
    if (searchInput && searchInput.length < 2) {
        event.preventDefault();
        searchError.style.display = 'block';
        return false;
    }
    
    searchError.style.display = 'none';
    return true;
}

document.getElementById('searchInput').addEventListener('input', function() {
    const searchError = document.getElementById('searchError');
    if (this.value.trim() && this.value.trim().length < 2) {
        searchError.style.display = 'block';
    } else {
        searchError.style.display = 'none';
    }
});

@if(session('success')) showToast('{{ session('success') }}', 'success'); @endif
</script>
</body>
</html>