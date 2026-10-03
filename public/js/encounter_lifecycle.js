/**
 * encounter_lifecycle.js
 * Phase 2 - Core Clinical Encounter SRS
 *
 * Wires up:
 *  - Save Draft button            (encounter_status = in_progress)
 *  - Sign & Finalize button       (POST /api/clinical/notes/{id}/sign)
 *  - Lock UI state                (dims inputs, shows Addendum btn)
 *  - Addendum modal               (POST /api/clinical/notes/{id}/addendum)
 *  - Status badge updates
 *  - BMI auto-calculation (client-side preview; server always recalculates)
 *
 * Loaded after app.js so ApiService and Toast are already available.
 */

(function () {
    'use strict';

    // -------------------------------------------------------
    // 1. BMI auto-calculation preview (VIT-CORE-002/003)
    //    Server recalculates on save; this is display-only.
    // -------------------------------------------------------
    function recalcBmi() {
        const heightEl = document.getElementById('vital-height');
        const weightEl = document.getElementById('vital-weight');
        const bmiEl    = document.getElementById('vital-bmi');
        if (!heightEl || !weightEl || !bmiEl) return;

        const h = parseFloat(heightEl.value);
        const w = parseFloat(weightEl.value);
        if (h > 0 && w > 0) {
            const bmi = ((w * 703) / (h * h)).toFixed(1);
            bmiEl.value = bmi;

            // Colour-code the BMI field
            let category = '';
            if      (bmi < 18.5) { bmiEl.style.color = '#64b5f6'; category = 'Underweight'; }
            else if (bmi < 25)   { bmiEl.style.color = '#81c784'; category = 'Normal';      }
            else if (bmi < 30)   { bmiEl.style.color = '#ffd54f'; category = 'Overweight';  }
            else                 { bmiEl.style.color = '#ef9a9a'; category = 'Obese';        }

            bmiEl.title = `BMI ${bmi} — ${category}`;
        } else {
            bmiEl.value = '';
            bmiEl.style.color = '';
            bmiEl.title = '';
        }
    }

    document.addEventListener('input', (e) => {
        if (e.target && (e.target.id === 'vital-height' || e.target.id === 'vital-weight')) {
            recalcBmi();
        }
    });

    // -------------------------------------------------------
    // 2. Status badge helper
    // -------------------------------------------------------
    const STATUS_LABELS = {
        draft:           'Draft',
        in_progress:     'In Progress',
        ready_for_sign:  'Ready for Sign',
        signed:          'Signed',
        locked:          'Locked',
    };

    function setStatusBadge(status) {
        const badge = document.getElementById('encounter-status-badge');
        const lockBadge = document.getElementById('encounter-lock-badge');
        if (!badge) return;

        // Reset classes
        badge.className = `enc-status-badge enc-status-${status}`;
        badge.textContent = STATUS_LABELS[status] || status;

        if (lockBadge) {
            if (status === 'locked') {
                lockBadge.classList.remove('hidden');
            } else {
                lockBadge.classList.add('hidden');
            }
        }
    }

    // -------------------------------------------------------
    // 3. Apply locked UI state (SIGN-004)
    //    Dims all inputs, shows Addendum btn, hides save btns.
    // -------------------------------------------------------
    function applyLockedUI(isLocked) {
        const accordion = document.getElementById('clinical-accordion');
        const signBtn   = document.getElementById('sign-finalize-btn');
        const saveDraft = document.getElementById('save-draft-btn');
        const updateBtn = document.getElementById('update-encounter-btn');
        const addendumBtn = document.getElementById('add-addendum-btn');

        if (isLocked) {
            if (accordion) accordion.classList.add('encounter-locked');
            if (signBtn)    signBtn.classList.add('hidden');
            if (saveDraft)  saveDraft.classList.add('hidden');
            if (updateBtn)  updateBtn.classList.add('hidden');
            if (addendumBtn) addendumBtn.classList.remove('hidden');
        } else {
            if (accordion) accordion.classList.remove('encounter-locked');
            if (signBtn)    signBtn.classList.remove('hidden');
            if (saveDraft)  saveDraft.classList.remove('hidden');
            if (addendumBtn) addendumBtn.classList.add('hidden');
        }
    }

    // -------------------------------------------------------
    // 4. Populate encounter status when modal opens
    //    Hooks into window.populateEncounterModal if available.
    // -------------------------------------------------------
    const _origPopulate = window.populateEncounterModal;
    window.populateEncounterModal = function (note, isFresh = false) {
        if (typeof _origPopulate === 'function') {
            _origPopulate(note, isFresh);
        }

        if (!note) return;

        // Status badge
        const status = note.encounter_status || 'in_progress';
        setStatusBadge(status);
        applyLockedUI(status === 'locked' || note.lock_state == 1);

        // Populate new Phase 2 fields
        const visitTypeEl = document.getElementById('encounter-visit-type');
        if (visitTypeEl && note.visit_type) {
            // an older record may hold a value that is no longer in the list: keep it selectable so saving doesn't blank it
            if (!Array.from(visitTypeEl.options).some(o => o.value === note.visit_type)) visitTypeEl.add(new Option(note.visit_type, note.visit_type));
            visitTypeEl.value = note.visit_type;
        }

        const modeEl = document.getElementById('encounter-mode-select');
        if (modeEl && note.encounter_mode) modeEl.value = note.encounter_mode;

        const painEl = document.getElementById('vital-pain-score');
        if (painEl && note.vital_pain_score != null) painEl.value = note.vital_pain_score;

        // Store appointment_id for inheritance
        window.activeAppointmentId = note.appointment_id || null;
    };

    // -------------------------------------------------------
    // 5. Save Draft handler
    //    Calls submitEncounter with encounter_status = in_progress
    //    (the existing submitEncounter function handles the PUT/POST)
    // -------------------------------------------------------
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('#save-draft-btn');
        if (!btn) return;

        // Inject draft status into the payload that submitEncounter will build
        window._overrideEncounterStatus = 'in_progress';

        if (typeof window.submitEncounter === 'function') {
            window.submitEncounter(e);
        }

        delete window._overrideEncounterStatus;
    });

    // -------------------------------------------------------
    // 6. Sign & Finalize handler (SIGN-001 to SIGN-003)
    //    1. First saves the encounter (PUT/POST) so latest data is persisted.
    //    2. Then calls /api/clinical/notes/{id}/sign to lock.
    // -------------------------------------------------------
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('#sign-finalize-btn');
        if (!btn) return;

        const patientId = window.activeClinicalPatientId || document.getElementById('clinical-patient-select')?.value;
        const noteId    = window.activeClinicalNoteId;

        if (!noteId) {
            // Must save first to get a note ID before signing
            const toast = (typeof Toast !== 'undefined') ? Toast : window.Toast;
            if (toast) toast.show('Please save the encounter as a draft first before signing.', 'warning');
            return;
        }

        // Confirm action
        const confirmed = window.confirm(
            '⚠️  Sign & Finalize this encounter?\n\n' +
            'Once signed, the record will be locked and can only be corrected via an Addendum.\n\n' +
            'Required before signing:\n• Chief Complaint\n• At least one ICD-10 Diagnosis\n\nProceed?'
        );
        if (!confirmed) return;

        btn.disabled = true;
        btn.textContent = '⏳ Signing...';

        try {
            // Collect signature data
            const sigData = document.getElementById('sig-data-url')?.value || '';

            const res = await ApiService.request(
                `/api/clinical/notes/${noteId}/sign`,
                'POST',
                { signed_signature_data: sigData }
            );

            if (res && res.status === 'success') {
                setStatusBadge('locked');
                applyLockedUI(true);

                if (typeof Toast !== 'undefined') {
                    Toast.show('✅ Encounter signed and locked. Sent to billing queue.', 'success');
                } else if (window.Toast) {
                    window.Toast.show('✅ Encounter signed and locked. Sent to billing queue.', 'success');
                }

                // Refresh encounter lists if available
                if (typeof window.refreshPatientEncounters === 'function') {
                    window.refreshPatientEncounters(patientId);
                }
                if (typeof window.refreshBillingData === 'function') {
                    try { window.refreshBillingData(); } catch (_) {}
                }
            } else {
                const msg = (res && res.message) ? res.message : 'Failed to sign encounter.';
                alert('❌ Sign failed: ' + msg);
            }
        } catch (err) {
            console.error('Sign & Lock error:', err);
            alert('❌ An error occurred while signing. Please try again.');
        } finally {
            btn.disabled = false;
            btn.textContent = '✅ Sign & Finalize';
        }
    });

    // -------------------------------------------------------
    // 7. Addendum modal controls (SIGN-005 / SIGN-006)
    // -------------------------------------------------------
    document.addEventListener('click', (e) => {
        // Open addendum modal
        if (e.target.closest('#add-addendum-btn')) {
            const modal = document.getElementById('addendum-modal');
            if (modal) {
                bootstrap.Modal.getOrCreateInstance(modal, { backdrop: 'static', keyboard: false }).show();
                document.getElementById('addendum-text').value = '';
                const msg = document.getElementById('addendum-status-msg');
                if (msg) msg.style.display = 'none';
            }
            return;
        }

        // Close addendum modal
        if (e.target.closest('#close-addendum-modal-btn') || e.target.closest('#cancel-addendum-btn')) {
            const modal = document.getElementById('addendum-modal');
            if (modal) {
                bootstrap.Modal.getOrCreateInstance(modal, { backdrop: 'static', keyboard: false }).hide();
            }
            return;
        }

        // Submit addendum
        if (e.target.closest('#submit-addendum-btn')) {
            submitAddendum();
            return;
        }
    });

    async function submitAddendum() {
        const noteId = window.activeClinicalNoteId;
        if (!noteId) {
            alert('No active encounter selected.');
            return;
        }

        const text = document.getElementById('addendum-text')?.value?.trim();
        if (!text) {
            alert('Addendum note text is required.');
            return;
        }

        const submitBtn = document.getElementById('submit-addendum-btn');
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = '⏳ Submitting...'; }

        try {
            const sigData = document.getElementById('sig-data-url')?.value || '';
            const res = await ApiService.request(
                `/api/clinical/notes/${noteId}/addendum`,
                'POST',
                { addendum_note: text, signed_signature_data: sigData }
            );

            const statusEl = document.getElementById('addendum-status-msg');

            if (res && res.status === 'success') {
                if (statusEl) {
                    statusEl.style.display = 'block';
                    statusEl.style.background = 'rgba(76,175,80,0.15)';
                    statusEl.style.color = '#81c784';
                    statusEl.textContent = '✅ Addendum submitted successfully and appended to the locked record.';
                }
                // Close modal after 2 seconds
                setTimeout(() => {
                    const modal = document.getElementById('addendum-modal');
                    if (modal) { bootstrap.Modal.getOrCreateInstance(modal, { backdrop: 'static', keyboard: false }).hide(); }
                }, 2000);
            } else {
                const msg = (res && res.message) ? res.message : 'Failed to submit addendum.';
                if (statusEl) {
                    statusEl.style.display = 'block';
                    statusEl.style.background = 'rgba(244,67,54,0.15)';
                    statusEl.style.color = '#ef9a9a';
                    statusEl.textContent = '❌ ' + msg;
                }
            }
        } catch (err) {
            console.error('Addendum error:', err);
            alert('An error occurred. Please try again.');
        } finally {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit Addendum'; }
        }
    }

    // -------------------------------------------------------
    // 8. Reset status badge/lock when modal opens fresh
    // -------------------------------------------------------
    document.addEventListener('click', (e) => {
        // When a fresh encounter modal is opened (not for editing)
        if (e.target.closest('#new-encounter-btn') || e.target.closest('#create-encounter-btn')) {
            setStatusBadge('draft');
            applyLockedUI(false);
            window.activeAppointmentId = null;
        }
    });

    // -------------------------------------------------------
    // 9. Expose helpers globally for use from app.js callbacks
    // -------------------------------------------------------
    window.EHREncounterLifecycle = {
        setStatusBadge,
        applyLockedUI,
        recalcBmi,
    };

    console.log('[encounter_lifecycle.js] Phase 2 lifecycle handlers registered.');
})();
