<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ClearCheck | DEORIS')</title>
    <script>
        if (window.self !== window.top) document.documentElement.classList.add('is-framed');
        window.DEORIS_SSO_MODE = "module";
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/clearanceport.css') }}?v={{ filemtime(public_path('css/clearanceport.css')) }}">
</head>
<body>
    <div class="app-shell">
        <header class="app-header" id="appHeader">
            <div class="app-header-inner">
                <a href="/" class="app-brand" aria-label="ClearCheck home">
                    <div class="app-brand-badge">C</div>
                    <div>
                        <div class="app-brand-name">ClearCheck</div>
                        <small class="app-brand-sub">Clearance Management</small>
                    </div>
                </a>
                <div class="app-header-spacer"></div>
                <div class="app-user-chip">
                    <div class="app-user-avatar" id="userInitial">{{ substr(session('sso_name', 'U'), 0, 1) }}</div>
                    <span id="userName">{{ session('sso_name', 'Portal User') }}</span>
                    <span class="app-role-badge" id="userRole">{{ session('sso_role', 'student') }}</span>
                </div>
            </div>
        </header>

        <main class="app-main">
            <section class="app-content">
                <div id="workspace" class="workspace">
                    @yield('content')
                </div>
            </section>
        </main>
    </div>

    <!-- The SSO bridge script must be loaded to handle logout/resizing if in portal -->
    <script src="{{ rtrim(config('app.portal_url', 'https://deoris.test'), '/') }}/module-bridge.js"></script>
    @stack('scripts')
</body>
</html>
