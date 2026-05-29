/* ============================================================
   Deor & Dune E-Clearance System — Main JS
   ============================================================ */

// ── Toast ──────────────────────────────────────────────────────
function showToast(message, type = 'info') {
    let stack = document.getElementById('toast-stack');
    if (!stack) {
        stack = document.createElement('div');
        stack.id = 'toast-stack';
        stack.className = 'toast-stack';
        document.body.appendChild(stack);
    }
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
    const t = document.createElement('div');
    t.className = `toast-item ${type}`;
    t.innerHTML = `<i class="fas ${icons[type] || icons.info}" style="font-size:.95rem;flex-shrink:0;"></i><span class="toast-msg">${message}</span>`;
    stack.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(30px)'; setTimeout(() => t.remove(), 300); }, 3500);
}

// ── Modal ──────────────────────────────────────────────────────
function openModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-backdrop')) closeModal(e.target.id);
});

// ── Mobile Sidebar Toggle ──────────────────────────────────────
const mobileToggle = document.getElementById('mobile-toggle');
if (mobileToggle) {
    mobileToggle.addEventListener('click', () => {
        document.querySelector('.sidebar')?.classList.toggle('open');
    });
}

// ── Active Nav Highlight ───────────────────────────────────────
(function() {
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') === currentPath) link.classList.add('active');
    });
})();

// ── Progress Bar Animate on load ──────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.progress-fill[data-width]').forEach(bar => {
        const w = bar.getAttribute('data-width');
        setTimeout(() => { bar.style.width = w + '%'; }, 200);
    });
});

// ── File Upload Preview ────────────────────────────────────────
function handleFilePreview(inputEl, previewContainerId) {
    const file = inputEl.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) { showToast('File too large. Max 5MB allowed.', 'error'); inputEl.value = ''; return; }
    const container = document.getElementById(previewContainerId);
    if (!container) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        if (file.type.startsWith('image/')) {
            container.innerHTML = `<img src="${e.target.result}" style="max-width:100%;max-height:160px;border-radius:10px;border:1.5px solid var(--gold-light);margin-top:10px;">`;
        } else {
            container.innerHTML = `<div style="display:flex;align-items:center;gap:10px;margin-top:10px;padding:12px;background:var(--parchment);border-radius:8px;border:1px solid var(--parchment-dark);"><i class="fas fa-file-pdf fa-2x" style="color:var(--danger);"></i><span style="font-size:.82rem;color:var(--ink-mid);">${file.name}</span></div>`;
        }
    };
    reader.readAsDataURL(file);
    showToast(`"${file.name}" ready`, 'success');
}

// ── Confirm Action ─────────────────────────────────────────────
// ✅ FIX: Replaced window.confirm() with a custom DOM modal.
//    window.confirm() is blocked in sandboxed iframes (claude.ai preview,
//    certain admin panels, etc). This custom dialog is never blocked.
(function buildConfirmModal() {
    // Inject styles once
    const style = document.createElement('style');
    style.textContent = `
        #cp-confirm-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: rgba(0,0,0,.45);
            align-items: center;
            justify-content: center;
        }
        #cp-confirm-backdrop.open { display: flex; }
        #cp-confirm-box {
            background: #fff;
            border-radius: 10px;
            padding: 28px 32px 22px;
            max-width: 380px;
            width: 90%;
            box-shadow: 0 8px 32px rgba(0,0,0,.18);
            font-family: inherit;
        }
        #cp-confirm-box p {
            margin: 0 0 22px;
            font-size: .97rem;
            color: #222;
            line-height: 1.5;
        }
        #cp-confirm-box .cp-btn-row {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        #cp-confirm-cancel {
            padding: 8px 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #f5f5f5;
            cursor: pointer;
            font-size: .88rem;
            color: #333;
        }
        #cp-confirm-ok {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            background: #8B4453;
            color: #fff;
            cursor: pointer;
            font-size: .88rem;
            font-weight: 600;
        }
        #cp-confirm-cancel:hover { background: #e5e5e5; }
        #cp-confirm-ok:hover     { background: #6e3342; }
    `;
    document.head.appendChild(style);

    // Build modal DOM
    const backdrop = document.createElement('div');
    backdrop.id = 'cp-confirm-backdrop';
    backdrop.innerHTML = `
        <div id="cp-confirm-box">
            <p id="cp-confirm-msg"></p>
            <div class="cp-btn-row">
                <button id="cp-confirm-cancel">Cancel</button>
                <button id="cp-confirm-ok">Confirm</button>
            </div>
        </div>
    `;
    document.body.appendChild(backdrop);

    let _resolve = null;

    document.getElementById('cp-confirm-ok').addEventListener('click', () => {
        backdrop.classList.remove('open');
        if (_resolve) _resolve(true);
    });
    document.getElementById('cp-confirm-cancel').addEventListener('click', () => {
        backdrop.classList.remove('open');
        if (_resolve) _resolve(false);
    });
    // Click outside box to cancel
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) {
            backdrop.classList.remove('open');
            if (_resolve) _resolve(false);
        }
    });
    // Escape key to cancel
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && backdrop.classList.contains('open')) {
            backdrop.classList.remove('open');
            if (_resolve) _resolve(false);
        }
    });

    // Public API — returns a Promise<boolean>
    window.showConfirm = function(message) {
        document.getElementById('cp-confirm-msg').textContent = message;
        backdrop.classList.add('open');
        document.getElementById('cp-confirm-ok').focus();
        return new Promise((resolve) => { _resolve = resolve; });
    };
})();

// confirmAction — drop-in replacement for the old window.confirm pattern.
// Usage: confirmAction('Are you sure?', () => doSomething())
function confirmAction(message, callback) {
    window.showConfirm(message).then((confirmed) => {
        if (confirmed && typeof callback === 'function') callback();
    });
}