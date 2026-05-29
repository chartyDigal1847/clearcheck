{{-- resources/views/clearanceport/partials/topbar.blade.php --}}
<header class="topbar">
    <div class="d-flex align-center gap-2">
        <span class="topbar-title">{{ $pageTitle ?? 'Dashboard' }}</span>
    </div>
    <div class="topbar-actions">
        <span style="font-size:.78rem;color:var(--ink-light);">
            {{ now()->format('F j, Y') }}
        </span>
    </div>
</header>