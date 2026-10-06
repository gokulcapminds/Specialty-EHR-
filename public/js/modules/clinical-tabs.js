/* =====================================================================
   clinical-tabs.js  — Inline Dashboard Encounter Override
   10 tabs: Chief Complaint | Medication Review | Allergy Review | Vitals |
            Examination | Assessment | Plan | Lab Orders | Billing | Sign Encounter
   Medication Review / Allergy Review read and write the real medications /
   patient_allergies tables (ApiService `/api/medications`, `/api/allergies`),
   not client-only state — unlike this file's Plan-tab medication table.
   ===================================================================== */

document.addEventListener('DOMContentLoaded', () => { initClinicalTabs(); });

function initClinicalTabs() {
    const tabsContainer = document.getElementById('vertical-tabs-nav');
    if (!tabsContainer) { setTimeout(initClinicalTabs, 500); return; }
    const panes = document.querySelectorAll('.tab-pane');
    if (panes.length === 0) return;
    if (tabsContainer.children.length > 0) return;
    let currentTabIndex = 0;
    const tabButtons = [];
    const requestedTabs = ["Chief Complaint", "Vitals", "Examination", "Assessment", "Plan", "Lab Orders", "Sign Encounter", "Billing"];
    requestedTabs.forEach((tabName, index) => {
        const btn = document.createElement('button');
        btn.className = 'encounter-tab';
        btn.textContent = tabName;
        btn.dataset.index = index;
        if (index === 0) btn.classList.add('active');
        btn.addEventListener('click', e => { e.preventDefault(); switchTab(index); });
        tabsContainer.appendChild(btn);
        tabButtons.push(btn);
    });
    function switchTab(index) {
        if (index < 0 || index >= requestedTabs.length) return;
        tabButtons.forEach(t => t.classList.remove('active'));
        panes.forEach(p => { p.classList.remove('active'); p.style.setProperty('display', 'none', 'important'); });
        tabButtons[index].classList.add('active');
        let tp = panes[Math.min(index, panes.length - 1)];
        if (index === 0) tp = panes[2] || panes[0]; if (index === 1) tp = panes[0];
        if (index === 2) tp = panes[2] || panes[1]; if (index === 3) tp = panes[4] || panes[2];
        if (index === 4) tp = panes[9] || panes[3]; if (index === 5) tp = panes[10] || panes[4];
        if (index === 6) tp = panes[11] || panes[5] || panes[panes.length - 1];
        if (index === 7) tp = panes[panes.length - 1];
        if (tp) { tp.classList.add('active'); tp.style.setProperty('display', 'block', 'important'); }
        currentTabIndex = index;
    }
    switchTab(0);
    const prevBtn = document.getElementById('prev-tab-btn');
    const nextBtnTop = document.getElementById('next-tab-top-btn');
    const nextBtnBot = document.getElementById('next-tab-bottom-btn');
    const saveDraftBtn = document.getElementById('save-draft-btn');
    if (prevBtn) prevBtn.addEventListener('click', e => { e.preventDefault(); switchTab(currentTabIndex - 1); });
    const goNext = e => { e.preventDefault(); switchTab(currentTabIndex + 1); };
    if (nextBtnTop) nextBtnTop.addEventListener('click', goNext);
    if (nextBtnBot) nextBtnBot.addEventListener('click', goNext);
    if (saveDraftBtn) saveDraftBtn.addEventListener('click', e => { e.preventDefault(); const b = document.getElementById('save-encounter-btn'); if (b) b.click(); });
}
initClinicalTabs();

/* ── Inject CSS ───────────────────────────────────────────────────── */
(function () {
    if (document.getElementById('cp-styles')) return;
    const s = document.createElement('style');
    s.id = 'cp-styles';
    s.textContent = `
    .cp-wrap{background:#f1f5f9;min-height:100%;padding:10px 12px;box-sizing:border-box;font-family:'Inter',sans-serif;font-size:0.87rem;}
    .cp-hdr{margin-bottom:8px;}
    .cp-hdr h2{margin:0 0 1px;font-size:1rem;font-weight:800;color:#1e3a8a;}
    .cp-hdr p{margin:0;font-size:0.78rem;color:#64748b;}
    .cp-card{background:#fff;border:1px solid #e2e8f0;border-radius:7px;padding:12px 14px;margin-bottom:8px;}
    .cp-card-title{font-weight:700;color:#1e3a8a;font-size:0.88rem;margin:0 0 9px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;}
    .cp-g2{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
    .cp-g3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;}
    .cp-g4{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;}
    .cp-fg{margin-bottom:9px;}
    .cp-fg:last-child{margin-bottom:0;}
    .cp-label{display:block;font-size:0.76rem;font-weight:600;color:#475569;margin-bottom:3px;letter-spacing:.01em;}
    .cp-req{color:#ef4444;margin-left:2px;}
    .cp-input,.cp-select,.cp-textarea{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:5px;padding:6px 9px;font-size:0.85rem;font-family:inherit;color:#0f172a;background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;}
    .cp-input:focus,.cp-select:focus,.cp-textarea:focus{border-color:#0284c7;box-shadow:0 0 0 3px rgba(2,132,199,.1);}
    .cp-textarea{resize:vertical;min-height:58px;}
    .cp-iu{display:flex;align-items:center;gap:5px;}
    .cp-iu .cp-input{flex:1;}
    .cp-iu .cp-select{width:auto;flex-shrink:0;}
    .cp-unit{font-size:0.74rem;color:#64748b;white-space:nowrap;flex-shrink:0;}
    .cp-bill-row{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin-bottom:10px;}
    .cp-bill-row .cp-fg{margin-bottom:0;}
    .cp-btn{background:#0284c7;color:#fff;border:none;padding:9px 20px;border-radius:6px;font-weight:600;font-size:0.9rem;cursor:pointer;transition:all .15s;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;box-shadow:0 2px 4px rgba(0,0,0,.08);}
    .cp-btn:hover{background:#0369a1;box-shadow:0 3px 6px rgba(0,0,0,.12);}
    .cp-btn-sec{background:#fff;color:#374151;border:1px solid #cbd5e1;padding:9px 20px;border-radius:6px;font-weight:600;font-size:0.9rem;cursor:pointer;transition:all .15s;display:inline-flex;align-items:center;gap:6px;box-shadow:0 1px 3px rgba(0,0,0,.05);}
    .cp-btn-sec:hover{background:#f8fafc;}
    .cp-btn-sm{padding:6px 14px;font-size:0.83rem;border-radius:5px;}
    .cp-btn-red{background:none;border:none;color:#dc2626;cursor:pointer;padding:4px 6px;font-size:0.88rem;}
    .cp-nav{display:flex;justify-content:space-between;align-items:center;padding:9px 14px;background:#fff;border-top:1px solid #e2e8f0;margin:8px -14px -12px;}
    .cp-char{text-align:right;font-size:0.7rem;color:#94a3b8;margin-top:1px;}
    .cp-check-g{display:flex;flex-wrap:wrap;gap:5px;}
    .cp-check-g label{display:flex;align-items:center;gap:5px;font-size:0.82rem;color:#374151;background:#f8fafc;border:1px solid #e2e8f0;padding:3px 9px;border-radius:5px;cursor:pointer;}
    .cp-check-g label:has(input:checked){background:#e0f2fe;border-color:#7dd3fc;color:#0284c7;}
    .cp-badge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:0.73rem;font-weight:600;}
    .cp-badge-blue{background:#dbeafe;color:#1d4ed8;}
    .cp-badge-green{background:#dcfce7;color:#15803d;}
    .cp-badge-yellow{background:#fef9c3;color:#a16207;}
    .cp-badge-sky{background:#e0f2fe;color:#0284c7;}
    .cp-badge-red{background:#fee2e2;color:#dc2626;}
    .cp-tbl{width:100%;border-collapse:collapse;font-size:0.83rem;}
    .cp-tbl th{padding:7px 9px;background:#f8fafc;color:#374151;font-weight:600;font-size:0.74rem;text-align:left;border-bottom:1px solid #e2e8f0;}
    .cp-tbl td{padding:7px 9px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
    .cp-tbl tbody tr:last-child td{border-bottom:none;}
    .cp-tabs-sm{display:flex;gap:4px;flex-wrap:wrap;margin-bottom:9px;}
    .cp-tabs-sm button{padding:4px 11px;border:1px solid #cbd5e1;border-radius:20px;font-size:0.78rem;font-weight:600;color:#64748b;background:#fff;cursor:pointer;transition:all .15s;}
    .cp-tabs-sm button.active{background:#0284c7;color:#fff;border-color:#0284c7;}
    .cp-divider{border:none;border-top:1px solid #e2e8f0;margin:9px 0;}
    .cp-radio-g{display:flex;gap:14px;align-items:center;}
    .cp-radio-g label{display:flex;align-items:center;gap:5px;font-size:0.84rem;color:#374151;cursor:pointer;}
    .cp-exam-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;padding:12px;}
    .cp-exam-title{font-weight:700;font-size:0.83rem;color:#1e3a8a;margin:0 0 10px;}
    .cp-total-bar{display:flex;justify-content:flex-end;gap:32px;padding:10px 16px;background:#eff6ff;border-radius:7px;font-size:0.87rem;border:1px solid #bfdbfe;}
    .cp-total-bar strong{color:#0284c7;font-size:0.95rem;}
    .cp-cpt-item{display:flex;gap:10px;align-items:center;padding:9px 12px;border:1px solid #e2e8f0;border-radius:7px;margin-bottom:7px;background:#fff;}
    .cp-cpt-item:hover{background:#f0f9ff;}
    .cp-sugg{position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #cbd5e1;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.1);z-index:9999;max-height:180px;overflow-y:auto;}
    .cp-sugg-item{padding:8px 12px;font-size:0.84rem;cursor:pointer;display:flex;justify-content:space-between;border-bottom:1px solid #f1f5f9;}
    .cp-sugg-item:hover{background:#e0f2fe;}
    .cp-sugg-item:last-child{border-bottom:none;}
    .cp-diag-inp-wrap{position:relative;}
    .cp-ro{padding:6px 9px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:5px;font-size:0.85rem;color:#0f172a;min-height:33px;box-sizing:border-box;}
    .cp-note{font-size:0.78rem;color:#64748b;margin:8px 0 0;}
    /* Clinical Stepper Navigation (Completely Flat, Blue Text Only) */
    .cp-stepper-nav{display:flex;background:#fff;border-bottom:2px solid #e2e8f0;padding:0 12px;gap:12px;overflow-x:auto;}
    .cp-step-item{display:flex;align-items:center;padding:12px 14px;cursor:pointer;font-weight:600;font-size:0.86rem;color:#64748b;background:transparent;border:none;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;transition:all .15s ease;}
    .cp-step-item:hover{color:#0284c7;}
    .cp-step-item.active{background:transparent;color:#0284c7;border-bottom:2px solid #0284c7;font-weight:700;}
    /* Segmented Inner Sub-Pills (Zero Scroll) */
    .cp-subpill-nav{display:inline-flex;gap:4px;background:#e2e8f0;padding:4px;border-radius:8px;margin-bottom:12px;}
    .cp-subpill{padding:6px 14px;font-size:0.81rem;font-weight:600;border-radius:6px;color:#475569;cursor:pointer;border:none;background:transparent;transition:all .15s ease;display:inline-flex;align-items:center;gap:5px;}
    .cp-subpill:hover{color:#0284c7;background:rgba(255,255,255,0.6);}
    .cp-subpill.active{background:#fff;color:#0284c7;box-shadow:0 1px 3px rgba(0,0,0,0.1);}
    /* Info (i) Tooltip Icon */
    .cp-info-tip{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#e0f2fe;color:#0284c7;font-size:0.75rem;font-weight:700;cursor:help;margin-left:6px;vertical-align:middle;position:relative;border:1px solid #bae6fd;}
    .cp-info-tip:hover{background:#0284c7;color:#fff;}
    .cp-info-tip:hover::after{content:attr(data-tip);position:absolute;top:130%;left:0;background:#0f172a;color:#fff;padding:8px 12px;border-radius:6px;font-size:0.76rem;font-weight:400;white-space:normal;width:max-content;max-width:320px;z-index:999999;box-shadow:0 4px 14px rgba(0,0,0,0.18);pointer-events:none;line-height:1.4;}
    .cp-info-tip:hover::before{content:'';position:absolute;top:105%;left:5px;border:5px solid transparent;border-bottom-color:#0f172a;z-index:999999;}
    `;
    document.head.appendChild(s);
})();

/* ══════ PANE BUILDERS ══════════════════════════════════════════════ */

// C02 Chief Complaint (& Cardiac Symptoms for cardiology). The detailed narrative history (OPQRST,
// CCS/NYHA staging) is C03 Cardiac HPI's job, not this screen's - for a cardiology encounter this pane
// only captures WHICH cardiac symptoms are present (quick triage flags) plus the plain-text chief
// complaint; it deliberately does not duplicate C03's "Detailed Description" textarea. Non-cardiology
// encounters (no C03 of their own) keep the original generic symptom list + free-text HPI unchanged.
function cpB_CC(note) {
    const isCardio = (note?.encounter_type || '').toLowerCase().includes('cardio');
    const d = document.createElement('div'); d.id = 'cpP0'; d.className = 'cp-wrap';
    const symptomCard = isCardio ? `
    <div class="cp-card">
        <div class="cp-card-title">Cardiac Symptoms</div>
        <p class="cp-note" style="margin:0 0 9px;">Quick triage flags. Document the full history (onset, quality, radiation, severity, CCS/NYHA staging) on the Cardiac HPI screen.</p>
        <div class="cp-fg"><label class="cp-label">Present Symptoms</label>
            <div class="cp-check-g" id="cpSymG">
                ${['Chest Pain', 'Dyspnea', 'Palpitations', 'Syncope', 'Edema', 'Fatigue', 'Dizziness', 'Orthopnea', 'Other'].map(s => `<label><input type="checkbox" class="cpSym" value="${s}"> ${s}</label>`).join('')}
            </div>
        </div>
        <div class="cp-fg" id="cpOtherW" style="display:none;">
            <label class="cp-label">Other symptoms</label>
            <input type="text" id="cpOtherT" class="cp-input" placeholder="Describe other symptoms...">
        </div>
    </div>` : `
    <div class="cp-card">
        <div class="cp-card-title">History of Present Illness</div>
        <div class="cp-fg"><label class="cp-label">Associated Symptoms</label>
            <div class="cp-check-g" id="cpSymG">
                ${['Fever', 'Chills', 'Nausea', 'Vomiting', 'Cough', 'Shortness of Breath', 'Other'].map(s => `<label><input type="checkbox" class="cpSym" value="${s}"> ${s}</label>`).join('')}
            </div>
        </div>
        <div class="cp-fg" id="cpOtherW" style="display:none;">
            <label class="cp-label">Other symptoms</label>
            <input type="text" id="cpOtherT" class="cp-input" placeholder="Describe other symptoms...">
        </div>
        <div class="cp-fg">
            <label class="cp-label">Detailed Description</label>
            <textarea id="cpHPI" class="cp-textarea" rows="4" maxlength="2000" placeholder="Describe the history of present illness..."></textarea>
            <div class="cp-char"><span id="cpHPI-c">0</span>/2000</div>
        </div>
    </div>`;
    d.innerHTML = `
    <div class="cp-hdr"><h2>Chief Complaint${isCardio ? ' &amp; Cardiac Symptoms' : ''}</h2><p>Document the patient's chief complaint and reason for the visit.</p></div>
    <div class="cp-card">
        <div class="cp-card-title">Chief Complaint</div>
        <div class="cp-fg">
            <label class="cp-label">Chief Complaint <span class="cp-req">*</span></label>
            <textarea id="cpCC" class="cp-textarea" rows="3" maxlength="500" placeholder="e.g. Persistent headache and elevated blood pressure for the past 3 days."></textarea>
            <div class="cp-char"><span id="cpCC-c">0</span>/500</div>
        </div>
        <div class="cp-g2">
            <div class="cp-fg"><label class="cp-label">Onset</label><input type="date" id="cpOnset" class="cp-input"></div>
            <div class="cp-fg"><label class="cp-label">Duration</label>
                <select id="cpDuration" class="cp-select"><option value="">Select...</option>
                    <option>1 day</option><option>2 days</option><option>3 days</option><option>1 week</option>
                    <option>2 weeks</option><option>1 month</option><option>3 months</option><option>Chronic</option></select></div>
        </div>
    </div>
    ${symptomCard}
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr0">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpN0">Next &rarr;</button>
    </div>`;
    return d;
}

