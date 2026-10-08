// public/js/router.js - Frontend SPA Routing Engine

export class Router {
    constructor() {
        this.routes = {
            'login': 'modules/login.php',
            'forgot-password': 'modules/forgot_password.php',
            'reset-password': 'modules/reset_password.php',
            'dashboard': 'modules/dashboard.php',
            'calendar': 'modules/calendar.php',
            'patients': 'modules/patients.php',
            'encounters': 'modules/encounters.php',
            'clinical': 'modules/clinical.php',
            'telehealth': 'modules/telehealth.php',
            'messaging': 'modules/messaging.php',
            'billing': 'modules/billing.php',
            'referrals': 'modules/referrals.php',
            'orders': 'modules/orders.php',
            'imaging': 'modules/imaging.php',
            'medications': 'modules/medications.php',
            'diagnoses': 'modules/diagnoses.php',
            'recalls': 'modules/recalls.php',
            'documents': 'modules/documents.php',
            'administration': 'modules/administration.php',
            'reports': 'modules/reports.php',
            'settings': 'modules/settings.php',
            'change-password': 'modules/change_password.php'
        };

        window.addEventListener('hashchange', () => this.handleRouting());
    }

    async init() {
        if (window.location.search.includes('username=') || window.location.search.includes('password=')) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.hash;
            window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
        }
        await this.handleRouting();
    }

    async handleRouting() {
        // Clean up any lingering floating tooltips
        const floatingTooltip = document.getElementById('floating-sidebar-tooltip');
        if (floatingTooltip) {
            floatingTooltip.style.display = 'none';
            floatingTooltip.classList.remove('active');
        }

        const root = document.getElementById('app-root');
        let fullHash = window.location.hash.substring(1) || 'dashboard';
        let hashParts = fullHash.split('?');
        let hash = hashParts[0];
        // Check if user is logged in (session check)
        const me = await ApiService.request('/api/me');
        // Pages a signed-out visitor may open
        const publicPages = ['login', 'forgot-password', 'reset-password'];
        if (me.status === 'error' && !publicPages.includes(hash)) {
            window.location.hash = '#login';
            return;
        } else if (me.status === 'success' && publicPages.includes(hash)) {
            window.location.hash = '#dashboard';
            return;
        }

        // Admin-issued temporary password: force the change-password screen until it's cleared,
        // and keep an already-changed user from wandering back onto it.
        const mustChangePassword = me.status === 'success' && me.user && !!me.user.force_password_change;
        if (mustChangePassword && hash !== 'change-password') {
            window.location.hash = '#change-password';
            return;
        }
        if (!mustChangePassword && hash === 'change-password') {
            window.location.hash = '#dashboard';
            return;
        }

        const moduleUrl = this.routes[hash] || this.routes['dashboard'];

        // sidebar.php is re-included inside every module fragment, so root.innerHTML below
        // destroys and recreates the sidebar <nav> on every navigation, resetting its own
        // scrollTop to 0 (it's the scrollable region itself, not an inner <ul>) - most visible
        // on deep items like Reports/Administration that need scrolling to reach.
        const prevSidebar = document.getElementById('app-sidebar');
        const prevSidebarScrollTop = prevSidebar ? prevSidebar.scrollTop : null;

        try {
            // Fetch view module fragment — add cache-buster to always get fresh HTML
            const cacheBuster = Math.floor(Date.now() / 60000); // changes every 60s
            const response = await fetch(moduleUrl + '?v=' + cacheBuster);
            if (!response.ok) throw new Error('Module fetch failed.');
            const html = await response.text();

            root.innerHTML = html;

            // Trigger module specific logic setup in app.js
            window.dispatchEvent(new CustomEvent('moduleLoaded', { detail: { module: hash } }));

            // Restore sidebar scroll position after initSidebarGroups() (run synchronously by the
            // moduleLoaded listener above) has re-applied the open/active classes, so the restored
            // position matches the now-settled layout.
            if (prevSidebarScrollTop !== null) {
                const newSidebar = document.getElementById('app-sidebar');
                if (newSidebar) newSidebar.scrollTop = prevSidebarScrollTop;
            }
            
            // Announce page load for screen readers
            const announcer = document.getElementById('sr-announcer');
            if (announcer) {
                announcer.textContent = `${hash.toUpperCase()} workspace page loaded successfully.`;
            }
        } catch (error) {
            root.innerHTML = `<div class="card"><h2 class="error-text">Failed to load page.</h2></div>`;
        }
    }
}
window.AppRouter = new Router();
window.addEventListener('DOMContentLoaded', () => window.AppRouter.init());
