        <div id="clinical-encounter-modal" class="modal-backdrop hidden">
            <div class="modal-dialog mod-clinical-style-8">
                <div class="modal-header mod-clinical-style-9">
                    <h2 class="mod-clinical-style-10" id="clinical-modal-title">New Clinical Encounter</h2>
                    <button type="button" class="modal-close" id="close-clinical-modal-btn">&times;</button>
                </div>
                <div class="modal-body mod-clinical-style-11">
                    
                    <!-- Clinical Workspace Accordion Container inside Modal -->
                    <div id="clinical-accordion" class="accordion-container">
                        
                        <!-- Accordion Section: Encounter Details & Vitals -->
                        <div class="accordion-item" style="display: none !important;">
                            <button class="accordion-header" aria-expanded="true" aria-controls="accordion-vitals" id="accordion-vitals-btn">
                                Encounter Setup & Patient Vitals
                            </button>
                            <div id="accordion-vitals" class="accordion-content" role="region" aria-labelledby="accordion-vitals-btn">
                                <form class="mod-clinical-style-12" id="vitals-form" novalidate>
                                    
                                    <div class="mod-clinical-style-13">
                                        <div class="form-group">
                                            <label class="form-label" for="encounter-type-select">Encounter Type / Specialty</label>
                                            <select id="encounter-type-select" class="form-control mod-clinical-style-14">
                                                <option value="Cardiology">Cardiology EHR</option>
                                                <option value="Orthopedics">Orthopedic EHR</option>
                                                <option value="Dermatology">Dermatology EHR</option>
                                                <option value="Neurology">Neurology EHR</option>
                                                <option value="Oncology">Oncology EHR</option>
                                                <option value="Ophthalmology">Ophthalmology EHR (covers optometry)</option>
                                                <option value="Physical Therapy">Physical Therapy EHR (covers chiropractic)</option>
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
                                </form>
                            </div>
                        </div>

                        <!-- Accordion Section: Pediatrics Assessment & Complete History -->
                        <div class="accordion-item peds-only hidden">
                            <button class="accordion-header" aria-expanded="false" aria-controls="accordion-peds" id="accordion-peds-btn">
                                Pediatrics Assessment & Complete History
                            </button>
                            <div id="accordion-peds" class="accordion-content hidden" role="region" aria-labelledby="accordion-peds-btn">
                                <form class="mod-clinical-style-18" id="peds-form" novalidate>
                                    
                                    <div class="mod-clinical-style-19">
                                            <h3 class="mod-clinical-style-20">Pediatric General Assessment</h3>
                                            <div class="grid-2col mod-clinical-style-21">
                                                <div class="form-group">
                                                    <label class="form-label" for="ped-visit-type">Visit Type</label>
                                                    <select id="ped-visit-type" class="form-control">
                                                        <option value="Well Child Visit">Well Child Visit</option>
                                                        <option value="Sick Visit">Sick Visit</option>
                                                        <option value="Follow-up">Follow-up</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                            <div class="form-group">
                                                <label class="form-label" for="ped-accompanied">Accompanied By</label>
                                                <select id="ped-accompanied" class="form-control">
                                                    <option value="Mother">Mother</option>
                                                    <option value="Father">Father</option>
                                                    <option value="Both Parents">Both Parents</option>
                                                    <option value="Guardian">Guardian</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="grid-2col mod-clinical-style-21">
                                            <div class="form-group">
                                                <label class="form-label" for="ped-chief-complaint">Chief Complaint</label>
                                                <input type="text" id="ped-chief-complaint" class="form-control" placeholder="Primary reason for visit...">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="ped-symptoms-duration">Duration of Symptoms</label>
                                                <input type="text" id="ped-symptoms-duration" class="form-control" placeholder="e.g. 3 days">
                                            </div>
                                        </div>
                                        
                                        <div class="form-group mod-clinical-style-22">
                                            <label class="form-label" for="ped-relationship-patient">Relationship to Patient (If Other)</label>
                                            <input type="text" id="ped-relationship-patient" class="form-control" placeholder="Specify relationship...">
                                        </div>

                                        <h4 class="form-section-title">General Appearance</h4>
                                        <div class="grid-3col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label">General Appearance</label>
                                                <select id="ped-gen-appearance" class="form-control" aria-label="clinical field 1">
                                                    <option value="Well Appearing">Well Appearing</option>
                                                    <option value="Ill Appearing">Ill Appearing</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Level of Consciousness</label>
                                                <select id="ped-consciousness" class="form-control" aria-label="clinical field 1">
                                                    <option value="Alert">Alert</option>
                                                    <option value="Lethargic">Lethargic</option>
                                                    <option value="Irritable">Irritable</option>
                                                    <option value="Unresponsive">Unresponsive</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Activity Level</label>
                                                <select id="ped-activity" class="form-control" aria-label="clinical field 1">
                                                    <option value="Active">Active</option>
                                                    <option value="Decreased">Decreased</option>
                                                    <option value="Lethargic">Lethargic</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="grid-3col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label">Hydration Status</label>
                                                <select id="ped-hydration" class="form-control" aria-label="clinical field 1">
                                                    <option value="Well Hydrated">Well Hydrated</option>
                                                    <option value="Mild Dehydration">Mild Dehydration</option>
                                                    <option value="Severe Dehydration">Severe Dehydration</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Nutritional Status</label>
                                                <select id="ped-nutritional" class="form-control" aria-label="clinical field 1">
                                                    <option value="Normal">Normal</option>
                                                    <option value="Underweight">Underweight</option>
                                                    <option value="Overweight">Overweight</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Distress Level</label>
                                                <select id="ped-distress" class="form-control" aria-label="clinical field 1">
                                                    <option value="None">None</option>
                                                    <option value="Mild">Mild</option>
                                                    <option value="Severe">Severe</option>
                                                </select>
                                            </div>
                                        </div>

                                        <h4 class="form-section-title">Current Symptoms</h4>
                                        <div class="mod-clinical-style-23">
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Fever" id="clinical-field-1" aria-label="clinical field 2"> Fever</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Cough" id="clinical-field-2" aria-label="clinical field 3"> Cough</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Cold" id="clinical-field-3" aria-label="clinical field 4"> Cold</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Vomiting" id="clinical-field-4" aria-label="clinical field 5"> Vomiting</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Diarrhea" id="clinical-field-5" aria-label="clinical field 6"> Diarrhea</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Rash" id="clinical-field-6" aria-label="clinical field 7"> Rash</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Ear Pain" id="clinical-field-7" aria-label="clinical field 8"> Ear Pain</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Sore Throat" id="clinical-field-8" aria-label="clinical field 9"> Sore Throat</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Poor Feeding" id="clinical-field-9" aria-label="clinical field 10"> Poor Feeding</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Breathing Difficulty" id="clinical-field-10" aria-label="clinical field 11"> Breathing Diff.</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Irritability" id="clinical-field-11" aria-label="clinical field 12"> Irritability</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-symptom-cb" value="Fatigue" id="clinical-field-12" aria-label="clinical field 13"> Fatigue</label>
                                        </div>

                                        <h4 class="form-section-title">Pain Assessment</h4>
                                        <div class="grid-4col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label">Pain Present</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-26"><input type="radio" name="ped_pain_present" value="Yes" id="clinical-ped_pain_present" aria-label="Ped pain present"> Yes</label>
                                                    <label class="mod-clinical-style-26"><input type="radio" name="ped_pain_present" value="No" checked id="clinical-ped_pain_present" aria-label="Ped pain present"> No</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="ped-pain-scale">Pain Scale (0-10)</label>
                                                <select id="ped-pain-scale" class="form-control">
                                                    <option value="0">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="ped-pain-location">Pain Location</label>
                                                <input type="text" id="ped-pain-location" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="ped-pain-duration">Pain Duration</label>
                                                <input type="text" id="ped-pain-duration" class="form-control">
                                            </div>
                                        </div>

                                        <h4 class="form-section-title">Clinical Impression & Recommendations</h4>
                                        <div class="form-group mod-clinical-style-22">
                                            <label class="form-label" for="ped-assessment-notes">Assessment Notes</label>
                                            <textarea id="ped-assessment-notes" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Recommendations</label>
                                            <div class="mod-clinical-style-27">
                                                <label class="mod-clinical-style-24"><input type="checkbox" class="ped-rec-cb" value="Routine Follow-up" id="clinical-field-15" aria-label="clinical field 16"> Routine Follow-up</label>
                                                <label class="mod-clinical-style-24"><input type="checkbox" class="ped-rec-cb" value="Laboratory Investigation" id="clinical-field-16" aria-label="clinical field 17"> Lab Investigation</label>
                                                <label class="mod-clinical-style-24"><input type="checkbox" class="ped-rec-cb" value="Imaging" id="clinical-field-17" aria-label="clinical field 18"> Imaging</label>
                                                <label class="mod-clinical-style-24"><input type="checkbox" class="ped-rec-cb" value="Specialist Referral" id="clinical-field-18" aria-label="clinical field 19"> Specialist Referral</label>
                                                <label class="mod-clinical-style-24"><input type="checkbox" class="ped-rec-cb" value="Emergency Evaluation" id="clinical-field-19" aria-label="clinical field 20"> Emergency Evaluation</label>
                                            </div>
                                            <input type="text" id="ped-rec-other" class="form-control" placeholder="Other Recommendation..." aria-label="Other Recommendation...">
                                        </div>
                                    </div>

                                    <!-- ============================================== -->
                                    <!-- 2. BIRTH & NEONATAL HISTORY                    -->
                                    <!-- ============================================== -->
                                    <div class="mod-clinical-style-19">
                                        <h3 class="mod-clinical-style-20">Birth & Neonatal History</h3>
                                        
                                        <h4 class="form-section-title">Pregnancy History</h4>
                                        <div class="grid-4col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label" for="ped-mother-age">Mother's Age at Delivery</label>
                                                <input type="number" id="ped-mother-age" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Prenatal Care Received</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_prenatal" value="Yes" checked id="clinical-ped_prenatal" aria-label="Ped prenatal"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_prenatal" value="No" id="clinical-ped_prenatal" aria-label="Ped prenatal"> No</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Multiple Pregnancy</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_multiple" value="Yes" id="clinical-ped_multiple" aria-label="Ped multiple"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_multiple" value="No" checked id="clinical-ped_multiple" aria-label="Ped multiple"> No</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Pregnancy Complications</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_preg_comp" value="Yes" id="clinical-ped_preg_comp" aria-label="Ped preg comp"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_preg_comp" value="No" checked id="clinical-ped_preg_comp" aria-label="Ped preg comp"> No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mod-clinical-style-22">
                                            <label class="form-label" for="ped-preg-comp-details">Complication Details</label>
                                            <input type="text" id="ped-preg-comp-details" class="form-control">
                                        </div>

                                        <h4 class="form-section-title">Birth Information</h4>
                                        <div class="grid-4col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label" for="ped-dob">Date of Birth</label>
                                                <input type="date" id="ped-dob" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="ped-gestational-age">Gestational Age</label>
                                                <input type="text" id="ped-gestational-age" class="form-control" placeholder="e.g. 39 weeks">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="ped-birth-weight">Birth Weight</label>
                                                <input type="text" id="ped-birth-weight" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="ped-birth-length">Birth Length</label>
                                                <input type="text" id="ped-birth-length" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="ped-head-circ">Head Circumference</label>
                                                <input type="text" id="ped-head-circ" class="form-control">
                                            </div>
                                        </div>

                                        <h4 class="form-section-title">Delivery Details</h4>
                                        <div class="grid-3col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label">Delivery Type</label>
                                                <select id="ped-delivery-type" class="form-control" aria-label="clinical field 26">
                                                    <option value="Normal Vaginal Delivery">Normal Vaginal Delivery</option>
                                                    <option value="C-Section">C-Section</option>
                                                    <option value="VBAC">VBAC</option>
                                                    <option value="Forceps/Vacuum">Forceps/Vacuum</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Place of Birth</label>
                                                <select id="ped-birth-place" class="form-control" aria-label="clinical field 26">
                                                    <option value="Hospital">Hospital</option>
                                                    <option value="Home">Home</option>
                                                    <option value="Birth Center">Birth Center</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Delivery Complications</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_del_comp" value="Yes" id="clinical-ped_del_comp" aria-label="Ped del comp"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_del_comp" value="No" checked id="clinical-ped_del_comp" aria-label="Ped del comp"> No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mod-clinical-style-22">
                                            <label class="form-label" for="ped-del-comp-details">Delivery Complication Details</label>
                                            <input type="text" id="ped-del-comp-details" class="form-control">
                                        </div>

                                        <h4 class="form-section-title">Neonatal Assessment</h4>
                                        <div class="grid-4col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label" for="ped-apgar1">APGAR Score (1 Min)</label>
                                                <input type="text" id="ped-apgar1" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="ped-apgar5">APGAR Score (5 Min)</label>
                                                <input type="text" id="ped-apgar5" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">NICU Admission</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_nicu" value="Yes" id="clinical-ped_nicu" aria-label="Ped nicu"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_nicu" value="No" checked id="clinical-ped_nicu" aria-label="Ped nicu"> No</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="ped-nicu-stay">NICU Stay Duration</label>
                                                <input type="text" id="ped-nicu-stay" class="form-control">
                                            </div>
                                        </div>

                                        <h4 class="form-section-title">Neonatal Conditions</h4>
                                        <div class="mod-clinical-style-23">
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-neonatal-cb" value="Neonatal Jaundice" id="clinical-field-30" aria-label="clinical field 31"> Neonatal Jaundice</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-neonatal-cb" value="Respiratory Distress" id="clinical-field-31" aria-label="clinical field 32"> Resp. Distress</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-neonatal-cb" value="Sepsis" id="clinical-field-32" aria-label="clinical field 33"> Sepsis</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-neonatal-cb" value="Hypoglycemia" id="clinical-field-33" aria-label="clinical field 34"> Hypoglycemia</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-neonatal-cb" value="Birth Asphyxia" id="clinical-field-34" aria-label="clinical field 35"> Birth Asphyxia</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-neonatal-cb" value="Meconium Aspiration" id="clinical-field-35" aria-label="clinical field 36"> Meconium Asp.</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-neonatal-cb" value="Congenital Anomaly" id="clinical-field-36" aria-label="clinical field 37"> Congenital Anomaly</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-neonatal-cb" value="Low Birth Weight" id="clinical-field-37" aria-label="clinical field 38"> Low Birth Weight</label>
                                            <label class="mod-clinical-style-24"><input type="checkbox" class="ped-neonatal-cb" value="Prematurity" id="clinical-field-38" aria-label="clinical field 39"> Prematurity</label>
                                        </div>

                                        <h4 class="form-section-title">Feeding History</h4>
                                        <div class="grid-3col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label">Breastfeeding Initiated</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_bf" value="Yes" checked id="clinical-ped_bf" aria-label="Ped bf"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_bf" value="No" id="clinical-ped_bf" aria-label="Ped bf"> No</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Formula Feeding</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_ff" value="Yes" id="clinical-ped_ff" aria-label="Ped ff"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_ff" value="No" checked id="clinical-ped_ff" aria-label="Ped ff"> No</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Feeding Difficulties</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_fd" value="Yes" id="clinical-ped_fd" aria-label="Ped fd"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_fd" value="No" checked id="clinical-ped_fd" aria-label="Ped fd"> No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mod-clinical-style-22">
                                            <label class="form-label" for="ped-feeding-comments">Comments</label>
                                            <input type="text" id="ped-feeding-comments" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="ped-birth-summary">Birth History Summary</label>
                                            <textarea id="ped-birth-summary" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>

                                    <!-- ============================================== -->
                                    <!-- 3. DEVELOPMENTAL MILESTONE ASSESSMENT          -->
                                    <!-- ============================================== -->
                                    <div class="mod-clinical-style-19">
                                        <h3 class="mod-clinical-style-20">Developmental Milestone Assessment</h3>
                                        
                                        <h4 class="form-section-title">Gross Motor Development</h4>
                                        <div class="grid-4col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label">Head Control</label>
                                                <select id="ped-gm-head" class="form-control" aria-label="clinical field 45"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Rolling Over</label>
                                                <select id="ped-gm-roll" class="form-control" aria-label="clinical field 45"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Sitting Without Support</label>
                                                <select id="ped-gm-sit" class="form-control" aria-label="clinical field 45"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Standing</label>
                                                <select id="ped-gm-stand" class="form-control" aria-label="clinical field 45"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Walking</label>
                                                <select id="ped-gm-walk" class="form-control" aria-label="clinical field 45"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Running</label>
                                                <select id="ped-gm-run" class="form-control" aria-label="clinical field 45"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Gross Motor Delay</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_gm_delay" value="Yes" id="clinical-ped_gm_delay" aria-label="Ped gm delay"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_gm_delay" value="No" checked id="clinical-ped_gm_delay" aria-label="Ped gm delay"> No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="form-section-title">Fine Motor Development</h4>
                                        <div class="grid-4col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label">Grasp Objects</label>
                                                <select id="ped-fm-grasp" class="form-control" aria-label="clinical field 47"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Transfers Objects</label>
                                                <select id="ped-fm-transfer" class="form-control" aria-label="clinical field 47"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Pincer Grasp</label>
                                                <select id="ped-fm-pincer" class="form-control" aria-label="clinical field 47"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Draw Shapes</label>
                                                <select id="ped-fm-draw" class="form-control" aria-label="clinical field 47"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Feeds Self</label>
                                                <select id="ped-fm-feed" class="form-control" aria-label="clinical field 47"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Fine Motor Delay</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_fm_delay" value="Yes" id="clinical-ped_fm_delay" aria-label="Ped fm delay"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_fm_delay" value="No" checked id="clinical-ped_fm_delay" aria-label="Ped fm delay"> No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="form-section-title">Language Development</h4>
                                        <div class="grid-4col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label">Babbles</label>
                                                <select id="ped-lang-babble" class="form-control" aria-label="clinical field 49"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">First Words</label>
                                                <select id="ped-lang-words" class="form-control" aria-label="clinical field 49"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Two Word Sentences</label>
                                                <select id="ped-lang-sentences" class="form-control" aria-label="clinical field 49"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Speaks Clearly</label>
                                                <select id="ped-lang-clear" class="form-control" aria-label="clinical field 49"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Understands Commands</label>
                                                <select id="ped-lang-cmd" class="form-control" aria-label="clinical field 49"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Language Delay</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_lang_delay" value="Yes" id="clinical-ped_lang_delay" aria-label="Ped lang delay"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_lang_delay" value="No" checked id="clinical-ped_lang_delay" aria-label="Ped lang delay"> No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="form-section-title">Social Development</h4>
                                        <div class="grid-4col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label">Smiles Socially</label>
                                                <select id="ped-soc-smile" class="form-control" aria-label="clinical field 51"><option value="Achieved">Achieved</option><option value="Not Achieved">Not Achieved</option><option value="Pending">Pending</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Eye Contact</label>
                                                <select id="ped-soc-eye" class="form-control" aria-label="clinical field 51"><option value="Good">Good</option><option value="Poor">Poor</option><option value="None">None</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Plays With Others</label>
                                                <select id="ped-soc-play" class="form-control" aria-label="clinical field 51"><option value="Appropriate">Appropriate</option><option value="Inappropriate">Inappropriate</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Responds To Name</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_soc_name" value="Yes" checked id="clinical-ped_soc_name" aria-label="Ped soc name"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_soc_name" value="No" id="clinical-ped_soc_name" aria-label="Ped soc name"> No</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Behavior Appropriate</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_soc_beh" value="Yes" checked id="clinical-ped_soc_beh" aria-label="Ped soc beh"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_soc_beh" value="No" id="clinical-ped_soc_beh" aria-label="Ped soc beh"> No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="form-section-title">Cognitive Development</h4>
                                        <div class="grid-4col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label">Problem Solving</label>
                                                <select id="ped-cog-solve" class="form-control" aria-label="clinical field 55"><option value="Age Appropriate">Age Appropriate</option><option value="Delayed">Delayed</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Attention Span</label>
                                                <select id="ped-cog-attention" class="form-control" aria-label="clinical field 55"><option value="Normal">Normal</option><option value="Short">Short</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Learning Ability</label>
                                                <select id="ped-cog-learn" class="form-control" aria-label="clinical field 55"><option value="Normal">Normal</option><option value="Impaired">Impaired</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Memory</label>
                                                <select id="ped-cog-memory" class="form-control" aria-label="clinical field 55"><option value="Normal">Normal</option><option value="Impaired">Impaired</option></select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Cognitive Delay</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_cog_delay" value="Yes" id="clinical-ped_cog_delay" aria-label="Ped cog delay"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_cog_delay" value="No" checked id="clinical-ped_cog_delay" aria-label="Ped cog delay"> No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="form-section-title">Developmental Screening</h4>
                                        <div class="grid-4col mod-clinical-style-22">
                                            <div class="form-group">
                                                <label class="form-label">Approp. For Age</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_scr_app" value="Yes" checked id="clinical-ped_scr_app" aria-label="Ped scr app"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_scr_app" value="No" id="clinical-ped_scr_app" aria-label="Ped scr app"> No</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Delay Suspected</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_scr_del" value="Yes" id="clinical-ped_scr_del" aria-label="Ped scr del"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_scr_del" value="No" checked id="clinical-ped_scr_del" aria-label="Ped scr del"> No</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Autism Screen</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_scr_autism" value="Yes" id="clinical-ped_scr_autism" aria-label="Ped scr autism"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_scr_autism" value="No" checked id="clinical-ped_scr_autism" aria-label="Ped scr autism"> No</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Early Interv. Ref</label>
                                                <div class="mod-clinical-style-25">
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_scr_ref" value="Yes" id="clinical-ped_scr_ref" aria-label="Ped scr ref"> Yes</label>
                                                    <label class="mod-clinical-style-28"><input type="radio" name="ped_scr_ref" value="No" checked id="clinical-ped_scr_ref" aria-label="Ped scr ref"> No</label>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="form-section-title">Assessment Summary & Recommendations</h4>
                                        <div class="form-group mod-clinical-style-22">
                                            <label class="form-label" for="ped-dev-summary">Assessment Summary</label>
                                            <textarea id="ped-dev-summary" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Recommendations</label>
                                            <div class="mod-clinical-style-27">
                                                <label class="mod-clinical-style-24"><input type="checkbox" class="ped-devrec-cb" value="Continue Routine Monitoring" id="clinical-field-65" aria-label="clinical field 66"> Continue Routine Monitoring</label>
                                                <label class="mod-clinical-style-24"><input type="checkbox" class="ped-devrec-cb" value="Speech Therapy Referral" id="clinical-field-66" aria-label="clinical field 67"> Speech Therapy Referral</label>
                                                <label class="mod-clinical-style-24"><input type="checkbox" class="ped-devrec-cb" value="Occupational Therapy Referral" id="clinical-field-67" aria-label="clinical field 68"> Occupational Therapy Referral</label>
                                                <label class="mod-clinical-style-24"><input type="checkbox" class="ped-devrec-cb" value="Physical Therapy Referral" id="clinical-field-68" aria-label="clinical field 69"> Physical Therapy Referral</label>
                                                <label class="mod-clinical-style-24"><input type="checkbox" class="ped-devrec-cb" value="Developmental Pediatrician Referral" id="clinical-field-69" aria-label="clinical field 70"> Dev. Pediatrician Referral</label>
                                                <label class="mod-clinical-style-24"><input type="checkbox" class="ped-devrec-cb" value="Early Intervention Program" id="clinical-field-70" aria-label="clinical field 71"> Early Intervention Program</label>
                                            </div>
                                            <input type="text" id="ped-devrec-other" class="form-control" placeholder="Other Recommendation..." aria-label="Other Recommendation...">
                                        </div>
                                    </div>

                                    <!-- Growth & Immunizations -->
                                    <div class="mod-clinical-style-19">
                                        <h3 class="mod-clinical-style-20">Growth & Immunizations</h3>
                                        <div class="mod-clinical-style-29">
                                            <div class="form-group">
                                                <label class="form-label" for="growth-weight-percentile">Weight-for-Age Percentile (%)</label>
                                                <input type="number" min="1" max="99" id="growth-weight-percentile" class="form-control" placeholder="e.g. 50">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="growth-height-percentile">Height-for-Age Percentile (%)</label>
                                                <input type="number" min="1" max="99" id="growth-height-percentile" class="form-control" placeholder="e.g. 65">
                                            </div>
                                        </div>

                                        <h4 class="form-section-title">Immunizations Administered Today</h4>
                                        <div class="vaccine-grid-container">
                                            <label class="vaccine-checkbox-item"><input type="checkbox" class="ped-vaccine-checkbox custom-accent-checkbox" value="DTaP" id="clinical-field-71" aria-label="clinical field 72"> DTaP</label>
                                            <label class="vaccine-checkbox-item"><input type="checkbox" class="ped-vaccine-checkbox custom-accent-checkbox" value="MMR" id="clinical-field-72" aria-label="clinical field 73"> MMR</label>
                                            <label class="vaccine-checkbox-item"><input type="checkbox" class="ped-vaccine-checkbox custom-accent-checkbox" value="Varicella" id="clinical-field-73" aria-label="clinical field 74"> Varicella</label>
                                            <label class="vaccine-checkbox-item"><input type="checkbox" class="ped-vaccine-checkbox custom-accent-checkbox" value="HepB" id="clinical-field-74" aria-label="clinical field 75"> HepB</label>
                                            <label class="vaccine-checkbox-item"><input type="checkbox" class="ped-vaccine-checkbox custom-accent-checkbox" value="IPV (Polio)" id="clinical-field-75" aria-label="clinical field 76"> IPV (Polio)</label>
                                            <label class="vaccine-checkbox-item"><input type="checkbox" class="ped-vaccine-checkbox custom-accent-checkbox" value="Hib" id="clinical-field-76" aria-label="clinical field 77"> Hib</label>
                                            <label class="vaccine-checkbox-item"><input type="checkbox" class="ped-vaccine-checkbox custom-accent-checkbox" value="Rotavirus" id="clinical-field-77" aria-label="clinical field 78"> Rotavirus</label>
                                            <label class="vaccine-checkbox-item"><input type="checkbox" class="ped-vaccine-checkbox custom-accent-checkbox" value="Influenza" id="clinical-field-78" aria-label="clinical field 79"> Influenza</label>
                                        </div>
                                    </div>
                                    
                                
                                <!-- Pediatric Nutrition & Feeding Assessment -->
                                <div class="mod-clinical-style-30">
                                    <h4 class="mod-clinical-style-31">Pediatric Nutrition & Feeding Assessment</h4>
                                    
                                    <h5 class="mod-clinical-style-32">Current Feeding Pattern</h5>
                                    <div class="mod-clinical-style-33">
                                        <div class="form-group">
                                            <label class="form-label" for="ped-feed-primary">Primary Feeding Type</label>
                                            <select id="ped-feed-primary" class="form-control">
                                                <option value="Breastfeeding">Breastfeeding</option>
                                                <option value="Formula">Formula</option>
                                                <option value="Mixed">Mixed (Breast & Formula)</option>
                                                <option value="Solid Foods">Solid Foods</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group"><label class="form-label" for="ped-feed-bf-freq">Breastfeeding Frequency</label><input type="text" id="ped-feed-bf-freq" class="form-control"></div>
                                        <div class="form-group"><label class="form-label" for="ped-feed-formula-type">Formula Type</label><input type="text" id="ped-feed-formula-type" class="form-control"></div>
                                    </div>
                                    <div class="mod-clinical-style-34">
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Formula Feeding</label>
                                            <label><input type="radio" name="ped_feed_formula" value="No" checked id="clinical-ped_feed_formula" aria-label="Ped feed formula"> No</label>
                                            <label><input type="radio" name="ped_feed_formula" value="Yes" id="clinical-ped_feed_formula" aria-label="Ped feed formula"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Bottle Feeding</label>
                                            <label><input type="radio" name="ped_feed_bottle" value="No" checked id="clinical-ped_feed_bottle" aria-label="Ped feed bottle"> No</label>
                                            <label><input type="radio" name="ped_feed_bottle" value="Yes" id="clinical-ped_feed_bottle" aria-label="Ped feed bottle"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Cup Feeding</label>
                                            <label><input type="radio" name="ped_feed_cup" value="No" checked id="clinical-ped_feed_cup" aria-label="Ped feed cup"> No</label>
                                            <label><input type="radio" name="ped_feed_cup" value="Yes" id="clinical-ped_feed_cup" aria-label="Ped feed cup"> Yes</label>
                                        </div>
                                    </div>

                                    <h5 class="mod-clinical-style-36">Complementary Feeding</h5>
                                    <div class="mod-clinical-style-33">
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Solid Foods Started</label>
                                            <label><input type="radio" name="ped_solid_started" value="No" checked id="clinical-ped_solid_started" aria-label="Ped solid started"> No</label>
                                            <label><input type="radio" name="ped_solid_started" value="Yes" id="clinical-ped_solid_started" aria-label="Ped solid started"> Yes</label>
                                        </div>
                                        <div class="form-group"><label class="form-label" for="ped-solid-age">Age Started (Months)</label><input type="number" id="ped-solid-age" class="form-control" min="0"></div>
                                        <div class="form-group"><label class="form-label" for="ped-fav-foods">Favorite Foods</label><input type="text" id="ped-fav-foods" class="form-control"></div>
                                    </div>
                                    <div class="mod-clinical-style-34">
                                        <div class="form-group"><label class="form-label" for="ped-meals-day">Meals Per Day</label><input type="number" id="ped-meals-day" class="form-control" min="0"></div>
                                        <div class="form-group"><label class="form-label" for="ped-snacks-day">Snacks Per Day</label><input type="number" id="ped-snacks-day" class="form-control" min="0"></div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Food Refusal</label>
                                            <label><input type="radio" name="ped_food_refusal" value="No" checked id="clinical-ped_food_refusal" aria-label="Ped food refusal"> No</label>
                                            <label><input type="radio" name="ped_food_refusal" value="Yes" id="clinical-ped_food_refusal" aria-label="Ped food refusal"> Yes</label>
                                        </div>
                                    </div>

                                    <h5 class="mod-clinical-style-36">Dietary Assessment</h5>
                                    <div class="mod-clinical-style-15">
                                        <div class="form-group">
                                            <label class="form-label" for="ped-diet-fruit">Fruit Intake</label>
                                            <select id="ped-diet-fruit" class="form-control"><option value="Adequate">Adequate</option><option value="Inadequate">Inadequate</option><option value="None">None</option></select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="ped-diet-veg">Vegetable Intake</label>
                                            <select id="ped-diet-veg" class="form-control"><option value="Adequate">Adequate</option><option value="Inadequate">Inadequate</option><option value="None">None</option></select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="ped-diet-fastfood">Fast Food</label>
                                            <select id="ped-diet-fastfood" class="form-control"><option value="Never">Never</option><option value="Rarely">Rarely</option><option value="Weekly">Weekly</option><option value="Daily">Daily</option></select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="ped-diet-sugary">Sugary Drinks</label>
                                            <select id="ped-diet-sugary" class="form-control"><option value="Never">Never</option><option value="Rarely">Rarely</option><option value="Weekly">Weekly</option><option value="Daily">Daily</option></select>
                                        </div>
                                    </div>
                                    <div class="mod-clinical-style-34">
                                        <div class="form-group"><label class="form-label" for="ped-diet-milk">Milk Intake</label><input type="text" id="ped-diet-milk" class="form-control"></div>
                                        <div class="form-group"><label class="form-label" for="ped-diet-water">Water Intake</label><input type="text" id="ped-diet-water" class="form-control"></div>
                                        <div class="form-group"><label class="form-label" for="ped-diet-juice">Juice Intake</label><input type="text" id="ped-diet-juice" class="form-control"></div>
                                    </div>

                                    <h5 class="mod-clinical-style-36">Appetite Assessment</h5>
                                    <div class="mod-clinical-style-33">
                                        <div class="form-group">
                                            <label class="form-label" for="ped-appetite">Appetite</label>
                                            <select id="ped-appetite" class="form-control"><option value="Good">Good</option><option value="Fair">Fair</option><option value="Poor">Poor</option></select>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Difficulty Eating</label>
                                            <label><input type="radio" name="ped_diff_eating" value="No" checked id="clinical-ped_diff_eating" aria-label="Ped diff eating"> No</label>
                                            <label><input type="radio" name="ped_diff_eating" value="Yes" id="clinical-ped_diff_eating" aria-label="Ped diff eating"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Difficulty Swallowing</label>
                                            <label><input type="radio" name="ped_diff_swallowing" value="No" checked id="clinical-ped_diff_swallowing" aria-label="Ped diff swallowing"> No</label>
                                            <label><input type="radio" name="ped_diff_swallowing" value="Yes" id="clinical-ped_diff_swallowing" aria-label="Ped diff swallowing"> Yes</label>
                                        </div>
                                    </div>
                                    <div class="mod-clinical-style-34">
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Vomiting After Feeding</label>
                                            <label><input type="radio" name="ped_vomiting" value="No" checked id="clinical-ped_vomiting" aria-label="Ped vomiting"> No</label>
                                            <label><input type="radio" name="ped_vomiting" value="Yes" id="clinical-ped_vomiting" aria-label="Ped vomiting"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Choking During Feeding</label>
                                            <label><input type="radio" name="ped_choking" value="No" checked id="clinical-ped_choking" aria-label="Ped choking"> No</label>
                                            <label><input type="radio" name="ped_choking" value="Yes" id="clinical-ped_choking" aria-label="Ped choking"> Yes</label>
                                        </div>
                                    </div>

                                    <h5 class="mod-clinical-style-36">Nutrition Risk & Supplements</h5>
                                    <div class="mod-clinical-style-37">
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Weight Loss</label>
                                            <label><input type="radio" name="ped_risk_weight_loss" value="No" checked id="clinical-ped_risk_weight_loss" aria-label="Ped risk weight loss"> No</label>
                                            <label><input type="radio" name="ped_risk_weight_loss" value="Yes" id="clinical-ped_risk_weight_loss" aria-label="Ped risk weight loss"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Poor Weight Gain</label>
                                            <label><input type="radio" name="ped_risk_poor_gain" value="No" checked id="clinical-ped_risk_poor_gain" aria-label="Ped risk poor gain"> No</label>
                                            <label><input type="radio" name="ped_risk_poor_gain" value="Yes" id="clinical-ped_risk_poor_gain" aria-label="Ped risk poor gain"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Food Allergy</label>
                                            <label><input type="radio" name="ped_risk_allergy" value="No" checked id="clinical-ped_risk_allergy" aria-label="Ped risk allergy"> No</label>
                                            <label><input type="radio" name="ped_risk_allergy" value="Yes" id="clinical-ped_risk_allergy" aria-label="Ped risk allergy"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Food Intolerance</label>
                                            <label><input type="radio" name="ped_risk_intolerance" value="No" checked id="clinical-ped_risk_intolerance" aria-label="Ped risk intolerance"> No</label>
                                            <label><input type="radio" name="ped_risk_intolerance" value="Yes" id="clinical-ped_risk_intolerance" aria-label="Ped risk intolerance"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Iron Def. Risk</label>
                                            <label><input type="radio" name="ped_risk_iron" value="No" checked id="clinical-ped_risk_iron" aria-label="Ped risk iron"> No</label>
                                            <label><input type="radio" name="ped_risk_iron" value="Yes" id="clinical-ped_risk_iron" aria-label="Ped risk iron"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Vit D Def. Risk</label>
                                            <label><input type="radio" name="ped_risk_vit_d" value="No" checked id="clinical-ped_risk_vit_d" aria-label="Ped risk vit d"> No</label>
                                            <label><input type="radio" name="ped_risk_vit_d" value="Yes" id="clinical-ped_risk_vit_d" aria-label="Ped risk vit d"> Yes</label>
                                        </div>
                                    </div>
                                    <div class="mod-clinical-style-15">
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Vitamin D Supp.</label>
                                            <label><input type="radio" name="ped_supp_vit_d" value="No" checked id="clinical-ped_supp_vit_d" aria-label="Ped supp vit d"> No</label>
                                            <label><input type="radio" name="ped_supp_vit_d" value="Yes" id="clinical-ped_supp_vit_d" aria-label="Ped supp vit d"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Iron Supp.</label>
                                            <label><input type="radio" name="ped_supp_iron" value="No" checked id="clinical-ped_supp_iron" aria-label="Ped supp iron"> No</label>
                                            <label><input type="radio" name="ped_supp_iron" value="Yes" id="clinical-ped_supp_iron" aria-label="Ped supp iron"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Multivitamin</label>
                                            <label><input type="radio" name="ped_supp_multi" value="No" checked id="clinical-ped_supp_multi" aria-label="Ped supp multi"> No</label>
                                            <label><input type="radio" name="ped_supp_multi" value="Yes" id="clinical-ped_supp_multi" aria-label="Ped supp multi"> Yes</label>
                                        </div>
                                        <div>
                                            <label class="form-label mod-clinical-style-35">Calcium Supp.</label>
                                            <label><input type="radio" name="ped_supp_calcium" value="No" checked id="clinical-ped_supp_calcium" aria-label="Ped supp calcium"> No</label>
                                            <label><input type="radio" name="ped_supp_calcium" value="Yes" id="clinical-ped_supp_calcium" aria-label="Ped supp calcium"> Yes</label>
                                        </div>
                                    </div>
                                    <div class="form-group mod-clinical-style-38"><label class="form-label" for="ped-supp-other">Other Supplement</label><input type="text" id="ped-supp-other" class="form-control"></div>

                                    <h5 class="mod-clinical-style-36">Nutrition Counseling & Recommendations</h5>
                                    <div class="mod-clinical-style-39">
                                        <div class="mod-clinical-style-40">
                                            <label><input type="checkbox" class="ped-counsel-checkbox" value="Balanced Diet Discussed" id="clinical-field-117" aria-label="clinical field 118"> Balanced Diet Discussed</label>
                                            <label><input type="checkbox" class="ped-counsel-checkbox" value="Healthy Eating Habits" id="clinical-field-118" aria-label="clinical field 119"> Healthy Eating Habits</label>
                                            <label><input type="checkbox" class="ped-counsel-checkbox" value="Reduce Sugary Drinks" id="clinical-field-119" aria-label="clinical field 120"> Reduce Sugary Drinks</label>
                                            <label><input type="checkbox" class="ped-counsel-checkbox" value="Increase Fruits" id="clinical-field-120" aria-label="clinical field 121"> Increase Fruits</label>
                                            <label><input type="checkbox" class="ped-counsel-checkbox" value="Increase Vegetables" id="clinical-field-121" aria-label="clinical field 122"> Increase Vegetables</label>
                                            <label><input type="checkbox" class="ped-counsel-checkbox" value="Hydration Education" id="clinical-field-122" aria-label="clinical field 123"> Hydration Education</label>
                                            <label><input type="checkbox" class="ped-counsel-checkbox" value="Portion Control" id="clinical-field-123" aria-label="clinical field 124"> Portion Control</label>
                                            <label><input type="checkbox" class="ped-counsel-checkbox" value="Breastfeeding Counseling" id="clinical-field-124" aria-label="clinical field 125"> Breastfeeding Counseling</label>
                                            <label><input type="checkbox" class="ped-counsel-checkbox" value="Formula Feeding Counseling" id="clinical-field-125" aria-label="clinical field 126"> Formula Feeding Counseling</label>
                                        </div>
                                        <div class="mod-clinical-style-40">
                                            <label><input type="checkbox" class="ped-nutri-rec-checkbox" value="Continue Current Diet" id="clinical-field-126" aria-label="clinical field 127"> Continue Current Diet</label>
                                            <label><input type="checkbox" class="ped-nutri-rec-checkbox" value="Nutrition Follow-up" id="clinical-field-127" aria-label="clinical field 128"> Nutrition Follow-up</label>
                                            <label><input type="checkbox" class="ped-nutri-rec-checkbox" value="Dietitian Referral" id="clinical-field-128" aria-label="clinical field 129"> Dietitian Referral</label>
                                            <label><input type="checkbox" class="ped-nutri-rec-checkbox" value="Feeding Therapy Referral" id="clinical-field-129" aria-label="clinical field 130"> Feeding Therapy Referral</label>
                                            <label><input type="checkbox" class="ped-nutri-rec-checkbox" value="Allergy Evaluation" id="clinical-field-130" aria-label="clinical field 131"> Allergy Evaluation</label>
                                            <div class="form-group mod-clinical-style-38"><label class="form-label" for="ped-nutri-rec-other">Other Recommendation</label><input type="text" id="ped-nutri-rec-other" class="form-control"></div>
                                        </div>
                                    </div>
                                    </div>
                                </form>
                            </div> </div>

                        <!-- Accordion Section: Pediatrics – Newborn Visit -->
                        <div class="accordion-item peds-only hidden">
                            <button class="accordion-header" aria-expanded="false" aria-controls="accordion-peds-newborn" id="accordion-peds-newborn-btn">CLINIC VISITS • PROGRESS NOTE NEWBORN VISIT</button>
                            <div id="accordion-peds-newborn" class="accordion-content hidden" role="region" aria-labelledby="accordion-peds-newborn-btn">
                                <form class="mod-clinical-style-18" id="peds-newborn-form" novalidate>
                                    <div class="mod-clinical-style-19">
                                        <!-- Date Header -->
                                        <div class="form-group" style="max-width: 250px; margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">Date</label>
                                            <input type="date" id="peds-nb-visit-date" class="form-control">
                                        </div>

                                        <!-- 1. IDENTIFICATION -->
                                        <h4 class="form-section-title">── IDENTIFICATION ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">ACCOMPANIED BY:</label>
                                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-acc-mom"> Mom</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-acc-dad"> Dad</label>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-acc-other-chk"> Other</label>
                                                    <input type="text" id="peds-nb-acc-other-txt" class="form-control" style="width: 200px;" placeholder="Specify relationship...">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 2. BIRTH HISTORY -->
                                        <h4 class="form-section-title">── BIRTH HISTORY ──</h4>
                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 12px;">
                                            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                                                <label class="form-label" style="margin: 0; min-width: 40px;">GA</label>
                                                <input type="text" id="peds-nb-ga-weeks" class="form-control" style="width: 100px;" placeholder="e.g. 39">
                                                <span>weeks</span>
                                            </div>
                                            <div class="form-group" style="display: flex; align-items: center; gap: 16px;">
                                                <label class="form-label" style="margin: 0;">Delivery:</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-del-vaginal"> Vaginal</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-del-cs"> C/S</label>
                                            </div>
                                        </div>

                                        <div style="margin-bottom: 12px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 8px;">Maternal Hx:</label>
                                            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                                                <span>Age</span> <input type="text" id="peds-nb-mat-age" class="form-control" style="width: 60px;" placeholder="Yrs"> <span>year old</span>
                                                <span style="margin-left: 12px;">G</span> <input type="text" id="peds-nb-mat-g" class="form-control" style="width: 50px;">
                                                <span>T</span> <input type="text" id="peds-nb-mat-t" class="form-control" style="width: 50px;">
                                                <span>P</span> <input type="text" id="peds-nb-mat-p" class="form-control" style="width: 50px;">
                                                <span>A</span> <input type="text" id="peds-nb-mat-a" class="form-control" style="width: 50px;">
                                                <span>L</span> <input type="text" id="peds-nb-mat-l" class="form-control" style="width: 50px;">
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 12px;">
                                            <div class="form-group">
                                                <label class="form-label">Blood type</label>
                                                <input type="text" id="peds-nb-blood-type" class="form-control" placeholder="e.g. O+">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Ab screen</label>
                                                <input type="text" id="peds-nb-ab-screen" class="form-control" placeholder="Antibody screen...">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">TB screening</label>
                                                <input type="text" id="peds-nb-tb-screen" class="form-control" placeholder="TB screening notes...">
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <label class="form-label" style="font-weight: 600;">Labs:</label>
                                            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-labs-normal"> All Normal</label>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-labs-except-chk"> Normal Except</label>
                                                    <input type="text" id="peds-nb-labs-except-txt" class="form-control" style="width: 250px;" placeholder="Specify lab exceptions...">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label">Pregnancy or delivery complications:</label>
                                            <input type="text" id="peds-nb-preg-complications" class="form-control" placeholder="Describe complications if any...">
                                        </div>

                                        <!-- 3. CONCERNS -->
                                        <h4 class="form-section-title">── CONCERNS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-nb-concerns" class="form-control" rows="2" placeholder="Parental or provider concerns..."></textarea>
                                        </div>

                                        <!-- 4. NUTRITION -->
                                        <h4 class="form-section-title">── NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-bf-chk"> Breast Feeding</label>
                                                <input type="text" id="peds-nb-bf-min" class="form-control" style="width: 70px;" placeholder="min"> <span>min per side every</span>
                                                <input type="text" id="peds-nb-bf-hrs" class="form-control" style="width: 70px;" placeholder="hrs"> <span>hours</span>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pump-chk"> Pumping every</label>
                                                <input type="text" id="peds-nb-pump-hrs" class="form-control" style="width: 70px;" placeholder="hrs"> <span>hours</span>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-formula-chk"> Formula type</label>
                                                <input type="text" id="peds-nb-formula-type" class="form-control" style="width: 180px;" placeholder="Brand/Type">
                                                <span>Amount</span>
                                                <input type="text" id="peds-nb-formula-amt" class="form-control" style="width: 80px;" placeholder="mL"> <span>mL every</span>
                                                <input type="text" id="peds-nb-formula-hrs" class="form-control" style="width: 70px;" placeholder="hrs"> <span>hours</span>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-nutr-prob-chk"> Problems</label>
                                                <input type="text" id="peds-nb-nutr-prob-txt" class="form-control" style="flex: 1; min-width: 250px;" placeholder="Describe feeding problems...">
                                            </div>
                                        </div>

                                        <!-- 5. ELIMINATION -->
                                        <h4 class="form-section-title">── ELIMINATION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-stools-chk"> Soft stools</label>
                                                <input type="text" id="peds-nb-stools-count" class="form-control" style="width: 70px;" placeholder="cnt"> <span>per day</span>
                                                <span style="margin-left: 12px;">color</span>
                                                <input type="text" id="peds-nb-stools-color" class="form-control" style="width: 150px;" placeholder="Stool color">
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-wet-chk"> Wet diapers</label>
                                                <input type="text" id="peds-nb-wet-count" class="form-control" style="width: 70px;" placeholder="cnt"> <span>per day</span>
                                            </div>
                                        </div>

                                        <!-- 6. SLEEP -->
                                        <h4 class="form-section-title">── SLEEP ──</h4>
                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;">
                                            <div class="form-group">
                                                <label class="form-label">Location</label>
                                                <input type="text" id="peds-nb-sleep-loc" class="form-control" placeholder="Crib, Bassinet, Room-sharing...">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Position</label>
                                                <input type="text" id="peds-nb-sleep-pos" class="form-control" placeholder="Back, Side...">
                                            </div>
                                        </div>

                                        <!-- 7. FAMILY STRUCTURE, PRIMARY CARETAKER(S) -->
                                        <h4 class="form-section-title">── FAMILY STRUCTURE, PRIMARY CARETAKER(S) ──</h4>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <label class="form-label">Source of social/emotional support:</label>
                                            <input type="text" id="peds-nb-social-support" class="form-control" placeholder="Partner, Family, Friends...">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label">Community resources (e.g., Pre-to-3):</label>
                                            <input type="text" id="peds-nb-community-res" class="form-control" placeholder="Resource details...">
                                        </div>

                                        <!-- 8. SOCIAL / ENVIRONMENTAL SCREENING -->
                                        <h4 class="form-section-title">── SOCIAL / ENVIRONMENTAL SCREENING ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 10px;">Check if discussed and negative</p>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-soc-food-insec"> <strong>Food insecurity</strong> — In the past year, have you run out of food before you could buy more - or worried about running out of food?</label>
                                            
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-soc-housing-insec"> <strong>Housing insecurity</strong></label>
                                                <div style="margin-left: 24px; display: flex; flex-direction: column; gap: 4px; margin-top: 4px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-soc-house-moved"> ○ Moved more than once in past year</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-soc-house-density"> ○ &gt;2 people/bedroom</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-soc-house-families"> ○ &gt;1 family/home</label>
                                                </div>
                                            </div>

                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-soc-dv"> <strong>Domestic violence</strong> — In the past year, have you felt afraid of your partner?</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-soc-guns"> <strong>Guns</strong> — Are there guns in your home?</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-soc-tobacco"> <strong>Tobacco</strong> — Does anyone smoke where you live?</label>
                                        </div>

                                        <!-- 9. PRIMARY LANGUAGE -->
                                        <h4 class="form-section-title">── PRIMARY LANGUAGE ──</h4>
                                        <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap; margin-bottom: 16px;">
                                            <input type="text" id="peds-nb-primary-lang" class="form-control" style="width: 250px;" placeholder="Primary Language...">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-lang-interp"> Interpreter used</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-lang-prov-speaks"> Provider speaks language</label>
                                        </div>

                                        <!-- 10. GROWTH -->
                                        <h4 class="form-section-title">── GROWTH ──</h4>
                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="width: 130px; margin: 0;">Birth Weight</label>
                                                <input type="text" id="peds-nb-bw-g" class="form-control" style="width: 90px;" placeholder="g"> <span>g</span>
                                                <input type="text" id="peds-nb-bw-pct" class="form-control" style="width: 70px;" placeholder="%"> <span>%</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="width: 130px; margin: 0;">D/C Weight</label>
                                                <input type="text" id="peds-nb-dcw-g" class="form-control" style="width: 90px;" placeholder="g"> <span>g</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="width: 130px; margin: 0;">D/C date</label>
                                                <input type="date" id="peds-nb-dc-date" class="form-control" style="width: 160px;">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="width: 130px; margin: 0;">Today's weight</label>
                                                <input type="text" id="peds-nb-today-wt-g" class="form-control" style="width: 90px;" placeholder="g"> <span>g</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="width: 130px; margin: 0;">Change from birthwt</label>
                                                <input type="text" id="peds-nb-wt-change-pct" class="form-control" style="width: 70px;" placeholder="%"> <span>%</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="width: 130px; margin: 0;">OFC</label>
                                                <input type="text" id="peds-nb-ofc-cm" class="form-control" style="width: 90px;" placeholder="cm"> <span>cm</span>
                                                <input type="text" id="peds-nb-ofc-pct" class="form-control" style="width: 70px;" placeholder="%"> <span>%</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="width: 130px; margin: 0;">Length</label>
                                                <input type="text" id="peds-nb-len-cm" class="form-control" style="width: 90px;" placeholder="cm"> <span>cm</span>
                                                <input type="text" id="peds-nb-len-pct" class="form-control" style="width: 70px;" placeholder="%"> <span>%</span>
                                            </div>
                                        </div>

                                        <!-- 11. NEWBORN SCREEN -->
                                        <h4 class="form-section-title">── NEWBORN SCREEN ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-nbs-pending"> Pending</label>
                                        </div>

                                        <!-- 12. ALGO -->
                                        <h4 class="form-section-title">── ALGO ──</h4>
                                        <div style="display: flex; gap: 24px; align-items: center; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-algo-passed"> Passed</label>
                                                <span>L</span> <input type="checkbox" id="peds-nb-algo-passed-l">
                                                <span>R</span> <input type="checkbox" id="peds-nb-algo-passed-r">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-algo-referred"> Referred</label>
                                                <span>L</span> <input type="checkbox" id="peds-nb-algo-ref-l">
                                                <span>R</span> <input type="checkbox" id="peds-nb-algo-ref-r">
                                            </div>
                                        </div>

                                        <!-- 13. BILIRUBIN -->
                                        <h4 class="form-section-title">── BILIRUBIN ──</h4>
                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="margin: 0;">24 hour Bili</label>
                                                <input type="text" id="peds-nb-bili-24h" class="form-control" style="width: 100px;" placeholder="mg/dl"> <span>mg/dl</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="margin: 0;">Bhutani Risk zone</label>
                                                <input type="text" id="peds-nb-bili-bhutani" class="form-control" placeholder="Low, Int, High...">
                                            </div>
                                        </div>

                                        <!-- 14. MEDS -->
                                        <h4 class="form-section-title">── MEDS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-meds-none"> None</label>
                                        </div>

                                        <!-- 15. ALLERGIES -->
                                        <h4 class="form-section-title">── ALLERGIES ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-allergies-none"> None known</label>
                                        </div>

                                        <!-- 16. IMMUNIZATIONS -->
                                        <h4 class="form-section-title">── IMMUNIZATIONS ──</h4>
                                        <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-imm-none"> None</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-imm-hepb1"> Hep B#1</label>
                                        </div>

                                        <!-- 17. DEVELOPMENT -->
                                        <h4 class="form-section-title">── DEVELOPMENT ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-dev-startles"> Startles</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-dev-regards-face"> Regards face</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-dev-tracks-90"> Tracks 90 degrees</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-dev-symm-mvmts"> Symmetrical movements</label>
                                        </div>

                                        <!-- 18. FAMILY HISTORY -->
                                        <h4 class="form-section-title">── FAMILY HISTORY ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; max-width: 500px;">
                                                <span>Unexplained infant/childhood deaths</span>
                                                <div style="display: flex; gap: 12px;">
                                                    <label><input type="radio" name="nb_fh_deaths" id="peds-nb-fh-deaths-y" value="Y"> Y</label>
                                                    <label><input type="radio" name="nb_fh_deaths" id="peds-nb-fh-deaths-n" value="N"> N</label>
                                                </div>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; max-width: 500px;">
                                                <span>Immunosuppressed family members</span>
                                                <div style="display: flex; gap: 12px;">
                                                    <label><input type="radio" name="nb_fh_immuno" id="peds-nb-fh-immuno-y" value="Y"> Y</label>
                                                    <label><input type="radio" name="nb_fh_immuno" id="peds-nb-fh-immuno-n" value="N"> N</label>
                                                </div>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; max-width: 500px;">
                                                <span>Atopy</span>
                                                <div style="display: flex; gap: 12px;">
                                                    <label><input type="radio" name="nb_fh_atopy" id="peds-nb-fh-atopy-y" value="Y"> Y</label>
                                                    <label><input type="radio" name="nb_fh_atopy" id="peds-nb-fh-atopy-n" value="N"> N</label>
                                                </div>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; max-width: 500px;">
                                                <span>TB: disease, treatment or positive test</span>
                                                <div style="display: flex; gap: 12px;">
                                                    <label><input type="radio" name="nb_fh_tb" id="peds-nb-fh-tb-y" value="Y"> Y</label>
                                                    <label><input type="radio" name="nb_fh_tb" id="peds-nb-fh-tb-n" value="N"> N</label>
                                                </div>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; max-width: 500px;">
                                                <span>Significant illnesses</span>
                                                <div style="display: flex; gap: 12px;">
                                                    <label><input type="radio" name="nb_fh_illness" id="peds-nb-fh-illness-y" value="Y"> Y</label>
                                                    <label><input type="radio" name="nb_fh_illness" id="peds-nb-fh-illness-n" value="N"> N</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 19. PHYSICAL EXAM -->
                                        <h4 class="form-section-title">── PHYSICAL EXAM ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 10px;">Check √ if Normal OR Describe if Abnormal</p>
                                        
                                        <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 12px; background: #f1f5f9; padding: 8px 12px; border-radius: 4px;">
                                            <strong>Vital Signs</strong>
                                            <span>Temp</span> <input type="text" id="peds-nb-pe-vt-temp" class="form-control" style="width: 70px;">
                                            <span>HR</span> <input type="text" id="peds-nb-pe-vt-hr" class="form-control" style="width: 70px;">
                                            <span>RR</span> <input type="text" id="peds-nb-pe-vt-rr" class="form-control" style="width: 70px;">
                                        </div>

                                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pe-gen-chk"> <strong>General:</strong> alert, pink, well appearing</label>
                                                <input type="text" id="peds-nb-pe-gen-abn" class="form-control" style="margin-top: 4px;" placeholder="Abnormal Description...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pe-skin-chk"> <strong>Skin:</strong> no jaundice, rashes or lesions</label>
                                                <input type="text" id="peds-nb-pe-skin-abn" class="form-control" style="margin-top: 4px;" placeholder="Abnormal Description...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pe-heent-chk"> <strong>HEENT:</strong> AFOSF, bilateral RR, intact palate, no ear pits/tags</label>
                                                <input type="text" id="peds-nb-pe-heent-abn" class="form-control" style="margin-top: 4px;" placeholder="Abnormal Description...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pe-neck-chk"> <strong>Neck:</strong> supple, no torticollis or lymphadenopathy</label>
                                                <input type="text" id="peds-nb-pe-neck-abn" class="form-control" style="margin-top: 4px;" placeholder="Abnormal Description...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pe-cv-chk"> <strong>CV:</strong> RRR, no murmurs, normal S1, S2, femoral pulses</label>
                                                <input type="text" id="peds-nb-pe-cv-abn" class="form-control" style="margin-top: 4px;" placeholder="Abnormal Description...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pe-chest-chk"> <strong>Chest:</strong> clear, no grunting/flaring/retractions</label>
                                                <input type="text" id="peds-nb-pe-chest-abn" class="form-control" style="margin-top: 4px;" placeholder="Abnormal Description...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pe-abd-chk"> <strong>Abdomen:</strong> soft, no HSM or masses, umbilical stump clean/dry</label>
                                                <input type="text" id="peds-nb-pe-abd-abn" class="form-control" style="margin-top: 4px;" placeholder="Abnormal Description...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pe-gu-chk"> <strong>GU:</strong> normal male/female, testes descended, no hypospadius</label>
                                                <input type="text" id="peds-nb-pe-gu-abn" class="form-control" style="margin-top: 4px;" placeholder="Abnormal Description...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pe-back-chk"> <strong>Back:</strong> intact spine, no sacral dimple/pit</label>
                                                <input type="text" id="peds-nb-pe-back-abn" class="form-control" style="margin-top: 4px;" placeholder="Abnormal Description...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pe-ext-chk"> <strong>Ext/Hips:</strong> well perfused, stable hips, intact clavicles</label>
                                                <input type="text" id="peds-nb-pe-ext-abn" class="form-control" style="margin-top: 4px;" placeholder="Abnormal Description...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-pe-neuro-chk"> <strong>Neuro:</strong> normal Moro, grasp, suck, tone</label>
                                                <input type="text" id="peds-nb-pe-neuro-abn" class="form-control" style="margin-top: 4px;" placeholder="Abnormal Description...">
                                            </div>
                                        </div>

                                        <!-- 20. ASSESSMENT -->
                                        <h4 class="form-section-title">── ASSESSMENT ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-ass-well-child"> Well Child</label>
                                                <input type="text" id="peds-nb-ass-well-days" class="form-control" style="width: 70px;" placeholder="days"> <span>days old</span>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-ass-feeding-well"> Feeding Well</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-ass-no-jaundice"> No Jaundice</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-ass-jaundice"> Jaundice</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <input type="checkbox" id="peds-nb-ass-other1-chk">
                                                <input type="text" id="peds-nb-ass-other1-txt" class="form-control" placeholder="Other Assessment 1...">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <input type="checkbox" id="peds-nb-ass-other2-chk">
                                                <input type="text" id="peds-nb-ass-other2-txt" class="form-control" placeholder="Other Assessment 2...">
                                            </div>
                                        </div>

                                        <!-- 21. EDINBURGH POSTPARTUM DEPRESSION SCREEN -->
                                        <h4 class="form-section-title">── EDINBURGH POSTPARTUM DEPRESSION SCREEN ──</h4>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                            <strong>Score</strong>
                                            <input type="text" id="peds-nb-epds-score" class="form-control" style="width: 100px;" placeholder="Score">
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-epds-0-8"> Score 0-8: No action needed</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-epds-9-12"> Score 9-12: Establish plan for follow up, refer to community resources</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-epds-13"> Score ≥ 13: Refer for further assessment, treatment</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-epds-item10"> If ≥1 on item 10: Establish safety plan and place copy in chart</label>
                                        </div>

                                        <!-- 22. PLAN -->
                                        <h4 class="form-section-title">── PLAN ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-plan-freq-feedings"> Continue frequent feedings</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-plan-ant-guidance"> Anticipatory Guidance</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-plan-lactation-consult"> Lactation consult</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-plan-breast-pump"> Breast pump Rx</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-plan-tsb"> Total Serum Bilirubin</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-plan-direct-bili"> Direct Bilirubin</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-plan-coombs"> Blood Type/Coombs</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-plan-tc-bili-chk"> TC bili</label>
                                                <input type="text" id="peds-nb-plan-tc-bili-val" class="form-control" style="width: 120px;" placeholder="TC Bili val">
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-plan-vit-d"> Vitamin D supplementation if breastfeeding or partially breastfeeding</label>
                                        </div>

                                        <!-- 23. RETURN TO CARE -->
                                        <h4 class="form-section-title">── RETURN TO CARE ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 10px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-ret-days-chk"> Return in</label>
                                                <input type="text" id="peds-nb-ret-days" class="form-control" style="width: 70px;" placeholder="days"> <span>days to recheck</span>
                                                <input type="text" id="peds-nb-ret-days-reason" class="form-control" style="width: 200px;" placeholder="Reason for recheck...">
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-ret-6-8wks"> Return to clinic in 6-8 weeks for exam and vaccines</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-nb-ret-sooner"> Return sooner for fever &gt; 100.4, poor feeding, poor urination, or acting sick</label>
                                        </div>
                                    </div>
