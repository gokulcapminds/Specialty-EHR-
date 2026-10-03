<!-- public/modules/clinical_modal.php -->
<div id="clinical-encounter-modal" class="modal fade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable mod-clinical-style-8">
    <div class="modal-content">
        <div class="modal-header mod-clinical-style-9">
            <div style="display:flex;align-items:center;gap:12px;flex:1;">
                <h2 class="mod-clinical-style-10" id="clinical-modal-title">New Clinical Encounter</h2>
                <!-- Phase 2: Encounter status badge (ENC lifecycle indicator) -->
                <span id="encounter-status-badge" class="enc-status-badge enc-status-draft" title="Encounter Status">Draft</span>
                <span id="encounter-lock-badge" class="enc-lock-badge hidden" title="Encounter is locked and read-only">🔒 Locked</span>
            </div>
            <button type="button" class="modal-close" id="close-clinical-modal-btn">&times;</button>
        </div>
        <div class="modal-body mod-clinical-style-11">

            <!-- Clinical Workspace Accordion Container inside Modal -->
            <div id="clinical-accordion" class="accordion-container">
                
                <!-- Accordion Section 1: Encounter Details & Vitals -->
                <div class="accordion-item">
                    <button class="accordion-header" aria-expanded="true" aria-controls="accordion-vitals" id="accordion-vitals-btn">
                        Encounter Setup & Patient Vitals
                    </button>
                    <div id="accordion-vitals" class="accordion-content" role="region" aria-labelledby="accordion-vitals-btn">
                        <form class="mod-clinical-style-12" id="vitals-form" novalidate>
                            
                            <div class="mod-clinical-style-13">
                                <div class="form-group">
                                    <label class="form-label" for="encounter-type-select">Encounter Type / Specialty</label>
                                    <select id="encounter-type-select" class="form-control mod-clinical-style-14">
                                        <?php foreach (require __DIR__ . '/../../backend/config/specialties.php' as $specKey => $specDef): ?>
                                        <option value="<?= htmlspecialchars($specKey) ?>"><?= htmlspecialchars($specDef['label']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="vital-temp">Temperature (°F)</label>
                                    <input type="number" step="0.1" id="vital-temp" class="form-control" placeholder="98.6">
                                </div>
                            </div>

                            <div class="mod-clinical-style-15">
                                <div class="form-group">
                                    <label class="form-label" for="vital-bp-systolic">BP Systolic (mmHg)</label>
                                    <input type="number" id="vital-bp-systolic" class="form-control" placeholder="120">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="vital-bp-diastolic">BP Diastolic (mmHg)</label>
                                    <input type="number" id="vital-bp-diastolic" class="form-control" placeholder="80">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="vital-heart-rate">Heart Rate (bpm)</label>
                                    <input type="number" id="vital-heart-rate" class="form-control" placeholder="72">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="vital-resp-rate">Resp Rate (cpm)</label>
                                    <input type="number" id="vital-resp-rate" class="form-control" placeholder="16">
                                </div>
                            </div>

                            <div class="mod-clinical-style-16">
                                <div class="form-group">
                                    <label class="form-label" for="vital-spo2">SpO2 (%)</label>
                                    <input type="number" id="vital-spo2" class="form-control" placeholder="98">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="vital-height">Height (in)</label>
                                    <input type="number" step="0.1" id="vital-height" class="form-control" placeholder="68">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="vital-weight">Weight (lbs)</label>
                                    <input type="number" step="0.1" id="vital-weight" class="form-control" placeholder="150">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="vital-bmi">Calculated BMI</label>
                                    <input type="number" step="0.1" id="vital-bmi" class="form-control mod-clinical-style-17" readonly placeholder="22.8">
                                </div>
                            </div>
                            <!-- Phase 2 VIT-CORE-001: Pain Score (0-10) -->
                            <div class="mod-clinical-style-16">
                                <div class="form-group">
                                    <label class="form-label" for="vital-pain-score">Pain Score (0–10)</label>
                                    <input type="number" min="0" max="10" id="vital-pain-score" class="form-control" placeholder="0–10">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="encounter-visit-type">Visit Type</label>
                                    <select id="encounter-visit-type" class="form-control">
                                        <!-- options are filled by applyVisitTypeSelects() (app.js) from GET /api/visit-types -->
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="encounter-mode-select">Encounter Mode</label>
                                    <select id="encounter-mode-select" class="form-control">
                                        <option value="In-Person">In-Person</option>
                                        <option value="Telehealth">Telehealth</option>
                                        <option value="Walk-In">Walk-In</option>
                                        <option value="Phone">Phone</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Accordion Section 2: Active Allergies, Past Medical History & Current Medications -->
                <div class="accordion-item">
                    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-history" id="accordion-history-btn">
                        Active Allergies, Past Medical History & Current Medications
                    </button>
                    <div id="accordion-history" class="accordion-content hidden" role="region" aria-labelledby="accordion-history-btn">
                        <form class="mod-clinical-style-12" id="history-form" novalidate>
                            <div class="form-group">
                                <label class="form-label mod-clinical-style-42" for="clinical-allergies">⚠️ Active Allergies & Adverse Reactions</label>
                                <textarea id="clinical-allergies" class="form-control" rows="2" placeholder="e.g. Penicillin (Anaphylaxis), Latex (Rash), NKA..."></textarea>
                            </div>
                            <div class="mod-clinical-style-39">
                                <div class="form-group">
                                    <label class="form-label" for="clinical-pmh">Past Medical & Surgical History (PMH)</label>
                                    <textarea id="clinical-pmh" class="form-control" rows="3" placeholder="e.g. Hypertension, Type 2 Diabetes, Coronary Stent (2021)..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="clinical-medications">Current Active Medications</label>
                                    <textarea id="clinical-medications" class="form-control" rows="3" placeholder="e.g. Atorvastatin 40mg daily, Lisinopril 10mg daily..."></textarea>
                                </div>
                            </div>
                            
                            <h4 class="mod-clinical-style-43">Family Medical History</h4>
                            <div class="mod-clinical-style-44">
                                <div class="form-group">
                                    <label class="form-label" for="fh-relation-select">Relation</label>
                                    <select id="fh-relation-select" class="form-control">
                                        <option value="Father">Father</option>
                                        <option value="Mother">Mother</option>
                                        <option value="Paternal Grandfather">Paternal Grandfather</option>
                                        <option value="Paternal Grandmother">Paternal Grandmother</option>
                                        <option value="Maternal Grandfather">Maternal Grandfather</option>
                                        <option value="Maternal Grandmother">Maternal Grandmother</option>
                                        <option value="Brother">Brother</option>
                                        <option value="Sister">Sister</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="fh-condition-input">Health Condition</label>
                                    <input type="text" id="fh-condition-input" class="form-control" placeholder="e.g. CAD, Hypertension, Stroke">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="fh-start-date-input">Onset Date / Age</label>
                                    <input type="text" id="fh-start-date-input" class="form-control" placeholder="e.g. Age 52">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" id="fh-add-row-btn" class="btn btn-secondary mod-clinical-style-45">Add</button>
                                </div>
                            </div>
                            
                            <div class="mod-clinical-style-46">
                                <table class="mod-clinical-style-47">
                                    <thead class="mod-clinical-style-48">
                                        <tr>
                                            <th class="mod-clinical-style-49">Relation</th>
                                            <th class="mod-clinical-style-50">Health Condition</th>
                                            <th class="mod-clinical-style-51">Onset</th>
                                            <th class="mod-clinical-style-52">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="fh-table-body">
                                        <tr id="fh-empty-row">
                                            <td class="mod-clinical-style-53" colspan="4">No family history entries added yet.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" id="clinical-family-history" value="">
                            <!-- Generic SOAP fields synced from the inline Cardiology-tabs UI (clinical-tabs.js
                                 _syncCustomTabsToDOM/syncToUI) before every save. These must exist for that sync
                                 (and submitEncounter's Chief Complaint validation) to have anywhere to write to. -->
                            <input type="hidden" id="chief-complaint" name="chief_complaint" value="">
                            <input type="hidden" id="hpi" name="hpi" value="">
                            <input type="hidden" id="ros" name="ros" value="">
                            <input type="hidden" id="pe-general" name="pe_general" value="">
                            <input type="hidden" id="pe-heent" name="pe_heent" value="">
                            <input type="hidden" id="pe-cardio" name="pe_cardio" value="">
                            <input type="hidden" id="pe-resp" name="pe_resp" value="">
                            <input type="hidden" id="pe-abdomen" name="pe_abdomen" value="">
                            <input type="hidden" id="pe-neuro" name="pe_neuro" value="">
                            <input type="hidden" id="pe-skin" name="pe_skin" value="">
                        </form>
                    </div>
                </div>

                <!-- ===== 1. CARDIOLOGY EHR PANEL ===== -->
                <div class="accordion-item specialty-only cardio-only">
                    <button class="accordion-header" aria-expanded="true" aria-controls="accordion-cardio" id="accordion-cardio-btn">
                        🫀 Cardiology Comprehensive Assessment Suite
                    </button>
                    <div id="accordion-cardio" class="accordion-content" role="region" aria-labelledby="accordion-cardio-btn">
                        <form id="cardio-form" novalidate class="mod-clinical-style-12">
                            <!-- Workflow Selector: drives which cards below are shown (see toggleCardioWorkflow() in app.js) -->
                            <div style="background: var(--bg-tertiary); border: 1px solid var(--primary-color); border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" for="cardio-workflow-select" style="font-weight: 700; color: var(--primary-color);">
                                        <i class="fas fa-route" style="margin-right: 6px;"></i> Cardiology Visit Workflow
                                    </label>
                                    <select id="cardio-workflow-select" name="cardio_workflow" class="form-control">
                                        <option value="chest_pain">Chest Pain Evaluation</option>
                                        <option value="dyspnea">Dyspnea / Heart Failure Evaluation</option>
                                        <option value="palpitations">Palpitations / Arrhythmia</option>
                                        <option value="hypertension">Hypertension / Cardiac Risk</option>
                                        <option value="followup">Routine Cardiology Follow-up</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Card F: Routine Follow-up Summary (Follow-up workflow only) -->
                            <div data-workflow="followup" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="margin:0 0 10px 0; color:#1d4ed8;"><i class="fas fa-history" style="margin-right:6px;"></i> Routine Follow-up Summary</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="cardio-followup-prev-diagnosis">Previous Diagnosis</label><textarea id="cardio-followup-prev-diagnosis" name="cardio_followup_prev_diagnosis" class="form-control" rows="2" placeholder="e.g. Stable CAD s/p PCI to LAD (2024), well-controlled HTN"></textarea></div>
                                    <div class="form-group"><label class="form-label" for="cardio-followup-interval-history">Interval History</label><textarea id="cardio-followup-interval-history" name="cardio_followup_interval_history" class="form-control" rows="2" placeholder="Changes/events since last visit"></textarea></div>
                                    <div class="form-group"><label class="form-label" for="cardio-followup-prev-results">Review of Previous Results</label><textarea id="cardio-followup-prev-results" name="cardio_followup_prev_results_reviewed" class="form-control" rows="2" placeholder="Prior labs/ECG/Echo reviewed with patient"></textarea></div>
                                </div>
                            </div>

                            <!-- Card 1: Hemodynamics, Orthostatics & ASCVD Risk -->
                            <div data-workflow="chest_pain dyspnea palpitations hypertension" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <h4 class="form-section-title" style="margin:0; color:#0369a1;"><i class="fas fa-calculator" style="margin-right:6px;"></i> 1. Hemodynamics &amp; 10-Year ASCVD Risk Score</h4>
                                    <span class="badge badge-primary-xs" style="background:#e0f2fe; color:#0369a1; font-weight:700; padding:4px 10px; border-radius:12px;">AHA/ACC GDMT Engine</span>
                                </div>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group">
                                        <label class="form-label" for="cardio-bp-sitting">Sitting BP (mmHg)</label>
                                        <input type="text" id="cardio-bp-sitting" name="cardio_bp_sitting" class="form-control" placeholder="e.g. 158/94">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="cardio-bp-standing">Standing BP (Orthostatics)</label>
                                        <input type="text" id="cardio-bp-standing" name="cardio_bp_standing" class="form-control" placeholder="e.g. 142/88 (-16 mmHg)">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="cardio-hr-rhythm">Heart Rate &amp; Rhythm</label>
                                        <select id="cardio-hr-rhythm" name="cardio_hr_rhythm" class="form-control">
                                            <option value="Regular">Regular</option>
                                            <option value="Irregularly Irregular (AFib)">Irregularly Irregular (AFib)</option>
                                            <option value="Regularly Irregular (PVCs/PACs)">Regularly Irregular (PVCs/PACs)</option>
                                            <option value="Paced Rhythm">Paced Rhythm</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="cardio-ascvd-score">10-Year ASCVD Risk Score (%)</label>
                                        <div style="display: flex; gap: 6px;">
                                            <input type="number" step="0.1" id="cardio-ascvd-score" name="cardio_ascvd_score" class="form-control" placeholder="e.g. 18.5">
                                            <button type="button" id="calc-ascvd-btn" class="btn btn-secondary btn-sm" style="white-space:nowrap; padding: 0 10px; font-weight:600;">Calc</button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="cardio-ascvd-tier">ASCVD Risk Category</label>
                                        <select id="cardio-ascvd-tier" name="cardio_ascvd_tier" class="form-control">
                                            <option value="">Select Tier...</option>
                                            <option value="Low-Risk (&lt;5%)">Low-Risk (&lt;5%)</option>
                                            <option value="Borderline (5% - 7.4%)">Borderline (5% - 7.4%)</option>
                                            <option value="Intermediate (7.5% - 19.9%)">Intermediate (7.5% - 19.9%)</option>
                                            <option value="High-Risk (≥20% or Clinical ASCVD)">High-Risk (≥20% or Clinical ASCVD)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Symptoms, Angina (CCS) & Heart Failure (NYHA) Staging -->
                            <div data-workflow="chest_pain dyspnea palpitations" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="color:#0f172a;"><i class="fas fa-stethoscope" style="margin-right:6px;"></i> 2. Cardiac Symptoms &amp; Functional Staging</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="cardio-complaint">Chief Complaint</label><input type="text" id="cardio-complaint" name="cardio_complaint" class="form-control" placeholder="e.g. Exertional chest pressure, dyspnea on exertion"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-onset">Onset / Timing</label><select id="cardio-onset" name="cardio_onset" class="form-control"><option value="">Select...</option><option>Acute (Hours to Days)</option><option>Subacute (Weeks)</option><option>Chronic / Progressive</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-chest-pain-quality">Chest Pain Quality</label><select id="cardio-chest-pain-quality" name="cardio_chest_pain_quality" class="form-control"><option value="None">None</option><option value="Pressure / Heaviness">Pressure / Heaviness</option><option value="Squeezing / Tightness">Squeezing / Tightness</option><option value="Burning / Aching">Burning / Aching</option><option value="Sharp / Pleuritic">Sharp / Pleuritic</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-radiation">Radiation of Pain</label><input type="text" id="cardio-radiation" name="cardio_radiation" class="form-control" placeholder="e.g. Left arm, neck, jaw, back"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-relief-nitro">Relieved by Rest / Nitroglycerin?</label><select id="cardio-relief-nitro" name="cardio_relief_nitro" class="form-control"><option value="Yes - prompt relief (&lt;5 min)">Yes - prompt relief (&lt;5 min)</option><option value="Partial relief">Partial relief</option><option value="No relief">No relief</option><option value="Not tested">Not tested</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-ccs-class">Canadian CV Society (CCS) Angina Class</label><select id="cardio-ccs-class" name="cardio_ccs_class" class="form-control"><option value="Class 0 - No Angina">Class 0 - No Angina</option><option value="Class I - Angina only with strenuous exercise">Class I - Angina only with strenuous exercise</option><option value="Class II - Slight limitation with ordinary activity">Class II - Slight limitation with ordinary activity</option><option value="Class III - Marked limitation with ordinary walking/stairs">Class III - Marked limitation with ordinary walking/stairs</option><option value="Class IV - Inability to carry out activity without discomfort">Class IV - Inability to carry out activity without discomfort</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-nyha">NYHA Heart Failure Functional Class</label><select id="cardio-nyha" name="cardio_nyha" class="form-control"><option value="N/A - No Heart Failure">N/A - No Heart Failure</option><option value="Class I - No limitation of physical activity">Class I - No limitation of physical activity</option><option value="Class II - Slight limitation, comfortable at rest">Class II - Slight limitation, comfortable at rest</option><option value="Class III - Marked limitation, comfortable only at rest">Class III - Marked limitation, comfortable only at rest</option><option value="Class IV - Symptoms present at rest">Class IV - Symptoms present at rest</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-orthopnea">Orthopnea / PND</label><select id="cardio-orthopnea" name="cardio_orthopnea" class="form-control"><option value="None">None</option><option value="1-Pillow Orthopnea">1-Pillow Orthopnea</option><option value="2-3 Pillow Orthopnea">2-3 Pillow Orthopnea</option><option value="Paroxysmal Nocturnal Dyspnea (PND)">Paroxysmal Nocturnal Dyspnea (PND)</option></select></div>
                                </div>
                            </div>

                            <!-- Card 3: Cardiovascular Physical Examination -->
                            <div data-workflow="chest_pain dyspnea palpitations hypertension followup" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="color:#0f172a;"><i class="fas fa-user-md" style="margin-right:6px;"></i> 3. Cardiovascular Physical Examination</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="cardio-jvp">Jugular Venous Pressure (JVP)</label><select id="cardio-jvp" name="cardio_jvp" class="form-control"><option value="Normal (&lt;3 cm above sternal angle)">Normal (&lt;3 cm above sternal angle)</option><option value="Elevated (3-5 cm)">Elevated (3-5 cm)</option><option value="Markedly Elevated (&gt;5 cm / JVD present)">Markedly Elevated (&gt;5 cm / JVD present)</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-carotid-bruits">Carotid Arteries</label><select id="cardio-carotid-bruits" name="cardio_carotid_bruits" class="form-control"><option value="Brisk upstroke, no bruits bilaterally">Brisk upstroke, no bruits bilaterally</option><option value="Right Carotid Bruit Present">Right Carotid Bruit Present</option><option value="Left Carotid Bruit Present">Left Carotid Bruit Present</option><option value="Bilateral Bruits">Bilateral Bruits</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-s1-s2">Heart Sounds S1/S2</label><select id="cardio-s1-s2" name="cardio_s1_s2" class="form-control"><option value="Normal S1/S2, regular rate and rhythm">Normal S1/S2, regular rate and rhythm</option><option value="Diminished S1">Diminished S1</option><option value="Loud A2 (Hypertension)">Loud A2 (Hypertension)</option><option value="Fixed Split S2 (ASD)">Fixed Split S2 (ASD)</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-s3-s4">Extra Heart Sounds / Gallops</label><select id="cardio-s3-s4" name="cardio_s3_s4" class="form-control"><option value="None">None</option><option value="S3 Gallop Present (Volume Overload / LV Dysfunction)">S3 Gallop Present (Volume Overload / LV Dysfunction)</option><option value="S4 Gallop Present (LV Hypertrophy / Stiff Ventricle)">S4 Gallop Present (LV Hypertrophy / Stiff Ventricle)</option><option value="Summation Gallop (S3 + S4)">Summation Gallop (S3 + S4)</option><option value="Pericardial Friction Rub">Pericardial Friction Rub</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-murmur">Cardiac Murmur</label><select id="cardio-murmur" name="cardio_murmur" class="form-control"><option value="None">None</option><option value="Systolic Ejection Murmur Grade 2/6">Systolic Ejection Murmur Grade 2/6</option><option value="Systolic Ejection Murmur Grade 3/6 (Aortic Stenosis)">Systolic Ejection Murmur Grade 3/6 (Aortic Stenosis)</option><option value="Holosystolic Murmur at Apex (Mitral Regurgitation)">Holosystolic Murmur at Apex (Mitral Regurgitation)</option><option value="Early Diastolic Decrescendo (Aortic Regurgitation)">Early Diastolic Decrescendo (Aortic Regurgitation)</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-lung-sounds">Lung Auscultation (CHF Signs)</label><select id="cardio-lung-sounds" name="cardio_lung_sounds" class="form-control"><option value="Clear to auscultation bilaterally (CTAB)">Clear to auscultation bilaterally (CTAB)</option><option value="Bibasilar Fine Crackles / Rales">Bibasilar Fine Crackles / Rales</option><option value="Coarse Rales to Mid-Lung Zones">Coarse Rales to Mid-Lung Zones</option><option value="Expiratory Wheezing (Cardiac Asthma)">Expiratory Wheezing (Cardiac Asthma)</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-edema">Peripheral Edema Grade</label><select id="cardio-edema" name="cardio_edema" class="form-control"><option value="None">None</option><option value="Trace / 1+ Bilateral Ankle Edema">Trace / 1+ Bilateral Ankle Edema</option><option value="2+ Pitting Ankle Edema">2+ Pitting Ankle Edema</option><option value="3+ Pitting Pretibial Edema">3+ Pitting Pretibial Edema</option><option value="4+ Severe Edema / Anasarca">4+ Severe Edema / Anasarca</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-pulses">Distal Peripheral Pulses (DP/PT)</label><select id="cardio-pulses" name="cardio_pulses" class="form-control"><option value="2+ Normal &amp; Symmetrical bilaterally">2+ Normal &amp; Symmetrical bilaterally</option><option value="1+ Diminished Bilaterally (PAD)">1+ Diminished Bilaterally (PAD)</option><option value="0 Absent Pulses (Severe PAD)">0 Absent Pulses (Severe PAD)</option></select></div>
                                </div>
                            </div>

                            <!-- Card 4: 12-Lead ECG / EKG Interpretation -->
                            <div data-workflow="chest_pain palpitations" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <h4 class="form-section-title" style="margin:0; color:#0f172a;"><i class="fas fa-wave-square" style="margin-right:6px;"></i> 4. 12-Lead Electrocardiogram (ECG / EKG)</h4>
                                    <span class="badge" style="background:#f1f5f9; color:#475569; font-weight:600; font-size:0.75rem; padding:3px 8px; border-radius:4px;">CPT-4: 93000</span>
                                </div>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="cardio-ekg-rhythm">ECG Rhythm</label><select id="cardio-ekg-rhythm" name="cardio_ekg_rhythm" class="form-control"><option value="Normal Sinus Rhythm">Normal Sinus Rhythm</option><option value="Sinus Tachycardia">Sinus Tachycardia</option><option value="Sinus Bradycardia">Sinus Bradycardia</option><option value="Atrial Fibrillation with RVR">Atrial Fibrillation with RVR</option><option value="Atrial Fibrillation Controlled">Atrial Fibrillation Controlled</option><option value="Atrial Flutter (2:1 Block)">Atrial Flutter (2:1 Block)</option><option value="Ventricular Paced Rhythm">Ventricular Paced Rhythm</option><option value="First Degree AV Block">First Degree AV Block</option><option value="Left Bundle Branch Block (LBBB)">Left Bundle Branch Block (LBBB)</option><option value="Right Bundle Branch Block (RBBB)">Right Bundle Branch Block (RBBB)</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-ekg-rate">Rate (bpm)</label><input type="number" id="cardio-ekg-rate" name="cardio_ekg_rate" class="form-control" placeholder="75"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-pr-interval">PR Interval (ms)</label><input type="number" id="cardio-pr-interval" name="cardio_pr_interval" class="form-control" placeholder="160 (Normal 120-200)"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-qrs-duration">QRS Duration (ms)</label><input type="number" id="cardio-qrs-duration" name="cardio_qrs_duration" class="form-control" placeholder="92 (Normal &lt;120)"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-qtc">QTc Interval (ms)</label><input type="number" id="cardio-qtc" name="cardio_qtc" class="form-control" placeholder="428 (Normal &lt;450 M / &lt;460 F)"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-st-changes">ST-T Wave Ischemia</label><select id="cardio-st-changes" name="cardio_st_changes" class="form-control"><option value="Normal ST-T segments">Normal ST-T segments</option><option value="ST Depression in Inferolateral leads (II, III, aVF, V5-V6)">ST Depression in Inferolateral leads (II, III, aVF, V5-V6)</option><option value="ST Elevation (Anteroseptal V1-V4)">ST Elevation (Anteroseptal V1-V4)</option><option value="T-Wave Inversions (V4-V6)">T-Wave Inversions (V4-V6)</option><option value="LVH by Sokolow-Lyon / Cornell Voltage Criteria">LVH by Sokolow-Lyon / Cornell Voltage Criteria</option><option value="Pathological Q-Waves (Prior Infarct)">Pathological Q-Waves (Prior Infarct)</option></select></div>
                                    <div class="form-group">
                                        <label class="form-label" for="cardio-ekg-file">ECG Report Upload (PDF/Image)</label>
                                        <input type="hidden" id="cardio-ekg-document-id" name="cardio_ekg_document_id">
                                        <input type="file" id="cardio-ekg-file" class="form-control cardio-file-attach-input" data-target-field="cardio-ekg-document-id" data-doc-label="ECG Report" accept=".pdf,.png,.jpg,.jpeg">
                                        <span id="cardio-ekg-file-status" class="cardio-file-attach-status" style="display:block; font-size:0.78rem; color:var(--text-secondary); margin-top:4px;"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 5: Echocardiogram & Wall Motion Suite -->
                            <div data-workflow="dyspnea" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <h4 class="form-section-title" style="margin:0; color:#0f172a;"><i class="fas fa-heart" style="margin-right:6px;"></i> 5. Transthoracic Echocardiogram (TTE) &amp; Hemodynamics</h4>
                                    <span class="badge" style="background:#f1f5f9; color:#475569; font-weight:600; font-size:0.75rem; padding:3px 8px; border-radius:4px;">CPT-4: 93306</span>
                                </div>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="cardio-echo-ef">Left Ventricular Ejection Fraction (LVEF %)</label><input type="number" id="cardio-echo-ef" name="cardio_echo_ef" class="form-control" placeholder="e.g. 55% (Normal ≥55%)"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-echo-wma">LV Wall Motion Abnormalities</label><select id="cardio-echo-wma" name="cardio_echo_wma" class="form-control"><option value="Normal Global LV Systolic Function">Normal Global LV Systolic Function</option><option value="Anterior / Anteroseptal Hypokinesis">Anterior / Anteroseptal Hypokinesis</option><option value="Inferior / Inferolateral Hypokinesis">Inferior / Inferolateral Hypokinesis</option><option value="Apical Akinesis / Aneurysm">Apical Akinesis / Aneurysm</option><option value="Global Severe LV Hypokinesis (LVEF &lt;30%)">Global Severe LV Hypokinesis (LVEF &lt;30%)</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-echo-diastolic">LV Diastolic Function</label><select id="cardio-echo-diastolic" name="cardio_echo_diastolic" class="form-control"><option value="Normal Diastolic Function">Normal Diastolic Function</option><option value="Grade I (Impaired Relaxation)">Grade I (Impaired Relaxation)</option><option value="Grade II (Pseudonormal)">Grade II (Pseudonormal)</option><option value="Grade III (Restrictive Filling)">Grade III (Restrictive Filling)</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-echo-aortic">Aortic Valve Status</label><select id="cardio-echo-aortic" name="cardio_echo_aortic" class="form-control"><option value="Normal Trileaflet Aortic Valve">Normal Trileaflet Aortic Valve</option><option value="Aortic Sclerosis (Peak Vel &lt;2.5 m/s)">Aortic Sclerosis (Peak Vel &lt;2.5 m/s)</option><option value="Mild Aortic Stenosis (Mean Grad &lt;20 mmHg)">Mild Aortic Stenosis (Mean Grad &lt;20 mmHg)</option><option value="Moderate Aortic Stenosis (Mean Grad 20-40 mmHg)">Moderate Aortic Stenosis (Mean Grad 20-40 mmHg)</option><option value="Severe Aortic Stenosis (AVA &lt;1.0 cm2, Mean Grad ≥40 mmHg)">Severe Aortic Stenosis (AVA &lt;1.0 cm2, Mean Grad ≥40 mmHg)</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-echo-mitral">Mitral Valve Status</label><select id="cardio-echo-mitral" name="cardio_echo_mitral" class="form-control"><option value="Normal / Trace Regurgitation">Normal / Trace Regurgitation</option><option value="Mild Mitral Regurgitation">Mild Mitral Regurgitation</option><option value="Moderate Mitral Regurgitation">Moderate Mitral Regurgitation</option><option value="Severe Mitral Regurgitation (Flail Leaflet)">Severe Mitral Regurgitation (Flail Leaflet)</option><option value="Mitral Valve Prolapse (MVP)">Mitral Valve Prolapse (MVP)</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-echo-pasp">Est. Pulmonary Artery Pressure (PASP mmHg)</label><input type="number" id="cardio-echo-pasp" name="cardio_echo_pasp" class="form-control" placeholder="e.g. 28 (Normal &lt;35 mmHg)"></div>
                                    <div class="form-group">
                                        <label class="form-label" for="cardio-echo-file">Echo Report Upload (PDF/Image)</label>
                                        <input type="hidden" id="cardio-echo-document-id" name="cardio_echo_document_id">
                                        <input type="file" id="cardio-echo-file" class="form-control cardio-file-attach-input" data-target-field="cardio-echo-document-id" data-doc-label="Echo Report" accept=".pdf,.png,.jpg,.jpeg">
                                        <span id="cardio-echo-file-status" class="cardio-file-attach-status" style="display:block; font-size:0.78rem; color:var(--text-secondary); margin-top:4px;"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 6: Device Interrogation & Interventional Cath / PCI -->
                            <div data-workflow="palpitations" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="color:#0f172a;"><i class="fas fa-microchip" style="margin-right:6px;"></i> 6. Device Interrogation &amp; Cardiac Catheterization / PCI</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="cardio-device-type">Cardiac Implanted Device</label><select id="cardio-device-type" name="cardio_device_type" class="form-control"><option value="None">None</option><option value="Dual Chamber Pacemaker (PPM)">Dual Chamber Pacemaker (PPM)</option><option value="Implantable Cardioverter Defibrillator (ICD)">Implantable Cardioverter Defibrillator (ICD)</option><option value="Biventricular ICD (CRT-D)">Biventricular ICD (CRT-D)</option><option value="Implantable Loop Recorder (ILR)">Implantable Loop Recorder (ILR)</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-device-battery">Device Battery &amp; Lead Integrity</label><input type="text" id="cardio-device-battery" name="cardio_device_battery" class="form-control" placeholder="e.g. Battery: 8.2 yrs, Leads normal, 100% Atrial sensed"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-cath-access">Cath / Angiogram Access Site</label><select id="cardio-cath-access" name="cardio_cath_access" class="form-control"><option value="N/A - Not Performed">N/A - Not Performed</option><option value="Right Radial Artery (6 Fr)">Right Radial Artery (6 Fr)</option><option value="Left Radial Artery (6 Fr)">Left Radial Artery (6 Fr)</option><option value="Right Femoral Artery (6 Fr)">Right Femoral Artery (6 Fr)</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-cath-findings">Coronary Angiography Findings</label><input type="text" id="cardio-cath-findings" name="cardio_cath_findings" class="form-control" placeholder="e.g. 80% Mid-LAD stenosis, 50% Proximal RCA, Patent LCx"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-pci-stent">PCI / Stenting Documentation</label><input type="text" id="cardio-pci-stent" name="cardio_pci_stent" class="form-control" placeholder="e.g. Deployed 3.0 x 24mm Drug-Eluting Stent (DES) in Mid-LAD, 0% residual"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-rehab">Cardiac Rehab Phase II Referral</label><select id="cardio-rehab" name="cardio_rehab" class="form-control"><option value="Ordered - 36 Sessions Outpatient Cardiac Rehab">Ordered - 36 Sessions Outpatient Cardiac Rehab</option><option value="Completed / In Progress">Completed / In Progress</option><option value="Deferred / Not Indicated">Deferred / Not Indicated</option></select></div>
                                </div>
                            </div>

                            <!-- Card: Holter / Ambulatory Rhythm Monitoring (Palpitations workflow only) -->
                            <div data-workflow="palpitations" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="margin:0 0 10px 0; color:#0f172a;"><i class="fas fa-heart-pulse" style="margin-right:6px;"></i> Holter / Ambulatory Rhythm Monitor Report</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="cardio-holter-duration">Monitor Type / Duration</label><select id="cardio-holter-duration" name="cardio_holter_duration" class="form-control"><option value="">Select...</option><option value="24-Hour Holter">24-Hour Holter</option><option value="48-Hour Holter">48-Hour Holter</option><option value="7-Day Patch Monitor">7-Day Patch Monitor</option><option value="14-Day Patch Monitor">14-Day Patch Monitor</option><option value="30-Day Event Monitor">30-Day Event Monitor</option></select></div>
                                    <div class="form-group"><label class="form-label" for="cardio-holter-findings">Report Findings</label><textarea id="cardio-holter-findings" name="cardio_holter_findings" class="form-control" rows="2" placeholder="e.g. Sinus rhythm with rare PACs, 3 episodes of asymptomatic NSVT, no AFib captured"></textarea></div>
                                    <div class="form-group">
                                        <label class="form-label" for="cardio-holter-file">Holter Report Upload (PDF/Image)</label>
                                        <input type="hidden" id="cardio-holter-document-id" name="cardio_holter_document_id">
                                        <input type="file" id="cardio-holter-file" class="form-control cardio-file-attach-input" data-target-field="cardio-holter-document-id" data-doc-label="Holter Report" accept=".pdf,.png,.jpg,.jpeg">
                                        <span id="cardio-holter-file-status" class="cardio-file-attach-status" style="display:block; font-size:0.78rem; color:var(--text-secondary); margin-top:4px;"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card: Labs (Chest Pain / Dyspnea / Palpitations / Hypertension workflows) -->
                            <div data-workflow="chest_pain dyspnea palpitations hypertension" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="margin:0 0 10px 0; color:#0f172a;"><i class="fas fa-vial" style="margin-right:6px;"></i> Cardiac &amp; Metabolic Labs</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="cardio-labs-troponin">Troponin</label><input type="text" id="cardio-labs-troponin" name="cardio_labs_troponin" class="form-control" placeholder="e.g. &lt;0.01 ng/mL (Normal &lt;0.04)"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-labs-bnp">BNP / NT-proBNP</label><input type="text" id="cardio-labs-bnp" name="cardio_labs_bnp" class="form-control" placeholder="e.g. 450 pg/mL (BNP)"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-labs-lipid">Lipid Panel</label><input type="text" id="cardio-labs-lipid" name="cardio_labs_lipid_panel" class="form-control" placeholder="e.g. LDL 145, HDL 38, TG 210, Total Chol 220"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-labs-bmp">BMP / Electrolytes &amp; Renal Function</label><input type="text" id="cardio-labs-bmp" name="cardio_labs_bmp" class="form-control" placeholder="e.g. Na 138, K 4.2, Cr 1.1, eGFR 68"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-labs-hba1c">HbA1c</label><input type="text" id="cardio-labs-hba1c" name="cardio_labs_hba1c" class="form-control" placeholder="e.g. 6.8%"></div>
                                    <div class="form-group"><label class="form-label" for="cardio-labs-ddimer">D-Dimer</label><input type="text" id="cardio-labs-ddimer" name="cardio_labs_ddimer" class="form-control" placeholder="e.g. 0.3 µg/mL FEU (Normal &lt;0.5)"></div>
                                </div>
                                <div class="form-group" style="margin-top:10px; margin-bottom:0;"><label class="form-label" for="cardio-labs-other">Other Labs / Notes</label><textarea id="cardio-labs-other" name="cardio_labs_other" class="form-control" rows="2" placeholder="Any additional lab results or pending orders"></textarea></div>
                            </div>

                            <!-- Card: Medication / Lifestyle Plan (Hypertension workflow only) -->
                            <div data-workflow="hypertension" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="margin:0 0 10px 0; color:#92400e;"><i class="fas fa-pills" style="margin-right:6px;"></i> Medication &amp; Lifestyle Plan</h4>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label" for="cardio-htn-plan">Medication Titration / Lifestyle Recommendations</label>
                                    <textarea id="cardio-htn-plan" name="cardio_htn_plan" class="form-control" rows="3" placeholder="e.g. Increase Lisinopril to 20mg daily. Low-sodium (&lt;2g/day) diet counseling given. Recheck BP in 2 weeks. Home BP log requested."></textarea>
                                </div>
                            </div>

                            <!-- Card 7: Quick ICD-10 & Guideline-Directed Medical Therapy (GDMT) -->
                            <div data-workflow="chest_pain dyspnea palpitations hypertension followup" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <div class="form-group" style="margin-bottom: 12px;">
                                    <label class="form-label" style="font-weight: 700; color: #166534; font-size: 0.88rem;">
                                        <i class="fas fa-heartbeat" style="margin-right: 6px;"></i> Common Cardiology ICD-10 Quick Selection
                                    </label>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px;">
                                        <button type="button" class="btn btn-outline-primary btn-sm cardio-icd-btn" data-code="I25.10" data-desc="Atherosclerotic heart disease (CAD)" style="border-radius: 20px; font-size: 0.78rem; padding: 4px 12px; font-weight: 600; cursor: pointer; background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7;">
                                            + I25.10 (CAD)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm cardio-icd-btn" data-code="I50.9" data-desc="Heart failure, unspecified" style="border-radius: 20px; font-size: 0.78rem; padding: 4px 12px; font-weight: 600; cursor: pointer; background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7;">
                                            + I50.9 (Heart Failure)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm cardio-icd-btn" data-code="I48.91" data-desc="Unspecified atrial fibrillation" style="border-radius: 20px; font-size: 0.78rem; padding: 4px 12px; font-weight: 600; cursor: pointer; background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7;">
                                            + I48.91 (Atrial Fibrillation)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm cardio-icd-btn" data-code="I10" data-desc="Essential (primary) hypertension" style="border-radius: 20px; font-size: 0.78rem; padding: 4px 12px; font-weight: 600; cursor: pointer; background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7;">
                                            + I10 (Hypertension)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm cardio-icd-btn" data-code="I35.0" data-desc="Nonrheumatic aortic stenosis" style="border-radius: 20px; font-size: 0.78rem; padding: 4px 12px; font-weight: 600; cursor: pointer; background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7;">
                                            + I35.0 (Aortic Stenosis)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm cardio-icd-btn" data-code="I20.9" data-desc="Angina pectoris, unspecified" style="border-radius: 20px; font-size: 0.78rem; padding: 4px 12px; font-weight: 600; cursor: pointer; background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7;">
                                            + I20.9 (Angina)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm cardio-icd-btn" data-code="I42.0" data-desc="Dilated cardiomyopathy" style="border-radius: 20px; font-size: 0.78rem; padding: 4px 12px; font-weight: 600; cursor: pointer; background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7;">
                                            + I42.0 (Cardiomyopathy)
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label" for="cardio-assessment" style="font-weight:700; color:#166534;">Cardiology Comprehensive Assessment &amp; GDMT Plan</label>
                                    <textarea id="cardio-assessment" name="cardio_assessment" class="form-control" rows="3" placeholder="e.g. Plan: 1. Start Atorvastatin 40mg daily (High intensity). 2. Metoprolol Succinate 50mg daily. 3. Sublingual Nitroglycerin PRN. 4. Outpatient Coronary Angiogram scheduled. 5. Cardiac Rehab referral placed."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ===== 2. ORTHOPEDIC EHR PANEL ===== -->
                <div class="accordion-item specialty-only ortho-only hidden">
                    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-ortho" id="accordion-ortho-btn">
                        Orthopedic Assessment Suite
                    </button>
                    <div id="accordion-ortho" class="accordion-content hidden" role="region" aria-labelledby="accordion-ortho-btn">
                        <form id="ortho-form" novalidate class="mod-clinical-style-12">
                            <!-- Pathway Selector: drives which pathway-specific blocks below are shown (see toggleOrthoPathway() in app.js) -->
                            <div style="background: var(--bg-tertiary); border: 1px solid var(--primary-color); border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" for="ortho-pathway-select" style="font-weight: 700; color: var(--primary-color);">
                                        <i class="fas fa-route" style="margin-right: 6px;"></i> Orthopedic Clinical Pathway
                                    </label>
                                    <select id="ortho-pathway-select" name="ortho_pathway" class="form-control">
                                        <option value="joint_pain">Joint Pain</option>
                                        <option value="acute_injury">Acute Injury</option>
                                        <option value="back_pain">Back Pain</option>
                                        <option value="fracture_followup">Fracture Follow-up</option>
                                        <option value="post_op">Post-op</option>
                                    </select>
                                </div>
                            </div>

                            <h4 class="form-section-title">Chief Complaint &amp; Pain Assessment</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="ortho-chief-complaint">Chief Complaint</label><input type="text" id="ortho-chief-complaint" name="ortho_chief_complaint" class="form-control" placeholder="e.g. Right knee pain"></div>
                                <div class="form-group"><label class="form-label" for="ortho-pain-score">Pain Score (0-10)</label><select id="ortho-pain-score" name="ortho_pain_score" class="form-control"><option value="">Select...</option><option>0 - None</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option><option>10 - Worst</option></select></div>
                                <div class="form-group"><label class="form-label" for="ortho-pain-location">Pain Location</label><input type="text" id="ortho-pain-location" name="ortho_pain_location" class="form-control" placeholder="e.g. Right knee medial compartment"></div>
                                <div class="form-group"><label class="form-label" for="ortho-pain-character">Pain Character</label><select id="ortho-pain-character" name="ortho_pain_character" class="form-control"><option value="">Select...</option><option>Sharp</option><option>Dull</option><option>Aching</option><option>Burning</option><option>Throbbing</option><option>Stabbing</option></select></div>
                                <div class="form-group"><label class="form-label" for="ortho-onset">Onset</label><select id="ortho-onset" name="ortho_onset" class="form-control"><option value="">Select...</option><option>Acute (Traumatic)</option><option>Gradual (Overuse)</option><option>Chronic</option></select></div>
                                <div class="form-group"><label class="form-label" for="ortho-duration">Duration of Symptoms</label><input type="text" id="ortho-duration" name="ortho_duration" class="form-control" placeholder="e.g. 3 months"></div>
                            </div>

                            <!-- ===== JOINT PAIN pathway ===== -->
                            <div data-ortho-pathway="joint_pain" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="margin:0 0 10px 0;"><i class="fas fa-bone" style="margin-right:6px;"></i> Joint Pain Assessment</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="ortho-jp-joint">Joint</label><select id="ortho-jp-joint" name="ortho_jp_joint" class="form-control"><option value="">Select...</option><option value="shoulder">Shoulder</option><option value="elbow">Elbow</option><option value="wrist">Wrist</option><option>Hand</option><option value="hip">Hip</option><option value="knee">Knee</option><option value="ankle">Ankle</option><option>Foot</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-jp-laterality">Laterality</label><select id="ortho-jp-laterality" name="ortho_jp_laterality" class="form-control"><option value="">Select...</option><option>Right</option><option>Left</option><option>Bilateral</option><option>Midline</option><option>Not Applicable</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-jp-pattern">Pain Pattern</label><select id="ortho-jp-pattern" name="ortho_jp_pattern" class="form-control"><option value="">Select...</option><option>Constant</option><option>Intermittent</option><option>Activity-related</option><option>Night Pain</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-jp-aggravating">Aggravating Factors</label><input type="text" id="ortho-jp-aggravating" name="ortho_jp_aggravating" class="form-control" placeholder="e.g. Stairs, prolonged standing"></div>
                                    <div class="form-group"><label class="form-label" for="ortho-jp-relieving">Relieving Factors</label><input type="text" id="ortho-jp-relieving" name="ortho_jp_relieving" class="form-control" placeholder="e.g. Rest, ice, NSAIDs"></div>
                                    <div class="form-group"><label class="form-label" for="ortho-jp-morning-stiffness">Morning Stiffness</label><select id="ortho-jp-morning-stiffness" name="ortho_jp_morning_stiffness" class="form-control"><option value="">Select...</option><option>None</option><option>&lt;30 min</option><option>&gt;30 min</option></select></div>
                                </div>
                                <div class="mod-clinical-style-33">
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" id="ortho-jp-locking" name="ortho_jp_locking" value="1"> Locking</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" id="ortho-jp-catching" name="ortho_jp_catching" value="1"> Catching</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" id="ortho-jp-clicking" name="ortho_jp_clicking" value="1"> Clicking</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" id="ortho-jp-instability-sym" name="ortho_jp_instability_sym" value="1"> Instability</label>
                                </div>

                                <!-- Joint-specific special tests (see toggleOrthoJoint() in app.js) -->
                                <div data-ortho-joint="knee" style="margin-top:12px; padding-top:12px; border-top:1px dashed #cbd5e1;">
                                    <h4 class="form-section-title" style="margin:0 0 8px 0; font-size:0.95rem;">Knee Special Tests</h4>
                                    <div class="mod-clinical-style-33">
                                        <div class="form-group"><label class="form-label" for="ortho-knee-effusion">Effusion</label><select id="ortho-knee-effusion" name="ortho_knee_effusion" class="form-control"><option value="">Select...</option><option>None</option><option>Mild</option><option>Moderate</option><option>Large</option></select></div>
                                        <div class="form-group"><label class="form-label" for="ortho-knee-lachman">Lachman Test</label><select id="ortho-knee-lachman" name="ortho_knee_lachman" class="form-control"><option value="">Select...</option><option>Negative</option><option>Positive (1+)</option><option>Positive (2+)</option><option>Positive (3+)</option></select></div>
                                        <div class="form-group"><label class="form-label" for="ortho-knee-mcmurray">McMurray Test</label><select id="ortho-knee-mcmurray" name="ortho_knee_mcmurray" class="form-control"><option value="">Select...</option><option>Negative</option><option>Positive Medial</option><option>Positive Lateral</option></select></div>
                                    </div>
                                </div>
                                <div data-ortho-joint="shoulder" style="margin-top:12px; padding-top:12px; border-top:1px dashed #cbd5e1;">
                                    <h4 class="form-section-title" style="margin:0 0 8px 0; font-size:0.95rem;">Shoulder Special Tests</h4>
                                    <div class="mod-clinical-style-33">
                                        <div class="form-group"><label class="form-label" for="ortho-shoulder-rotator-cuff">Rotator Cuff Test</label><select id="ortho-shoulder-rotator-cuff" name="ortho_shoulder_rotator_cuff" class="form-control"><option value="">Select...</option><option>Negative</option><option>Positive - Supraspinatus</option><option>Positive - Infraspinatus</option><option>Positive - Subscapularis</option></select></div>
                                        <div class="form-group"><label class="form-label" for="ortho-shoulder-impingement">Impingement Test</label><select id="ortho-shoulder-impingement" name="ortho_shoulder_impingement" class="form-control"><option value="">Select...</option><option>Negative</option><option>Positive Neer</option><option>Positive Hawkins-Kennedy</option></select></div>
                                    </div>
                                </div>
                                <div data-ortho-joint="hip" style="margin-top:12px; padding-top:12px; border-top:1px dashed #cbd5e1;">
                                    <h4 class="form-section-title" style="margin:0 0 8px 0; font-size:0.95rem;">Hip Special Tests</h4>
                                    <div class="mod-clinical-style-33">
                                        <div class="form-group"><label class="form-label" for="ortho-hip-provocative">Provocative Test</label><select id="ortho-hip-provocative" name="ortho_hip_provocative" class="form-control"><option value="">Select...</option><option>Negative</option><option>Positive FADIR</option><option>Positive FABER</option></select></div>
                                        <div class="form-group"><label class="form-label" for="ortho-hip-gait-note">Gait Note</label><input type="text" id="ortho-hip-gait-note" name="ortho_hip_gait_note" class="form-control" placeholder="e.g. Trendelenburg on right"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== ACUTE INJURY pathway ===== -->
                            <div data-ortho-pathway="acute_injury" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="margin:0 0 10px 0;"><i class="fas fa-truck-medical" style="margin-right:6px;"></i> Acute Injury</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="ortho-ai-date">Injury Date</label><input type="date" id="ortho-ai-date" name="ortho_ai_date" class="form-control"></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ai-time">Injury Time</label><input type="time" id="ortho-ai-time" name="ortho_ai_time" class="form-control"></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ai-mechanism">Mechanism</label><select id="ortho-ai-mechanism" name="ortho_ai_mechanism" class="form-control"><option value="">Select...</option><option>Fall</option><option>Sports</option><option>Motor Vehicle</option><option>Work-related</option><option>Direct Blow</option><option>Twisting</option><option>Overuse</option><option>Other</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ai-activity">Activity at Injury</label><input type="text" id="ortho-ai-activity" name="ortho_ai_activity" class="form-control" placeholder="e.g. Playing basketball"></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ai-location">Body Part</label><input type="text" id="ortho-ai-location" name="ortho_ai_location" class="form-control" placeholder="e.g. Left ankle"></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ai-laterality">Laterality</label><select id="ortho-ai-laterality" name="ortho_ai_laterality" class="form-control"><option value="">Select...</option><option>Right</option><option>Left</option><option>Bilateral</option></select></div>
                                </div>
                                <div class="mod-clinical-style-33">
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" id="ortho-ai-deformity" name="ortho_ai_deformity" value="1"> Deformity</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" id="ortho-ai-unable-bear-weight" name="ortho_ai_unable_bear_weight" value="1"> Unable to Bear Weight</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" id="ortho-ai-numbness" name="ortho_ai_numbness" value="1"> Numbness</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" id="ortho-ai-tingling" name="ortho_ai_tingling" value="1"> Tingling</label>
                                </div>
                                <h4 class="form-section-title" style="margin:10px 0 8px 0; font-size:0.95rem;">Neurovascular Examination</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="ortho-ai-nv-sensation">Sensation</label><select id="ortho-ai-nv-sensation" name="ortho_ai_nv_sensation" class="form-control"><option value="">Select...</option><option>Intact</option><option>Diminished</option><option>Absent</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ai-nv-motor">Motor Function</label><select id="ortho-ai-nv-motor" name="ortho_ai_nv_motor" class="form-control"><option value="">Select...</option><option>Intact</option><option>Weak</option><option>Absent</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ai-nv-pulse">Pulse</label><select id="ortho-ai-nv-pulse" name="ortho_ai_nv_pulse" class="form-control"><option value="">Select...</option><option>Palpable</option><option>Diminished</option><option>Absent</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ai-nv-cap-refill">Capillary Refill</label><select id="ortho-ai-nv-cap-refill" name="ortho_ai_nv_cap_refill" class="form-control"><option value="">Select...</option><option>&lt;2 sec</option><option>&gt;2 sec</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ai-nv-skin">Skin Temp / Color</label><input type="text" id="ortho-ai-nv-skin" name="ortho_ai_nv_skin" class="form-control" placeholder="e.g. Warm, pink"></div>
                                </div>
                                <div class="form-group"><label class="form-label" for="ortho-ai-imaging">Imaging Type</label><select id="ortho-ai-imaging" name="ortho_ai_imaging" class="form-control"><option value="">Select...</option><option>X-Ray</option><option>CT</option><option>MRI</option><option>Ultrasound</option><option>Other</option><option>None Ordered</option></select></div>
                            </div>

                            <!-- ===== BACK PAIN pathway ===== -->
                            <div data-ortho-pathway="back_pain" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="margin:0 0 10px 0;"><i class="fas fa-person" style="margin-right:6px;"></i> Back Pain Assessment</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="ortho-bp-location">Spine Location</label><select id="ortho-bp-location" name="ortho_bp_location" class="form-control"><option value="">Select...</option><option>Cervical</option><option>Thoracic</option><option>Lumbar</option><option>Sacral</option><option>Multiple</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-bp-radiation">Radiation</label><select id="ortho-bp-radiation" name="ortho_bp_radiation" class="form-control"><option value="">Select...</option><option>None</option><option>Buttock</option><option>Hip</option><option>Thigh</option><option>Leg</option><option>Foot</option><option>Other</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-bp-character">Character</label><select id="ortho-bp-character" name="ortho_bp_character" class="form-control"><option value="">Select...</option><option>Mechanical</option><option>Constant</option><option>Intermittent</option><option>Rest Pain</option><option>Night Pain</option></select></div>
                                </div>
                                <div class="mod-clinical-style-33">
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-bp-neuro-cb" id="ortho-bp-numbness" name="ortho_bp_numbness" value="1"> Numbness</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-bp-neuro-cb" id="ortho-bp-tingling" name="ortho_bp_tingling" value="1"> Tingling</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-bp-neuro-cb" id="ortho-bp-weakness" name="ortho_bp_weakness" value="1"> Weakness</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-bp-neuro-cb" id="ortho-bp-gait-changes" name="ortho_bp_gait_changes" value="1"> Gait Changes</label>
                                </div>

                                <h4 class="form-section-title" style="margin:14px 0 8px 0; color:#b91c1c;"><i class="fas fa-triangle-exclamation" style="margin-right:6px;"></i> Red-Flag Screening</h4>
                                <div class="mod-clinical-style-33">
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-redflag-cb" id="ortho-rf-trauma" name="ortho_rf_trauma" value="1"> Trauma</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-redflag-cb" id="ortho-rf-fever" name="ortho_rf_fever" value="1"> Fever / Infection Risk</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-redflag-cb" id="ortho-rf-cancer" name="ortho_rf_cancer" value="1"> Cancer History</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-redflag-cb" id="ortho-rf-weight-loss" name="ortho_rf_weight_loss" value="1"> Unexplained Weight Loss</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-redflag-cb" id="ortho-rf-immunosuppression" name="ortho_rf_immunosuppression" value="1"> Immunosuppression</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-redflag-cb" id="ortho-rf-osteoporosis" name="ortho_rf_osteoporosis" value="1"> Osteoporosis / Fracture Risk</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-redflag-cb" id="ortho-rf-neuro-deficit" name="ortho_rf_neuro_deficit" value="1"> Progressive Neurologic Deficit</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-redflag-cb" id="ortho-rf-bowel-bladder" name="ortho_rf_bowel_bladder" value="1"> Bowel / Bladder Symptoms</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" class="ortho-redflag-cb" id="ortho-rf-saddle" name="ortho_rf_saddle" value="1"> Saddle Sensory Changes</label>
                                </div>
                                <div id="ortho-redflag-banner" class="hidden" style="margin-top:10px; background:#fef2f2; border:1px solid #fca5a5; border-radius:6px; padding:10px 14px; color:#991b1b; font-weight:600; font-size:0.88rem;">
                                    <i class="fas fa-circle-exclamation" style="margin-right:6px;"></i> Red flag(s) present — consider appropriate imaging per clinical judgment. This is an informational prompt only; no order is placed automatically.
                                </div>
                            </div>

                            <!-- ===== FRACTURE FOLLOW-UP pathway ===== -->
                            <div data-ortho-pathway="fracture_followup" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="margin:0 0 10px 0;"><i class="fas fa-bandage" style="margin-right:6px;"></i> Fracture Follow-up</h4>
                                <div class="form-group">
                                    <label class="form-label" for="ortho-fracture-ref">Linked Fracture Diagnosis</label>
                                    <select id="ortho-fracture-ref" name="ortho_fracture_ref_id" class="form-control"><option value="">-- Loading patient diagnoses --</option></select>
                                </div>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="ortho-ff-device">Immobilization Device</label><select id="ortho-ff-device" name="ortho_ff_device" class="form-control"><option value="">Select...</option><option>Cast</option><option>Splint</option><option>Brace</option><option>Boot</option><option>Sling</option><option>None</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ff-applied-date">Device Applied Date</label><input type="date" id="ortho-ff-applied-date" name="ortho_ff_applied_date" class="form-control"></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ff-device-condition">Device Condition</label><select id="ortho-ff-device-condition" name="ortho_ff_device_condition" class="form-control"><option value="">Select...</option><option>Intact</option><option>Loose</option><option>Damaged</option><option>Removed</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ff-union">Union Status</label><select id="ortho-ff-union" name="ortho_ff_union" class="form-control"><option value="">Select...</option><option>Healing as Expected</option><option>Delayed Union</option><option>Nonunion</option><option>Malunion</option><option>United</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ff-alignment">Alignment</label><select id="ortho-ff-alignment" name="ortho_ff_alignment" class="form-control"><option value="">Select...</option><option>Anatomic</option><option>Acceptable</option><option>Unacceptable</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-ff-weightbearing">Weight-bearing Status</label><select id="ortho-ff-weightbearing" name="ortho_ff_weightbearing" class="form-control"><option value="">Select...</option><option>Non-weight-bearing</option><option>Touch-down Weight-bearing</option><option>Partial Weight-bearing</option><option>Weight-bearing as Tolerated</option><option>Full Weight-bearing</option></select></div>
                                </div>
                                <div class="mod-clinical-style-33">
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" id="ortho-ff-pt" name="ortho_ff_pt" value="1"> Physical Therapy Ordered</label>
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" id="ortho-ff-home-exercise" name="ortho_ff_home_exercise" value="1"> Home Exercise Program</label>
                                </div>
                            </div>

                            <!-- ===== POST-OP pathway ===== -->
                            <div data-ortho-pathway="post_op" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                                <h4 class="form-section-title" style="margin:0 0 10px 0;"><i class="fas fa-user-doctor" style="margin-right:6px;"></i> Post-op Assessment</h4>
                                <div class="form-group">
                                    <label class="form-label" for="ortho-procedure-ref">Linked Procedure</label>
                                    <select id="ortho-procedure-ref" name="ortho_procedure_ref_id" class="form-control"><option value="">-- Loading patient procedures --</option></select>
                                </div>
                                <h4 class="form-section-title" style="margin:10px 0 8px 0; font-size:0.95rem;">Wound Assessment</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="ortho-po-wound">Wound Status</label><select id="ortho-po-wound" name="ortho_po_wound" class="form-control"><option value="">Select...</option><option>Clean / Dry / Intact</option><option>Redness</option><option>Drainage</option><option>Dehiscence</option><option>Signs of Infection</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-po-closure">Sutures / Staples</label><select id="ortho-po-closure" name="ortho_po_closure" class="form-control"><option value="">Select...</option><option>In Place</option><option>Removed</option><option>N/A - Dissolvable</option></select></div>
                                </div>
                                <h4 class="form-section-title" style="margin:10px 0 8px 0; font-size:0.95rem;">Pain &amp; Function</h4>
                                <div class="mod-clinical-style-33">
                                    <div class="form-group"><label class="form-label" for="ortho-po-pain-score">Pain Score (0-10)</label><select id="ortho-po-pain-score" name="ortho_po_pain_score" class="form-control"><option value="">Select...</option><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option><option>10</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-po-med-effectiveness">Medication Effectiveness</label><select id="ortho-po-med-effectiveness" name="ortho_po_med_effectiveness" class="form-control"><option value="">Select...</option><option>Well Controlled</option><option>Partially Controlled</option><option>Poorly Controlled</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-po-weightbearing">Weight-bearing Status</label><select id="ortho-po-weightbearing" name="ortho_po_weightbearing" class="form-control"><option value="">Select...</option><option>Non-weight-bearing</option><option>Partial Weight-bearing</option><option>Weight-bearing as Tolerated</option><option>Full Weight-bearing</option></select></div>
                                    <div class="form-group"><label class="form-label" for="ortho-po-gait">Gait / Mobility</label><input type="text" id="ortho-po-gait" name="ortho_po_gait" class="form-control" placeholder="e.g. Ambulating with walker"></div>
                                </div>
                                <div class="mod-clinical-style-33">
                                    <label style="display:flex; align-items:center; gap:6px; font-weight:600;"><input type="checkbox" id="ortho-po-pt-ordered" name="ortho_po_pt_ordered" value="1"> PT Ordered</label>
                                    <div class="form-group"><label class="form-label" for="ortho-po-pt-frequency">PT Frequency</label><input type="text" id="ortho-po-pt-frequency" name="ortho_po_pt_frequency" class="form-control" placeholder="e.g. 3x/week"></div>
                                    <div class="form-group"><label class="form-label" for="ortho-po-rom-goals">ROM / Strength Goals</label><input type="text" id="ortho-po-rom-goals" name="ortho_po_rom_goals" class="form-control" placeholder="e.g. 90° flexion by week 6"></div>
                                </div>
                            </div>

                            <h4 class="form-section-title">Shared Orthopedic Exam</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="ortho-rom-affected">Affected Joint / Region</label><input type="text" id="ortho-rom-affected" name="ortho_rom_affected" class="form-control" placeholder="e.g. Right knee"></div>
                                <div class="form-group"><label class="form-label" for="ortho-rom-flexion">Flexion (°)</label><input type="number" id="ortho-rom-flexion" name="ortho_rom_flexion" class="form-control" placeholder="120"></div>
                                <div class="form-group"><label class="form-label" for="ortho-rom-extension">Extension (°)</label><input type="number" id="ortho-rom-extension" name="ortho_rom_extension" class="form-control" placeholder="0"></div>
                                <div class="form-group"><label class="form-label" for="ortho-rom-abduction">Abduction (°)</label><input type="number" id="ortho-rom-abduction" name="ortho_rom_abduction" class="form-control"></div>
                                <div class="form-group"><label class="form-label" for="ortho-rom-notes">ROM Notes</label><input type="text" id="ortho-rom-notes" name="ortho_rom_notes" class="form-control" placeholder="e.g. Limited, painful at end range"></div>
                            </div>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="ortho-swelling">Swelling / Edema</label><select id="ortho-swelling" name="ortho_swelling" class="form-control"><option value="">Select...</option><option>None</option><option>Mild</option><option>Moderate</option><option>Severe</option></select></div>
                                <div class="form-group"><label class="form-label" for="ortho-tenderness">Point Tenderness</label><input type="text" id="ortho-tenderness" name="ortho_tenderness" class="form-control" placeholder="Location of tenderness"></div>
                                <div class="form-group"><label class="form-label" for="ortho-instability">Joint Instability</label><select id="ortho-instability" name="ortho_instability" class="form-control"><option value="">Select...</option><option>None</option><option>Mild</option><option>Moderate</option><option>Severe</option></select></div>
                                <div class="form-group"><label class="form-label" for="ortho-crepitus">Crepitus</label><select id="ortho-crepitus" name="ortho_crepitus" class="form-control"><option value="">Select...</option><option>Present</option><option>Absent</option></select></div>
                                <div class="form-group"><label class="form-label" for="ortho-muscle-strength">Muscle Strength (0-5)</label><select id="ortho-muscle-strength" name="ortho_muscle_strength" class="form-control"><option value="">Select...</option><option>0 - No contraction</option><option>1 - Trace</option><option>2 - Active movement gravity eliminated</option><option>3 - Active movement against gravity</option><option>4 - Active movement against resistance</option><option>5 - Normal strength</option></select></div>
                                <div class="form-group"><label class="form-label" for="ortho-gait">Gait Assessment</label><select id="ortho-gait" name="ortho_gait" class="form-control"><option value="">Select...</option><option>Normal</option><option>Antalgic</option><option>Trendelenburg</option><option>Waddling</option><option>Steppage</option></select></div>
                            </div>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="ortho-disc">Disc Pathology</label><select id="ortho-disc" name="ortho_disc" class="form-control"><option value="">Select...</option><option>None</option><option>Bulge</option><option>Herniation</option><option>Degenerative Disc Disease</option></select></div>
                                <div class="form-group"><label class="form-label" for="ortho-radiculopathy">Radiculopathy</label><select id="ortho-radiculopathy" name="ortho_radiculopathy" class="form-control"><option value="">Select...</option><option>None</option><option>Cervical</option><option>Lumbar (Sciatica)</option></select></div>
                                <div class="form-group"><label class="form-label" for="ortho-slr">SLR Test</label><select id="ortho-slr" name="ortho_slr" class="form-control"><option value="">Select...</option><option>Negative</option><option>Positive Left</option><option>Positive Right</option><option>Positive Bilateral</option></select></div>
                            </div>
                            <h4 class="form-section-title">Imaging &amp; Diagnostics</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="ortho-imaging-type">Imaging Ordered / Reviewed</label><select id="ortho-imaging-type" name="ortho_imaging_type" class="form-control"><option value="">Select...</option><option>X-Ray</option><option>MRI</option><option>CT Scan</option><option>Ultrasound</option><option>Bone Scan</option><option>None</option></select></div>
                                <div class="form-group"><label class="form-label" for="ortho-imaging-findings">Imaging Findings</label><textarea id="ortho-imaging-findings" name="ortho_imaging_findings" class="form-control" rows="2"></textarea></div>
                            </div>
                            <h4 class="form-section-title">Treatment Plan</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="ortho-treatment">Treatment Modality</label><select id="ortho-treatment" name="ortho_treatment" class="form-control"><option value="">Select...</option><option>Conservative (PT/OT)</option><option>Bracing / Splinting</option><option>Injection Therapy</option><option>Surgical Consultation</option><option>Observation</option></select></div>
                                <div class="form-group"><label class="form-label" for="ortho-surgery">Surgical Plan</label><input type="text" id="ortho-surgery" name="ortho_surgery" class="form-control" placeholder="e.g. Total knee arthroplasty"></div>
                                <div class="form-group"><label class="form-label" for="ortho-referral">Referral</label><input type="text" id="ortho-referral" name="ortho_referral" class="form-control" placeholder="e.g. Physical Therapy, Spine Surgery"></div>
                                <div class="form-group"><label class="form-label" for="ortho-followup">Follow-Up Plan</label><input type="text" id="ortho-followup" name="ortho_followup" class="form-control" placeholder="e.g. 4 weeks post-injection"></div>
                            </div>
                            <div class="form-group"><label class="form-label" for="ortho-assessment">Assessment &amp; Plan Summary</label><textarea id="ortho-assessment" name="ortho_assessment" class="form-control" rows="3"></textarea></div>
                        </form>
                    </div>
                </div>

                <!-- ===== 3. DERMATOLOGY EHR PANEL ===== -->
                <div class="accordion-item specialty-only derma-only hidden">
                    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-derma" id="accordion-derma-btn">
                        Dermatology Assessment Suite
                    </button>
                    <div id="accordion-derma" class="accordion-content hidden" role="region" aria-labelledby="accordion-derma-btn">
                        <form id="derma-form" novalidate class="mod-clinical-style-12">
                            <h4 class="form-section-title">Presenting Skin Complaint</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="derma-complaint">Primary Complaint</label><input type="text" id="derma-complaint" name="derma_complaint" class="form-control" placeholder="e.g. Pruritic erythematous rash bilateral forearms"></div>
                                <div class="form-group"><label class="form-label" for="derma-onset">Onset</label><input type="text" id="derma-onset" name="derma_onset" class="form-control" placeholder="e.g. 2 weeks ago, acute"></div>
                                <div class="form-group"><label class="form-label" for="derma-progression">Progression</label><select id="derma-progression" name="derma_progression" class="form-control"><option value="">Select...</option><option>Improving</option><option>Stable</option><option>Worsening</option><option>Intermittent</option></select></div>
                                <div class="form-group"><label class="form-label" for="derma-triggers">Known Triggers</label><input type="text" id="derma-triggers" name="derma_triggers" class="form-control" placeholder="e.g. sunlight, soaps, stress"></div>
                                <div class="form-group"><label class="form-label" for="derma-pruritus">Pruritus (Itching)</label><select id="derma-pruritus" name="derma_pruritus" class="form-control"><option value="">Select...</option><option>None</option><option>Mild</option><option>Moderate</option><option>Severe</option></select></div>
                                <div class="form-group"><label class="form-label" for="derma-prior-treatment">Prior Treatment</label><input type="text" id="derma-prior-treatment" name="derma_prior_treatment" class="form-control" placeholder="e.g. OTC hydrocortisone cream"></div>
                            </div>
                            <h4 class="form-section-title">Lesion / Rash Characteristics</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="derma-lesion-type">Primary Lesion Type</label><select id="derma-lesion-type" name="derma_lesion_type" class="form-control"><option value="">Select...</option><option>Macule</option><option>Patch</option><option>Papule</option><option>Plaque</option><option>Vesicle</option><option>Bulla</option><option>Pustule</option><option>Nodule</option><option>Cyst</option><option>Wheal</option><option>Erosion</option><option>Ulcer</option><option>Scale</option><option>Crust</option></select></div>
                                <div class="form-group"><label class="form-label" for="derma-distribution">Distribution Pattern</label><select id="derma-distribution" name="derma_distribution" class="form-control"><option value="">Select...</option><option>Localized</option><option>Generalized</option><option>Symmetric</option><option>Dermatomal</option><option>Sun-exposed areas</option><option>Flexural</option><option>Extensor surfaces</option></select></div>
                                <div class="form-group"><label class="form-label" for="derma-color">Lesion Color</label><input type="text" id="derma-color" name="derma_color" class="form-control" placeholder="e.g. Erythematous, violaceous, hypopigmented"></div>
                                <div class="form-group"><label class="form-label" for="derma-size">Lesion Size</label><input type="text" id="derma-size" name="derma_size" class="form-control" placeholder="e.g. 0.5 cm x 0.5 cm"></div>
                                <div class="form-group"><label class="form-label" for="derma-border">Border</label><select id="derma-border" name="derma_border" class="form-control"><option value="">Select...</option><option>Well-defined</option><option>Ill-defined</option><option>Irregular</option></select></div>
                                <div class="form-group"><label class="form-label" for="derma-surface">Surface</label><select id="derma-surface" name="derma_surface" class="form-control"><option value="">Select...</option><option>Smooth</option><option>Rough/Scaly</option><option>Crusted</option><option>Verrucous</option></select></div>
                            </div>
                            <h4 class="form-section-title">Body Mapping &amp; Affected Areas</h4>
                            <div class="form-group"><label class="form-label" for="derma-body-map">Affected Body Regions</label><textarea id="derma-body-map" name="derma_body_map" class="form-control" rows="2" placeholder="Describe affected areas: e.g. bilateral forearms, dorsal hands, forehead"></textarea></div>
                            <h4 class="form-section-title">Biopsy &amp; Pathology</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="derma-biopsy-done">Biopsy Performed</label><select id="derma-biopsy-done" name="derma_biopsy_done" class="form-control"><option value="">Select...</option><option>Yes</option><option>No</option><option>Planned</option></select></div>
                                <div class="form-group"><label class="form-label" for="derma-biopsy-type">Biopsy Type</label><select id="derma-biopsy-type" name="derma_biopsy_type" class="form-control"><option value="">Select...</option><option>Punch</option><option>Shave</option><option>Excisional</option><option>Incisional</option><option>N/A</option></select></div>
                                <div class="form-group"><label class="form-label" for="derma-biopsy-site">Biopsy Site</label><input type="text" id="derma-biopsy-site" name="derma_biopsy_site" class="form-control" placeholder="e.g. Right forearm lesion"></div>
                                <div class="form-group"><label class="form-label" for="derma-pathology">Pathology Result</label><textarea id="derma-pathology" name="derma_pathology" class="form-control" rows="2" placeholder="Pathology findings or pending"></textarea></div>
                            </div>
                            <h4 class="form-section-title">Treatment Plan</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="derma-topical">Topical Regimen</label><input type="text" id="derma-topical" name="derma_topical" class="form-control" placeholder="e.g. Triamcinolone 0.1% cream BID x 2 weeks"></div>
                                <div class="form-group"><label class="form-label" for="derma-systemic">Systemic Medication</label><input type="text" id="derma-systemic" name="derma_systemic" class="form-control" placeholder="e.g. Prednisone 40mg x 5 days"></div>
                                <div class="form-group"><label class="form-label" for="derma-procedure">In-Office Procedure</label><select id="derma-procedure" name="derma_procedure" class="form-control"><option value="">Select...</option><option>None</option><option>Cryotherapy</option><option>Electrocautery</option><option>Laser Therapy</option><option>Chemical Peel</option><option>Phototherapy</option></select></div>
                                <div class="form-group"><label class="form-label" for="derma-followup">Follow-Up Plan</label><input type="text" id="derma-followup" name="derma_followup" class="form-control" placeholder="e.g. 4 weeks to assess treatment response"></div>
                            </div>
                            <div class="form-group"><label class="form-label" for="derma-assessment">Assessment &amp; Plan Summary</label><textarea id="derma-assessment" name="derma_assessment" class="form-control" rows="3"></textarea></div>
                        </form>
                    </div>
                </div>

                <!-- ===== 4. NEUROLOGY EHR PANEL ===== -->
                <div class="accordion-item specialty-only neuro-only hidden">
                    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-neuro" id="accordion-neuro-btn">
                        Neurology Assessment Suite
                    </button>
                    <div id="accordion-neuro" class="accordion-content hidden" role="region" aria-labelledby="accordion-neuro-btn">
                        <form id="neuro-form" novalidate class="mod-clinical-style-12">
                            <h4 class="form-section-title">Neurological Chief Complaint</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="neuro-complaint">Chief Complaint</label><input type="text" id="neuro-complaint" name="neuro_complaint" class="form-control" placeholder="e.g. New onset seizures, progressive weakness"></div>
                                <div class="form-group"><label class="form-label" for="neuro-onset">Onset</label><select id="neuro-onset" name="neuro_onset" class="form-control"><option value="">Select...</option><option>Sudden</option><option>Subacute</option><option>Gradual/Progressive</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-duration">Duration</label><input type="text" id="neuro-duration" name="neuro_duration" class="form-control" placeholder="e.g. 3 months, episodic"></div>
                            </div>
                            <h4 class="form-section-title">Mental Status Examination</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="neuro-orientation">Orientation</label><select id="neuro-orientation" name="neuro_orientation" class="form-control"><option value="">Select...</option><option>Oriented x4 (Person, Place, Time, Event)</option><option>Oriented x3</option><option>Oriented x2</option><option>Oriented x1</option><option>Disoriented</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-cognition">Cognition / Memory</label><select id="neuro-cognition" name="neuro_cognition" class="form-control"><option value="">Select...</option><option>Intact</option><option>Mildly Impaired</option><option>Moderately Impaired</option><option>Severely Impaired</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-speech">Speech</label><select id="neuro-speech" name="neuro_speech" class="form-control"><option value="">Select...</option><option>Normal</option><option>Dysarthria</option><option>Aphasia</option><option>Dysphasia</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-mood">Mood &amp; Affect</label><select id="neuro-mood" name="neuro_mood" class="form-control"><option value="">Select...</option><option>Appropriate</option><option>Anxious</option><option>Depressed</option><option>Flat</option><option>Labile</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-mmse">MMSE Score (0-30)</label><input type="number" id="neuro-mmse" name="neuro_mmse" class="form-control" min="0" max="30" placeholder="30 = Normal"></div>
                            </div>
                            <h4 class="form-section-title">Cranial Nerve Examination (CN I - XII)</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="neuro-cn1">CN I - Olfactory</label><select id="neuro-cn1" name="neuro_cn1" class="form-control"><option value="">Select...</option><option>Intact</option><option>Impaired</option><option>Not tested</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-cn2">CN II - Optic (Vision)</label><select id="neuro-cn2" name="neuro_cn2" class="form-control"><option value="">Select...</option><option>Intact</option><option>Impaired</option><option>Not tested</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-cn345">CN III/IV/VI - Oculomotor, Trochlear, Abducens</label><select id="neuro-cn345" name="neuro_cn345" class="form-control"><option value="">Select...</option><option>EOM intact, no nystagmus</option><option>Nystagmus present</option><option>Ptosis</option><option>Diplopia</option><option>Impaired</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-cn5">CN V - Trigeminal (Facial sensation)</label><select id="neuro-cn5" name="neuro_cn5" class="form-control"><option value="">Select...</option><option>Intact</option><option>Impaired</option><option>Not tested</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-cn7">CN VII - Facial (Facial movement)</label><select id="neuro-cn7" name="neuro_cn7" class="form-control"><option value="">Select...</option><option>Symmetric</option><option>Left facial droop</option><option>Right facial droop</option><option>Bilateral weakness</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-cn8">CN VIII - Vestibulocochlear (Hearing)</label><select id="neuro-cn8" name="neuro_cn8" class="form-control"><option value="">Select...</option><option>Intact bilaterally</option><option>Left hearing loss</option><option>Right hearing loss</option><option>Bilateral hearing loss</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-cn9-10">CN IX/X - Glossopharyngeal/Vagus</label><select id="neuro-cn9-10" name="neuro_cn9_10" class="form-control"><option value="">Select...</option><option>Intact - gag reflex present</option><option>Impaired gag reflex</option><option>Dysphagia present</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-cn11">CN XI - Accessory (Neck/Shoulder strength)</label><select id="neuro-cn11" name="neuro_cn11" class="form-control"><option value="">Select...</option><option>Intact</option><option>Impaired</option><option>Not tested</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-cn12">CN XII - Hypoglossal (Tongue)</label><select id="neuro-cn12" name="neuro_cn12" class="form-control"><option value="">Select...</option><option>Midline, intact</option><option>Deviation left</option><option>Deviation right</option><option>Atrophy</option></select></div>
                            </div>
                            <h4 class="form-section-title">Motor &amp; Sensory Examination</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="neuro-motor-ue">Motor - Upper Extremities</label><select id="neuro-motor-ue" name="neuro_motor_ue" class="form-control"><option value="">Select...</option><option>5/5 bilaterally (Normal)</option><option>Weakness left upper extremity</option><option>Weakness right upper extremity</option><option>Bilateral weakness</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-motor-le">Motor - Lower Extremities</label><select id="neuro-motor-le" name="neuro_motor_le" class="form-control"><option value="">Select...</option><option>5/5 bilaterally (Normal)</option><option>Weakness left lower extremity</option><option>Weakness right lower extremity</option><option>Paraparesis</option><option>Paraplegia</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-sensory">Sensory</label><select id="neuro-sensory" name="neuro_sensory" class="form-control"><option value="">Select...</option><option>Intact bilaterally</option><option>Decreased left</option><option>Decreased right</option><option>Bilateral decrease</option><option>Glove and stocking pattern</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-reflexes">Deep Tendon Reflexes</label><select id="neuro-reflexes" name="neuro_reflexes" class="form-control"><option value="">Select...</option><option>2+ bilaterally (Normal)</option><option>Hyperreflexia</option><option>Hyporeflexia</option><option>Areflexia</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-babinski">Babinski Sign</label><select id="neuro-babinski" name="neuro_babinski" class="form-control"><option value="">Select...</option><option>Negative bilaterally</option><option>Positive left</option><option>Positive right</option><option>Positive bilateral</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-coordination">Coordination (Cerebellar)</label><select id="neuro-coordination" name="neuro_coordination" class="form-control"><option value="">Select...</option><option>Intact - FNF/HTS normal</option><option>Dysdiadochokinesia</option><option>Dysmetria</option><option>Ataxia</option></select></div>
                            </div>
                            <h4 class="form-section-title">Neuro-imaging &amp; Diagnostics</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="neuro-imaging">Imaging Ordered / Reviewed</label><select id="neuro-imaging" name="neuro_imaging" class="form-control"><option value="">Select...</option><option>MRI Brain</option><option>MRI Spine</option><option>CT Brain</option><option>CT Angiography</option><option>EEG</option><option>EMG/NCS</option><option>None</option></select></div>
                                <div class="form-group"><label class="form-label" for="neuro-imaging-findings">Imaging / EEG Findings</label><textarea id="neuro-imaging-findings" name="neuro_imaging_findings" class="form-control" rows="2"></textarea></div>
                            </div>
                            <div class="form-group"><label class="form-label" for="neuro-assessment">Assessment &amp; Plan Summary</label><textarea id="neuro-assessment" name="neuro_assessment" class="form-control" rows="3"></textarea></div>
                        </form>
                    </div>
                </div>

                <!-- ===== 5. ONCOLOGY EHR PANEL ===== -->
                <div class="accordion-item specialty-only onco-only hidden">
                    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-onco" id="accordion-onco-btn">
                        Oncology Assessment Suite
                    </button>
                    <div id="accordion-onco" class="accordion-content hidden" role="region" aria-labelledby="accordion-onco-btn">
                        <form id="onco-form" novalidate class="mod-clinical-style-12">
                            <h4 class="form-section-title">Oncology Patient Overview</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="onco-cancer-type">Cancer Type / Primary Site</label><input type="text" id="onco-cancer-type" name="onco_cancer_type" class="form-control" placeholder="e.g. Non-small cell lung cancer, Breast adenocarcinoma"></div>
                                <div class="form-group"><label class="form-label" for="onco-histology">Histology / Subtype</label><input type="text" id="onco-histology" name="onco_histology" class="form-control" placeholder="e.g. Adenocarcinoma, Squamous cell, ER+ HER2-"></div>
                                <div class="form-group"><label class="form-label" for="onco-diagnosis-date">Date of Diagnosis</label><input type="date" id="onco-diagnosis-date" name="onco_diagnosis_date" class="form-control"></div>
                                <div class="form-group"><label class="form-label" for="onco-ecog">ECOG Performance Status</label><select id="onco-ecog" name="onco_ecog" class="form-control"><option value="">Select...</option><option>0 - Fully active</option><option>1 - Restricted in strenuous activity</option><option>2 - Ambulatory, up >50% of waking hours</option><option>3 - Limited self-care, confined to bed >50%</option><option>4 - Completely disabled, no self-care</option><option>5 - Dead</option></select></div>
                            </div>
                            <h4 class="form-section-title">TNM Staging</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="onco-t-stage">T - Tumor Size</label><select id="onco-t-stage" name="onco_t_stage" class="form-control"><option value="">Select...</option><option>T0</option><option>Tis</option><option>T1</option><option>T1a</option><option>T1b</option><option>T2</option><option>T2a</option><option>T2b</option><option>T3</option><option>T4</option><option>Tx</option></select></div>
                                <div class="form-group"><label class="form-label" for="onco-n-stage">N - Lymph Nodes</label><select id="onco-n-stage" name="onco_n_stage" class="form-control"><option value="">Select...</option><option>N0</option><option>N1</option><option>N2</option><option>N3</option><option>Nx</option></select></div>
                                <div class="form-group"><label class="form-label" for="onco-m-stage">M - Metastasis</label><select id="onco-m-stage" name="onco_m_stage" class="form-control"><option value="">Select...</option><option>M0 - No distant metastasis</option><option>M1 - Distant metastasis present</option><option>M1a</option><option>M1b</option><option>M1c</option><option>Mx</option></select></div>
                                <div class="form-group"><label class="form-label" for="onco-overall-stage">Overall Stage</label><select id="onco-overall-stage" name="onco_overall_stage" class="form-control"><option value="">Select...</option><option>Stage I</option><option>Stage IA</option><option>Stage IB</option><option>Stage II</option><option>Stage IIA</option><option>Stage IIB</option><option>Stage III</option><option>Stage IIIA</option><option>Stage IIIB</option><option>Stage IIIC</option><option>Stage IV</option></select></div>
                                <div class="form-group"><label class="form-label" for="onco-grade">Tumor Grade</label><select id="onco-grade" name="onco_grade" class="form-control"><option value="">Select...</option><option>Grade 1 (Well differentiated)</option><option>Grade 2 (Moderately differentiated)</option><option>Grade 3 (Poorly differentiated)</option><option>Grade 4 (Undifferentiated)</option></select></div>
                            </div>
                            <h4 class="form-section-title">Current Treatment Regimen</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="onco-treatment-intent">Treatment Intent</label><select id="onco-treatment-intent" name="onco_treatment_intent" class="form-control"><option value="">Select...</option><option>Curative</option><option>Adjuvant</option><option>Neoadjuvant</option><option>Palliative</option><option>Maintenance</option></select></div>
                                <div class="form-group"><label class="form-label" for="onco-chemo-regimen">Chemotherapy Regimen</label><input type="text" id="onco-chemo-regimen" name="onco_chemo_regimen" class="form-control" placeholder="e.g. Carboplatin + Paclitaxel, Cycle 3 of 6"></div>
                                <div class="form-group"><label class="form-label" for="onco-radiation">Radiation Therapy</label><input type="text" id="onco-radiation" name="onco_radiation" class="form-control" placeholder="e.g. 45 Gy in 25 fractions to chest wall"></div>
                                <div class="form-group"><label class="form-label" for="onco-immunotherapy">Immunotherapy / Targeted Therapy</label><input type="text" id="onco-immunotherapy" name="onco_immunotherapy" class="form-control" placeholder="e.g. Pembrolizumab 200mg q3w"></div>
                                <div class="form-group"><label class="form-label" for="onco-surgery-history">Surgical History</label><input type="text" id="onco-surgery-history" name="onco_surgery_history" class="form-control" placeholder="e.g. Right mastectomy 2024"></div>
                                <div class="form-group"><label class="form-label" for="onco-last-treatment">Last Treatment Date</label><input type="date" id="onco-last-treatment" name="onco_last_treatment" class="form-control"></div>
                            </div>
                            <h4 class="form-section-title">Response &amp; Toxicity</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="onco-response">Treatment Response</label><select id="onco-response" name="onco_response" class="form-control"><option value="">Select...</option><option>Complete Response (CR)</option><option>Partial Response (PR)</option><option>Stable Disease (SD)</option><option>Progressive Disease (PD)</option><option>Not yet assessed</option></select></div>
                                <div class="form-group"><label class="form-label" for="onco-toxicity">Toxicity / Side Effects</label><input type="text" id="onco-toxicity" name="onco_toxicity" class="form-control" placeholder="e.g. Grade 2 peripheral neuropathy, Grade 1 nausea"></div>
                                <div class="form-group"><label class="form-label" for="onco-tumor-markers">Tumor Markers</label><input type="text" id="onco-tumor-markers" name="onco_tumor_markers" class="form-control" placeholder="e.g. CEA 12.4, CA-125 45"></div>
                                <div class="form-group"><label class="form-label" for="onco-tumor-board">Tumor Board Discussion</label><textarea id="onco-tumor-board" name="onco_tumor_board" class="form-control" rows="2" placeholder="Tumor board recommendation if applicable"></textarea></div>
                            </div>
                            <div class="form-group"><label class="form-label" for="onco-assessment">Assessment &amp; Plan Summary</label><textarea id="onco-assessment" name="onco_assessment" class="form-control" rows="3"></textarea></div>
                        </form>
                    </div>
                </div>

                <!-- ===== 6. OPHTHALMOLOGY EHR PANEL ===== -->
                <div class="accordion-item specialty-only ophthal-only hidden">
                    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-ophthal" id="accordion-ophthal-btn">
                        Ophthalmology &amp; Optometry Assessment Suite
                    </button>
                    <div id="accordion-ophthal" class="accordion-content hidden" role="region" aria-labelledby="accordion-ophthal-btn">
                        <form id="ophthal-form" novalidate class="mod-clinical-style-12">
                            <h4 class="form-section-title">Visual Acuity (VA)</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="ophthal-va-od">VA - OD (Right Eye) Uncorrected</label><input type="text" id="ophthal-va-od" name="ophthal_va_od" class="form-control" placeholder="e.g. 20/40"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-va-os">VA - OS (Left Eye) Uncorrected</label><input type="text" id="ophthal-va-os" name="ophthal_va_os" class="form-control" placeholder="e.g. 20/60"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-va-ou">VA - OU (Both Eyes)</label><input type="text" id="ophthal-va-ou" name="ophthal_va_ou" class="form-control" placeholder="e.g. 20/30"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-va-od-corrected">VA - OD Corrected (Best Corrected)</label><input type="text" id="ophthal-va-od-corrected" name="ophthal_va_od_corrected" class="form-control" placeholder="e.g. 20/20"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-va-os-corrected">VA - OS Corrected (Best Corrected)</label><input type="text" id="ophthal-va-os-corrected" name="ophthal_va_os_corrected" class="form-control" placeholder="e.g. 20/20"></div>
                            </div>
                            <h4 class="form-section-title">Intraocular Pressure (IOP - Tonometry)</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="ophthal-iop-od">IOP - OD (mmHg)</label><input type="number" id="ophthal-iop-od" name="ophthal_iop_od" class="form-control" placeholder="Normal: 10-21"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-iop-os">IOP - OS (mmHg)</label><input type="number" id="ophthal-iop-os" name="ophthal_iop_os" class="form-control" placeholder="Normal: 10-21"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-iop-method">Tonometry Method</label><select id="ophthal-iop-method" name="ophthal_iop_method" class="form-control"><option value="">Select...</option><option>Goldmann Applanation</option><option>Non-contact (Air puff)</option><option>iCare</option></select></div>
                            </div>
                            <h4 class="form-section-title">Slit Lamp Examination</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="ophthal-cornea">Cornea</label><input type="text" id="ophthal-cornea" name="ophthal_cornea" class="form-control" placeholder="e.g. Clear, arcus senilis OD"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-ac">Anterior Chamber (AC)</label><select id="ophthal-ac" name="ophthal_ac" class="form-control"><option value="">Select...</option><option>Deep and quiet</option><option>Shallow</option><option>Cell/Flare present</option><option>Hyphema</option></select></div>
                                <div class="form-group"><label class="form-label" for="ophthal-iris">Iris</label><input type="text" id="ophthal-iris" name="ophthal_iris" class="form-control" placeholder="e.g. Round, reactive to light"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-lens">Lens</label><select id="ophthal-lens" name="ophthal_lens" class="form-control"><option value="">Select...</option><option>Clear</option><option>Nuclear sclerosis Grade 1</option><option>Nuclear sclerosis Grade 2</option><option>Nuclear sclerosis Grade 3</option><option>Posterior subcapsular cataract</option><option>IOL in place</option></select></div>
                            </div>
                            <h4 class="form-section-title">Fundus (Dilated) Examination</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="ophthal-disc-od">Optic Disc - OD</label><input type="text" id="ophthal-disc-od" name="ophthal_disc_od" class="form-control" placeholder="e.g. Pink, sharp margins, C/D 0.3"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-disc-os">Optic Disc - OS</label><input type="text" id="ophthal-disc-os" name="ophthal_disc_os" class="form-control" placeholder="e.g. Pink, sharp margins, C/D 0.3"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-macula">Macula</label><select id="ophthal-macula" name="ophthal_macula" class="form-control"><option value="">Select...</option><option>Normal - foveal reflex present</option><option>Drusen present</option><option>Macular degeneration (dry)</option><option>Macular degeneration (wet)</option><option>Epiretinal membrane</option><option>Macular hole</option></select></div>
                                <div class="form-group"><label class="form-label" for="ophthal-vessels">Vessels</label><select id="ophthal-vessels" name="ophthal_vessels" class="form-control"><option value="">Select...</option><option>Normal A/V ratio</option><option>AV nicking</option><option>Neovascularization</option><option>Hemorrhages</option><option>Cotton wool spots</option></select></div>
                                <div class="form-group"><label class="form-label" for="ophthal-retina">Peripheral Retina</label><select id="ophthal-retina" name="ophthal_retina" class="form-control"><option value="">Select...</option><option>Normal</option><option>Lattice degeneration</option><option>Retinal tear</option><option>Retinal detachment</option></select></div>
                            </div>
                            <h4 class="form-section-title">Refraction / Optical Prescription</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="ophthal-rx-od-sphere">OD - Sphere</label><input type="text" id="ophthal-rx-od-sphere" name="ophthal_rx_od_sphere" class="form-control" placeholder="e.g. -2.50"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-rx-od-cylinder">OD - Cylinder</label><input type="text" id="ophthal-rx-od-cylinder" name="ophthal_rx_od_cylinder" class="form-control" placeholder="e.g. -0.75"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-rx-od-axis">OD - Axis</label><input type="text" id="ophthal-rx-od-axis" name="ophthal_rx_od_axis" class="form-control" placeholder="e.g. 180"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-rx-os-sphere">OS - Sphere</label><input type="text" id="ophthal-rx-os-sphere" name="ophthal_rx_os_sphere" class="form-control" placeholder="e.g. -3.00"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-rx-os-cylinder">OS - Cylinder</label><input type="text" id="ophthal-rx-os-cylinder" name="ophthal_rx_os_cylinder" class="form-control" placeholder="e.g. -0.50"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-rx-os-axis">OS - Axis</label><input type="text" id="ophthal-rx-os-axis" name="ophthal_rx_os_axis" class="form-control" placeholder="e.g. 175"></div>
                                <div class="form-group"><label class="form-label" for="ophthal-rx-add">Add Power (Bifocal/Progressive)</label><input type="text" id="ophthal-rx-add" name="ophthal_rx_add" class="form-control" placeholder="e.g. +2.00"></div>
                            </div>
                            <div class="form-group"><label class="form-label" for="ophthal-assessment">Assessment &amp; Plan Summary</label><textarea id="ophthal-assessment" name="ophthal_assessment" class="form-control" rows="3"></textarea></div>
                        </form>
                    </div>
                </div>

                <!-- ===== 7. PHYSICAL THERAPY / CHIROPRACTIC EHR PANEL ===== -->
                <div class="accordion-item specialty-only pt-only hidden">
                    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-pt" id="accordion-pt-btn">
                        Physical Therapy &amp; Chiropractic Assessment Suite
                    </button>
                    <div id="accordion-pt" class="accordion-content hidden" role="region" aria-labelledby="accordion-pt-btn">
                        <form id="pt-form" novalidate class="mod-clinical-style-12">
                            <h4 class="form-section-title">Presenting Complaint &amp; Functional Limitation</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="pt-complaint">Chief Complaint</label><input type="text" id="pt-complaint" name="pt_complaint" class="form-control" placeholder="e.g. Lower back pain with radiation to left leg"></div>
                                <div class="form-group"><label class="form-label" for="pt-pain-score">Pain Score (0-10)</label><select id="pt-pain-score" name="pt_pain_score" class="form-control"><option value="">Select...</option><option>0</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option><option>10</option></select></div>
                                <div class="form-group"><label class="form-label" for="pt-functional-limitation">Functional Limitation</label><input type="text" id="pt-functional-limitation" name="pt_functional_limitation" class="form-control" placeholder="e.g. Cannot walk more than 1 block without pain"></div>
                                <div class="form-group"><label class="form-label" for="pt-activities-limited">Activities Limited</label><input type="text" id="pt-activities-limited" name="pt_activities_limited" class="form-control" placeholder="e.g. Climbing stairs, prolonged sitting"></div>
                            </div>
                            <h4 class="form-section-title">Postural &amp; Spinal Assessment</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="pt-posture">Postural Assessment</label><input type="text" id="pt-posture" name="pt_posture" class="form-control" placeholder="e.g. Forward head posture, increased lumbar lordosis"></div>
                                <div class="form-group"><label class="form-label" for="pt-spinal-alignment">Spinal Alignment</label><select id="pt-spinal-alignment" name="pt_spinal_alignment" class="form-control"><option value="">Select...</option><option>Normal</option><option>Scoliosis - Mild</option><option>Scoliosis - Moderate</option><option>Scoliosis - Severe</option><option>Kyphosis</option><option>Lordosis increased</option><option>Flat back</option></select></div>
                                <div class="form-group"><label class="form-label" for="pt-subluxation">Chiropractic Subluxation</label><input type="text" id="pt-subluxation" name="pt_subluxation" class="form-control" placeholder="e.g. C5/C6, L4/L5 subluxation complex"></div>
                                <div class="form-group"><label class="form-label" for="pt-palpation">Palpation Findings</label><input type="text" id="pt-palpation" name="pt_palpation" class="form-control" placeholder="e.g. Tenderness L3/L4, myofascial trigger points bilateral trapezius"></div>
                            </div>
                            <h4 class="form-section-title">Muscle Testing (MMT 0-5)</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="pt-mmt-flexors">Flexors</label><select id="pt-mmt-flexors" name="pt_mmt_flexors" class="form-control"><option value="">Select...</option><option>5 - Normal</option><option>4 - Good</option><option>3 - Fair</option><option>2 - Poor</option><option>1 - Trace</option><option>0 - Zero</option></select></div>
                                <div class="form-group"><label class="form-label" for="pt-mmt-extensors">Extensors</label><select id="pt-mmt-extensors" name="pt_mmt_extensors" class="form-control"><option value="">Select...</option><option>5 - Normal</option><option>4 - Good</option><option>3 - Fair</option><option>2 - Poor</option><option>1 - Trace</option><option>0 - Zero</option></select></div>
                                <div class="form-group"><label class="form-label" for="pt-mmt-abductors">Abductors</label><select id="pt-mmt-abductors" name="pt_mmt_abductors" class="form-control"><option value="">Select...</option><option>5 - Normal</option><option>4 - Good</option><option>3 - Fair</option><option>2 - Poor</option><option>1 - Trace</option><option>0 - Zero</option></select></div>
                                <div class="form-group"><label class="form-label" for="pt-mmt-notes">Additional MMT Notes</label><input type="text" id="pt-mmt-notes" name="pt_mmt_notes" class="form-control" placeholder="e.g. Left hip flexors 3/5, right 5/5"></div>
                            </div>
                            <h4 class="form-section-title">Physical Modalities Used</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="pt-modalities">Modalities Applied</label><input type="text" id="pt-modalities" name="pt_modalities" class="form-control" placeholder="e.g. Ultrasound, TENS, Hot pack, Ice, Traction"></div>
                                <div class="form-group"><label class="form-label" for="pt-chiro-technique">Chiropractic Technique</label><select id="pt-chiro-technique" name="pt_chiro_technique" class="form-control"><option value="">Select...</option><option>N/A</option><option>Diversified</option><option>Gonstead</option><option>Activator</option><option>SOT</option><option>Thompson Drop</option><option>Flexion-Distraction</option></select></div>
                                <div class="form-group"><label class="form-label" for="pt-exercises">Home Exercise Program (HEP)</label><textarea id="pt-exercises" name="pt_exercises" class="form-control" rows="2" placeholder="e.g. Core strengthening x3 sets, Hamstring stretch BID"></textarea></div>
                                <div class="form-group"><label class="form-label" for="pt-sessions">Planned Sessions</label><input type="text" id="pt-sessions" name="pt_sessions" class="form-control" placeholder="e.g. 2x/week for 6 weeks"></div>
                            </div>
                            <h4 class="form-section-title">Functional Goals &amp; Rehab Outcomes</h4>
                            <div class="mod-clinical-style-33">
                                <div class="form-group"><label class="form-label" for="pt-goal-short">Short-term Goals (4-6 weeks)</label><input type="text" id="pt-goal-short" name="pt_goal_short" class="form-control" placeholder="e.g. Pain reduced to 3/10, improved mobility"></div>
                                <div class="form-group"><label class="form-label" for="pt-goal-long">Long-term Goals (12 weeks)</label><input type="text" id="pt-goal-long" name="pt_goal_long" class="form-control" placeholder="e.g. Return to full work activities without pain"></div>
                                <div class="form-group"><label class="form-label" for="pt-outcome">Outcome Measure</label><select id="pt-outcome" name="pt_outcome" class="form-control"><option value="">Select...</option><option>Oswestry Disability Index</option><option>DASH Score</option><option>VAS Pain Scale</option><option>SF-36</option><option>WOMAC</option><option>QuickDASH</option></select></div>
                                <div class="form-group"><label class="form-label" for="pt-outcome-score">Outcome Score</label><input type="text" id="pt-outcome-score" name="pt_outcome_score" class="form-control" placeholder="e.g. ODI 42% (Moderate disability)"></div>
                            </div>
                            <div class="form-group"><label class="form-label" for="pt-assessment">Assessment &amp; Plan Summary</label><textarea id="pt-assessment" name="pt_assessment" class="form-control" rows="3"></textarea></div>
                        </form>
                    </div>
                </div>

                <!-- Accordion Section: Diagnosis, Treatment Plan & Signing -->
                <div class="accordion-item">
                    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-plan" id="accordion-plan-btn">
                        Diagnosis, Treatment Plan & Signing
                    </button>
                    <div id="accordion-plan" class="accordion-content hidden" role="region" aria-labelledby="accordion-plan-btn">
                        <form id="plan-form" novalidate class="obgyn-form-card">
                            
                            <h4 class="form-section-title">CPT-4 Procedure Codes & Active Medical Problem List</h4>
                            
                            <div class="form-group margin-bottom-0">
                                <label class="form-label" for="icd10-quick-select">Quick CPT-4 Procedure Code Selector</label>
                                <div class="icd10-selector-flex">
                                    <select id="icd10-quick-select" class="form-control font-weight-600">
                                        <option value="">-- Select CPT-4 Procedure Code to Add --</option>
                                    </select>
                                    <button type="button" class="btn btn-secondary" id="add-icd10-code-btn">+ Add Code</button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="clinical-icd10">Selected CPT-4 Procedure Codes & Active Problem List</label>
                                <textarea id="clinical-icd10" class="form-control" rows="3" placeholder="CPT-4 procedure codes & medical problems will appear here..."></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="clinical-summary">Treatment Plan, Rx & Follow-Up Orders</label>
                                <textarea id="clinical-summary" class="form-control" rows="4" placeholder="Enter clinical assessment notes, prescription orders, patient counseling, and follow-up directives..."></textarea>
                            </div>

                            <div class="card signature-card-attestation">
                                <h4 class="margin-bottom-8">HIPAA Practitioner Attestation & Signature Pad</h4>
                                <p class="text-secondary font-size-85 margin-bottom-12">By signing on the digital pad below, I certify that I have personally evaluated this patient and that the records entered are true and accurate.</p>
                                <div class="form-group flex-column-gap8">
                                    <div class="signature-canvas-wrapper">
                                        <canvas id="signature-canvas" width="600" height="140" class="signature-canvas-element"></canvas>
                                        <button type="button" id="clear-sig-btn" class="btn btn-secondary signature-clear-btn">Clear Signature</button>
                                    </div>
                                    <input type="hidden" id="sig-pad-input" name="sig_pad_input" value="Authenticated Provider Digital Signature">
                                    <input type="hidden" id="sig-data-url" name="sig_data_url">
                                    <span id="sig-status-msg" class="text-secondary font-italic font-size-80">Sign above with mouse or touch screen. No typing required.</span>
                                </div>
                            </div>
                            
                            <div class="flex-row-end">
                                <button type="button" class="btn btn-secondary" id="cancel-encounter-btn">Cancel</button>
                                <!-- Phase 2: Save draft without locking -->
                                <button type="button" class="btn btn-secondary" id="save-draft-btn">💾 Save Draft</button>
                                <button type="button" class="btn btn-primary hidden" id="update-encounter-btn">Update Encounter</button>
                                <!-- Phase 2: Sign & Finalize (SIGN-001) — triggers lock workflow -->
                                <button type="button" class="btn btn-success" id="sign-finalize-btn">✅ Sign &amp; Finalize</button>
                                <!-- Phase 2: Addendum button — visible only when locked (SIGN-005) -->
                                <button type="button" class="btn btn-warning hidden" id="add-addendum-btn">📝 Add Addendum</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div> <!-- End Accordion -->
        </div>
    </div>
  </div>
</div>

<!-- ================================================================
     Phase 2: Addendum Modal (SIGN-005 / SIGN-006)
     Appends a timestamped correction to a locked encounter without
     overwriting the original signed clinical content.
     ================================================================ -->
<div id="addendum-modal" class="modal fade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" style="max-width:560px;">
    <div class="modal-content">
        <div class="modal-header mod-clinical-style-9">
            <h3 style="margin:0;">📝 Add Signed Addendum</h3>
            <button type="button" class="modal-close" id="close-addendum-modal-btn">&times;</button>
        </div>
        <div class="modal-body" style="padding:24px;">
            <p class="text-secondary" style="font-size:0.85rem;margin-bottom:16px;">
                This encounter is <strong>locked</strong>. Your addendum will be appended to the record with your name, role, and timestamp. The original signed note will be preserved unchanged.
            </p>
            <div class="form-group">
                <label class="form-label" for="addendum-text">Addendum Note <span style="color:#e53935;">*</span></label>
                <textarea id="addendum-text" class="form-control" rows="5" placeholder="Describe the correction or supplementary clinical information..."></textarea>
            </div>
            <div id="addendum-status-msg" style="display:none;padding:8px 12px;border-radius:6px;font-size:0.85rem;margin-top:12px;"></div>
        </div>
        <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid rgba(255,255,255,0.1);">
            <button type="button" class="btn btn-secondary" id="cancel-addendum-btn">Cancel</button>
            <button type="button" class="btn btn-warning" id="submit-addendum-btn">Submit Addendum</button>
        </div>
    </div>
  </div>
</div>

<!-- Phase 2: Encounter Status & Lock CSS tokens -->
<style>
.enc-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.enc-status-draft        { background: rgba(120,120,120,0.25); color: #bbb; }
.enc-status-in_progress  { background: rgba(33,150,243,0.2);  color: #64b5f6; }
.enc-status-ready_for_sign { background: rgba(255,193,7,0.2);  color: #ffd54f; }
.enc-status-signed       { background: rgba(76,175,80,0.2);   color: #81c784; }
.enc-status-locked       { background: rgba(244,67,54,0.15);  color: #ef9a9a; }
.enc-lock-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    background: rgba(244,67,54,0.15);
    color: #ef9a9a;
    border: 1px solid rgba(244,67,54,0.3);
}
/* Lock overlay: when encounter is locked, dim all editable inputs */
.encounter-locked input:not([readonly]),
.encounter-locked textarea,
.encounter-locked select {
    opacity: 0.55;
    pointer-events: none;
    cursor: not-allowed;
}
.btn-success {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: #fff;
    border: none;
}
.btn-success:hover { background: linear-gradient(135deg, #1b5e20, #2e7d32); }
.btn-warning {
    background: linear-gradient(135deg, #e65100, #f57c00);
    color: #fff;
    border: none;
}
.btn-warning:hover { background: linear-gradient(135deg, #bf360c, #e65100); }
</style>
