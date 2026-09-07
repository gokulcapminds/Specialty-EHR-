<!-- public/modules/patients.php --><link rel="stylesheet" href="css/modules/patients.css?v=<?= time() ?>">
<link rel="stylesheet" href="css/modules/clinical.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'patients'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" id="patients-workspace-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h1>Patient Directory</h1>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- Patient Directory List View -->
        <div id="patient-directory-list-view">
            <div class="card search-card">
                <div class="search-bar">
                    <label for="patient-search" class="sr-only">Search Patients</label>
                    <input type="text" id="patient-search" class="form-control" placeholder="Search by name, DOB...">
                    <button class="btn btn-primary" id="search-btn">Search</button>
                </div>
            </div>

            <div class="card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;">
                    <h2 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">Active Demographics</h2>
                    <button class="btn btn-primary" id="register-patient-btn" style="background: #0284c7; border-color: #0284c7; font-weight: 700; border-radius: 6px; padding: 8px 18px; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px;"><i class="fas fa-user-plus"></i> New Patient</button>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Home Phone</th>
                                <th>SSN</th>
                                <th>Date of Birth</th>
                                <th>External ID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="patients-list">
                            <tr>
                                <td colspan="6">Loading patients...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="patients-pagination"></div>
            </div>
        </div>

        <!-- Separate Dedicated Full-Screen Patient Registration View (No Popup) -->
        <div id="patient-registration-full-view" style="display: none;">
            <!-- Registration Header Bar -->
            <div class="card reg-header-card" style="margin-bottom: 20px; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;">
                <h2 id="full-reg-header-title" style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #1e293b;">Add Patient</h2>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-warning" id="full-reg-submit-add-btn" style="background: #f59e0b; border-color: #f59e0b; color: white; font-weight: 700; padding: 8px 24px;">Add</button>
                    <button type="button" class="btn btn-secondary" id="full-reg-cancel-btn" style="font-weight: 600;">Cancel</button>
                </div>
            </div>

            <!-- Error Alert Banner -->
            <div id="full-reg-error-alert" style="display: none; background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 1.1rem; color: #ef4444;"></i>
                    <span id="full-reg-error-text">Please fill in all required fields marked with an asterisk (*).</span>
                </div>
            </div>

            <!-- 3-Column Registration Form Layout -->
            <form id="full-patient-reg-form" novalidate>
                <div class="reg-form-3col-grid">
                    
                    <!-- Column 1: Basic Information -->
                    <div class="card reg-column-card">
                        <h3 class="reg-section-title">Basic Information</h3>
                        
                        <!-- Avatar photo upload badge -->
                        <div style="display: flex; justify-content: flex-start; margin-bottom: 20px;">
                            <div id="full-reg-photo-badge" style="position: relative; width: 88px; height: 88px; border-radius: 50%; background: #94a3b8; display: flex; align-items: center; justify-content: center; color: #f8fafc; font-size: 2.8rem; cursor: pointer;" title="Upload Profile Photo">
                                <i class="fas fa-user" id="avatar-default-icon"></i>
                                <img id="avatar-preview-img" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" src="" alt="Patient Photo">
                                <div style="position: absolute; bottom: -2px; right: -2px; width: 30px; height: 30px; border-radius: 50%; background: #94a3b8; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                                    <i class="fas fa-plus"></i>
                                </div>
                            </div>
                            <input type="file" id="full-reg-photo-input" accept="image/*" style="display: none;">
                        </div>

                        <div class="form-group">
                            <label class="form-label">First Name <span style="color: #ef4444;">*</span></label>
                            <input type="text" id="full-reg-first-name" class="form-control" placeholder="Enter first name">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Middle Name</label>
                            <input type="text" id="full-reg-middle-name" class="form-control" placeholder="Enter middle name">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Last Name <span style="color: #ef4444;">*</span></label>
                            <input type="text" id="full-reg-last-name" class="form-control" placeholder="Enter last name">
                        </div>

                        <div class="form-group">
                            <label class="form-label">DOB <span style="color: #ef4444;">*</span></label>
                            <input type="date" id="full-reg-dob" class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Birth Sex <span style="color: #ef4444;">*</span></label>
                            <select id="full-reg-birth-sex" class="form-control">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Intersex">Intersex</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label class="form-label" style="margin-bottom: 0;">Gender Identity</label>
                                <label style="font-size: 0.8rem; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="full-reg-gender-declined"> Declined to specify
                                </label>
                            </div>
                            <select id="full-reg-gender-identity" class="form-control" style="margin-top: 4px;">
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
                                <option value="He/Him">He/Him</option>
                                <option value="She/Her">She/Her</option>
                                <option value="They/Them">They/Them</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nickname</label>
                            <input type="text" id="full-reg-nickname" class="form-control" placeholder="Enter nickname">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Suffix</label>
                            <input type="text" id="full-reg-suffix" class="form-control" placeholder="e.g. Jr, Sr, III">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Maiden Name</label>
                            <input type="text" id="full-reg-maiden-name" class="form-control" placeholder="Enter maiden name">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Previous Name</label>
                            <input type="text" id="full-reg-previous-name" class="form-control" placeholder="Enter previous name">
                        </div>

                        <!-- Patient IDs Section (Exact Match to media_1787659003342.png) -->
                        <div style="border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 14px;">
                            <h4 style="margin: 0 0 12px 0; font-size: 1rem; font-weight: 700; color: #1e293b;">Patient IDs</h4>
                            <div id="patient-ids-container" style="display: flex; flex-direction: column; gap: 10px;">
                                <div class="patient-id-row" style="display: flex; align-items: center; gap: 8px;">
                                    <select class="form-control patient-id-type" style="width: 140px; flex: none;">
                                        <option value="">-- Select --</option>
                                        <option value="Driver License">Driver License</option>
                                        <option value="Military ID">Military ID</option>
                                        <option value="SSN">SSN</option>
                                        <option value="State ID">State ID</option>
                                        <option value="Passport">Passport</option>
                                    </select>
                                    <input type="text" class="form-control patient-id-val" style="width: 120px; flex: none;" placeholder="">
                                    <span class="id-action-link" style="color: #0284c7; font-weight: 600; font-size: 0.85rem; cursor: pointer; text-decoration: none; margin-left: 4px;">Upload</span>
                                    <i class="fas fa-times-circle remove-id-row-btn" style="color: #94a3b8; font-size: 0.95rem; cursor: pointer; display: none;" title="Remove Row"></i>
                                </div>
                                <div class="patient-id-row" style="display: flex; align-items: center; gap: 8px;">
                                    <select class="form-control patient-id-type" style="width: 140px; flex: none;">
                                        <option value="">-- Select --</option>
                                        <option value="Driver License">Driver License</option>
                                        <option value="Military ID">Military ID</option>
                                        <option value="SSN">SSN</option>
                                        <option value="State ID">State ID</option>
                                        <option value="Passport">Passport</option>
                                    </select>
                                    <input type="text" class="form-control patient-id-val" style="width: 120px; flex: none;" placeholder="">
                                    <span class="id-action-link" style="color: #0284c7; font-weight: 600; font-size: 0.85rem; cursor: pointer; text-decoration: none; margin-left: 4px;">Upload</span>
                                    <i class="fas fa-times-circle remove-id-row-btn" style="color: #94a3b8; font-size: 0.95rem; cursor: pointer; display: none;" title="Remove Row"></i>
                                </div>
                                <div class="patient-id-row" style="display: flex; align-items: center; gap: 8px;">
                                    <select class="form-control patient-id-type" style="width: 140px; flex: none;">
                                        <option value="">-- Select --</option>
                                        <option value="Driver License">Driver License</option>
                                        <option value="Military ID">Military ID</option>
                                        <option value="SSN">SSN</option>
                                        <option value="State ID">State ID</option>
                                        <option value="Passport">Passport</option>
                                    </select>
                                    <input type="text" class="form-control patient-id-val" style="width: 120px; flex: none;" placeholder="">
                                    <span class="id-action-link" style="color: #0284c7; font-weight: 600; font-size: 0.85rem; cursor: pointer; text-decoration: none; margin-left: 4px;">Upload</span>
                                    <i class="fas fa-times-circle remove-id-row-btn" style="color: #94a3b8; font-size: 0.95rem; cursor: pointer; display: none;" title="Remove Row"></i>
                                </div>
                            </div>
                            <span id="add-more-ids-btn" class="reg-text-link" style="margin-top: 8px;">More</span>
                        </div>
                    </div>

                    <!-- Column 2: Contact Details -->
                    <div class="card reg-column-card">
                        <h3 class="reg-section-title">Contact Details</h3>

                        <div class="form-group">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" id="full-reg-address-1" class="form-control" placeholder="Street address">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" id="full-reg-address-2" class="form-control" placeholder="Apt, Suite, Unit">
                        </div>

                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" id="full-reg-city" class="form-control" placeholder="Search by Zip Code or City">
                        </div>

                        <div class="form-group">
                            <label class="form-label">State</label>
                            <select id="full-reg-state" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="AL">Alabama</option>
                                <option value="CA">California</option>
                                <option value="FL">Florida</option>
                                <option value="NY">New York</option>
                                <option value="TX">Texas</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <select id="full-reg-country" class="form-control">
                                <option value="United States" selected>United States</option>
                                <option value="Canada">Canada</option>
                                <option value="United Kingdom">United Kingdom</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div style="display: flex; gap: 10px; align-items: flex-end;">
                                <div style="flex: 1;">
                                    <label class="form-label">Zip Code</label>
                                    <input type="text" id="full-reg-zip" class="form-control" placeholder="Zip code">
                                </div>
                                <label style="font-size: 0.8rem; color: #64748b; font-weight: 500; padding-bottom: 10px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="full-reg-po-box"> PO Box
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">County</label>
                            <select id="full-reg-county" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="Orange County">Orange County</option>
                                <option value="Los Angeles County">Los Angeles County</option>
                                <option value="Miami-Dade County">Miami-Dade County</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Cell Phone</label>
                            <input type="tel" id="full-reg-cell-phone" class="form-control" placeholder="xxx-xxx-xxxx">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Home Phone</label>
                            <input type="tel" id="full-reg-home-phone" class="form-control" placeholder="xxx-xxx-xxxx">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Work Phone</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="tel" id="full-reg-work-phone" class="form-control" placeholder="xxx-xxx-xxxx" style="flex: 1;">
                                <input type="text" id="full-reg-work-extn" class="form-control" placeholder="Extn" style="width: 80px;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email <span style="color: #ef4444;">*</span></label>
                            <input type="email" id="full-reg-email" class="form-control" placeholder="patient@example.com">
                        </div>

                        <div style="border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #1e293b;">Previous Address</h4>
                                <span id="add-previous-address-btn" class="reg-text-link">Add</span>
                            </div>
                            <div id="prev-address-list-container" style="margin-top: 4px;"></div>
                        </div>

                        <!-- Emergency Contact Section -->
                        <div style="border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 14px;">
                            <h4 style="margin: 0 0 10px 0; font-size: 0.95rem; font-weight: 700; color: #1e293b;">Emergency Contact</h4>
                            <div class="form-group">
                                <label class="form-label">Contact Name</label>
                                <input type="text" id="full-reg-emerg-name" class="form-control" placeholder="Full name">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="tel" id="full-reg-emerg-phone" class="form-control" placeholder="xxx-xxx-xxxx" style="flex: 1;">
                                    <input type="text" id="full-reg-emerg-extn" class="form-control" placeholder="Extn" style="width: 80px;">
                                </div>
                            </div>
                        </div>

                        <!-- Caregivers Section -->
                        <div style="border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #1e293b;">Caregivers</h4>
                                <span id="add-caregiver-btn" class="reg-text-link">Add</span>
                            </div>
                            <div id="caregiver-list-container" style="margin-top: 4px;"></div>
                        </div>

                        <!-- Guarantor Section -->
                        <div style="border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #1e293b;">Guarantor</h4>
                                <span id="add-guarantor-btn" class="reg-text-link">Add</span>
                            </div>
                            <div id="guarantor-list-container" style="margin-top: 4px;"></div>
                        </div>

                        <!-- Patient Preferences Section (Exact Match to media_1787659003342.png) -->
                        <div style="border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 14px;">
                            <h4 style="margin: 0 0 12px 0; font-size: 1rem; font-weight: 700; color: #1e293b;">Patient Preferences</h4>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <label style="font-size: 0.88rem; font-weight: 600; color: #334155; margin-bottom: 0;">Preferred Communication</label>
                                <select id="full-reg-pref-comm" class="form-control" style="width: 160px;">
                                    <option value="">--Select--</option>
                                    <option value="Email">Email</option>
                                    <option value="Text">Text Message</option>
                                    <option value="Voice Call">Voice Call</option>
                                    <option value="Patient Portal">Patient Portal</option>
                                </select>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem; color: #334155; font-weight: 500;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Email Notifications</span>
                                    <div style="display: flex; gap: 14px; align-items: center;">
                                        <label style="margin:0; cursor:pointer;"><input type="radio" name="email_notifications" value="1" checked> Yes</label>
                                        <label style="margin:0; cursor:pointer;"><input type="radio" name="email_notifications" value="0"> No</label>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Text Notifications</span>
                                    <div style="display: flex; gap: 14px; align-items: center;">
                                        <label style="margin:0; cursor:pointer;"><input type="radio" name="text_notifications" value="1" checked> Yes</label>
                                        <label style="margin:0; cursor:pointer;"><input type="radio" name="text_notifications" value="0"> No</label>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Voice Notifications</span>
                                    <div style="display: flex; gap: 14px; align-items: center;">
                                        <label style="margin:0; cursor:pointer;"><input type="radio" name="voice_notifications" value="1" checked> Yes</label>
                                        <label style="margin:0; cursor:pointer;"><input type="radio" name="voice_notifications" value="0"> No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: PHR Registration & Additional Information -->
                    <div class="card reg-column-card">
                        <h3 class="reg-section-title" style="border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 10px;">Send Patient Intake Forms via Email</h3>
                        <div style="margin-bottom: 20px;">
                            <label style="font-size: 0.88rem; font-weight: 600; color: #475569; display: block; margin-bottom: 8px;">Select Forms to Email Patient upon Registration</label>
                            <div style="display: flex; flex-direction: column; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                                <label style="font-size: 0.85rem; color: #1e293b; font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" class="reg-intake-form-checkbox" value="Consent to Treat" checked> <i class="fas fa-file-signature" style="color:#0284c7;"></i> Consent to Treat
                                </label>
                                <label style="font-size: 0.85rem; color: #1e293b; font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" class="reg-intake-form-checkbox" value="HIPAA Notice of Privacy Practices" checked> <i class="fas fa-shield-alt" style="color:#0d9488;"></i> HIPAA / Privacy Practices
                                </label>
                                <label style="font-size: 0.85rem; color: #1e293b; font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" class="reg-intake-form-checkbox" value="Medical History Questionnaire"> <i class="fas fa-notes-medical" style="color:#6366f1;"></i> Medical History Questionnaire
                                </label>
                                <label style="font-size: 0.85rem; color: #1e293b; font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" class="reg-intake-form-checkbox" value="Financial Guarantor Agreement"> <i class="fas fa-file-invoice-dollar" style="color:#f59e0b;"></i> Financial Guarantor Agreement
                                </label>
                            </div>
                        </div>

                        <h3 class="reg-section-title" style="border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 10px;">Additional Information</h3>

                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select id="full-reg-category" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="General Practice">General Practice</option>
                                <option value="Pediatrics">Pediatrics</option>
                                <option value="OB/GYN">OB/GYN</option>
                                <option value="Geriatrics">Geriatrics</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Payment Source</label>
                            <select id="full-reg-payment-source" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="Insurance">Insurance</option>
                                <option value="Self Pay">Self Pay</option>
                                <option value="Medicaid">Medicaid</option>
                                <option value="Medicare">Medicare</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Blood Group</label>
                            <select id="full-reg-blood-group" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label class="form-label" style="margin-bottom: 0;">Language</label>
                                <label style="font-size: 0.8rem; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="full-reg-lang-declined"> Declined to specify
                                </label>
                            </div>
                            <select id="full-reg-language" class="form-control" style="margin-top: 4px;">
                                <option value="">-- Select --</option>
                                <option value="English">English</option>
                                <option value="Spanish">Spanish</option>
                                <option value="French">French</option>
                                <option value="Mandarin">Mandarin</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label class="form-label" style="margin-bottom: 0;">Race</label>
                                <label style="font-size: 0.8rem; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="full-reg-race-declined"> Declined to specify
                                </label>
                            </div>
                            <select id="full-reg-race" class="form-control" style="margin-top: 4px;">
                                <option value="">-- Select --</option>
                                <option value="Caucasian / White">Caucasian / White</option>
                                <option value="African American / Black">African American / Black</option>
                                <option value="Asian">Asian</option>
                                <option value="Native American">Native American</option>
                                <option value="Pacific Islander">Pacific Islander</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label class="form-label" style="margin-bottom: 0;">Ethnicity</label>
                                <label style="font-size: 0.8rem; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="full-reg-ethnicity-declined"> Declined to specify
                                </label>
                            </div>
                            <select id="full-reg-ethnicity" class="form-control" style="margin-top: 4px;">
                                <option value="">-- Select --</option>
                                <option value="Hispanic or Latino">Hispanic or Latino</option>
                                <option value="Not Hispanic or Latino">Not Hispanic or Latino</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Smoking Status</label>
                            <select id="full-reg-smoking-status" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="Never Smoked">Never Smoked</option>
                                <option value="Former Smoker">Former Smoker</option>
                                <option value="Current Every Day Smoker">Current Every Day Smoker</option>
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
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Employment Status</label>
                            <select id="full-reg-employment-status" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="Full-Time">Full-Time</option>
                                <option value="Part-Time">Part-Time</option>
                                <option value="Self-Employed">Self-Employed</option>
                                <option value="Unemployed">Unemployed</option>
                                <option value="Retired">Retired</option>
                                <option value="Student">Student</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label class="form-label" style="margin-bottom: 0;">Sexual Orientation</label>
                                <label style="font-size: 0.8rem; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <input type="checkbox" id="full-reg-orientation-declined"> Declined to specify
                                </label>
                            </div>
                            <select id="full-reg-sexual-orientation" class="form-control" style="margin-top: 4px;">
                                <option value="">-- Select --</option>
                                <option value="Straight or Heterosexual">Straight or Heterosexual</option>
                                <option value="Lesbian or Gay">Lesbian or Gay</option>
                                <option value="Bisexual">Bisexual</option>
                                <option value="Something else">Something else</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mother's Maiden Name</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <input type="text" id="full-reg-mom-first" class="form-control" placeholder="First Name">
                                <input type="text" id="full-reg-mom-last" class="form-control" placeholder="Last Name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-size: 0.88rem; color: #334155; font-weight: 500; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" id="full-reg-multiple-birth"> Multiple Birth
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label">About Patient</label>
                            <textarea id="full-reg-about-patient" class="form-control" rows="3" placeholder="Additional notes about patient..."></textarea>
                        </div>

                        <!-- How did you hear about us -->
                        <div style="border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 14px;">
                            <h4 style="margin: 0 0 10px 0; font-size: 0.95rem; font-weight: 700; color: #1e293b;">How did you hear about us</h4>
                            <div class="form-group">
                                <label class="form-label">Source</label>
                                <select id="full-reg-hear-source" class="form-control">
                                    <option value="">-- Select --</option>
                                    <option value="Doctor Referral">Doctor Referral</option>
                                    <option value="Friend or Family">Friend or Family</option>
                                    <option value="Online Search">Online Search</option>
                                    <option value="Social Media">Social Media</option>
                                    <option value="Advertisement">Advertisement</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Specific Source</label>
                                <select id="full-reg-hear-specific" class="form-control">
                                    <option value="">-- Select --</option>
                                    <option value="Google">Google</option>
                                    <option value="Insurance Directory">Insurance Directory</option>
                                    <option value="Hospital Affiliate">Hospital Affiliate</option>
                                </select>
                            </div>
                        </div>

                    </div>

                </div>
            </form>
        </div>

        <!-- Separate Full-Page Medical Record Dashboard View -->
        <div id="patient-dashboard-full-view" style="display: none;">
            <div style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
                <button class="btn btn-outline" id="back-to-directory-btn" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-arrow-left"></i> Back to Patient Directory
                </button>
                <h2 id="full-dash-patient-name" style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a;">Medical Record Dashboard</h2>
            </div>
            <div id="patient-dashboard-full-content"></div>
        </div>

        <?php include __DIR__ . '/clinical_modal.php'; ?>
    </main>
</div>
