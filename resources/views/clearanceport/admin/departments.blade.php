{{-- resources/views/clearanceport/admin/departments.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Offices — Admin — Deor &amp; Dune High School</title>
    <link rel="stylesheet" href="{{ asset('css/clearanceport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div id="toast-stack" class="toast-stack"></div>

{{-- Header Navigation --}}
<div class="header">
    <div class="nav-tabs">
        <a href="{{ route('admin.dashboard') }}" class="nav-tab">Overview</a>
        <a href="{{ route('admin.uploads') }}" class="nav-tab">Document Uploads</a>
        <a href="{{ route('admin.students') }}" class="nav-tab">Students</a>
        <a href="{{ route('admin.reports') }}" class="nav-tab active">Reports</a>
    </div>
</div>

<div class="dashboard-container">
    <div class="page-body">
            <h1 style="font-size:24px;font-weight:700;margin-bottom:8px;">Clearance Offices</h1>
            <p style="font-size:14px;color:#666;margin-bottom:30px;">Manage clearance offices and staff for Grade 7-12 students</p>

            {{-- Clearance Offices Grid --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;">
                {{-- Library Office --}}
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:15px;border-bottom:3px solid #8B4453;">
                        <h2 style="font-size:18px;font-weight:600;color:#8B4453;margin:0;">
                            <i class="fas fa-book"></i> Library
                        </h2>
                        <button class="btn-edit" onclick="openEditModal('library')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                    <div style="margin-bottom:16px;">
                        <div style="font-size:12px;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">In Charge</div>
                        <div style="font-size:15px;font-weight:600;color:#000;">Ms. Sarah Williams</div>
                    </div>
                    <div style="margin-bottom:20px;">
                        <div style="font-size:12px;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Contact</div>
                        <div style="font-size:14px;color:#000;">
                            <i class="fas fa-envelope" style="margin-right:6px;color:#8B4453;"></i>
                            library@deordune.edu
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:20px;">
                        <div style="text-align:center;padding:12px;background:#f5f5f5;border-radius:6px;">
                            <div style="font-size:24px;font-weight:700;color:#666;">-</div>
                            <div style="font-size:12px;color:#666;">Pending</div>
                        </div>
                        <div style="text-align:center;padding:12px;background:#f5f5f5;border-radius:6px;">
                            <div style="font-size:24px;font-weight:700;color:#28a745;">-</div>
                            <div style="font-size:12px;color:#666;">Approved</div>
                        </div>
                    </div>
                </div>

                {{-- Finance Office --}}
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:15px;border-bottom:3px solid #28a745;">
                        <h2 style="font-size:18px;font-weight:600;color:#28a745;margin:0;">
                            <i class="fas fa-dollar-sign"></i> Finance
                        </h2>
                        <button class="btn-edit" onclick="openEditModal('finance')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                    <div style="margin-bottom:16px;">
                        <div style="font-size:12px;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">In Charge</div>
                        <div style="font-size:15px;font-weight:600;color:#000;">Mr. Robert Brown</div>
                    </div>
                    <div style="margin-bottom:20px;">
                        <div style="font-size:12px;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Contact</div>
                        <div style="font-size:14px;color:#000;">
                            <i class="fas fa-envelope" style="margin-right:6px;color:#28a745;"></i>
                            finance@deordune.edu
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:20px;">
                        <div style="text-align:center;padding:12px;background:#f5f5f5;border-radius:6px;">
                            <div style="font-size:24px;font-weight:700;color:#666;">-</div>
                            <div style="font-size:12px;color:#666;">Pending</div>
                        </div>
                        <div style="text-align:center;padding:12px;background:#f5f5f5;border-radius:6px;">
                            <div style="font-size:24px;font-weight:700;color:#28a745;">-</div>
                            <div style="font-size:12px;color:#666;">Approved</div>
                        </div>
                    </div>
                </div>

                {{-- Exams & Records Office --}}
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:15px;border-bottom:3px solid #007bff;">
                        <h2 style="font-size:18px;font-weight:600;color:#007bff;margin:0;">
                            <i class="fas fa-clipboard-list"></i> Exams & Records
                        </h2>
                        <button class="btn-edit" onclick="openEditModal('exams')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                    <div style="margin-bottom:16px;">
                        <div style="font-size:12px;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">In Charge</div>
                        <div style="font-size:15px;font-weight:600;color:#000;">Mrs. Emily Davis</div>
                    </div>
                    <div style="margin-bottom:20px;">
                        <div style="font-size:12px;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Contact</div>
                        <div style="font-size:14px;color:#000;">
                            <i class="fas fa-envelope" style="margin-right:6px;color:#007bff;"></i>
                            exams@deordune.edu
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:20px;">
                        <div style="text-align:center;padding:12px;background:#f5f5f5;border-radius:6px;">
                            <div style="font-size:24px;font-weight:700;color:#666;">-</div>
                            <div style="font-size:12px;color:#666;">Pending</div>
                        </div>
                        <div style="text-align:center;padding:12px;background:#f5f5f5;border-radius:6px;">
                            <div style="font-size:24px;font-weight:700;color:#28a745;">-</div>
                            <div style="font-size:12px;color:#666;">Approved</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Office Information --}}
            <div class="card" style="margin-top:24px;">
                <h2 style="font-size:18px;font-weight:600;margin-bottom:15px;">
                    <i class="fas fa-info-circle"></i> Office Responsibilities
                </h2>
                <p style="font-size:14px;color:#000;line-height:1.6;margin-bottom:12px;">
                    Each office is responsible for reviewing and approving student clearance documents:
                </p>
                <ul style="font-size:14px;color:#000;line-height:1.8;padding-left:24px;">
                    <li><strong>Library:</strong> Checks for unreturned books, unpaid fines, and library clearance</li>
                    <li><strong>Finance:</strong> Verifies payment of tuition fees, outstanding balances, and financial obligations</li>
                    <li><strong>Exams & Records:</strong> Confirms completion of academic requirements, exam records, and transcript requests</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Edit Office Modal --}}
