@extends('clearanceport.layouts.app')

@section('title', 'Student Dashboard — Clearance Portal')

@section('content')
<div class="module-topbar">
    <div>
        <h1 class="module-title">Clearance Status</h1>
        <p class="module-sub">Track your clearance progress — {{ now()->format('l, F j, Y') }}</p>
    </div>
    <div class="module-actions" aria-label="ClearCheck navigation">
        <a href="{{ route('student.clearance') }}" class="top-action active"><i class="fa-solid fa-tasks"></i> Status</a>
        <a href="{{ route('student.documents') }}" class="top-action"><i class="fa-solid fa-folder"></i> Documents</a>
        <a href="{{ route('student.library') }}" class="top-action"><i class="fa-solid fa-book"></i> Library</a>
        <a href="{{ route('student.finance') }}" class="top-action"><i class="fa-solid fa-dollar-sign"></i> Finance</a>
        <a href="{{ route('student.academic') }}" class="top-action"><i class="fa-solid fa-graduation-cap"></i> Academic</a>
    </div>
</div>

<section class="page active">
    @if(session('success'))
        <div class="privacy-notice" style="background:#d4edda;color:#155724;border-color:#c3e6cb;">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="privacy-notice" style="background:#f8d7da;color:#721c24;border-color:#f5c6cb;">
            <i class="fa-solid fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="dashboard-grid">
        <article class="card" style="grid-column: 1 / -1;">
            <div class="card-header">
                <h2><i class="fa-solid fa-network-wired"></i> Module Validations</h2>
                <form method="POST" action="{{ route('student.validate') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="fa-solid fa-rotate"></i> Refresh</button>
                </form>
            </div>
            <div class="card-body">
                @php
                    $moduleLabels = [
                        'enrollease'  => ['label' => 'EnrollEase',  'icon' => 'fa-user-check',    'color' => '#007bff'],
                        'assesspay'   => ['label' => 'AssessPay',   'icon' => 'fa-money-bill-wave','color' => '#28a745'],
                        'librarysys'  => ['label' => 'LibrarySys',  'icon' => 'fa-book',           'color' => '#6f42c1'],
                        'gradetrack'  => ['label' => 'GradeTrack',  'icon' => 'fa-graduation-cap', 'color' => '#fd7e14'],
                    ];
                    $moduleValidations = $clearance_status['module_validations'] ?? [];
                @endphp
                <div class="metrics-grid">
                    @foreach($moduleLabels as $key => $meta)
                        @php
                            $mv     = collect($moduleValidations)->firstWhere('module_key', $key);
                            $status = $mv['status'] ?? 'pending';
                            $colors = ['cleared'=>'var(--green)','failed'=>'var(--red)','error'=>'var(--red)','timeout'=>'var(--amber)','pending'=>'var(--muted)'];
                            $icons  = ['cleared'=>'fa-check-circle','failed'=>'fa-times-circle','error'=>'fa-exclamation-triangle','timeout'=>'fa-clock','pending'=>'fa-hourglass-half'];
                            $sc     = $colors[$status] ?? 'var(--muted)';
                            $si     = $icons[$status]  ?? 'fa-hourglass-half';
                        @endphp
                        <div class="metric-card" style="border-top: 4px solid {{ $meta['color'] }}; min-height: auto; align-content: start; justify-items: start; padding: 16px;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                                <i class="fa-solid {{ $meta['icon'] }}" style="color:{{ $meta['color'] }};font-size:20px;"></i>
                                <span style="font-weight:700;font-size:15px;">{{ $meta['label'] }}</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                <i class="fa-solid {{ $si }}" style="color:{{ $sc }};"></i>
                                <span style="font-size:13px;font-weight:800;color:{{ $sc }};text-transform:uppercase;">{{ ucfirst($status) }}</span>
                            </div>
                            @if(!empty($mv['unresolved_issues']))
                                <p style="font-size:12px;color:var(--red);margin:0;">{{ $mv['unresolved_issues'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </article>
    </div>

    <div class="dashboard-grid">
        <article class="card">
            <div class="card-header"><h2><i class="fa-solid fa-tasks"></i> Clearance Status</h2></div>
            <div class="card-body">
                @php
                    $overallStatus = $clearance_status['overall'] ?? 'pending';
                    $progressPct = $clearance_status['progress_percentage']
                        ?? (($clearance_status['completed_steps'] / max($clearance_status['total_steps'],1)) * 100);
                @endphp
                <div style="margin-bottom:16px;">
                    <span class="badge @if($overallStatus === 'cleared') generated @elseif($overallStatus === 'pending') pending_checkup @else emergency @endif">
                        {{ str_replace('_',' ', $overallStatus) }}
                    </span>
                </div>
                <div class="bars" style="margin-bottom: 12px;">
                    <div class="bar-row">
                        <span>Progress</span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $progressPct }}%;"></div>
                        </div>
                        <span>{{ round($progressPct) }}%</span>
                    </div>
                </div>
            </div>
        </article>

        <article class="card">
            <div class="card-header"><h2><i class="fa-solid fa-clipboard-check"></i> Required Documents</h2></div>
            <div class="card-body dense-list">
                @foreach($clearance_status['required_documents'] ?? [] as $doc)
                    @php
                        $upload = $uploads->firstWhere('document_type', $doc);
                        $status = $upload ? $upload->status : 'not-uploaded';
                    @endphp
                    <div class="list-item">
                        <div class="item-title">{{ $doc }}</div>
                        <span class="badge @if($status === 'approved') generated @elseif($status === 'pending') pending_checkup @else emergency @endif">
                            {{ $status === 'not-uploaded' ? 'Not Uploaded' : ucfirst($status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="card">
            <div class="card-header"><h2><i class="fa-solid fa-file-upload"></i> My Documents</h2></div>
            <div class="card-body dense-list">
                @forelse($uploads as $upload)
                    <div class="list-item">
                        <div>
                            <div class="item-title">{{ $upload->document_type }}</div>
                            <div class="item-meta">Uploaded {{ $upload->created_at->format('M d, Y') }}</div>
                        </div>
                        <span class="badge @if($upload->status === 'approved') generated @elseif($upload->status === 'pending') pending_checkup @else emergency @endif">{{ ucfirst($upload->status) }}</span>
                    </div>
                @empty
                    <div class="empty">No documents uploaded yet</div>
                @endforelse
            </div>
        </article>

        <article class="card">
            <div class="card-header"><h2><i class="fa-solid fa-bolt"></i> Quick Actions</h2></div>
            <div class="card-body form-grid">
                <a href="{{ route('student.documents') }}" class="form-tile" style="text-decoration:none;"><i class="fa-solid fa-plus"></i><span>Upload</span></a>
                <a href="{{ route('student.library') }}" class="form-tile" style="text-decoration:none;"><i class="fa-solid fa-book"></i><span>Library</span></a>
                <a href="{{ route('student.finance') }}" class="form-tile" style="text-decoration:none;"><i class="fa-solid fa-dollar-sign"></i><span>Finance</span></a>
                <a href="{{ route('student.academic') }}" class="form-tile" style="text-decoration:none;"><i class="fa-solid fa-graduation-cap"></i><span>Academic</span></a>
            </div>
        </article>
    </div>
</section>
@endsection
