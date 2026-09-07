// public/js/security/inspector.js - Security Inspector

export class SecurityInspector {
    static scan() {
        const violations = [];

        const isThirdParty = (el) => {
            if (!el) return false;
            if (el.tagName === 'IFRAME' || el === document.body || el.tagName === 'HTML') return true;
            if (el.closest('.swal2-container') || el.closest('.swal2-popup')) return true;
            if (typeof el.className === 'string' && el.className.includes('swal2')) return true;
            return false;
        };

        // 1. Scan for inline styles
        const inlineStyles = document.querySelectorAll('[style]');
        inlineStyles.forEach(el => {
            if (isThirdParty(el)) return;
            violations.push({ type: 'SECURITY', msg: 'Forbidden inline CSS style="" found on element:', el });
        });

        // 2. Scan for inline JS event handlers
        const allElements = document.getElementsByTagName('*');
        const inlineJsAttributes = [
            'onclick', 'onchange', 'onkeyup', 'onkeydown', 'onload', 
            'onsubmit', 'onblur', 'onfocus', 'onmouseenter', 'onmouseleave'
        ];

        for (let el of allElements) {
            if (isThirdParty(el)) continue;
            inlineJsAttributes.forEach(attr => {
                if (el.hasAttribute(attr)) {
                    violations.push({ type: 'SECURITY', msg: `Forbidden inline JS event handler ${attr}="" found on element:`, el });
                }
            });
        }

        // 3. Scan for missing img alt attributes
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (isThirdParty(img)) return;
            if (!img.hasAttribute('alt') || img.getAttribute('alt').trim() === '') {
                violations.push({ type: 'ACCESSIBILITY', msg: 'Image missing alt attribute or description:', el: img });
            }
        });

        // 4. Scan interactive elements for missing labels
        const buttons = document.querySelectorAll('button');
        buttons.forEach(btn => {
            if (isThirdParty(btn)) return;
            if (!btn.textContent.trim() && !btn.hasAttribute('aria-label')) {
                violations.push({ type: 'ACCESSIBILITY', msg: 'Button has no text content or aria-label:', el: btn });
            }
        });

        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (isThirdParty(input)) return;
            const type = input.getAttribute('type');
            if (type === 'hidden' || type === 'submit' || type === 'button') return;

            const id = input.getAttribute('id');
            const hasAriaLabel = input.hasAttribute('aria-label') || input.hasAttribute('aria-labelledby');
            const hasTitle = input.hasAttribute('title') || input.hasAttribute('placeholder');
            const isWrappedInLabel = !!input.closest('label');

            if (!id) {
                violations.push({ type: 'ACCESSIBILITY', msg: 'Input field missing unique "id" attribute for label mapping:', el: input });
            } else {
                const label = document.querySelector(`label[for="${id}"]`);
                if (!label && !isWrappedInLabel && !hasAriaLabel && !hasTitle) {
                    violations.push({ type: 'ACCESSIBILITY', msg: 'Input field missing corresponding label with matching "for" attribute:', el: input });
                }
            }
        });

        // Only output to console if violations are detected
        if (violations.length > 0) {
            console.group('%c [Security & Accessibility Inspector] Violations Found ', 'background: #b00000; color: #ffffff; font-weight: bold;');
            violations.forEach(v => {
                console.warn(`[${v.type} VIOLATION] ${v.msg}`, v.el);
            });
            console.log(`%c Scan complete. Total violations found: ${violations.length} `, 'background: #b00000; color: #ffffff;');
            console.groupEnd();
        }
    }
}

// Attach scanner to document load and dynamic SPA content renders
window.addEventListener('DOMContentLoaded', () => {
    // Initial Scan
    SecurityInspector.scan();

    // Observe future DOM updates
    const observer = new MutationObserver(() => {
        // Debounce to prevent multiple fires during large DOM renders
        if (window.inspectorTimeout) clearTimeout(window.inspectorTimeout);
        window.inspectorTimeout = setTimeout(() => {
            SecurityInspector.scan();
        }, 500);
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
