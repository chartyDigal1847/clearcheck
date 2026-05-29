<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Clearance — Student Portal</title>
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
        
        .academic-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .academic-item {
            background: white;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .academic-item:hover {
            border-color: #8B4453;
            box-shadow: 0 4px 12px rgba(139, 68, 83, 0.15);
        }
        
        .academic-item-icon {
            font-size: 48px;
            color: #8B4453;
            margin-bottom: 15px;
        }
        
        .academic-item-title {
            font-size: 16px;
            font-weight: 600;
            color: #000;
            margin-bottom: 10px;
        }
        
        .academic-item-description {
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 10px;
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
        
        .status-badge.not-uploaded {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #8B4453;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }
        
        .btn:hover {
            background-color: #6d3641;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .upload-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .upload-item {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <div id="toast-stack" class="toast-stack"></div>

    {{-- Header Navigation --}}
    <div class="header">
        {{-- Navigation Tabs --}}
        <div class="nav-tabs">
            <a href="{{ route('student.clearance') }}" class="nav-tab">Clearance Status</a>
            <a href="{{ route('student.documents') }}" class="nav-tab">My Documents</a>
            <a href="{{ route('student.library') }}" class="nav-tab">Library</a>
            <a href="{{ route('student.finance') }}" class="nav-tab">Finance</a>
            <a href="{{ route('student.academic') }}" class="nav-tab active">Academic</a>
        </div>
    </div>

    <div class="dashboard-container">
        <div style="margin-bottom: 30px;">
            <h1 style="font-size: 28px; margin-bottom: 8px;">Academic Clearance</h1>
            <p style="color: #666; margin: 0;">Submit your academic clearance documents</p>
        </div>

        {{-- Academic Requirements --}}
        <div class="academic-grid">
            <div class="academic-item">
                <div class="academic-item-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="academic-item-title">Report Card Copy</div>
                <div class="academic-item-description">
                    A copy of your official report card showing your academic performance and grades for the current term.
                </div>
                @php
                    $reportCard = $uploads->firstWhere('document_type', 'Report Card Copy');
                @endphp
                @if($reportCard)
                    <span class="status-badge {{ $reportCard->status }}">{{ ucfirst($reportCard->status) }}</span>
                @else
                    <span class="status-badge not-uploaded">Not Uploaded</span>
                @endif
                <button class="btn" onclick="openModal('uploadDocumentModal')" style="width: 100%; margin-top: 15px;">
                    <i class="fas fa-upload"></i> Upload
                </button>
            </div>

            <div class="academic-item">
                <div class="academic-item-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="academic-item-title">Enrollment/Exam Permit</div>
                <div class="academic-item-description">
                    Official enrollment confirmation or examination permit showing your registration status for the current academic period.
                </div>
                @php
                    $permit = $uploads->firstWhere('document_type', 'Enrollment/Exam Permit');
                @endphp
                @if($permit)
                    <span class="status-badge {{ $permit->status }}">{{ ucfirst($permit->status) }}</span>
                @else
                    <span class="status-badge not-uploaded">Not Uploaded</span>
                @endif
                <button class="btn" onclick="openModal('uploadDocumentModal')" style="width: 100%; margin-top: 15px;">
                    <i class="fas fa-upload"></i> Upload
                </button>
            </div>
        </div>

        {{-- Uploaded Documents --}}
        @if($uploads->count() > 0)
        <div class="card">
            <h2><i class="fas fa-file-check"></i> Your Academic Documents</h2>
            <ul class="upload-list">
                @foreach($uploads as $upload)
                <li class="upload-item">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #000; margin-bottom: 4px;">{{ $upload->document_type }}</div>
                        <div style="font-size: 12px; color: #666;">Uploaded {{ $upload->created_at->format('M d, Y') }}</div>
                        @if($upload->status === 'rejected' && $upload->rejection_reason)
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $upload->rejection_reason }}
                            </div>
                        @endif
                    </div>
                    <span class="status-badge {{ $upload->status }}">{{ ucfirst($upload->status) }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    {{-- Upload Document Modal --}}
    <div id="uploadDocumentModal" class="modal modal-backdrop">
        <div class="modal-content" style="max-width: 600px; background: white; border-radius: 8px; padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; color: #000;">Upload Academic Document</h2>
                <button onclick="closeModal('uploadDocumentModal')" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

           <form action="{{ route('student.upload') }}" method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #000;">Document Type *</label>
                    <select name="document_type" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">Select a document type</option>
                        <option value="Report Card Copy">Report Card Copy</option>
                        <option value="Enrollment/Exam Permit">Enrollment/Exam Permit</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #000;">Select File *</label>
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display: block; width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <small style="color: #666; margin-top: 5px;">Max 5MB. Allowed: PDF, JPG, PNG, DOC, DOCX</small>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" onclick="closeModal('uploadDocumentModal')" style="padding: 10px 20px; background: #e5e5e5; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="submit" style="padding: 10px 20px; background: #8B4453; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
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
            z-index: 1000;
        }

        .modal.open {
            display: flex;
        }

        .modal-backdrop {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            animation: slideUp 0.3s ease-in-out;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>

    <script src="{{ asset('js/clearanceport.js') }}"></script>
    <script>
        @if(session('success')) showToast('{{ session('success') }}', 'success'); @endif
        @if(session('error')) showToast('{{ session('error') }}', 'error'); @endif
    </script>
</body>
</html>
