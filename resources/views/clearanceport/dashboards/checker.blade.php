@extends('clearanceport.layouts.app')

@section('title', 'Clearance Checker Dashboard — Clearance Portal')

@section('content')
<div class="module-topbar">
    <div>
        <h1 class="module-title">Review Queue</h1>
        <p class="module-sub">Welcome back — {{ now()->format('l, F j, Y') }}</p>
    </div>
    <div class="module-actions" aria-label="ClearCheck navigation">
        <a href="{{ route('checker.queue') }}" class="top-action active"><i class="fa-solid fa-list-check"></i> Review Queue</a>
        <a href="{{ route('checker.statistics') }}" class="top-action"><i class="fa-solid fa-chart-pie"></i> Statistics</a>
        <a href="{{ route('checker.history') }}" class="top-action"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
    </div>
</div>

<section class="page active">
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-file-signature"></i></div>
            <div class="metric-value">{{ $stats['pending'] ?? 0 }}</div>
            <div class="metric-label">Pending Review</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
            <div class="metric-value">{{ $stats['under_review'] ?? 0 }}</div>
            <div class="metric-label">Under Review</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-check-circle"></i></div>
            <div class="metric-value">{{ $stats['approved_today'] ?? 0 }}</div>
            <div class="metric-label">Approved Today</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-circle-xmark"></i></div>
            <div class="metric-value">{{ $stats['rejected_today'] ?? 0 }}</div>
            <div class="metric-label">Rejected Today</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <article class="card">
            <div class="card-header"><h2><i class="fa-solid fa-list-check"></i> Review Queue</h2></div>
            <div class="card-body table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Registration No.</th>
                            <th>Documents</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($queue as $item)
                        <tr>
                            <td>{{ $item['student'] }}</td>
                            <td>{{ $item['reg_no'] }}</td>
                            <td>
                                <span class="mini">
                                    @if($item['pending_count'] > 0)
                                        {{ $item['pending_count'] }} document{{ $item['pending_count'] != 1 ? 's' : '' }} pending
                                    @elseif($item['approved_count'] > 0)
                                        {{ $item['total_count'] }} document{{ $item['total_count'] != 1 ? 's' : '' }} approved
                                    @else
                                        {{ $item['total_count'] }} document{{ $item['total_count'] != 1 ? 's' : '' }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($item['pending_count'] > 0)
                                    <span class="badge pending_checkup">Needs Review</span>
                                @else
                                    <span class="badge treated">Complete</span>
                                @endif
                            </td>
                            <td>{{ $item['uploaded_at'] }}</td>
                            <td>
                                <a href="{{ route('checker.student', $item['student_id']) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> Review</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty text-center">No documents in queue</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="card">
            <div class="card-header"><h2><i class="fa-solid fa-bolt"></i> Quick Actions</h2></div>
            <div class="card-body dense-list">
                <a href="{{ route('checker.pending') }}" class="list-item" style="text-decoration: none; color: inherit;">
                    <div class="item-title"><i class="fa-solid fa-eye"></i> Review Documents</div>
                </a>
                <a href="{{ route('checker.statistics') }}" class="list-item" style="text-decoration: none; color: inherit;">
                    <div class="item-title"><i class="fa-solid fa-chart-bar"></i> View Statistics</div>
                </a>
            </div>
        </article>
    </div>
</section>
@endsection
