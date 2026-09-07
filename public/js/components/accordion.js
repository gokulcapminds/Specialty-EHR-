// public/js/components/accordion.js

export class Accordion {
    static init(containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        const headers = container.querySelectorAll('.accordion-header');
        headers.forEach(header => {
            if (header.dataset.accordionBound === 'true') return;
            header.dataset.accordionBound = 'true';
            header.addEventListener('click', () => {
                const targetId = header.getAttribute('aria-controls');
                const content = document.getElementById(targetId);
                const isExpanded = header.getAttribute('aria-expanded') === 'true';

                // Close all other panels
                headers.forEach(otherHeader => {
                    if (otherHeader !== header) {
                        otherHeader.setAttribute('aria-expanded', 'false');
                        const otherContent = document.getElementById(otherHeader.getAttribute('aria-controls'));
                        if (otherContent) otherContent.classList.add('hidden');
                    }
                });

                // Toggle active panel
                header.setAttribute('aria-expanded', !isExpanded ? 'true' : 'false');
                if (content) {
                    content.classList.toggle('hidden', isExpanded);
                }
            });

            // Keyboard navigation support
            header.addEventListener('keydown', (e) => {
                if (e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    header.click();
                }
            });
        });
    }

    static openSection(containerSelector, index = 0) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        Accordion.init(containerSelector);

        const allHeaders = Array.from(container.querySelectorAll('.accordion-header'));
        const visibleHeaders = allHeaders.filter(h => {
            const parentItem = h.closest('.accordion-item');
            return parentItem && getComputedStyle(parentItem).display !== 'none' && !parentItem.classList.contains('hidden');
        });

        const targetHeader = visibleHeaders[index] || visibleHeaders[0] || allHeaders[index];

        allHeaders.forEach((header) => {
            const targetId = header.getAttribute('aria-controls');
            const content = document.getElementById(targetId);
            if (header === targetHeader) {
                header.setAttribute('aria-expanded', 'true');
                if (content) content.classList.remove('hidden');
            } else {
                header.setAttribute('aria-expanded', 'false');
                if (content) content.classList.add('hidden');
            }
        });
    }
}
window.Accordion = Accordion;
