{{-- resources/views/clearanceport/partials/sidebar.blade.php --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="school-name">Deor &amp; Dune High School</div>
        <div class="portal-label">E-Clearance System</div>
    </div>

    @if($role === 'student')
        <span class="sidebar-role-pill role-student"><i class="fas fa-user-graduate"></i> &nbsp;Student</span>
    @elseif($role === 'admin')
        <span class="sidebar-role-pill role-admin"><i class="fas fa-user-shield"></i> &nbsp;Admin</span>
    @elseif($role === 'checker')
        <span class="sidebar-role-pill role-checker"><i class="fas fa-user-check"></i> &nbsp;Clearance Officer</span>
    @endif

    <nav class="sidebar-nav">
        @if($role === 'student')
            <div class="nav-group-label">Navigation</div>
            <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large nav-icon"></i> Dashboard
            </a>
            <a href="{{ route('student.clearance') }}" class="nav-link {{ request()->routeIs('student.clearance') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check nav-icon"></i> My Clearance
            </a>
            <a href="{{ route('student.documents') }}" class="nav-link {{ request()->routeIs('student.documents') ? 'active' : '' }}">
                <i class="fas fa-folder-open nav-icon"></i> Documents
            </a>
            <a href="{{ route('student.certificate') }}" class="nav-link {{ request()->routeIs('student.certificate') ? 'active' : '' }}">
                <i class="fas fa-certificate nav-icon"></i> Certificate
            </a>

        @elseif($role === 'admin')
            <div class="nav-group-label">Management</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line nav-icon"></i> Dashboard
            </a>
            <a href="{{ route('admin.students') }}" class="nav-link {{ request()->routeIs('admin.students') ? 'active' : '' }}">
                <i class="fas fa-users nav-icon"></i> Students
            </a>
            <a href="{{ route('admin.departments') }}" class="nav-link {{ request()->routeIs('admin.departments') ? 'active' : '' }}">
                <i class="fas fa-building nav-icon"></i> Clearance Offices
            </a>
            <a href="{{ route('admin.uploads') }}" class="nav-link {{ request()->routeIs('admin.uploads') ? 'active' : '' }}">
                <i class="fas fa-inbox nav-icon"></i> All Uploads
                @if(isset($pendingCount) && $pendingCount > 0)
                    <span class="nav-badge">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.reports') }}" class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                <i class="fas fa-file-alt nav-icon"></i> Reports
            </a>

        @elseif($role === 'checker')
            <div class="nav-group-label">Clearance</div>
            <a href="{{ route('checker.dashboard') }}" class="nav-link {{ request()->routeIs('checker.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large nav-icon"></i> Dashboard
            </a>
            <a href="{{ route('checker.pending') }}" class="nav-link {{ request()->routeIs('checker.pending') ? 'active' : '' }}">
                <i class="fas fa-hourglass-half nav-icon"></i> Pending Reviews
                @if(isset($pendingCount) && $pendingCount > 0)
                    <span class="nav-badge">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('checker.approved') }}" class="nav-link {{ request()->routeIs('checker.approved') ? 'active' : '' }}">
                <i class="fas fa-check-circle nav-icon"></i> Approved
            </a>
            <a href="{{ route('checker.rejected') }}" class="nav-link {{ request()->routeIs('checker.rejected') ? 'active' : '' }}">
                <i class="fas fa-times-circle nav-icon"></i> Rejected
            </a>
            <a href="{{ route('checker.history') }}" class="nav-link {{ request()->routeIs('checker.history') ? 'active' : '' }}">
                <i class="fas fa-history nav-icon"></i> History
            </a>
        @endif
    </nav>
</aside>