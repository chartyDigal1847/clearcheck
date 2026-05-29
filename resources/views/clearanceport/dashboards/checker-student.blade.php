<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Documents — Clearance Checker</title>
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
            padding: 20px 40px;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #000;
        }
        
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: #f0f0f0;
            color: #000;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        
        .back-button:hover {
            background: #e0e0e0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 40px;
        }
        
        .student-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .student-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #000;
            font-size: 16px;
            font-weight: 500;
        }
        
        .documents-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .documents-section h2 {
            margin: 0 0 20px 0;
            color: #000;
            font-size: 20px;
        }
        
        .documents-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        
        .document-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 100px;
            gap: 15px;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e5e5e5;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .document-row:hover {
            background: #f9f9f9;
        }
        
        .document-row:last-child {
            border-bottom: none;
        }
        
        .document-name {
            font-weight: 500;
            color: #000;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-badge.approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-badge.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .date-cell {
            color: #666;
            font-size: 14px;
        }
        
        .view-link {
            color: #8B4453;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .view-link:hover {
            color: #6d3641;
        }
        
        .document-row-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 100px;
            gap: 15px;
            padding: 12px 15px;
            background: #f5f5f5;
            border-radius: 4px;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>Student Documents</h1>
        <a href="{{ route('checker.queue') }}" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Queue
        </a>
    </div>

    <div class="container">
        {{-- Student Info Card --}}
        <div class="student-header">
            <div class="student-info-grid">
                <div class="info-item">
                    <span class="info-label">Student Name</span>
                    <span class="info-value">{{ $student['name'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Registration No.</span>
                    <span class="info-value">{{ $student['reg_no'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $student['email'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Grade Level</span>
                    <span class="info-value">{{ $student['grade_level'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Section</span>
                    <span class="info-value">{{ $student['section'] }}</span>
                </div>
            </div>
        </div>

        {{-- Documents Section --}}
        <div class="documents-section">
            <h2><i class="fas fa-file-alt"></i> Submitted Documents</h2>
            
            @if($documents->count() > 0)
                <div class="document-row-header">
                    <div>Document</div>
                    <div>Status</div>
                    <div>Uploaded</div>
                    <div></div>
                    <div>Action</div>
                </div>
                
                <div class="documents-list">
                    @foreach($documents as $doc)
                    <div class="document-row" onclick="location.href='{{ route('checker.review', $doc['id']) }}'" style="cursor: pointer;">
                        <div class="document-name">
                            <i class="fas fa-file"></i> {{ $doc['document_type'] }}
                        </div>
                        <div>
                            <span class="status-badge {{ $doc['status'] }}">{{ ucfirst($doc['status']) }}</span>
                        </div>
                        <div class="date-cell">{{ $doc['uploaded_at'] }}</div>
                        <div>
                            @if($doc['status'] === 'rejected' && $doc['rejection_reason'])
                                <small style="color: #dc3545;"><i class="fas fa-exclamation-circle"></i> {{ $doc['rejection_reason'] }}</small>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('checker.review', $doc['id']) }}" class="view-link">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No documents submitted yet</p>
                </div>
            @endif
        </div>
    </div>

    <script src="{{ asset('js/clearanceport.js') }}"></script>
</body>
</html>
