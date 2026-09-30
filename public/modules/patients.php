<!-- public/modules/patients.php --><link rel="stylesheet" href="css/modules/patients.css?v=<?= time() ?>&nocache=999">
<link rel="stylesheet" href="css/modules/clinical.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'patients'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" id="patients-workspace-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; background: white; padding: 14px 24px; border-bottom: 1px solid #e2e8f0; border-radius: 8px;">
            <h1 style="color: #0f172a; font-weight: 800; font-size: 1.5rem; margin: 0;">Patient Directory</h1>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- Patient Directory List View -->
        <div id="patient-directory-list-view">
            <!-- Search & Filter Controls Bar -->
            <div class="card" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 24px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <label for="patient-search" style="font-weight: 700; color: #475569; font-size: 0.9rem; margin: 0; white-space: nowrap;">Search:</label>
                            <input type="text" id="patient-search" class="form-control" style="width: 280px; height: 38px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box; margin: 0;" placeholder="">
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <label for="filter-assigned-to" style="font-weight: 700; color: #475569; font-size: 0.9rem; margin: 0; white-space: nowrap;">Doctor:</label>
                            <select id="filter-assigned-to" class="form-select" style="height: 38px; padding: 0 32px 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; color: #334155; width: 170px; box-sizing: border-box; margin: 0;">
                                <option value="">All Clinicians</option>
                            </select>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <label for="filter-status" style="font-weight: 700; color: #475569; font-size: 0.9rem; margin: 0; white-space: nowrap;">Status:</label>
                            <select id="filter-status" class="form-select" style="height: 38px; padding: 0 32px 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; color: #334155; width: 120px; box-sizing: border-box; margin: 0;">
                                <option value="Active" selected>Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Draft">Draft</option>
                                <option value="All">All</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" id="search-btn" style="background: #007bb6; border-color: #007bb6; width: 110px; height: 38px; font-weight: 700; border-radius: 6px; font-size: 0.9rem; color: #fff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; box-sizing: border-box; margin: 0; padding: 0;">Search</button>
                    </div>
                    <div>
                        <button class="btn btn-primary" id="register-patient-btn" style="background: #007bb6; border-color: #007bb6; width: 110px; height: 38px; font-weight: 700; border-radius: 6px; font-size: 0.9rem; color: #fff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; box-sizing: border-box; margin: 0; padding: 0;">Register</button>
                    </div>
                </div>
            </div>

            <!-- Patient Table Card -->
            <div class="card" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;">
                    <h2 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">Active Demographics</h2>
                    <!-- Table tools: they act on the list below, so they live with it (and disappear with it) -->
                    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 20px;">
                        <a href="javascript:void(0)" id="btn-select-columns" style="color: #0ea5e9; text-decoration: none; font-weight: 700; font-size: 0.9rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;"><i class="fas fa-columns"></i> Select Columns</a>
                        <a href="javascript:void(0)" id="btn-export-spreadsheet" style="color: #0ea5e9; text-decoration: none; font-weight: 700; font-size: 0.9rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;"><i class="fas fa-file-csv"></i> Export Spreadsheet</a>
                        <a href="javascript:void(0)" id="btn-print-patients" style="color: #0ea5e9; text-decoration: none; font-weight: 700; font-size: 0.9rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;"><i class="fas fa-print"></i> Print</a>
                    </div>
                </div>
                <div class="table-container" style="overflow-x: auto; margin: 0; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <table class="table-community-patients" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr>
                                <th id="th-patient-name" data-sort="name">PATIENT NAME <span class="pd-sort-ind" id="name-sort-icon"></span></th>
                                <th id="th-dob" data-sort="dob">DOB <span class="pd-sort-ind"></span></th>
                                <th id="th-phone" data-sort="phone">PHONE NUMBER <span class="pd-sort-ind"></span></th>
                                <th id="th-last-appt" data-sort="last_appt">LAST APPT <span class="pd-sort-ind"></span></th>
                                <th id="th-next-appt" data-sort="next_appt">NEXT APPT <span class="pd-sort-ind"></span></th>
                                <th id="th-clinicians" data-sort="clinicians">CLINICIANS <span class="pd-sort-ind"></span></th>
                                <th id="th-status" data-sort="status">STATUS <span class="pd-sort-ind"></span></th>
                                <th id="th-messages" style="text-align: center; width: 44px; color: #64748b;"><i class="far fa-envelope" style="font-size: 15px;"></i></th>
                                <th id="th-actions" style="text-align: right;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="patients-list">
                            <tr>
                                <td colspan="9" style="padding: 24px; text-align: center; color: #64748b;">Loading patients...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination (Showing N of M entries | Per page | Prev / pages / Next) -->
                <div id="patients-pagination"></div>
            </div>
        </div>

                <!-- 7-Step Guided Patient Registration & Management Workflow (Matching vital-forms-prototype.lovable.app) -->
        <div id="patient-registration-full-view" style="display: none;">
            
            <!-- Top Wizard Header Bar -->
            <div class="card pt-wizard-header-card">
                <div class="pt-wizard-header-left">
                    <h2 id="full-reg-header-title" class="pt-wizard-title">Patient Registration</h2>
                </div>
                <div class="pt-wizard-header-right">
                    <span class="pt-step-indicator-pill" id="pt-header-step-pill">Step 1 of 6</span>
                    <button type="button" class="btn-pt-cancel" id="full-reg-cancel-btn"><i class="fas fa-times"></i> Cancel</button>
                </div>
            </div>

            <!-- Validation / Error Alert Banner -->
            <div id="full-reg-error-alert" class="pt-alert-banner" style="display: none;">
                <div class="pt-alert-content">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="full-reg-error-text">Please fill in all required fields marked with an asterisk (*).</span>
                </div>
            </div>

            <!-- Main 2-Column Guided Stepper Layout -->
            <form id="full-patient-reg-form" novalidate onsubmit="event.preventDefault(); return false;">
                <div class="pt-wizard-layout-grid">
                    
                    <!-- Left Stepper Navigation Sidebar -->
                    <aside class="pt-stepper-sidebar">
                        <div class="pt-stepper-sidebar-inner">
                            <div class="pt-stepper-item active" data-step="1" id="stepper-item-1">
                                <div class="pt-stepper-badge">
                                    <span class="step-num">1</span>
                                    <i class="fas fa-check step-check"></i>
                                </div>
                                <div class="pt-stepper-meta">
                                    <div class="pt-stepper-heading">Basic Info</div>
                                     
                                </div>
                            </div>

                            <div class="pt-stepper-item" data-step="2" id="stepper-item-2">
                                <div class="pt-stepper-badge">
                                    <span class="step-num">2</span>
                                    <i class="fas fa-check step-check"></i>
                                </div>
                                <div class="pt-stepper-meta">
                                    <div class="pt-stepper-heading">Contact</div>
                                    
                                </div>
                            </div>

                            <div class="pt-stepper-item" data-step="3" id="stepper-item-3">
                                <div class="pt-stepper-badge">
                                    <span class="step-num">3</span>
                                    <i class="fas fa-check step-check"></i>
                                </div>
                                <div class="pt-stepper-meta">
                                    <div class="pt-stepper-heading">Insurance</div>
                                   
                                </div>
                            </div>

                            <div class="pt-stepper-item" data-step="4" id="stepper-item-4">
                                <div class="pt-stepper-badge">
                                    <span class="step-num">4</span>
                                    <i class="fas fa-check step-check"></i>
                                </div>
                                <div class="pt-stepper-meta">
                                    <div class="pt-stepper-heading">Medical History</div>
                                     
                                </div>
                            </div>

                            <div class="pt-stepper-item" data-step="5" id="stepper-item-5">
                                <div class="pt-stepper-badge">
                                    <span class="step-num">5</span>
                                    <i class="fas fa-check step-check"></i>
                                </div>
                                <div class="pt-stepper-meta">
                                    <div class="pt-stepper-heading">Consents</div>
                                    
                                </div>
                            </div>

                            <div class="pt-stepper-item" data-step="6" id="stepper-item-6">
                                <div class="pt-stepper-badge">
                                    <span class="step-num">6</span>
                                    <i class="fas fa-check step-check"></i>
                                </div>
                                <div class="pt-stepper-meta">
                                    <div class="pt-stepper-heading">Review</div>
                                   
                                </div>
                            </div>
                        </div>

                        <!-- Mini Help Box in Sidebar -->
                        <div class="pt-stepper-help-card">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>HIPAA Secured</strong>
                                <p>All patient identifiers and sensitive PHI are encrypted at rest.</p>
                            </div>
                        </div>
                    </aside>

                    <!-- Right Step Content Container -->
                    <section class="pt-step-content-area">
                        
                        <!-- ═══════════════════════════════════════════════════════ -->
                        <!-- STEP 1: BASIC INFO & DEMOGRAPHICS -->
                        <!-- ═══════════════════════════════════════════════════════ -->
                        <div class="pt-step-pane active" id="pt-step-pane-1">
                            <div class="pt-card">
                                <div class="pt-card-header">
                                    <div class="pt-card-header-left">
                                        <h3 class="pt-section-title">Basic Info</h3>
                                        <p class="pt-section-desc">Fields marked with <span class="req">*</span> are required. Progress is kept as you move between steps.</p>
                                    </div>
                                    <span class="pt-step-badge">Step 1 of 6</span>
                                </div>

                                <div class="pt-card-body">
                                    <!-- Photo & MRN Banner -->
                                    <div class="pt-identity-banner">
                                        <div class="pt-avatar-upload-box">
                                            <div id="full-reg-photo-badge" class="pt-avatar-badge" title="Click to upload profile photo">
                                                <i class="fas fa-user" id="avatar-default-icon"></i>
                                                <img id="avatar-preview-img" style="display: none;" src="" alt="Patient Photo">
                                                <div class="pt-avatar-plus-icon"><i class="fas fa-camera"></i></div>
                                            </div>
                                            <input type="file" id="full-reg-photo-input" accept="image/*" style="display: none;">
                                        </div>
                                        <div class="pt-identity-meta">
                                            <div class="pt-meta-label">Patient Identity & Legal Record</div>
                                            <div class="pt-meta-desc">Legal name as it appears on the patient's official government ID.</div>
                                        </div>
                                    </div>

                                    <!-- Legal Name Fields Grid -->
                                    <div class="pt-form-row pt-grid-3">
                                        <div class="form-group">
                                            <label class="form-label">First Name <span class="req">*</span></label>
                                            <input type="text" id="full-reg-first-name" class="form-control" placeholder="Legal first name">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Middle Name</label>
                                            <input type="text" id="full-reg-middle-name" class="form-control" placeholder="Middle name or initial">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Last Name <span class="req">*</span></label>
                                            <input type="text" id="full-reg-last-name" class="form-control" placeholder="Legal last name">
                                        </div>
                                    </div>

                                    <div class="pt-form-row pt-grid-3">
                                        <div class="form-group">
                                            <label class="form-label">Suffix</label>
                                            <select id="full-reg-suffix" class="form-control">
                                                <option value="">None</option>
                                                <option value="Jr">Jr.</option>
                                                <option value="Sr">Sr.</option>
                                                <option value="II">II</option>
                                                <option value="III">III</option>
                                                <option value="IV">IV</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Preferred / Nickname</label>
                                            <input type="text" id="full-reg-nickname" class="form-control" placeholder="e.g. Alex, Bob">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Maiden / Previous Name</label>
                                            <input type="text" id="full-reg-maiden-name" class="form-control" placeholder="Previous legal name">
                                        </div>
                                    </div>

                                    <div class="pt-sub-divider">
                                        <h4>Demographics & Encrypted Identifiers</h4>
                                        <span class="pt-sub-tag">Optional & Encrypted</span>
                                    </div>

                                    <div class="pt-form-row pt-grid-3">
                                        <div class="form-group">
                                            <label class="form-label">Date of Birth <span class="req">*</span></label>
                                            <input type="date" id="full-reg-dob" class="form-control">
                                            <div id="full-reg-age-preview" class="pt-field-hint">Age: —</div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Birth Sex <span class="req">*</span></label>
                                            <select id="full-reg-birth-sex" class="form-control">
                                                <option value="">-- Select --</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Intersex">Intersex</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Social Security Number (SSN)</label>
                                            <input type="text" id="full-reg-ssn" class="form-control" placeholder="XXX-XX-XXXX" maxlength="11">
                                            <div class="pt-field-hint"><i class="fas fa-lock"></i> Encrypted at rest</div>
                                        </div>
                                    </div>

                                    <div class="pt-form-row pt-grid-3">
                                        <div class="form-group">
                                            <label class="form-label">Gender Identity</label>
                                            <select id="full-reg-gender-identity" class="form-control">
                                                <option value="">-- Select --</option>
                                                <option value="Male">Identifies as Male</option>
                                                <option value="Female">Identifies as Female</option>
                                                <option value="Transgender Male">Transgender Male</option>
                                                <option value="Transgender Female">Transgender Female</option>
                                                <option value="Non-Binary">Non-Binary</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Pronouns</label>
                                            <select id="full-reg-pronouns" class="form-control">
                                                <option value="">-- Select --</option>
                                                <option value="He/Him">He / Him</option>
                                                <option value="She/Her">She / Her</option>
                                                <option value="They/Them">They / Them</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Marital Status</label>
                                            <select id="full-reg-marital-status" class="form-control">
                                                <option value="">-- Select --</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Divorced">Divorced</option>
                                                <option value="Widowed">Widowed</option>
                                                <option value="Separated">Separated</option>
                                                <option value="Domestic Partner">Domestic Partner</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="pt-form-row pt-grid-3">
                                        <div class="form-group">
                                            <label class="form-label">Primary Language</label>
                                            <select id="full-reg-language" class="form-control">
                                                <option value="English" selected>English</option>
                                                <option value="Spanish">Spanish</option>
                                                <option value="French">French</option>
                                                <option value="Arabic">Arabic</option>
                                                <option value="Chinese">Chinese (Mandarin/Cantonese)</option>
                                                <option value="Vietnamese">Vietnamese</option>
                                                <option value="Tagalog">Tagalog</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Race</label>
                                            <select id="full-reg-race" class="form-control">
                                                <option value="">-- Select --</option>
                                                <option value="American Indian or Alaska Native">American Indian / Alaska Native</option>
                                                <option value="Asian">Asian</option>
                                                <option value="Black or African American">Black or African American</option>
                                                <option value="Native Hawaiian or Other Pacific Islander">Native Hawaiian / Pacific Islander</option>
                                                <option value="White">White</option>
                                                <option value="Other Race">Other Race</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Ethnicity</label>
                                            <select id="full-reg-ethnicity" class="form-control">
                                                <option value="">-- Select --</option>
                                                <option value="Hispanic or Latino">Hispanic or Latino</option>
                                                <option value="Not Hispanic or Latino">Not Hispanic or Latino</option>
                                                <option value="Declined to Specify">Declined to Specify</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Patient IDs & Documents -->
                                    <div class="pt-sub-divider">
                                        <h4>Government & Clinical Identification Documents</h4>
                                        <button type="button" class="btn btn-sm" id="btn-add-id-row" style="font-size: 0.78rem; font-weight: 700; color: #0284c7; border: 1px solid #cbd5e1; background: #ffffff; padding: 4px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;"><i class="fas fa-plus"></i> Add Document</button>
                                    </div>
                                    <div id="patient-ids-container" class="pt-ids-list">
                                        <div class="patient-id-row">
                                            <select class="form-control patient-id-type" style="width: 160px; flex: none;">
                                                <option value="Driver License">Driver's License</option>
                                                <option value="State ID">State ID Card</option>
                                                <option value="Passport">Passport</option>
                                                <option value="Military ID">Military ID</option>
                                                <option value="SSN">SSN Card</option>
                                                <option value="Insurance Card">Insurance Card</option>
                                                <option value="Other Document">Other Document</option>
                                            </select>
                                            <input type="text" class="form-control patient-id-val" placeholder="Document ID Number" style="flex: 1;">
                                            <input type="file" class="patient-id-file-input" style="display: none;" accept="image/*,.pdf">
                                            <span class="id-action-link" style="color: #0284c7; font-weight: 600; font-size: 0.82rem; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;"><i class="fas fa-paperclip"></i> <span class="id-action-text">Attach File</span></span>
                                            <i class="fas fa-times remove-id-row-btn" title="Remove Document Row" style="color: #94a3b8; cursor: pointer; margin-left: 6px;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══════════════════════════════════════════════════════ -->
                        <!-- STEP 2: CONTACT & EMERGENCY DETAILS -->
                        <!-- ═══════════════════════════════════════════════════════ -->
                        <div class="pt-step-pane" id="pt-step-pane-2">
                            <div class="pt-card">
                                <div class="pt-card-header">
                                    <div class="pt-card-header-left">
                                        <h3 class="pt-section-title">Contact & Address</h3>
                                        <p class="pt-section-desc">Primary residential address, personal contact channels, and designated emergency contact.</p>
                                    </div>
                                    <span class="pt-step-badge">Step 2 of 6</span>
                                </div>

                                <div class="pt-card-body">
                                    <div class="pt-sub-divider" style="margin-top: 0;">
                                        <h4>Residential Address</h4>
                                    </div>

                                    <div class="pt-form-row pt-grid-2">
                                        <div class="form-group">
                                            <label class="form-label">Street Address Line 1 <span class="req">*</span></label>
                                            <input type="text" id="full-reg-address-1" class="form-control" placeholder="House number and street name">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Address Line 2 (Apt, Suite, Unit)</label>
                                            <input type="text" id="full-reg-address-2" class="form-control" placeholder="Apt, Suite, Bldg, Fl">
                                        </div>
                                    </div>

                                    <div class="pt-form-row pt-grid-4">
                                        <div class="form-group">
                                            <label class="form-label">City <span class="req">*</span></label>
                                            <input type="text" id="full-reg-city" class="form-control" placeholder="City">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">State <span class="req">*</span></label>
                                            <select id="full-reg-state" class="form-control">
                                                <option value="">-- Select --</option>
                                                <option value="AL">Alabama</option>
                                                <option value="AK">Alaska</option>
                                                <option value="AZ">Arizona</option>
                                                <option value="CA">California</option>
                                                <option value="CO">Colorado</option>
                                                <option value="FL">Florida</option>
                                                <option value="GA">Georgia</option>
                                                <option value="IL">Illinois</option>
                                                <option value="NY">New York</option>
                                                <option value="NC">North Carolina</option>
                                                <option value="OH">Ohio</option>
                                                <option value="PA">Pennsylvania</option>
                                                <option value="TX">Texas</option>
                                                <option value="VA">Virginia</option>
                                                <option value="WA">Washington</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Zip Code <span class="req">*</span></label>
                                            <input type="text" id="full-reg-zip" class="form-control" placeholder="5-digit Zip" maxlength="10">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Country</label>
                                            <select id="full-reg-country" class="form-control">
                                                <option value="United States" selected>United States</option>
                                                <option value="Canada">Canada</option>
                                                <option value="Mexico">Mexico</option>
                                                <option value="United Kingdom">United Kingdom</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="pt-form-row">
                                        <label class="pt-checkbox-label">
                                            <input type="checkbox" id="full-reg-is-po-box">
                                            <span>This is a P.O. Box address</span>
                                        </label>
                                    </div>

                                    <div class="pt-sub-divider">
                                        <h4>Direct Contact Numbers & Email</h4>
                                    </div>

                                    <div class="pt-form-row pt-grid-3">
                                        <div class="form-group">
                                            <label class="form-label">Mobile / Cell Phone <span class="req">*</span></label>
                                            <input type="tel" id="full-reg-cell-phone" class="form-control" placeholder="(555) 000-0000">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Home Phone</label>
                                            <input type="tel" id="full-reg-home-phone" class="form-control" placeholder="(555) 000-0000">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Email Address <span class="req">*</span></label>
                                            <input type="email" id="full-reg-email" class="form-control" placeholder="patient@example.com">
                                        </div>
                                    </div>

                                    <div class="pt-sub-divider">
                                        <h4>Emergency Contact</h4>
                                        <span class="pt-sub-tag">Primary point of contact</span>
                                    </div>

                                    <div class="pt-form-row pt-grid-3">
                                        <div class="form-group">
                                            <label class="form-label">Emergency Contact Name <span class="req">*</span></label>
                                            <input type="text" id="full-reg-emergency-name" class="form-control" placeholder="Full name">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Relationship to Patient <span class="req">*</span></label>
                                            <select id="full-reg-emergency-rel" class="form-control">
                                                <option value="">-- Select --</option>
                                                <option value="Spouse">Spouse</option>
                                                <option value="Parent">Parent</option>
                                                <option value="Child">Child</option>
                                                <option value="Sibling">Sibling</option>
                                                <option value="Guardian">Legal Guardian</option>
                                                <option value="Friend">Friend</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Emergency Phone <span class="req">*</span></label>
                                            <input type="tel" id="full-reg-emergency-phone" class="form-control" placeholder="(555) 000-0000">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══════════════════════════════════════════════════════ -->
                        <!-- STEP 3: INSURANCE & COVERAGE DETAILS -->
                        <!-- ═══════════════════════════════════════════════════════ -->
                        <div class="pt-step-pane" id="pt-step-pane-3">
                            <div class="pt-card">
                                <div class="pt-card-header">
                                    <div class="pt-card-header-left">
                                        <h3 class="pt-section-title">Insurance Coverage</h3>
                                        <p class="pt-section-desc">Primary and optional secondary payer details for medical claims, copays, and eligibility.</p>
                                    </div>
                                    <span class="pt-step-badge">Step 3 of 6</span>
                                </div>

                                <div class="pt-card-body">
                                    <div class="pt-sub-divider" style="margin-top: 0;">
                                        <h4>Primary Insurance Policy</h4>
                                        <span class="pt-sub-tag">Primary Payer</span>
                                    </div>

                                    <div class="pt-form-row pt-grid-3">
                                        <div class="form-group">
                                            <label class="form-label">Insurance Provider / Payer Name <span class="req">*</span></label>
                                            <select id="full-reg-insurance-provider" class="form-control">
                                                <option value="">-- Select Payer --</option>
                                                <option value="Blue Cross Blue Shield" selected>Blue Cross Blue Shield</option>
                                                <option value="Medicare Part B">Medicare Part B</option>
                                                <option value="Medicaid">Medicaid</option>
                                                <option value="Aetna">Aetna</option>
                                                <option value="UnitedHealthcare">UnitedHealthcare</option>
                                                <option value="Cigna Health">Cigna Health</option>
                                                <option value="Humana">Humana</option>
                                                <option value="Kaiser Permanente">Kaiser Permanente</option>
                                                <option value="Self Pay / Uninsured">Self Pay / Uninsured</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Policy / Member ID Number <span class="req">*</span></label>
                                            <input type="text" id="full-reg-policy-no" class="form-control" placeholder="e.g. BCBS-88392100">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Group Number</label>
                                            <input type="text" id="full-reg-group-no" class="form-control" placeholder="e.g. GRP-44029">
                                        </div>
                                    </div>

                                    <div class="pt-form-row pt-grid-3">
                                        <div class="form-group">
                                            <label class="form-label">Payer ID</label>
                                            <input type="text" id="full-reg-payer-id" class="form-control" placeholder="e.g. 00040">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Plan Name / Product</label>
                                            <input type="text" id="full-reg-plan-name" class="form-control" placeholder="e.g. Preferred PPO Gold">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Copay Amount ($)</label>
                                            <input type="text" id="full-reg-copay" class="form-control" placeholder="e.g. 25.00">
                                        </div>
                                    </div>

                                    <div class="pt-sub-divider">
                                        <h4>Policy Subscriber & Relationship</h4>
                                    </div>

                                    <div class="pt-form-row pt-grid-3">
                                        <div class="form-group">
                                            <label class="form-label">Subscriber Relationship</label>
                                            <select id="full-reg-subscriber-rel" class="form-control">
                                                <option value="Self" selected>Self (Patient is Policyholder)</option>
                                                <option value="Spouse">Spouse</option>
                                                <option value="Parent">Parent</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Subscriber Full Name</label>
                                            <input type="text" id="full-reg-subscriber-name" class="form-control" placeholder="Subscriber name">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Subscriber DOB</label>
                                            <input type="date" id="full-reg-subscriber-dob" class="form-control">
                                        </div>
                                    </div>

                                    <div class="pt-sub-divider">
                                        <h4>Secondary Insurance (Optional)</h4>
                                    </div>

                                    <div class="form-group">
                                        <label class="pt-checkbox-label">
                                            <input type="checkbox" id="full-reg-has-secondary-insurance">
                                            <span>Patient has Secondary Insurance Coverage</span>
                                        </label>
                                    </div>

                                    <div id="secondary-insurance-fields-box" class="pt-form-row pt-grid-3" style="display: none; background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 10px;">
                                        <div class="form-group">
                                            <label class="form-label">Secondary Payer</label>
                                            <input type="text" id="full-reg-sec-provider" class="form-control" placeholder="Secondary Provider">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Secondary Policy Number</label>
                                            <input type="text" id="full-reg-sec-policy-no" class="form-control" placeholder="Policy ID">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Secondary Group #</label>
                                            <input type="text" id="full-reg-sec-group-no" class="form-control" placeholder="Group ID">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- ═══════════════════════════════════════════════════════ -->
                        <!-- STEP 5: MEDICAL HISTORY & CLINICAL BASELINE -->
                        <!-- ═══════════════════════════════════════════════════════ -->
                        <div class="pt-step-pane" id="pt-step-pane-4">
                            <div class="pt-card">
                                <div class="pt-card-header">
                                    <div class="pt-card-header-left">
                                        <h3 class="pt-section-title">Medical History</h3>
                                        <p class="pt-section-desc">Clinical baseline, active allergies, current medications, past surgical, family, and social history.</p>
                                    </div>
                                    <span class="pt-step-badge">Step 4 of 6</span>
                                </div>

                                <div class="pt-card-body">
                                    <div class="pt-sub-divider" style="margin-top: 0;">
                                        <h4>Known Allergies</h4>
                                        <span class="pt-sub-tag">Clinical Safety Alert</span>
                                    </div>

                                    <div class="form-group" style="margin-bottom: 20px;">
                                        <label class="form-label">Known Allergies, Reactions & Severity</label>
                                        <textarea id="full-reg-allergies-input" class="form-control" rows="2" placeholder="e.g. No Known Drug Allergies (NKDA), or Penicillin (Hives / Severe), Latex, Peanuts"></textarea>
                                        <span class="pt-field-hint" style="font-size: 0.78rem; color: #64748b; margin-top: 4px; display: block;">Specify all drug, food, or environmental allergies or enter &quot;NKDA&quot; if none.</span>
                                    </div>

                                    <div class="pt-sub-divider">
                                        <h4>Current Medications</h4>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Active Prescriptions & Over-the-Counter Medications (Dosage & Frequency)</label>
                                        <textarea id="full-reg-medications" class="form-control" rows="2" placeholder="e.g. Lisinopril 10mg PO Daily, Metformin 500mg BID with meals, Aspirin 81mg Daily, Multivitamin"></textarea>
                                    </div>

                                    <div class="pt-sub-divider">
                                        <h4>Chronic Conditions & Past Medical History (PMH)</h4>
                                    </div>

                                    <div class="pt-conditions-grid">
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="Hypertension"> Hypertension</label>
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="Type 2 Diabetes"> Type 2 Diabetes</label>
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="Asthma / COPD"> Asthma / COPD</label>
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="Hyperlipidemia"> Hyperlipidemia</label>
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="Heart Disease / CAD"> Heart Disease / CAD</label>
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="Arthritis / Osteoarthritis"> Arthritis / Joint Disease</label>
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="Depression / Anxiety"> Depression / Anxiety</label>
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="Thyroid Disorder"> Thyroid Disorder</label>
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="Chronic Kidney Disease"> Chronic Kidney Disease</label>
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="Cancer History"> Cancer History</label>
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="Stroke / TIA"> Stroke / TIA</label>
                                        <label class="pt-condition-cb"><input type="checkbox" class="condition-item" value="GERD / Acid Reflux"> GERD / Acid Reflux</label>
                                    </div>

                                    <div class="pt-form-row pt-grid-2" style="margin-top: 16px;">
                                        <div class="form-group">
                                            <label class="form-label">Past Surgeries & Hospitalizations (PSH)</label>
                                            <textarea id="full-reg-surgeries" class="form-control" rows="2" placeholder="e.g. Appendectomy (2018), Knee Arthroscopy (2021), C-Section (2015)"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Family Medical History (FH)</label>
                                            <textarea id="full-reg-family-history" class="form-control" rows="2" placeholder="e.g. Father: CAD/Myocardial Infarction, Mother: Type 2 Diabetes, Sibling: Hypertension"></textarea>
                                        </div>
                                    </div>

                                    <div class="pt-sub-divider">
                                        <h4>Social & Lifestyle History</h4>
                                    </div>

                                    <div class="pt-form-row pt-grid-2">
                                        <div class="form-group">
                                            <label class="form-label">Tobacco / Smoking Status</label>
                                            <select id="full-reg-smoking-status" class="form-control">
                                                <option value="Never Smoker" selected>Never Smoker</option>
                                                <option value="Former Smoker">Former Smoker</option>
                                                <option value="Current Every Day Smoker">Current Every Day Smoker</option>
                                                <option value="Current Some Day Smoker">Current Some Day Smoker</option>
                                                <option value="Unknown">Unknown / Declined</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Alcohol Consumption</label>
                                            <select id="full-reg-alcohol-use" class="form-control">
                                                <option value="None" selected>None (Non-drinker)</option>
                                                <option value="Occasional">Occasional / Social (1-2 drinks/month)</option>
                                                <option value="Moderate">Moderate (1-2 drinks/week)</option>
                                                <option value="Heavy">Heavy / Daily</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="pt-sub-divider">
                                        <h4>Primary Care & Pharmacy</h4>
                                    </div>

                                    <div class="pt-form-row pt-grid-2">
                                        <div class="form-group">
                                            <label class="form-label">Primary Care Provider (EHR Staff)</label>
                                            <select id="full-reg-primary-provider" class="form-control">
                                                <option value="">-- Select Provider --</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Preferred Pharmacy</label>
                                            <input type="text" id="full-reg-preferred-pharmacy" class="form-control" placeholder="e.g. CVS Pharmacy #4029, 123 Main St (555-0199)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══════════════════════════════════════════════════════ -->
                        <!-- STEP 6: CONSENTS & AUTHORIZATIONS -->
                        <!-- ═══════════════════════════════════════════════════════ -->
                        <div class="pt-step-pane" id="pt-step-pane-5">
                            <div class="pt-card">
                                <div class="pt-card-header">
                                    <div class="pt-card-header-left">
                                        <h3 class="pt-section-title">Consents & Authorizations</h3>
                                        <p class="pt-section-desc">HIPAA compliance acknowledgments, general treatment consent, and digital signature.</p>
                                    </div>
                                    <span class="pt-step-badge">Step 5 of 6</span>
                                </div>

                                <div class="pt-card-body">
                                    <div id="consent-live-indicator-box" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 0.88rem; color: #166534; display: flex; align-items: center; justify-content: space-between;">
                                        <div><i class="fas fa-file-signature"></i> Consenting for: <strong id="consent-live-patient-display">Patient</strong></div>
                                        <div>Signer Capacity: <strong id="consent-live-role-display">Self (Patient)</strong></div>
                                    </div>

                                    <div class="pt-consent-card-list">
                                        <!-- Consent 1: Consent to treat -->
                                        <div class="pt-consent-box">
                                            <label class="pt-consent-header">
                                                <input type="checkbox" id="consent-treatment" checked>
                                                <strong>1. Consent to Treat <span class="req">*</span></strong>
                                            </label>
                                            <div style="font-size: 0.8rem; font-weight: 600; color: #0284c7; margin: 2px 0 6px 26px;">Authorizes the care team to provide medical evaluation and treatment.</div>
                                            <p class="pt-consent-text">I voluntarily consent to outpatient healthcare evaluations, diagnostic examinations, clinical laboratory tests, preventative screenings, and medical treatment by the healthcare providers and staff of Specialty EHR for <span class="consent-patient-name-span">the patient</span>.</p>
                                        </div>

                                        <!-- Consent 2: HIPAA privacy notice -->
                                        <div class="pt-consent-box">
                                            <label class="pt-consent-header">
                                                <input type="checkbox" id="consent-hipaa" checked>
                                                <strong>2. HIPAA Privacy Notice <span class="req">*</span></strong>
                                            </label>
                                            <div style="font-size: 0.8rem; font-weight: 600; color: #0284c7; margin: 2px 0 6px 26px;">Acknowledges receipt of the Notice of Privacy Practices.</div>
                                            <p class="pt-consent-text">I acknowledge that I have received, read, and understand the Notice of Privacy Practices (NPP) outlining how Protected Health Information (PHI) is safeguarded, used for treatment, payment, and operations (TPO), and how patient privacy rights are maintained for <span class="consent-patient-name-span">the patient</span>.</p>
                                        </div>

                                        <!-- Consent 3: Financial responsibility -->
                                        <div class="pt-consent-box">
                                            <label class="pt-consent-header">
                                                <input type="checkbox" id="consent-financial-resp" checked>
                                                <strong>3. Financial Responsibility <span class="req">*</span></strong>
                                            </label>
                                            <div style="font-size: 0.8rem; font-weight: 600; color: #0284c7; margin: 2px 0 6px 26px;">Accepts responsibility for balances not covered by insurance.</div>
                                            <p class="pt-consent-text">I accept personal financial responsibility for the payment of all clinical charges, co-payments, deductibles, coinsurance, and any non-covered service balances for healthcare services rendered to <span class="consent-patient-name-span">the patient</span>.</p>
                                        </div>

                                        <!-- Consent 4: Assignment of benefits -->
                                        <div class="pt-consent-box">
                                            <label class="pt-consent-header">
                                                <input type="checkbox" id="consent-assignment-benefits" checked>
                                                <strong>4. Assignment of Benefits <span class="req">*</span></strong>
                                            </label>
                                            <div style="font-size: 0.8rem; font-weight: 600; color: #0284c7; margin: 2px 0 6px 26px;">Allows insurance payments to be sent directly to the practice.</div>
                                            <p class="pt-consent-text">I hereby authorize direct assignment and payment of all medical, surgical, and diagnostic insurance benefits directly to Specialty EHR for healthcare services provided to <span class="consent-patient-name-span">the patient</span>.</p>
                                        </div>

                                        <!-- Consent 5: Telehealth consent -->
                                        <div class="pt-consent-box">
                                            <label class="pt-consent-header">
                                                <input type="checkbox" id="consent-telehealth" checked>
                                                <strong>5. Telehealth Consent <span class="req">*</span></strong>
                                            </label>
                                            <div style="font-size: 0.8rem; font-weight: 600; color: #0284c7; margin: 2px 0 6px 26px;">Permits care delivered by secure video or phone visits.</div>
                                            <div class="pt-consent-text" style="font-size: 0.85rem; line-height: 1.5; color: #334155;">
                                                <p style="margin: 0 0 6px 0;">This contract indicates consent for distance-oriented behavioral health and clinical sessions, otherwise known as telehealth, which take place over a HIPAA compliant telehealth platform for <span class="consent-patient-name-span">the patient</span>. By signing this contract, I agree to the following:</p>
                                                <ul style="margin: 0; padding-left: 18px;">
                                                    <li>To find a quiet and protected space for our virtual sessions.</li>
                                                    <li>That during our session time, no one else will be present in the room (unless indicated to the therapist and discussed prior to session).</li>
                                                    <li>That no phone calls, texts, emails or web surfing will occur.</li>
                                                    <li>That if there is a loss of connection, the therapist will initiate the call back.</li>
                                                    <li>Sessions are scheduled for 60 minutes to account for any connection disruption, but the session shall last 45-50 minute as per residential session protocol.</li>
                                                    <li>The session and the chat will not be recorded nor will screen shots be taken unless expressly discussed prior to session and with clinical goals in mind.</li>
                                                    <li>All rules regarding mandated reporting and reporting harm to self or others remain the same as residential sessions as per Torres Behavioral Health ethical standards and legal protocol.</li>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- Consent 6: Release of information & Communications -->
                                        <div class="pt-consent-box">
                                            <label class="pt-consent-header">
                                                <input type="checkbox" id="consent-release-info" checked>
                                                <strong>6. Release of Information & Communications Consent <span class="req">*</span></strong>
                                            </label>
                                            <div style="font-size: 0.8rem; font-weight: 600; color: #0284c7; margin: 2px 0 6px 26px;">Authorizes care coordination and secure electronic notices (Email/SMS).</div>
                                            <p class="pt-consent-text">I authorize Specialty EHR to coordinate medical records with consulting physicians, diagnostic labs, and pharmacies for continuity of care, and consent to receiving appointment reminders and clinical notices via secure email and SMS text messages for <span class="consent-patient-name-span">the patient</span>.</p>
                                        </div>
                                    </div>

                                    <div class="pt-sub-divider">
                                        <h4>Electronic Acknowledgment & Signature</h4>
                                    </div>

                                    <div class="pt-form-row pt-grid-3">
                                        <div class="form-group">
                                            <label class="form-label">Signer Full Legal Name <span class="req">*</span></label>
                                            <input type="text" id="consent-signer-name" class="form-control" placeholder="Type full name as signature">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Signer Relationship to Patient</label>
                                            <select id="consent-signer-rel" class="form-control">
                                                <option value="Self (Patient)" selected>Self (Patient)</option>
                                                <option value="Parent / Legal Guardian">Parent / Legal Guardian</option>
                                                <option value="Medical Power of Attorney">Medical Power of Attorney</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Date of Acknowledgment</label>
                                            <input type="date" id="consent-date" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══════════════════════════════════════════════════════ -->
                        <!-- STEP 7: REVIEW & FINAL CONFIRMATION -->
                        <!-- ═══════════════════════════════════════════════════════ -->
                        <div class="pt-step-pane" id="pt-step-pane-6">
                            <div class="pt-card">
                                <div class="pt-card-header">
                                    <div class="pt-card-header-left">
                                        <h3 class="pt-section-title">Review & Confirmation</h3>
                                        <p class="pt-section-desc">Please review all patient information for accuracy before final submission to the EHR directory.</p>
                                    </div>
                                    <span class="pt-step-badge pt-step-badge-ready">Step 6 of 6 • Ready to Submit</span>
                                </div>

                                <div class="pt-card-body">
                                    <div class="pt-review-grid">
                                        
                                        <!-- Review Card 1: Basic Info -->
                                        <div class="pt-review-card">
                                            <div class="pt-review-card-head">
                                                <div class="pt-review-card-title"><i class="fas fa-user-circle"></i> 1. Basic Info & Demographics</div>
                                                <button type="button" class="btn-review-jump" data-jump-step="1">Edit</button>
                                            </div>
                                            <div class="pt-review-card-body" id="review-summary-basic">
                                                <!-- Dynamic population -->
                                            </div>
                                        </div>

                                        <!-- Review Card 2: Contact -->
                                        <div class="pt-review-card">
                                            <div class="pt-review-card-head">
                                                <div class="pt-review-card-title"><i class="fas fa-map-marker-alt"></i> 2. Contact & Address</div>
                                                <button type="button" class="btn-review-jump" data-jump-step="2">Edit</button>
                                            </div>
                                            <div class="pt-review-card-body" id="review-summary-contact">
                                                <!-- Dynamic population -->
                                            </div>
                                        </div>

                                        <!-- Review Card 3: Insurance -->
                                        <div class="pt-review-card">
                                            <div class="pt-review-card-head">
                                                <div class="pt-review-card-title"><i class="fas fa-file-invoice-dollar"></i> 3. Insurance Coverage</div>
                                                <button type="button" class="btn-review-jump" data-jump-step="3">Edit</button>
                                            </div>
                                            <div class="pt-review-card-body" id="review-summary-insurance">
                                                <!-- Dynamic population -->
                                            </div>
                                        </div>

                                        <!-- Review Card 4: Guarantor -->
                                        <div class="pt-review-card">
                                            <div class="pt-review-card-head">
                                                <div class="pt-review-card-title"><i class="fas fa-hand-holding-usd"></i> 4. Guarantor & Notifications</div>
                                                <button type="button" class="btn-review-jump" data-jump-step="4">Edit</button>
                                            </div>
                                            <div class="pt-review-card-body" id="review-summary-guarantor">
                                                <!-- Dynamic population -->
                                            </div>
                                        </div>

                                        <!-- Review Card 5: Medical History -->
                                        <div class="pt-review-card">
                                            <div class="pt-review-card-head">
                                                <div class="pt-review-card-title"><i class="fas fa-notes-medical"></i> 4. Clinical Baseline</div>
                                                <button type="button" class="btn-review-jump" data-jump-step="4">Edit</button>
                                            </div>
                                            <div class="pt-review-card-body" id="review-summary-medical">
                                                <!-- Dynamic population -->
                                            </div>
                                        </div>

                                        <!-- Review Card 6: Consents -->
                                        <div class="pt-review-card">
                                            <div class="pt-review-card-head">
                                                <div class="pt-review-card-title"><i class="fas fa-file-signature"></i> 5. Consents & Authorizations</div>
                                                <button type="button" class="btn-review-jump" data-jump-step="5">Edit</button>
                                            </div>
                                            <div class="pt-review-card-body" id="review-summary-consents">
                                                <!-- Dynamic population -->
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </section>
                </div>

                <!-- Sticky Stepper Navigation Footer -->
                <div class="pt-wizard-footer">
                    <div class="pt-wizard-footer-left">
                        <button type="button" class="btn-pt-cancel" id="btn-wizard-cancel">Cancel</button>
                    </div>
                    <div class="pt-wizard-footer-right">
                        <button type="button" class="btn-pt-draft" id="btn-step-save-draft"><i class="fas fa-save"></i> Save Draft</button>
                        <button type="button" class="btn-pt-prev" id="btn-step-prev" style="display: none;"><i class="fas fa-arrow-left"></i> Previous Step</button>
                        <button type="button" class="btn-pt-next" id="btn-step-next">Next Step <i class="fas fa-arrow-right"></i></button>
                        <button type="button" class="btn-pt-submit" id="btn-step-submit" style="display: none;"><i class="fas fa-check-circle"></i> Submit Patient Registration</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Separate Full-Page Medical Record Dashboard View -->
        <div id="patient-dashboard-full-view" style="display: none;">
            <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <button class="btn" id="back-to-directory-btn" style="background: #f1f5f9; color: #0f172a; border: none; font-weight: 600; font-size: 0.85rem; padding: 8px 16px; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                    <i class="fas fa-arrow-left"></i> Back to Patient Directory
                </button>
                <h2 id="full-dash-patient-name" style="display: none;"></h2>
            </div>
            <div id="patient-dashboard-full-content" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); min-height: 80vh; display: flex; flex-direction: column;"></div>
        </div>

        <?php include __DIR__ . '/clinical_modal.php'; ?>
    </main>
</div>





