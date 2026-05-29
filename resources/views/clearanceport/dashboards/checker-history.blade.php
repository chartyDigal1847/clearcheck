<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History — Clearance Portal</title>
    <link rel="stylesheet" href="{{ asset('css/clearanceport.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            padding: 0;
            background-color: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: #000;
        }
        
        .header {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            border-bottom: 1px solid #e5e5e5;
        }
        
        .header-left h1 {
            font-size: 28px;
            color: #000;
            margin: 0;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-info .name {
            font-weight: 600;
            color: #000;
            font-size: 14px;
        }
        
        .user-info .detail {
            color: #666;
            font-size: 12px;
        }
        
        .nav-tabs {
            display: flex;
            padding: 0 40px;
            border-bottom: 2px solid #e5e5e5;
            gap: 0;
        }
        
        .nav-tab {
            padding: 15px 25px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 15px;
            color: #666;
            text-decoration: none;
            font-weight: 500;
            position: relative;
            transition: color 0.3s ease;
        }
        
        .nav-tab:hover {
            color: #000;
        }
        
        .nav-tab.active {
            color: #000;
            font-weight: 600;
        }
        
        .nav-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #8B4453;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 40px;
        }
        
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card h2 {
            margin: 0 0 15px 0;
            color: #000;
            font-size: 18px;
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .history-table thead {
            background-color: #f8f9fa;
        }
        
        .history-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #000;
            border-bottom: 2px solid #dee2e6;
        }
        
        .history-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
            color: #000;
        }
        
        .history-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-badge.approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-badge.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            text-decoration: none;
            color: #000;
            font-size: 14px;
        }
        
        .pagination a:hover {
            background-color: #f8f9fa;
        }
        
        .pagination .active {
            background-color: #8B4453;
            color: white;
            border-color: #8B4453;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        {{-- Navigation Tabs --}}
        <div class="nav-tabs">
            <a href="{{ route('checker.queue') }}" class="nav-tab">Review Queue</a>
            <a href="{{ route('checker.statistics') }}" class="nav-tab">Statistics</a>
            <a href="{{ route('checker.history') }}" class="nav-tab active">History</a>
        </div>
    </div>

    <div class="dashboard-container">
        {{-- Review History --}}
        <div class="card">
            <h2><i class="fas fa-clock-rotate-left"></i> Review History</h2>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Registration No.</th>
                        <th>Document</th>
                        <th>Status</th>
                        <th>Reviewed By</th>
                        <th>Reviewed Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $item)
                    <tr>
                        <td>{{ $item->student->user->name ?? 'Unknown' }}</td>
                        <td>{{ $item->student->reg_no ?? 'N/A' }}</td>
                        <td>{{ $item->document_type }}</td>
                        <td><span class="status-badge {{ $item->status }}">{{ ucfirst($item->status) }}</span></td>
                        <td>{{ $item->reviewer->name ?? 'System' }}</td>
                        <td>{{ $item->reviewed_at ? $item->reviewed_at->format('M d, Y h:i A') : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #666;">No review history available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            {{-- Pagination --}}
            @if($history->hasPages())
            <div class="pagination">
                {{-- Previous Page Link --}}
                @if ($history->onFirstPage())
                    <span style="opacity: 0.5; cursor: not-allowed;">&laquo; Previous</span>
                @else
                    <a href="{{ $history->previousPageUrl() }}">&laquo; Previous</a>
                @endif

                {{-- Page Numbers --}}
                @foreach ($history->getUrlRange(1, $history->lastPage()) as $page => $url)
                    @if ($page == $history->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($history->hasMorePages())
                    <a href="{{ $history->nextPageUrl() }}">Next &raquo;</a>
                @else
                    <span style="opacity: 0.5; cursor: not-allowed;">Next &raquo;</span>
                @endif
            </div>
            @endif
        </div>
    </div>
</body>
</html>
