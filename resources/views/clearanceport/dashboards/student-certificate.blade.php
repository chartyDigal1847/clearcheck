    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Certificate — Clearance Portal</title>
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
                max-width: 1200px;
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
            
            .alert {
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 15px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .alert.warning {
                background-color: #fff3cd;
                color: #856404;
                border: 1px solid #ffeaa7;
            }
            
            .alert.success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            
            .alert i {
                font-size: 18px;
            }
            
            .certificate-preview {
                border: 2px solid #8B4453;
                border-radius: 8px;
                padding: 40px;
                text-align: center;
                background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                margin: 20px 0;
            }
            
            .certificate-preview .title {
                font-size: 24px;
                font-weight: 700;
                color: #8B4453;
                margin-bottom: 20px;
            }
            
            .certificate-preview .subtitle {
                font-size: 16px;
                color: #666;
                margin-bottom: 30px;
            }
            
            .certificate-preview .student-name {
                font-size: 28px;
                font-weight: 700;
                color: #000;
                margin: 20px 0;
            }
            
            .certificate-preview .details {
                font-size: 14px;
                color: #666;
                margin: 10px 0;
            }
            
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background: #8B4453;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                font-size: 14px;
                border: none;
                cursor: pointer;
                transition: background-color 0.3s ease;
                margin-top: 20px;
            }
            
            .btn:hover {
                background-color: #6d3641;
            }
            
            .btn i {
                margin-right: 5px;
            }
            
            .btn:disabled {
                background-color: #ccc;
                cursor: not-allowed;
            }
        </style>
    </head>
    <body>
        {{-- Header --}}
        <div class="header">
            {{-- Navigation Tabs --}}
            <div class="nav-tabs">
                <a href="{{ route('student.clearance') }}" class="nav-tab">Clearance Status</a>
                <a href="{{ route('student.documents') }}" class="nav-tab">My Documents</a>
                <a href="{{ route('student.support') }}" class="nav-tab">Support</a>
            </div>
        </div>

        <div class="dashboard-container">
            @if($clearance_status['overall'] === 'completed')
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <span>Congratulations! Your clearance is complete. You can now download your certificate.</span>
                </div>

                {{-- Certificate Preview --}}
                <div class="card">
                    <h2><i class="fas fa-award"></i> Clearance Certificate</h2>
                    <div class="certificate-preview">
                        <div class="title">CLEARANCE CERTIFICATE</div>
                        <div class="subtitle">Deor & Dune High School</div>
                        <div style="margin: 30px 0;">
                            <p style="font-size: 14px; color: #666;">This is to certify that</p>
                            <div class="student-name">{{ $student['name'] }}</div>
                            <div class="details">Registration No: {{ $student['reg_no'] }}</div>
                            <div class="details">Grade {{ $student['grade_level'] }}{{ $student['section'] ? '-' . $student['section'] : '' }}</div>
                            <p style="font-size: 14px; color: #666; margin-top: 30px;">
                                has successfully completed all clearance requirements and is hereby cleared.
                            </p>
                        </div>
                        <div style="margin-top: 40px; font-size: 12px; color: #999;">
                            Issued on {{ now()->format('F d, Y') }}
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <button class="btn" onclick="alert('Download functionality coming soon')">
                            <i class="fas fa-download"></i> Download Certificate
                        </button>
                    </div>
                </div>
            @else
                <div class="alert warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Your clearance is not yet complete. Please ensure all documents are uploaded and approved before you can access your certificate.</span>
                </div>

                {{-- Progress Info --}}
                <div class="card">
                    <h2><i class="fas fa-tasks"></i> Clearance Progress</h2>
                    <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                        You have completed {{ $clearance_status['completed_steps'] }} out of {{ $clearance_status['total_steps'] }} required steps.
                    </p>
                    <div style="background-color: #e9ecef; height: 20px; border-radius: 10px; overflow: hidden;">
                        <div style="background-color: #8B4453; height: 100%; width: {{ ($clearance_status['completed_steps'] / $clearance_status['total_steps']) * 100 }}%; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 600;">
                            {{ round(($clearance_status['completed_steps'] / $clearance_status['total_steps']) * 100) }}%
                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="{{ route('student.clearance') }}" class="btn">
                            <i class="fas fa-arrow-left"></i> Back to Clearance Status
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </body>
    </html>
