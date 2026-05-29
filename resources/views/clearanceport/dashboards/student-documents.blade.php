<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Documents — Clearance Portal</title>
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
        
        .upload-item .info {
            flex: 1;
        }
        
        .upload-item .document-name {
            font-weight: 600;
            color: #000;
            font-size: 14px;
        }
        
        .upload-item .uploaded-date {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
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
        }
        
        .btn:hover {
            background-color: #6d3641;
        }
        
        .btn i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        {{-- Navigation Tabs --}}
        <div class="nav-tabs">
            <a href="{{ route('student.clearance') }}" class="nav-tab">Clearance Status</a>
            <a href="{{ route('student.documents') }}" class="nav-tab active">My Documents</a>
            <a href="{{ route('student.library') }}" class="nav-tab">Library</a>
            <a href="{{ route('student.finance') }}" class="nav-tab">Finance</a>
            <a href="{{ route('student.academic') }}" class="nav-tab">Academic</a>
        </div>
    </div>

    <div class="dashboard-container">
        {{-- Upload Button --}}
        <div style="margin-bottom: 20px;">
            <button class="btn" onclick="openModal('uploadDocumentModal')">
                <i class="fas fa-upload"></i> Upload New Document
            </button>
        </div>

        {{-- Required Documents Info --}}
        <div class="card" style="background: #f0f8ff; border-left: 4px solid #8B4453; margin-bottom: 20px;">
            <h2 style="color: #8B4453;"><i class="fas fa-info-circle"></i> Required Documents</h2>
            <p style="color: #333; margin-bottom: 15px;">Upload all the following documents to complete your clearance:</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">
                @foreach($requiredDocuments ?? [] as $doc)
                    <div style="display: flex; align-items: center; padding: 10px; background: white; border-radius: 4px;">
                        <i class="fas fa-file-alt" style="color: #8B4453; margin-right: 10px; font-size: 18px;"></i>
                        <span>{{ $doc }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Document Uploads --}}
        <div class="card">
            <h2><i class="fas fa-file-upload"></i> Uploaded Documents</h2>
            <ul class="upload-list">
                @forelse($uploads as $upload)
                <li class="upload-item">
                    <div class="info">
                        <div class="document-name">{{ $upload->document_type }}</div>
                        <div class="uploaded-date">Uploaded {{ $upload->created_at->format('M d, Y') }}</div>
                        @if($upload->status === 'rejected' && $upload->rejection_reason)
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">
                                <i class="fas fa-exclamation-circle"></i> {{ $upload->rejection_reason }}
                            </div>
                        @endif
                    </div>
                    <span class="status-badge {{ $upload->status }}">{{ ucfirst($upload->status) }}</span>
                </li>
                @empty
                <li class="upload-item">
                    <div class="info">
                        <div class="document-name">No documents uploaded yet</div>
                        <div class="uploaded-date">Click "Upload New Document" to get started</div>
                    </div>
                </li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Upload Document Modal --}}
    <div id="uploadDocumentModal" class="modal modal-backdrop">
        <div class="modal-content" style="max-width: 600px; background: white; border-radius: 8px; padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; color: #000;">Upload Document</h2>
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
                        @foreach($requiredDocuments ?? [] as $doc)
                            <option value="{{ $doc }}">{{ $doc }}</option>
                        @endforeach
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
