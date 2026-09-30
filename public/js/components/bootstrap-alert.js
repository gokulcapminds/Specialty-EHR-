// public/js/components/bootstrap-alert.js
// Drop-in replacement for the SweetAlert2 `Swal` API, backed by real Bootstrap 5 modals.
// Every call site in app.js was mechanically renamed from `Swal.` to `BsAlert.`; this class
// implements the subset of the SweetAlert2 surface actually used (fire/close/showValidationMessage/
// showLoading/getPopup/getCancelButton/getConfirmButton/clickConfirm/DismissReason) so none of the
// existing preConfirm/didOpen/.then() logic needed to change.

const ICONS = {
    success: { icon: 'fa-check-circle', color: '#16a34a' },
    error: { icon: 'fa-times-circle', color: '#dc2626' },
    warning: { icon: 'fa-exclamation-triangle', color: '#f59e0b' },
    info: { icon: 'fa-info-circle', color: '#0284c7' },
    question: { icon: 'fa-question-circle', color: '#64748b' },
};

export class BsAlert {
    static DismissReason = { cancel: 'cancel', backdrop: 'backdrop', close: 'close', esc: 'esc' };

    static _ensureEl() {
        if (BsAlert._el) return BsAlert._el;

        const el = document.createElement('div');
        el.className = 'modal fade';
        el.id = 'bsalert-modal';
        el.tabIndex = -1;
        el.setAttribute('aria-hidden', 'true');
        el.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title bsalert-title"></h5>
                        <button type="button" class="btn-close bsalert-x-btn" aria-label="Close"></button>
                    </div>
                    <div class="modal-body bsalert-body"></div>
                    <div class="modal-footer bsalert-footer"></div>
                </div>
            </div>
        `;
        document.body.appendChild(el);

        el.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') BsAlert._escPressed = true;
        });

        el.addEventListener('hidden.bs.modal', () => {
            if (!BsAlert._settled) {
                BsAlert._settled = true;
                const dismiss = BsAlert._escPressed ? BsAlert.DismissReason.esc : BsAlert.DismissReason.backdrop;
                BsAlert._resolve && BsAlert._resolve({ isConfirmed: false, isDenied: false, isDismissed: true, dismiss, value: undefined });
            }
        });

        BsAlert._el = el;
        BsAlert._instance = bootstrap.Modal.getOrCreateInstance(el, { backdrop: true, keyboard: true });
        return el;
    }

    static fire(opt1, opt2, opt3) {
        const options = (typeof opt1 === 'string')
            ? { title: opt1, text: opt2, icon: opt3 }
            : (opt1 || {});

        // A chained .then() (e.g. a delete-confirmation opened right after this one resolves)
        // runs as a microtask before the deferred hide() below ever fires — cancel it so the
        // popup morphs in place instead of racing a hide/show transition on the same instance.
        if (BsAlert._hideTimer) {
            clearTimeout(BsAlert._hideTimer);
            BsAlert._hideTimer = null;
        }

        const el = BsAlert._ensureEl();
        const dialog = el.querySelector('.modal-dialog');
        const titleEl = el.querySelector('.bsalert-title');
        const bodyEl = el.querySelector('.bsalert-body');
        const footerEl = el.querySelector('.bsalert-footer');
        const xBtn = el.querySelector('.bsalert-x-btn');

        dialog.style.maxWidth = options.width || '';
        xBtn.style.display = options.allowOutsideClick === false ? 'none' : '';

        const customClass = options.customClass || {};
        if (customClass.closeButton) xBtn.classList.add(customClass.closeButton);
        if (customClass.actions) footerEl.classList.add(customClass.actions);

        const iconDef = options.icon ? ICONS[options.icon] : null;
        const iconHtml = iconDef ? `<i class="fas ${iconDef.icon}" style="color:${iconDef.color}; margin-right:8px;"></i>` : '';
        titleEl.innerHTML = `${iconHtml}${options.title || ''}`;

        let inputId = null;
        let inputHtml = '';
        if (options.input === 'text' || options.input === 'textarea') {
            inputId = 'bsalert-input';
            const label = options.inputLabel ? `<label class="form-label fw-semibold" for="${inputId}">${options.inputLabel}</label>` : '';
            const value = options.inputValue != null ? options.inputValue : '';
            const placeholder = options.inputPlaceholder || '';
            inputHtml = options.input === 'textarea'
                ? `${label}<textarea id="${inputId}" class="form-control" rows="4" placeholder="${placeholder}">${value}</textarea>`
                : `${label}<input type="text" id="${inputId}" class="form-control" placeholder="${placeholder}" value="${value}">`;
        }

        let imageHtml = '';
        if (options.imageUrl) {
            imageHtml = `<div class="text-center mb-3"><img src="${options.imageUrl}" alt="${options.imageAlt || ''}" style="max-width:100%; max-height:60vh;"></div>`;
        }

        bodyEl.innerHTML = `
            ${imageHtml}
            <div class="bsalert-content">${options.html || options.text || ''}</div>
            ${inputHtml}
            <div class="bsalert-validation-message" style="display:none;"></div>
        `;

        const confirmColor = options.confirmButtonColor ? `style="background-color:${options.confirmButtonColor}; border-color:${options.confirmButtonColor};"` : '';
        const cancelColor = options.cancelButtonColor ? `style="background-color:${options.cancelButtonColor}; border-color:${options.cancelButtonColor}; color:#1e293b;"` : '';
        const denyColor = options.denyButtonColor ? `style="background-color:${options.denyButtonColor}; border-color:${options.denyButtonColor};"` : '';

        let footerHtml = '';
        if (options.showDenyButton) {
            footerHtml += `<button type="button" class="btn btn-danger bsalert-deny-btn" ${denyColor}>${options.denyButtonText || 'No'}</button>`;
        }
        if (options.showCancelButton) {
            footerHtml += `<button type="button" class="btn btn-secondary bsalert-cancel-btn" ${cancelColor}>${options.cancelButtonText || 'Cancel'}</button>`;
        }
        footerHtml += `<button type="button" class="btn btn-primary bsalert-confirm-btn" ${confirmColor}>${options.confirmButtonText || 'OK'}</button>`;
        footerEl.innerHTML = footerHtml;

        BsAlert._settled = false;
        BsAlert._escPressed = false;

        const popup = el.querySelector('.modal-content');
        const validationEl = bodyEl.querySelector('.bsalert-validation-message');
        const inputEl = inputId ? bodyEl.querySelector(`#${inputId}`) : null;

        const showValidation = (msg) => {
            validationEl.textContent = msg;
            validationEl.style.display = msg ? 'block' : 'none';
        };
        BsAlert._showValidation = showValidation;

        const settle = (result) => {
            if (BsAlert._settled) return;
            BsAlert._settled = true;
            BsAlert._resolve(result);
            BsAlert._hideTimer = setTimeout(() => {
                BsAlert._hideTimer = null;
                BsAlert._instance.hide();
            }, 0);
        };

        const resultPromise = new Promise((resolve) => { BsAlert._resolve = resolve; });

        const doConfirm = async () => {
            const inputValue = inputEl ? inputEl.value : undefined;

            if (options.inputValidator) {
                const err = await options.inputValidator(inputValue);
                if (err) { showValidation(err); return; }
                settle({ isConfirmed: true, isDenied: false, isDismissed: false, value: inputValue });
                return;
            }

            if (options.preConfirm) {
                showValidation('');
                const result = await options.preConfirm(inputValue);
                if (result === false) return;
                settle({ isConfirmed: true, isDenied: false, isDismissed: false, value: result });
                return;
            }

            settle({ isConfirmed: true, isDenied: false, isDismissed: false, value: inputValue });
        };
        BsAlert._doConfirm = doConfirm;

        popup.querySelector('.bsalert-confirm-btn').addEventListener('click', doConfirm);

        const cancelBtn = popup.querySelector('.bsalert-cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => settle({ isConfirmed: false, isDenied: false, isDismissed: true, dismiss: BsAlert.DismissReason.cancel, value: undefined }));
        }

        const denyBtn = popup.querySelector('.bsalert-deny-btn');
        if (denyBtn) {
            denyBtn.addEventListener('click', async () => {
                // Opt-in preDeny (SweetAlert2-compatible): returning false keeps the popup open.
                if (options.preDeny) {
                    showValidation('');
                    const result = await options.preDeny();
                    if (result === false) return;
                    settle({ isConfirmed: false, isDenied: true, isDismissed: false, value: result });
                    return;
                }
                settle({ isConfirmed: false, isDenied: true, isDismissed: false, value: undefined });
            });
        }

        xBtn.onclick = () => settle({ isConfirmed: false, isDenied: false, isDismissed: true, dismiss: BsAlert.DismissReason.close, value: undefined });

        BsAlert._instance.show();

        if (typeof options.didOpen === 'function') {
            options.didOpen(popup);
        }

        return resultPromise;
    }

