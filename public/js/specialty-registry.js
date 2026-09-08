// public/js/specialty-registry.js
// Canonical specialty registry — JS mirror of backend/config/specialties.php.
// Single source of truth for the 7 planned specialty engines. Every dropdown
// that lets a user pick a specialty (login, appointment, waiting list,
// referral, encounter) should render its options from this list instead of
// hardcoding its own <option> set, so the value strings never drift apart.

// label preserves the exact display text already shown across the app's
// various dropdowns today, so switching a dropdown to render from this
// registry changes only the underlying <option value>, never what the user
// sees.
window.SPECIALTY_REGISTRY = [
    { key: 'Cardiology', label: 'Cardiology EHR', dataColumn: 'cardio_data', formId: 'cardio-form', panelClass: 'cardio-only' },
    { key: 'Orthopedics', label: 'Orthopedic EHR', dataColumn: 'ortho_data', formId: 'ortho-form', panelClass: 'ortho-only' },
    { key: 'Dermatology', label: 'Dermatology EHR', dataColumn: 'derma_data', formId: 'derma-form', panelClass: 'derma-only' },
    { key: 'Neurology', label: 'Neurology EHR', dataColumn: 'neuro_data', formId: 'neuro-form', panelClass: 'neuro-only' },
    { key: 'Oncology', label: 'Oncology EHR', dataColumn: 'onco_data', formId: 'onco-form', panelClass: 'onco-only' },
    { key: 'Ophthalmology', label: 'Ophthalmology EHR (covers optometry)', dataColumn: 'ophthal_data', formId: 'ophthal-form', panelClass: 'ophthal-only' },
    { key: 'Physical Therapy', label: 'Physical Therapy EHR (covers chiropractic)', dataColumn: 'pt_data', formId: 'pt-form', panelClass: 'pt-only' }
];

// Renders <option value="key">label</option> for every specialty.
// selectedKey (optional) marks that option as selected.
window.renderSpecialtyOptions = function(selectedKey) {
    return window.SPECIALTY_REGISTRY.map(function(s) {
        const sel = (selectedKey && s.key === selectedKey) ? ' selected' : '';
        return `<option value="${s.key}"${sel}>${s.label}</option>`;
    }).join('');
};
