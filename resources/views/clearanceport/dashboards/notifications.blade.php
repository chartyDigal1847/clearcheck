<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifications — ClearCheck</title>
    <link rel="stylesheet" href="{{ asset('css/clearanceport.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background:#f5f5f5; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; margin:0; }
        .header { background:white; box-shadow:0 2px 4px rgba(0,0,0,.1); }
        .nav-tabs { display:flex; padding:0 40px; border-bottom:2px solid #e5e5e5; }
        .nav-tab { padding:15px 25px; background:none; border:none; cursor:pointer; font-size:15px; color:#666; text-decoration:none; font-weight:500; position:relative; }
        .nav-tab.active { color:#000; font-weight:600; }
        .nav-tab.active::after { content:''; position:absolute; bottom:-2px; left:0; right:0; height:3px; background:#8B4453; }
        .container { max-width:900px; margin:0 auto; padding:30px 40px; }
        .page-title { font-size:22px; font-weight:700; color:#000; margin-bottom:20px; }
        .notif-card { background:white; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.1); margin-bottom:10px; padding:16px 20px; display:flex; align-items:flex-start; gap:14px; border-left:4px solid #8B4453; transition:opacity .2s; }
        .notif-card.read { border-left-color:#e0e0e0; opacity:.75; }
        .notif-icon { font-size:20px; color:#8B4453; margin-top:2px; flex-shrink:0; }
        .notif-card.read .notif-icon { color:#aaa; }
        .notif-body { flex:1; }
        .notif-title { font-weight:700; font-size:14px; color:#000; margin-bottom:4px; }
        .notif-text  { font-size:13px; color:#555; margin-bottom:6px; }
        .notif-meta  { font-size:11px; color:#999; }
        .notif-actions { display:flex; gap:8px; flex-shrink:0; }
        .btn-sm { padding:5px 12px; border-radius:4px; font-size:12px; border:none; cursor:pointer; }
        .btn-read { background:#e9ecef; color:#333; }
        .btn-del  { background:#f8d7da; color:#721c24; }
        .btn-read:hover { background:#dee2e6; }
        .btn-del:hover  { background:#f5c6cb; }
        .toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
        .badge-count { background:#8B4453; color:white; border-radius:20px; padding:2px 10px; font-size:12px; font-weight:700; }
        .empty-state { text-align:center; padding:60px 20px; color:#999; }
        .empty-state i { font-size:48px; margin-bottom:16px; display:block; }
    </style>
</head>
<body>
<div class="header">
    <div class="nav-tabs">
        @if(session('sso_role') === 'student')
            <a href="{{ route('student.clearance') }}" class="nav-tab">Clearance Status</a>
            <a href="{{ route('student.documents') }}" class="nav-tab">My Documents</a>
        @elseif(session('sso_role') === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="nav-tab">Overview</a>
        @else
            <a href="{{ route('checker.queue') }}" class="nav-tab">Review Queue</a>
        @endif
        <a href="#" class="nav-tab active">Notifications</a>
    </div>
</div>

<div class="container">
    <div class="toolbar">
        <div class="page-title">
            <i class="fas fa-bell" style="color:#8B4453;margin-right:8px;"></i>
            Notifications
            @if($unreadCount > 0)
                <span class="badge-count">{{ $unreadCount }} unread</span>
            @endif
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-sm btn-read">
                    <i class="fas fa-check-double"></i> Mark all read
                </button>
            </form>
        @endif
    </div>

    @forelse($notifications as $notif)
        <div class="notif-card {{ $notif->is_read ? 'read' : '' }}" id="notif-{{ $notif->id }}">
            <div class="notif-icon">
                @php
                    $iconMap = [
                        'clearance_updated'   => 'fa-sync-alt',
                        'student_cleared'     => 'fa-check-circle',
                        'validation_failed'   => 'fa-exclamation-triangle',
                        'module_cleared'      => 'fa-check',
                        'pending_requirement' => 'fa-hourglass-half',
                    ];
                    $icon = $iconMap[$notif->type] ?? 'fa-bell';
                @endphp
                <i class="fas {{ $icon }}"></i>
            </div>
            <div class="notif-body">
                <div class="notif-title">{{ $notif->title }}</div>
                <div class="notif-text">{{ $notif->body }}</div>
                <div class="notif-meta">
                    {{ $notif->created_at->diffForHumans() }}
                    @if($notif->is_read && $notif->read_at)
                        · Read {{ $notif->read_at->diffForHumans() }}
                    @endif
                </div>
            </div>
            <div class="notif-actions">
                @if(!$notif->is_read)
                    <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-sm btn-read" title="Mark as read">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('notifications.destroy', $notif->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-sm btn-del" title="Delete"
                        onclick="return confirm('Delete this notification?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <p>No notifications yet.</p>
        </div>
    @endforelse

    {{ $notifications->links() }}
</div>
</body>
</html>