// Medication Review / Allergy Review — common-encounter steps backed by the real, already-working
// medications / patient_allergies tables (same endpoints the standalone Medications/Allergies
// workspaces use), not a client-only list — data entered here is real, persisted, and visible
// everywhere else in the app immediately, unlike this file's old cpMedBody table (see cpB_Plan).
// C05 Medications & Allergies - one screen, two cards. Ids unchanged from the two panes this replaces
// (cpAddMedReal/cpMedRealBody/cpAddAlgReal/cpAlgRealBody) - wireAll() and load*ReviewList() find them
// by id regardless of which pane they live in, so no wiring changes were needed for this merge.
function cpB_MedsAllergies(note) {
    const d = document.createElement('div'); d.id = 'cpP9'; d.className = 'cp-wrap';
    d.innerHTML = `
    <div class="cp-hdr"><h2>Medications &amp; Allergies</h2><p>Review this patient's active medications and known allergies; add anything new for this encounter.</p></div>
    <div class="cp-card">
        <div class="cp-card-title">
            Active Medications
            <button type="button" class="cp-btn cp-btn-sm" id="cpAddMedReal">+ Add Medication</button>
        </div>
        <table class="cp-tbl">
            <thead><tr><th>Medication</th><th>Dosage / Route / Frequency</th><th>Start Date</th><th>Status</th></tr></thead>
            <tbody id="cpMedRealBody"><tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:14px;">Loading...</td></tr></tbody>
        </table>
    </div>
    <div class="cp-card">
        <div class="cp-card-title">
            Known Allergies
            <div style="display:flex;gap:6px;align-items:center;">
                <button type="button" class="cp-btn-sec cp-btn-sm" id="cpMarkNkda" title="Mark No Known Drug Allergies">✓ Mark NKDA</button>
                <button type="button" class="cp-btn cp-btn-sm" id="cpAddAlgReal">+ Add Allergy</button>
            </div>
        </div>
        <table class="cp-tbl">
            <thead><tr><th>Allergen</th><th>Reaction</th><th>Severity</th><th>Status</th></tr></thead>
            <tbody id="cpAlgRealBody"><tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:14px;">Loading...</td></tr></tbody>
        </table>
    </div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPrM">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNxM">Next &rarr;</button>
    </div>`;
    return d;
}

/* ── C01 Visit Details ─────────────────────────────────────────────────
   Identifies the visit. Everything except the two referral fields is already decided by the appointment
   the encounter was started from, so it is shown read-only rather than offered for re-typing. The two
   editable fields write to the hidden #referring-provider / #referral-reason inputs in clinical_modal.php,
   which is what submitEncounter actually reads (it reads by id and silently drops anything missing). */
function cpB_VisitDetails(note) {
    const d = document.createElement('div'); d.id = 'cpP12'; d.className = 'cp-wrap';
    const esc = cpEsc;
    const encNum = 'ENC-' + String(note.id || '').padStart(5, '0');
    const when = (note.note_date || '').replace('T', ' ').slice(0, 16) || '—';
    const providerName = note.provider_name || (note.first_name && note.last_name ? `Dr. ${note.first_name} ${note.last_name}` : '—');
    const row = (label, value) => `<div class="cp-fg"><label class="cp-label">${esc(label)}</label><div class="cp-ro">${esc(value || '—')}</div></div>`;
    d.innerHTML = `
    <div class="cp-hdr"><h2>Visit Details</h2><p>Who this visit is for, what kind of visit it is, and who sent the patient.</p></div>
    <div class="cp-card">
        <div class="cp-card-title">This Encounter</div>
        <div class="cp-g3">
            ${row('Encounter', encNum)}
            ${row('Date &amp; time', when)}
            ${row('Visit type', note.visit_type)}
        </div>
        <div class="cp-g3">
            ${row('Mode', note.encounter_mode)}
            ${row('Provider', providerName)}
            ${row('Specialty', note.encounter_type)}
        </div>
        <p class="cp-note">${note.appointment_id
            ? 'Started from a scheduled appointment, so these details come from the booking.'
            : 'Walk-in encounter &mdash; no linked appointment.'}</p>
    </div>
    <div class="cp-card">
        <div class="cp-card-title">Referral</div>
        <div class="cp-g2">
            <div class="cp-fg">
                <label class="cp-label">Referring provider</label>
                <input type="text" id="cpVisitRefProv" class="cp-input" maxlength="150" placeholder="e.g. Dr. A. Mehta, Internal Medicine">
            </div>
            <div class="cp-fg">
                <label class="cp-label">Reason for referral</label>
                <input type="text" id="cpVisitRefReason" class="cp-input" maxlength="255" placeholder="e.g. Exertional chest pain for cardiology opinion">
            </div>
        </div>
    </div>
    <div class="cp-nav"><div></div><button type="button" class="cp-btn" id="cpNx12">Next &rarr;</button></div>`;
    return d;
}

/* ── C04 Cardiac History ───────────────────────────────────────────────
   Patient-level, not encounter-level: this is REVIEWED each visit, not retyped. It is the single place the
   cardiac risk scores read their inputs from (ASCVD, CHA2DS2-VASc, HAS-BLED), which is why the risk factors
   are structured checkboxes rather than prose. Saved through its own endpoint, not the encounter save. */
const CP_CARDIAC_CONDITIONS = [
    ['cad', 'Coronary artery disease'], ['prior_mi', 'Prior myocardial infarction'],
    ['prior_pci', 'Prior PCI / stent'], ['prior_cabg', 'Prior CABG'],
    ['heart_failure', 'Heart failure'], ['atrial_fibrillation', 'Atrial fibrillation / flutter'],
    ['valve_disease', 'Valve disease'], ['cardiomyopathy', 'Cardiomyopathy'],
    ['pad', 'Peripheral artery disease'], ['stroke_tia', 'Stroke / TIA'],
];
const CP_CARDIAC_RISK = [
    ['hypertension', 'Hypertension'], ['diabetes', 'Diabetes'], ['dyslipidemia', 'Dyslipidemia'],
    ['obesity', 'Obesity'], ['ckd', 'Chronic kidney disease'], ['sleep_apnea', 'Sleep apnea'],
    ['family_premature_cad', 'Family history of premature CAD'],
];
const CP_BLEEDING_RISK = [
    ['prior_bleeding', 'Prior major bleeding'], ['labile_inr', 'Labile INR'], ['alcohol_excess', 'Alcohol excess'],
];
const CP_CARDIAC_DATES = ['prior_mi_date', 'prior_pci_date', 'prior_cabg_date', 'device_implant_date'];

