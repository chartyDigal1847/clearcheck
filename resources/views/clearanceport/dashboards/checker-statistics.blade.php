<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics — Clearance Portal</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-card .label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #000;
        }
        
        .stat-card.primary .value {
            color: #8B4453;
        }
        
        .stat-card.success .value {
            color: #28a745;
        }
        
        .stat-card.danger .value {
            color: #dc3545;
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
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .stat-row:last-child {
            border-bottom: none;
        }
        
        .stat-row .label {
            font-size: 14px;
            color: #666;
        }
        
        .stat-row .value {
            font-size: 18px;
            font-weight: 600;
            color: #000;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        {{-- Navigation Tabs --}}
        <div class="nav-tabs">
            <a href="{{ route('checker.queue') }}" class="nav-tab">Review Queue</a>
            <a href="{{ route('checker.statistics') }}" class="nav-tab active">Statistics</a>
            <a href="{{ route('checker.history') }}" class="nav-tab">History</a>
        </div>
    </div>

    <div class="dashboard-container">
        {{-- Stats Grid --}}
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="label">Total Reviewed</div>
                <div class="value">{{ $statisticsData['total_reviewed'] }}</div>
            </div>
            <div class="stat-card success">
                <div class="label">Total Approved</div>
                <div class="value">{{ $statisticsData['total_approved'] }}</div>
            </div>
            <div class="stat-card danger">
                <div class="label">Total Rejected</div>
                <div class="value">{{ $statisticsData['total_rejected'] }}</div>
            </div>
            <div class="stat-card">
                <div class="label">Approval Rate</div>
                <div class="value">{{ $statisticsData['approval_rate'] }}%</div>
            </div>
        </div>

        {{-- Detailed Statistics --}}
        <div class="card">
            <h2><i class="fas fa-list"></i> Detailed Statistics</h2>
            <div class="stat-row">
                <span class="label">Pending Review</span>
                <span class="value">{{ $stats['pending'] }}</span>
            </div>
            <div class="stat-row">
                <span class="label">Under Review</span>
                <span class="value">{{ $stats['under_review'] }}</span>
            </div>
            <div class="stat-row">
                <span class="label">Approved Today</span>
                <span class="value" style="color: #28a745;">{{ $stats['approved_today'] }}</span>
            </div>
            <div class="stat-row">
                <span class="label">Rejected Today</span>
                <span class="value" style="color: #dc3545;">{{ $stats['rejected_today'] }}</span>
            </div>
        </div>

        {{-- Performance Summary --}}
        <div class="card">
            <h2><i class="fas fa-trophy"></i> Performance Summary</h2>
            <p style="font-size: 14px; color: #666; line-height: 1.6;">
                You have reviewed a total of <strong>{{ $statisticsData['total_reviewed'] }}</strong> documents.
                Your approval rate is <strong>{{ $statisticsData['approval_rate'] }}%</strong>, 
                with <strong>{{ $statisticsData['total_approved'] }}</strong> approvals and 
                <strong>{{ $statisticsData['total_rejected'] }}</strong> rejections.
            </p>
        </div>
    </div>
</body>
</html>