</form>
                            </div>
                        </div>

                        <!-- Accordion Section: One Month Well Child Visit -->
                        <div class="accordion-item peds-only hidden">
                            <button class="accordion-header" aria-expanded="false" aria-controls="accordion-peds-one-month" id="accordion-peds-one-month-btn">
                                CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE ONE MONTH WELL CHILD VISIT
                            </button>
                            <div id="accordion-peds-one-month" class="accordion-content hidden" role="region" aria-labelledby="accordion-peds-one-month-btn">
                                <form class="mod-clinical-style-18" id="peds-one-month-form" novalidate>
                                    <div class="mod-clinical-style-19">
                                        <!-- Date Header -->
                                        <div class="form-group" style="max-width: 250px; margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">Date</label>
                                            <input type="date" id="peds-om-visit-date" class="form-control">
                                        </div>

                                        <!-- 1. IDENTIFICATION -->
                                        <h4 class="form-section-title">── IDENTIFICATION ──</h4>
                                        <div style="margin-bottom: 12px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                            <label class="form-label" style="margin: 0; min-width: 90px;">LANGUAGE:</label>
                                            <input type="text" id="peds-om-language" class="form-control" style="width: 250px;" placeholder="Primary Language...">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-lang-interp"> Interpreter</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-lang-prov-speaks"> Provider speaks language</label>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">ACCOMPANIED BY:</label>
                                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-acc-mom"> Mom</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-acc-dad"> Dad</label>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-om-acc-other-chk"> Other:</label>
                                                    <input type="text" id="peds-om-acc-other-txt" class="form-control" style="width: 200px;" placeholder="Specify relationship...">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Wt.</strong>
                                                    <input type="text" id="peds-om-wt-kg" class="form-control" style="width: 80px;" placeholder="kg"> <span>kg</span>
                                                    <span>(</span><input type="text" id="peds-om-wt-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-wt-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Ht.</strong>
                                                    <input type="text" id="peds-om-ht-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-om-ht-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-ht-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>HC</strong>
                                                    <input type="text" id="peds-om-hc-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-om-hc-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-hc-track"> tracking</label>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">CONCERNS:</label>
                                            <textarea id="peds-om-concerns" class="form-control" rows="2" placeholder="Parental or provider concerns..."></textarea>
                                        </div>

                                        <!-- 2. INTERVAL HISTORY -->
                                        <h4 class="form-section-title">── INTERVAL HISTORY ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 6px;">(significant changes, illnesses, events since last visit)</p>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <textarea id="peds-om-interval-history" class="form-control" rows="2" placeholder="Interval history notes..."></textarea>
                                        </div>
                                        <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px; background: #f1f5f9; padding: 8px 12px; border-radius: 4px;">
                                            <span>T</span> <input type="text" id="peds-om-temp" class="form-control" style="width: 70px;">
                                            <span>P</span> <input type="text" id="peds-om-pulse" class="form-control" style="width: 70px;">
                                            <span>RR</span> <input type="text" id="peds-om-rr" class="form-control" style="width: 70px;">
                                            <span>BP</span> <input type="text" id="peds-om-bp" class="form-control" style="width: 90px;">
                                        </div>

                                        <!-- 3. FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS -->
                                        <h4 class="form-section-title">── FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-om-followup-problems" class="form-control" rows="2" placeholder="Follow up problem notes..."></textarea>
                                        </div>

                                        <!-- 4. ADJUSTMENT TO BABY -->
                                        <h4 class="form-section-title">── ADJUSTMENT TO BABY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-om-adj-baby" class="form-control" rows="2" placeholder="Family adjustment notes..."></textarea>
                                        </div>

                                        <!-- 5. MEDICATIONS -->
                                        <h4 class="form-section-title">── MEDICATIONS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-meds-none"> None</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-meds-vitd-chk"> Vitamin D</label>
                                                <input type="text" id="peds-om-meds-vitd-txt" class="form-control" style="width: 250px;" placeholder="Dosage / Details (e.g. 400 IU/day)...">
                                            </div>
                                        </div>

                                        <!-- 6. NUTRITION / BREASTFEEDING -->
                                        <h4 class="form-section-title">── NUTRITION / BREASTFEEDING ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-nutr-concerns-chk"> <strong>Concerns/Changes from previous feeding plan:</strong></label>
                                                <input type="text" id="peds-om-nutr-concerns-txt" class="form-control" style="margin-top: 4px;" placeholder="Describe changes/concerns...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-nutr-plan-chk"> <strong>Current feeding plan</strong> (breastfeeding, expressed breastmilk, formula):</label>
                                                <input type="text" id="peds-om-nutr-plan-txt" class="form-control" style="margin-top: 4px;" placeholder="Describe current plan...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-nutr-formula-chk"> <strong>Formula type, if using formula:</strong></label>
                                                <input type="text" id="peds-om-nutr-formula-txt" class="form-control" style="margin-top: 4px;" placeholder="Formula type...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-nutr-timing-chk"> <strong>Timing, duration, amount of each feeding:</strong></label>
                                                <input type="text" id="peds-om-nutr-timing-txt" class="form-control" style="margin-top: 4px;" placeholder="Timing and amounts...">
                                            </div>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-nutr-prep-chk"> <strong>Feeding plans/preparation if returning to work, school:</strong></label>
                                                <input type="text" id="peds-om-nutr-prep-txt" class="form-control" style="margin-top: 4px;" placeholder="Work/school return plans...">
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-nutr-pump-support"> Need for a breast pump or lactation support</label>
                                        </div>

                                        <!-- 7. ALLERGIES / VACCINE REACTIONS -->
                                        <h4 class="form-section-title">── ALLERGIES / VACCINE REACTIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-allergies-none"> No known drug, food, or environmental allergies</label>
                                        </div>

                                        <!-- 8. ELIMINATION -->
                                        <h4 class="form-section-title">── ELIMINATION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-elim-urine-stream"> Straight urine stream in boys</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-elim-soft-stools-chk"> Soft stools</label>
                                                <input type="text" id="peds-om-elim-soft-stools-cnt" class="form-control" style="width: 70px;" placeholder="cnt"> <span># stools/day</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-elim-norm-urine-chk"> Normal urination</label>
                                                <input type="text" id="peds-om-elim-norm-urine-cnt" class="form-control" style="width: 70px;" placeholder="cnt"> <span># wet diapers/day</span>
                                            </div>
                                        </div>

                                        <!-- 9. IMMUNIZATIONS -->
                                        <h4 class="form-section-title">── IMMUNIZATIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-imm-up-to-date"> Record reviewed - up to date</label>
                                        </div>

                                        <!-- 10. PAST MEDICAL HISTORY -->
                                        <h4 class="form-section-title">── PAST MEDICAL HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-pmh-full-term"> Full Term</label>
                                        </div>

                                        <!-- 11. FAMILY HISTORY -->
                                        <h4 class="form-section-title">── FAMILY HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-om-family-history" class="form-control" rows="2" placeholder="Family history notes..."></textarea>
                                        </div>

                                        <!-- 12. FAMILY STRUCTURE, PRIMARY CARETAKER(S) -->
                                        <h4 class="form-section-title">── FAMILY STRUCTURE, PRIMARY CARETAKER(S) ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-om-family-structure" class="form-control" rows="2" placeholder="Family structure details..."></textarea>
                                        </div>

                                        <!-- 13. SOCIAL SUPPORTS -->
                                        <h4 class="form-section-title">── SOCIAL SUPPORTS ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 6px;">(family, friends, church, etc. to help avoid isolation)</p>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-om-social-supports" class="form-control" rows="2" placeholder="Social support details..."></textarea>
                                        </div>

                                        <!-- 14. PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION -->
                                        <h4 class="form-section-title">── PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-om-pregnancy-plans" class="form-control" rows="2" placeholder="Pregnancy spacing & contraception plans..."></textarea>
                                        </div>

                                        <!-- 15. POST-PARTUM VISIT SCHEDULED -->
                                        <h4 class="form-section-title">── POST-PARTUM VISIT SCHEDULED ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <input type="text" id="peds-om-postpartum-visit" class="form-control" placeholder="Post-partum visit details...">
                                        </div>

                                        <!-- 16. SLEEP -->
                                        <h4 class="form-section-title">── SLEEP ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>Position/location:</span>
                                                <input type="text" id="peds-om-sleep-pos-loc" class="form-control" style="flex: 1; max-width: 350px;" placeholder="Back, crib, bassinet...">
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-sleep-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-sleep-appr-amt"> Appropriate amount (14-16 hours per day, including naps)</label>
                                        </div>

                                        <!-- 17. SOCIAL / ENVIRONMENTAL SCREENING -->
                                        <h4 class="form-section-title">── SOCIAL / ENVIRONMENTAL SCREENING ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 10px;">Check if discussed and negative</p>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-soc-food-insec"> <strong>Food insecurity:</strong> In the past year, have you run out of food before you could buy more - or worried about running out of food?</label>
                                            
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-om-soc-housing-insec"> <strong>Housing insecurity:</strong> (positive if any one of the following are true)</label>
                                                <div style="margin-left: 24px; display: flex; flex-direction: column; gap: 4px; margin-top: 4px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-om-soc-house-moved"> ○ Moved more than once in past year</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-om-soc-house-density"> ○ &gt;2 people/bedroom</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-om-soc-house-families"> ○ &gt;1 family/home</label>
                                                </div>
                                            </div>

                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-soc-dv"> <strong>Domestic violence:</strong> In the past year, have you felt afraid of your partner?</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-soc-guns"> <strong>Guns:</strong> Are there guns in your home?</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-soc-tobacco"> <strong>Tobacco:</strong> Does anyone smoke where you live?</label>
                                        </div>

                                        <!-- 18. DEVELOPMENTAL SURVEILLANCE -->
                                        <h4 class="form-section-title">── DEVELOPMENTAL SURVEILLANCE ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-dev-turns-head"> Turns head when prone</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-dev-moves-limbs"> Moves arms and legs symmetrically</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-dev-responds-sound"> Responds to sound</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-om-dev-focuses-eyes"> Focuses eyes on objects 8-12 inches away</label>
                                        </div>

                                        <!-- 19. TB RISK SCREENING -->
                                        <h4 class="form-section-title">── TB RISK SCREENING ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 6px;">(see pre-visit questionnaire)</p>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-om-tb-risk" class="form-control" rows="2" placeholder="TB risk screening notes..."></textarea>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Accordion Section: CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE TWO MONTH WELL CHILD VISIT -->
                        <div class="accordion-item peds-only hidden">
                            <button class="accordion-header" aria-expanded="false" aria-controls="accordion-peds-two-month" id="accordion-peds-two-month-btn">
                                CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE TWO MONTH WELL CHILD VISIT
                            </button>
                            <div id="accordion-peds-two-month" class="accordion-content hidden" role="region" aria-labelledby="accordion-peds-two-month-btn">
                                <form class="mod-clinical-style-18" id="peds-two-month-form" novalidate>
                                    <div class="mod-clinical-style-19">
                                        <!-- Date Header -->
                                        <div class="form-group" style="max-width: 250px; margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">Date</label>
                                            <input type="date" id="peds-2m-visit-date" class="form-control">
                                        </div>

                                        <!-- 1. IDENTIFICATION -->
                                        <h4 class="form-section-title">── IDENTIFICATION ──</h4>
                                        <div style="margin-bottom: 12px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                            <label class="form-label" style="margin: 0; min-width: 90px;">LANGUAGE:</label>
                                            <input type="text" id="peds-2m-language" class="form-control" style="width: 250px;" placeholder="Primary Language...">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-lang-interp"> Interpreter</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-lang-prov-speaks"> Provider speaks language</label>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">ACCOMPANIED BY:</label>
                                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-acc-mom"> Mom</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-acc-dad"> Dad</label>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-acc-other-chk"> Other:</label>
                                                    <input type="text" id="peds-2m-acc-other-txt" class="form-control" style="width: 200px;" placeholder="Specify relationship...">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Wt</strong>
                                                    <input type="text" id="peds-2m-wt-kg" class="form-control" style="width: 80px;" placeholder="kg"> <span>kg</span>
                                                    <span>(</span><input type="text" id="peds-2m-wt-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-wt-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Ht</strong>
                                                    <input type="text" id="peds-2m-ht-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-2m-ht-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-ht-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>HC</strong>
                                                    <input type="text" id="peds-2m-hc-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-2m-hc-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-hc-track"> tracking</label>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">CONCERNS:</label>
                                            <textarea id="peds-2m-concerns" class="form-control" rows="2" placeholder="Parental or provider concerns..."></textarea>
                                        </div>

                                        <!-- 2. INTERVAL HISTORY -->
                                        <h4 class="form-section-title">── INTERVAL HISTORY ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 6px;">(significant changes, illnesses, events since last visit)</p>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <textarea id="peds-2m-interval-history" class="form-control" rows="2" placeholder="Interval history notes..."></textarea>
                                        </div>
                                        <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px; background: #f1f5f9; padding: 8px 12px; border-radius: 4px;">
                                            <span>T</span> <input type="text" id="peds-2m-temp" class="form-control" style="width: 70px;">
                                            <span>P</span> <input type="text" id="peds-2m-pulse" class="form-control" style="width: 70px;">
                                            <span>RR</span> <input type="text" id="peds-2m-rr" class="form-control" style="width: 70px;">
                                            <span>BP</span> <input type="text" id="peds-2m-bp" class="form-control" style="width: 90px;">
                                        </div>

                                        <!-- 3. FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS -->
                                        <h4 class="form-section-title">── FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-2m-followup-problems" class="form-control" rows="2" placeholder="Follow up problem notes..."></textarea>
                                        </div>

                                        <!-- 4. MEDICATIONS -->
                                        <h4 class="form-section-title">── MEDICATIONS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-meds-none"> None</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-meds-vitd-chk"> Vitamin D</label>
                                                <input type="text" id="peds-2m-meds-vitd-txt" class="form-control" style="width: 250px;" placeholder="Dosage / Details (e.g. 400 IU/day)...">
                                            </div>
                                        </div>

                                        <!-- 5. NUTRITION -->
                                        <h4 class="form-section-title">── NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-nutr-bf-chk"> Breast feeding:</label>
                                                <span>Times per day</span>
                                                <input type="text" id="peds-2m-nutr-bf-times" class="form-control" style="width: 100px;" placeholder="times/day">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-nutr-formula-chk"> Formula type:</label>
                                                <input type="text" id="peds-2m-nutr-formula-type" class="form-control" style="width: 200px;" placeholder="Formula type">
                                                <span>Amount</span>
                                                <input type="text" id="peds-2m-nutr-formula-amt" class="form-control" style="width: 80px;" placeholder="oz"> <span>oz/day</span>
                                            </div>
                                            
                                        </div>

                                        <!-- 6. ALLERGIES / VACCINE REACTIONS -->
                                        <h4 class="form-section-title">── ALLERGIES / VACCINE REACTIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-allergies-none"> No known drug, food, or environmental allergies</label>
                                        </div>

                                        <!-- 7. ELIMINATION -->
                                        <h4 class="form-section-title">── ELIMINATION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-elim-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-elim-urine-stream"> Straight urine stream in boys</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-elim-soft-stools-chk"> Soft stools</label>
                                                <span>(</span><input type="text" id="peds-2m-elim-soft-stools-cnt" class="form-control" style="width: 60px;"> <span># stools/day)</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-elim-norm-urine-chk"> Normal urination</label>
                                                <span>(</span><input type="text" id="peds-2m-elim-norm-urine-cnt" class="form-control" style="width: 60px;"> <span># wet diapers/day)</span>
                                            </div>
                                        </div>

                                        <!-- 8. SLEEP -->
                                        <h4 class="form-section-title">── SLEEP ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>POSITION/LOCATION</span>
                                                <input type="text" id="peds-2m-sleep-pos-loc" class="form-control" style="flex: 1; max-width: 350px;" placeholder="Back, crib, bassinet...">
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-sleep-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-sleep-appr-amt"> Appropriate amount (target hours including naps)</label>
                                        </div>

                                        <!-- 9. IMMUNIZATIONS -->
                                        <h4 class="form-section-title">── IMMUNIZATIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-imm-up-to-date"> Record reviewed - up to date</label>
                                        </div>

                                        <!-- 10. PAST MEDICAL HISTORY -->
                                        <h4 class="form-section-title">── PAST MEDICAL HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pmh-full-term"> Full Term</label>
                                        </div>

                                        <!-- 11. FAMILY HISTORY -->
                                        <h4 class="form-section-title">── FAMILY HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-2m-family-history" class="form-control" rows="2" placeholder="Family history notes..."></textarea>
                                        </div>

                                        <!-- 12. FAMILY STRUCTURE, PRIMARY CARETAKER(S) -->
                                        <h4 class="form-section-title">── FAMILY STRUCTURE, PRIMARY CARETAKER(S) ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-2m-family-structure" class="form-control" rows="2" placeholder="Family structure details..."></textarea>
                                        </div>

                                        <!-- 13. SOCIAL SUPPORTS -->
                                        <h4 class="form-section-title">── SOCIAL SUPPORTS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-2m-social-supports" class="form-control" rows="2" placeholder="Social support details..."></textarea>
                                        </div>

                                        <!-- 14. PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION -->
                                        <h4 class="form-section-title">── PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-2m-pregnancy-plans" class="form-control" rows="2" placeholder="Pregnancy spacing & contraception plans..."></textarea>
                                        </div>

                                        <!-- 15. DEVELOPMENTAL SURVEILLANCE -->
                                        <h4 class="form-section-title">── DEVELOPMENTAL SURVEILLANCE ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-dev-coos"> Coos/vocalizes</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-dev-lifts-head"> Lifts head when prone</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-dev-tracks-midline"> Tracks past midline</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-dev-responds-voice"> Responds to voice</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-dev-smiles"> Smiles responsively</label>
                                        </div>

                                        

                                        <!-- 16. SOCIAL / ENVIRONMENTAL SCREENING -->
                                        <h4 class="form-section-title">── SOCIAL / ENVIRONMENTAL SCREENING ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-soc-food-insec"> <strong>Food insecurity</strong></label>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-soc-housing-insec"> <strong>Housing insecurity</strong></label>
                                                <div style="margin-left: 24px; display: flex; flex-direction: column; gap: 4px; margin-top: 4px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-soc-house-moved"> ○ Moved more than once in past year</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-soc-house-density"> ○ &gt;2 people/bedroom</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-soc-house-families"> ○ &gt;1 family/home</label>
                                                </div>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-soc-dv"> <strong>Domestic violence</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-soc-guns"> <strong>Guns</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-soc-tobacco"> <strong>Tobacco</strong></label>
                                        </div>

                                        <!-- 17. PHYSICAL EXAM -->
                                        <h4 class="form-section-title">── PHYSICAL EXAM ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-general"> General: well-appearing</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-skin"> Skin: no jaundice, no rash, no lesions</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-head"> Head: normocephalic, open anterior fontanelle</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-eyes"> Eyes: symmetric red reflex / no strabismus</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-ent"> ENT: intact palate, patent nares</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-neck"> Neck: supple, no lymphadenopathy, no torticollis</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-cv"> CV: RRR, no murmurs, NI S1 & S2, good femoral pulses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-chest"> Chest: clear, no retractions/grunting/flaring</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-abd"> Abdomen: soft, no hepatosplenomegaly, no masses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-gu"> GU: NI female/male, testes descended</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-back"> Back: intact spine, no sacral dimple/pit</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-ext"> Ext/Hips: stable hips, well perfused, no deformity</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-pe-neuro"> Neuro: symmetric movements, normal tone, no deficits</label>
                                        </div>

                                        <!-- 18. ASSESSMENT -->
                                        <h4 class="form-section-title">── ASSESSMENT ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>1.</span> <input type="text" id="peds-2m-ass-age" class="form-control" style="width: 80px;" placeholder="2"> <span>month old</span>
                                            </div>
                                            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-ass-healthy"> Healthy child</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-ass-growth"> Good growth</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-ass-dev"> Normal development</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-ass-social"> No significant social concerns</label>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>2.</span> <input type="text" id="peds-2m-ass-note2" class="form-control" placeholder="Assessment note 2...">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>3.</span> <input type="text" id="peds-2m-ass-note3" class="form-control" placeholder="Assessment note 3...">
                                            </div>
                                        </div>

                                        <!-- 19. PLAN / NUTRITION -->
                                        <h4 class="form-section-title">── PLAN / NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-plan-vitd"> Vitamin D 400 IU/day</label>
                                        </div>

                                        <!-- 20. GUIDANCE TO FAMILIES -->
                                        <h4 class="form-section-title">── GUIDANCE TO FAMILIES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-gui-handout"> Bright Futures handout provided</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-gui-sleep-back"> Sleep on back</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-gui-no-bedding"> No pillow or extra bedding in baby's crib</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-gui-car-seat"> Car seat facing backwards until age 2</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-gui-rolling"> Rolling off table</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-gui-crying"> Normal crying patterns - strategies for soothing</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-gui-solids"> Solids at 4-6 months</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-gui-no-honey"> No honey before 1 year</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-gui-milk"> Breastmilk or formula until 12 months</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-gui-tummy-time"> Time on stomach while awake</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-gui-self-care"> Time for self/partner</label>
                                        </div>

                                        <!-- 21. FOLLOW-UP VISITS -->
                                        <h4 class="form-section-title">── FOLLOW-UP VISITS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-followup-wcc"> Follow-up for WCC</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-followup-other-chk"> Other follow-up:</label>
                                                <input type="text" id="peds-2m-followup-other-txt" class="form-control" style="width: 250px;" placeholder="Other follow-up details...">
                                            </div>
                                        </div>

                                        <!-- 22. PROBLEMS FOR FOLLOW UP -->
                                        <h4 class="form-section-title">── PROBLEMS FOR FOLLOW UP ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-2m-problems-followup" class="form-control" rows="2" placeholder="Problems for follow up..."></textarea>
                                        </div>

                                        <!-- 23. EDINBURGH POSTPARTUM DEPRESSION SCREEN -->
                                        <h4 class="form-section-title">── EDINBURGH POSTPARTUM DEPRESSION SCREEN ──</h4>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                            <strong>Score</strong>
                                            <input type="text" id="peds-2m-epds-score" class="form-control" style="width: 100px;" placeholder="Score">
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-epds-0-8"> Score 0-8: No action needed</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-epds-9-12"> Score 9-12: Establish plan for follow up, refer to community resources</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-epds-13"> Score &gt; 13: Refer for further assessment, treatment</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-2m-epds-item10"> If &gt;1 on item 10: Establish safety plan and place copy in chart</label>
                                        </div>

                                        

                                        <!-- 24. VACCINES -->
                                        <h4 class="form-section-title">── VACCINES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-dtap-chk"> DTaP #</label><input type="text" id="peds-2m-vac-dtap-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-hepb-chk"> Hepatitis B #</label><input type="text" id="peds-2m-vac-hepb-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-hib-chk"> Hib #</label><input type="text" id="peds-2m-vac-hib-val" class="form-control" style="width: 80px;"></div>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-vac-vis"> Vaccine Information Sheet given</label>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-ipv-chk"> IPV #</label><input type="text" id="peds-2m-vac-ipv-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-pcv-chk"> PCV #</label><input type="text" id="peds-2m-vac-pcv-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-rv-chk"> RV #</label><input type="text" id="peds-2m-vac-rv-val" class="form-control" style="width: 80px;"></div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Accordion Section: CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE FOUR MONTH WELL CHILD VISIT -->
                        <div class="accordion-item peds-only hidden">
                            <button class="accordion-header" aria-expanded="false" aria-controls="accordion-peds-four-month" id="accordion-peds-four-month-btn">
                                CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE FOUR MONTH WELL CHILD VISIT
                            </button>
                            <div id="accordion-peds-four-month" class="accordion-content hidden" role="region" aria-labelledby="accordion-peds-four-month-btn">
                                <form class="mod-clinical-style-18" id="peds-four-month-form" novalidate>
                                    <div class="mod-clinical-style-19">
                                        <!-- Date Header -->
                                        <div class="form-group" style="max-width: 250px; margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">Date</label>
                                            <input type="date" id="peds-4m-visit-date" class="form-control">
                                        </div>

                                        <!-- 1. IDENTIFICATION -->
                                        <h4 class="form-section-title">── IDENTIFICATION ──</h4>
                                        <div style="margin-bottom: 12px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                            <label class="form-label" style="margin: 0; min-width: 90px;">LANGUAGE:</label>
                                            <input type="text" id="peds-4m-language" class="form-control" style="width: 250px;" placeholder="Primary Language...">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-lang-interp"> Interpreter</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-lang-prov-speaks"> Provider speaks language</label>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">ACCOMPANIED BY:</label>
                                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-acc-mom"> Mom</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-acc-dad"> Dad</label>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-acc-other-chk"> Other:</label>
                                                    <input type="text" id="peds-4m-acc-other-txt" class="form-control" style="width: 200px;" placeholder="Specify relationship...">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Wt</strong>
                                                    <input type="text" id="peds-4m-wt-kg" class="form-control" style="width: 80px;" placeholder="kg"> <span>kg</span>
                                                    <span>(</span><input type="text" id="peds-4m-wt-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-wt-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Ht</strong>
                                                    <input type="text" id="peds-4m-ht-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-4m-ht-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-ht-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>HC</strong>
                                                    <input type="text" id="peds-4m-hc-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-4m-hc-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-hc-track"> tracking</label>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">CONCERNS:</label>
                                            <textarea id="peds-4m-concerns" class="form-control" rows="2" placeholder="Parental or provider concerns..."></textarea>
                                        </div>

                                        <!-- 2. INTERVAL HISTORY -->
                                        <h4 class="form-section-title">── INTERVAL HISTORY ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 6px;">(significant changes, illnesses, events since last visit)</p>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <textarea id="peds-4m-interval-history" class="form-control" rows="2" placeholder="Interval history notes..."></textarea>
                                        </div>
                                        <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px; background: #f1f5f9; padding: 8px 12px; border-radius: 4px;">
                                            <span>T</span> <input type="text" id="peds-4m-temp" class="form-control" style="width: 70px;">
                                            <span>P</span> <input type="text" id="peds-4m-pulse" class="form-control" style="width: 70px;">
                                            <span>RR</span> <input type="text" id="peds-4m-rr" class="form-control" style="width: 70px;">
                                            <span>BP</span> <input type="text" id="peds-4m-bp" class="form-control" style="width: 90px;">
                                        </div>

                                        <!-- 3. FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS -->
                                        <h4 class="form-section-title">── FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-4m-followup-problems" class="form-control" rows="2" placeholder="Follow up problem notes..."></textarea>
                                        </div>

                                        <!-- 4. MEDICATIONS -->
                                        <h4 class="form-section-title">── MEDICATIONS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-meds-none"> None</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-meds-vitd-chk"> Vitamin D</label>
                                                <input type="text" id="peds-4m-meds-vitd-txt" class="form-control" style="width: 250px;" placeholder="Dosage / Details (e.g. 400 IU/day)...">
                                            </div>
                                        </div>

                                        <!-- 5. NUTRITION -->
                                        <h4 class="form-section-title">── NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-nutr-bf-chk"> Breast feeding:</label>
                                                <span>Times per day</span>
                                                <input type="text" id="peds-4m-nutr-bf-times" class="form-control" style="width: 100px;" placeholder="times/day">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-nutr-formula-chk"> Formula type:</label>
                                                <input type="text" id="peds-4m-nutr-formula-type" class="form-control" style="width: 200px;" placeholder="Formula type">
                                                <span>Amount</span>
                                                <input type="text" id="peds-4m-nutr-formula-amt" class="form-control" style="width: 80px;" placeholder="oz"> <span>oz/day</span>
                                            </div>
                                            
                                        </div>

                                        <!-- 6. ALLERGIES / VACCINE REACTIONS -->
                                        <h4 class="form-section-title">── ALLERGIES / VACCINE REACTIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-allergies-none"> No known drug, food, or environmental allergies</label>
                                        </div>

                                        <!-- 7. ELIMINATION -->
                                        <h4 class="form-section-title">── ELIMINATION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-elim-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-elim-urine-stream"> Straight urine stream in boys</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-elim-soft-stools-chk"> Soft stools</label>
                                                <span>(</span><input type="text" id="peds-4m-elim-soft-stools-cnt" class="form-control" style="width: 60px;"> <span># stools/day)</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-elim-norm-urine-chk"> Normal urination</label>
                                                <span>(</span><input type="text" id="peds-4m-elim-norm-urine-cnt" class="form-control" style="width: 60px;"> <span># wet diapers/day)</span>
                                            </div>
                                        </div>

                                        <!-- 8. SLEEP -->
                                        <h4 class="form-section-title">── SLEEP ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>POSITION/LOCATION</span>
                                                <input type="text" id="peds-4m-sleep-pos-loc" class="form-control" style="flex: 1; max-width: 350px;" placeholder="Back, crib, bassinet...">
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-sleep-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-sleep-appr-amt"> Appropriate amount (target hours including naps)</label>
                                        </div>

                                        <!-- 9. IMMUNIZATIONS -->
                                        <h4 class="form-section-title">── IMMUNIZATIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-imm-up-to-date"> Record reviewed - up to date</label>
                                        </div>

                                        <!-- 10. PAST MEDICAL HISTORY -->
                                        <h4 class="form-section-title">── PAST MEDICAL HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pmh-full-term"> Full Term</label>
                                        </div>

                                        <!-- 11. FAMILY HISTORY -->
                                        <h4 class="form-section-title">── FAMILY HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-4m-family-history" class="form-control" rows="2" placeholder="Family history notes..."></textarea>
                                        </div>

                                        <!-- 12. FAMILY STRUCTURE, PRIMARY CARETAKER(S) -->
                                        <h4 class="form-section-title">── FAMILY STRUCTURE, PRIMARY CARETAKER(S) ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-4m-family-structure" class="form-control" rows="2" placeholder="Family structure details..."></textarea>
                                        </div>

                                        <!-- 13. SOCIAL SUPPORTS -->
                                        <h4 class="form-section-title">── SOCIAL SUPPORTS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-4m-social-supports" class="form-control" rows="2" placeholder="Social support details..."></textarea>
                                        </div>

                                        <!-- 14. PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION -->
                                        <h4 class="form-section-title">── PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-4m-pregnancy-plans" class="form-control" rows="2" placeholder="Pregnancy spacing & contraception plans..."></textarea>
                                        </div>

                                        <!-- 15. DEVELOPMENTAL SURVEILLANCE -->
                                        <h4 class="form-section-title">── DEVELOPMENTAL SURVEILLANCE ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-dev-no-head-lag"> No head lag</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-dev-rolls"> Rolls front to back</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-dev-holds-head"> Holds head/chest up with support</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-dev-tracks-180"> Tracks 180 degrees</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-dev-brings-hands"> Brings hands together</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-dev-reaches"> Reaches for object</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-dev-holds-toy"> Holds rattle/small toy</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-dev-laughs"> Laughs/squeals</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-dev-turns-sound"> Turns to sound</label>
                                        </div>

                                        

                                        <!-- 16. SOCIAL / ENVIRONMENTAL SCREENING -->
                                        <h4 class="form-section-title">── SOCIAL / ENVIRONMENTAL SCREENING ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-soc-food-insec"> <strong>Food insecurity</strong></label>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-soc-housing-insec"> <strong>Housing insecurity</strong></label>
                                                <div style="margin-left: 24px; display: flex; flex-direction: column; gap: 4px; margin-top: 4px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-soc-house-moved"> ○ Moved more than once in past year</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-soc-house-density"> ○ &gt;2 people/bedroom</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-soc-house-families"> ○ &gt;1 family/home</label>
                                                </div>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-soc-dv"> <strong>Domestic violence</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-soc-guns"> <strong>Guns</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-soc-tobacco"> <strong>Tobacco</strong></label>
                                        </div>

                                        <!-- 17. PHYSICAL EXAM -->
                                        <h4 class="form-section-title">── PHYSICAL EXAM ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-general"> General: well-appearing</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-skin"> Skin: no jaundice, no rash, no lesions</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-head"> Head: normocephalic, open anterior fontanelle</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-eyes"> Eyes: symmetric red reflex / no strabismus</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-ent"> ENT: intact palate, patent nares</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-neck"> Neck: supple, no lymphadenopathy, no torticollis</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-cv"> CV: RRR, no murmurs, NI S1 & S2, good femoral pulses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-chest"> Chest: clear, no retractions/grunting/flaring</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-abd"> Abdomen: soft, no hepatosplenomegaly, no masses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-gu"> GU: NI female/male, testes descended</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-back"> Back: intact spine, no sacral dimple/pit</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-ext"> Ext/Hips: stable hips, well perfused, no deformity</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-pe-neuro"> Neuro: symmetric movements, normal tone, no deficits</label>
                                        </div>

                                        <!-- 18. ASSESSMENT -->
                                        <h4 class="form-section-title">── ASSESSMENT ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>1.</span> <input type="text" id="peds-4m-ass-age" class="form-control" style="width: 80px;" placeholder="4"> <span>month old</span>
                                            </div>
                                            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-ass-healthy"> Healthy child</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-ass-growth"> Good growth</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-ass-dev"> Normal development</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-ass-social"> No significant social concerns</label>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>2.</span> <input type="text" id="peds-4m-ass-note2" class="form-control" placeholder="Assessment note 2...">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>3.</span> <input type="text" id="peds-4m-ass-note3" class="form-control" placeholder="Assessment note 3...">
                                            </div>
                                        </div>

                                        <!-- 19. PLAN / NUTRITION -->
                                        <h4 class="form-section-title">── PLAN / NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-plan-vitd"> Vitamin D 400 IU/day</label>
                                        </div>

                                        <!-- 20. GUIDANCE TO FAMILIES -->
                                        <h4 class="form-section-title">── GUIDANCE TO FAMILIES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-gui-handout"> Bright Futures handout provided</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-gui-sleep-back"> Sleep on back</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-gui-no-bedding"> No pillow or extra bedding in baby's crib</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-gui-car-seat"> Car seat facing backwards until age 2</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-gui-rolling"> Rolling off table</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-4m-gui-solids"> Introducing solids</label>
                                        </div>

                                        <!-- 21. FOLLOW-UP VISITS -->
                                        <h4 class="form-section-title">── FOLLOW-UP VISITS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-followup-wcc"> Follow-up for WCC</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-followup-other-chk"> Other follow-up:</label>
                                                <input type="text" id="peds-4m-followup-other-txt" class="form-control" style="width: 250px;" placeholder="Other follow-up details...">
                                            </div>
                                        </div>

                                        <!-- 22. PROBLEMS FOR FOLLOW UP -->
                                        <h4 class="form-section-title">── PROBLEMS FOR FOLLOW UP ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-4m-problems-followup" class="form-control" rows="2" placeholder="Problems for follow up..."></textarea>
                                        </div>

                                        <!-- 23. EDINBURGH POSTPARTUM DEPRESSION SCREEN -->
                                        <h4 class="form-section-title">── EDINBURGH POSTPARTUM DEPRESSION SCREEN ──</h4>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                            <strong>Score</strong>
                                            <input type="text" id="peds-4m-epds-score" class="form-control" style="width: 100px;" placeholder="Score">
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-epds-0-8"> Score 0-8: No action needed</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-epds-9-12"> Score 9-12: Establish plan for follow up, refer to community resources</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-epds-13"> Score &gt; 13: Refer for further assessment, treatment</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-4m-epds-item10"> If &gt;1 on item 10: Establish safety plan and place copy in chart</label>
                                        </div>

                                        

                                        <!-- 24. VACCINES -->
                                        <h4 class="form-section-title">── VACCINES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-dtap-chk"> DTaP #</label><input type="text" id="peds-2m-vac-dtap-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-hepb-chk"> Hepatitis B #</label><input type="text" id="peds-2m-vac-hepb-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-hib-chk"> Hib #</label><input type="text" id="peds-2m-vac-hib-val" class="form-control" style="width: 80px;"></div>
<label style="cursor: pointer;"><input type="checkbox" id="peds-2m-vac-vis"> Vaccine Information Sheet given</label>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-ipv-chk"> IPV #</label><input type="text" id="peds-2m-vac-ipv-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-pcv-chk"> PCV #</label><input type="text" id="peds-2m-vac-pcv-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-2m-vac-rv-chk"> RV #</label><input type="text" id="peds-2m-vac-rv-val" class="form-control" style="width: 80px;"></div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Accordion Section: CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE SIX MONTH WELL CHILD VISIT -->
                        <div class="accordion-item peds-only hidden">
                            <button class="accordion-header" aria-expanded="false" aria-controls="accordion-peds-six-month" id="accordion-peds-six-month-btn">
                                CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE SIX MONTH WELL CHILD VISIT
                            </button>
                            <div id="accordion-peds-six-month" class="accordion-content hidden" role="region" aria-labelledby="accordion-peds-six-month-btn">
                                <form class="mod-clinical-style-18" id="peds-six-month-form" novalidate>
                                    <div class="mod-clinical-style-19">
                                        <!-- Date Header -->
                                        <div class="form-group" style="max-width: 250px; margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">Date</label>
                                            <input type="date" id="peds-6m-visit-date" class="form-control">
                                        </div>

                                        <!-- 1. IDENTIFICATION -->
                                        <h4 class="form-section-title">── IDENTIFICATION ──</h4>
                                        <div style="margin-bottom: 12px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                            <label class="form-label" style="margin: 0; min-width: 90px;">LANGUAGE:</label>
                                            <input type="text" id="peds-6m-language" class="form-control" style="width: 250px;" placeholder="Primary Language...">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-lang-interp"> Interpreter</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-lang-prov-speaks"> Provider speaks language</label>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">ACCOMPANIED BY:</label>
                                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-acc-mom"> Mom</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-acc-dad"> Dad</label>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-acc-other-chk"> Other:</label>
                                                    <input type="text" id="peds-6m-acc-other-txt" class="form-control" style="width: 200px;" placeholder="Specify relationship...">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Wt</strong>
                                                    <input type="text" id="peds-6m-wt-kg" class="form-control" style="width: 80px;" placeholder="kg"> <span>kg</span>
                                                    <span>(</span><input type="text" id="peds-6m-wt-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-wt-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Ht</strong>
                                                    <input type="text" id="peds-6m-ht-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-6m-ht-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-ht-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>HC</strong>
                                                    <input type="text" id="peds-6m-hc-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-6m-hc-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-hc-track"> tracking</label>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">CONCERNS:</label>
                                            <textarea id="peds-6m-concerns" class="form-control" rows="2" placeholder="Parental or provider concerns..."></textarea>
                                        </div>

                                        <!-- 2. INTERVAL HISTORY -->
                                        <h4 class="form-section-title">── INTERVAL HISTORY ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 6px;">(significant changes, illnesses, events since last visit)</p>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <textarea id="peds-6m-interval-history" class="form-control" rows="2" placeholder="Interval history notes..."></textarea>
                                        </div>
                                        <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px; background: #f1f5f9; padding: 8px 12px; border-radius: 4px;">
                                            <span>T</span> <input type="text" id="peds-6m-temp" class="form-control" style="width: 70px;">
                                            <span>P</span> <input type="text" id="peds-6m-pulse" class="form-control" style="width: 70px;">
                                            <span>RR</span> <input type="text" id="peds-6m-rr" class="form-control" style="width: 70px;">
                                            <span>BP</span> <input type="text" id="peds-6m-bp" class="form-control" style="width: 90px;">
                                        </div>

                                        <!-- 3. FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS -->
                                        <h4 class="form-section-title">── FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-6m-followup-problems" class="form-control" rows="2" placeholder="Follow up problem notes..."></textarea>
                                        </div>

                                        <!-- 4. MEDICATIONS -->
                                        <h4 class="form-section-title">── MEDICATIONS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-meds-none"> None</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-meds-vitd-chk"> Vitamin D</label>
                                                <input type="text" id="peds-6m-meds-vitd-txt" class="form-control" style="width: 250px;" placeholder="Dosage / Details (e.g. 400 IU/day)...">
                                            </div>
                                        </div>

                                        <!-- 5. NUTRITION -->
                                        <h4 class="form-section-title">── NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-nutr-bf-chk"> Breast feeding:</label>
                                                <span>Times per day</span>
                                                <input type="text" id="peds-6m-nutr-bf-times" class="form-control" style="width: 100px;" placeholder="times/day">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-nutr-formula-chk"> Formula type:</label>
                                                <input type="text" id="peds-6m-nutr-formula-type" class="form-control" style="width: 200px;" placeholder="Formula type">
                                                <span>Amount</span>
                                                <input type="text" id="peds-6m-nutr-formula-amt" class="form-control" style="width: 80px;" placeholder="oz"> <span>oz/day</span>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-nutr-solids"> Solids</label>
                                        </div>

                                        <!-- 6. ALLERGIES / VACCINE REACTIONS -->
                                        <h4 class="form-section-title">── ALLERGIES / VACCINE REACTIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-allergies-none"> No known drug, food, or environmental allergies</label>
                                        </div>

                                        <!-- 7. ELIMINATION -->
                                        <h4 class="form-section-title">── ELIMINATION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-elim-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-elim-urine-stream"> Straight urine stream in boys</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-elim-soft-stools-chk"> Soft stools</label>
                                                <span>(</span><input type="text" id="peds-6m-elim-soft-stools-cnt" class="form-control" style="width: 60px;"> <span># stools/day)</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-elim-norm-urine-chk"> Normal urination</label>
                                                <span>(</span><input type="text" id="peds-6m-elim-norm-urine-cnt" class="form-control" style="width: 60px;"> <span># wet diapers/day)</span>
                                            </div>
                                        </div>

                                        <!-- 8. SLEEP -->
                                        <h4 class="form-section-title">── SLEEP ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>POSITION/LOCATION</span>
                                                <input type="text" id="peds-6m-sleep-pos-loc" class="form-control" style="flex: 1; max-width: 350px;" placeholder="Back, crib, bassinet...">
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-sleep-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-sleep-appr-amt"> Appropriate amount (target hours including naps)</label>
                                        </div>

                                        <!-- 9. IMMUNIZATIONS -->
                                        <h4 class="form-section-title">── IMMUNIZATIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-imm-up-to-date"> Record reviewed - up to date</label>
                                        </div>

                                        <!-- 10. PAST MEDICAL HISTORY -->
                                        <h4 class="form-section-title">── PAST MEDICAL HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pmh-full-term"> Full Term</label>
                                        </div>

                                        <!-- 11. FAMILY HISTORY -->
                                        <h4 class="form-section-title">── FAMILY HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-6m-family-history" class="form-control" rows="2" placeholder="Family history notes..."></textarea>
                                        </div>

                                        <!-- 12. FAMILY STRUCTURE, PRIMARY CARETAKER(S) -->
                                        <h4 class="form-section-title">── FAMILY STRUCTURE, PRIMARY CARETAKER(S) ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-6m-family-structure" class="form-control" rows="2" placeholder="Family structure details..."></textarea>
                                        </div>

                                        <!-- 13. SOCIAL SUPPORTS -->
                                        <h4 class="form-section-title">── SOCIAL SUPPORTS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-6m-social-supports" class="form-control" rows="2" placeholder="Social support details..."></textarea>
                                        </div>

                                        <!-- 14. PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION -->
                                        <h4 class="form-section-title">── PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-6m-pregnancy-plans" class="form-control" rows="2" placeholder="Pregnancy spacing & contraception plans..."></textarea>
                                        </div>

                                        <!-- 15. DEVELOPMENTAL SURVEILLANCE -->
                                        <h4 class="form-section-title">── DEVELOPMENTAL SURVEILLANCE ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-dev-bears-weight"> Bears weight on legs</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-dev-sits"> Sits with little or no support</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-dev-rolls-both"> Rolls both ways</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-dev-creeps"> Creeps, scoots</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-dev-transfers"> Transfers objects</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-dev-feeds-self"> Feeds self</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-dev-works-toy"> Works for toy out of reach</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-dev-babbles"> Babbles/groups of sounds</label>
                                        </div>

                                        <!-- TB RISK ASSESSMENT -->
<h4 class="form-section-title">── TB RISK ASSESSMENT - CHECK IF NEGATIVE ──</h4>
<div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-tb-contact"> Has a family member or someone your child has been in contact with had TB disease?</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-tb-meds"> Has your child, a family member, or someone your child has been in contact with had a positive TB test or received medications for TB?</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-tb-born-foreign"> Was your child born in another country?</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-tb-traveled"> Has your child traveled outside of the United States for more than a month?</label>
</div>

                                        <!-- 16. SOCIAL / ENVIRONMENTAL SCREENING -->
                                        <h4 class="form-section-title">── SOCIAL / ENVIRONMENTAL SCREENING ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-soc-food-insec"> <strong>Food insecurity</strong></label>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-soc-housing-insec"> <strong>Housing insecurity</strong></label>
                                                <div style="margin-left: 24px; display: flex; flex-direction: column; gap: 4px; margin-top: 4px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-soc-house-moved"> ○ Moved more than once in past year</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-soc-house-density"> ○ &gt;2 people/bedroom</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-soc-house-families"> ○ &gt;1 family/home</label>
                                                </div>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-soc-dv"> <strong>Domestic violence</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-soc-guns"> <strong>Guns</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-soc-tobacco"> <strong>Tobacco</strong></label>
                                        </div>

                                        <!-- 17. PHYSICAL EXAM -->
                                        <h4 class="form-section-title">── PHYSICAL EXAM ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-general"> General: well-appearing</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-skin"> Skin: no jaundice, no rash, no lesions</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-head"> Head: normocephalic, open anterior fontanelle</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-eyes"> Eyes: symmetric red reflex / no strabismus</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-ent"> ENT: intact palate, patent nares</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-neck"> Neck: supple, no lymphadenopathy, no torticollis</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-cv"> CV: RRR, no murmurs, NI S1 & S2, good femoral pulses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-chest"> Chest: clear, no retractions/grunting/flaring</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-abd"> Abdomen: soft, no hepatosplenomegaly, no masses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-gu"> GU: NI female/male, testes descended</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-back"> Back: intact spine, no sacral dimple/pit</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-ext"> Ext/Hips: stable hips, well perfused, no deformity</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-pe-neuro"> Neuro: symmetric movements, normal tone, no deficits</label>
                                        </div>

                                        <!-- 18. ASSESSMENT -->
                                        <h4 class="form-section-title">── ASSESSMENT ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>1.</span> <input type="text" id="peds-6m-ass-age" class="form-control" style="width: 80px;" placeholder="6"> <span>month old</span>
                                            </div>
                                            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-ass-healthy"> Healthy child</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-ass-growth"> Good growth</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-ass-dev"> Normal development</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-ass-social"> No significant social concerns</label>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>2.</span> <input type="text" id="peds-6m-ass-note2" class="form-control" placeholder="Assessment note 2...">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>3.</span> <input type="text" id="peds-6m-ass-note3" class="form-control" placeholder="Assessment note 3...">
                                            </div>
                                        </div>

                                        <!-- 19. PLAN / NUTRITION -->
                                        <h4 class="form-section-title">── PLAN / NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-plan-vitd"> Vitamin D 400 IU/day</label>
                                        </div>

                                        <!-- 20. GUIDANCE TO FAMILIES -->
                                        <h4 class="form-section-title">── GUIDANCE TO FAMILIES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-handout"> Bright Futures handout provided</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-mobility"> Increasing mobility, fine motor skills -- impact on safety</label>
<div style="margin-left: 20px; display: flex; flex-direction: column; gap: 4px;">
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-childproof"> ○ Childproofing</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-choking"> ○ Choking risk (food, coins, small objects)</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-poison"> ○ Keep Poison Control number by phone</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-walkers"> ○ Avoid walkers or take off wheels</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-water"> ○ Water safety (tubs, toilets, pools)</label>
</div>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-car-seat"> Car seat facing backwards until age 2</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-cup"> Start using cup</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-milk"> Breastmilk or formula until 12 months</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-read-book"> REACH OUT AND READ BOOK GIVEN</label>
                                        </div>

                                        <!-- 21. FOLLOW-UP VISITS -->
                                        <h4 class="form-section-title">── FOLLOW-UP VISITS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-followup-wcc"> Follow-up for WCC</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-followup-other-chk"> Other follow-up:</label>
                                                <input type="text" id="peds-6m-followup-other-txt" class="form-control" style="width: 250px;" placeholder="Other follow-up details...">
                                            </div>
                                        </div>

                                        <!-- 22. PROBLEMS FOR FOLLOW UP -->
                                        <h4 class="form-section-title">── PROBLEMS FOR FOLLOW UP ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-6m-problems-followup" class="form-control" rows="2" placeholder="Problems for follow up..."></textarea>
                                        </div>

                                        <!-- 23. EDINBURGH POSTPARTUM DEPRESSION SCREEN -->
                                        <h4 class="form-section-title">── EDINBURGH POSTPARTUM DEPRESSION SCREEN ──</h4>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                            <strong>Score</strong>
                                            <input type="text" id="peds-6m-epds-score" class="form-control" style="width: 100px;" placeholder="Score">
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-epds-0-8"> Score 0-8: No action needed</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-epds-9-12"> Score 9-12: Establish plan for follow up, refer to community resources</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-epds-13"> Score &gt; 13: Refer for further assessment, treatment</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-epds-item10"> If &gt;1 on item 10: Establish safety plan and place copy in chart</label>
                                        </div>

                                        <!-- TB SCREEN -->
<h4 class="form-section-title">── TB SCREEN ──</h4>
<div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-tb-neg"> TB risk screening negative</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-tb-ppd"> PPD done (if high risk)</label>
</div>

                                        <!-- 24. VACCINES -->
                                        <h4 class="form-section-title">── VACCINES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-6m-vac-dtap-chk"> DTaP #</label><input type="text" id="peds-6m-vac-dtap-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-6m-vac-hepb-chk"> Hepatitis B #</label><input type="text" id="peds-6m-vac-hepb-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-6m-vac-hib-chk"> Hib #</label><input type="text" id="peds-6m-vac-hib-val" class="form-control" style="width: 80px;"></div>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-vac-vis"> Vaccine Information Sheet given</label>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-6m-vac-ipv-chk"> IPV #</label><input type="text" id="peds-6m-vac-ipv-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-6m-vac-pcv-chk"> PCV #</label><input type="text" id="peds-6m-vac-pcv-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-6m-vac-rv-chk"> RV #</label><input type="text" id="peds-6m-vac-rv-val" class="form-control" style="width: 80px;"></div>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-vac-flu"> Influenza</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-vac-dental"> GPCH DENTAL SCREENING AND EDUCATION</label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Accordion Section: CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE NINE MONTH WELL CHILD VISIT -->
                        <div class="accordion-item peds-only hidden">
                            <button class="accordion-header" aria-expanded="false" aria-controls="accordion-peds-nine-month" id="accordion-peds-nine-month-btn">
                                CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE NINE MONTH WELL CHILD VISIT
                            </button>
                            <div id="accordion-peds-nine-month" class="accordion-content hidden" role="region" aria-labelledby="accordion-peds-nine-month-btn">
                                <form class="mod-clinical-style-18" id="peds-nine-month-form" novalidate>
                                    <div class="mod-clinical-style-19">
                                        <!-- Date Header -->
                                        <div class="form-group" style="max-width: 250px; margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">Date</label>
                                            <input type="date" id="peds-9m-visit-date" class="form-control">
                                        </div>

                                        <!-- 1. IDENTIFICATION -->
                                        <h4 class="form-section-title">── IDENTIFICATION ──</h4>
                                        <div style="margin-bottom: 12px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                            <label class="form-label" style="margin: 0; min-width: 90px;">LANGUAGE:</label>
                                            <input type="text" id="peds-9m-language" class="form-control" style="width: 250px;" placeholder="Primary Language...">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-lang-interp"> Interpreter</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-lang-prov-speaks"> Provider speaks language</label>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">ACCOMPANIED BY:</label>
                                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-acc-mom"> Mom</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-acc-dad"> Dad</label>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-acc-other-chk"> Other:</label>
                                                    <input type="text" id="peds-9m-acc-other-txt" class="form-control" style="width: 200px;" placeholder="Specify relationship...">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Wt</strong>
                                                    <input type="text" id="peds-9m-wt-kg" class="form-control" style="width: 80px;" placeholder="kg"> <span>kg</span>
                                                    <span>(</span><input type="text" id="peds-9m-wt-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-wt-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Ht</strong>
                                                    <input type="text" id="peds-9m-ht-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-9m-ht-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-ht-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>HC</strong>
                                                    <input type="text" id="peds-9m-hc-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-9m-hc-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-hc-track"> tracking</label>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">CONCERNS:</label>
                                            <textarea id="peds-9m-concerns" class="form-control" rows="2" placeholder="Parental or provider concerns..."></textarea>
                                        </div>

                                        <!-- 2. INTERVAL HISTORY -->
                                        <h4 class="form-section-title">── INTERVAL HISTORY ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 6px;">(significant changes, illnesses, events since last visit)</p>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <textarea id="peds-9m-interval-history" class="form-control" rows="2" placeholder="Interval history notes..."></textarea>
                                        </div>
                                        <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px; background: #f1f5f9; padding: 8px 12px; border-radius: 4px;">
                                            <span>T</span> <input type="text" id="peds-9m-temp" class="form-control" style="width: 70px;">
                                            <span>P</span> <input type="text" id="peds-9m-pulse" class="form-control" style="width: 70px;">
                                            <span>RR</span> <input type="text" id="peds-9m-rr" class="form-control" style="width: 70px;">
                                            <span>BP</span> <input type="text" id="peds-9m-bp" class="form-control" style="width: 90px;">
                                        </div>

                                        <!-- 3. FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS -->
                                        <h4 class="form-section-title">── FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-9m-followup-problems" class="form-control" rows="2" placeholder="Follow up problem notes..."></textarea>
                                        </div>

                                        <!-- 4. MEDICATIONS -->
                                        <h4 class="form-section-title">── MEDICATIONS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-meds-none"> None</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-meds-vitd-chk"> Vitamin D</label>
                                                <input type="text" id="peds-9m-meds-vitd-txt" class="form-control" style="width: 250px;" placeholder="Dosage / Details (e.g. 400 IU/day)...">
                                            </div>
                                        </div>

                                        <!-- 5. NUTRITION -->
                                        <h4 class="form-section-title">── NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-nutr-bf-chk"> Breast feeding:</label>
                                                <span>Times per day</span>
                                                <input type="text" id="peds-9m-nutr-bf-times" class="form-control" style="width: 100px;" placeholder="times/day">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-nutr-formula-chk"> Formula type:</label>
                                                <input type="text" id="peds-9m-nutr-formula-type" class="form-control" style="width: 200px;" placeholder="Formula type">
                                                <span>Amount</span>
                                                <input type="text" id="peds-9m-nutr-formula-amt" class="form-control" style="width: 80px;" placeholder="oz"> <span>oz/day</span>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-nutr-solids-bal"> Balance of solids: protein, fruit/vegetables</label>
                                        </div>

                                        <!-- 6. ALLERGIES / VACCINE REACTIONS -->
                                        <h4 class="form-section-title">── ALLERGIES / VACCINE REACTIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-allergies-none"> No known drug, food, or environmental allergies</label>
                                        </div>

                                        <!-- 7. ELIMINATION -->
                                        <h4 class="form-section-title">── ELIMINATION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-elim-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-elim-urine-stream"> Straight urine stream in boys</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-elim-soft-stools-chk"> Soft stools</label>
                                                <span>(</span><input type="text" id="peds-9m-elim-soft-stools-cnt" class="form-control" style="width: 60px;"> <span># stools/day)</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-elim-norm-urine-chk"> Normal urination</label>
                                                <span>(</span><input type="text" id="peds-9m-elim-norm-urine-cnt" class="form-control" style="width: 60px;"> <span># wet diapers/day)</span>
                                            </div>
                                        </div>

                                        <!-- 8. SLEEP -->
                                        <h4 class="form-section-title">── SLEEP ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>POSITION/LOCATION</span>
                                                <input type="text" id="peds-9m-sleep-pos-loc" class="form-control" style="flex: 1; max-width: 350px;" placeholder="Back, crib, bassinet...">
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-sleep-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-sleep-appr-amt"> Appropriate amount (target hours including naps)</label>
                                        </div>

                                        <!-- 9. IMMUNIZATIONS -->
                                        <h4 class="form-section-title">── IMMUNIZATIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-imm-up-to-date"> Record reviewed - up to date</label>
                                        </div>

                                        <!-- 10. PAST MEDICAL HISTORY -->
                                        <h4 class="form-section-title">── PAST MEDICAL HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pmh-full-term"> Full Term</label>
                                        </div>

                                        <!-- 11. FAMILY HISTORY -->
                                        <h4 class="form-section-title">── FAMILY HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-9m-family-history" class="form-control" rows="2" placeholder="Family history notes..."></textarea>
                                        </div>

                                        <!-- 12. FAMILY STRUCTURE, PRIMARY CARETAKER(S) -->
                                        <h4 class="form-section-title">── FAMILY STRUCTURE, PRIMARY CARETAKER(S) ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-9m-family-structure" class="form-control" rows="2" placeholder="Family structure details..."></textarea>
                                        </div>

                                        <!-- 13. SOCIAL SUPPORTS -->
                                        <h4 class="form-section-title">── SOCIAL SUPPORTS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-9m-social-supports" class="form-control" rows="2" placeholder="Social support details..."></textarea>
                                        </div>

                                        <!-- 14. PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION -->
                                        <h4 class="form-section-title">── PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-9m-pregnancy-plans" class="form-control" rows="2" placeholder="Pregnancy spacing & contraception plans..."></textarea>
                                        </div>

                                        <!-- 15. DEVELOPMENTAL SURVEILLANCE -->
                                        <h4 class="form-section-title">── DEVELOPMENTAL SURVEILLANCE ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-dev-asq-passed"> ASQ passed</label>
<p style="font-weight: 600; margin-top: 8px; margin-bottom: 4px;">SAMPLE MILESTONES</p>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-dev-crawls"> Crawls/creeps</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-dev-sits-alone"> Sits alone</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-dev-pulls-stand"> Pulls to stand</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-dev-stands-holding"> Stands holding on</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-dev-cruises"> Cruises</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-dev-imm-pincer"> Immature pincer</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-dev-bangs-obj"> Bangs two objects together</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-dev-waves"> Waves/claps/peek-a-boo</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-dev-stranger-anx"> Stranger anxiety</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-dev-mama-dada"> Mama/Dada nonspecific</label>
                                        </div>

                                        

                                        <!-- 16. SOCIAL / ENVIRONMENTAL SCREENING -->
                                        <h4 class="form-section-title">── SOCIAL / ENVIRONMENTAL SCREENING ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-soc-food-insec"> <strong>Food insecurity</strong></label>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-soc-housing-insec"> <strong>Housing insecurity</strong></label>
                                                <div style="margin-left: 24px; display: flex; flex-direction: column; gap: 4px; margin-top: 4px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-soc-house-moved"> ○ Moved more than once in past year</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-soc-house-density"> ○ &gt;2 people/bedroom</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-soc-house-families"> ○ &gt;1 family/home</label>
                                                </div>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-soc-dv"> <strong>Domestic violence</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-soc-guns"> <strong>Guns</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-soc-tobacco"> <strong>Tobacco</strong></label>
                                        </div>

                                        <!-- 17. PHYSICAL EXAM -->
                                        <h4 class="form-section-title">── PHYSICAL EXAM ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-general"> General: well-appearing</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-skin"> Skin: no jaundice, no rash, no lesions</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-head"> Head: normocephalic, open anterior fontanelle</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-eyes"> Eyes: symmetric red reflex / no strabismus</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-ent"> ENT: intact palate, patent nares</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-neck"> Neck: supple, no lymphadenopathy, no torticollis</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-cv"> CV: RRR, no murmurs, NI S1 & S2, good femoral pulses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-chest"> Chest: clear, no retractions/grunting/flaring</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-abd"> Abdomen: soft, no hepatosplenomegaly, no masses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-gu"> GU: NI female/male, testes descended</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-back"> Back: intact spine, no sacral dimple/pit</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-ext"> Ext/Hips: stable hips, well perfused, no deformity</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-pe-neuro"> Neuro: symmetric movements, normal tone, no deficits</label>
                                        </div>

                                        <!-- 18. ASSESSMENT -->
                                        <h4 class="form-section-title">── ASSESSMENT ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>1.</span> <input type="text" id="peds-9m-ass-age" class="form-control" style="width: 80px;" placeholder="9"> <span>month old</span>
                                            </div>
                                            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-ass-healthy"> Healthy child</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-ass-growth"> Good growth</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-ass-dev"> Normal development</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-ass-social"> No significant social concerns</label>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>2.</span> <input type="text" id="peds-9m-ass-note2" class="form-control" placeholder="Assessment note 2...">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>3.</span> <input type="text" id="peds-9m-ass-note3" class="form-control" placeholder="Assessment note 3...">
                                            </div>
                                        </div>

                                        <!-- 19. PLAN / NUTRITION -->
                                        <h4 class="form-section-title">── PLAN / NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-plan-vitd"> Vitamin D 400 IU/day</label>
                                        </div>

                                        <!-- 20. GUIDANCE TO FAMILIES -->
                                        <h4 class="form-section-title">── GUIDANCE TO FAMILIES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-handout"> Bright Futures handout provided</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-mobility"> Increasing mobility, fine motor skills -- impact on safety</label>
<div style="margin-left: 20px; display: flex; flex-direction: column; gap: 4px;">
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-childproof"> ○ Childproofing</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-choking"> ○ Choking risk (food, coins, small objects)</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-poison"> ○ Keep Poison Control number by phone</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-walkers"> ○ Avoid walkers or take off wheels</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-water"> ○ Water safety (tubs, toilets, pools)</label>
</div>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-car-seat"> Car seat facing backwards until age 2</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-cup"> Start using cup</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-milk"> Breastmilk or formula until 12 months</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-6m-gui-read-book"> REACH OUT AND READ BOOK GIVEN</label>
                                        </div>

                                        <!-- 21. FOLLOW-UP VISITS -->
                                        <h4 class="form-section-title">── FOLLOW-UP VISITS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-followup-wcc"> Follow-up for WCC</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-followup-other-chk"> Other follow-up:</label>
                                                <input type="text" id="peds-9m-followup-other-txt" class="form-control" style="width: 250px;" placeholder="Other follow-up details...">
                                            </div>
                                        </div>

                                        <!-- 22. PROBLEMS FOR FOLLOW UP -->
                                        <h4 class="form-section-title">── PROBLEMS FOR FOLLOW UP ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-9m-problems-followup" class="form-control" rows="2" placeholder="Problems for follow up..."></textarea>
                                        </div>

                                        <!-- 23. EDINBURGH POSTPARTUM DEPRESSION SCREEN -->
                                        <h4 class="form-section-title">── EDINBURGH POSTPARTUM DEPRESSION SCREEN ──</h4>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                            <strong>Score</strong>
                                            <input type="text" id="peds-9m-epds-score" class="form-control" style="width: 100px;" placeholder="Score">
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-epds-0-8"> Score 0-8: No action needed</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-epds-9-12"> Score 9-12: Establish plan for follow up, refer to community resources</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-epds-13"> Score &gt; 13: Refer for further assessment, treatment</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-epds-item10"> If &gt;1 on item 10: Establish safety plan and place copy in chart</label>
                                        </div>

                                        

                                        <!-- 24. VACCINES -->
                                        <h4 class="form-section-title">── VACCINES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-9m-vac-none"> None</label>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-9m-vac-dtap-chk"> DTaP #</label><input type="text" id="peds-9m-vac-dtap-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-9m-vac-hepb-chk"> Hepatitis B #</label><input type="text" id="peds-9m-vac-hepb-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-9m-vac-hib-chk"> Hib #</label><input type="text" id="peds-9m-vac-hib-val" class="form-control" style="width: 80px;"></div>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-vac-vis"> Vaccine Information Sheet given</label>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-9m-vac-ipv-chk"> IPV #</label><input type="text" id="peds-9m-vac-ipv-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-9m-vac-pcv-chk"> PCV #</label><input type="text" id="peds-9m-vac-pcv-val" class="form-control" style="width: 80px;"></div>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-vac-flu"> Influenza</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-9m-vac-dental"> GPCH DENTAL SCREENING AND EDUCATION</label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Accordion Section: CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE TWELVE MONTH WELL CHILD VISIT -->
                        <div class="accordion-item peds-only hidden">
                            <button class="accordion-header" aria-expanded="false" aria-controls="accordion-peds-twelve-month" id="accordion-peds-twelve-month-btn">
                                CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE TWELVE MONTH WELL CHILD VISIT
                            </button>
                            <div id="accordion-peds-twelve-month" class="accordion-content hidden" role="region" aria-labelledby="accordion-peds-twelve-month-btn">
                                <form class="mod-clinical-style-18" id="peds-twelve-month-form" novalidate>
                                    <div class="mod-clinical-style-19">
                                        <!-- Date Header -->
                                        <div class="form-group" style="max-width: 250px; margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">Date</label>
                                            <input type="date" id="peds-12m-visit-date" class="form-control">
                                        </div>

                                        <!-- 1. IDENTIFICATION -->
                                        <h4 class="form-section-title">── IDENTIFICATION ──</h4>
                                        <div style="margin-bottom: 12px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                            <label class="form-label" style="margin: 0; min-width: 90px;">LANGUAGE:</label>
                                            <input type="text" id="peds-12m-language" class="form-control" style="width: 250px;" placeholder="Primary Language...">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-lang-interp"> Interpreter</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-lang-prov-speaks"> Provider speaks language</label>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">ACCOMPANIED BY:</label>
                                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-acc-mom"> Mom</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-acc-dad"> Dad</label>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-acc-other-chk"> Other:</label>
                                                    <input type="text" id="peds-12m-acc-other-txt" class="form-control" style="width: 200px;" placeholder="Specify relationship...">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Wt</strong>
                                                    <input type="text" id="peds-12m-wt-kg" class="form-control" style="width: 80px;" placeholder="kg"> <span>kg</span>
                                                    <span>(</span><input type="text" id="peds-12m-wt-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-wt-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Ht</strong>
                                                    <input type="text" id="peds-12m-ht-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-12m-ht-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-ht-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>HC</strong>
                                                    <input type="text" id="peds-12m-hc-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-12m-hc-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-hc-track"> tracking</label>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">CONCERNS:</label>
                                            <textarea id="peds-12m-concerns" class="form-control" rows="2" placeholder="Parental or provider concerns..."></textarea>
                                        </div>

                                        <!-- 2. INTERVAL HISTORY -->
                                        <h4 class="form-section-title">── INTERVAL HISTORY ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 6px;">(significant changes, illnesses, events since last visit)</p>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <textarea id="peds-12m-interval-history" class="form-control" rows="2" placeholder="Interval history notes..."></textarea>
                                        </div>
                                        <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px; background: #f1f5f9; padding: 8px 12px; border-radius: 4px;">
                                            <span>T</span> <input type="text" id="peds-12m-temp" class="form-control" style="width: 70px;">
                                            <span>P</span> <input type="text" id="peds-12m-pulse" class="form-control" style="width: 70px;">
                                            <span>RR</span> <input type="text" id="peds-12m-rr" class="form-control" style="width: 70px;">
                                            <span>BP</span> <input type="text" id="peds-12m-bp" class="form-control" style="width: 90px;">
                                        </div>

                                        <!-- 3. FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS -->
                                        <h4 class="form-section-title">── FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-12m-followup-problems" class="form-control" rows="2" placeholder="Follow up problem notes..."></textarea>
                                        </div>

                                        <!-- 4. MEDICATIONS -->
                                        <h4 class="form-section-title">── MEDICATIONS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-meds-none"> None</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-meds-vitd-chk"> Vitamin D</label>
                                                <input type="text" id="peds-12m-meds-vitd-txt" class="form-control" style="width: 250px;" placeholder="Dosage / Details (e.g. 400 IU/day)...">
                                            </div>
                                        </div>

                                        <!-- 5. NUTRITION -->
                                        <h4 class="form-section-title">── NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-nutr-bf-chk"> Breast feeding:</label>
                                                <span>Times per day</span>
                                                <input type="text" id="peds-12m-nutr-bf-times" class="form-control" style="width: 100px;" placeholder="times/day">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-nutr-formula-chk"> Formula type:</label>
                                                <input type="text" id="peds-12m-nutr-formula-type" class="form-control" style="width: 200px;" placeholder="Formula type">
                                                <span>Amount</span>
                                                <input type="text" id="peds-12m-nutr-formula-amt" class="form-control" style="width: 80px;" placeholder="oz"> <span>oz/day</span>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-nutr-intake"> Appropriate intake of protein, iron, calcium, fruit/vegetables</label>
                                        </div>

                                        <!-- 6. ALLERGIES / VACCINE REACTIONS -->
                                        <h4 class="form-section-title">── ALLERGIES / VACCINE REACTIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-allergies-none"> No known drug, food, or environmental allergies</label>
                                        </div>

                                        <!-- 7. ELIMINATION -->
                                        <h4 class="form-section-title">── ELIMINATION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-elim-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-elim-urine-stream"> Straight urine stream in boys</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-elim-soft-stools-chk"> Soft stools</label>
                                                <span>(</span><input type="text" id="peds-12m-elim-soft-stools-cnt" class="form-control" style="width: 60px;"> <span># stools/day)</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-elim-norm-urine-chk"> Normal urination</label>
                                                <span>(</span><input type="text" id="peds-12m-elim-norm-urine-cnt" class="form-control" style="width: 60px;"> <span># wet diapers/day)</span>
                                            </div>
                                        </div>

                                        <!-- 8. SLEEP -->
                                        <h4 class="form-section-title">── SLEEP ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>POSITION/LOCATION</span>
                                                <input type="text" id="peds-12m-sleep-pos-loc" class="form-control" style="flex: 1; max-width: 350px;" placeholder="Back, crib, bassinet...">
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-sleep-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-sleep-appr-amt"> Appropriate amount (target hours including naps)</label>
                                        </div>

                                        <!-- 9. IMMUNIZATIONS -->
                                        <h4 class="form-section-title">── IMMUNIZATIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-imm-up-to-date"> Record reviewed - up to date</label>
                                        </div>

                                        <!-- 10. PAST MEDICAL HISTORY -->
                                        <h4 class="form-section-title">── PAST MEDICAL HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pmh-full-term"> Full Term</label>
                                        </div>

                                        <!-- 11. FAMILY HISTORY -->
                                        <h4 class="form-section-title">── FAMILY HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-12m-family-history" class="form-control" rows="2" placeholder="Family history notes..."></textarea>
                                        </div>

                                        <!-- 12. FAMILY STRUCTURE, PRIMARY CARETAKER(S) -->
                                        <h4 class="form-section-title">── FAMILY STRUCTURE, PRIMARY CARETAKER(S) ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-12m-family-structure" class="form-control" rows="2" placeholder="Family structure details..."></textarea>
                                        </div>

                                        <!-- 13. SOCIAL SUPPORTS -->
                                        <h4 class="form-section-title">── SOCIAL SUPPORTS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-12m-social-supports" class="form-control" rows="2" placeholder="Social support details..."></textarea>
                                        </div>

                                        <!-- 14. PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION -->
                                        <h4 class="form-section-title">── PLANS FOR FUTURE PREGNANCY, PREGNANCY SPACING, CONTRACEPTION ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-12m-pregnancy-plans" class="form-control" rows="2" placeholder="Pregnancy spacing & contraception plans..."></textarea>
                                        </div>

                                        <!-- 15. DEVELOPMENTAL SURVEILLANCE -->
                                        <h4 class="form-section-title">── DEVELOPMENTAL SURVEILLANCE ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-dev-stands-alone"> Stands alone</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-dev-walks"> Walks 2-3 steps without help</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-dev-mat-pincer"> Mature pincer</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-dev-points"> Points</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-dev-gives-toy"> Gives/takes toy</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-dev-drinks-cup"> Holds/drinks from cup</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-dev-turns-pages"> Turns pages</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-dev-mama-dada-spec"> Mama/Dada specific</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-dev-other-words"> 1-3 other words</label>
                                        </div>

                                        <!-- TB RISK ASSESSMENT -->
<h4 class="form-section-title">── TB RISK ASSESSMENT - CHECK IF NEGATIVE ──</h4>
<div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-tb-contact"> Has a family member or someone your child has been in contact with had TB disease?</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-tb-meds"> Has your child, a family member, or someone your child has been in contact with had a positive TB test or received medications for TB?</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-tb-born-foreign"> Was your child born in another country?</label>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-6m-tb-traveled"> Has your child traveled outside of the United States for more than a month?</label>
</div>

                                        <!-- 16. SOCIAL / ENVIRONMENTAL SCREENING -->
                                        <h4 class="form-section-title">── SOCIAL / ENVIRONMENTAL SCREENING ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-soc-food-insec"> <strong>Food insecurity</strong></label>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-soc-housing-insec"> <strong>Housing insecurity</strong></label>
                                                <div style="margin-left: 24px; display: flex; flex-direction: column; gap: 4px; margin-top: 4px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-soc-house-moved"> ○ Moved more than once in past year</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-soc-house-density"> ○ &gt;2 people/bedroom</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-soc-house-families"> ○ &gt;1 family/home</label>
                                                </div>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-soc-dv"> <strong>Domestic violence</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-soc-guns"> <strong>Guns</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-soc-tobacco"> <strong>Tobacco</strong></label>
                                        </div>

                                        <!-- 17. PHYSICAL EXAM -->
                                        <h4 class="form-section-title">── PHYSICAL EXAM ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-general"> General: well-appearing</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-skin"> Skin: no jaundice, no rash, no lesions</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-head"> Head: normocephalic, open anterior fontanelle</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-eyes"> Eyes: symmetric red reflex / no strabismus</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-ent"> ENT: intact palate, patent nares</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-neck"> Neck: supple, no lymphadenopathy, no torticollis</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-cv"> CV: RRR, no murmurs, NI S1 & S2, good femoral pulses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-chest"> Chest: clear, no retractions/grunting/flaring</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-abd"> Abdomen: soft, no hepatosplenomegaly, no masses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-gu"> GU: NI female/male, testes descended</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-back"> Back: intact spine, no sacral dimple/pit</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-ext"> Ext/Hips: stable hips, well perfused, no deformity</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-pe-neuro"> Neuro: symmetric movements, normal tone, no deficits</label>
                                        </div>

                                        <!-- 18. ASSESSMENT -->
                                        <h4 class="form-section-title">── ASSESSMENT ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>1.</span> <input type="text" id="peds-12m-ass-age" class="form-control" style="width: 80px;" placeholder="12"> <span>month old</span>
                                            </div>
                                            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-ass-healthy"> Healthy child</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-ass-growth"> Good growth</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-ass-dev"> Normal development</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-ass-social"> No significant social concerns</label>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>2.</span> <input type="text" id="peds-12m-ass-note2" class="form-control" placeholder="Assessment note 2...">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>3.</span> <input type="text" id="peds-12m-ass-note3" class="form-control" placeholder="Assessment note 3...">
                                            </div>
                                        </div>

                                        <!-- 19. PLAN / NUTRITION -->
                                        <h4 class="form-section-title">── PLAN / NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-plan-vitd"> Vitamin D 400 IU/day</label>
                                        </div>

                                        <!-- 20. GUIDANCE TO FAMILIES -->
                                        <h4 class="form-section-title">── GUIDANCE TO FAMILIES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-gui-handout"> Bright Futures handout provided</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-gui-whole-milk"> Change to whole milk</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-gui-wean-bottle"> Wean from bottle</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-gui-car-seat"> Car seat facing backwards until age 2</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-gui-choking"> Choking risk (food, coins, small objects)</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-gui-poison"> Keep Poison Control number by phone</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-gui-water"> Water safety (tubs, toilets, pools)</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-gui-teeth"> Brush teeth; grain of rice-sized amount of toothpaste</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-gui-dental-visit"> First dental visit</label>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-gui-read-book"> REACH OUT AND READ BOOK GIVEN</label>
                                        </div>

                                        <!-- 21. FOLLOW-UP VISITS -->
                                        <h4 class="form-section-title">── FOLLOW-UP VISITS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-followup-wcc"> Follow-up for WCC</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-followup-other-chk"> Other follow-up:</label>
                                                <input type="text" id="peds-12m-followup-other-txt" class="form-control" style="width: 250px;" placeholder="Other follow-up details...">
                                            </div>
                                        </div>

                                        <!-- 22. PROBLEMS FOR FOLLOW UP -->
                                        <h4 class="form-section-title">── PROBLEMS FOR FOLLOW UP ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-12m-problems-followup" class="form-control" rows="2" placeholder="Problems for follow up..."></textarea>
                                        </div>

                                        <!-- 23. EDINBURGH POSTPARTUM DEPRESSION SCREEN -->
                                        <h4 class="form-section-title">── EDINBURGH POSTPARTUM DEPRESSION SCREEN ──</h4>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                            <strong>Score</strong>
                                            <input type="text" id="peds-12m-epds-score" class="form-control" style="width: 100px;" placeholder="Score">
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-epds-0-8"> Score 0-8: No action needed</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-epds-9-12"> Score 9-12: Establish plan for follow up, refer to community resources</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-epds-13"> Score &gt; 13: Refer for further assessment, treatment</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-epds-item10"> If &gt;1 on item 10: Establish safety plan and place copy in chart</label>
                                        </div>

                                        <!-- SCREENING -->
<h4 class="form-section-title">── SCREENING ──</h4>
<div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
        <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-scr-hgb-chk"> Hemoglobin</label>
        <input type="text" id="peds-12m-scr-hgb-val" class="form-control" style="width: 120px;">
    </div>
    <div style="display: flex; align-items: center; gap: 8px;">
        <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-scr-lead-chk"> Lead level</label>
        <input type="text" id="peds-12m-scr-lead-val" class="form-control" style="width: 120px;">
    </div>
    <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-scr-dental"> GPCH DENTAL SCREENING AND EDUCATION</label>
</div>

                                        <!-- 24. VACCINES -->
                                        <h4 class="form-section-title">── VACCINES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-12m-vac-none"> None</label>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-12m-vac-hepa-chk"> Hepatitis A #</label><input type="text" id="peds-12m-vac-hepa-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-12m-vac-mmr-chk"> MMR #</label><input type="text" id="peds-12m-vac-mmr-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-12m-vac-var-chk"> Varicella #</label><input type="text" id="peds-12m-vac-var-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-12m-vac-pcv-chk"> PCV #</label><input type="text" id="peds-12m-vac-pcv-val" class="form-control" style="width: 80px;"></div>
<label style="cursor: pointer;"><input type="checkbox" id="peds-12m-vac-vis"> Vaccine Information Sheet given</label>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-12m-vac-dtap-chk"> DTaP #</label><input type="text" id="peds-12m-vac-dtap-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-12m-vac-hib-chk"> Hib #</label><input type="text" id="peds-12m-vac-hib-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-12m-vac-hepb-chk"> Hepatitis B #</label><input type="text" id="peds-12m-vac-hepb-val" class="form-control" style="width: 80px;"></div>
<div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-12m-vac-ipv-chk"> IPV #</label><input type="text" id="peds-12m-vac-ipv-val" class="form-control" style="width: 80px;"></div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Accordion Section: CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE FIFTEEN MONTH WELL CHILD VISIT -->
                        <div class="accordion-item peds-only hidden">
                            <button class="accordion-header" aria-expanded="false" aria-controls="accordion-peds-fifteen-month" id="accordion-peds-fifteen-month-btn">
                                CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE FIFTEEN MONTH WELL CHILD VISIT
                            </button>
                            <div id="accordion-peds-fifteen-month" class="accordion-content hidden" role="region" aria-labelledby="accordion-peds-fifteen-month-btn">
                                <form class="mod-clinical-style-18" id="peds-fifteen-month-form" novalidate>
                                    <div class="mod-clinical-style-19">
                                        <!-- Date Header -->
                                        <div class="form-group" style="max-width: 250px; margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">Date</label>
                                            <input type="date" id="peds-15m-visit-date" class="form-control">
                                        </div>

                                        <!-- 1. IDENTIFICATION -->
                                        <h4 class="form-section-title">── IDENTIFICATION ──</h4>
                                        <div style="margin-bottom: 12px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                            <label class="form-label" style="margin: 0; min-width: 90px;">LANGUAGE:</label>
                                            <input type="text" id="peds-15m-language" class="form-control" style="width: 250px;" placeholder="Primary Language...">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-lang-interp"> Interpreter</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-lang-prov-speaks"> Provider speaks language</label>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">ACCOMPANIED BY:</label>
                                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-acc-mom"> Mom</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-acc-dad"> Dad</label>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-acc-other-chk"> Other:</label>
                                                    <input type="text" id="peds-15m-acc-other-txt" class="form-control" style="width: 200px;" placeholder="Specify relationship...">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Wt</strong>
                                                    <input type="text" id="peds-15m-wt-kg" class="form-control" style="width: 80px;" placeholder="kg"> <span>kg</span>
                                                    <span>(</span><input type="text" id="peds-15m-wt-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-wt-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Ht</strong>
                                                    <input type="text" id="peds-15m-ht-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-15m-ht-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-ht-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>HC</strong>
                                                    <input type="text" id="peds-15m-hc-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-15m-hc-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-hc-track"> tracking</label>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">CONCERNS:</label>
                                            <textarea id="peds-15m-concerns" class="form-control" rows="2" placeholder="Parental or provider concerns..."></textarea>
                                        </div>

                                        <!-- 2. INTERVAL HISTORY -->
                                        <h4 class="form-section-title">── INTERVAL HISTORY ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 6px;">(significant changes, illnesses, events since last visit)</p>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <textarea id="peds-15m-interval-history" class="form-control" rows="2" placeholder="Interval history notes..."></textarea>
                                        </div>
                                        <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px; background: #f1f5f9; padding: 8px 12px; border-radius: 4px;">
                                            <span>T</span> <input type="text" id="peds-15m-temp" class="form-control" style="width: 70px;">
                                            <span>P</span> <input type="text" id="peds-15m-pulse" class="form-control" style="width: 70px;">
                                            <span>RR</span> <input type="text" id="peds-15m-rr" class="form-control" style="width: 70px;">
                                            <span>BP</span> <input type="text" id="peds-15m-bp" class="form-control" style="width: 90px;">
                                        </div>

                                        <!-- 3. FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS -->
                                        <h4 class="form-section-title">── FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-15m-followup-problems" class="form-control" rows="2" placeholder="Follow up problem notes..."></textarea>
                                        </div>

                                        <!-- 4. NUTRITION -->
                                        <h4 class="form-section-title">── NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-nutr-milk-chk"> Milk per day:</label>
                                                <input type="text" id="peds-15m-nutr-milk-oz" class="form-control" style="width: 80px;" placeholder="oz"> <span>oz. (target 16-24 oz.)</span>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-nutr-intake"> Appropriate intake of protein, iron, calcium, fruit/vegetables</label>
                                        </div>

                                        <!-- 5. ALLERGIES / VACCINE REACTIONS -->
                                        <h4 class="form-section-title">── ALLERGIES / VACCINE REACTIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-allergies-none"> No known drug, food, or environmental allergies</label>
                                        </div>

                                        <!-- 6. ELIMINATION -->
                                        <h4 class="form-section-title">── ELIMINATION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-elim-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-elim-soft-stools"> Soft stools</label>
                                        </div>

                                        <!-- 7. IMMUNIZATIONS -->
                                        <h4 class="form-section-title">── IMMUNIZATIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-imm-up-to-date"> Record reviewed - up to date</label>
                                        </div>

                                        <!-- 8. PAST MEDICAL HISTORY -->
                                        <h4 class="form-section-title">── PAST MEDICAL HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pmh-full-term"> Full Term</label>
                                        </div>

                                        <!-- 9. FAMILY HISTORY -->
                                        <h4 class="form-section-title">── FAMILY HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-15m-family-history" class="form-control" rows="2" placeholder="Family history notes..."></textarea>
                                        </div>

                                        <!-- 10. FAMILY STRUCTURE, PRIMARY CARETAKER(S) -->
                                        <h4 class="form-section-title">── FAMILY STRUCTURE, PRIMARY CARETAKER(S) ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-15m-family-structure" class="form-control" rows="2" placeholder="Family structure details..."></textarea>
                                        </div>

                                        <!-- 11. SLEEP -->
                                        <h4 class="form-section-title">── SLEEP ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-sleep-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-sleep-appr-amt"> Appropriate amount (target 11-14 hours/day, including naps)</label>
                                        </div>

                                        <!-- 12. SOCIAL / ENVIRONMENTAL SCREENING -->
                                        <h4 class="form-section-title">── SOCIAL / ENVIRONMENTAL SCREENING ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-soc-food-insec"> <strong>Food insecurity</strong></label>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-soc-housing-insec"> <strong>Housing insecurity</strong></label>
                                                <div style="margin-left: 24px; display: flex; flex-direction: column; gap: 4px; margin-top: 4px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-soc-house-moved"> ○ Moved more than once in past year</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-soc-house-density"> ○ &gt;2 people/bedroom</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-soc-house-families"> ○ &gt;1 family/home</label>
                                                </div>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-soc-dv"> <strong>Domestic violence</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-soc-guns"> <strong>Guns</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-soc-tobacco"> <strong>Tobacco</strong></label>
                                        </div>

                                        <!-- 13. DEVELOPMENTAL SURVEILLANCE -->
                                        <h4 class="form-section-title">── DEVELOPMENTAL SURVEILLANCE ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-dev-walks-well"> Walks well</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-dev-stoops"> Stoops and recovers</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-dev-stacks2"> Stacks 2 blocks/objects</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-dev-scribbles"> Scribbles</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-dev-drinks-cup"> Drinks from a cup</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-dev-uses-spoon"> Uses spoon</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-dev-3-6words"> 3-6 words</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-dev-points-pics"> Points to pictures in book</label>
                                        </div>

                                        <!-- 14. PHYSICAL EXAM -->
                                        <h4 class="form-section-title">── PHYSICAL EXAM ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-general"> General: well-appearing</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-skin"> Skin: no jaundice, no rash, no lesions</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-head"> Head: normocephalic, open anterior fontanelle</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-eyes"> Eyes: symmetric red reflex – no strabismus</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-ent"> ENT: intact palate, patent nares</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-neck"> Neck: supple, no lymphadenopathy, no torticollis</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-cv"> CV: RRR, no murmurs, NI S1 & S2, good femoral pulses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-chest"> Chest: clear, no retractions/grunting/flaring</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-abd"> Abdomen: soft, no hepatosplenomegaly, no masses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-gu"> GU: NI female/male, testes descended</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-back"> Back: intact spine, no sacral dimple/pit</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-ext"> Ext/Hips: stable hips, well perfused, no deformity</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-pe-neuro"> Neuro: symmetric movements, normal tone, no deficits</label>
                                        </div>

                                        <!-- 15. ASSESSMENT -->
                                        <h4 class="form-section-title">── ASSESSMENT ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>1.</span> <input type="text" id="peds-15m-ass-age" class="form-control" style="width: 80px;" placeholder="15"> <span>month old</span>
                                            </div>
                                            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-ass-healthy"> Healthy child</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-ass-growth"> Good growth</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-ass-dev"> Normal development</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-ass-social"> No significant social concerns</label>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>2.</span> <input type="text" id="peds-15m-ass-note2" class="form-control" placeholder="Assessment note 2...">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>3.</span> <input type="text" id="peds-15m-ass-note3" class="form-control" placeholder="Assessment note 3...">
                                            </div>
                                        </div>

                                        <!-- 16. PLAN / VACCINES -->
                                        <h4 class="form-section-title">── PLAN / VACCINES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-vac-none"> None</label>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-15m-vac-hepa-chk"> Hepatitis A#</label><input type="text" id="peds-15m-vac-hepa-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-15m-vac-mmr-chk"> MMR #</label><input type="text" id="peds-15m-vac-mmr-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-15m-vac-var-chk"> Varicella #</label><input type="text" id="peds-15m-vac-var-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-15m-vac-pcv-chk"> PCV #</label><input type="text" id="peds-15m-vac-pcv-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-15m-vac-dtap-chk"> DTaP #</label><input type="text" id="peds-15m-vac-dtap-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-15m-vac-hib-chk"> Hib #</label><input type="text" id="peds-15m-vac-hib-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-15m-vac-hepb-chk"> Hepatitis B#</label><input type="text" id="peds-15m-vac-hepb-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-15m-vac-ipv-chk"> IPV #</label><input type="text" id="peds-15m-vac-ipv-val" class="form-control" style="width: 80px;"></div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-vac-vis"> Vaccine Information Sheet given</label>
                                        </div>

                                        <!-- 17. SCREENING -->
                                        <h4 class="form-section-title">── SCREENING ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-scr-hgb-chk"> Hemoglobin</label>
                                                <input type="text" id="peds-15m-scr-hgb-val" class="form-control" style="width: 120px;">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-scr-lead-chk"> Lead level</label>
                                                <input type="text" id="peds-15m-scr-lead-val" class="form-control" style="width: 120px;">
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-scr-dental"> GPCH DENTAL SCREENING AND EDUCATION</label>
                                        </div>

                                        <!-- 18. GUIDANCE TO FAMILIES -->
                                        <h4 class="form-section-title">── GUIDANCE TO FAMILIES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-gui-handout"> Bright Futures handout provided</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-gui-tantrums"> Tantrums, limit setting, consistency</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-gui-wean-bottle"> Wean from bottle</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-gui-car-seat"> Car seat facing backwards until age 2</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-gui-choking"> Choking risk (food, coins, small objects)</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-gui-poison"> Keep Poison Control number by phone</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-gui-water"> Water safety (tubs, toilets, pools)</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-gui-teeth"> Brush teeth twice a day; grain of rice-sized amount of toothpaste</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-gui-dental-visit"> Dental visit</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-gui-read-book"> REACH OUT AND READ BOOK GIVEN</label>
                                        </div>

                                        <!-- 19. FOLLOW-UP VISITS -->
                                        <h4 class="form-section-title">── FOLLOW-UP VISITS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-followup-wcc"> 3 months for WCC</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-15m-followup-other-chk"> Other follow-up:</label>
                                                <input type="text" id="peds-15m-followup-other-txt" class="form-control" style="width: 250px;" placeholder="Other follow-up details...">
                                            </div>
                                        </div>

                                        <!-- 20. PROBLEMS FOR FOLLOW-UP -->
                                        <h4 class="form-section-title">── PROBLEMS FOR FOLLOW-UP ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-15m-problems-followup" class="form-control" rows="2" placeholder="Problems for follow-up..."></textarea>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Accordion Section: CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE EIGHTEEN MONTH WELL CHILD VISIT -->
                        <div class="accordion-item peds-only hidden">
                            <button class="accordion-header" aria-expanded="false" aria-controls="accordion-peds-eighteen-month" id="accordion-peds-eighteen-month-btn">
                                CLINIC VISITS • PRIMARY CARE CLINIC - PROGRESS NOTE EIGHTEEN MONTH WELL CHILD VISIT
                            </button>
                            <div id="accordion-peds-eighteen-month" class="accordion-content hidden" role="region" aria-labelledby="accordion-peds-eighteen-month-btn">
                                <form class="mod-clinical-style-18" id="peds-eighteen-month-form" novalidate>
                                    <div class="mod-clinical-style-19">
                                        <!-- Date Header -->
                                        <div class="form-group" style="max-width: 250px; margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">Date</label>
                                            <input type="date" id="peds-18m-visit-date" class="form-control">
                                        </div>

                                        <!-- 1. IDENTIFICATION -->
                                        <h4 class="form-section-title">── IDENTIFICATION ──</h4>
                                        <div style="margin-bottom: 12px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                            <label class="form-label" style="margin: 0; min-width: 90px;">LANGUAGE:</label>
                                            <input type="text" id="peds-18m-language" class="form-control" style="width: 250px;" placeholder="Primary Language...">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-lang-interp"> Interpreter</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-lang-prov-speaks"> Provider speaks language</label>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">ACCOMPANIED BY:</label>
                                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-acc-mom"> Mom</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-acc-dad"> Dad</label>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-acc-other-chk"> Other:</label>
                                                    <input type="text" id="peds-18m-acc-other-txt" class="form-control" style="width: 200px;" placeholder="Specify relationship...">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Wt</strong>
                                                    <input type="text" id="peds-18m-wt-kg" class="form-control" style="width: 80px;" placeholder="kg"> <span>kg</span>
                                                    <span>(</span><input type="text" id="peds-18m-wt-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-wt-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>Ht</strong>
                                                    <input type="text" id="peds-18m-ht-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-18m-ht-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-ht-track"> tracking</label>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <strong>HC</strong>
                                                    <input type="text" id="peds-18m-hc-cm" class="form-control" style="width: 80px;" placeholder="cm"> <span>cm</span>
                                                    <span>(</span><input type="text" id="peds-18m-hc-pct" class="form-control" style="width: 60px;" placeholder="%"> <span>%ile)</span>
                                                </div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-hc-track"> tracking</label>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label class="form-label" style="font-weight: 600;">CONCERNS:</label>
                                            <textarea id="peds-18m-concerns" class="form-control" rows="2" placeholder="Parental or provider concerns..."></textarea>
                                        </div>

                                        <!-- 2. INTERVAL HISTORY -->
                                        <h4 class="form-section-title">── INTERVAL HISTORY ──</h4>
                                        <p style="font-style: italic; color: #64748b; margin-bottom: 6px;">(significant changes, illnesses, events since last visit)</p>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <textarea id="peds-18m-interval-history" class="form-control" rows="2" placeholder="Interval history notes..."></textarea>
                                        </div>
                                        <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px; background: #f1f5f9; padding: 8px 12px; border-radius: 4px;">
                                            <span>T</span> <input type="text" id="peds-18m-temp" class="form-control" style="width: 70px;">
                                            <span>P</span> <input type="text" id="peds-18m-pulse" class="form-control" style="width: 70px;">
                                            <span>RR</span> <input type="text" id="peds-18m-rr" class="form-control" style="width: 70px;">
                                            <span>BP</span> <input type="text" id="peds-18m-bp" class="form-control" style="width: 90px;">
                                        </div>

                                        <!-- 3. FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS -->
                                        <h4 class="form-section-title">── FOLLOW UP – PROBLEMS FROM PREVIOUS VISITS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-18m-followup-problems" class="form-control" rows="2" placeholder="Follow up problem notes..."></textarea>
                                        </div>

                                        <!-- 4. MEDICATIONS -->
                                        <h4 class="form-section-title">── MEDICATIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-18m-medications" class="form-control" rows="2" placeholder="Current medications..."></textarea>
                                        </div>

                                        <!-- 5. NUTRITION -->
                                        <h4 class="form-section-title">── NUTRITION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-nutr-milk-chk"> Milk per day:</label>
                                                <input type="text" id="peds-18m-nutr-milk-oz" class="form-control" style="width: 80px;" placeholder="oz"> <span>oz. (target 16-24 oz.)</span>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-nutr-intake"> Appropriate intake of protein, iron, calcium, fruit/vegetables</label>
                                        </div>

                                        <!-- 6. ALLERGIES / VACCINE REACTIONS -->
                                        <h4 class="form-section-title">── ALLERGIES / VACCINE REACTIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-allergies-none"> No known drug, food, or environmental allergies</label>
                                        </div>

                                        <!-- 7. ELIMINATION -->
                                        <h4 class="form-section-title">── ELIMINATION ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-elim-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-elim-soft-stools"> Soft stools</label>
                                        </div>

                                        <!-- 8. IMMUNIZATIONS -->
                                        <h4 class="form-section-title">── IMMUNIZATIONS ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-imm-up-to-date"> Record reviewed - up to date</label>
                                        </div>

                                        <!-- 9. PAST MEDICAL HISTORY -->
                                        <h4 class="form-section-title">── PAST MEDICAL HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pmh-full-term"> Full Term</label>
                                        </div>

                                        <!-- 10. FAMILY HISTORY -->
                                        <h4 class="form-section-title">── FAMILY HISTORY ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-18m-family-history" class="form-control" rows="2" placeholder="Family history notes..."></textarea>
                                        </div>

                                        <!-- 11. FAMILY STRUCTURE, PRIMARY CARETAKER(S) -->
                                        <h4 class="form-section-title">── FAMILY STRUCTURE, PRIMARY CARETAKER(S) ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-18m-family-structure" class="form-control" rows="2" placeholder="Family structure details..."></textarea>
                                        </div>

                                        <!-- 12. SLEEP -->
                                        <h4 class="form-section-title">── SLEEP ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-sleep-no-concerns"> No concerns</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-sleep-appr-amt"> Appropriate amount (target 11-14 hours/day, including naps)</label>
                                        </div>

                                        <!-- 13. SOCIAL / ENVIRONMENTAL SCREENING -->
                                        <h4 class="form-section-title">── SOCIAL / ENVIRONMENTAL SCREENING ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-soc-food-insec"> <strong>Food insecurity</strong></label>
                                            <div>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-soc-housing-insec"> <strong>Housing insecurity</strong></label>
                                                <div style="margin-left: 24px; display: flex; flex-direction: column; gap: 4px; margin-top: 4px;">
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-soc-house-moved"> ○ Moved more than once in past year</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-soc-house-density"> ○ &gt;2 people/bedroom</label>
                                                    <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-soc-house-families"> ○ &gt;1 family/home</label>
                                                </div>
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-soc-dv"> <strong>Domestic violence</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-soc-guns"> <strong>Guns</strong></label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-soc-tobacco"> <strong>Tobacco</strong></label>
                                        </div>

                                        <!-- 14. DEVELOPMENTAL SCREENING -->
                                        <h4 class="form-section-title">── DEVELOPMENTAL SCREENING ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 12px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-mchat-passed"> MCHAT passed</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-asq-passed"> ASQ passed</label>
                                        </div>
                                        <p style="font-weight: 600; margin-top: 8px; margin-bottom: 4px;">SAMPLE MILESTONES</p>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-walks-back"> Walks backward</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-runs-stiffly"> Runs stiffly</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-throws-ball"> Throws ball</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-walks-steps"> Walk up steps with help</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-stacks3"> Stacks 3 blocks</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-imitates-housework"> Imitates housework/"helps" in house</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-4-20words"> 4-20 words</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-points-body"> Points to &gt; 1 body part</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-names-pics"> Names pictures in a book</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-dev-follows-dirs"> Follows simple directions</label>
                                        </div>

                                        <!-- 15. PHYSICAL EXAM -->
                                        <h4 class="form-section-title">── PHYSICAL EXAM ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-general"> General: well-appearing</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-skin"> Skin: no jaundice, no rash, no lesions</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-head"> Head: normocephalic, open anterior fontanelle</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-eyes"> Eyes: symmetric red reflex – no strabismus</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-ent"> ENT: intact palate, patent nares</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-neck"> Neck: supple, no lymphadenopathy, no torticollis</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-cv"> CV: RRR, no murmurs, NI S1 & S2, good femoral pulses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-chest"> Chest: clear, no retractions/grunting/flaring</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-abd"> Abdomen: soft, no hepatosplenomegaly, no masses</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-gu"> GU: NI female/male, testes descended</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-back"> Back: intact spine, no sacral dimple/pit</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-ext"> Ext/Hips: stable hips, well perfused, no deformity</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-pe-neuro"> Neuro: symmetric movements, normal tone, no deficits</label>
                                        </div>

                                        <!-- 16. ASSESSMENT -->
                                        <h4 class="form-section-title">── ASSESSMENT ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>1.</span> <input type="text" id="peds-18m-ass-age" class="form-control" style="width: 80px;" placeholder="18"> <span>month old</span>
                                            </div>
                                            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-ass-healthy"> Healthy child</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-ass-growth"> Good growth</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-ass-dev"> Normal development</label>
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-ass-social"> No significant social concerns</label>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>2.</span> <input type="text" id="peds-18m-ass-note2" class="form-control" placeholder="Assessment note 2...">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span>3.</span> <input type="text" id="peds-18m-ass-note3" class="form-control" placeholder="Assessment note 3...">
                                            </div>
                                        </div>

                                        <!-- 17. PLAN / VACCINES -->
                                        <h4 class="form-section-title">── PLAN / VACCINES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-vac-none"> None</label>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-18m-vac-hepa-chk"> Hepatitis A#</label><input type="text" id="peds-18m-vac-hepa-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-18m-vac-mmr-chk"> MMR #</label><input type="text" id="peds-18m-vac-mmr-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-18m-vac-var-chk"> Varicella #</label><input type="text" id="peds-18m-vac-var-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-18m-vac-pcv-chk"> PCV #</label><input type="text" id="peds-18m-vac-pcv-val" class="form-control" style="width: 80px;"></div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-vac-vis"> Vaccine Information Sheet given</label>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-18m-vac-dtap-chk"> DTaP #</label><input type="text" id="peds-18m-vac-dtap-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-18m-vac-hib-chk"> Hib #</label><input type="text" id="peds-18m-vac-hib-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-18m-vac-hepb-chk"> Hepatitis B#</label><input type="text" id="peds-18m-vac-hepb-val" class="form-control" style="width: 80px;"></div>
                                            <div style="display: flex; gap: 12px; align-items: center;"><label style="width: 150px;"><input type="checkbox" id="peds-18m-vac-ipv-chk"> IPV #</label><input type="text" id="peds-18m-vac-ipv-val" class="form-control" style="width: 80px;"></div>
                                        </div>

                                        <!-- 18. SCREENING -->
                                        <h4 class="form-section-title">── SCREENING ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-scr-hgb-chk"> Hemoglobin</label>
                                                <input type="text" id="peds-18m-scr-hgb-val" class="form-control" style="width: 120px;">
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-scr-lead-chk"> Lead level</label>
                                                <input type="text" id="peds-18m-scr-lead-val" class="form-control" style="width: 120px;">
                                            </div>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-scr-dental"> GPCH DENTAL SCREENING AND EDUCATION</label>
                                        </div>

                                        <!-- 19. GUIDANCE TO FAMILIES -->
                                        <h4 class="form-section-title">── GUIDANCE TO FAMILIES ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-gui-handout"> Bright Futures handout provided</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-gui-tantrums"> Tantrums, limit setting, consistency</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-gui-car-seat"> Car seat facing backwards until age 2</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-gui-choking"> Choking risk (food, coins, small objects)</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-gui-poison"> Keep Poison Control number by phone</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-gui-water"> Water safety (tubs, toilets, pools)</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-gui-teeth"> Brush teeth twice a day; grain of rice-sized amount of toothpaste</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-gui-dental-visit"> Dental visit</label>
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-gui-read-book"> REACH OUT AND READ BOOK GIVEN</label>
                                        </div>

                                        <!-- 20. FOLLOW-UP VISITS -->
                                        <h4 class="form-section-title">── FOLLOW-UP VISITS ──</h4>
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                                            <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-followup-wcc"> 6 months for WCC</label>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <label style="cursor: pointer;"><input type="checkbox" id="peds-18m-followup-other-chk"> Other follow-up:</label>
                                                <input type="text" id="peds-18m-followup-other-txt" class="form-control" style="width: 250px;" placeholder="Other follow-up details...">
                                            </div>
                                        </div>

                                        <!-- 21. PROBLEMS FOR FOLLOW UP -->
                                        <h4 class="form-section-title">── PROBLEMS FOR FOLLOW UP ──</h4>
                                        <div class="form-group" style="margin-bottom: 16px;">
                                            <textarea id="peds-18m-problems-followup" class="form-control" rows="2" placeholder="Problems for follow up..."></textarea>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

<!-- Accordion Section: Active Allergies, Past Medical History & Current Medications -->
<div class="accordion-item" style="display: none !important;">
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
                    <textarea id="clinical-pmh" class="form-control" rows="3" placeholder="e.g. Hypertension, Type 2 Diabetes..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="clinical-medications">Current Active Medications</label>
                    <textarea id="clinical-medications" class="form-control" rows="3" placeholder="e.g. Lisinopril 10mg daily..."></textarea>
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
                    <input type="text" id="fh-condition-input" class="form-control" placeholder="e.g. Type 2 Diabetes, Hypertension">
                </div>
                <div class="form-group">
                    <label class="form-label" for="fh-start-date-input">Onset Date / Age</label>
                    <input type="text" id="fh-start-date-input" class="form-control" placeholder="e.g. 2018 or Age 45">
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
        </form>
    </div>
</div>

<!-- Accordion Section: Family Medicine / Internal Medicine SOAP & Physical Exam -->
<div class="accordion-item general-only">
    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-soap" id="accordion-soap-btn">
        Family Medicine / Internal Medicine SOAP & Physical Exam
    </button>
    <div id="accordion-soap" class="accordion-content hidden" role="region" aria-labelledby="accordion-soap-btn">
        <form class="mod-clinical-style-12" id="soap-form" novalidate>
            <div class="form-group">
                <label class="form-label" for="chief-complaint">Chief Complaint (CC)</label>
                <textarea id="chief-complaint" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="hpi">History of Present Illness (HPI)</label>
                <textarea id="hpi" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="ros">Review of Systems (ROS)</label>
                <textarea id="ros" class="form-control" rows="2"></textarea>
            </div>
            <h4 class="mod-clinical-style-43">Physical Examination (PE)</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group"><label class="form-label" for="pe-general">General Appearance</label><input type="text" id="pe-general" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pe-heent">HEENT</label><input type="text" id="pe-heent" class="form-control"></div>
            </div>
            <div class="mod-clinical-style-39">
                <div class="form-group"><label class="form-label" for="pe-cardio">Cardiovascular</label><input type="text" id="pe-cardio" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pe-resp">Respiratory / Lungs</label><input type="text" id="pe-resp" class="form-control"></div>
            </div>
            <div class="mod-clinical-style-33">
                <div class="form-group"><label class="form-label" for="pe-abdomen">Abdomen</label><input type="text" id="pe-abdomen" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pe-neuro">Neurological</label><input type="text" id="pe-neuro" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pe-skin">Skin</label><input type="text" id="pe-skin" class="form-control"></div>
            </div>
            
            <!-- FM Detailed Checklists -->
            <h4 class="mod-clinical-style-43">General Health & Lifestyle</h4>
            <div class="mod-clinical-style-15">
                <div class="form-group">
                    <label class="form-label" for="fm-overall-health">Overall Health</label>
                    <select id="fm-overall-health" class="form-control"><option value="Excellent">Excellent</option><option value="Good" selected>Good</option><option value="Fair">Fair</option><option value="Poor">Poor</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="fm-fatigue">Fatigue Level</label>
                    <select id="fm-fatigue" class="form-control"><option value="None" selected>None</option><option value="Mild">Mild</option><option value="Moderate">Moderate</option><option value="Severe">Severe</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="fm-smoking">Smoking Status</label>
                    <select id="fm-smoking" class="form-control"><option value="Never" selected>Never</option><option value="Former">Former</option><option value="Current">Current</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="fm-alcohol">Alcohol Use</label>
                    <select id="fm-alcohol" class="form-control"><option value="None">None</option><option value="Occasional" selected>Occasional</option><option value="Moderate">Moderate</option><option value="Heavy">Heavy</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="fm-exercise">Exercise Level</label>
                    <select id="fm-exercise" class="form-control"><option value="Sedentary">Sedentary</option><option value="Light" selected>Light</option><option value="Moderate">Moderate</option><option value="Active">Active</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="fm-diet">Diet</label>
                    <select id="fm-diet" class="form-control"><option value="Standard">Standard</option><option value="Healthy" selected>Healthy</option><option value="Vegetarian">Vegetarian</option><option value="Poor">Poor</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="fm-sleep-hours">Sleep Hours</label>
                    <input type="number" id="fm-sleep-hours" class="form-control" value="7">
                </div>
                <div class="form-group">
                    <label class="form-label" for="fm-occupation">Occupation</label>
                    <input type="text" id="fm-occupation" class="form-control">
                </div>
            </div>
            <div class="form-group"><label class="form-label" for="fm-current-concerns">Current Concerns</label><textarea id="fm-current-concerns" class="form-control" rows="2"></textarea></div>
            <div class="form-group"><label class="form-label" for="fm-lifestyle-comments">Lifestyle Comments</label><textarea id="fm-lifestyle-comments" class="form-control" rows="2"></textarea></div>
            <div class="form-group"><label class="form-label" for="fm-ros-notes">Additional ROS Notes</label><textarea id="fm-ros-notes" class="form-control" rows="2"></textarea></div>
            
            <h4 class="mod-clinical-style-43">Pain Assessment</h4>
            <div class="mod-clinical-style-15">
                <div class="form-group"><label class="form-label" for="fm-pain-score">Pain Score (0-10)</label><input type="number" id="fm-pain-score" class="form-control" min="0" max="10" value="0"></div>
                <div class="form-group"><label class="form-label" for="fm-pain-location">Pain Location</label><input type="text" id="fm-pain-location" class="form-control"></div>
                <div class="form-group">
                    <label class="form-label" for="fm-pain-type">Pain Type</label>
                    <select id="fm-pain-type" class="form-control"><option value="None" selected>None</option><option value="Aching">Aching</option><option value="Burning">Burning</option><option value="Sharp">Sharp</option><option value="Throbbing">Throbbing</option></select>
                </div>
                <div class="form-group"><label class="form-label" for="fm-pain-duration">Pain Duration</label><input type="text" id="fm-pain-duration" class="form-control"></div>
            </div>
            <div class="form-group"><label class="form-label" for="fm-pain-notes">Pain Notes</label><textarea id="fm-pain-notes" class="form-control" rows="2"></textarea></div>

            <h4 class="mod-clinical-style-43">Preventative Maintenance / Screening</h4>
            <div class="mod-clinical-style-15">
                <div class="form-group"><label class="form-label" for="fm-flu-date">Last Flu Vaccine</label><input type="date" id="fm-flu-date" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="fm-covid-date">Last COVID Vaccine</label><input type="date" id="fm-covid-date" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="fm-eye-date">Last Eye Exam</label><input type="date" id="fm-eye-date" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="fm-dental-date">Last Dental Exam</label><input type="date" id="fm-dental-date" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="fm-colonoscopy-date">Last Colonoscopy</label><input type="date" id="fm-colonoscopy-date" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="fm-mammogram-date">Last Mammogram</label><input type="date" id="fm-mammogram-date" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="fm-pap-date">Last Pap Smear</label><input type="date" id="fm-pap-date" class="form-control"></div>
            </div>

            <h4 class="mod-clinical-style-43">Mental Health Screen</h4>
            <div class="mod-clinical-style-15">
                <div class="form-group">
                    <label class="form-label" for="fm-mh-down">Feeling Down/Depressed</label>
                    <select id="fm-mh-down" class="form-control"><option value="Never" selected>Never</option><option value="Several Days">Several Days</option><option value="More than half the days">More than half the days</option><option value="Nearly every day">Nearly every day</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="fm-mh-nervous">Feeling Nervous/Anxious</label>
                    <select id="fm-mh-nervous" class="form-control"><option value="Never" selected>Never</option><option value="Several Days">Several Days</option><option value="More than half the days">More than half the days</option><option value="Nearly every day">Nearly every day</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="fm-mh-sleep">Sleep Problems</label>
                    <select id="fm-mh-sleep" class="form-control"><option value="No" selected>No</option><option value="Yes">Yes</option></select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="fm-mh-stress">Stress Level</label>
                    <select id="fm-mh-stress" class="form-control"><option value="Low">Low</option><option value="Mild" selected>Mild</option><option value="High">High</option></select>
                </div>
            </div>
            <div class="form-group"><label class="form-label" for="fm-mh-comments">Mental Health Comments</label><textarea id="fm-mh-comments" class="form-control" rows="2"></textarea></div>
        </form>
    </div>
</div>

<!-- Accordion Section: Functional Status & Geriatric Assessment -->
<div class="accordion-item general-only">
    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-functional" id="accordion-functional-btn">
        Functional Status & Geriatric Assessment
    </button>
    <div id="accordion-functional" class="accordion-content hidden" role="region" aria-labelledby="accordion-functional-btn">
        <form class="mod-clinical-style-12" id="functional-form" novalidate>
            <h4 class="mod-clinical-style-54">Activities of Daily Living (ADLs)</h4>
            <div class="mod-clinical-style-33">
                <div class="form-group"><label class="form-label" for="adl-bathing">Bathing</label><select id="adl-bathing" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
                <div class="form-group"><label class="form-label" for="adl-dressing">Dressing</label><select id="adl-dressing" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
                <div class="form-group"><label class="form-label" for="adl-toileting">Toileting</label><select id="adl-toileting" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
                <div class="form-group"><label class="form-label" for="adl-transferring">Transferring</label><select id="adl-transferring" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
                <div class="form-group"><label class="form-label" for="adl-continence">Continence</label><select id="adl-continence" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
                <div class="form-group"><label class="form-label" for="adl-feeding">Feeding</label><select id="adl-feeding" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
            </div>

            <h4 class="mod-clinical-style-43">Instrumental Activities of Daily Living (IADLs)</h4>
            <div class="mod-clinical-style-33">
                <div class="form-group"><label class="form-label" for="iadl-shopping">Shopping</label><select id="iadl-shopping" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
                <div class="form-group"><label class="form-label" for="iadl-meals">Meal Preparation</label><select id="iadl-meals" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
                <div class="form-group"><label class="form-label" for="iadl-housekeeping">Housekeeping</label><select id="iadl-housekeeping" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
                <div class="form-group"><label class="form-label" for="iadl-laundry">Laundry</label><select id="iadl-laundry" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
                <div class="form-group"><label class="form-label" for="iadl-transportation">Transportation</label><select id="iadl-transportation" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
                <div class="form-group"><label class="form-label" for="iadl-meds">Medications</label><select id="iadl-meds" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
                <div class="form-group"><label class="form-label" for="iadl-finance">Finances</label><select id="iadl-finance" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Dependent">Dependent</option></select></div>
            </div>

            <h4 class="mod-clinical-style-43">Mobility & Fall Risk</h4>
            <div class="mod-clinical-style-15">
                <div class="form-group"><label class="form-label" for="mob-walking">Walking</label><select id="mob-walking" class="form-control"><option value="Normal">Normal</option><option value="Slow">Slow</option><option value="Unsteady">Unsteady</option></select></div>
                <div class="form-group"><label class="form-label" for="mob-distance">Distance</label><input type="text" id="mob-distance" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="mob-device">Assistive Device</label><select id="mob-device" class="form-control"><option value="None">None</option><option value="Cane">Cane</option><option value="Walker">Walker</option><option value="Wheelchair">Wheelchair</option></select></div>
                <div class="form-group"><label class="form-label" for="mob-stairs">Stairs</label><select id="mob-stairs" class="form-control"><option value="Independent">Independent</option><option value="Needs Assistance">Needs Assistance</option><option value="Unable">Unable</option></select></div>
            </div>
            
            <div class="mod-clinical-style-55">
                <div>
                    <label class="form-label mod-clinical-style-56">History of Falls (Past 12mo)?</label>
                    <label><input type="radio" name="func_falls_history" value="No" checked id="clinical-func_falls_history" aria-label="Func falls history"> No</label>
                    <label class="mod-clinical-style-57"><input type="radio" name="func_falls_history" value="Yes" id="clinical-func_falls_history" aria-label="Func falls history"> Yes</label>
                </div>
                <div class="form-group"><label class="form-label" for="func-falls-count">If yes, how many?</label><input type="number" id="func-falls-count" class="form-control" value="0" min="0"></div>
                <div>
                    <label class="form-label mod-clinical-style-56">Fall resulted in injury?</label>
                    <label><input type="radio" name="func_fall_injury" value="No" checked id="clinical-func_fall_injury" aria-label="Func fall injury"> No</label>
                    <label class="mod-clinical-style-57"><input type="radio" name="func_fall_injury" value="Yes" id="clinical-func_fall_injury" aria-label="Func fall injury"> Yes</label>
                </div>
            </div>
            
            <div class="mod-clinical-style-58">
                <div>
                    <label class="form-label mod-clinical-style-35">Difficulty Standing?</label>
                    <label><input type="radio" name="func_diff_standing" value="No" checked id="clinical-func_diff_standing" aria-label="Func diff standing"> No</label>
                    <label><input type="radio" name="func_diff_standing" value="Yes" id="clinical-func_diff_standing" aria-label="Func diff standing"> Yes</label>
                </div>
                <div>
                    <label class="form-label mod-clinical-style-35">Difficulty Walking?</label>
                    <label><input type="radio" name="func_diff_walking" value="No" checked id="clinical-func_diff_walking" aria-label="Func diff walking"> No</label>
                    <label><input type="radio" name="func_diff_walking" value="Yes" id="clinical-func_diff_walking" aria-label="Func diff walking"> Yes</label>
                </div>
                <div>
                    <label class="form-label mod-clinical-style-35">Unsteady Gait?</label>
                    <label><input type="radio" name="func_unsteady_gait" value="No" checked id="clinical-func_unsteady_gait" aria-label="Func unsteady gait"> No</label>
                    <label><input type="radio" name="func_unsteady_gait" value="Yes" id="clinical-func_unsteady_gait" aria-label="Func unsteady gait"> Yes</label>
                </div>
                <div>
                    <label class="form-label mod-clinical-style-35">Needs hand support?</label>
                    <label><input type="radio" name="func_hand_support" value="No" checked id="clinical-func_hand_support" aria-label="Func hand support"> No</label>
                    <label><input type="radio" name="func_hand_support" value="Yes" id="clinical-func_hand_support" aria-label="Func hand support"> Yes</label>
                </div>
            </div>
            <div class="form-group"><label class="form-label" for="func-balance-notes">Balance & Gait Notes</label><textarea id="func-balance-notes" class="form-control" rows="2"></textarea></div>
        </form>
    </div>
</div>

<!-- 1. Primary Care – Preventive Health / Annual Wellness -->
<div class="accordion-item general-only">
    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-pc-preventive" id="accordion-pc-preventive-btn">
        Primary Care – Preventive Health / Annual Wellness
    </button>
    <div id="accordion-pc-preventive" class="accordion-content hidden" role="region" aria-labelledby="accordion-pc-preventive-btn">
        <form class="mod-clinical-style-12" id="pc-preventive-form" novalidate>
            <h4 class="mod-clinical-style-43">Preventive Health Assessment</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-prev-overall-health">Overall Health</label>
                    <select id="pc-prev-overall-health" class="form-control">
                        <option value="Excellent" selected>Excellent</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-prev-general-wellness">General Wellness</label>
                    <select id="pc-prev-general-wellness" class="form-control">
                        <option value="Excellent">Excellent</option>
                        <option value="Good" selected>Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Preventive Screening</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-prev-bp-screen">Blood Pressure</label>
                    <select id="pc-prev-bp-screen" class="form-control">
                        <option value="Completed" selected>Completed</option>
                        <option value="Due">Due</option>
                        <option value="Pending">Pending</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-prev-diabetes-screen">Diabetes Screening</label>
                    <select id="pc-prev-diabetes-screen" class="form-control">
                        <option value="Completed" selected>Completed</option>
                        <option value="Due">Due</option>
                        <option value="Pending">Pending</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
            </div>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-prev-cholesterol-screen">Cholesterol Screening</label>
                    <select id="pc-prev-cholesterol-screen" class="form-control">
                        <option value="Completed" selected>Completed</option>
                        <option value="Due">Due</option>
                        <option value="Pending">Pending</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-prev-cancer-screen">Cancer Screening</label>
                    <select id="pc-prev-cancer-screen" class="form-control">
                        <option value="Completed" selected>Completed</option>
                        <option value="Due">Due</option>
                        <option value="Pending">Pending</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Immunizations</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-prev-flu-date">Influenza</label>
                    <input type="date" id="pc-prev-flu-date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-prev-covid-date">COVID-19</label>
                    <input type="date" id="pc-prev-covid-date" class="form-control">
                </div>
            </div>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-prev-pneumo-date">Pneumococcal</label>
                    <input type="date" id="pc-prev-pneumo-date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-prev-tdap-date">Tdap</label>
                    <input type="date" id="pc-prev-tdap-date" class="form-control">
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Lifestyle</h4>
            <div class="mod-clinical-style-33">
                <div class="form-group">
                    <label class="form-label" for="pc-prev-exercise">Exercise</label>
                    <select id="pc-prev-exercise" class="form-control">
                        <option value="Sedentary">Sedentary</option>
                        <option value="Light">Light</option>
                        <option value="Moderate" selected>Moderate</option>
                        <option value="Active">Active</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-prev-diet">Diet</label>
                    <select id="pc-prev-diet" class="form-control">
                        <option value="Standard">Standard</option>
                        <option value="Healthy" selected>Healthy</option>
                        <option value="Vegetarian">Vegetarian</option>
                        <option value="Poor">Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-prev-sleep-hours">Sleep Hours</label>
                    <input type="number" id="pc-prev-sleep-hours" class="form-control" value="7" min="0" max="24">
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Wellness Plan</h4>
            <div class="form-group">
                <label class="form-label" for="pc-prev-recommendations">Recommendations</label>
                <textarea id="pc-prev-recommendations" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="pc-prev-followup-date">Follow-up</label>
                <input type="date" id="pc-prev-followup-date" class="form-control">
            </div>
        </form>
    </div>
</div>

<!-- 2. Primary Care – Acute Sick Visit -->
<div class="accordion-item general-only">
    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-pc-acute" id="accordion-pc-acute-btn">
        Primary Care – Acute Sick Visit
    </button>
    <div id="accordion-pc-acute" class="accordion-content hidden" role="region" aria-labelledby="accordion-pc-acute-btn">
        <form class="mod-clinical-style-12" id="pc-acute-form" novalidate>
            <div class="form-group">
                <label class="form-label" for="pc-acute-chief-complaint">Chief Complaint</label>
                <textarea id="pc-acute-chief-complaint" class="form-control" rows="2"></textarea>
            </div>

            <h4 class="mod-clinical-style-43">Symptoms</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-acute-symptom">Symptom</label>
                    <input type="text" id="pc-acute-symptom" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-acute-onset-date">Onset</label>
                    <input type="date" id="pc-acute-onset-date" class="form-control">
                </div>
            </div>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-acute-duration">Duration</label>
                    <input type="text" id="pc-acute-duration" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-acute-severity">Severity</label>
                    <select id="pc-acute-severity" class="form-control">
                        <option value="Mild" selected>Mild</option>
                        <option value="Moderate">Moderate</option>
                        <option value="Severe">Severe</option>
                    </select>
                </div>
            </div>

            <h4 class="mod-clinical-style-43">HPI</h4>
            <div class="form-group">
                <textarea id="pc-acute-hpi" class="form-control" rows="3"></textarea>
            </div>

            <h4 class="mod-clinical-style-43">Review of Systems</h4>
            <div class="mod-clinical-style-33">
                <div class="form-group">
                    <label class="form-label" for="pc-acute-ros-general">General</label>
                    <select id="pc-acute-ros-general" class="form-control">
                        <option value="Normal" selected>Normal</option>
                        <option value="Abnormal">Abnormal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-acute-ros-resp">Respiratory</label>
                    <select id="pc-acute-ros-resp" class="form-control">
                        <option value="Normal" selected>Normal</option>
                        <option value="Abnormal">Abnormal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-acute-ros-cardiac">Cardiac</label>
                    <select id="pc-acute-ros-cardiac" class="form-control">
                        <option value="Normal" selected>Normal</option>
                        <option value="Abnormal">Abnormal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-acute-ros-gi">GI</label>
                    <select id="pc-acute-ros-gi" class="form-control">
                        <option value="Normal" selected>Normal</option>
                        <option value="Abnormal">Abnormal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-acute-ros-neuro">Neuro</label>
                    <select id="pc-acute-ros-neuro" class="form-control">
                        <option value="Normal" selected>Normal</option>
                        <option value="Abnormal">Abnormal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-acute-ros-skin">Skin</label>
                    <select id="pc-acute-ros-skin" class="form-control">
                        <option value="Normal" selected>Normal</option>
                        <option value="Abnormal">Abnormal</option>
                    </select>
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Physical Examination</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group"><label class="form-label" for="pc-acute-pe-general">General</label><input type="text" id="pc-acute-pe-general" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-acute-pe-heent">HEENT</label><input type="text" id="pc-acute-pe-heent" class="form-control"></div>
            </div>
            <div class="mod-clinical-style-39">
                <div class="form-group"><label class="form-label" for="pc-acute-pe-lungs">Lungs</label><input type="text" id="pc-acute-pe-lungs" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-acute-pe-heart">Heart</label><input type="text" id="pc-acute-pe-heart" class="form-control"></div>
            </div>
            <div class="mod-clinical-style-39">
                <div class="form-group"><label class="form-label" for="pc-acute-pe-abdomen">Abdomen</label><input type="text" id="pc-acute-pe-abdomen" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-acute-pe-neuro">Neuro</label><input type="text" id="pc-acute-pe-neuro" class="form-control"></div>
            </div>

            <h4 class="mod-clinical-style-43">Assessment & Plan</h4>
            <div class="form-group">
                <label class="form-label" for="pc-acute-assessment">Assessment</label>
                <textarea id="pc-acute-assessment" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="pc-acute-treatment">Treatment</label>
                <textarea id="pc-acute-treatment" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="pc-acute-followup-date">Follow-up</label>
                <input type="date" id="pc-acute-followup-date" class="form-control">
            </div>
        </form>
    </div>
</div>

<!-- 3. Primary Care – Medication Management -->
<div class="accordion-item general-only">
    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-pc-med-mgmt" id="accordion-pc-med-mgmt-btn">
        Primary Care – Medication Management
    </button>
    <div id="accordion-pc-med-mgmt" class="accordion-content hidden" role="region" aria-labelledby="accordion-pc-med-mgmt-btn">
        <form class="mod-clinical-style-12" id="pc-med-mgmt-form" novalidate>
            <h4 class="mod-clinical-style-43">Medication Review</h4>
            <div class="form-group">
                <label class="form-label" for="pc-med-current-meds">Current Medications</label>
                <textarea id="pc-med-current-meds" class="form-control" rows="2"></textarea>
            </div>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-med-adherence">Medication Adherence</label>
                    <select id="pc-med-adherence" class="form-control">
                        <option value="Excellent">Excellent</option>
                        <option value="Good" selected>Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-med-side-effects">Side Effects</label>
                    <select id="pc-med-side-effects" class="form-control">
                        <option value="No" selected>No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Medication Changes</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-med-change-name">Medication</label>
                    <input type="text" id="pc-med-change-name" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-med-change-action">Action</label>
                    <select id="pc-med-change-action" class="form-control">
                        <option value="Continue" selected>Continue</option>
                        <option value="Modify">Modify</option>
                        <option value="Discontinue">Discontinue</option>
                        <option value="New Prescribe">New Prescribe</option>
                    </select>
                </div>
            </div>
            <div class="mod-clinical-style-33">
                <div class="form-group">
                    <label class="form-label" for="pc-med-change-dose">Dose</label>
                    <input type="text" id="pc-med-change-dose" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-med-change-freq">Frequency</label>
                    <input type="text" id="pc-med-change-freq" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-med-change-reason">Reason for Change</label>
                    <input type="text" id="pc-med-change-reason" class="form-control">
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Medication Safety</h4>
            <div class="mod-clinical-style-33">
                <div class="form-group">
                    <label class="form-label" for="pc-med-drug-interaction">Drug Interaction</label>
                    <select id="pc-med-drug-interaction" class="form-control">
                        <option value="None" selected>None</option>
                        <option value="Mild">Mild</option>
                        <option value="Moderate">Moderate</option>
                        <option value="Severe">Severe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-med-allergy-concern">Allergy Concern</label>
                    <select id="pc-med-allergy-concern" class="form-control">
                        <option value="None" selected>None</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-med-compliance-concern">Compliance Concern</label>
                    <select id="pc-med-compliance-concern" class="form-control">
                        <option value="No" selected>No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Plan</h4>
            <div class="form-group">
                <label class="form-label" for="pc-med-plan">Medication Plan</label>
                <textarea id="pc-med-plan" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="pc-med-patient-edu">Patient Education</label>
                <textarea id="pc-med-patient-edu" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="pc-med-followup-date">Follow-up</label>
                <input type="date" id="pc-med-followup-date" class="form-control">
            </div>
        </form>
    </div>
</div>

<!-- 4. Primary Care – Health Maintenance & Screening -->
<div class="accordion-item general-only">
    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-pc-health-maint" id="accordion-pc-health-maint-btn">
        Primary Care – Health Maintenance & Screening
    </button>
    <div id="accordion-pc-health-maint" class="accordion-content hidden" role="region" aria-labelledby="accordion-pc-health-maint-btn">
        <form class="mod-clinical-style-12" id="pc-health-maint-form" novalidate>
            <h4 class="mod-clinical-style-43">Screening</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-hm-bp">Blood Pressure</label>
                    <select id="pc-hm-bp" class="form-control">
                        <option value="Up to Date" selected>Up to Date</option>
                        <option value="Due">Due</option>
                        <option value="Overdue">Overdue</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-hm-diabetes">Diabetes</label>
                    <select id="pc-hm-diabetes" class="form-control">
                        <option value="Up to Date">Up to Date</option>
                        <option value="Due" selected>Due</option>
                        <option value="Overdue">Overdue</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
            </div>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-hm-lipid">Lipid Panel</label>
                    <select id="pc-hm-lipid" class="form-control">
                        <option value="Up to Date" selected>Up to Date</option>
                        <option value="Due">Due</option>
                        <option value="Overdue">Overdue</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-hm-colorectal">Colorectal Cancer</label>
                    <select id="pc-hm-colorectal" class="form-control">
                        <option value="Up to Date">Up to Date</option>
                        <option value="Due" selected>Due</option>
                        <option value="Overdue">Overdue</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
            </div>
            <div class="mod-clinical-style-33">
                <div class="form-group">
                    <label class="form-label" for="pc-hm-breast">Breast Cancer</label>
                    <select id="pc-hm-breast" class="form-control">
                        <option value="Up to Date" selected>Up to Date</option>
                        <option value="Due">Due</option>
                        <option value="Overdue">Overdue</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-hm-cervical">Cervical Cancer</label>
                    <select id="pc-hm-cervical" class="form-control">
                        <option value="Up to Date" selected>Up to Date</option>
                        <option value="Due">Due</option>
                        <option value="Overdue">Overdue</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-hm-prostate">Prostate Screening</label>
                    <select id="pc-hm-prostate" class="form-control">
                        <option value="Up to Date">Up to Date</option>
                        <option value="Due">Due</option>
                        <option value="Overdue">Overdue</option>
                        <option value="Not Applicable" selected>Not Applicable</option>
                    </select>
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Preventive Exams</h4>
            <div class="mod-clinical-style-33">
                <div class="form-group">
                    <label class="form-label" for="pc-hm-dental-date">Dental Exam</label>
                    <input type="date" id="pc-hm-dental-date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-hm-eye-date">Eye Exam</label>
                    <input type="date" id="pc-hm-eye-date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-hm-physical-date">Annual Physical</label>
                    <input type="date" id="pc-hm-physical-date" class="form-control">
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Immunization Review</h4>
            <div class="mod-clinical-style-33">
                <div class="form-group">
                    <label class="form-label" for="pc-hm-flu">Influenza</label>
                    <select id="pc-hm-flu" class="form-control">
                        <option value="Up to Date" selected>Up to Date</option>
                        <option value="Due">Due</option>
                        <option value="Overdue">Overdue</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-hm-covid">COVID-19</label>
                    <select id="pc-hm-covid" class="form-control">
                        <option value="Up to Date" selected>Up to Date</option>
                        <option value="Due">Due</option>
                        <option value="Overdue">Overdue</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-hm-tdap">Tdap</label>
                    <select id="pc-hm-tdap" class="form-control">
                        <option value="Up to Date" selected>Up to Date</option>
                        <option value="Due">Due</option>
                        <option value="Overdue">Overdue</option>
                    </select>
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Recommendations</h4>
            <div class="form-group">
                <textarea id="pc-hm-recommendations" class="form-control" rows="2"></textarea>
            </div>
        </form>
    </div>
</div>

<!-- 5. Primary Care – Physical Examination -->
<div class="accordion-item general-only">
    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-pc-physical-exam" id="accordion-pc-physical-exam-btn">
        Primary Care – Physical Examination
    </button>
    <div id="accordion-pc-physical-exam" class="accordion-content hidden" role="region" aria-labelledby="accordion-pc-physical-exam-btn">
        <form class="mod-clinical-style-12" id="pc-physical-exam-form" novalidate>
            <h4 class="mod-clinical-style-43">General</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group"><label class="form-label" for="pc-pe-general-app">General Appearance</label><input type="text" id="pc-pe-general-app" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-pe-mental-status">Mental Status</label><input type="text" id="pc-pe-mental-status" class="form-control"></div>
            </div>

            <h4 class="mod-clinical-style-43">HEENT</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group"><label class="form-label" for="pc-pe-head">Head</label><input type="text" id="pc-pe-head" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-pe-eyes">Eyes</label><input type="text" id="pc-pe-eyes" class="form-control"></div>
            </div>
            <div class="mod-clinical-style-33">
                <div class="form-group"><label class="form-label" for="pc-pe-ears">Ears</label><input type="text" id="pc-pe-ears" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-pe-nose">Nose</label><input type="text" id="pc-pe-nose" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-pe-throat">Throat</label><input type="text" id="pc-pe-throat" class="form-control"></div>
            </div>

            <h4 class="mod-clinical-style-43">Cardiovascular</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group"><label class="form-label" for="pc-pe-heart">Heart</label><input type="text" id="pc-pe-heart" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-pe-pulses">Peripheral Pulses</label><input type="text" id="pc-pe-pulses" class="form-control"></div>
            </div>

            <h4 class="mod-clinical-style-43">Respiratory</h4>
            <div class="form-group"><label class="form-label" for="pc-pe-lungs">Lungs</label><input type="text" id="pc-pe-lungs" class="form-control"></div>

            <h4 class="mod-clinical-style-43">Abdomen</h4>
            <div class="form-group"><label class="form-label" for="pc-pe-abdomen">Abdomen</label><input type="text" id="pc-pe-abdomen" class="form-control"></div>

            <h4 class="mod-clinical-style-43">Neurological</h4>
            <div class="form-group"><label class="form-label" for="pc-pe-neuro">Neurological</label><input type="text" id="pc-pe-neuro" class="form-control"></div>

            <h4 class="mod-clinical-style-43">Musculoskeletal</h4>
            <div class="form-group"><label class="form-label" for="pc-pe-musculo">Musculoskeletal</label><input type="text" id="pc-pe-musculo" class="form-control"></div>

            <h4 class="mod-clinical-style-43">Skin</h4>
            <div class="form-group"><label class="form-label" for="pc-pe-skin">Skin</label><input type="text" id="pc-pe-skin" class="form-control"></div>

            <h4 class="mod-clinical-style-43">Examination Notes</h4>
            <div class="form-group">
                <textarea id="pc-pe-notes" class="form-control" rows="2"></textarea>
            </div>
        </form>
    </div>
</div>

<!-- 6. Primary Care – Care Plan & Follow-Up -->
<div class="accordion-item general-only">
    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-pc-care-plan" id="accordion-pc-care-plan-btn">
        Primary Care – Care Plan & Follow-Up
    </button>
    <div id="accordion-pc-care-plan" class="accordion-content hidden" role="region" aria-labelledby="accordion-pc-care-plan-btn">
        <form class="mod-clinical-style-12" id="pc-care-plan-form" novalidate>
            <h4 class="mod-clinical-style-43">Assessment</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group"><label class="form-label" for="pc-cp-primary-diag">Primary Diagnosis</label><input type="text" id="pc-cp-primary-diag" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-cp-secondary-diag">Secondary Diagnosis</label><input type="text" id="pc-cp-secondary-diag" class="form-control"></div>
            </div>

            <h4 class="mod-clinical-style-43">Care Plan</h4>
            <div class="form-group"><label class="form-label" for="pc-cp-treatment-plan">Treatment Plan</label><textarea id="pc-cp-treatment-plan" class="form-control" rows="2"></textarea></div>
            <div class="form-group"><label class="form-label" for="pc-cp-med-plan">Medication Plan</label><textarea id="pc-cp-med-plan" class="form-control" rows="2"></textarea></div>
            <div class="form-group"><label class="form-label" for="pc-cp-lifestyle-plan">Lifestyle Plan</label><textarea id="pc-cp-lifestyle-plan" class="form-control" rows="2"></textarea></div>

            <h4 class="mod-clinical-style-43">Patient Education</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-cp-edu-provided">Education Provided</label>
                    <select id="pc-cp-edu-provided" class="form-control">
                        <option value="Yes" selected>Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>
            <div class="form-group"><label class="form-label" for="pc-cp-edu-notes">Education Notes</label><textarea id="pc-cp-edu-notes" class="form-control" rows="2"></textarea></div>

            <h4 class="mod-clinical-style-43">Follow-Up</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-cp-followup-req">Follow-up Required</label>
                    <select id="pc-cp-followup-req" class="form-control">
                        <option value="Yes" selected>Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-cp-followup-date">Follow-up Date</label>
                    <input type="date" id="pc-cp-followup-date" class="form-control">
                </div>
            </div>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-cp-followup-type">Follow-up Type</label>
                    <select id="pc-cp-followup-type" class="form-control">
                        <option value="Office Visit" selected>Office Visit</option>
                        <option value="Telehealth">Telehealth</option>
                        <option value="Lab Work">Lab Work</option>
                        <option value="Phone Call">Phone Call</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-cp-referral-req">Referral Required</label>
                    <select id="pc-cp-referral-req" class="form-control">
                        <option value="No" selected>No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
            </div>

            <h4 class="mod-clinical-style-43">Provider Notes</h4>
            <div class="form-group">
                <textarea id="pc-cp-provider-notes" class="form-control" rows="2"></textarea>
            </div>
        </form>
    </div>
</div>

<!-- 7. Primary Care – Procedure / Treatment Encounter -->
<div class="accordion-item general-only">
    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-pc-procedure" id="accordion-pc-procedure-btn">
        Primary Care – Procedure / Treatment Encounter
    </button>
    <div id="accordion-pc-procedure" class="accordion-content hidden" role="region" aria-labelledby="accordion-pc-procedure-btn">
        <form class="mod-clinical-style-12" id="pc-procedure-form" novalidate>
            <h4 class="mod-clinical-style-43">Procedure Information</h4>
            <div class="mod-clinical-style-33">
                <div class="form-group"><label class="form-label" for="pc-proc-type">Procedure Type</label><input type="text" id="pc-proc-type" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-proc-date">Procedure Date</label><input type="date" id="pc-proc-date" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-proc-indication">Indication</label><input type="text" id="pc-proc-indication" class="form-control"></div>
            </div>

            <h4 class="mod-clinical-style-43">Pre-Procedure</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-proc-consent">Consent Obtained</label>
                    <select id="pc-proc-consent" class="form-control">
                        <option value="Yes" selected>Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label" for="pc-proc-pre-findings">Pre-Procedure Findings</label><input type="text" id="pc-proc-pre-findings" class="form-control"></div>
            </div>

            <h4 class="mod-clinical-style-43">Procedure</h4>
            <div class="mod-clinical-style-33">
                <div class="form-group"><label class="form-label" for="pc-proc-performed">Procedure Performed</label><input type="text" id="pc-proc-performed" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-proc-site">Site</label><input type="text" id="pc-proc-site" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-proc-technique">Technique</label><input type="text" id="pc-proc-technique" class="form-control"></div>
            </div>

            <h4 class="mod-clinical-style-43">Treatment</h4>
            <div class="mod-clinical-style-33">
                <div class="form-group"><label class="form-label" for="pc-proc-treatment-provided">Treatment Provided</label><input type="text" id="pc-proc-treatment-provided" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-proc-med-used">Medication Used</label><input type="text" id="pc-proc-med-used" class="form-control"></div>
                <div class="form-group"><label class="form-label" for="pc-proc-dosage">Dosage</label><input type="text" id="pc-proc-dosage" class="form-control"></div>
            </div>

            <h4 class="mod-clinical-style-43">Post-Procedure</h4>
            <div class="mod-clinical-style-39">
                <div class="form-group">
                    <label class="form-label" for="pc-proc-tolerance">Patient Tolerance</label>
                    <select id="pc-proc-tolerance" class="form-control">
                        <option value="Excellent">Excellent</option>
                        <option value="Good" selected>Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pc-proc-complications">Complications</label>
                    <select id="pc-proc-complications" class="form-control">
                        <option value="None" selected>None</option>
                        <option value="Minor">Minor</option>
                        <option value="Major">Major</option>
                    </select>
                </div>
            </div>
            <div class="form-group"><label class="form-label" for="pc-proc-post-notes">Post-Procedure Notes</label><textarea id="pc-proc-post-notes" class="form-control" rows="2"></textarea></div>

            <h4 class="mod-clinical-style-43">Follow-Up</h4>
            <div class="form-group"><label class="form-label" for="pc-proc-followup-date">Follow-up Date</label><input type="date" id="pc-proc-followup-date" class="form-control"></div>
            <div class="form-group"><label class="form-label" for="pc-proc-instructions">Instructions</label><textarea id="pc-proc-instructions" class="form-control" rows="2"></textarea></div>
        </form>
    </div>
</div>

                        
<!-- Accordion Section: Complete OB/GYN EHR Module -->
<div class="accordion-item obgyn-only hidden">
    <button class="accordion-header" aria-expanded="false" aria-controls="accordion-obgyn" id="accordion-obgyn-btn">
        OB/GYN Complete Assessment Suite
    </button>
    <div id="accordion-obgyn" class="accordion-content hidden" role="region" aria-labelledby="accordion-obgyn-btn">
        
        <!-- Tab Navigation for OB/GYN Forms -->
<div class="obgyn-tabs mod-clinical-style-59">
    <button type="button" class="btn btn-sm btn-primary obgyn-tab-btn active" data-target="obgyn-gyn">Gynecology</button>
    <button type="button" class="btn btn-sm btn-secondary obgyn-tab-btn" data-target="obgyn-menstrual">Menstrual History</button>
    <button type="button" class="btn btn-sm btn-secondary obgyn-tab-btn" data-target="obgyn-obstetric">Obstetric History</button>
    <button type="button" class="btn btn-sm btn-secondary obgyn-tab-btn" data-target="obgyn-pregnancy">Pregnancy Assessment</button>
</div>

<form id="obgyn-comprehensive-form" novalidate class="obgyn-form-card">
    
    <!-- 1. Gynecology General Assessment -->
    <div id="obgyn-gyn" class="obgyn-tab-content">
        <h4 class="form-section-title">Chief Complaint</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Primary Complaint</label><input type="text" name="gyn_primary_complaint" class="form-control" id="clinical-gyn_primary_complaint" aria-label="Gyn primary complaint"></div>
            <div class="form-group"><label class="form-label">Duration of Symptoms</label><input type="text" name="gyn_duration" class="form-control" id="clinical-gyn_duration" aria-label="Gyn duration"></div>
        </div>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Severity</label><select name="gyn_severity" class="form-control" id="clinical-gyn_severity" aria-label="Gyn severity"><option value="">Select...</option><option value="Mild">Mild</option><option value="Moderate">Moderate</option><option value="Severe">Severe</option></select></div>
            <div class="form-group"><label class="form-label">Onset</label><select name="gyn_onset" class="form-control" id="clinical-gyn_onset" aria-label="Gyn onset"><option value="">Select...</option><option value="Sudden">Sudden</option><option value="Gradual">Gradual</option></select></div>
        </div>
        <div class="form-group"><label class="form-label">Chief Complaint Notes</label><textarea name="gyn_complaint_notes" class="form-control" rows="2" id="clinical-gyn_complaint_notes" aria-label="Gyn complaint notes"></textarea></div>

        <h4 class="form-section-title">General Health Assessment</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">General Health Status</label><select name="gyn_health_status" class="form-control" id="clinical-gyn_health_status" aria-label="Gyn health status"><option value="">Select...</option><option value="Good">Good</option><option value="Fair">Fair</option><option value="Poor">Poor</option></select></div>
            <div class="form-group"><label class="form-label">Current Pregnancy</label><select name="gyn_current_pregnancy" class="form-control" id="clinical-gyn_current_pregnancy" aria-label="Gyn current pregnancy"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Breastfeeding</label><select name="gyn_breastfeeding" class="form-control" id="clinical-gyn_breastfeeding" aria-label="Gyn breastfeeding"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Last Menstrual Period (LMP)</label><input type="date" name="gyn_lmp" class="form-control" id="clinical-gyn_lmp" aria-label="Gyn lmp"></div>
            <div class="form-group"><label class="form-label">Menstrual Status</label><select name="gyn_menstrual_status" class="form-control" id="clinical-gyn_menstrual_status" aria-label="Gyn menstrual status"><option value="">Select...</option><option value="Regular">Regular</option><option value="Irregular">Irregular</option></select></div>
            <div class="form-group"><label class="form-label">Menopause Status</label><select name="gyn_menopause_status" class="form-control" id="clinical-gyn_menopause_status" aria-label="Gyn menopause status"><option value="">Select...</option><option value="Premenopausal">Premenopausal</option><option value="Perimenopausal">Perimenopausal</option><option value="Postmenopausal">Postmenopausal</option></select></div>
        </div>

        <h4 class="form-section-title">Current Symptoms</h4>
        <div class="grid-3col mod-clinical-style-60">
            <label><input type="checkbox" name="gyn_symptoms[]" value="Pelvic Pain" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Pelvic Pain</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Lower Abdominal Pain" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Lower Abdominal Pain</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Vaginal Bleeding" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Vaginal Bleeding</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Irregular Menstrual Cycle" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Irregular Menstrual Cycle</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Heavy Menstrual Bleeding" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Heavy Menstrual Bleeding</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Vaginal Discharge" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Vaginal Discharge</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Pain During Intercourse" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Pain During Intercourse</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Painful Menstruation" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Painful Menstruation</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Missed Period" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Missed Period</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Urinary Frequency" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Urinary Frequency</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Burning During Urination" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Burning During Urination</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Urinary Incontinence" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Urinary Incontinence</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Hot Flashes" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Hot Flashes</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Night Sweats" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Night Sweats</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Mood Changes" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Mood Changes</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Fatigue" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Fatigue</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Breast Pain" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Breast Pain</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Breast Lump" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Breast Lump</label>
            <label><input type="checkbox" name="gyn_symptoms[]" value="Nipple Discharge" id="clinical-gyn_symptoms" aria-label="Gyn symptoms[]"> Nipple Discharge</label>
        </div>
        <div class="form-group"><label class="form-label">Other Symptoms</label><input type="text" name="gyn_other_symptoms" class="form-control" id="clinical-gyn_other_symptoms" aria-label="Gyn other symptoms"></div>

        <h4 class="form-section-title">Pain Assessment</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Pain Present</label><select name="gyn_pain_present" class="form-control" id="clinical-gyn_pain_present" aria-label="Gyn pain present"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Pain Location</label><input type="text" name="gyn_pain_location" class="form-control" id="clinical-gyn_pain_location" aria-label="Gyn pain location"></div>
            <div class="form-group"><label class="form-label">Pain Score</label><select name="gyn_pain_score" class="form-control" id="clinical-gyn_pain_score" aria-label="Gyn pain score"><option value="">Select...</option><option value="0">0 - No Pain</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10 - Worst Pain</option></select></div>
            <div class="form-group"><label class="form-label">Pain Duration</label><input type="text" name="gyn_pain_duration" class="form-control" id="clinical-gyn_pain_duration" aria-label="Gyn pain duration"></div>
            <div class="form-group"><label class="form-label">Pain Character</label><select name="gyn_pain_character" class="form-control" id="clinical-gyn_pain_character" aria-label="Gyn pain character"><option value="">Select...</option><option value="Sharp">Sharp</option><option value="Dull">Dull</option><option value="Aching">Aching</option><option value="Cramping">Cramping</option></select></div>
            <div class="form-group"><label class="form-label">Pain Radiation</label><select name="gyn_pain_radiation" class="form-control" id="clinical-gyn_pain_radiation" aria-label="Gyn pain radiation"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Pain Trigger</label><input type="text" name="gyn_pain_trigger" class="form-control" id="clinical-gyn_pain_trigger" aria-label="Gyn pain trigger"></div>
            <div class="form-group"><label class="form-label">Pain Relief</label><input type="text" name="gyn_pain_relief" class="form-control" id="clinical-gyn_pain_relief" aria-label="Gyn pain relief"></div>
        </div>

        <h4 class="form-section-title">Obstetric Overview</h4>
        <div class="grid-3col">
            <div class="form-group"><label class="form-label">Gravida (G)</label><input type="number" name="gyn_gravida" class="form-control" id="clinical-gyn_gravida" aria-label="Gyn gravida"></div>
            <div class="form-group"><label class="form-label">Para (P)</label><input type="number" name="gyn_para" class="form-control" id="clinical-gyn_para" aria-label="Gyn para"></div>
            <div class="form-group"><label class="form-label">Abortions (A)</label><input type="number" name="gyn_abortions" class="form-control" id="clinical-gyn_abortions" aria-label="Gyn abortions"></div>
            <div class="form-group"><label class="form-label">Living Children (L)</label><input type="number" name="gyn_living" class="form-control" id="clinical-gyn_living" aria-label="Gyn living"></div>
            <div class="form-group"><label class="form-label">Previous Cesarean Section</label><select name="gyn_prev_cs" class="form-control" id="clinical-gyn_prev_cs" aria-label="Gyn prev cs"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Previous Pregnancy Complications</label><select name="gyn_prev_comp" class="form-control" id="clinical-gyn_prev_comp" aria-label="Gyn prev comp"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
        </div>

        <h4 class="form-section-title">Gynecological History</h4>
        <div class="grid-3col">
            <div class="form-group"><label class="form-label">Previous Gynecological Surgery</label><select name="gyn_prev_surgery" class="form-control" id="clinical-gyn_prev_surgery" aria-label="Gyn prev surgery"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">History of Fibroids</label><select name="gyn_hx_fibroids" class="form-control" id="clinical-gyn_hx_fibroids" aria-label="Gyn hx fibroids"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">History of Ovarian Cysts</label><select name="gyn_hx_cysts" class="form-control" id="clinical-gyn_hx_cysts" aria-label="Gyn hx cysts"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">History of Endometriosis</label><select name="gyn_hx_endo" class="form-control" id="clinical-gyn_hx_endo" aria-label="Gyn hx endo"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">History of PCOS</label><select name="gyn_hx_pcos" class="form-control" id="clinical-gyn_hx_pcos" aria-label="Gyn hx pcos"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">History of Cervical Dysplasia</label><select name="gyn_hx_dysplasia" class="form-control" id="clinical-gyn_hx_dysplasia" aria-label="Gyn hx dysplasia"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">History of STI</label><select name="gyn_hx_sti" class="form-control" id="clinical-gyn_hx_sti" aria-label="Gyn hx sti"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
        </div>

        <h4 class="form-section-title">Contraceptive History</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Current Contraceptive Method</label><select name="gyn_contra_current" class="form-control" id="clinical-gyn_contra_current" aria-label="Gyn contra current"><option value="">Select...</option><option value="None">None</option><option value="Pills">Pills</option><option value="IUD">IUD</option><option value="Implant">Implant</option><option value="Condoms">Condoms</option></select></div>
            <div class="form-group"><label class="form-label">Previous Contraceptive Use</label><select name="gyn_contra_prev" class="form-control" id="clinical-gyn_contra_prev" aria-label="Gyn contra prev"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Interested in Family Planning</label><select name="gyn_fam_planning" class="form-control" id="clinical-gyn_fam_planning" aria-label="Gyn fam planning"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Pregnancy Intention</label><select name="gyn_preg_intention" class="form-control" id="clinical-gyn_preg_intention" aria-label="Gyn preg intention"><option value="">Select...</option><option value="Planning Pregnancy">Planning Pregnancy</option><option value="Not Planning">Not Planning</option></select></div>
        </div>

        <h4 class="form-section-title">Breast Health Assessment</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Performs Breast Self Examination</label><select name="gyn_breast_exam" class="form-control" id="clinical-gyn_breast_exam" aria-label="Gyn breast exam"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Breast Lump</label><select name="gyn_breast_lump" class="form-control" id="clinical-gyn_breast_lump" aria-label="Gyn breast lump"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Breast Pain</label><select name="gyn_breast_pain" class="form-control" id="clinical-gyn_breast_pain" aria-label="Gyn breast pain"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Nipple Discharge</label><select name="gyn_breast_discharge" class="form-control" id="clinical-gyn_breast_discharge" aria-label="Gyn breast discharge"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Last Mammogram</label><input type="date" name="gyn_last_mammogram" class="form-control" id="clinical-gyn_last_mammogram" aria-label="Gyn last mammogram"></div>
            <div class="form-group"><label class="form-label">Abnormal Mammogram History</label><select name="gyn_abnormal_mammogram" class="form-control" id="clinical-gyn_abnormal_mammogram" aria-label="Gyn abnormal mammogram"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
        </div>

        <h4 class="form-section-title">Preventive Screening</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Last Pap Smear</label><input type="date" name="gyn_last_pap" class="form-control" id="clinical-gyn_last_pap" aria-label="Gyn last pap"></div>
            <div class="form-group"><label class="form-label">Pap Smear Result</label><select name="gyn_pap_result" class="form-control" id="clinical-gyn_pap_result" aria-label="Gyn pap result"><option value="">Select...</option><option value="Normal">Normal</option><option value="Abnormal">Abnormal</option></select></div>
            <div class="form-group"><label class="form-label">HPV Screening</label><select name="gyn_hpv" class="form-control" id="clinical-gyn_hpv" aria-label="Gyn hpv"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Bone Density Screening</label><select name="gyn_bone_density" class="form-control" id="clinical-gyn_bone_density" aria-label="Gyn bone density"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Pelvic Ultrasound</label><select name="gyn_pelvic_us" class="form-control" id="clinical-gyn_pelvic_us" aria-label="Gyn pelvic us"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
        </div>
        
        <h4 class="form-section-title">Clinical Assessment & Recommendations</h4>
        <div class="grid-3col mod-clinical-style-60">
            <label><input type="checkbox" name="gyn_rec[]" value="Routine Follow-up" id="clinical-gyn_rec" aria-label="Gyn rec[]"> Routine Follow-up</label>
            <label><input type="checkbox" name="gyn_rec[]" value="Pap Smear" id="clinical-gyn_rec" aria-label="Gyn rec[]"> Pap Smear</label>
            <label><input type="checkbox" name="gyn_rec[]" value="HPV Test" id="clinical-gyn_rec" aria-label="Gyn rec[]"> HPV Test</label>
            <label><input type="checkbox" name="gyn_rec[]" value="Pelvic Ultrasound" id="clinical-gyn_rec" aria-label="Gyn rec[]"> Pelvic Ultrasound</label>
            <label><input type="checkbox" name="gyn_rec[]" value="Mammogram" id="clinical-gyn_rec" aria-label="Gyn rec[]"> Mammogram</label>
            <label><input type="checkbox" name="gyn_rec[]" value="Hormonal Evaluation" id="clinical-gyn_rec" aria-label="Gyn rec[]"> Hormonal Evaluation</label>
            <label><input type="checkbox" name="gyn_rec[]" value="Family Planning Counseling" id="clinical-gyn_rec" aria-label="Gyn rec[]"> Family Planning</label>
            <label><input type="checkbox" name="gyn_rec[]" value="Contraceptive Counseling" id="clinical-gyn_rec" aria-label="Gyn rec[]"> Contraceptive Counseling</label>
            <label><input type="checkbox" name="gyn_rec[]" value="Surgical Consultation" id="clinical-gyn_rec" aria-label="Gyn rec[]"> Surgical Consultation</label>
        </div>
        <div class="form-group"><label class="form-label">Other Recommendations</label><input type="text" name="gyn_other_rec" class="form-control" id="clinical-gyn_other_rec" aria-label="Gyn other rec"></div>
        <div class="form-group"><label class="form-label">Assessment Summary</label><textarea name="gyn_summary" class="form-control" rows="3" id="clinical-gyn_summary" aria-label="Gyn summary"></textarea></div>
    </div>

    <!-- 2. Menstrual History Assessment -->
    <div id="obgyn-menstrual" class="obgyn-tab-content mod-clinical-style-61">
        <h4 class="form-section-title">Menstrual Profile</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Age at Menarche</label><input type="number" name="men_menarche" class="form-control" id="clinical-men_menarche" aria-label="Men menarche"></div>
            <div class="form-group"><label class="form-label">Last Menstrual Period (LMP)</label><input type="date" name="men_lmp" class="form-control" id="clinical-men_lmp" aria-label="Men lmp"></div>
            <div class="form-group"><label class="form-label">Menstrual Cycle Length (Days)</label><input type="number" name="men_cycle_length" class="form-control" id="clinical-men_cycle_length" aria-label="Men cycle length"></div>
            <div class="form-group"><label class="form-label">Duration of Menstrual Flow (Days)</label><input type="number" name="men_flow_duration" class="form-control" id="clinical-men_flow_duration" aria-label="Men flow duration"></div>
            <div class="form-group"><label class="form-label">Cycle Pattern</label><select name="men_pattern" class="form-control" id="clinical-men_pattern" aria-label="Men pattern"><option value="">Select...</option><option value="Regular">Regular</option><option value="Irregular">Irregular</option></select></div>
            <div class="form-group"><label class="form-label">Flow Amount</label><select name="men_flow" class="form-control" id="clinical-men_flow" aria-label="Men flow"><option value="">Select...</option><option value="Light">Light</option><option value="Moderate">Moderate</option><option value="Heavy">Heavy</option></select></div>
        </div>

        <h4 class="form-section-title">Menstrual Symptoms</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Painful Menstruation (Dysmenorrhea)</label><select name="men_dysmenorrhea" class="form-control" id="clinical-men_dysmenorrhea" aria-label="Men dysmenorrhea"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Pain Severity</label><select name="men_pain_severity" class="form-control" id="clinical-men_pain_severity" aria-label="Men pain severity"><option value="">Select...</option><option value="Mild">Mild</option><option value="Moderate">Moderate</option><option value="Severe">Severe</option></select></div>
            <div class="form-group"><label class="form-label">Heavy Menstrual Bleeding (Menorrhagia)</label><select name="men_menorrhagia" class="form-control" id="clinical-men_menorrhagia" aria-label="Men menorrhagia"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Light Menstrual Bleeding</label><select name="men_light_bleeding" class="form-control" id="clinical-men_light_bleeding" aria-label="Men light bleeding"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Irregular Menstrual Cycle</label><select name="men_irregular" class="form-control" id="clinical-men_irregular" aria-label="Men irregular"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Missed Periods (Amenorrhea)</label><select name="men_amenorrhea" class="form-control" id="clinical-men_amenorrhea" aria-label="Men amenorrhea"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Infrequent Periods (Oligomenorrhea)</label><select name="men_oligomenorrhea" class="form-control" id="clinical-men_oligomenorrhea" aria-label="Men oligomenorrhea"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Frequent Periods (Polymenorrhea)</label><select name="men_polymenorrhea" class="form-control" id="clinical-men_polymenorrhea" aria-label="Men polymenorrhea"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
        </div>

        <h4 class="form-section-title">Bleeding Assessment</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Intermenstrual Bleeding</label><select name="men_intermenstrual" class="form-control" id="clinical-men_intermenstrual" aria-label="Men intermenstrual"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Postcoital Bleeding</label><select name="men_postcoital" class="form-control" id="clinical-men_postcoital" aria-label="Men postcoital"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Passage of Blood Clots</label><select name="men_clots" class="form-control" id="clinical-men_clots" aria-label="Men clots"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Spotting Between Periods</label><select name="men_spotting" class="form-control" id="clinical-men_spotting" aria-label="Men spotting"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Bleeding After Menopause</label><select name="men_postmenopausal" class="form-control" id="clinical-men_postmenopausal" aria-label="Men postmenopausal"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
        </div>

        <h4 class="form-section-title">Associated Symptoms</h4>
        <div class="grid-3col mod-clinical-style-60">
            <label><input type="checkbox" name="men_assoc_symp[]" value="Pelvic Pain" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Pelvic Pain</label>
            <label><input type="checkbox" name="men_assoc_symp[]" value="Lower Abdominal Pain" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Lower Abdominal Pain</label>
            <label><input type="checkbox" name="men_assoc_symp[]" value="Back Pain" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Back Pain</label>
            <label><input type="checkbox" name="men_assoc_symp[]" value="Breast Tenderness" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Breast Tenderness</label>
            <label><input type="checkbox" name="men_assoc_symp[]" value="Mood Changes" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Mood Changes</label>
            <label><input type="checkbox" name="men_assoc_symp[]" value="Fatigue" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Fatigue</label>
            <label><input type="checkbox" name="men_assoc_symp[]" value="Headache" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Headache</label>
            <label><input type="checkbox" name="men_assoc_symp[]" value="Nausea" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Nausea</label>
            <label><input type="checkbox" name="men_assoc_symp[]" value="Bloating" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Bloating</label>
            <label><input type="checkbox" name="men_assoc_symp[]" value="Acne" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Acne</label>
            <label><input type="checkbox" name="men_assoc_symp[]" value="Weight Gain" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Weight Gain</label>
            <label><input type="checkbox" name="men_assoc_symp[]" value="Dizziness" id="clinical-men_assoc_symp" aria-label="Men assoc symp[]"> Dizziness</label>
        </div>
        <div class="form-group"><label class="form-label">Other Symptoms</label><input type="text" name="men_other_symptoms" class="form-control" id="clinical-men_other_symptoms" aria-label="Men other symptoms"></div>
        <div class="form-group"><label class="form-label">Assessment Summary</label><textarea name="men_summary" class="form-control" rows="3" id="clinical-men_summary" aria-label="Men summary"></textarea></div>
    </div>

    <!-- 3. Obstetric History Assessment -->
    <div id="obgyn-obstetric" class="obgyn-tab-content mod-clinical-style-61">
        <h4 class="form-section-title">Obstetric Score</h4>
        <div class="grid-3col">
            <div class="form-group"><label class="form-label">Gravida (G)</label><input type="number" name="obs_g" class="form-control" id="clinical-obs_g" aria-label="Obs g"></div>
            <div class="form-group"><label class="form-label">Para (P)</label><input type="number" name="obs_p" class="form-control" id="clinical-obs_p" aria-label="Obs p"></div>
            <div class="form-group"><label class="form-label">Term Births (T)</label><input type="number" name="obs_t" class="form-control" id="clinical-obs_t" aria-label="Obs t"></div>
            <div class="form-group"><label class="form-label">Preterm Births (PT)</label><input type="number" name="obs_pt" class="form-control" id="clinical-obs_pt" aria-label="Obs pt"></div>
            <div class="form-group"><label class="form-label">Abortions (A)</label><input type="number" name="obs_a" class="form-control" id="clinical-obs_a" aria-label="Obs a"></div>
            <div class="form-group"><label class="form-label">Living Children (L)</label><input type="number" name="obs_l" class="form-control" id="clinical-obs_l" aria-label="Obs l"></div>
            <div class="form-group"><label class="form-label">Multiple Pregnancy</label><select name="obs_multiple" class="form-control" id="clinical-obs_multiple" aria-label="Obs multiple"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
        </div>

        <h4 class="form-section-title">Previous Pregnancy History</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Normal Vaginal Deliveries</label><input type="number" name="obs_nvd" class="form-control" id="clinical-obs_nvd" aria-label="Obs nvd"></div>
            <div class="form-group"><label class="form-label">Cesarean Deliveries</label><input type="number" name="obs_cs" class="form-control" id="clinical-obs_cs" aria-label="Obs cs"></div>
            <div class="form-group"><label class="form-label">Assisted Vaginal Deliveries</label><input type="number" name="obs_avd" class="form-control" id="clinical-obs_avd" aria-label="Obs avd"></div>
            <div class="form-group"><label class="form-label">Stillbirths</label><input type="number" name="obs_stillbirths" class="form-control" id="clinical-obs_stillbirths" aria-label="Obs stillbirths"></div>
            <div class="form-group"><label class="form-label">Ectopic Pregnancies</label><input type="number" name="obs_ectopic" class="form-control" id="clinical-obs_ectopic" aria-label="Obs ectopic"></div>
            <div class="form-group"><label class="form-label">Molar Pregnancies</label><input type="number" name="obs_molar" class="form-control" id="clinical-obs_molar" aria-label="Obs molar"></div>
        </div>

        <h4 class="form-section-title">Pregnancy Loss History</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Miscarriage</label><select name="obs_miscarriage" class="form-control" id="clinical-obs_miscarriage" aria-label="Obs miscarriage"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Number of Miscarriages</label><input type="number" name="obs_num_miscarriage" class="form-control" id="clinical-obs_num_miscarriage" aria-label="Obs num miscarriage"></div>
            <div class="form-group"><label class="form-label">Elective Termination</label><select name="obs_termination" class="form-control" id="clinical-obs_termination" aria-label="Obs termination"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Number of Abortions</label><input type="number" name="obs_num_abortions" class="form-control" id="clinical-obs_num_abortions" aria-label="Obs num abortions"></div>
            <div class="form-group"><label class="form-label">Second Trimester Loss</label><select name="obs_second_tri_loss" class="form-control" id="clinical-obs_second_tri_loss" aria-label="Obs second tri loss"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Recurrent Pregnancy Loss</label><select name="obs_recurrent_loss" class="form-control" id="clinical-obs_recurrent_loss" aria-label="Obs recurrent loss"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
        </div>

        <h4 class="form-section-title">Previous Pregnancy Complications</h4>
        <div class="grid-3col mod-clinical-style-60">
            <label><input type="checkbox" name="obs_comp[]" value="Gestational Diabetes" id="clinical-obs_comp" aria-label="Obs comp[]"> Gestational Diabetes</label>
            <label><input type="checkbox" name="obs_comp[]" value="Pregnancy-Induced Hypertension" id="clinical-obs_comp" aria-label="Obs comp[]"> Pregnancy-Induced Hypertension</label>
            <label><input type="checkbox" name="obs_comp[]" value="Preeclampsia" id="clinical-obs_comp" aria-label="Obs comp[]"> Preeclampsia</label>
            <label><input type="checkbox" name="obs_comp[]" value="Eclampsia" id="clinical-obs_comp" aria-label="Obs comp[]"> Eclampsia</label>
            <label><input type="checkbox" name="obs_comp[]" value="Placenta Previa" id="clinical-obs_comp" aria-label="Obs comp[]"> Placenta Previa</label>
            <label><input type="checkbox" name="obs_comp[]" value="Placental Abruption" id="clinical-obs_comp" aria-label="Obs comp[]"> Placental Abruption</label>
            <label><input type="checkbox" name="obs_comp[]" value="Preterm Labor" id="clinical-obs_comp" aria-label="Obs comp[]"> Preterm Labor</label>
            <label><input type="checkbox" name="obs_comp[]" value="PROM" id="clinical-obs_comp" aria-label="Obs comp[]"> PROM</label>
            <label><input type="checkbox" name="obs_comp[]" value="IUGR" id="clinical-obs_comp" aria-label="Obs comp[]"> IUGR</label>
            <label><input type="checkbox" name="obs_comp[]" value="Postpartum Hemorrhage" id="clinical-obs_comp" aria-label="Obs comp[]"> Postpartum Hemorrhage</label>
            <label><input type="checkbox" name="obs_comp[]" value="Shoulder Dystocia" id="clinical-obs_comp" aria-label="Obs comp[]"> Shoulder Dystocia</label>
            <label><input type="checkbox" name="obs_comp[]" value="Postpartum Infection" id="clinical-obs_comp" aria-label="Obs comp[]"> Postpartum Infection</label>
        </div>
        
        <h4 class="form-section-title">Delivery History</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Last Delivery Date</label><input type="date" name="obs_last_delivery" class="form-control" id="clinical-obs_last_delivery" aria-label="Obs last delivery"></div>
            <div class="form-group"><label class="form-label">Mode of Delivery</label><select name="obs_mode_delivery" class="form-control" id="clinical-obs_mode_delivery" aria-label="Obs mode delivery"><option value="">Select...</option><option value="Normal Vaginal Delivery">Normal Vaginal Delivery</option><option value="Cesarean">Cesarean</option></select></div>
            <div class="form-group"><label class="form-label">Delivery Complications</label><select name="obs_del_comp" class="form-control" id="clinical-obs_del_comp" aria-label="Obs del comp"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Birth Weight of Last Baby</label><input type="text" name="obs_birth_weight" class="form-control" id="clinical-obs_birth_weight" aria-label="Obs birth weight"></div>
            <div class="form-group"><label class="form-label">NICU Admission</label><select name="obs_nicu" class="form-control" id="clinical-obs_nicu" aria-label="Obs nicu"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Breastfeeding Initiated</label><select name="obs_breastfeeding" class="form-control" id="clinical-obs_breastfeeding" aria-label="Obs breastfeeding"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
        </div>
        <div class="form-group"><label class="form-label">Assessment Summary</label><textarea name="obs_summary" class="form-control" rows="3" id="clinical-obs_summary" aria-label="Obs summary"></textarea></div>
    </div>

    <!-- 4. Pregnancy Assessment -->
    <div id="obgyn-pregnancy" class="obgyn-tab-content mod-clinical-style-61">
        <h4 class="form-section-title">Current Pregnancy Information</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Pregnancy Status</label><select name="preg_status" class="form-control" id="clinical-preg_status" aria-label="Preg status"><option value="">Select...</option><option value="Confirmed">Confirmed</option><option value="Suspected">Suspected</option></select></div>
            <div class="form-group"><label class="form-label">Estimated Due Date (EDD)</label><input type="date" name="preg_edd" class="form-control" id="clinical-preg_edd" aria-label="Preg edd"></div>
            <div class="form-group"><label class="form-label">Gestational Age</label><input type="text" name="preg_gestational_age" class="form-control" id="clinical-preg_gestational_age" aria-label="Preg gestational age"></div>
            <div class="form-group"><label class="form-label">Trimester</label><select name="preg_trimester" class="form-control" id="clinical-preg_trimester" aria-label="Preg trimester"><option value="">Select...</option><option value="First Trimester">First Trimester</option><option value="Second Trimester">Second Trimester</option><option value="Third Trimester">Third Trimester</option></select></div>
            <div class="form-group"><label class="form-label">Singleton / Multiple Pregnancy</label><select name="preg_multiple" class="form-control" id="clinical-preg_multiple" aria-label="Preg multiple"><option value="">Select...</option><option value="Singleton">Singleton</option><option value="Multiple">Multiple</option></select></div>
        </div>

        <h4 class="form-section-title">Presenting Symptoms</h4>
        <div class="grid-3col mod-clinical-style-60">
            <label><input type="checkbox" name="preg_symp[]" value="Nausea" id="clinical-preg_symp" aria-label="Preg symp[]"> Nausea</label>
            <label><input type="checkbox" name="preg_symp[]" value="Vomiting" id="clinical-preg_symp" aria-label="Preg symp[]"> Vomiting</label>
            <label><input type="checkbox" name="preg_symp[]" value="Fatigue" id="clinical-preg_symp" aria-label="Preg symp[]"> Fatigue</label>
            <label><input type="checkbox" name="preg_symp[]" value="Breast Tenderness" id="clinical-preg_symp" aria-label="Preg symp[]"> Breast Tenderness</label>
            <label><input type="checkbox" name="preg_symp[]" value="Frequent Urination" id="clinical-preg_symp" aria-label="Preg symp[]"> Frequent Urination</label>
            <label><input type="checkbox" name="preg_symp[]" value="Heartburn" id="clinical-preg_symp" aria-label="Preg symp[]"> Heartburn</label>
            <label><input type="checkbox" name="preg_symp[]" value="Constipation" id="clinical-preg_symp" aria-label="Preg symp[]"> Constipation</label>
            <label><input type="checkbox" name="preg_symp[]" value="Back Pain" id="clinical-preg_symp" aria-label="Preg symp[]"> Back Pain</label>
            <label><input type="checkbox" name="preg_symp[]" value="Pelvic Pain" id="clinical-preg_symp" aria-label="Preg symp[]"> Pelvic Pain</label>
            <label><input type="checkbox" name="preg_symp[]" value="Leg Swelling" id="clinical-preg_symp" aria-label="Preg symp[]"> Leg Swelling</label>
            <label><input type="checkbox" name="preg_symp[]" value="Headache" id="clinical-preg_symp" aria-label="Preg symp[]"> Headache</label>
            <label><input type="checkbox" name="preg_symp[]" value="Dizziness" id="clinical-preg_symp" aria-label="Preg symp[]"> Dizziness</label>
        </div>
        
        <h4 class="form-section-title">Warning Signs Assessment</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Vaginal Bleeding</label><select name="preg_warn_bleeding" class="form-control" id="clinical-preg_warn_bleeding" aria-label="Preg warn bleeding"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Leakage of Fluid</label><select name="preg_warn_fluid" class="form-control" id="clinical-preg_warn_fluid" aria-label="Preg warn fluid"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Reduced Fetal Movement</label><select name="preg_warn_movement" class="form-control" id="clinical-preg_warn_movement" aria-label="Preg warn movement"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Severe Abdominal Pain</label><select name="preg_warn_pain" class="form-control" id="clinical-preg_warn_pain" aria-label="Preg warn pain"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Severe Headache</label><select name="preg_warn_headache" class="form-control" id="clinical-preg_warn_headache" aria-label="Preg warn headache"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Blurred Vision</label><select name="preg_warn_vision" class="form-control" id="clinical-preg_warn_vision" aria-label="Preg warn vision"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Fever</label><select name="preg_warn_fever" class="form-control" id="clinical-preg_warn_fever" aria-label="Preg warn fever"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
            <div class="form-group"><label class="form-label">Convulsions</label><select name="preg_warn_convulsions" class="form-control" id="clinical-preg_warn_convulsions" aria-label="Preg warn convulsions"><option value="">Select...</option><option value="Yes">Yes</option><option value="No">No</option></select></div>
        </div>

        <h4 class="form-section-title">Fetal Assessment</h4>
        <div class="grid-2col">
            <div class="form-group"><label class="form-label">Fetal Heart Rate</label><input type="text" name="preg_fhr" class="form-control" id="clinical-preg_fhr" aria-label="Preg fhr"></div>
            <div class="form-group"><label class="form-label">Fetal Movement</label><select name="preg_fetal_movement" class="form-control" id="clinical-preg_fetal_movement" aria-label="Preg fetal movement"><option value="">Select...</option><option value="Present">Present</option><option value="Absent">Absent</option></select></div>
            <div class="form-group"><label class="form-label">Fundal Height</label><input type="text" name="preg_fundal_height" class="form-control" id="clinical-preg_fundal_height" aria-label="Preg fundal height"></div>
            <div class="form-group"><label class="form-label">Fetal Position</label><select name="preg_fetal_position" class="form-control" id="clinical-preg_fetal_position" aria-label="Preg fetal position"><option value="">Select...</option><option value="Cephalic">Cephalic</option><option value="Breech">Breech</option><option value="Transverse">Transverse</option></select></div>
            <div class="form-group"><label class="form-label">Presentation</label><select name="preg_presentation" class="form-control" id="clinical-preg_presentation" aria-label="Preg presentation"><option value="">Select...</option><option value="Vertex">Vertex</option><option value="Other">Other</option></select></div>
            <div class="form-group"><label class="form-label">Amniotic Fluid Status</label><select name="preg_amniotic" class="form-control" id="clinical-preg_amniotic" aria-label="Preg amniotic"><option value="">Select...</option><option value="Normal">Normal</option><option value="Oligohydramnios">Oligohydramnios</option><option value="Polyhydramnios">Polyhydramnios</option></select></div>
        </div>
        <div class="form-group"><label class="form-label">Assessment Summary</label><textarea name="preg_summary" class="form-control" rows="3" id="clinical-preg_summary" aria-label="Preg summary"></textarea></div>
    </div>
</form>

    </div>
</div>

                        <!-- Accordion Section: Diagnosis, Plan & Save -->
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
                                        <textarea id="clinical-icd10" class="form-control" rows="3" placeholder="CPT-4 procedure codes & medical problems will appear here (e.g. 99213 - Office visit est; 99391 - Prev visit est infant)..."></textarea>
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
                                        <button type="button" class="btn btn-primary hidden" id="update-encounter-btn">Update Encounter</button>
                                        <button type="button" class="btn btn-primary" id="save-encounter-btn">Submit & Sign Encounter</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div> <!-- End Accordion -->
                </div>
            </div>
        </div>
