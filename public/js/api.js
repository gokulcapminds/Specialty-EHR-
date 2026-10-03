// public/js/api.js - API Fetch Service Wrapper

export class ApiService {
    // Pages a signed-out visitor is allowed to be on: an "unauthenticated" answer there must not bounce them to the sign-in page.
    static onPublicPage() {
        const page = (window.location.hash.substring(1) || '').split('?')[0];
        return ['login', 'forgot-password', 'reset-password'].includes(page);
    }

    static getCsrfToken() {
        let token = window.sessionStorage.getItem('csrf_token');
        if (!token) {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                token = meta.getAttribute('content');
                if (token) {
                    window.sessionStorage.setItem('csrf_token', token);
                }
            }
        }
        return token || '';
    }

    static setCsrfToken(token) {
        if (token) {
            window.sessionStorage.setItem('csrf_token', token);
        }
    }

    static getBaseUrl() {
        const path = window.location.pathname;
        const match = path.match(/^(.*?\/public)(?:\/.*)?$/i);
        if (match) {
            return match[1] + '/';
        }
        const lastSlash = path.lastIndexOf('/');
        if (lastSlash !== -1) {
            return path.substring(0, lastSlash + 1);
        }
        return '/';
    }

    static async request(url, method = 'GET', data = null) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        const csrfToken = this.getCsrfToken();
        if (csrfToken && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(method.toUpperCase())) {
            headers['X-CSRF-Token'] = csrfToken;
        }

        const options = {
            method,
            headers
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const normalizedUrl = this.getBaseUrl() + url.replace(/^\//, '');

        try {
            const response = await fetch(normalizedUrl, options);
            if (response.status === 401 && url.indexOf('api/login') === -1) {
                // Session expired or unauthenticated
                if (!this.onPublicPage()) window.location.hash = '#login';
                return { status: 'error', message: 'Session expired.' };
            }

            const responseText = await response.text();
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (jsonErr) {
                console.error('API response is not valid JSON:', responseText);
                return { status: 'error', message: 'Server error or invalid response received.' };
            }

            if (result && result.csrf_token) {
                this.setCsrfToken(result.csrf_token);
            }
            if (result && result.new_csrf_token) {
                this.setCsrfToken(result.new_csrf_token);
            }

            if (result.status === 'error' && result.authenticated === false && url.indexOf('api/login') === -1) {
                if (!this.onPublicPage()) window.location.hash = '#login';
                return { status: 'error', message: 'Session expired.' };
            }
            
            // Auto update CSRF token if returned in response
            if (result.csrf_token) {
                this.setCsrfToken(result.csrf_token);
            }

            return result;
        } catch (error) {
            console.error('API Request failed:', error);
            return { status: 'error', message: 'Network error or service unavailable.' };
        }
    }

    static async upload(url, formData) {
        const headers = {
            'Accept': 'application/json'
        };

        const csrfToken = this.getCsrfToken();
        if (csrfToken) {
            headers['X-CSRF-Token'] = csrfToken;
        }

        const normalizedUrl = this.getBaseUrl() + url.replace(/^\//, '');

        try {
            const response = await fetch(normalizedUrl, {
                method: 'POST',
                headers,
                body: formData
            });

            if (response.status === 401 && url.indexOf('api/login') === -1) {
                if (!this.onPublicPage()) window.location.hash = '#login';
                return { status: 'error', message: 'Session expired.' };
            }

            const responseText = await response.text();
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (jsonErr) {
                console.error('API response is not valid JSON:', responseText);
                return { status: 'error', message: 'Server error or invalid response received.' };
            }

            if (result.csrf_token) {
                this.setCsrfToken(result.csrf_token);
            }

            return result;
        } catch (error) {
            console.error('API Upload failed:', error);
            return { status: 'error', message: 'Network error or upload failed.' };
        }
    }
}
window.ApiService = ApiService;