<div id="editOfficeModal" class="modal">
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fas fa-edit"></i> Edit Office Information</h2>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editOfficeForm" onsubmit="saveOfficeInfo(event)">
            <div class="modal-body">
                <input type="hidden" id="officeId" name="office_id">
                
                <div class="form-group">
                    <label class="form-label">Office Name</label>
                    <input type="text" id="officeName" name="office_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Person In Charge</label>
                    <input type="text" id="personInCharge" name="person_in_charge" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" id="contactEmail" name="contact_email" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-gold">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/clearanceport.js') }}"></script>
<script>
// Office data (in real implementation, this would come from database)
const officeData = {
    library: {
        name: 'Library',
        person: 'Ms. Sarah Williams',
        email: 'library@deordune.edu'
    },
    finance: {
        name: 'Finance',
        person: 'Mr. Robert Brown',
        email: 'finance@deordune.edu'
    },
    exams: {
        name: 'Exams & Records',
        person: 'Mrs. Emily Davis',
        email: 'exams@deordune.edu'
    }
};

function openEditModal(officeId) {
    const office = officeData[officeId];
    if (!office) return;
    
    document.getElementById('officeId').value = officeId;
    document.getElementById('officeName').value = office.name;
    document.getElementById('personInCharge').value = office.person;
    document.getElementById('contactEmail').value = office.email;
    
    document.getElementById('editOfficeModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editOfficeModal').style.display = 'none';
    document.getElementById('editOfficeForm').reset();
}

function saveOfficeInfo(event) {
    event.preventDefault();
    
    const officeId = document.getElementById('officeId').value;
    const officeName = document.getElementById('officeName').value;
    const personInCharge = document.getElementById('personInCharge').value;
    const contactEmail = document.getElementById('contactEmail').value;
    
    // In real implementation, this would send data to server
    // For now, we'll just update the local data and show success message
    officeData[officeId] = {
        name: officeName,
        person: personInCharge,
        email: contactEmail
    };
    
    closeEditModal();
    showToast('Office information updated successfully!', 'success');
    
    // TODO: Send AJAX request to server to save changes
    // fetch('/admin/offices/update', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify({ officeId, officeName, personInCharge, contactEmail })
    // });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editOfficeModal');
    if (event.target === modal) {
        closeEditModal();
    }
}
</script>
</body>
</html>
