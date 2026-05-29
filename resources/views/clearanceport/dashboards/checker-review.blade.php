<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Document — Clearance Checker</title>
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
        
        .review-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .document-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .document-image {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 600px;
            object-fit: contain;
            margin-bottom: 20px;
        }
        
        .student-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #000;
            font-size: 14px;
        }
        
        .info-value {
            color: #666;
            font-size: 14px;
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
        
        .action-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .action-section h3 {
            margin: 0 0 15px 0;
            color: #000;
            font-size: 16px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-approve:hover {
            background: #218838;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-reject:hover {
            background: #c82333;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #000;
            font-size: 14px;
        }
        
        .form-group textarea,
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .form-group textarea:focus,
        .form-group input:focus {
            outline: none;
            border-color: #8B4453;
            box-shadow: 0 0 0 3px rgba(139, 68, 83, 0.1);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        
        .modal.open {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            padding: 30px;
        }
        
        .modal-header {
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            margin: 0;
            color: #000;
            font-size: 20px;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .modal-footer button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .modal-footer .btn-cancel {
            background: #e5e5e5;
            color: #000;
        }
        
        .modal-footer .btn-confirm {
            background: #dc3545;
            color: white;
        }
        
        .rejection-note {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 12px;
            margin-top: 15px;
            color: #721c24;
            font-size: 14px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>Document Review</h1>
        <a href="{{ route('checker.queue') }}" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Queue
        </a>
    </div>

    <div class="container">
        <div class="review-grid">
            {{-- Document Display --}}
            <div class="document-section">
                <h2 style="margin: 0 0 15px 0; color: #000; font-size: 18px;">
                    <i class="fas fa-file-alt"></i> {{ $document['document_type'] }}
                </h2>
                
                @if($document['file_url'])
                    <img src="{{ $document['file_url'] }}" alt="{{ $document['document_type'] }}" class="document-image">
                    <a href="{{ $document['file_url'] }}" target="_blank" class="back-button" style="display: block; text-align: center;">
                        <i class="fas fa-download"></i> Download Original
                    </a>
                @else
                    <div style="background: #f5f5f5; padding: 60px 20px; text-align: center; border-radius: 4px; color: #999;">
                        <i class="fas fa-file-alt" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                        <p>No file available for preview</p>
                    </div>
                @endif
            </div>

            {{-- Student Information & Actions --}}
            <div>
                {{-- Student Info Card --}}
                <div class="student-info" style="margin-bottom: 20px;">
                    <h3 style="margin: 0 0 15px 0; color: #000; font-size: 16px;">Student Information</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Name</span>
                        <span class="info-value">{{ $document['student_name'] }}</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Registration</span>
                        <span class="info-value">{{ $document['student_reg'] }}</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value" style="font-size: 12px;">{{ $document['student_email'] }}</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Grade Level</span>
                        <span class="info-value">{{ $document['grade_level'] }}</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Section</span>
                        <span class="info-value">{{ $document['section'] }}</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Uploaded</span>
                        <span class="info-value">{{ $document['date_uploaded'] }}</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="status-badge {{ $document['status'] }}">{{ ucfirst($document['status']) }}</span>
                    </div>
                </div>

                {{-- Rejection Reason (if rejected) --}}
                @if($document['status'] === 'rejected' && $document['rejection_reason'])
                    <div class="rejection-note">
                        <strong><i class="fas fa-exclamation-circle"></i> Rejection Reason:</strong><br>
                        {{ $document['rejection_reason'] }}
                    </div>
                @endif

                {{-- Action Buttons --}}
                @if($document['status'] === 'pending')
                    <div class="action-section" style="margin-top: 20px;">
                        <h3><i class="fas fa-check-square"></i> Review Decision</h3>
                        
                        <div class="action-buttons">
                            <form action="{{ route('checker.approve', $document['id']) }}" method="POST" style="flex: 1;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-approve" style="width: 100%;">
                                    <i class="fas fa-check"></i> Approve Document
                                </button>
                            </form>
                            
                            <button class="btn btn-reject" onclick="openRejectModal()" style="flex: 1;">
                                <i class="fas fa-times"></i> Reject Document
                            </button>
                        </div>
                        
                        <a href="{{ route('checker.queue') }}" class="btn btn-back" style="display: block; text-align: center; text-decoration: none; width: 100%;">
                            <i class="fas fa-arrow-left"></i> Back to Queue
                        </a>
                    </div>
                @else
                    <div class="action-section" style="margin-top: 20px; background: #f0f8ff; border-left: 4px solid #8B4453;">
                        <p style="margin: 0; color: #333; font-size: 14px;">
                            <i class="fas fa-info-circle" style="color: #8B4453; margin-right: 10px;"></i>
                            This document has already been reviewed and <strong>{{ ucfirst($document['status']) }}</strong>.
                        </p>
                    </div>
                    
                    <a href="{{ route('checker.queue') }}" class="btn btn-back" style="display: block; text-align: center; text-decoration: none; width: 100%; margin-top: 20px;">
                        <i class="fas fa-arrow-left"></i> Back to Queue
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Reject Confirmation Modal --}}
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-times-circle" style="color: #dc3545;"></i> Reject Document</h2>
            </div>
            
            <form action="{{ route('checker.reject', $document['id']) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reject_note">Reason for Rejection *</label>
                        <textarea 
                            id="reject_note" 
                            name="reject_note" 
                            rows="4" 
                            placeholder="Provide a clear reason for rejection (e.g., Document is unclear, Missing information, etc.)" 
                            required></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn-confirm"><i class="fas fa-times"></i> Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/clearanceport.js') }}"></script>
    <script>
        function openRejectModal() {
            document.getElementById('rejectModal').classList.add('open');
        }
        
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('open');
        }
        
        // Close modal when clicking outside
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
        
        @if(session('success'))
            window.addEventListener('load', function() {
                if (typeof showToast === 'function') {
                    showToast('{{ session('success') }}', 'success');
                    setTimeout(() => window.location.href = '{{ route('checker.queue') }}', 1500);
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