    static close() {
        if (!BsAlert._el || BsAlert._settled) return;
        BsAlert._settled = true;
        BsAlert._resolve && BsAlert._resolve({ isConfirmed: false, isDenied: false, isDismissed: true, dismiss: BsAlert.DismissReason.close, value: undefined });
        BsAlert._hideTimer = setTimeout(() => {
            BsAlert._hideTimer = null;
            BsAlert._instance && BsAlert._instance.hide();
        }, 0);
    }

    static clickConfirm() {
        if (BsAlert._doConfirm) BsAlert._doConfirm();
    }

    static showValidationMessage(msg) {
        if (BsAlert._showValidation) BsAlert._showValidation(msg);
    }

    static showLoading() {
        if (!BsAlert._el) return;
        const footerEl = BsAlert._el.querySelector('.bsalert-footer');
        footerEl.innerHTML = `<div class="spinner-border text-primary" role="status" style="width:2rem; height:2rem;"><span class="visually-hidden">Loading...</span></div>`;
    }

    static getPopup() {
        return BsAlert._el ? BsAlert._el.querySelector('.modal-content') : null;
    }

    static getCancelButton() {
        return BsAlert._el ? BsAlert._el.querySelector('.bsalert-cancel-btn') : null;
    }

    static getConfirmButton() {
        return BsAlert._el ? BsAlert._el.querySelector('.bsalert-confirm-btn') : null;
    }

    static getDenyButton() {
        return BsAlert._el ? BsAlert._el.querySelector('.bsalert-deny-btn') : null;
    }
}

window.BsAlert = BsAlert;
