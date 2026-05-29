@extends('clearanceport.layouts.app')

@section('title', 'Admin Dashboard — Clearance Portal')

@section('content')
<div class="module-topbar">
    <div>
        <h1 class="module-title">Admin Dashboard</h1>
        <p class="module-sub">Welcome back, {{ $adminName ?? 'Administrator' }} — {{ now()->format('l, F j, Y') }}</p>
    </div>
    <div class="module-actions" aria-label="ClearCheck navigation">
        <a href="{{ route('admin.dashboard') }}" class="top-action active"><i class="fa-solid fa-chart-pie"></i> Overview</a>
        <a href="{{ route('admin.uploads') }}" class="top-action"><i class="fa-solid fa-upload"></i> Uploads</a>
        <a href="{{ route('admin.students') }}" class="top-action"><i class="fa-solid fa-user-graduate"></i> Students</a>
        <a href="{{ route('admin.reports') }}" class="top-action"><i class="fa-solid fa-chart-simple"></i> Reports</a>
        <a href="{{ route('admin.departments') }}" class="top-action"><i class="fa-solid fa-cogs"></i> Departments</a>
    </div>
</div>

<section class="page active">
    <div class="metrics-grid" id="metricsGrid">
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-users"></i></div>
            <div class="metric-value">{{ $stats['total_students'] ?? 0 }}</div>
            <div class="metric-label">Total Students</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-file-arrow-up"></i></div>
            <div class="metric-value">{{ $stats['pending_uploads'] ?? 0 }}</div>
            <div class="metric-label">Pending Uploads</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-check-circle"></i></div>
            <div class="metric-value">{{ $stats['approved_uploads'] ?? 0 }}</div>
            <div class="metric-label">Approved Uploads</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-circle-xmark"></i></div>
            <div class="metric-value">{{ $stats['rejected_uploads'] ?? 0 }}</div>
            <div class="metric-label">Rejected Uploads</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-user-tie"></i></div>
            <div class="metric-value">{{ $stats['active_checkers'] ?? 0 }}</div>
            <div class="metric-label">Active Checkers</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <article class="card">
            <div class="card-header"><h2><i class="fa-solid fa-bolt"></i> Quick Actions</h2></div>
            <div class="card-body dense-list">
                <a href="{{ route('admin.uploads') }}" class="list-item" style="text-decoration: none; color: inherit;">
                    <div class="item-title"><i class="fa-solid fa-upload"></i> View Document Uploads</div>
                    <div class="item-meta">Review pending student submissions</div>
                </a>
                <a href="{{ route('admin.students') }}" class="list-item" style="text-decoration: none; color: inherit;">
                    <div class="item-title"><i class="fa-solid fa-users"></i> Manage Students</div>
                    <div class="item-meta">Search and manage student clearance records</div>
                </a>
                <a href="{{ route('admin.reports') }}" class="list-item" style="text-decoration: none; color: inherit;">
                    <div class="item-title"><i class="fa-solid fa-chart-bar"></i> View Reports</div>
                    <div class="item-meta">Generate clearance statistics and reports</div>
                </a>
                <a href="{{ route('admin.departments') }}" class="list-item" style="text-decoration: none; color: inherit;">
                    <div class="item-title"><i class="fa-solid fa-cog"></i> Clearance Offices</div>
                    <div class="item-meta">Configure departments and sign-off requirements</div>
                </a>
            </div>
        </article>
    </div>
</section>
@endsection
