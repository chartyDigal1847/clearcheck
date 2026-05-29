/**
 * clearcheck.js — ClearCheck boot script
 *
 * Listens for module:ready from the centralized portal bridge.
 *
 * SECURITY: Identity should come from event.detail.user/window.PORTAL_USER
 * after the bridge validates portal origin and exchanges the single-use token.
 * New ClearCheck UI code should boot from that in-memory identity and remove
 * the legacy server-session hydration flow below.
 */

if (window.__CLEARCHECK_LOADED__) {
    console.warn('[clearcheck] Already loaded — skipping.');
} else {
    window.__CLEARCHECK_LOADED__ = true;

    console.log('[clearcheck] Script loaded.');

    function showInitError(message) {
        console.error('[clearcheck] Init error:', message);
        var el = document.getElementById('clearcheck-loader-error');
        if (el) { el.style.display = 'block'; el.textContent = message; }
    }

    window.addEventListener('module:ready', async function (event) {
        console.log('[clearcheck] module:ready received.');

        var user  = (event.detail && event.detail.user) || window.PORTAL_USER || {};
        var token = window.SSO_TOKEN || null;

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) { showInitError('CSRF token missing.'); return; }
        var csrf = csrfMeta.getAttribute('content');

        try {
            var res = await fetch('/sso/exchange', {
                method:      'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    token:  user.id ? null : token,
                    // Standalone / dev fallback fields — ignored by the server
                    // when a real token is present
                    id: user.id || '',
                    embedded: !!(event.detail && event.detail.embedded),
                    _standalone_role:  user.role  || 'admin',
                    _standalone_name:  user.name  || 'Dev User',
                    _standalone_email: user.email || 'dev@localhost',
                    role: user.role || 'admin',
                    name: user.name || 'Dev User',
                    email: user.email || 'dev@localhost',
                }),
            });

            var data = await res.json();

            if (!res.ok) {
                showInitError('Legacy dashboard setup failed (' + res.status + '): ' + (data.error || 'unknown'));
                return;
            }

            var redirectUrl = data.redirect;
            if (!redirectUrl) {
                showInitError('No redirect URL returned from server.');
                return;
            }

            console.log('[clearcheck] Legacy dashboard ready. Redirecting to:', redirectUrl);
            window.location.href = redirectUrl;

        } catch (err) {
            showInitError('Failed to set up legacy dashboard: ' + err.message);
        }
    });

    window.addEventListener('module:error', function (event) {
        var reason = (event.detail && event.detail.error) || 'Unknown error';
        showInitError('Authentication failed: ' + reason);
    });
}
