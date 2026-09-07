// public/js/router.js - Frontend SPA Routing Engine

export class Router {
    constructor() {
        this.routes = {
            'login': 'modules/login.php',
            'dashboard': 'modules/dashboard.php',
            'calendar': 'modules/calendar.php',
            'patients': 'modules/patients.php',
            'clinical': 'modules/clinical.php',
            'telehealth': 'modules/telehealth.php',
            'messaging': 'modules/messaging.php',
            'billing': 'modules/billing.php',
            'referrals': 'modules/referrals.php',
            'recalls': 'modules/recalls.php',
            'documents': 'modules/documents.php',
            'administration': 'modules/administration.php',
            'reports': 'modules/reports.php',
            'settings': 'modules/settings.php'
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
        if (me.status === 'error' && hash !== 'login') {
            window.location.hash = '#login';
            return;
        } else if (me.status === 'success' && hash === 'login') {
            window.location.hash = '#dashboard';
            return;
        }

        const moduleUrl = this.routes[hash] || this.routes['dashboard'];
        
        try {
            // Fetch view module fragment — add cache-buster to always get fresh HTML
            const cacheBuster = Math.floor(Date.now() / 60000); // changes every 60s
            const response = await fetch(moduleUrl + '?v=' + cacheBuster);
            if (!response.ok) throw new Error('Module fetch failed.');
            const html = await response.text();
            
            root.innerHTML = html;

            // Trigger module specific logic setup in app.js
            window.dispatchEvent(new CustomEvent('moduleLoaded', { detail: { module: hash } }));
            
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
