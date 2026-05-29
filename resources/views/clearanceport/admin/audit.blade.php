<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log — Admin — ClearCheck</title>
    <link rel="stylesheet" href="{{ asset('css/clearanceport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background:#f5f5f5; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; margin:0; }
        .header { background:white; box-shadow:0 2px 4px rgba(0,0,0,.1); }
        .nav-tabs { display:flex; padding:0 40px; border-bottom:2px solid #e5e5e5; }
        .nav-tab { padding:15px 25px; background:none; border:none; cursor:pointer; font-size:15px; color:#666; text-decoration:none; font-weight:500; position:relative; }
        .nav-tab.active { color:#000; font-weight:600; }
        .nav-tab.active::after { content:''; position:absolute; bottom:-2px; left:0; right:0; height:3px; background:#8B4453; }
        .container { max-width:1400px; margin:0 auto; padding:30px 40px; }
        .page-title { font-size:22px; font-weight:700; color:#000; margin-bottom:6px; }
        .page-sub   { font-size:14px; color:#666; margin-bottom:24px; }
        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
        .filter-bar input, .filter-bar select { padding:8px 12px; border:1px solid #ddd; border-radius:6px; font-size:13px; }
        .filter-bar button { padding:8px 18px; background:#8B4453; color:white; border:none; border-radius:6px; font-size:13px; cursor:pointer; }
        .audit-table { width:100%; border-collapse:collapse; background:white; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.1); }
        .audit-table th { background:#f8f9fa; padding:12px 14px; text-align:left; font-size:12px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #dee2e6; }
        .audit-table td { padding:11px 14px; font-size:13px; color:#333; border-bottom:1px solid #f0f0f0; vertical-align:top; }
        .audit-table tr:hover td { background:#fafafa; }
        .level-badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:700; text-transform:uppercase; }
        .level-info     { background:#cfe2ff; color:#084298; }
        .level-warning  { background:#fff3cd; color:#856404; }
        .level-critical { background:#f8d7da; color:#721c24; }
        .action-text { font-family:monospace; font-size:12px; background:#f8f9fa; padding:2px 6px; border-radius:3px; }
        .empty-state { text-align:center; padding:60px; color:#999; }
    </style>
</head>
<body>
<div class="header">
    <div class="nav-tabs">
        <a href="{{ route('admin.dashboard') }}" class="nav-tab">Overview</a>
        <a href="{{ route('admin.uploads') }}" class="nav-tab">Document Uploads</a>
        <a href="{{ route('admin.students') }}" class="nav-tab">Students</a>
        <a href="{{ route('admin.reports') }}" class="nav-tab">Reports</a>
        <a href="{{ route('admin.audit') }}" class="nav-tab active">Audit Log</a>
    </div>
</div>

<div class="container">
    <div class="page-title"><i class="fas fa-shield-alt" style="color:#8B4453;margin-right:8px;"></i>Audit Monitoring</div>
    <div class="page-sub">System-wide activity log — all actions, API access, and security events.</div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.audit') }}" class="filter-bar">
        <input type="text" name="actor" placeholder="Actor (email)" value="{{ request('actor') }}">
        <input type="text" name="action" placeholder="Action keyword" value="{{ request('action') }}">
        <select name="level">
            <option value="">All Levels</option>
            <option value="info"     {{ request('level') === 'info'     ? 'selected' : '' }}>Info</option>
            <option value="warning"  {{ request('level') === 'warning'  ? 'selected' : '' }}>Warning</option>
            <option value="critical" {{ request('level') === 'critical' ? 'selected' : '' }}>Critical</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}" title="From date">
        <input type="date" name="to"   value="{{ request('to') }}"   title="To date">
        <button type="submit"><i class="fas fa-search"></i> Filter</button>
        <a href="{{ route('admin.audit') }}" style="padding:8px 14px;background:#e9ecef;color:#333;border-radius:6px;text-decoration:none;font-size:13px;">Reset</a>
    </form>

    @if($logs->isEmpty())
        <div class="empty-state"><i class="fas fa-clipboard-list" style="font-size:48px;display:block;margin-bottom:16px;"></i>No audit records found.</div>
    @else
        <table class="audit-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Actor</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Subject</th>
                    <th>Level</th>
                    <th>IP</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td style="white-space:nowrap;font-size:12px;color:#666;">
                        {{ $log->occurred_at->format('M d, Y H:i:s') }}
                    </td>
                    <td style="font-size:13px;">{{ $log->actor ?? '—' }}</td>
                    <td style="font-size:12px;color:#666;">{{ $log->actor_role ?? '—' }}</td>
                    <td><span class="action-text">{{ $log->action }}</span></td>
                    <td style="font-size:12px;color:#666;">
                        @if($log->subject_type && $log->subject_id)
                            {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <span class="level-badge level-{{ $log->level }}">{{ $log->level }}</span>
                    </td>
                    <td style="font-size:12px;color:#666;">{{ $log->ip_address ?? '—' }}</td>
                    <td style="font-size:12px;color:#666;">{{ $log->method ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:16px;">
            {{ $logs->withQueryString()->links() }}
        </div>
    @endif
</div>
</body>
</html>
