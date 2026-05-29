<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="dev-role" content="admin">
    <title>ClearCheck — Clearance Management</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/clearcheck.css') }}">
    <script>
        // ── Module configuration ──────────────────────────────────────────
        // API base URL — this module's own origin
        window.CLEARCHECK_API_BASE = "{{ config('app.url') }}";

        // Portal origin for SSO handshake — the parent portal that embeds this module
        window.PORTAL_ORIGIN = "{{ config('app.portal_url') }}";

        // SSO timeout (ms) — how long to wait for portal response before showing error
        window.SSO_TIMEOUT_MS = 8000;
        window.DEORIS_SSO_MODE = "module";
    </script>
</head>
<body>

{{-- Root container — shown until session is ready and redirect fires --}}
<div id="clearcheck-root" style="
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
">
    <div id="clearcheck-loader" style="
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
    ">
        <div style="
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 4px solid rgba(114,47,55,.15);
            border-top-color: #722F37;
            animation: spin .8s linear infinite;
        "></div>
        <p style="
            color: #722F37;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: .02em;
        ">Loading ClearCheck…</p>
        <p id="clearcheck-loader-error" style="
            color: #dc2626;
            font-size: 13px;
            display: none;
            max-width: 360px;
            text-align: center;
        "></p>
    </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

{{-- module-bridge.js MUST load first — it owns the SSO lifecycle --}}
<script src="{{ rtrim(config('app.portal_url', 'https://deoris.test'), '/') }}/module-bridge.js"></script>
{{-- clearcheck.js listens for module:ready, POSTs to /sso/exchange, then redirects --}}
<script src="{{ asset('js/clearcheck.js') }}?v={{ filemtime(public_path('js/clearcheck.js')) }}"></script>

</body>
</html>
