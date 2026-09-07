// public/js/components/modal.js

export class Modal {
    static open(id, title, contentHtml, onConfirm = null, confirmText = 'Save', confirmBtnClass = 'btn-primary', cancelText = 'Cancel', customFooterHtml = null) {
        // Build modal dialog
        const modal = document.createElement('div');
        modal.id = id;
        modal.className = 'modal-backdrop';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-labelledby', `${id}-title`);
        
        let footerHtml = '';
        if (customFooterHtml) {
            footerHtml = customFooterHtml;
        } else if (onConfirm) {
            footerHtml = `
                <button class="btn btn-secondary modal-cancel">${cancelText}</button>
                <button class="btn ${confirmBtnClass} modal-save">${confirmText}</button>
            `;
        } else {
            footerHtml = `
                <button class="btn btn-secondary modal-cancel">${cancelText || 'Close'}</button>
            `;
        }

        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-header">
                    <h2 id="${id}-title">${title}</h2>
                    <button class="modal-close" aria-label="Close modal">&times;</button>
                </div>
                <div class="modal-body">
                    ${contentHtml}
                </div>
                <div class="modal-footer">
                    ${footerHtml}
                </div>
            </div>
        `;

        // CSS styles injection to keep style rules within public/css/theme.css
        // Style overrides are forbidden inline, so we rely on parent class triggers or central CSS definitions
        document.body.appendChild(modal);

        // Trap focus inside modal
        const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex="0"]');
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        modal.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        lastElement.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        firstElement.focus();
                        e.preventDefault();
                    }
                }
            } else if (e.key === 'Escape') {
                Modal.close(id);
            }
        });

        // Close actions
        const closeBtn = modal.querySelector('.modal-close');
        if (closeBtn) closeBtn.addEventListener('click', () => Modal.close(id));

        const cancelBtn = modal.querySelector('.modal-cancel');
        if (cancelBtn) cancelBtn.addEventListener('click', () => Modal.close(id));

        if (onConfirm) {
            const saveBtn = modal.querySelector('.modal-save');
            if (saveBtn) {
                saveBtn.addEventListener('click', () => {
                    onConfirm(modal);
                });
            }
        }

        const firstInput = modal.querySelector('.modal-body input, .modal-body select, .modal-body textarea');
        if (firstInput) {
            firstInput.focus();
        } else if (firstElement) {
            firstElement.focus();
        }
    }

    static close(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.remove();
        }
    }
}
window.Modal = Modal;
