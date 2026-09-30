<!-- public/modules/administration.php -->
<link rel="stylesheet" href="css/modules/administration.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'administration'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h1>Administration Workspace</h1>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- Tab Navigation Bar -->
        <div class="admin-tab-bar" id="admin-tab-bar">
            <button class="admin-tab-btn" data-tab="facility">Facility</button>
            <button class="admin-tab-btn" data-tab="specialties">Specialties</button>
            <button class="admin-tab-btn" data-tab="user-management">User Management</button>
            <button class="admin-tab-btn" data-tab="roles-permissions">Roles &amp; Permissions</button>
        </div>

        <!-- ── TAB: Facility Management ── -->
        <div class="admin-tab-section active-tab" id="admin-tab-facility">
            <!-- 1A: Facility Directory -->
            <div id="facility-directory-view">
                <div class="card mod-administration-style-5" id="facility-management-card">
                    <div class="mod-administration-style-6">
                        <div>
                            <h2 class="mod-administration-style-2">Facility Management</h2>
                            <p style="color:#64748b;font-size:0.85rem;margin-top:2px;">Manage enterprise hospital organizations, legal entities, and healthcare networks.</p>
                        </div>
                        <button class="btn btn-primary mod-administration-style-7" id="add-facility-btn">
                            <i class="fas fa-plus"></i> Add Facility
                        </button>
                    </div>
                    <div class="table-container">
                        <table class="admin-users-table">
                            <thead>
                                <tr>
                                    <th>Facility Name</th>
                                    <th>Type</th>
                                    <th>Code</th>
                                    <th>Contact</th>
                                    <th>Specialties</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="facility-directory-list">
                                <tr><td colspan="7">Loading facilities...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 1B: Create / Edit Facility Form View -->
            <div id="facility-form-view" style="display: none;">
                <div class="staff-workflow-top-bar">
                    <div class="staff-workflow-title-area">
                        <button type="button" class="btn-back-to-directory" id="btn-back-to-facility-dir" title="Back to Facility Directory">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="staff-workflow-titles">
                            <h2 id="facility-form-title">Create Facility</h2>
                            <p id="facility-form-sub">Register a new enterprise hospital organization or healthcare network.</p>
                        </div>
                    </div>
                    <div class="staff-workflow-actions">
                        <button type="button" class="btn-cancel" id="btn-cancel-facility-form">Cancel</button>
                        <button type="button" class="btn-submit-user" id="btn-save-facility">
                            <span id="facility-save-btn-text">Create Facility</span>
                        </button>
                    </div>
                </div>

                <input type="hidden" id="facility-edit-id" value="">

                <div class="staff-workflow-grid" style="grid-template-columns: 1fr;">
                    <div class="staff-form-container">
                        <!-- Section 1: Organization & Identity -->
                        <div class="staff-card-section">
                            <div class="staff-card-header">
                                <div class="staff-step-num-badge">1</div>
                                <div class="staff-card-header-text">
                                    <h3>Organization Identity</h3>
                                    <p>Primary hospital or network name and legal credentials.</p>
                                </div>
                            </div>
                            <div class="staff-form-grid-2">
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-name-input">Facility Name <span class="req">*</span></label>
                                    <input type="text" id="fac-name-input" class="staff-input" placeholder="e.g. Apex Health System" required>
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-code-input">Facility Code <span class="req">*</span></label>
                                    <input type="text" id="fac-code-input" class="staff-input" placeholder="e.g. FAC-APX-01">
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-type-select">Facility Type <span class="req">*</span></label>
                                    <select id="fac-type-select" class="staff-select">
                                        <option value="Clinic" selected>Clinic</option>
                                        <option value="Hospital">Hospital</option>
                                        <option value="Specialty Center">Specialty Center</option>
                                        <option value="Outpatient Center">Outpatient Center</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-legal-name-input">Legal Entity Name</label>
                                    <input type="text" id="fac-legal-name-input" class="staff-input" placeholder="e.g. Apex Healthcare Network LLC">
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-tax-id-input">Tax ID / TIN / EIN</label>
                                    <input type="text" id="fac-tax-id-input" class="staff-input" placeholder="e.g. 12-3456789">
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-npi-input">Facility NPI (Billing)</label>
                                    <input type="text" id="fac-npi-input" class="staff-input" placeholder="10-digit NPI">
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-contact-person-input">Facility Contact Person / Director</label>
                                    <input type="text" id="fac-contact-person-input" class="staff-input" placeholder="e.g. Dr. Arthur Pendelton">
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-status-select">Status <span class="req">*</span></label>
                                    <select id="fac-status-select" class="staff-select">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Contact & Headquarters Address -->
                        <div class="staff-card-section">
                            <div class="staff-card-header">
                                <div class="staff-step-num-badge">2</div>
                                <div class="staff-card-header-text">
                                    <h3>Contact &amp; Headquarters Address</h3>
                                    <p>Official communication details and main physical address.</p>
                                </div>
                            </div>
                            <div class="staff-form-grid-2">
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-phone-input">Phone Number <span class="req">*</span></label>
                                    <input type="text" id="fac-phone-input" class="staff-input" placeholder="(555) 234-5678" required>
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-fax-input">Fax Number</label>
                                    <input type="text" id="fac-fax-input" class="staff-input" placeholder="(555) 234-5679">
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-email-input">Official Email <span class="req">*</span></label>
                                    <input type="email" id="fac-email-input" class="staff-input" placeholder="admin@apexhealth.org" required>
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-website-input">Website URL</label>
                                    <input type="url" id="fac-website-input" class="staff-input" placeholder="https://www.apexhealth.org">
                                </div>
                                <div class="staff-form-group span-2">
                                    <label class="staff-form-label" for="fac-address-input">Address Line 1 <span class="req">*</span></label>
                                    <input type="text" id="fac-address-input" class="staff-input" placeholder="100 Medical Center Parkway" required>
                                </div>
                                <div class="staff-form-group span-2">
                                    <label class="staff-form-label" for="fac-address-line2-input">Address Line 2 (Suite / Building)</label>
                                    <input type="text" id="fac-address-line2-input" class="staff-input" placeholder="Suite 400">
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-city-input">City <span class="req">*</span></label>
                                    <input type="text" id="fac-city-input" class="staff-input" placeholder="New York" required>
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-state-input">State / Province <span class="req">*</span></label>
                                    <input type="text" id="fac-state-input" class="staff-input" placeholder="NY" required>
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-zip-input">Postal / Zip Code <span class="req">*</span></label>
                                    <input type="text" id="fac-zip-input" class="staff-input" placeholder="10001" required>
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="fac-country-input">Country <span class="req">*</span></label>
                                    <input type="text" id="fac-country-input" class="staff-input" value="United States" required>
                                </div>
                                <div class="staff-form-group span-2">
                                    <label class="staff-form-label" for="fac-timezone-select">Time Zone <span class="req">*</span></label>
                                    <select id="fac-timezone-select" class="staff-select">
                                        <option value="America/New_York" selected>Eastern Time (US &amp; Canada) - America/New_York</option>
                                        <option value="America/Chicago">Central Time (US &amp; Canada) - America/Chicago</option>
                                        <option value="America/Denver">Mountain Time (US &amp; Canada) - America/Denver</option>
                                        <option value="America/Los_Angeles">Pacific Time (US &amp; Canada) - America/Los_Angeles</option>
                                        <option value="America/Phoenix">Arizona - America/Phoenix</option>
                                        <option value="America/Anchorage">Alaska - America/Anchorage</option>
                                        <option value="Pacific/Honolulu">Hawaii - Pacific/Honolulu</option>
                                        <option value="Asia/Kolkata">India Standard Time - Asia/Kolkata</option>
                                        <option value="UTC">UTC / Universal</option>
                                    </select>
                                </div>
                                <div class="staff-form-group span-2">
                                    <label class="staff-form-label" for="fac-desc-input">Description / Notes</label>
                                    <textarea id="fac-desc-input" class="staff-input" rows="3" placeholder="Brief summary of facility operations, network scope, and facilities..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Clinical Scope -->
                        <div class="staff-card-section">
                            <div class="staff-card-header">
                                <div class="staff-step-num-badge">3</div>
                                <div class="staff-card-header-text">
                                    <h3>Clinical Scope</h3>
                                    <p>Which specialties this facility practices. Onboarding and login are scoped to this list.</p>
                                </div>
                            </div>
                            <div class="perm-checkbox-list" id="fac-specialties-checklist" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                                <!-- Populated dynamically from /api/specialties -->
                            </div>
                            <p class="field-help-text" id="fac-specialties-error" style="color:#ef4444; display:none; margin-top:8px;">Select at least one clinical specialty this facility practices.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- end #admin-tab-facility -->

        <!-- ── TAB: User Management ── -->
        <div class="admin-tab-section" id="admin-tab-user-management">
            
            <!-- 1A: User Directory Table View -->
            <div id="user-management-directory-view">
                <div class="card mod-administration-style-5" id="user-management-card">
                    <div class="mod-administration-style-6">
                        <div>
                            <h2 class="mod-administration-style-2">User Management</h2>
                            <p style="color:#64748b;font-size:0.85rem;margin-top:2px;">Manage staff users, clinical providers, and administrators. Control roles and access levels.</p>
                        </div>
                        <button class="btn btn-primary mod-administration-style-7" id="add-user-btn">
                            <i class="fas fa-plus"></i> Add New User
                        </button>
                    </div>
                    <div class="table-container">
                        <table class="admin-users-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Specialty</th>
                                    <th>Facility</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="administration-users-list">
                                <tr>
                                    <td colspan="7">Loading users directory...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="administration-pagination"></div>
                </div>
            </div>

            <!-- 1B: Athenahealth 5-Step User Onboarding Stepper UI -->
            <div id="staff-user-workflow-view" class="athena-wizard-view" style="display: none;">
                
                <!-- Top Header with Back Button, Titles & Brand Accent -->
                <div class="athena-wizard-top-bar">
                    <div class="athena-wizard-header-row">
                        <div class="athena-wizard-title-area">
                            <button type="button" class="btn-back-to-directory" id="btn-back-to-users-dir" title="Back to User Directory">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <div class="athena-wizard-titles">
                                <h2 id="staff-form-main-title">Add New User</h2>
                                <p id="staff-form-main-sub">Create a new staff or provider account and assign roles and access.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 5-Step Stepper Navigation Trail -->
                    <div class="athena-stepper-trail" id="athena-stepper-trail">
                        <div class="athena-step-item active" data-step="1">
                            <div class="athena-step-circle">1</div>
                            <div class="athena-step-text">
                                <span class="athena-step-title">Personal Information</span>
                            </div>
                        </div>
                        <div class="athena-step-divider"></div>
                        <div class="athena-step-item" data-step="2">
                            <div class="athena-step-circle">2</div>
                            <div class="athena-step-text">
                                <span class="athena-step-title">Role &amp; Type</span>
                            </div>
                        </div>
                        <div class="athena-step-divider"></div>
                        <div class="athena-step-item" data-step="3">
                            <div class="athena-step-circle">3</div>
                            <div class="athena-step-text">
                                <span class="athena-step-title">Practice Assignment</span>
                            </div>
                        </div>
                        <div class="athena-step-divider"></div>
                        <div class="athena-step-item" data-step="4">
                            <div class="athena-step-circle">4</div>
                            <div class="athena-step-text">
                                <span class="athena-step-title">Access &amp; Permissions</span>
                            </div>
                        </div>
                        <div class="athena-step-divider"></div>
                        <div class="athena-step-item" data-step="5">
                            <div class="athena-step-circle">5</div>
                            <div class="athena-step-text">
                                <span class="athena-step-title">Review &amp; Create</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs -->
                <input type="hidden" id="staff-user-id" value="">
                <input type="hidden" id="staff-photo-url" value="">

                <!-- STEP 1 PANEL: Personal Information -->
                <div class="athena-wizard-step-panel" id="wizard-step-panel-1">
                    <div class="athena-card">
                        <h3 class="athena-step-heading">1. Personal Information</h3>
                        <p class="athena-step-subheading">Enter the basic details for the user.</p>
                        
                        <div class="athena-form-grid-2">
                            <div class="athena-form-group">
                                <label class="athena-label">First Name <span class="req">*</span></label>
                                <input type="text" id="staff-first-name" class="athena-input" placeholder="Sarah" required>
                            </div>
                            <div class="athena-form-group">
                                <label class="athena-label">Last Name <span class="req">*</span></label>
                                <input type="text" id="staff-last-name" class="athena-input" placeholder="Williams" required>
                            </div>
                            <div class="athena-form-group">
                                <label class="athena-label">Middle Name</label>
                                <input type="text" id="staff-middle-name" class="athena-input" placeholder="Enter middle name">
                            </div>

                            <div class="athena-form-group">
                                <label class="athena-label">Preferred Name</label>
                                <input type="text" id="staff-preferred-name" class="athena-input" placeholder="Enter preferred name">
                            </div>
                            <div class="athena-form-group">
                                <label class="athena-label">Status <span class="req">*</span></label>
                                <div class="athena-radio-group">
                                    <label class="athena-radio-label"><input type="radio" name="staff-status-radio" value="Active" checked> Active</label>
                                    <label class="athena-radio-label"><input type="radio" name="staff-status-radio" value="Inactive"> Inactive</label>
                                </div>
                            </div>
                            <div class="athena-form-group">
                                <label class="athena-label">Email Address <span class="req">*</span></label>
                                <input type="email" id="staff-email" class="athena-input" placeholder="sarah.williams@westsidehealth.com" required>
                            </div>
                            <div class="athena-form-group">
                                <label class="athena-label">Hire Date</label>
                                <input type="date" id="staff-hire-date" class="athena-input">
                            </div>
                            <div class="athena-form-group">
                                <label class="athena-label">Mobile Phone</label>
                                <input type="text" id="staff-mobile-phone" class="athena-input" placeholder="(555) 123-4567">
                            </div>
                            <div class="athena-form-group">
                                <label class="athena-label">Termination Date</label>
                                <input type="date" id="staff-termination-date" class="athena-input">
                            </div>
                            <div class="athena-form-group">
                                <label class="athena-label">Work Phone</label>
                                <input type="text" id="staff-work-phone" class="athena-input" placeholder="(555) 987-6543">
                            </div>
                            <div class="athena-form-group span-2">
                                <label class="athena-label">Notes</label>
                                <input type="text" id="staff-notes" class="athena-input" placeholder="Enter any additional notes (optional)">
                            </div>
                        </div>

                        <div class="athena-wizard-footer">
                            <button type="button" class="btn-athena-secondary" id="btn-cancel-staff-form">Cancel</button>
                            <div class="footer-actions-right">
                                <button type="button" class="btn-athena-primary" id="btn-step-1-next">Next</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2 PANEL: Role & Type -->
                <div class="athena-wizard-step-panel" id="wizard-step-panel-2" style="display: none;">
                    <div class="athena-card">
                        <h3 class="athena-step-heading">2. Role &amp; Type</h3>
                        <p class="athena-step-subheading">Select the user type and role to define their access and responsibilities.</p>
                        
                        <label class="athena-label mb-12">User Type <span class="req">*</span></label>
                        <div class="athena-user-type-cards-grid">
                            <div class="athena-user-type-card selected" data-user-type="Staff Member">
                                <div class="user-type-card-icon"><i class="fas fa-users"></i></div>
                                <h4>Staff Member</h4>
                                <p>Non-clinical or clinical staff (e.g., nurse, MA, front desk, billing).</p>
                            </div>
                            <div class="athena-user-type-card" data-user-type="Provider / Clinician">
                                <div class="user-type-card-icon"><i class="fas fa-user-md"></i></div>
                                <h4>Provider / Clinician</h4>
                                <p>Physicians, nurse practitioners, physician assistants, etc.</p>
                            </div>
                            <div class="athena-user-type-card" data-user-type="Administrator">
                                <div class="user-type-card-icon"><i class="fas fa-user-shield"></i></div>
                                <h4>Administrator</h4>
                                <p>Full system access including user management, practice settings, and reports.</p>
                            </div>
                        </div>

                        <div class="athena-form-grid-2 mt-20">
                            <div class="athena-form-group span-2">
                                <label class="athena-label">Job Role <span class="req">*</span></label>
                                <select id="staff-primary-role" class="athena-select">
                                    <option value="">— Select a User Type first —</option>
                                </select>
                            </div>
                            <div class="athena-form-group span-2">
                                <label class="athena-label">Department / Specialty</label>
                                <select id="staff-department" class="athena-select">
                                    <option value="Cardiology">Cardiology</option>
                                    <option value="Internal Medicine">Internal Medicine</option>
                                    <option value="Family Practice">Family Practice</option>
                                    <option value="Administration">Administration</option>
                                </select>
                                <p class="field-help-text">Select the primary department or specialty this user will work with.</p>
                            </div>
                            <div class="athena-form-group span-2">
                                <label class="athena-label">Employment Type</label>
                                <div class="athena-radio-group">
                                    <label class="athena-radio-label"><input type="radio" name="staff-employment-type" value="Full-time" checked> Full-time</label>
                                    <label class="athena-radio-label"><input type="radio" name="staff-employment-type" value="Part-time"> Part-time</label>
                                    <label class="athena-radio-label"><input type="radio" name="staff-employment-type" value="Contract"> Contract</label>
                                    <label class="athena-radio-label"><input type="radio" name="staff-employment-type" value="Other"> Other</label>
                                </div>
                            </div>
                            <div class="athena-form-group">
                                <label class="athena-label">Job Title</label>
                                <input type="text" id="staff-job-title" class="athena-input" placeholder="e.g. Medical Assistant">
                            </div>
                            <div class="athena-form-group">
                                <label class="athena-label">Suffix / Credentials</label>
                                <select id="staff-suffix" class="athena-select">
                                    <option value="">Select (e.g., MD, DO, NP, PA, RN)</option>
                                    <option value="MD">MD</option>
                                    <option value="DO">DO</option>
                                    <option value="NP">NP</option>
                                    <option value="PA">PA</option>
                                    <option value="RN">RN</option>
                                    <option value="DNP">DNP</option>
                                    <option value="PharmD">PharmD</option>
                                    <option value="DPM">DPM</option>
                                    <option value="LCSW">LCSW</option>
                                </select>
                            </div>
                        </div>

                        <!-- Provider Credentials Panel (Only visible for Provider / Clinician) -->
                        <div id="provider-credentials-panel" class="athena-generated-creds-section" style="display: none; margin-top: 20px;">
                            <div class="generated-creds-header">
                                <i class="fas fa-id-card"></i> Provider Clinical Credentials &amp; Regulatory IDs
                            </div>
                            <div class="athena-form-grid-2">
                                <div class="athena-form-group">
                                    <label class="athena-label">Individual NPI (10-Digit) <span class="req">*</span></label>
                                    <input type="text" id="staff-provider-npi" class="athena-input" placeholder="e.g. 1982736450" maxlength="10">
                                </div>
                                <div class="athena-form-group">
                                    <label class="athena-label">DEA Registration Number</label>
                                    <input type="text" id="staff-provider-dea" class="athena-input" placeholder="e.g. AB1234567">
                                </div>
                                <div class="athena-form-group">
                                    <label class="athena-label">Healthcare Taxonomy Code</label>
                                    <input type="text" id="staff-provider-taxonomy" class="athena-input" placeholder="e.g. 207Q00000X">
                                </div>
                                <div class="athena-form-group">
                                    <label class="athena-label">Provider Classification / Type</label>
                                    <select id="staff-provider-type" class="athena-select">
                                        <option value="Physician (MD)">Physician (MD)</option>
                                        <option value="Physician (DO)">Physician (DO)</option>
                                        <option value="Nurse Practitioner (NP)">Nurse Practitioner (NP)</option>
                                        <option value="Physician Assistant (PA)">Physician Assistant (PA)</option>
                                        <option value="Doctor of Podiatric Medicine (DPM)">Doctor of Podiatric Medicine (DPM)</option>
                                        <option value="Clinical Psychologist">Clinical Psychologist</option>
                                        <option value="Licensed Clinical Social Worker (LCSW)">Licensed Clinical Social Worker (LCSW)</option>
                                        <option value="Other Clinician">Other Clinician</option>
                                    </select>
                                </div>
                            </div>

                            <!-- State Medical License Numbers (a provider can hold licenses in multiple states) -->
                            <div class="athena-form-group" style="margin-top: 4px;">
                                <label class="athena-label">State Medical License Numbers</label>
                                <div id="staff-license-list"></div>
                                <button type="button" class="btn-athena-secondary" id="btn-add-state-license" style="margin-top: 8px;">
                                    <i class="fas fa-plus"></i> Add Another State License
                                </button>
                            </div>
                        </div>

                        <!-- Generated Credentials Section -->
                        <div class="athena-generated-creds-section mt-24">
                            <div class="generated-creds-header">
                                <i class="fas fa-key"></i>
                                <span>Account Credentials</span>
                                <span class="generated-creds-badge">Set by administrator</span>
                            </div>
                            <div class="athena-form-grid-2">
                                <div class="athena-form-group">
                                    <label class="athena-label">Employee ID</label>
                                    <div class="athena-input-with-action">
                                        <input type="text" id="staff-employee-id" class="athena-input" placeholder="Select a role first" readonly>
                                        <button type="button" class="btn-regen" id="btn-regen-empid" title="Regenerate Employee ID" disabled>
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="athena-form-group">
                                    <label class="athena-label">Login Username <span class="req">*</span></label>
                                    <div class="athena-input-with-action">
                                        <input type="text" id="staff-username" class="athena-input" placeholder="Select a role first">
                                        <button type="button" class="btn-regen" id="btn-regen-username" title="Regenerate Username" disabled>
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                    <p class="field-help-text">Auto-suggested from name &amp; role. You can edit it.</p>
                                </div>
                                <div class="athena-form-group">
                                    <label class="athena-label" id="staff-password-label">Initial Password <span class="req">*</span></label>
                                    <div class="athena-input-with-action">
                                        <input type="password" id="staff-password" class="athena-input" placeholder="Set a password for this user" autocomplete="new-password">
                                        <button type="button" class="btn-regen" id="btn-gen-password" title="Generate Secure Password">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                    <p class="field-help-text" id="staff-password-help">Minimum 8 characters. This will be emailed to the user, who must change it on first login.</p>
                                </div>
                                <div class="athena-form-group">
                                    <label class="athena-label" id="staff-confirm-password-label">Confirm Password <span class="req">*</span></label>
                                    <input type="password" id="staff-confirm-password" class="athena-input" placeholder="Re-enter the password" autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <div class="athena-wizard-footer">
                            <button type="button" class="btn-athena-secondary" id="btn-step-2-back">Back</button>
                            <div class="footer-actions-right">
                                <button type="button" class="btn-athena-primary" id="btn-step-2-next">Next</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3 PANEL: Practice Assignment -->
                <div class="athena-wizard-step-panel" id="wizard-step-panel-3" style="display: none;">
                    <div class="athena-card">
                        <h3 class="athena-step-heading">3. Facility Assignment</h3>
                        <p class="athena-step-subheading">Assign parent enterprise facility and specialty.</p>

                        <div class="athena-form-grid-2">
                            <div class="athena-form-group span-2">
                                <label class="athena-label">Assigned Enterprise Facility <span class="req">*</span></label>
                                <select id="staff-facility-id" class="athena-select" required>
                                    <option value="">— Select Facility —</option>
                                </select>
                                <p class="field-help-text">Select the enterprise healthcare facility where this user operates.</p>
                            </div>

                            <div class="athena-form-group span-2">
                                <label class="athena-label">Primary Specialty <span class="req">*</span></label>
                                <select id="staff-specialty" class="athena-select">
                                    <option value="">— Select Primary Specialty —</option>
                                </select>
                            </div>

                            <div class="athena-form-group span-2">
                                <label class="athena-label">Supervising Provider (Optional)</label>
                                <select id="staff-supervising-provider" class="athena-select">
                                    <option value="">— None / Self-Supervised —</option>
                                </select>
                                <p class="field-help-text">Assign a supervising physician if applicable (e.g. for NP, PA, or medical staff).</p>
                            </div>

                            <div class="athena-form-group">
                                <label class="athena-label">Schedule Access</label>
                                <div class="athena-radio-group">
                                    <label class="athena-radio-label"><input type="radio" name="staff-schedule-access" value="All assigned locations" checked> All assigned locations</label>
                                    <label class="athena-radio-label"><input type="radio" name="staff-schedule-access" value="Specific locations only"> Specific locations only</label>
                                </div>
                            </div>

                            <div class="athena-form-group">
                                <label class="athena-label">Default Time Zone</label>
                                <select id="staff-timezone" class="athena-select">
                                    <option value="America/New_York" selected>Eastern Time (US &amp; Canada)</option>
                                    <option value="America/Chicago">Central Time (US &amp; Canada)</option>
                                    <option value="America/Denver">Mountain Time (US &amp; Canada)</option>
                                    <option value="America/Los_Angeles">Pacific Time (US &amp; Canada)</option>
                                    <option value="Asia/Kolkata">IST (Asia/Kolkata)</option>
                                    <option value="UTC">UTC Standard</option>
                                </select>
                            </div>
                        </div>

                        <div class="athena-wizard-footer">
                            <button type="button" class="btn-athena-secondary" id="btn-step-3-back">Back</button>
                            <div class="footer-actions-right">
                                <button type="button" class="btn-athena-primary" id="btn-step-3-next">Next</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4 PANEL: Access & Permissions -->
                <div class="athena-wizard-step-panel" id="wizard-step-panel-4" style="display: none;">
                    <div class="athena-card">
                        <div class="athena-step-4-header">
                            <div>
                                <h3 class="athena-step-heading">4. Access &amp; Permissions</h3>
                                <p class="athena-step-subheading">Configure what this user can access in the system based on their role and responsibilities.</p>
                            </div>
                        </div>

                        <!-- Role Template Cards Grid -->
                        <div class="athena-role-templates-grid mb-24" id="athena-role-templates-container">
                            <div class="athena-template-card selected" data-template="Medical Assistant">
                                <div class="template-card-icon"><i class="fas fa-stethoscope"></i></div>
                                <h4>Medical Assistant</h4>
                                <p>Patient intake, documentation, orders, etc.</p>
                            </div>
                            <div class="athena-template-card" data-template="Front Desk">
                                <div class="template-card-icon"><i class="fas fa-user-clock"></i></div>
                                <h4>Front Desk</h4>
                                <p>Scheduling, registration, check-in/out.</p>
                            </div>
                            <div class="athena-template-card" data-template="Billing Staff">
                                <div class="template-card-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                                <h4>Billing Staff</h4>
                                <p>Claims, payments, billing reports.</p>
                            </div>
                            <div class="athena-template-card" data-template="Practice Manager">
                                <div class="template-card-icon"><i class="fas fa-briefcase"></i></div>
                                <h4>Practice Manager</h4>
                                <p>Operational access, reports.</p>
                            </div>
                        </div>

                        <!-- Sub-Tabs for Permissions Category -->
                        <div class="athena-permissions-tabs">
                            <button type="button" class="athena-perm-tab active" data-perm-tab="module-permissions">Module Permissions</button>
                            <button type="button" class="athena-perm-tab" data-perm-tab="data-access">Data Access</button>
                            <button type="button" class="athena-perm-tab" data-perm-tab="administrative">Administrative</button>
                            <button type="button" class="athena-perm-tab" data-perm-tab="other-settings">Other Settings</button>
                        </div>

                        <!-- Sub-Tab 1: Module Permissions Panel -->
                        <div class="athena-permissions-category-grid athena-perm-tab-panel mt-20" id="perm-tab-panel-module-permissions">
                            <!-- Category 1: Patient Management -->
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-user-cog"></i> Patient Management</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="View patient demographics" checked> View patient demographics</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Register new patients" checked> Register new patients</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Update patient information" checked> Update patient information</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Merge duplicate patients"> Merge duplicate patients</label>
                                </div>
                            </div>

                            <!-- Category 2: Clinical -->
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-notes-medical"></i> Clinical</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="View clinical notes" checked> View clinical notes</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Add documentation" checked> Add documentation</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Record vitals" checked> Record vitals</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Place orders" checked> Place orders</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Manage medications"> Manage medications</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="View lab results" checked> View lab results</label>
                                </div>
                            </div>

                            <!-- Category 3: Scheduling -->
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-calendar-check"></i> Scheduling</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="View schedule" checked> View schedule</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Create appointments" checked> Create appointments</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Reschedule / Cancel" checked> Reschedule / Cancel</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Block time"> Block time</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Manage provider schedules"> Manage provider schedules</label>
                                </div>
                            </div>

                            <!-- Category 4: Billing & Claims -->
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-file-invoice-dollar"></i> Billing &amp; Claims</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="View billing information"> View billing information</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Create claims"> Create claims</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Post payments"> Post payments</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="View patient balances"> View patient balances</label>
                                </div>
                            </div>

                            <!-- Category 5: Reports -->
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-chart-line"></i> Reports</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="View standard reports"> View standard reports</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Export reports"> Export reports</label>
                                </div>
                            </div>

                            <!-- Category 6: Practice Administration -->
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-cog"></i> Practice Administration</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Manage users"> Manage users</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Manage practice settings"> Manage practice settings</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="View audit logs"> View audit logs</label>
                                </div>
                            </div>
                        </div>

                        <!-- Sub-Tab 2: Data Access Scope Panel -->
                        <div class="athena-permissions-category-grid athena-perm-tab-panel mt-20" id="perm-tab-panel-data-access" style="display: none;">
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-building"></i> Location &amp; Org Scope</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Access all locations in practice" checked> Access all locations in practice</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Restrict to assigned location only"> Restrict to assigned location only</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Access telehealth virtual clinics" checked> Access telehealth virtual clinics</label>
                                </div>
                            </div>
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-lock"></i> Patient Record Access Scope</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Access all practice patient charts" checked> Access all practice patient charts</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Access assigned provider patients only"> Access assigned provider patients only</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Access VIP / Confidential charts"> Access VIP / Confidential charts</label>
                                </div>
                            </div>
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-user-shield"></i> PHI &amp; Sensitive Data Protection</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Mask Social Security Numbers" checked> Mask Social Security Numbers</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Mask financial billing details"> Mask financial billing details</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Export PHI to Excel / CSV" checked> Export PHI to Excel / CSV</label>
                                </div>
                            </div>
                        </div>

                        <!-- Sub-Tab 3: Administrative Rights Panel -->
                        <div class="athena-permissions-category-grid athena-perm-tab-panel mt-20" id="perm-tab-panel-administrative" style="display: none;">
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-user-plus"></i> Staff &amp; User Management</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Create & invite new staff users"> Create &amp; invite new staff users</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Edit user roles & security levels"> Edit user roles &amp; security levels</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Reset staff passwords & MFA"> Reset staff passwords &amp; MFA</label>
                                </div>
                            </div>
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-sliders-h"></i> Practice Setup &amp; Config</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Configure clinical templates & forms"> Configure clinical templates &amp; forms</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Manage fee schedules & CPT codes"> Manage fee schedules &amp; CPT codes</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Manage lab & pharmacy integrations"> Manage lab &amp; pharmacy integrations</label>
                                </div>
                            </div>
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-file-contract"></i> Compliance &amp; Audit Logs</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="View system audit logs"> View system audit logs</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Export HIPAA audit reports"> Export HIPAA audit reports</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Manage security policies & RBAC matrix"> Manage security policies &amp; RBAC matrix</label>
                                </div>
                            </div>
                        </div>

                        <!-- Sub-Tab 4: Other Settings & Features Panel -->
                        <div class="athena-permissions-category-grid athena-perm-tab-panel mt-20" id="perm-tab-panel-other-settings" style="display: none; grid-template-columns: repeat(2, 1fr);">
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-comments"></i> Communication &amp; Messaging</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Broadcast practice announcements" checked> Broadcast practice announcements</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Send direct secure staff messages" checked> Send direct secure staff messages</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Send patient SMS & Email reminders" checked> Send patient SMS &amp; Email reminders</label>
                                </div>
                            </div>
                            <div class="athena-perm-category-box">
                                <div class="perm-category-title"><i class="fas fa-video"></i> Telehealth &amp; Virtual Visits</div>
                                <div class="perm-checkbox-list">
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Host video consultations" checked> Host video consultations</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Record virtual patient sessions"> Record virtual patient sessions</label>
                                    <label class="athena-checkbox-item"><input type="checkbox" class="staff-perm-cb" value="Share screen & digital whiteboard" checked> Share screen &amp; digital whiteboard</label>
                                </div>
                            </div>
                        </div>

                        <!-- Save as Reusable Role Template Option -->
                        <div class="athena-save-as-role-box mt-20" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px;">
                            <label class="athena-checkbox-item" style="font-weight: 700; color: #1e293b; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" id="chk-save-as-new-custom-role"> Save these permissions as a new reusable Role Template
                            </label>
                            <div id="save-new-custom-role-fields" style="display: none; margin-top: 14px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                                    <div>
                                        <label class="athena-label">Template Name <span class="req">*</span></label>
                                        <input type="text" id="new-custom-role-name" class="athena-input" placeholder="e.g., Triage Coordinator / Clinical Scribe">
                                    </div>
                                    <div>
                                        <label class="athena-label">Description</label>
                                        <input type="text" id="new-custom-role-desc" class="athena-input" placeholder="Brief summary of duties and access">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="athena-wizard-footer">
                            <button type="button" class="btn-athena-secondary" id="btn-step-4-back">Back</button>
                            <div class="footer-actions-right">
                                <button type="button" class="btn-athena-primary" id="btn-step-4-next">Next</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 5 PANEL: Review & Create -->
                <div class="athena-wizard-step-panel" id="wizard-step-panel-5" style="display: none;">
                    <div class="athena-card">
                        <h3 class="athena-step-heading">5. Review &amp; Create</h3>
                        <p class="athena-step-subheading">Review the user details before creating the account. You can edit any section if needed.</p>

                        <div class="athena-review-layout-grid">
                            <!-- Left Column: Summary Sections -->
                            <div class="athena-review-main-col">
                                
                                <!-- Review Section 1: Personal Information -->
                                <div class="athena-review-card">
                                    <div class="athena-review-card-header">
                                        <div class="review-header-title"><i class="fas fa-user"></i> Personal Information</div>
                                        <button type="button" class="btn-athena-edit-step" data-jump-step="1"><i class="fas fa-pen"></i> Edit</button>
                                    </div>
                                    <div class="athena-review-fields-grid">
                                        <div class="review-field-item">
                                            <span class="review-field-label">Name</span>
                                            <span class="review-field-value" id="rev-user-name">Sarah Williams, RN</span>
                                        </div>
                                        <div class="review-field-item">
                                            <span class="review-field-label">Phone</span>
                                            <span class="review-field-value" id="rev-user-phone">(555) 123-4567</span>
                                        </div>
                                        <div class="review-field-item">
                                            <span class="review-field-label">Email</span>
                                            <span class="review-field-value text-accent" id="rev-user-email">sarah.williams@westsidehealth.com</span>
                                        </div>
                                        <div class="review-field-item">
                                            <span class="review-field-label">Employee ID</span>
                                            <span class="review-field-value" id="rev-user-empid">EMP-1024</span>
                                        </div>
                                        <div class="review-field-item">
                                            <span class="review-field-label">Login Username</span>
                                            <span class="review-field-value" id="rev-user-username">—</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Review Section 2: Role & Type -->
                                <div class="athena-review-card">
                                    <div class="athena-review-card-header">
                                        <div class="review-header-title"><i class="fas fa-briefcase"></i> Role &amp; Type</div>
                                        <button type="button" class="btn-athena-edit-step" data-jump-step="2"><i class="fas fa-pen"></i> Edit</button>
                                    </div>
                                    <div class="athena-review-fields-grid">
                                        <div class="review-field-item">
                                            <span class="review-field-label">User Type</span>
                                            <span class="review-field-value" id="rev-user-type">Staff Member</span>
                                        </div>
                                        <div class="review-field-item">
                                            <span class="review-field-label">Job Role</span>
                                            <span class="review-field-value" id="rev-user-job-role">Medical Assistant</span>
                                        </div>
                                        <div class="review-field-item">
                                            <span class="review-field-label">Job Title</span>
                                            <span class="review-field-value" id="rev-user-job-title">—</span>
                                        </div>
                                        <div class="review-field-item">
                                            <span class="review-field-label">Suffix / Credentials</span>
                                            <span class="review-field-value" id="rev-user-suffix">—</span>
                                        </div>
                                        <div class="review-field-item">
                                            <span class="review-field-label">Employment Type</span>
                                            <span class="review-field-value" id="rev-user-employment">Full-time</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Review Section 3: Facility Assignment -->
                                <div class="athena-review-card">
                                    <div class="athena-review-card-header">
                                        <div class="review-header-title"><i class="fas fa-hospital"></i> Facility Assignment</div>
                                        <button type="button" class="btn-athena-edit-step" data-jump-step="3"><i class="fas fa-pen"></i> Edit</button>
                                    </div>
                                    <div class="athena-review-fields-grid">
                                        <div class="review-field-item">
                                            <span class="review-field-label">Facility</span>
                                            <span class="review-field-value" id="rev-user-location">—</span>
                                        </div>
                                        <div class="review-field-item">
                                            <span class="review-field-label">Department</span>
                                            <span class="review-field-value" id="rev-user-dept">Cardiology</span>
                                        </div>
                                        <div class="review-field-item span-2">
                                            <span class="review-field-label">Specialties</span>
                                            <span class="review-field-value" id="rev-user-specialties">Cardiology, Internal Medicine</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Review Section 4: Access & Permissions -->
                                <div class="athena-review-card">
                                    <div class="athena-review-card-header">
                                        <div class="review-header-title"><i class="fas fa-shield-alt"></i> Access &amp; Permissions</div>
                                        <button type="button" class="btn-athena-edit-step" data-jump-step="4"><i class="fas fa-pen"></i> Edit</button>
                                    </div>
                                    <div class="athena-review-fields-grid">
                                        <div class="review-field-item">
                                            <span class="review-field-label">Role Template</span>
                                            <span class="review-field-value" id="rev-user-template">Medical Assistant</span>
                                        </div>
                                        <div class="review-field-item">
                                            <span class="review-field-label">Modules</span>
                                            <span class="review-field-value" id="rev-user-modules">Patient Management, Clinical, Scheduling</span>
                                        </div>
                                        <div class="review-field-item span-2">
                                            <span class="review-field-label">Additional Permissions</span>
                                            <span class="review-field-value" id="rev-user-perms">View lab results, Create appointments</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Right Sidebar: Invitation & Steps -->
                            <div class="athena-review-side-col">
                                
                                <!-- All Set Banner -->
                                <div class="athena-all-set-card">
                                    <div class="all-set-icon"><i class="fas fa-check-circle"></i></div>
                                    <div>
                                        <h4>All Set!</h4>
                                        <p>Review the information and create the account. Login credentials can be emailed to the user below.</p>
                                    </div>
                                </div>

                                <!-- Credentials Delivery Settings Box -->
                                <div class="athena-invitation-box">
                                    <div class="invitation-box-header">
                                        <div class="invitation-title"><i class="fas fa-envelope"></i> Credentials Delivery</div>
                                    </div>
                                    <div class="invitation-toggle-row">
                                        <span class="toggle-label">Email Login Credentials Now</span>
                                        <label class="staff-toggle-switch">
                                            <input type="checkbox" id="staff-invite-email-toggle" checked>
                                            <span class="staff-toggle-slider"></span>
                                        </label>
                                    </div>
                                    <p class="field-help-text">The username and password set in Step 2 will be emailed to the user. Turn this off to save the account without emailing credentials yet.</p>
                                </div>

                                <!-- What Happens Next Checklist -->
                                <div class="athena-next-steps-box">
                                    <h4><i class="fas fa-info-circle"></i> What happens next?</h4>
                                    <ol class="next-steps-list">
                                        <li><span class="num">1</span> <span class="txt">The user receives their username and password by email — ask them to check Spam/Junk if it doesn't arrive within a few minutes.</span></li>
                                        <li><span class="num">2</span> <span class="txt">They log in and are required to set their own new password before doing anything else.</span></li>
                                        <li><span class="num">3</span> <span class="txt">The temporary password expires after 72 hours if never used — resend it from the Users list if needed.</span></li>
                                    </ol>
                                </div>

                            </div>
                        </div>

                        <div class="athena-wizard-footer">
                            <button type="button" class="btn-athena-secondary" id="btn-step-5-back">Back</button>
                            <div class="footer-actions-right">
                                <button type="button" class="btn-athena-submit" id="btn-save-staff-user">
                                    <i class="fas fa-paper-plane"></i> <span id="staff-save-btn-text">Create User &amp; Send Credentials</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── TAB 3: Roles & Permissions ── -->
        <div class="admin-tab-section" id="admin-tab-roles-permissions">
            <div class="card mod-administration-style-5" style="margin-top: 0;">
                <div class="mod-administration-style-6" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
                    <div>
                        <h2 class="mod-administration-style-2">Roles &amp; Permissions</h2>
                        <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: #64748b;">Configure granular sidebar menu &amp; submenu CRUD permissions and role policy templates across the system.</p>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="button" class="btn btn-outline" id="btn-add-new-role" style="background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7; font-weight: 700; padding: 8px 16px; border-radius: 6px; font-size: 0.88rem; cursor: pointer; transition: all 0.2s ease;">
                            <i class="fas fa-plus" style="margin-right: 4px;"></i> Add Custom Role
                        </button>
                        <button class="btn btn-primary mod-administration-style-7" id="save-rbac-policies-btn" style="padding: 8px 18px; font-size: 0.88rem; font-weight: 700; border-radius: 6px;">
                            Save RBAC Policies
                        </button>
                    </div>
                </div>

                <!-- Role Selector Pills -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Select Role to Configure</label>
                    <div class="rbac-role-pill-list" id="rbac-role-pill-container">
                        <button type="button" class="rbac-role-pill active" data-role="Super Admin">Super Admin</button>
                        <button type="button" class="rbac-role-pill" data-role="Doctor">Doctor</button>
                        <button type="button" class="rbac-role-pill" data-role="Nurse">Nurse</button>
                        <button type="button" class="rbac-role-pill" data-role="Therapist">Therapist</button>
                        <button type="button" class="rbac-role-pill" data-role="Receptionist">Receptionist</button>
                        <button type="button" class="rbac-role-pill" data-role="Billing Staff">Billing Staff</button>
                    </div>
                </div>

                <!-- Role Identity Fields -->
                <div class="rbac-crud-container" style="margin-bottom: 20px; padding: 16px 18px;">
                    <input type="hidden" id="customrole-id" value="">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;">
                        <div class="athena-form-group">
                            <label class="athena-label">Role Name <span class="req">*</span></label>
                            <input type="text" id="customrole-name" class="athena-input" placeholder="e.g., Clinical Scribe">
                        </div>
                        <div class="athena-form-group">
                            <label class="athena-label">Base System Role <span class="req">*</span></label>
                            <select id="customrole-base-role" class="athena-select">
                                <option value="Super Admin">Super Admin</option>
                                <option value="Doctor">Doctor</option>
                                <option value="Therapist">Therapist</option>
                                <option value="Nurse">Nurse</option>
                                <option value="Receptionist">Receptionist</option>
                                <option value="Billing Staff">Billing Staff</option>
                            </select>
                        </div>
                        <div class="athena-form-group">
                            <label class="athena-label">Role Type <span class="req">*</span></label>
                            <select id="customrole-role-type" class="athena-select">
                                <option value="security">Security Role (sidebar access only)</option>
                                <option value="template">Permission Template (onboarding preset only)</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                        <div class="athena-form-group">
                            <label class="athena-label">Description</label>
                            <input type="text" id="customrole-description" class="athena-input" placeholder="Brief summary of duties and responsibilities">
                        </div>
                        <div class="athena-form-group">
                            <label class="athena-label">Icon</label>
                            <select id="customrole-icon" class="athena-select">
                                <option value="fas fa-id-badge">🪪 ID Badge (General Staff)</option>
                                <option value="fas fa-stethoscope">🩺 Stethoscope (Clinical)</option>
                                <option value="fas fa-user-clock">👤 User Clock (Front Desk)</option>
                                <option value="fas fa-file-invoice-dollar">💳 Billing / Dollar</option>
                                <option value="fas fa-briefcase">💼 Briefcase (Manager)</option>
                                <option value="fas fa-user-md">👨‍⚕️ Doctor / Clinician</option>
                                <option value="fas fa-user-shield">🛡️ Shield (Admin)</option>
                                <option value="fas fa-user-nurse">👩‍⚕️ Nurse</option>
                            </select>
                        </div>
                    </div>
                    <p class="field-help-text" style="font-size: 0.78rem; color: #64748b; margin-top: 10px;" id="customrole-system-note"></p>
                </div>

                <!-- Active Role CRUD Matrix Card -->
                <div class="rbac-crud-container" style="margin-bottom: 28px;">
                    <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #ffffff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <div style="font-size: 0.96rem; font-weight: 800; color: #0f172a;">Role Permissions: <span id="rbac-active-role-title" style="color: #0284c7;">Super Admin</span></div>
                            <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">Configure sidebar menu visibility (View) and module operations (Create, Edit, Delete).</div>
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <button type="button" class="btn btn-outline" id="btn-rbac-select-all" style="padding: 5px 12px; font-size: 0.8rem; font-weight: 600; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;">Select All</button>
                            <button type="button" class="btn btn-outline" id="btn-rbac-clear-all" style="padding: 5px 12px; font-size: 0.8rem; font-weight: 600; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;">Clear All</button>
                            <button type="button" class="btn btn-outline" id="btn-rbac-delete-role" style="display: none; padding: 5px 12px; font-size: 0.8rem; font-weight: 600; background: #ffffff; border: 1px solid #ef4444; color: #ef4444; border-radius: 6px; cursor: pointer;">Delete Role</button>
                        </div>
                    </div>

                    <!-- Toolbar with Search and Tree Toggle -->
                    <div class="rbac-table-toolbar">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="text" id="rbac-matrix-search" class="rbac-search-input" placeholder="Search menu or module...">
                            <button type="button" class="btn btn-outline" id="btn-rbac-toggle-all-submenus" style="padding: 5px 10px; font-size: 0.78rem; font-weight: 600; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;">
                                <i class="fas fa-folder-open" style="margin-right: 4px; color: #0284c7;"></i> Expand Submenus
                            </button>
                        </div>
                    </div>

                    <table class="rbac-crud-table">
                        <thead>
                            <tr>
                                <th>Module / Submenu</th>
                                <th class="center-col">View</th>
                                <th class="center-col">Create</th>
                                <th class="center-col">Edit</th>
                                <th class="center-col">Delete</th>
                            </tr>
                        </thead>
                        <tbody id="rbac-role-matrix-tbody">
                            <!-- Populated dynamically with tree structure -->
                        </tbody>
                    </table>

                    <!-- Matrix Pagination Bar -->
                    <div class="rbac-pagination-bar" id="rbac-matrix-pagination">
                        <div class="rbac-pagination-info" id="rbac-pagination-info">Showing 1 to 11 menus</div>
                        <div class="rbac-pagination-pages" id="rbac-pagination-pages">
                            <!-- Dynamic page buttons -->
                        </div>
                    </div>
                </div>

                <!-- Feature Permission Presets (used by the Staff Onboarding wizard's Role Template cards) -->
                <div class="rbac-crud-container" style="margin-bottom: 28px; padding: 16px 18px;">
                    <div style="font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Feature Permission Presets</div>
                    <div class="athena-permissions-tabs">
                        <button type="button" class="customrole-perm-tab active" data-perm-tab="module-permissions">Module Permissions</button>
                        <button type="button" class="customrole-perm-tab" data-perm-tab="data-access">Data Access</button>
                        <button type="button" class="customrole-perm-tab" data-perm-tab="administrative">Administrative</button>
                        <button type="button" class="customrole-perm-tab" data-perm-tab="other-settings">Other Settings</button>
                    </div>

                    <div class="athena-permissions-category-grid customrole-perm-tab-panel mt-20" id="customrole-perm-tab-panel-module-permissions">
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-user-cog"></i> Patient Management</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="View patient demographics"> View patient demographics</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Register new patients"> Register new patients</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Update patient information"> Update patient information</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Merge duplicate patients"> Merge duplicate patients</label>
                            </div>
                        </div>
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-notes-medical"></i> Clinical</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="View clinical notes"> View clinical notes</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Add documentation"> Add documentation</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Record vitals"> Record vitals</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Place orders"> Place orders</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Manage medications"> Manage medications</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="View lab results"> View lab results</label>
                            </div>
                        </div>
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-calendar-check"></i> Scheduling</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="View schedule"> View schedule</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Create appointments"> Create appointments</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Reschedule / Cancel"> Reschedule / Cancel</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Block time"> Block time</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Manage provider schedules"> Manage provider schedules</label>
                            </div>
                        </div>
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-file-invoice-dollar"></i> Billing &amp; Claims</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="View billing information"> View billing information</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Create claims"> Create claims</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Post payments"> Post payments</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="View patient balances"> View patient balances</label>
                            </div>
                        </div>
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-chart-line"></i> Reports</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="View standard reports"> View standard reports</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Export reports"> Export reports</label>
                            </div>
                        </div>
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-cog"></i> Practice Administration</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Manage users"> Manage users</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Manage practice settings"> Manage practice settings</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="View audit logs"> View audit logs</label>
                            </div>
                        </div>
                    </div>

                    <div class="athena-permissions-category-grid customrole-perm-tab-panel mt-20" id="customrole-perm-tab-panel-data-access" style="display: none;">
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-building"></i> Location &amp; Org Scope</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Access all locations in practice"> Access all locations in practice</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Restrict to assigned location only"> Restrict to assigned location only</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Access telehealth virtual clinics"> Access telehealth virtual clinics</label>
                            </div>
                        </div>
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-lock"></i> Patient Record Access Scope</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Access all practice patient charts"> Access all practice patient charts</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Access assigned provider patients only"> Access assigned provider patients only</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Access VIP / Confidential charts"> Access VIP / Confidential charts</label>
                            </div>
                        </div>
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-user-shield"></i> PHI &amp; Sensitive Data Protection</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Mask Social Security Numbers"> Mask Social Security Numbers</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Mask financial billing details"> Mask financial billing details</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Export PHI to Excel / CSV"> Export PHI to Excel / CSV</label>
                            </div>
                        </div>
                    </div>

                    <div class="athena-permissions-category-grid customrole-perm-tab-panel mt-20" id="customrole-perm-tab-panel-administrative" style="display: none;">
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-user-plus"></i> Staff &amp; User Management</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Create & invite new staff users"> Create &amp; invite new staff users</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Edit user roles & security levels"> Edit user roles &amp; security levels</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Reset staff passwords & MFA"> Reset staff passwords &amp; MFA</label>
                            </div>
                        </div>
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-sliders-h"></i> Practice Setup &amp; Config</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Configure clinical templates & forms"> Configure clinical templates &amp; forms</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Manage fee schedules & CPT codes"> Manage fee schedules &amp; CPT codes</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Manage lab & pharmacy integrations"> Manage lab &amp; pharmacy integrations</label>
                            </div>
                        </div>
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-file-contract"></i> Compliance &amp; Audit Logs</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="View system audit logs"> View system audit logs</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Export HIPAA audit reports"> Export HIPAA audit reports</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Manage security policies & RBAC matrix"> Manage security policies &amp; RBAC matrix</label>
                            </div>
                        </div>
                    </div>

                    <div class="athena-permissions-category-grid customrole-perm-tab-panel mt-20" id="customrole-perm-tab-panel-other-settings" style="display: none; grid-template-columns: repeat(2, 1fr);">
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-comments"></i> Communication &amp; Messaging</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Broadcast practice announcements"> Broadcast practice announcements</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Send direct secure staff messages"> Send direct secure staff messages</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Send patient SMS & Email reminders"> Send patient SMS &amp; Email reminders</label>
                            </div>
                        </div>
                        <div class="athena-perm-category-box">
                            <div class="perm-category-title"><i class="fas fa-video"></i> Telehealth &amp; Virtual Visits</div>
                            <div class="perm-checkbox-list">
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Host video consultations"> Host video consultations</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Record virtual patient sessions"> Record virtual patient sessions</label>
                                <label class="athena-checkbox-item"><input type="checkbox" class="customrole-perm-cb" value="Share screen & digital whiteboard"> Share screen &amp; digital whiteboard</label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- ── TAB: Specialty Management ── -->
        <div class="admin-tab-section" id="admin-tab-specialties">
            <!-- 4A: Specialty Directory View -->
            <div id="specialty-directory-view">
                <div class="card mod-administration-style-5" id="specialty-management-card">
                    <div class="mod-administration-style-6">
                        <div>
                            <h2 class="mod-administration-style-2">Specialty Management</h2>
                            <p style="color:#64748b;font-size:0.85rem;margin-top:2px;">Configure clinical specialty engines, discipline codes, and specialized encounter modules.</p>
                        </div>
                        <button class="btn btn-primary mod-administration-style-7" id="add-specialty-btn">
                            <i class="fas fa-plus"></i> Add Specialty
                        </button>
                    </div>
                    <div class="table-container">
                        <table class="admin-users-table">
                            <thead>
                                <tr>
                                    <th>Specialty Name</th>
                                    <th>Specialty Key</th>
                                    <th>Code</th>
                                    <th>Assigned Providers</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="specialty-directory-list">
                                <tr><td colspan="6">Loading specialties...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 4B: Create / Edit Specialty Form View -->
            <div id="specialty-form-view" class="athena-wizard-view" style="display: none;">
                <div class="staff-workflow-top-bar">
                    <div class="staff-workflow-title-area">
                        <button type="button" class="btn-back-to-directory" id="btn-back-to-specialty-dir" title="Back to Specialty Directory">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="staff-workflow-titles">
                            <h2 id="specialty-form-title">Create Clinical Specialty</h2>
                            <p id="specialty-form-sub">Register a clinical specialty discipline and its encounter configuration.</p>
                        </div>
                    </div>
                    <div class="staff-workflow-actions">
                        <button type="button" class="btn-cancel" id="btn-cancel-specialty-form">Cancel</button>
                        <button type="button" class="btn-submit-user" id="btn-save-specialty">
                            <span id="specialty-save-btn-text">Create Specialty</span>
                        </button>
                    </div>
                </div>

                <input type="hidden" id="specialty-edit-id" value="">

                <div class="staff-workflow-grid" style="grid-template-columns: 1fr;">
                    <div class="staff-form-container">
                        <div class="staff-card-section">
                            <div class="staff-card-header">
                                <div class="staff-step-num-badge">1</div>
                                <div class="staff-card-header-text">
                                    <h3>Discipline Details</h3>
                                    <p>Key identifier, display name, and scope of care.</p>
                                </div>
                            </div>
                            <div class="staff-form-grid-2">
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="spec-name-input">Specialty Display Name <span class="req">*</span></label>
                                    <input type="text" id="spec-name-input" class="staff-input" placeholder="e.g. Cardiology (Heart & Vascular)" required>
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="spec-key-input">Specialty Key / Identifier <span class="req">*</span></label>
                                    <input type="text" id="spec-key-input" class="staff-input" placeholder="e.g. Cardiology" required>
                                    <p class="field-help-text" style="font-size:0.78rem;color:#64748b;margin-top:4px;">Machine key used for encounter templates and role presets.</p>
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="spec-code-input">Specialty Code</label>
                                    <input type="text" id="spec-code-input" class="staff-input" placeholder="e.g. SPEC-CARD">
                                </div>
                                <div class="staff-form-group">
                                    <label class="staff-form-label" for="spec-status-select">Status</label>
                                    <select id="spec-status-select" class="staff-select">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div class="staff-form-group span-2">
                                    <label class="staff-form-label" for="spec-desc-input">Description / Scope of Care</label>
                                    <textarea id="spec-desc-input" class="staff-input" rows="3" placeholder="Brief overview of clinical conditions, diagnostics, and procedures managed..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- end #admin-tab-specialties -->

        <!-- Add/Edit Custom Role Modal -->
        <div class="athena-modal-overlay" id="modal-custom-role" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center;">
            <div class="athena-modal-container" style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 600px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden;">
                <div class="athena-modal-header" style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                    <h3 class="athena-modal-title" id="modal-custom-role-title" style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-shield-alt" style="color: #0284c7;"></i> Create Custom Role
                    </h3>
                    <button type="button" class="athena-modal-close" id="btn-close-custom-role-modal" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b;">&times;</button>
                </div>
                <div class="athena-modal-body" style="padding: 20px;">
                    <p class="field-help-text" style="font-size: 0.8rem; color: #64748b; margin-top: 0; margin-bottom: 16px;">Name the role and pick its base access level. You'll configure its full sidebar and feature permissions on the next screen.</p>
                    <div class="athena-form-group mb-16" style="margin-bottom: 16px;">
                        <label class="athena-label">Role Name <span class="req">*</span></label>
                        <input type="text" id="modal-role-name" class="athena-input" placeholder="e.g., Clinical Scribe / Nurse Supervisor">
                    </div>
                    <div class="athena-form-group mb-16" style="margin-bottom: 16px;">
                        <label class="athena-label">Base System Role <span class="req">*</span></label>
                        <select id="modal-role-base" class="athena-select">
                            <option value="Nurse">Nurse (Clinical / Charting base)</option>
                            <option value="Receptionist">Receptionist (Front desk / Registration base)</option>
                            <option value="Billing Staff">Billing Staff (Claims &amp; Payments base)</option>
                            <option value="Doctor">Doctor (Provider / Prescription base)</option>
                            <option value="Therapist">Therapist (Clinical / Rehab base)</option>
                            <option value="Super Admin">Super Admin (Management / Oversight base)</option>
                        </select>
                        <p class="field-help-text" style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">Determines system-level authentication baseline.</p>
                    </div>
                    <div class="athena-form-group mb-16" style="margin-bottom: 16px;">
                        <label class="athena-label">Role Type <span class="req">*</span></label>
                        <select id="modal-role-type" class="athena-select">
                            <option value="security">Security Role (sidebar access only)</option>
                            <option value="template">Permission Template (onboarding preset only)</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                </div>
                <div class="athena-modal-footer" style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-athena-secondary" id="btn-cancel-custom-role">Cancel</button>
                    <button type="button" class="btn-athena-primary" id="btn-save-custom-role">Create &amp; Continue</button>
                </div>
            </div>
        </div>

    </main>
</div>