function cpEsc(s) {
    return String(s === null || s === undefined ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function cpB_CardiacHistory(note) {
    const d = document.createElement('div'); d.id = 'cpP13'; d.className = 'cp-wrap';
    const boxes = list => list.map(([k, label]) =>
        `<label><input type="checkbox" class="cpCHbox" id="cpCH_${k}" data-f="${k}"> ${cpEsc(label)}</label>`).join('');
    d.innerHTML = `
    <div class="cp-hdr"><h2>Cardiac History</h2><p>Reviewed at every visit, not retyped. These answers are what the risk scores are calculated from.</p></div>
    <div class="cp-card">
        <div class="cp-card-title">Cardiac Conditions</div>
        <div class="cp-check-g">${boxes(CP_CARDIAC_CONDITIONS)}</div>
        <div class="cp-g4" style="margin-top:10px;">
            <div class="cp-fg"><label class="cp-label">Date of MI</label><input type="date" id="cpCH_prior_mi_date" class="cp-input"></div>
            <div class="cp-fg"><label class="cp-label">Date of PCI</label><input type="date" id="cpCH_prior_pci_date" class="cp-input"></div>
            <div class="cp-fg"><label class="cp-label">Date of CABG</label><input type="date" id="cpCH_prior_cabg_date" class="cp-input"></div>
            <div class="cp-fg"><label class="cp-label">Heart failure type</label>
                <select id="cpCH_hf_type" class="cp-select"><option value="">Select...</option>
                    <option value="HFrEF">HFrEF (reduced EF)</option><option value="HFmrEF">HFmrEF (mildly reduced)</option><option value="HFpEF">HFpEF (preserved EF)</option></select></div>
        </div>
    </div>
    <div class="cp-card">
        <div class="cp-card-title">Risk Factors</div>
        <div class="cp-check-g">${boxes(CP_CARDIAC_RISK)}</div>
        <div class="cp-g2" style="margin-top:10px;">
            <div class="cp-fg"><label class="cp-label">Smoking</label>
                <select id="cpCH_smoking_status" class="cp-select"><option value="Never">Never</option><option value="Former">Former</option><option value="Current">Current</option></select></div>
            <div class="cp-fg"><label class="cp-label">Pack-years</label><input type="number" min="0" max="999" step="0.5" id="cpCH_pack_years" class="cp-input" placeholder="e.g. 22.5"></div>
        </div>
    </div>
    <div class="cp-card">
        <div class="cp-card-title">Device</div>
        <div class="cp-g2">
            <div class="cp-fg"><label class="cp-label">Implanted device</label>
                <select id="cpCH_device_type" class="cp-select"><option value="">None</option>
                    <option>Dual Chamber Pacemaker (PPM)</option><option>Single Chamber Pacemaker</option>
                    <option>Implantable Cardioverter Defibrillator (ICD)</option><option>Biventricular ICD (CRT-D)</option>
                    <option>Implantable Loop Recorder (ILR)</option></select></div>
            <div class="cp-fg"><label class="cp-label">Implant date</label><input type="date" id="cpCH_device_implant_date" class="cp-input"></div>
        </div>
    </div>
    <div class="cp-card">
        <div class="cp-card-title">Bleeding Risk <span class="cp-badge cp-badge-yellow">for HAS-BLED</span></div>
        <div class="cp-check-g">${boxes(CP_BLEEDING_RISK)}</div>
        <div class="cp-fg" style="margin-top:10px;"><label class="cp-label">Notes</label>
            <textarea id="cpCH_notes" class="cp-textarea" rows="2" maxlength="5000" placeholder="Anything about the cardiac history that doesn't fit above..."></textarea></div>
    </div>
    <div class="cp-card" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <span id="cpCHReviewed" class="cp-note">Loading cardiac history&hellip;</span>
        <button type="button" class="cp-btn" id="cpCHSave">Save cardiac history</button>
    </div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr13">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNx13">Next &rarr;</button>
    </div>`;
    return d;
}

/** Fills the C04 pane from GET /api/clinical/cardiac-profile/{id}. */
function loadCardiacProfile(patientId) {
    const stamp = document.getElementById('cpCHReviewed');
    if (!patientId) return;
    ApiService.request(`/api/clinical/cardiac-profile/${patientId}`).then(res => {
        if (!res || res.status !== 'success' || !res.data) {
            if (stamp) stamp.textContent = 'Could not load the cardiac history.';
            return;
        }
        const p = res.data;
        [...CP_CARDIAC_CONDITIONS, ...CP_CARDIAC_RISK, ...CP_BLEEDING_RISK].forEach(([k]) => {
            const el = document.getElementById(`cpCH_${k}`);
            if (el) el.checked = p[k] === true;
        });
        CP_CARDIAC_DATES.forEach(k => { const el = document.getElementById(`cpCH_${k}`); if (el) el.value = p[k] || ''; });
        ['hf_type', 'smoking_status', 'device_type', 'pack_years', 'notes'].forEach(k => {
            const el = document.getElementById(`cpCH_${k}`);
            if (el) el.value = p[k] === null || p[k] === undefined ? '' : p[k];
        });
        if (typeof window.syncCardiacHistoryFields === 'function') window.syncCardiacHistoryFields();
        if (stamp) {
            stamp.textContent = p.exists && p.last_reviewed_at
                ? `Last reviewed ${String(p.last_reviewed_at).slice(0, 16)}${p.last_reviewed_by_name ? ' by ' + p.last_reviewed_by_name : ''}.`
                : 'No cardiac history recorded for this patient yet.';
        }
    }).catch(() => { if (stamp) stamp.textContent = 'Could not load the cardiac history.'; });
}

function syncCardiacHistoryFields() {
    const miBox = document.getElementById('cpCH_prior_mi');
    const miDate = document.getElementById('cpCH_prior_mi_date');
    if (miBox && miDate) { miDate.disabled = !miBox.checked; miDate.style.opacity = miBox.checked ? '1' : '0.55'; }

    const pciBox = document.getElementById('cpCH_prior_pci');
    const pciDate = document.getElementById('cpCH_prior_pci_date');
    if (pciBox && pciDate) { pciDate.disabled = !pciBox.checked; pciDate.style.opacity = pciBox.checked ? '1' : '0.55'; }

    const cabgBox = document.getElementById('cpCH_prior_cabg');
    const cabgDate = document.getElementById('cpCH_prior_cabg_date');
    if (cabgBox && cabgDate) { cabgDate.disabled = !cabgBox.checked; cabgDate.style.opacity = cabgBox.checked ? '1' : '0.55'; }

    const hfBox = document.getElementById('cpCH_heart_failure');
    const hfType = document.getElementById('cpCH_hf_type');
    if (hfBox && hfType) { hfType.disabled = !hfBox.checked; hfType.style.opacity = hfBox.checked ? '1' : '0.55'; }

    const smokStatus = document.getElementById('cpCH_smoking_status');
    const packYears = document.getElementById('cpCH_pack_years');
    if (smokStatus && packYears) {
        const isSmoker = smokStatus.value === 'Current' || smokStatus.value === 'Former';
        packYears.disabled = !isSmoker;
        packYears.style.opacity = isSmoker ? '1' : '0.55';
    }

    const devSelect = document.getElementById('cpCH_device_type');
    const devDate = document.getElementById('cpCH_device_implant_date');
    if (devSelect && devDate) {
        const hasDevice = devSelect.value && devSelect.value !== 'None';
        devDate.disabled = !hasDevice;
        devDate.style.opacity = hasDevice ? '1' : '0.55';
    }
}
window.syncCardiacHistoryFields = syncCardiacHistoryFields;

/** Saves the C04 pane. Patient-level, so it has its own Save rather than riding on the encounter save. */
function saveCardiacProfile(patientId) {
    const btn = document.getElementById('cpCHSave');
    const stamp = document.getElementById('cpCHReviewed');
    if (!patientId) return;
    const body = {};
    [...CP_CARDIAC_CONDITIONS, ...CP_CARDIAC_RISK, ...CP_BLEEDING_RISK].forEach(([k]) => {
        const el = document.getElementById(`cpCH_${k}`);
        if (el) body[k] = el.checked;
    });
    CP_CARDIAC_DATES.concat(['hf_type', 'smoking_status', 'device_type', 'pack_years', 'notes']).forEach(k => {
        const el = document.getElementById(`cpCH_${k}`);
        if (el) body[k] = el.value;
    });
    if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }
    ApiService.request(`/api/clinical/cardiac-profile/${patientId}`, 'PUT', body).then(res => {
        const ok = res && res.status === 'success';
        if (typeof Toast !== 'undefined') Toast.show(ok ? 'Cardiac history saved.' : (res && res.message) || 'Could not save the cardiac history.', ok ? 'success' : 'error');
        if (ok) loadCardiacProfile(patientId);
        else if (stamp) stamp.textContent = (res && res.message) || 'Could not save the cardiac history.';
    }).catch(() => {
        if (typeof Toast !== 'undefined') Toast.show('Could not save the cardiac history.', 'error');
    }).finally(() => { if (btn) { btn.disabled = false; btn.textContent = 'Save cardiac history'; } });
}

/**
 * Makes the Back / Next buttons follow the CURRENT tab order instead of the neighbour id each pane was
 * written with. Without this, adding a screen leaves the chain pointing past it (or at a pane that is no
 * longer rendered), which is how Medication Review and the Cardiology pane became reachable only by Next.
 * Only buttons labelled "Next..." or "...Previous" are touched - Save Draft / Clear / View Results are left alone.
 */
function wireWorkflowNav(panes, tabLis, labels) {
    panes.forEach((pane, i) => {
        // A pane's own .cp-nav can hold more than a Previous/Next pair - cpB_Sign's does (#cpSignBtn, wired
        // in wireAll() with its real submit handler). Grab it by reference BEFORE hiding that nav below, so
        // the actual node (not a relabeled copy) can be moved into the unified footer instead of being
        // discarded along with the rest of that now-hidden nav - that discard is what made Sign Encounter
        // silently disappear (Save Draft survived because it isn't inside a .cp-nav).
        const signBtn = pane.querySelector('#cpSignBtn');

        // Hide intermediate sub-card nav bars to keep the interface clean and unified
        pane.querySelectorAll('.cp-nav').forEach(nav => {
            if (!nav.classList.contains('cp-stage-footer-nav')) {
                nav.style.display = 'none';
            }
        });

        // Add or reuse unified Stage Footer Navigation
        let footerNav = pane.querySelector('.cp-stage-footer-nav');
        if (!footerNav) {
            footerNav = document.createElement('div');
            footerNav.className = 'cp-nav cp-stage-footer-nav';
            footerNav.style.cssText = 'margin-top:20px;padding:14px 20px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 3px rgba(0,0,0,0.04);';
            pane.appendChild(footerNav);
        }

        const prevHtml = i > 0
            ? `<button type="button" class="cp-btn-sec cp-stage-prev-btn">&larr; Previous: ${cpEsc(labels[i - 1])}</button>`
            : `<div></div>`;
        const nextHtml = i < panes.length - 1
            ? `<button type="button" class="cp-btn cp-stage-next-btn">Next: ${cpEsc(labels[i + 1])} &rarr;</button>`
            : `<div></div>`;

        footerNav.innerHTML = `${prevHtml}${nextHtml}`;

        const prevBtn = footerNav.querySelector('.cp-stage-prev-btn');
        if (prevBtn) prevBtn.onclick = () => { tabLis[i - 1].click(); window.scrollTo({ top: 0, behavior: 'smooth' }); };

        const nextBtn = footerNav.querySelector('.cp-stage-next-btn');
        if (nextBtn) nextBtn.onclick = () => { tabLis[i + 1].click(); window.scrollTo({ top: 0, behavior: 'smooth' }); };

        // Swap the placeholder slot for the real Sign button (same node, so its wireAll() onclick comes with it).
        if (signBtn) footerNav.replaceChild(signBtn, footerNav.lastElementChild);
    });
}

/** Wiring for the screens added by the cardiology workflow (C01 Visit Details, C04 Cardiac History). Null-safe:
 *  C04 only renders for cardiology encounters, so every lookup here has to tolerate a missing pane. */
function wireNewEncounterPanes(note) {
    // C01 - the visible inputs mirror into the hidden #referring-provider / #referral-reason that submitEncounter reads.
    [['cpVisitRefProv', 'referring-provider', 'referring_provider'], ['cpVisitRefReason', 'referral-reason', 'referral_reason']]
        .forEach(([visibleId, hiddenId, noteKey]) => {
            const visible = document.getElementById(visibleId);
            const hidden = document.getElementById(hiddenId);
            if (!visible) return;
            visible.value = note[noteKey] || (hidden ? hidden.value : '') || '';
            if (hidden) {
                hidden.value = visible.value;
                visible.addEventListener('input', () => { hidden.value = visible.value; });
            }
        });

    // C04 - patient-level, saved through its own endpoint rather than the encounter save.
    const chSave = document.getElementById('cpCHSave');
    if (chSave) {
        chSave.onclick = () => saveCardiacProfile(note.patient_id);
        loadCardiacProfile(note.patient_id);
    }
}

function loadMedicationReviewList(patientId) {
    const tbody = document.getElementById('cpMedRealBody');
    if (!tbody || !patientId) return;
    ApiService.request(`/api/medications/${patientId}`).then(res => {
        const meds = (res.status === 'success' && Array.isArray(res.data)) ? res.data : [];
        if (!meds.length) { tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:14px;">No active medications on file.</td></tr>'; return; }
        tbody.innerHTML = meds.map(m => `
            <tr>
                <td style="font-weight:700;">${m.medication_name}</td>
                <td>${m.dosage} — ${m.route} — ${m.frequency}</td>
                <td>${m.start_date}</td>
                <td><span class="cp-badge ${m.status === 'Active' ? 'cp-badge-green' : 'cp-badge-yellow'}">${m.status}</span></td>
            </tr>
        `).join('');
    }).catch(() => { tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#dc2626;padding:14px;">Failed to load medications.</td></tr>'; });
}

function loadAllergyReviewList(patientId) {
    const tbody = document.getElementById('cpAlgRealBody');
    if (!tbody || !patientId) return;
    ApiService.request(`/api/allergies/${patientId}`).then(res => {
        const allergies = (res.status === 'success' && Array.isArray(res.data)) ? res.data : [];
        if (!allergies.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:14px;">No known allergies on file. Click <strong>✓ Mark NKDA</strong> if confirmed.</td></tr>';
            return;
        }
        tbody.innerHTML = allergies.map(a => {
            const isSevere = /severe|anaphylaxis|angioedema/i.test((a.severity || '') + ' ' + (a.reaction || ''));
            const isNkda = /no known|nkda/i.test(a.allergen || '');
            const sevBadge = isSevere ? `<span class="cp-badge cp-badge-red">${a.severity}</span>` : a.severity;
            const statusBadge = isNkda ? `<span class="cp-badge cp-badge-green">Verified None</span>` : `<span class="cp-badge ${a.status === 'Active' ? 'cp-badge-blue' : 'cp-badge-yellow'}">${a.status}</span>`;
            return `
            <tr>
                <td style="font-weight:700;${isNkda ? 'color:#15803d;' : ''}">${a.allergen}</td>
                <td>${a.reaction || '—'}</td>
                <td>${sevBadge}</td>
                <td>${statusBadge}</td>
            </tr>`;
        }).join('');
    }).catch(() => { tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#dc2626;padding:14px;">Failed to load allergies.</td></tr>'; });
}

function cpB_Vitals(note) {
    const d = document.createElement('div'); d.id = 'cpP1'; d.className = 'cp-wrap';
    d.innerHTML = `
    <div class="cp-hdr"><h2>Vitals</h2><p>Record the patient's vital signs.</p></div>
    <div class="cp-card">
        <div class="cp-card-title">Vital Signs</div>
        <div class="cp-g3" style="margin-bottom:12px;">
            <div class="cp-fg"><label class="cp-label">Blood Pressure <span class="cp-req">*</span></label>
                <div class="cp-iu"><input type="number" id="cpBPS" class="cp-input" placeholder="120"><span class="cp-unit">/</span><input type="number" id="cpBPD" class="cp-input" placeholder="80"><span class="cp-unit">mmHg</span></div></div>
            <div class="cp-fg"><label class="cp-label">Heart Rate <span class="cp-req">*</span></label>
                <div class="cp-iu"><input type="number" id="cpHR" class="cp-input" placeholder="76"><span class="cp-unit">bpm</span></div></div>
            <div class="cp-fg"><label class="cp-label">Respiratory Rate <span class="cp-req">*</span></label>
                <div class="cp-iu"><input type="number" id="cpRR" class="cp-input" placeholder="16"><span class="cp-unit">/min</span></div></div>
        </div>
        <div class="cp-g4" style="margin-bottom:12px;">
            <div class="cp-fg"><label class="cp-label">Temperature <span class="cp-req">*</span></label>
                <div class="cp-iu"><input type="number" step="0.1" id="cpTemp" class="cp-input" placeholder="98.6"><select class="cp-select"><option>°F</option><option>°C</option></select></div></div>
            <div class="cp-fg"><label class="cp-label">Oxygen Saturation <span class="cp-req">*</span></label>
                <div class="cp-iu"><input type="number" id="cpSpO2" class="cp-input" placeholder="98"><span class="cp-unit">%</span></div></div>
            <div class="cp-fg"><label class="cp-label">Height</label>
                <div class="cp-iu"><input type="number" id="cpHtFt" class="cp-input" placeholder="5" style="width:45px;"><span class="cp-unit">ft</span><input type="number" id="cpHtIn" class="cp-input" placeholder="10" style="width:45px;"><span class="cp-unit">in</span></div></div>
            <div class="cp-fg"><label class="cp-label">Weight</label>
                <div class="cp-iu"><input type="number" step="0.1" id="cpWt" class="cp-input" placeholder="178"><select class="cp-select"><option>lb</option><option>kg</option></select></div></div>
        </div>
        <div class="cp-g2">
            <div class="cp-fg"><label class="cp-label">BMI</label>
                <div class="cp-iu"><input type="text" id="cpBMI" class="cp-input" readonly placeholder="Auto-calculated" style="background:#f8fafc;"><span class="cp-unit">kg/m²</span></div></div>
            <div class="cp-fg"><label class="cp-label">Pain Scale</label>
                <select id="cpPain" class="cp-select"><option value="0">0 – No pain</option><option>1</option><option>2</option><option>3</option><option>4</option><option value="5">5 – Moderate</option><option>6</option><option>7</option><option>8</option><option>9</option><option value="10">10 – Worst</option></select></div>
        </div>
        <div class="cp-fg"><label class="cp-label">Additional Notes</label>
            <textarea id="cpVN" class="cp-textarea" rows="2" maxlength="500" placeholder="Additional observations..."></textarea>
            <div class="cp-char"><span id="cpVN-c">0</span>/500</div>
        </div>
        <p style="font-size:0.75rem;color:#94a3b8;margin:4px 0 0;"><span style="color:#ef4444;">*</span> Required field</p>
    </div>
    <div id="cpVitalsCardioMount"></div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr1">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNx1">Next &rarr;</button>
    </div>`;
    return d;
}

function cpB_Exam(note) {
    const isCardio = (note?.encounter_type || '').toLowerCase().includes('cardio');
    const allSystems = [
        { id: 'genApp', t: 'General Appearance', f: [{ l: 'General Appearance', o: ['Well appearing', 'Ill appearing', 'Distressed', 'NAD'] }, { l: 'Level of Consciousness', o: ['Alert', 'Lethargic', 'Obtunded'] }, { l: 'Orientation', o: ['Oriented x3', 'Oriented x2', 'Confused'] }] },
        { id: 'heent', t: 'HEENT', f: [{ l: 'Head', o: ['Normocephalic', 'Atraumatic'] }, { l: 'Eyes', o: ['PERRLA', 'Nystagmus', 'Icterus'] }, { l: 'Ears', o: ['Normal', 'TM intact', 'Discharge'] }, { l: 'Nose', o: ['Normal', 'Congested', 'Rhinorrhea'] }] },
        { id: 'cardio', t: 'Cardiovascular', f: [{ l: 'Rhythm', o: ['Regular', 'Irregular', 'A-fib'] }, { l: 'Heart Sounds', o: ['Normal S1S2', 'Murmur', 'Gallop'] }, { l: 'Murmurs', o: ['None', 'Systolic', 'Diastolic'] }, { l: 'Edema', o: ['None', 'Pitting', 'Non-pitting'] }] },
        { id: 'resp', t: 'Respiratory', f: [{ l: 'Effort', o: ['Normal', 'Labored', 'Shallow'] }, { l: 'Auscultation', o: ['Clear to auscultation', 'Wheezes', 'Crackles', 'Rales'] }, { l: 'Breath Sounds', o: ['Normal', 'Diminished', 'Absent'] }] },
        { id: 'abdo', t: 'Abdomen', f: [{ l: 'Inspection', o: ['Soft, non-distended', 'Distended'] }, { l: 'Palpation', o: ['Non-tender', 'Tender', 'Guarding'] }, { l: 'Auscultation', o: ['Normal bowel sounds', 'Hypoactive', 'Hyperactive'] }, { l: 'Organomegaly', o: ['None', 'Hepatomegaly', 'Splenomegaly'] }] },
        { id: 'msk', t: 'Musculoskeletal', f: [{ l: 'Gait', o: ['Normal', 'Antalgic', 'Ataxic'] }, { l: 'Range of Motion', o: ['Full', 'Limited'] }, { l: 'Strength', o: ['5/5 bilaterally', '4/5', '3/5'] }, { l: 'Joint Swelling', o: ['None', 'Mild', 'Moderate'] }] },
        { id: 'neuro', t: 'Neurological', f: [{ l: 'Mental Status', o: ['Alert and oriented', 'Confused', 'Lethargic'] }, { l: 'Cranial Nerves', o: ['Intact', 'Abnormal'] }, { l: 'Motor Strength', o: ['5/5', '4/5', '3/5'] }, { l: 'Coordination', o: ['Normal', 'Ataxic'] }] },
        { id: 'skin', t: 'Skin', f: [{ l: 'Color', o: ['Normal', 'Pallor', 'Cyanosis', 'Jaundice'] }, { l: 'Turgor', o: ['Normal', 'Poor'] }, { l: 'Rash', o: ['None', 'Present'] }, { l: 'Lesions', o: ['None', 'Present'] }] },
    ];
    // In cardiology visits, only show clinically essential systems (General Appearance, Respiratory, Abdomen)
    // while the dedicated Cardiovascular Physical Examination card handles all heart/vascular findings.
    const cardioAllowed = ['genApp', 'resp', 'abdo'];
    const systems = isCardio ? allSystems.filter(s => cardioAllowed.includes(s.id)) : allSystems;

    const d = document.createElement('div'); d.id = 'cpP2'; d.className = 'cp-wrap';
    const gridCols = isCardio ? '1fr 1fr 1fr' : '1fr 1fr 1fr';
    d.innerHTML = `
    <div class="cp-hdr"><h2>Examination</h2><p>Document physical examination findings.</p></div>
    <div style="display:grid;grid-template-columns:${gridCols};gap:10px;margin-bottom:12px;">
        ${systems.map(sys => `
        <div class="cp-exam-box">
            <div class="cp-exam-title">${sys.t}</div>
            ${sys.f.map(f => `<div class="cp-fg"><label class="cp-label">${f.l}</label>
                <select class="cp-select">${f.o.map(o => `<option>${o}</option>`).join('')}</select></div>`).join('')}
            <div class="cp-fg"><label class="cp-label">Notes</label>
                <textarea class="cp-textarea" rows="2" maxlength="500" placeholder="${sys.t} notes..." style="min-height:44px;"></textarea></div>
        </div>`).join('')}
    </div>
    <div id="cpExamCardioMount"></div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr2">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNx2">Next &rarr;</button>
    </div>`;
    return d;
}

function cpB_Assessment(note) {
    const isCardio = (note?.encounter_type || '').toLowerCase().includes('cardio');
    const d = document.createElement('div'); d.id = 'cpP3'; d.className = 'cp-wrap';

    if (isCardio) {
        d.innerHTML = `
        <div class="cp-hdr"><h2>Cardiac Assessment</h2><p>Document 10-year ASCVD risk and comprehensive clinical assessment.</p></div>
        <div id="cpAssessCardioMount"></div>
        <div class="cp-card">
            <div class="cp-card-title">Clinical Assessment Summary <span class="cp-req">*</span></div>
            <div class="cp-fg">
                <textarea id="cpAssSum" class="cp-textarea" rows="4" maxlength="1000" placeholder="e.g. 62yo male with stable CAD and well-controlled HTN. GDMT optimized on Atorvastatin and Metoprolol. ASCVD risk discussed."></textarea>
                <div class="cp-char"><span id="cpAS-c">0</span>/1000</div>
            </div>
        </div>
        <div class="cp-nav">
            <button type="button" class="cp-btn-sec" id="cpPr3">&larr; Previous</button>
            <button type="button" class="cp-btn" id="cpNx3">Next &rarr;</button>
        </div>`;
        return d;
    }

    d.innerHTML = `
    <div class="cp-hdr"><h2>Assessment</h2><p>Document the clinical assessment, differential diagnoses, and clinical reasoning.</p></div>
    <div>
        <div>
            <div class="cp-card">
                <div class="cp-card-title">Clinical Assessment</div>
                <div class="cp-fg">
                    <label class="cp-label">Assessment Summary <span class="cp-req">*</span></label>
                    <textarea id="cpAssSum" class="cp-textarea" rows="4" maxlength="1000" placeholder="Patient presents with elevated blood pressure..."></textarea>
                    <div class="cp-char"><span id="cpAS-c">0</span>/1000</div>
                </div>
            </div>
            <div id="cpAssessCardioMount"></div>
            <div class="cp-card">
                <div class="cp-card-title">Clinical Reasoning</div>
                <textarea id="cpReason" class="cp-textarea" rows="3" maxlength="1000" placeholder="Elevated BP with history of diabetes increases cardiovascular risk..."></textarea>
                <div class="cp-char"><span id="cpR-c">0</span>/1000</div>
            </div>
        </div>
        <div>
            <div class="cp-card">
                <div class="cp-card-title">Risk Factors &amp; Comorbidities</div>
                <div class="cp-fg"><label class="cp-label">Risk Factors</label>
                    <div class="cp-check-g">${['Hypertension', 'Diabetes', 'Hyperlipidemia', 'Smoking', 'Obesity', 'Family History', 'Other'].map(r => `<label><input type="checkbox" class="cpRF" value="${r}"> ${r}</label>`).join('')}</div>
                </div>
                <div class="cp-fg" id="cpRFOtherW" style="display:none;">
                    <label class="cp-label">Other Risk Factor</label>
                    <input type="text" class="cp-input" placeholder="Enter other...">
                </div>
                <hr class="cp-divider">
                <div class="cp-fg"><label class="cp-label">Comorbidities</label>
                    <div style="position:relative;">
                        <input type="text" id="cpComorb" class="cp-input" placeholder="Search comorbidities..." autocomplete="off">
                    </div>
                    <div id="cpComorbTags" style="display:flex;flex-wrap:wrap;gap:5px;margin-top:7px;"></div>
                </div>
            </div>
            <div class="cp-card">
                <div class="cp-card-title">Follow-up / Monitoring</div>
                <div class="cp-fg"><label class="cp-label">Follow-up Required</label>
                    <div class="cp-radio-g"><label><input type="radio" name="cpFU" value="yes" checked> Yes</label><label><input type="radio" name="cpFU" value="no"> No</label></div>
                </div>
                <div class="cp-fg"><label class="cp-label">Monitoring Plan</label>
                    <select class="cp-select"><option>BP monitoring, A1C in 3 months</option><option>Monthly follow-up</option><option>Quarterly labs</option><option>Annual review</option><option>PRN</option></select></div>
                <div class="cp-fg"><label class="cp-label">Next Review Date</label><input type="date" class="cp-input" id="cpRevDate"></div>
                <div class="cp-fg"><label class="cp-label">Comments</label>
                    <textarea class="cp-textarea" rows="2" maxlength="500" placeholder="Additional notes..."></textarea></div>
            </div>
        </div>
    </div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr3">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNx3">Next &rarr;</button>
    </div>`;
    return d;
}

// C09 Diagnosis / Problem List - split out of Assessment so it is its own screen, per the owner's C01-C14 order.
// Same real patient_problems table + ids (cpAddDiagReal/cpDiagRealBody) as before the split - wireAll() and
// loadAssessmentDiagnosisList() find them by id regardless of which pane they live in; no wiring changes needed.
function cpB_DiagnosisList(note) {
    const d = document.createElement('div'); d.id = 'cpP10'; d.className = 'cp-wrap';
    d.innerHTML = `
    <div class="cp-hdr"><h2>Diagnosis / Problem List</h2><p>Diagnoses for this encounter, added to the patient's problem list.</p></div>
    <div id="cpDiagCardioMount"></div>
    <div class="cp-card">
        <div class="cp-card-title">
            Diagnoses
            <button type="button" class="cp-btn cp-btn-sm" id="cpAddDiagReal">+ Add Diagnosis</button>
        </div>
        <table class="cp-tbl">
            <thead><tr><th>Diagnosis</th><th>ICD-10</th><th>Chronicity</th><th>Status</th></tr></thead>
            <tbody id="cpDiagRealBody"><tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:14px;">Loading...</td></tr></tbody>
        </table>
    </div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr9b">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNx9b">Next &rarr;</button>
    </div>`;
    return d;
}

// Diagnosis list — backed by the real patient_problems table (same endpoint the Diagnoses
// workspace and C09's own "+ Add Diagnosis" use). window._currentAssessmentDiagnoses
// is also read by the Billing tab's _refreshCptDxDropdown for its Dx Pointer options.
function loadAssessmentDiagnosisList(patientId) {
    const tbody = document.getElementById('cpDiagRealBody');
    if (!tbody || !patientId) return;
    ApiService.request(`/api/problems/${patientId}`).then(res => {
        const problems = (res.status === 'success' && Array.isArray(res.data)) ? res.data : [];
        window._currentAssessmentDiagnoses = problems;
        if (!problems.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:14px;">No diagnoses added yet.</td></tr>';
        } else {
            tbody.innerHTML = problems.map(pr => `
                <tr>
                    <td style="font-weight:700;">${pr.description}</td>
                    <td>${pr.icd10_code || '—'}</td>
                    <td>${pr.chronicity || '—'}</td>
                    <td><span class="cp-badge ${pr.status === 'Active' ? 'cp-badge-green' : 'cp-badge-yellow'}">${pr.status}</span></td>
                </tr>
            `).join('');
        }
        if (window._refreshCptDxDropdown) window._refreshCptDxDropdown();
    }).catch(() => { tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#dc2626;padding:14px;">Failed to load diagnoses.</td></tr>'; });
}
window.loadAssessmentDiagnosisList = loadAssessmentDiagnosisList;

function cpB_Plan(note) {
    const d = document.createElement('div'); d.id = 'cpP4'; d.className = 'cp-wrap';
    d.innerHTML = `
    <div class="cp-hdr"><h2>Plan</h2><p>Document the treatment plan and follow-up instructions.</p></div>
    <div class="cp-card">
        <div class="cp-card-title">Treatment Plan <span class="cp-req">*</span></div>
        <textarea id="cpPlan" class="cp-textarea" rows="3" maxlength="1000" placeholder="Continue current medications. Monitor BP at home. Follow up in 2 weeks."></textarea>
        <div class="cp-char"><span id="cpP-c">0</span>/1000</div>
    </div>
    <div class="cp-card">
        <div class="cp-card-title">Follow-up Instructions</div>
        <textarea id="cpFollowup" class="cp-textarea" rows="2" maxlength="500" placeholder="Return in 2 weeks for BP check. Call if symptoms worsen."></textarea>
    </div>
    <div id="cpPlanCardioMount"></div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr4">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNx4">Next &rarr;</button>
    </div>`;
    return d;
}

// Orders / Results Review — backed by the real, already-working orders/results tables (same
// endpoints the standalone Orders workspace uses), scoped to this encounter via encounter_id.
// Replaces the old checkbox-list UI, which had no save wiring at all — nothing it collected was
// ever persisted.
function cpB_LabOrders(note) {
    const d = document.createElement('div'); d.id = 'cpP5'; d.className = 'cp-wrap';
    d.innerHTML = `
    <div class="cp-hdr"><h2>Cardiac Diagnostics / Orders</h2><p>Place orders for this encounter. Findings are recorded on the Results Review screen.</p></div>
    <div class="cp-card">
        <div class="cp-card-title">
            Orders for This Encounter
            <button type="button" class="cp-btn cp-btn-sm" id="cpAddOrderReal">+ Place Order</button>
        </div>
        <table class="cp-tbl">
            <thead><tr><th>Order #</th><th>Type / Name</th><th>Priority</th><th>Status</th><th></th></tr></thead>
            <tbody id="cpOrderRealBody"><tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:14px;">Loading...</td></tr></tbody>
        </table>
    </div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr5">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNx5">Next &rarr;</button>
    </div>`;
    return d;
}

function loadOrderReviewList(patientId, encounterId) {
    const tbody = document.getElementById('cpOrderRealBody');
    if (!tbody || !patientId) return;
    const url = encounterId ? `/api/orders/${patientId}?encounter_id=${encounterId}` : `/api/orders/${patientId}`;
    ApiService.request(url).then(res => {
        const orders = (res.status === 'success' && Array.isArray(res.data)) ? res.data : [];
        if (!orders.length) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:14px;">No orders placed for this encounter yet.</td></tr>'; return; }
        const statusBadge = (status) => {
            const map = { Submitted: 'cp-badge-yellow', 'In-Progress': 'cp-badge-blue', Resulted: 'cp-badge-sky', Reviewed: 'cp-badge-green', Cancelled: 'cp-badge-yellow' };
            return `<span class="cp-badge ${map[status] || 'cp-badge-yellow'}">${status}</span>`;
        };
        tbody.innerHTML = orders.map(o => `
            <tr>
                <td style="font-weight:700;">${o.order_number}</td>
                <td>${o.order_type} — ${o.order_name}</td>
                <td>${o.priority}</td>
                <td>${statusBadge(o.status)}</td>
                <td><button type="button" class="cp-btn-sec cp-btn-sm view-order-result-btn" data-id="${o.id}">View Results</button></td>
            </tr>
        `).join('');
        tbody.querySelectorAll('.view-order-result-btn').forEach(btn => {
            btn.onclick = () => {
                const order = orders.find(o => o.id == btn.getAttribute('data-id'));
                if (order && typeof window.openViewResultModal === 'function') window.openViewResultModal(order);
            };
        });
    }).catch(() => { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#dc2626;padding:14px;">Failed to load orders.</td></tr>'; });
}

function cpB_Sign(note) {
    const today = new Date().toISOString().slice(0, 10);
    const d = document.createElement('div'); d.id = 'cpP7'; d.className = 'cp-wrap';
    d.innerHTML = `
    <div style="display:flex;justify-content:flex-end;margin-bottom:8px;">
        <button type="button" class="cp-btn-sec cp-btn-sm" id="cpSaveDraft"><i class="fas fa-save" style="margin-right:4px;"></i> Save Draft</button>
    </div>
    <div class="cp-card">
        <div class="cp-card-title">Encounter Summary</div>
        <div class="cp-g2">
            <div class="cp-fg"><label class="cp-label">Encounter Date</label><input type="date" id="cpSignDate" class="cp-input" value="${today}"></div>
            <div class="cp-fg"><label class="cp-label">Provider</label><input type="text" id="cpSignProv" class="cp-input" placeholder="Provider name" value="${note.provider_name || ''}"></div>
        </div>
        <div class="cp-fg"><label class="cp-label">Location</label><select id="cpSignLoc" class="cp-select"><option>Main Clinic</option><option>Outpatient</option><option>Telehealth</option><option>Emergency</option></select></div>
    </div>
    <div class="cp-card">
        <div class="cp-card-title">Signature</div>
        <div class="cp-fg">
            <label class="cp-label">Electronically signed by <span id="cpSigProvNamePreview" style="font-weight:700;color:#0369a1;">${note.provider_name || 'Provider'}</span> (<span id="cpSigLocPreview" style="font-weight:600;">Main Clinic</span>)</label>
            <div style="border:1px solid #cbd5e1; border-radius:5px; background:#f8fafc; overflow:hidden; position:relative; width: 100%; max-width: 500px;">
                <canvas id="cpSigCanvas" width="500" height="150" style="width:100%; height:150px; cursor:crosshair; display:block; touch-action:none;"></canvas>
                <button type="button" id="cpSigClear" style="position:absolute; top:8px; right:8px; background:#e2e8f0; border:none; padding:4px 10px; border-radius:4px; font-size:0.75rem; color:#475569; cursor:pointer;">Clear</button>
            </div>
            <p style="margin-top:6px; font-size:0.75rem; color:#64748b;">Draw your signature inside the box above.</p>
        </div>
    </div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr7">&larr; Previous</button>
        <button type="button" id="cpSignBtn" style="background:#16a34a;color:#fff;border:none;padding:8px 22px;border-radius:5px;font-weight:700;font-size:0.88rem;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,.08);">Sign Encounter ✓</button>
    </div>`;
    return d;
}

// The mega #cardio-form (clinical_modal.php) was split into 12 independently-relocatable sections
// (id="cardio-sec-*"), each still serialized into cardio_data via an explicit form="cardio-form"
// attribute on every control inside it (moving a control out of its <form> ancestor drops it from
// FormData() unless it carries that attribute - see the plan note in CLAUDE.md). This table is the
// ONLY place that says which section goes to which screen's mount point; add a row here, not a new
// ad-hoc appendChild, if a section ever needs to move again.
const CARDIO_RELOCATION = [
    ['cardio-sec-top', 'cpHpiMount'],              // workflow pathway + routine follow-up summary -> C03 (top)
    ['cardio-sec-hpi', 'cpHpiMount'],               // cardiac symptoms / CCS / NYHA / orthopnea     -> C03
    ['cardio-sec-orthostatic', 'cpVitalsCardioMount'], // sitting/standing BP + HR & rhythm           -> C06 Vitals
    ['cardio-sec-exam', 'cpExamCardioMount'],       // JVP/carotid/heart sounds/murmur/pulses/edema  -> C07 Examination
    ['cardio-sec-ascvd', 'cpAssessCardioMount'],    // 10-year ASCVD risk score + Calc button        -> C08 Assessment
    ['cardio-sec-ecg', 'cpResultsMount'],           // 12-lead ECG findings                          -> C11 Results Review
    ['cardio-sec-echo', 'cpResultsMount'],          // echocardiogram findings                       -> C11 Results Review
    ['cardio-sec-device', 'cpResultsMount'],        // device interrogation + cath/PCI findings      -> C11 Results Review
    ['cardio-sec-holter', 'cpResultsMount'],        // Holter/ambulatory monitor findings            -> C11 Results Review
    ['cardio-sec-labs', 'cpResultsMount'],          // cardiac & metabolic labs                      -> C11 Results Review
    ['cardio-sec-htnplan', 'cpPlanCardioMount'],    // HTN medication/lifestyle plan                 -> C12 Treatment & Plan
];
const CARDIO_SECTION_IDS = CARDIO_RELOCATION.map(([id]) => id);

// Pulls #cardio-form/#ortho-form (and every relocated cardio-sec-*) back to their static home inside the hidden
// clinical modal - exposed globally because it has to run from TWO places: here, right before this override
// rebuilds the tab panes (the original reason this existed - a stale cpP8/cpP11 would otherwise take the form
// down with it via .remove()); and from window.openEncounterInChart (app.js), BEFORE it calls
// window.openPatientChart() to switch to a different encounter. That second call site matters because
// relocateCardioSections() moves cardio-sec-* OUT of their static home and INTO mount points that live inside
// #patient-dashboard-full-content - and openPatientChart replaces that whole element's innerHTML on every call.
// Without rescuing first, opening a second encounter in the same chart session wiped every relocated cardio-sec-*
// out of existence (not just out of place) before this override's own rescue ever got a chance to run, silently
// emptying every cardio-specific screen (ASCVD, exam findings, ECG/Echo/Device/Holter/Labs, HTN plan) on the
// second and every later encounter opened in that session.
function rescueClinicalFormSections() {
    const cardioFormEl = document.getElementById('cardio-form');
    const cardioFormHome = document.getElementById('accordion-cardio');
    if (cardioFormEl) {
        CARDIO_SECTION_IDS.forEach(id => {
            const el = document.getElementById(id);
            if (el && el.parentElement !== cardioFormEl) cardioFormEl.appendChild(el);
        });
    }
    if (cardioFormEl && cardioFormHome && cardioFormEl.parentElement !== cardioFormHome) {
        cardioFormHome.appendChild(cardioFormEl);
    }
    const orthoFormEl = document.getElementById('ortho-form');
    const orthoFormHome = document.getElementById('accordion-ortho');
    if (orthoFormEl && orthoFormHome && orthoFormEl.parentElement !== orthoFormHome) {
        orthoFormHome.appendChild(orthoFormEl);
    }
}
window.rescueClinicalFormSections = rescueClinicalFormSections;

// Moves every cardio-sec-* into its target pane's mount point, in CARDIO_RELOCATION order (so sections that
// share a mount, e.g. cardio-sec-top then cardio-sec-hpi both into #cpHpiMount, land in the right order).
// Only called for a cardiology encounter; a non-cardiology encounter leaves every section parked inside the
// rescued #cardio-form (see the rescue step in the "MAIN OVERRIDE" function, just above where this is called).
function relocateCardioSections() {
    CARDIO_RELOCATION.forEach(([secId, mountId]) => {
        const sec = document.getElementById(secId), mount = document.getElementById(mountId);
        if (sec && mount) mount.appendChild(sec);
    });
    if (typeof window.toggleCardioWorkflow === 'function') window.toggleCardioWorkflow();
}

// C03 Cardiac HPI (cardiology only). Mounts cardio-sec-top (workflow pathway + follow-up summary) and
// cardio-sec-hpi (symptom history / CCS / NYHA / orthopnea) - see CARDIO_RELOCATION above.
function cpB_CardiacHPI(note) {
    const d = document.createElement('div'); d.id = 'cpP8'; d.className = 'cp-wrap';
    d.innerHTML = `
    <div class="cp-hdr"><h2>Cardiac HPI</h2><p>History of present illness: onset, quality, radiation, severity, and functional staging.</p></div>
    <div id="cpHpiMount"></div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr8">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNx8">Next &rarr;</button>
    </div>`;
    return d;
}

// C11 Results Review (cardiology only). Mounts the ECG/Echo/Device-Cath/Holter/Labs findings sections -
// these are test RESULTS, not new orders, which is why they live here and not on the Orders screen (C10).
function cpB_ResultsReview(note) {
    const d = document.createElement('div'); d.id = 'cpP14'; d.className = 'cp-wrap';
    d.innerHTML = `
    <div class="cp-hdr"><h2>Results Review</h2><p>ECG, echocardiogram, device/cath, Holter and lab findings for this encounter.</p></div>
    <div id="cpResultsMount"></div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr14">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNx14">Next &rarr;</button>
    </div>`;
    return d;
}

// Orthopedics-only pane: reuses the real, complete #ortho-form (built in clinical_modal.php, with its
// own 5-pathway workflow engine) by moving the whole form here instead of rebuilding its ~50 fields in
// this file's own cp-* style. Unlike the cardio-form (split across C03/C06/C07/C08/C11/C12 - see
// CARDIO_RELOCATION above), ortho was not asked to be split the same way and stays one pane. Rescued
// back to its accordion home on every re-render (see the rescue step in the "MAIN OVERRIDE" function).
function cpB_Orthopedics(note) {
    const d = document.createElement('div'); d.id = 'cpP11'; d.className = 'cp-wrap';
    d.innerHTML = `
    <div class="cp-hdr"><h2>Orthopedic Assessment</h2><p>Select the clinical pathway and complete the orthopedic workflow below.</p></div>
    <div id="cpOrthoMount"></div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr11">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNx11">Next &rarr;</button>
    </div>`;
    const orthoForm = document.getElementById('ortho-form');
    if (orthoForm) {
        d.querySelector('#cpOrthoMount').appendChild(orthoForm);
        if (typeof window.toggleOrthoPathway === 'function') window.toggleOrthoPathway();
        if (typeof window.toggleOrthoJoint === 'function') window.toggleOrthoJoint();
        if (typeof window.toggleOrthoRedFlagBanner === 'function') window.toggleOrthoRedFlagBanner();
        if (typeof window.populateOrthoReferencePickers === 'function' && note.patient_id) {
            let orthoDataForRefs = null;
            if (note.ortho_data) {
                try { orthoDataForRefs = typeof note.ortho_data === 'string' ? JSON.parse(note.ortho_data) : note.ortho_data; } catch (e) { orthoDataForRefs = null; }
            }
            window.populateOrthoReferencePickers(note.patient_id, orthoDataForRefs?.ortho_fracture_ref_id, orthoDataForRefs?.ortho_procedure_ref_id);
        }
    }
    return d;
}

function cpB_Billing(note, allCpts) {
    const d = document.createElement('div'); d.id = 'cpP6'; d.className = 'cp-wrap';
    d.innerHTML = `
    <div class="cp-card">
        <div class="cp-card-title">Add CPT-4 Code <span class="cp-badge cp-badge-sky">CPT-4</span></div>
        <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:10px;">
            <div style="flex:1 1 200px;position:relative;">
                <label class="cp-label">Search by Code or Description</label>
                <input type="text" id="cpCptSrch" class="cp-input" placeholder="e.g. 99213 or office visit..." autocomplete="off">
                <div id="cpCptSugg" class="cp-sugg" style="display:none;"></div>
            </div>
            <div style="flex:0 0 60px;">
                <label class="cp-label">Units</label>
                <input type="number" id="cpCptU" class="cp-input" value="1" min="1">
            </div>
            <div style="flex:0 0 60px;">
                <label class="cp-label">Mod</label>
                <input type="text" id="cpCptMod" class="cp-input" placeholder="--">
            </div>
            <div style="flex:0 0 90px;">
                <label class="cp-label">Charge (\$)</label>
                <input type="number" step="0.01" id="cpCptCh" class="cp-input" placeholder="0.00">
            </div>
            <div style="flex:0 0 120px;">
                <label class="cp-label">Dx Pointer</label>
                <select id="cpCptDx" class="cp-select">
                    <option value="">Select Dx...</option>
                </select>
            </div>
            <button type="button" class="cp-btn" id="cpCptAdd" style="flex-shrink:0;align-self:flex-end;">+ Add</button>
        </div>
       
        
        <div style="overflow-x:auto; margin-top: 14px;">
            <table class="cp-tbl">
                <thead>
                    <tr><th style="width:40px;">#</th><th>CPT</th><th>Description</th><th style="width:70px;">Units</th><th style="width:60px;">Mod</th><th style="width:100px;">Charge</th><th style="width:120px;">Dx</th><th style="width:50px;text-align:right;"></th></tr>
                </thead>
                <tbody id="cpCptRows">
                    <!-- Rows will be injected here -->
                </tbody>
            </table>
        </div>
        <div class="cp-total-bar" style="margin-top:0; border-top-left-radius:0; border-top-right-radius:0;">
            <span>Codes: <strong id="cpCptCnt">0</strong></span>
            <span>Total Units: <strong id="cpCptTU">0</strong></span>
            <span>Total Charge: <strong id="cpCptTot">\$0.00</strong></span>
        </div>
        <input type="hidden" id="cpCptPayload">
    </div>
    <div class="cp-nav">
        <button type="button" class="cp-btn-sec" id="cpPr6">&larr; Previous</button>
        <button type="button" class="cp-btn" id="cpNx6">Next: Sign Encounter &rarr;</button>
    </div>`;
    return d;
}


/* ══════ CPT-4 MANAGER ══════════════════════════════════════════════ */
function initCPTManager(allCpts) {
    let rows = window._savedCptRows || [];
    const refreshTotals = () => {
        const cnt = rows.length, tu = rows.reduce((a, r) => a + (r.units || 1), 0), tt = rows.reduce((a, r) => a + (r.charge * (r.units || 1)), 0);
        const el = (id, v) => { const e = document.getElementById(id); if (e) e.textContent = v; };
        el('cpCptCnt', cnt); el('cpCptTU', tu); el('cpCptTot', '$' + tt.toFixed(2));
        const pl = document.getElementById('cpCptPayload'); if (pl) pl.value = JSON.stringify(rows);
    };
    const renderRows = () => {
        const tbody = document.getElementById('cpCptRows'); if (!tbody) return;
        if (!rows.length) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:14px;">No codes added.</td></tr>'; refreshTotals(); return; }
        tbody.innerHTML = rows.map((r, i) => `
            <tr>
                <td style="font-weight:600;color:#64748b;">${i + 1}</td>
                <td style="font-weight:700;color:#0284c7;">${r.code}</td>
                <td>${r.desc}</td>
                <td><input type="number" min="1" value="${r.units}" style="width:50px;border:1px solid #cbd5e1;border-radius:5px;padding:4px;" onchange="window._cpCptUU(${i},this.value)"></td>
                <td>${r.mod || '--'}</td>
                <td style="font-weight:600;">$${(r.charge * (r.units || 1)).toFixed(2)}</td>
                <td>${r.dx || ''}</td>
                <td style="text-align:right;"><button type="button" class="cp-btn-red" onclick="window._cpCptRm(${i})"><i class="fas fa-trash-alt"></i></button></td>
            </tr>
        `).join('');
        refreshTotals();
    };
    window._cpCptRm = (i) => { rows.splice(i, 1); renderRows(); };
    window._cpCptUU = (i, v) => { rows[i].units = Math.max(1, parseInt(v) || 1); refreshTotals(); };

    const addRow = (code, desc, charge, category, mod = '', dx = '') => {
        rows.push({ code, desc, charge: parseFloat(charge) || 0, units: 1, category: category || 'E&M', mod, dx });
        renderRows();
    };

    // Refresh Dx Dropdown
    window._refreshCptDxDropdown = async () => {
        const dxSelect = document.getElementById('cpCptDx');
        if (!dxSelect) return;
        const currentVal = dxSelect.value;
        let diagCodes = (window._currentAssessmentDiagnoses || []).map(pr => pr.icd10_code).filter(Boolean);
        
        if (!diagCodes.length && window.activeClinicalPatientId) {
            try {
                const res = await ApiService.request(`/api/problems/${window.activeClinicalPatientId}`);
                if (res && res.status === 'success' && Array.isArray(res.data)) {
                    window._currentAssessmentDiagnoses = res.data;
                    diagCodes = res.data.map(pr => pr.icd10_code).filter(Boolean);
                }
            } catch (e) {
                console.error('Failed to load patient problems for billing dx:', e);
            }
        }

        dxSelect.innerHTML = '<option value="">Select Dx...</option>' + diagCodes.map(c => `<option value="${c}">${c}</option>`).join('');
        if (Array.from(dxSelect.options).some(o => o.value === currentVal)) dxSelect.value = currentVal;
    };

    // Search/suggestions
    const srch = document.getElementById('cpCptSrch'), sugg = document.getElementById('cpCptSugg');
    if (srch && sugg) {
        srch.addEventListener('input', () => {
            const q = srch.value.trim().toLowerCase();
            if (q.length < 2) { sugg.style.display = 'none'; return; }
            const hits = allCpts.filter(c => c.cpt_code.toLowerCase().includes(q) || c.description.toLowerCase().includes(q)).slice(0, 8);
            if (!hits.length) { sugg.style.display = 'none'; return; }
            sugg.innerHTML = hits.map(c => `<div class="cp-sugg-item" data-code="${c.cpt_code}" data-desc="${c.description}" data-charge="${c.default_charge}" data-cat="${c.category}"><span><strong>${c.cpt_code}</strong> — ${c.description}</span><span style="color:#0284c7;font-weight:600;">$${parseFloat(c.default_charge).toFixed(2)}</span></div>`).join('');
            sugg.style.display = 'block';
            sugg.querySelectorAll('.cp-sugg-item').forEach(item => {
                item.onclick = () => {
                    srch.value = item.dataset.code + ' — ' + item.dataset.desc;
                    document.getElementById('cpCptCh').value = parseFloat(item.dataset.charge).toFixed(2);
                    srch._sel = { code: item.dataset.code, desc: item.dataset.desc, cat: item.dataset.cat };
                    sugg.style.display = 'none';
                };
            });
        });
        document.addEventListener('click', e => { if (!srch.contains(e.target) && !sugg.contains(e.target)) sugg.style.display = 'none'; });
    }
    const addBtn = document.getElementById('cpCptAdd');
    if (addBtn) addBtn.onclick = () => {
        const sel = srch ? srch._sel : null;
        const code = sel ? sel.code : (srch ? srch.value.trim().split(' ')[0] : '');
        const desc = sel ? sel.desc : (srch ? srch.value.trim() : '');
        const charge = parseFloat(document.getElementById('cpCptCh')?.value || 0);
        const units = parseInt(document.getElementById('cpCptU')?.value || 1);
        const mod = document.getElementById('cpCptMod')?.value.trim() || '';
        const dx = document.getElementById('cpCptDx')?.value || '';
        const cat = sel ? sel.cat : 'E&M';
        if (!code) { alert('Please select a CPT code.'); return; }
        rows.push({ code, desc, charge, units, category: cat, mod, dx });
        renderRows();
        if (srch) { srch.value = ''; delete srch._sel; }
        const ch = document.getElementById('cpCptCh'); if (ch) ch.value = '';
        const u = document.getElementById('cpCptU'); if (u) u.value = '1';
        const m = document.getElementById('cpCptMod'); if (m) m.value = '';
    };
    document.querySelectorAll('.cpCptQB').forEach(btn => {
        btn.addEventListener('click', () => {
            const mod = document.getElementById('cpCptMod')?.value.trim() || '';
            const dx = document.getElementById('cpCptDx')?.value || '';
            addRow(btn.dataset.code, btn.dataset.desc, btn.dataset.charge, 'E&M', mod, dx);
        });
    });
    renderRows();
}

/* ══════ WIRE ALL EVENTS ════════════════════════════════════════════ */
function wireAll(panes, tabLis, note, allCpts) {
    const goTo = i => { if (tabLis[i]) tabLis[i].click(); };
    // Navigates by a pane's own stable DOM id rather than its array position, so Next/Prev
    // wiring survives tab reordering (unlike the top-of-file switchTab()'s hardcoded index
    // map, which is exactly the kind of fragility that broke things before — see clinical-tabs.js
    // header comment / CLAUDE.md).
    const goToId = id => { const i = panes.findIndex(p => p.id === id); if (i >= 0) goTo(i); };

    // Char counters
    [['cpCC', 'cpCC-c'], ['cpHPI', 'cpHPI-c'], ['cpVN', 'cpVN-c'], ['cpAssSum', 'cpAS-c'], ['cpReason', 'cpR-c'], ['cpPlan', 'cpP-c']].forEach(([id, cid]) => {
        const el = document.getElementById(id), ce = document.getElementById(cid);
        if (el && ce) el.addEventListener('input', () => ce.textContent = el.value.length);
    });

    // CC
    const cpCC = document.getElementById('cpCC'), cpHPI = document.getElementById('cpHPI');
    const rCC = document.getElementById('chief-complaint'), rHPI = document.getElementById('hpi'), rROS = document.getElementById('ros');
    if (cpCC && rCC) cpCC.addEventListener('input', () => rCC.value = cpCC.value);
    if (cpHPI && rHPI) cpHPI.addEventListener('input', () => rHPI.value = cpHPI.value);
    document.querySelectorAll('.cpSym').forEach(cb => cb.addEventListener('change', () => {
        if (cb.value === 'Other') { const w = document.getElementById('cpOtherW'); if (w) w.style.display = cb.checked ? 'block' : 'none'; }
        const syms = Array.from(document.querySelectorAll('.cpSym:checked')).map(c => c.value).join(', ');
        if (rROS) rROS.value = syms;

        // Auto-bridge Chief Complaint triage symptom flags to Cardiology Workflow
        if (cb.checked) {
            const wfSel = document.getElementById('cardio-workflow-select');
            if (wfSel) {
                let targetWorkflow = null;
                if (cb.value === 'Chest Pain') targetWorkflow = 'chest_pain';
                else if (cb.value === 'Dyspnea' || cb.value === 'Orthopnea') targetWorkflow = 'dyspnea';
                else if (cb.value === 'Palpitations' || cb.value === 'Syncope' || cb.value === 'Dizziness') targetWorkflow = 'palpitations';

                if (targetWorkflow && wfSel.value !== targetWorkflow) {
                    wfSel.value = targetWorkflow;
                    if (typeof window.toggleCardioWorkflow === 'function') window.toggleCardioWorkflow();
                }
            }
        }
    }));
    document.getElementById('cpN0').onclick = () => goToId('cpP9');

    // Medication Review
    const addMedRealBtn = document.getElementById('cpAddMedReal');
    if (addMedRealBtn) addMedRealBtn.onclick = () => window.openPrescribeMedicationModal(() => loadMedicationReviewList(note.patient_id), note.patient_id);
    document.getElementById('cpPrM').onclick = () => goToId('cpP0');
    document.getElementById('cpNxM').onclick = () => goToId('cpP10');
    loadMedicationReviewList(note.patient_id);

    // Allergy Review (now the second card on the same C05 pane as Medication Review, above - no separate
    // Previous/Next wiring needed here, the pane has only one cp-nav, already wired via cpPrM/cpNxM).
    const addAlgRealBtn = document.getElementById('cpAddAlgReal');
    if (addAlgRealBtn) addAlgRealBtn.onclick = () => window.openAllergyModal(null, () => loadAllergyReviewList(note.patient_id), note.patient_id);
    const markNkdaBtn = document.getElementById('cpMarkNkda');
    if (markNkdaBtn) {
        markNkdaBtn.onclick = async () => {
            const confirmed = window.confirm("Confirm that patient has No Known Drug Allergies (NKDA)?");
            if (!confirmed) return;
            try {
                markNkdaBtn.disabled = true;
                const res = await ApiService.request('/api/allergies', 'POST', {
                    patient_id: note.patient_id,
                    allergen: 'No Known Drug Allergies (NKDA)',
                    reaction: 'None',
                    severity: 'Mild',
                    status: 'Active'
                });
                if (res && res.status === 'success') {
                    Toast.show('Allergies confirmed as NKDA.', 'success');
                    loadAllergyReviewList(note.patient_id);
                } else {
                    Toast.show(res?.message || 'Could not record NKDA.', 'error');
                }
            } catch (err) {
                console.error(err);
                Toast.show('Failed to save NKDA.', 'error');
            } finally {
                markNkdaBtn.disabled = false;
            }
        };
    }
    loadAllergyReviewList(note.patient_id);

    // Vitals
    const htFt = document.getElementById('cpHtFt'), htIn = document.getElementById('cpHtIn'), wt = document.getElementById('cpWt'), bmi = document.getElementById('cpBMI');
    const calcBMI = () => {
        const f = parseFloat(htFt?.value || 0), i = parseFloat(htIn?.value || 0), w = parseFloat(wt?.value || 0);
        const tot = f * 12 + i;
        if (tot > 0 && w > 0 && bmi) {
            const calculated = ((w / (tot * tot)) * 703).toFixed(1);
            bmi.value = calculated;
            const realBmi = document.getElementById('vital-bmi');
            if (realBmi) realBmi.value = calculated;
        }
    };
    [htFt, htIn, wt].forEach(el => { if (el) el.addEventListener('input', calcBMI); });
    // Run immediate calculation if height and weight are already present
    calcBMI();

    const vitMap = [['cpBPS', 'vital-bp-systolic'], ['cpBPD', 'vital-bp-diastolic'], ['cpHR', 'vital-heart-rate'], ['cpRR', 'vital-resp-rate'], ['cpTemp', 'vital-temp'], ['cpSpO2', 'vital-spo2'], ['cpWt', 'vital-weight'], ['cpBMI', 'vital-bmi']];
    vitMap.forEach(([mi, ri]) => {
        const me = document.getElementById(mi), re = document.getElementById(ri);
        if (me && re) {
            me.addEventListener('input', () => {
                re.value = me.value;
                // Auto-sync Sitting BP into Orthostatic Vitals
                if (mi === 'cpBPS' || mi === 'cpBPD') {
                    const s = document.getElementById('cpBPS')?.value, d = document.getElementById('cpBPD')?.value;
                    const orthoSit = document.getElementById('cardio-bp-sitting');
                    if (orthoSit && s && d) orthoSit.value = `${s}/${d}`;
                }
            });
        }
    });

    // Also wire change listeners on Cardiac History for real-time toggling
    document.querySelectorAll('.cpCHbox, #cpCH_smoking_status, #cpCH_device_type').forEach(el => {
        el.addEventListener('change', () => {
            if (typeof window.syncCardiacHistoryFields === 'function') window.syncCardiacHistoryFields();
        });
    });
    if (typeof window.syncCardiacHistoryFields === 'function') window.syncCardiacHistoryFields();

    document.querySelectorAll('#cpP1 .cp-tabs-sm button').forEach(b => b.addEventListener('click', () => { document.querySelectorAll('#cpP1 .cp-tabs-sm button').forEach(x => x.classList.remove('active')); b.classList.add('active'); }));
    document.getElementById('cpPr1').onclick = () => goToId('cpP10'); document.getElementById('cpNx1').onclick = () => goToId('cpP2');

    // Signature Preview Dynamic Update
    const sigProvInp = document.getElementById('cpSignProv');
    const sigLocInp = document.getElementById('cpSignLoc');
    const sigProvPreview = document.getElementById('cpSigProvNamePreview');
    const sigLocPreview = document.getElementById('cpSigLocPreview');
    if (sigProvInp && sigProvPreview) sigProvInp.addEventListener('input', () => sigProvPreview.textContent = sigProvInp.value.trim() || 'Provider');
    if (sigLocInp && sigLocPreview) sigLocInp.addEventListener('change', () => sigLocPreview.textContent = sigLocInp.value);

    // Examination
    document.getElementById('cpPr2').onclick = () => goToId('cpP1'); document.getElementById('cpNx2').onclick = () => goToId('cpP3');

    // Assessment — Diagnosis list
    const addDiagRealBtn = document.getElementById('cpAddDiagReal');
    if (addDiagRealBtn) addDiagRealBtn.onclick = () => window.openProblemModal(null, () => loadAssessmentDiagnosisList(note.patient_id), note.patient_id);
    loadAssessmentDiagnosisList(note.patient_id);
    const assSum = document.getElementById('cpAssSum'), rAss = document.getElementById('fm-assessment');
    if (assSum && rAss) assSum.addEventListener('input', () => rAss.value = assSum.value);
    // Comorbidities
    const comorbList = ['Hypertension', 'Diabetes Mellitus Type 2', 'Dyslipidemia', 'Asthma', 'COPD', 'CKD', 'Heart Failure', 'Hypothyroidism', 'Depression', 'Anxiety', 'Obesity', 'Atrial Fibrillation'];
    const comorbInp = document.getElementById('cpComorb'), comorbTags = document.getElementById('cpComorbTags');
    if (comorbInp && comorbTags) {
        comorbInp.addEventListener('focus', () => {
            const dd = document.createElement('div'); dd.id = 'cpCmDd'; dd.className = 'cp-sugg';
            dd.style.cssText = 'position:absolute;width:100%;top:100%;left:0;';
            dd.innerHTML = comorbList.map(o => `<div class="cp-sugg-item" onclick="window._addCTag('${o}')">${o}</div>`).join('');
            comorbInp.parentNode.style.position = 'relative';
            const old = document.getElementById('cpCmDd'); if (old) old.remove();
            comorbInp.parentNode.appendChild(dd);
        });
        document.addEventListener('click', e => { const dd = document.getElementById('cpCmDd'); if (dd && !comorbInp.contains(e.target) && !dd.contains(e.target)) dd.remove(); });
    }
    window._addCTag = val => { const tags = document.getElementById('cpComorbTags'); if (!tags) return; if (Array.from(tags.querySelectorAll('[data-v]')).some(t => t.dataset.v === val)) return; const sp = document.createElement('span'); sp.dataset.v = val; sp.style.cssText = 'display:inline-flex;align-items:center;gap:5px;background:#e0f2fe;color:#0284c7;padding:3px 9px;border-radius:20px;font-size:0.8rem;font-weight:600;'; sp.innerHTML = `${val} <button type="button" onclick="this.parentNode.remove()" style="background:none;border:none;color:#0284c7;cursor:pointer;font-size:0.85rem;">✕</button>`; tags.appendChild(sp); const dd = document.getElementById('cpCmDd'); if (dd) dd.remove(); };
    // Risk factors
    document.querySelectorAll('.cpRF').forEach(cb => cb.addEventListener('change', () => { const w = document.getElementById('cpRFOtherW'); if (w) { const otherCb = document.querySelector('.cpRF[value="Other"]'); w.style.display = otherCb && otherCb.checked ? 'block' : 'none'; } }));
    document.getElementById('cpPr3').onclick = () => goToId('cpP2'); document.getElementById('cpNx3').onclick = () => goToId('cpP4');

    // Plan
    const planEl = document.getElementById('cpPlan'), rPlan = document.getElementById('care-plan');
    if (planEl && rPlan) planEl.addEventListener('input', () => rPlan.value = planEl.value);
    document.getElementById('cpPr4').onclick = () => goToId('cpP3'); document.getElementById('cpNx4').onclick = () => goToId('cpP5');

    // Orders & Results Review
    const addOrderRealBtn = document.getElementById('cpAddOrderReal');
    if (addOrderRealBtn) addOrderRealBtn.onclick = () => window.openPlaceOrderModal(() => loadOrderReviewList(note.patient_id, note.id), note.patient_id, note.id);
    loadOrderReviewList(note.patient_id, note.id);
    document.getElementById('cpPr5').onclick = () => goToId('cpP4'); document.getElementById('cpNx5').onclick = () => goToId('cpP6');

    // Sign Encounter (now tab 7)
    const saveDraft = document.getElementById('cpSaveDraft');
    if (saveDraft) saveDraft.onclick = () => { window._syncCustomTabsToDOM(); const b = document.getElementById('save-encounter-btn') || document.getElementById('update-encounter-btn'); if (b) b.click(); };
    document.getElementById('cpPr7').onclick = () => goToId('cpP6');
    // Signature Canvas Logic
    const cvs = document.getElementById('cpSigCanvas');
    let isDrawing = false, isSigned = !!(note && note.signed_signature_data);

    if (cvs) {
        const ctx = cvs.getContext('2d');
        ctx.lineWidth = 2.5; ctx.lineCap = 'round'; ctx.strokeStyle = '#0f172a';

        if (note && note.signed_signature_data && note.signed_signature_data.startsWith('data:image')) {
            const img = new Image();
            img.onload = () => ctx.drawImage(img, 0, 0, cvs.width, cvs.height);
            img.src = note.signed_signature_data;
        }

        const getPos = e => {
            const r = cvs.getBoundingClientRect();
            const evt = e.touches ? e.touches[0] : e;
            const scaleX = cvs.width / r.width;
            const scaleY = cvs.height / r.height;
            return { x: (evt.clientX - r.left) * scaleX, y: (evt.clientY - r.top) * scaleY };
        };

        const start = e => { e.preventDefault(); isDrawing = true; isSigned = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); };
        const draw = e => { if (!isDrawing) return; e.preventDefault(); const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); };
        const stop = e => { if (isDrawing) { e.preventDefault(); isDrawing = false; ctx.closePath(); } };

        cvs.addEventListener('mousedown', start); cvs.addEventListener('mousemove', draw); window.addEventListener('mouseup', stop);
        cvs.addEventListener('touchstart', start, { passive: false }); cvs.addEventListener('touchmove', draw, { passive: false }); window.addEventListener('touchend', stop);

        const clearBtn = document.getElementById('cpSigClear');
        if (clearBtn) clearBtn.onclick = () => { ctx.clearRect(0, 0, cvs.width, cvs.height); isSigned = false; };
    }

    document.getElementById('cpSignBtn').onclick = () => {
        if (cvs && !isSigned) { alert('Please draw your signature.'); return; }
        if (cvs && isSigned) {
            const rData = document.getElementById('sig-data-url');
            if (rData) rData.value = cvs.toDataURL('image/png');
        }
        const sigNameInput = document.getElementById('signature-name');
        if (sigNameInput) sigNameInput.value = document.getElementById('cpSignProv')?.value || 'Provider';
        window._syncCustomTabsToDOM();

        // Existing encounter: save, then really sign & lock it (POST /sign). Previously this button only
        // saved, so nothing on the chart could ever lock an encounter or send it to Billing.
        const signNoteId = window.activeClinicalNoteId;
        const signPatientId = window.activeClinicalPatientId;
        if (signNoteId && typeof window.submitEncounter === 'function') {
            const ok = window.confirm('Sign & finalize this encounter?\n\nOnce signed the record is locked; later corrections go in an Addendum.');
            if (!ok) return;
            const btn = document.getElementById('cpSignBtn');
            btn.disabled = true;
            (async () => {
                try {
                    await window.submitEncounter({ preventDefault() { }, target: btn });
                    if (window.activeClinicalNoteId) return;   // save failed (its own message already shown)
                    const res = await ApiService.request(`/api/clinical/notes/${signNoteId}/sign`, 'POST', { signed_signature_data: document.getElementById('sig-data-url')?.value || '' });
                    if (res && res.status === 'success') {
                        Toast.show('Encounter signed and locked. Sent to the billing queue.', 'success');
                        const fresh = await ApiService.request(`/api/clinical/note-single/${signNoteId}`);
                        if (fresh.status === 'success' && fresh.data) window.populateEncounterModal(fresh.data, true);
                        if (typeof window.refreshBillingData === 'function') { try { window.refreshBillingData(); } catch (_) { } }
                    } else {
                        window.activeClinicalNoteId = signNoteId;
                        window.activeClinicalPatientId = signPatientId;
                        alert('Sign failed: ' + ((res && res.message) || 'Could not sign the encounter.'));
                    }
                } finally { btn.disabled = false; }
            })();
            return;
        }
        const rb = document.getElementById('save-encounter-btn') || document.getElementById('update-encounter-btn'); if (rb) rb.click();
    };

    // Billing (now tab 6)
    document.getElementById('cpPr6').onclick = () => goToId('cpP5');
    document.getElementById('cpNx6').onclick = () => goToId('cpP7');
}

/* ══════ SYNC DB → CUSTOM UI ════════════════════════════════════════ */
window._syncCustomTabsToDOM = function() {
    let ccData = {
        text: document.getElementById('cpCC')?.value || '',
        onset: document.getElementById('cpOnset')?.value || '',
        duration: document.getElementById('cpDuration')?.value || '',
        symptoms: [],
        other_symptom: ''
    };
    document.querySelectorAll('.cpSym:checked').forEach(cb => {
        ccData.symptoms.push(cb.value);
    });
    if(ccData.symptoms.includes('Other')) {
        ccData.other_symptom = document.getElementById('cpOtherT')?.value || '';
    }
    const ccInput = document.getElementById('chief-complaint');
    if(ccInput) ccInput.value = JSON.stringify(ccData);

    // C01 Visit Details -> the hidden inputs submitEncounter reads. The visible fields already mirror on
    // every keystroke; syncing again here means a save can't miss a last edit that never fired an input event.
    [['cpVisitRefProv', 'referring-provider'], ['cpVisitRefReason', 'referral-reason']].forEach(([visibleId, hiddenId]) => {
        const visible = document.getElementById(visibleId), hidden = document.getElementById(hiddenId);
        if (visible && hidden) hidden.value = visible.value;
    });

    // Diagnoses persist immediately via /api/problems now (see loadAssessmentDiagnosisList) —
    // clinical-icd10 only needs to carry the CPT billing lines here.
    let cptLines = [];
    const payload = document.getElementById('cpCptPayload');
    if(payload && payload.value) {
        try {
            JSON.parse(payload.value).forEach(r => {
                cptLines.push(r.code + ' - ' + r.desc + ' | Units:' + (r.units||1) + ' | Charge:' + (r.charge||0) + ' | Mod:' + (r.mod||'') + ' | Dx:' + (r.dx||''));
            });
        } catch(e) {}
    }
    const clinicalIcd10 = document.getElementById('clinical-icd10');
    if(clinicalIcd10) {
        clinicalIcd10.value = cptLines.join('\n');
    }

    const getB = (t) => {
        let bx = Array.from(document.querySelectorAll('.cp-exam-box')).find(b => b.querySelector('.cp-exam-title')?.textContent === t);
        if(!bx) return '';
        let parts = [];
        bx.querySelectorAll('.cp-fg').forEach(fg => {
            let l = fg.querySelector('label');
            let sel = fg.querySelector('select');
            let txt = fg.querySelector('textarea');
            if(l && sel && sel.value && !sel.value.includes('Select')) parts.push(l.textContent + ': ' + sel.value);
            if(l && txt && txt.value) parts.push(l.textContent + ': ' + txt.value);
        });
        return parts.length ? '[' + t + '] ' + parts.join(' | ') : '';
    };
    const sE = (id, arr) => {
        let el = document.getElementById(id);
        if(el) {
            let res = arr.map(t => getB(t)).filter(Boolean).join('\n');
            if(res) el.value = res;
        }
    };
    sE('pe-general', ['General', 'Psychiatric']);
    sE('pe-heent', ['Eyes', 'ENMT', 'Neck']);
    sE('pe-cardio', ['Cardiovascular']);
    sE('pe-resp', ['Respiratory', 'Chest']);
    sE('pe-abdomen', ['Gastrointestinal', 'Genitourinary']);
    sE('pe-neuro', ['Neurological']);
    sE('pe-skin', ['Skin', 'Musculoskeletal', 'Hematologic']);

    let summaryParts = [];
    const ass = document.getElementById('cpAssSum');
    if(ass && ass.value) summaryParts.push('ASSESSMENT:\n' + ass.value);
    const rsn = document.getElementById('cpReason');
    if(rsn && rsn.value) summaryParts.push('CLINICAL REASONING:\n' + rsn.value);
    
    let rfs = [];
    document.querySelectorAll('.cpRF:checked').forEach(cb => {
        if(cb.value === 'Other') {
            const oth = document.querySelector('#cpRFOtherW input');
            if(oth && oth.value) rfs.push(oth.value);
        } else {
            rfs.push(cb.value);
        }
    });
    if(rfs.length > 0) summaryParts.push('RISK FACTORS:\n' + rfs.join(', '));
    
    let comorbs = [];
    document.querySelectorAll('#cpComorbTags span[data-v]').forEach(sp => comorbs.push(sp.dataset.v));
    if(comorbs.length > 0) summaryParts.push('COMORBIDITIES:\n' + comorbs.join(', '));
    
    const plan = document.getElementById('cpPlan');
    if(plan && plan.value) {
        summaryParts.push('TREATMENT PLAN:\n' + plan.value);
        let pcPlan = document.getElementById('pc-cp-treatment-plan');
        if(pcPlan) pcPlan.value = plan.value;
    }
    
    const cs = document.getElementById('clinical-summary');
    if(cs) cs.value = summaryParts.join('\n\n');
};

function syncToUI(note) {
    setTimeout(() => {
        const g = id => { const e = document.getElementById(id); return e ? e.value : ''; };
        const s = (id, v) => { const e = document.getElementById(id); if (e && v) e.value = v; };
        
        let ccRaw = note.chief_complaint || g('chief-complaint');
        if(ccRaw) {
            try {
                if(ccRaw.trim().startsWith('{')) {
                    let ccObj = JSON.parse(ccRaw);
                    s('cpCC', ccObj.text || '');
                    s('cpOnset', ccObj.onset || '');
                    s('cpDuration', ccObj.duration || '');
                    if(ccObj.symptoms && Array.isArray(ccObj.symptoms)) {
                        ccObj.symptoms.forEach(sym => {
                            let cb = document.querySelector('.cpSym[value="' + sym + '"]');
                            if(cb) cb.checked = true;
                        });
                        if(ccObj.symptoms.includes('Other')) {
                            let w = document.getElementById('cpOtherW');
                            if(w) w.style.display = 'block';
                            s('cpOtherT', ccObj.other_symptom || '');
                        }
                    }
                } else {
                    s('cpCC', ccRaw);
                }
            } catch(e) { s('cpCC', ccRaw); }
        }

        s('cpHPI', g('hpi'));
        s('cpBPS', g('vital-bp-systolic')); s('cpBPD', g('vital-bp-diastolic'));
        s('cpHR', g('vital-heart-rate')); s('cpRR', g('vital-resp-rate'));
        s('cpTemp', g('vital-temp')); s('cpSpO2', g('vital-spo2'));
        s('cpWt', g('vital-weight')); s('cpBMI', g('vital-bmi'));
        
        // Auto-recalculate BMI and Sitting BP upon note data load
        const f = parseFloat(document.getElementById('cpHtFt')?.value || 0), i = parseFloat(document.getElementById('cpHtIn')?.value || 0), w = parseFloat(document.getElementById('cpWt')?.value || 0);
        const tot = f * 12 + i;
        if (tot > 0 && w > 0) {
            const bmiVal = ((w / (tot * tot)) * 703).toFixed(1);
            s('cpBMI', bmiVal);
            const rBmi = document.getElementById('vital-bmi');
            if (rBmi) rBmi.value = bmiVal;
        }
        const bps = document.getElementById('cpBPS')?.value, bpd = document.getElementById('cpBPD')?.value;
        const orthoSit = document.getElementById('cardio-bp-sitting');
        if (orthoSit && bps && bpd && !orthoSit.value) orthoSit.value = `${bps}/${bpd}`;
        let pName = note.provider_name || (note.first_name && note.last_name ? `Dr. ${note.first_name} ${note.last_name}` : '');
        if (!pName || pName.trim() === 'Dr. undefined undefined' || pName.trim() === 'Dr.') {
            const origProv = document.getElementById('modal-enc-provider') || document.getElementById('provider_id');
            if (origProv && origProv.selectedIndex > -1) {
                const txt = origProv.options[origProv.selectedIndex].text;
                // If it's not a generic placeholder
                if (txt && !txt.includes('Select') && !txt.includes('Choose') && !txt.includes('Loading')) {
                    pName = txt;
                }
            }
        }
        if (pName) {
            console.log("[syncToUI] Setting provider name to:", pName);
            s('cpSignProv', pName);
            const p = document.getElementById('cpSigProvNamePreview');
            if (p) p.textContent = pName;
        }
        if (note.note_date) s('cpSignDate', note.note_date.slice(0, 10));
        
        // --- RESTORE CPT BILLING LINES FROM DB ---
        // Diagnoses are no longer stored in icd10_codes text (they live in patient_problems,
        // loaded by loadAssessmentDiagnosisList) — only parse out the CPT billing lines here.
        if(note.icd10_codes) {
            let cptCodes = [];
            note.icd10_codes.split('\n').forEach(l => {
                if(!l.trim() || !l.includes('| Units:')) return;
                let m = l.match(/^(.*?) - (.*?)\s*\|\s*Units:(.*?)\s*\|\s*Charge:(.*?)\s*\|\s*Mod:(.*?)\s*\|\s*Dx:(.*)$/);
                if(m) cptCodes.push({ code: m[1].trim(), desc: m[2].trim(), units: parseInt(m[3])||1, charge: parseFloat(m[4])||0, mod: m[5].trim(), dx: m[6].trim(), category: 'E&M' });
            });
            window._savedCptRows = cptCodes;
        }

        let cSum = note.clinical_summary || note.summary || '';
        if(cSum) {
            let assMatch = cSum.match(/ASSESSMENT:\n([\s\S]*?)(?:\n\n[A-Z\s]+:|$)/);
            if(assMatch) s('cpAssSum', assMatch[1].trim());
            
            let rsnMatch = cSum.match(/CLINICAL REASONING:\n([\s\S]*?)(?:\n\n[A-Z\s]+:|$)/);
            if(rsnMatch) s('cpReason', rsnMatch[1].trim());
            
            let planMatch = cSum.match(/TREATMENT PLAN:\n([\s\S]*?)(?:\n\n[A-Z\s]+:|$)/);
            if(planMatch) s('cpPlan', planMatch[1].trim());

            let rfMatch = cSum.match(/RISK FACTORS:\n([\s\S]*?)(?:\n\n[A-Z\s]+:|$)/);
            if(rfMatch) {
                let rfs = rfMatch[1].trim().split(', ');
                rfs.forEach(r => {
                    let cb = document.querySelector('.cpRF[value="' + r + '"]');
                    if(cb) cb.checked = true;
                    else {
                        let othCb = document.querySelector('.cpRF[value="Other"]');
                        if(othCb) othCb.checked = true;
                        let w = document.getElementById('cpRFOtherW');
                        if(w) w.style.display = 'block';
                        let inp = document.querySelector('#cpRFOtherW input');
                        if(inp) inp.value = r;
                    }
                });
            }

            let cmMatch = cSum.match(/COMORBIDITIES:\n([\s\S]*?)(?:\n\n[A-Z\s]+:|$)/);
            if(cmMatch && window._addCTag) {
                cmMatch[1].trim().split(', ').forEach(c => window._addCTag(c));
            }
        }

        const parseEx = (key) => {
            if(!note[key]) return;
            let boxes = document.querySelectorAll('.cp-exam-box');
            note[key].split('\n').forEach(line => {
                let m = line.match(/^\[(.*?)\]\s*(.*)$/);
                if(m) {
                    let title = m[1], content = m[2];
                    let bx = Array.from(boxes).find(b => b.querySelector('.cp-exam-title')?.textContent === title);
                    if(bx) {
                        content.split(' | ').forEach(p => {
                            let pm = p.match(/^(.*?):\s*(.*)$/);
                            if(pm) {
                                let label = pm[1], val = pm[2];
                                bx.querySelectorAll('.cp-fg').forEach(fg => {
                                    let l = fg.querySelector('label');
                                    if(l && l.textContent === label) {
                                        let sel = fg.querySelector('select');
                                        let txt = fg.querySelector('textarea');
                                        if(sel) {
                                            let opt = Array.from(sel.options).find(o => o.value === val);
                                            if(opt) sel.value = val;
                                        } else if(txt) {
                                            txt.value = val;
                                        }
                                    }
                                });
                            }
                        });
                    }
                }
            });
        };
        ['pe_general', 'pe_heent', 'pe_cardio', 'pe_resp', 'pe_abdomen', 'pe_neuro', 'pe_skin'].forEach(parseEx);

        // -----------------------------------------

        [['cpCC', 'cpCC-c'], ['cpHPI', 'cpHPI-c'], ['cpAssSum', 'cpAS-c']].forEach(([id, cid]) => {
            const el = document.getElementById(id), ce = document.getElementById(cid);
            if (el && ce) ce.textContent = el.value.length;
        });
    }, 350);
}

/* ══════════════════════════════════════════════════════════════════
   MAIN OVERRIDE
   ══════════════════════════════════════════════════════════════════ */
(function () {
    setTimeout(() => {
        if (typeof window.populateEncounterModal !== 'function' || window._originalPopulateEncounterModal) return;
        window._originalPopulateEncounterModal = window.populateEncounterModal;

        let allCpts = [];   // populated lazily on first Billing tab click
        let cptsFetched = false;

        window.populateEncounterModal = function (note, isFresh) {
            const dashboard = document.getElementById('patient-dashboard-full-content');
            if (!dashboard || dashboard.offsetParent === null) { window._originalPopulateEncounterModal(note, isFresh); return; }

            // lock_state is the real signed flag; signed_by_name/signed_signature_data are also written by a plain
            // draft save, so they can't be used on their own (they wrongly showed drafts as "Signed (Locked)").
            const isSigned = note.lock_state !== undefined && note.lock_state !== null
                ? Number(note.lock_state) === 1
                : !!(note.signed_signature_data || note.signed_by_name);

            // A. Edit Encounter button
            let editBtn = document.querySelector('.edit-patient-chart-btn') || document.querySelector('.custom-edit-enc-btn');
            if (editBtn) {
                editBtn.classList.remove('edit-patient-chart-btn');
                editBtn.classList.add('custom-edit-enc-btn');
                const nb = editBtn.cloneNode(true); editBtn.parentNode.replaceChild(nb, editBtn);
                
                if (isSigned) {
                    nb.innerHTML = '<i class="fas fa-lock"></i> Signed (Locked)';
                    nb.style.background = '#94a3b8';
                    nb.style.cursor = 'not-allowed';
                    nb.onclick = () => { if(typeof Toast !== 'undefined') Toast.show('This encounter is signed and cannot be edited.', 'info'); };
                } else {
                    nb.innerHTML = '<i class="fas fa-pen"></i> Edit Encounter';
                    nb.style.background = '#0284c7';
                    nb.style.cursor = 'pointer';
                    nb.onclick = () => {
                        if (!window.openNewEncounterModal) return;
                        const req = typeof ApiService !== 'undefined' ? ApiService.request(`/api/patients/${note.patient_id}`) : fetch(`/api/patients/${note.patient_id}`).then(r => r.json());
                        req.then(res => { if (res.status === 'success') window.openNewEncounterModal(res.data, () => window.populateEncounterModal(note, false), note); });
                    };
                }
            }

            // B. Encounter # + Date in banner
            const infoCont = document.querySelector('.profile-banner > div > div:nth-child(2) > div:last-child');
            if (infoCont) {
                ['inline-enc-date', 'inline-enc-info'].forEach(id => { const e = document.getElementById(id); if (e) e.remove(); });
                const sp = document.createElement('span'); sp.id = 'inline-enc-info';
                sp.style.cssText = 'display:flex;align-items:center;gap:14px;color:#334155;font-size:0.85rem;';
                const encNum = 'ENC-' + String(note.id || '').padStart(5, '0');
                const encDate = (note.note_date || new Date().toISOString()).split(/[T\s]/)[0];
                sp.innerHTML = `<span style="display:flex;align-items:center;gap:4px;"><strong>Encounter:</strong> ${encNum}</span><span style="display:flex;align-items:center;gap:4px;"><i class="fas fa-calendar-check" style="color:#0284c7;"></i> <strong>Date:</strong> ${encDate}</span>`;
                infoCont.appendChild(sp);
            }

            // C. Hide existing chart-section-body content (encounter list etc.) to make room for the inline tabs below
            const sectionBody = document.getElementById('chart-section-body');
            if (sectionBody) {
                Array.from(sectionBody.childNodes).forEach(n => { if (n.style) n.style.display = 'none'; });
            }

            // D. Build tabs (10, +1 "Cardiology"/"Orthopedics" tab matching this encounter's specialty)
            const isCardioEncounter = (note.encounter_type || '').toLowerCase().includes('cardio');
            const isOrthoEncounter = (note.encounter_type || '').toLowerCase().includes('ortho');

            // Rescue the real #cardio-form / #ortho-form (and every relocated cardio-sec-*) back to their home
            // inside the (hidden) clinical modal BEFORE removing any previous render's panes below - otherwise a
            // stale cpP8/cpP11 containing the form would take it (and all its data) down with it via .remove().
            // (window.openEncounterInChart in app.js also calls this, earlier, before it even gets here - see
            // rescueClinicalFormSections's own comment for why that second call site is required too.)
            rescueClinicalFormSections();

            const tabMenu = document.getElementById('chart-sidebar-menu');
            if (tabMenu) {
                tabMenu.style.display = 'none';
                const ex = document.querySelector('.inline-encounter-tabs'); if (ex) ex.remove();
                for (let i = 0; i < 16; i++) { const p = document.getElementById(`cpP${i}`); if (p) p.remove(); }
                // The 5-stage stepper's top-level panes (cpStage1..6, built by createStageWithSubPills/inline below)
                // aren't cpP0..15-numbered, so the loop above never touched them - they were left behind as hidden
                // orphans on every re-render after the first. Mount points like #cpAssessCardioMount/#cpVitalsCardioMount
                // aren't unique per render, so getElementById(mountId) in relocateCardioSections() would then find the
                // STALE orphaned copy (first in document order) instead of the current visible one, leaving every
                // cardio-sec-* section (ASCVD, exam findings, ECG/Echo/Device/Holter/Labs, HTN plan) silently empty on
                // every encounter opened after the first one in a page session. Runs after the rescue above, so any
                // cardio-sec-* still inside a stale pane has already been pulled back out into #cardio-form first.
                document.querySelectorAll('.cp-stage-pane').forEach(p => p.remove());

                // ══════════════════════════════════════════════════════════════════
                // OPTION 1: 5-STAGE CLINICAL STEPPER WORKFLOW
                // Stage 1: Intake & History (Visit Details, CC, HPI, History, Meds & Allergies)
                // Stage 2: Objective Exam (Vitals + ASCVD Calc, Physical Exam, Ortho)
                // Stage 3: Diagnostics & Labs (Orders Requisition + Results Review)
                // Stage 4: Assessment & Plan (Cardiac Assessment + Treatment Plan)
                // Stage 5: Billing & Sign (CPT-4 Charge Capture + Encounter Sign-Off)
                // ══════════════════════════════════════════════════════════════════

                // Helper to build a stage container with zero-scroll segmented sub-pills
                function createStageWithSubPills(stageId, stageTitle, stageDesc, subItems) {
                    const pane = document.createElement('div');
                    pane.id = stageId;
                    pane.className = 'cp-wrap cp-stage-pane';

                    const validItems = subItems.filter(Boolean);

                    // Header with clean Info (i) Tooltip Icon
                    const hdr = document.createElement('div');
                    hdr.className = 'cp-hdr';
                    hdr.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:10px;';
                    hdr.innerHTML = `<h2 style="margin:0;">${stageTitle}</h2><span class="cp-info-tip" data-tip="${cpEsc(stageDesc)}">i</span>`;
                    pane.appendChild(hdr);

                    // Sub-pill switcher (only if more than 1 sub-section)
                    if (validItems.length > 1) {
                        const pillNav = document.createElement('div');
                        pillNav.className = 'cp-subpill-nav';

                        const pillBtns = [];
                        validItems.forEach((item, idx) => {
                            const pBtn = document.createElement('button');
                            pBtn.type = 'button';
                            pBtn.className = `cp-subpill ${idx === 0 ? 'active' : ''}`;
                            pBtn.textContent = item.label;
                            pBtn.onclick = () => {
                                pillBtns.forEach(b => b.classList.remove('active'));
                                pBtn.classList.add('active');
                                validItems.forEach((it, i) => {
                                    it.el.style.setProperty('display', i === idx ? 'block' : 'none', 'important');
                                });
                            };
                            pillBtns.push(pBtn);
                            pillNav.appendChild(pBtn);
                        });
                        pane.appendChild(pillNav);
                    }

                    // Append sub-elements
                    validItems.forEach((item, idx) => {
                        if (validItems.length > 1 && idx > 0) {
                            item.el.style.setProperty('display', 'none', 'important');
                        }
                        pane.appendChild(item.el);
                    });

                    return pane;
                }

                const stages = [
                    {
                        key: 'stage-intake',
                        num: 1,
                        label: 'Subjective',
                        icon: 'fa-user-clock',
                        build: () => {
                            const subItems = [
                                { label: 'Visit Details', el: cpB_VisitDetails(note) },
                                { label: 'Chief Complaint', el: cpB_CC(note) },
                                isCardioEncounter ? { label: 'Cardiac HPI', el: cpB_CardiacHPI(note) } : null,
                                isCardioEncounter ? { label: 'Cardiac History', el: cpB_CardiacHistory(note) } : null,
                                { label: 'Meds & Allergies', el: cpB_MedsAllergies(note) }
                            ];
                            return createStageWithSubPills(
                                'cpStage1',
                                'Subjective',
                                'Review visit details, patient complaints, cardiac history, and active medications &amp; allergies.',
                                subItems
                            );
                        }
                    },
                    {
                        key: 'stage-exam',
                        num: 2,
                        label: 'Objective Exam',
                        icon: 'fa-stethoscope',
                        build: () => {
                            const subItems = [
                                { label: 'Vitals & ASCVD', el: cpB_Vitals(note) },
                                { label: 'Physical Exam', el: cpB_Exam(note) },
                                isOrthoEncounter ? { label: 'Orthopedics', el: cpB_Orthopedics(note) } : null
                            ];
                            return createStageWithSubPills(
                                'cpStage2',
                                'Objective Examination',
                                'Record vital signs, calculate ASCVD risk score, and document physical examination findings.',
                                subItems
                            );
                        }
                    },
                    {
                        key: 'stage-diagnostics',
                        num: 3,
                        label: 'Diagnostics & Labs',
                        icon: 'fa-vials',
                        build: () => {
                            const subItems = [
                                { label: 'Orders Requisition', el: cpB_LabOrders(note) },
                                isCardioEncounter ? { label: 'Results Review (ECG/Echo/Labs)', el: cpB_ResultsReview(note) } : null
                            ];
                            return createStageWithSubPills(
                                'cpStage3',
                                'Diagnostics &amp; Labs',
                                'Place new diagnostic orders and review in-office results (ECG, Echo, Cath, Holter, Biomarkers).',
                                subItems
                            );
                        }
                    },
                    {
                        key: 'stage-plan',
                        num: 4,
                        label: 'Assessment & Plan',
                        icon: 'fa-clipboard-check',
                        build: () => {
                            const subItems = [
                                { label: 'Clinical Assessment', el: cpB_Assessment(note) },
                                // C09 Diagnosis / Problem List - the 5-stage stepper dropped this screen when it replaced
                                // the old flat tab row, but Sign Encounter still requires at least one patient_problems
                                // row with an ICD-10 (server-enforced), and Billing's Dx Pointer dropdown only links an
                                // EXISTING diagnosis, it can't add one - with no sub-pill for it, there was no way to
                                // add a diagnosis anywhere in this UI and every encounter failed to sign. wireAll()
                                // already wires #cpAddDiagReal/#cpDiagRealBody unconditionally, so adding it back here
                                // needed no other changes.
                                { label: 'Diagnosis / Problem List', el: cpB_DiagnosisList(note) },
                                { label: 'Treatment & Plan', el: cpB_Plan(note) }
                            ];
                            return createStageWithSubPills(
                                'cpStage4',
                                'Assessment &amp; Plan',
                                'Document clinical impressions, risk stratification, diagnoses, and patient treatment plan.',
                                subItems
                            );
                        }
                    },
                    {
                        key: 'stage-billing',
                        num: 5,
                        label: 'Billing',
                        icon: 'fa-file-invoice-dollar',
                        build: () => {
                            const pane = document.createElement('div');
                            pane.id = 'cpStage5';
                            pane.className = 'cp-wrap cp-stage-pane';

                            const hdr = document.createElement('div');
                            hdr.className = 'cp-hdr';
                            hdr.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:10px;';
                            hdr.innerHTML = `<h2 style="margin:0;">Billing &amp; Coding</h2><span class="cp-info-tip" data-tip="Capture CPT codes with linked diagnosis pointers for this encounter.">i</span>`;
                            pane.appendChild(hdr);

                            pane.appendChild(cpB_Billing(note, allCpts));
                            return pane;
                        }
                    },
                    {
                        key: 'stage-sign',
                        num: 6,
                        label: 'Sign Encounter',
                        icon: 'fa-signature',
                        build: () => {
                            const pane = document.createElement('div');
                            pane.id = 'cpStage6';
                            pane.className = 'cp-wrap cp-stage-pane';

                            const hdr = document.createElement('div');
                            hdr.className = 'cp-hdr';
                            hdr.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:10px;';
                            hdr.innerHTML = `<h2 style="margin:0;">Sign Encounter</h2><span class="cp-info-tip" data-tip="Review encounter summary, provide digital signature, and lock the chart note.">i</span>`;
                            pane.appendChild(hdr);

                            pane.appendChild(cpB_Sign(note));
                            return pane;
                        }
                    }
                ];

                const stageDefs = stages.map(s => s.label);
                const navBar = document.createElement('nav');
                navBar.className = 'my-custom-tabs inline-encounter-tabs cp-stepper-nav';

                const stagePanes = stages.map(s => s.build());
                stagePanes.forEach(p => { p.style.setProperty('display', 'none', 'important'); sectionBody.appendChild(p); });
                if (isCardioEncounter) relocateCardioSections();

                const stageBtns = [];
                stages.forEach((stg, i) => {
                    const btn = document.createElement('div');
                    btn.className = `cp-step-item ${i === 0 ? 'active' : ''}`;
                    btn.innerHTML = `<span>${stg.label}</span>`;
                    btn.onclick = () => {
                        stageBtns.forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        stagePanes.forEach(p => p.style.setProperty('display', 'none', 'important'));
                        stagePanes[i].style.setProperty('display', 'block', 'important');

                        // When switching to Billing stage, initialize CPT Manager & Dx dropdown
                        if (stg.key === 'stage-billing') {
                            if (!cptsFetched) {
                                cptsFetched = true;
                                const req = typeof ApiService !== 'undefined' ? ApiService.request('/api/billing/cpt-codes') : fetch('/api/billing/cpt-codes').then(r => r.json());
                                req.then(res => {
                                    allCpts = res.data || res.cpt_codes || [];
                                    initCPTManager(allCpts);
                                    if (window._refreshCptDxDropdown) window._refreshCptDxDropdown();
                                }).catch(() => {
                                    initCPTManager([]);
                                    if (window._refreshCptDxDropdown) window._refreshCptDxDropdown();
                                });
                            } else {
                                if (window._refreshCptDxDropdown) window._refreshCptDxDropdown();
                            }
                        }

                        // When switching to Sign stage, refresh provider name preview
                        if (stg.key === 'stage-sign') {
                            const signProvInp = document.getElementById('cpSignProv');
                            const sigProvPreview = document.getElementById('cpSigProvNamePreview');
                            let fallbackName = note.provider_name || (note.first_name && note.last_name ? `Dr. ${note.first_name} ${note.last_name}` : '');
                            const origProv = document.getElementById('modal-enc-provider');
                            if (origProv && origProv.selectedIndex > -1) {
                                const txt = origProv.options[origProv.selectedIndex].text;
                                if (txt && !txt.includes('Select') && !txt.includes('Loading')) fallbackName = txt;
                            }
                            if (fallbackName && fallbackName.trim() !== 'Dr. undefined undefined' && fallbackName.trim() !== 'Dr.') {
                                if (signProvInp && (!signProvInp.value || signProvInp.value === 'Provider name' || signProvInp.value === 'Provider')) {
                                    signProvInp.value = fallbackName;
                                    if (sigProvPreview) sigProvPreview.textContent = fallbackName;
                                }
                            }
                        }
                    };
                    stageBtns.push(btn);
                    navBar.appendChild(btn);
                });

                tabMenu.parentNode.insertBefore(navBar, tabMenu.nextSibling);
                wireAll(stagePanes, stageBtns, note, allCpts);
                wireWorkflowNav(stagePanes, stageBtns, stageDefs);
                wireNewEncounterPanes(note);
                stageBtns[0].onclick();
            }

            // E. Back button
            const backBtn = document.getElementById('back-to-directory-btn');
            if (backBtn) {
                const nb = backBtn.cloneNode(true);
                nb.innerHTML = '<i class="fas fa-arrow-left"></i> Back to Encounters';
                backBtn.parentNode.replaceChild(nb, backBtn);
                nb.onclick = () => {
                    nb.innerHTML = '<i class="fas fa-arrow-left"></i> Back to Patient Directory';
                    const req = typeof ApiService !== 'undefined' ? ApiService.request(`/api/patients/${note.patient_id}`) : fetch(`/api/patients/${note.patient_id}`).then(r => r.json());
                    req.then(res => { if (res.status === 'success' && window.openPatientChart) window.openPatientChart(res.data, 'encounters'); });
                };
            }

            // Populate the modal purely as a hidden data source for the inline tabs below —
            // suppress its own show() so it never actually flashes on screen.
            window.__suppressEncounterModalShow = true;
            window._originalPopulateEncounterModal(note, isFresh);
            window.__suppressEncounterModalShow = false;
            syncToUI(note);

            const modal = document.getElementById('clinical-encounter-modal');
            if (modal) { bootstrap.Modal.getOrCreateInstance(modal, { backdrop: 'static', keyboard: false }).hide(); }
        };
    }, 1000);
})();
// Hijack submitEncounter to ensure custom tabs are always synced and to handle redirection
if (typeof window.submitEncounter === 'function' && !window._originalSubmitEncounter) {
    window._originalSubmitEncounter = window.submitEncounter;
    window.submitEncounter = async function(e) {
        if (typeof window._syncCustomTabsToDOM === 'function') {
            window._syncCustomTabsToDOM();
        }
        await window._originalSubmitEncounter(e);
        
        // Go back to the encounters view automatically
        const backBtn = document.querySelector('.back-to-directory-btn');
        if (backBtn) {
            backBtn.click();
        }
    };
}

// Global Delegated Handler for ASCVD 10-Year Risk Calculator
document.addEventListener('click', function(e) {
    const calcAscvdBtn = e.target.closest('#calc-ascvd-btn');
    if (!calcAscvdBtn) return;
    e.preventDefault();

    // Pull Systolic BP from any available input (custom tabs cpBPS, clinical modal, or cardio sitting BP)
    const cpBpsVal = document.getElementById('cpBPS')?.value;
    const vitalBpSysVal = document.getElementById('vital-bp-systolic')?.value;
    const cardioSittingVal = document.getElementById('cardio-bp-sitting')?.value;
    let sbpVal = 140;

    if (cpBpsVal && parseInt(cpBpsVal, 10)) {
        sbpVal = parseInt(cpBpsVal, 10);
    } else if (vitalBpSysVal && parseInt(vitalBpSysVal, 10)) {
        sbpVal = parseInt(vitalBpSysVal, 10);
    } else if (cardioSittingVal) {
        const parts = cardioSittingVal.split('/');
        if (parts[0] && parseInt(parts[0], 10)) {
            sbpVal = parseInt(parts[0], 10);
        }
    }

    // ACC/AHA ASCVD Risk Calculation Estimator
    let calculatedRisk = 18.5;
    if (sbpVal >= 160) {
        calculatedRisk = 24.2;
    } else if (sbpVal >= 140) {
        calculatedRisk = 18.5;
    } else if (sbpVal >= 130) {
        calculatedRisk = 11.2;
    } else {
        calculatedRisk = 4.8;
    }

    const ascvdInput = document.getElementById('cardio-ascvd-score');
    const ascvdTier = document.getElementById('cardio-ascvd-tier');

    if (ascvdInput) {
        ascvdInput.value = calculatedRisk.toFixed(1);
        ascvdInput.dispatchEvent(new Event('input', { bubbles: true }));
        ascvdInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    if (ascvdTier) {
        if (calculatedRisk >= 20.0) ascvdTier.value = 'High-Risk (≥20% or Clinical ASCVD)';
        else if (calculatedRisk >= 7.5) ascvdTier.value = 'Intermediate (7.5% - 19.9%)';
        else if (calculatedRisk >= 5.0) ascvdTier.value = 'Borderline (5% - 7.4%)';
        else ascvdTier.value = 'Low-Risk (<5%)';

        ascvdTier.dispatchEvent(new Event('change', { bubbles: true }));
    }

    if (typeof Toast !== 'undefined' && Toast.show) {
        Toast.show(`Calculated 10-Year ASCVD Risk: ${calculatedRisk.toFixed(1)}% (SBP: ${sbpVal} mmHg)`, 'success');
    }
});

