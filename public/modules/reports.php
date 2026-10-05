<!-- public/modules/reports.php --><link rel="stylesheet" href="css/modules/reports.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'reports'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="justify-content: flex-end; margin-bottom: 0.5rem; min-height: auto; padding: 0.25rem 0;">
            <div id="rep-page-title" style="display: none;"></div>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- 1. CLINICAL REPORTS TAB -->
        <div class="rep-tab-pane active" id="pane-clinical">
            <div class="user-rep-header">
                <h2 class="user-rep-title">Clinical Reports</h2>
                <p class="user-rep-subtitle">This report shows an overview of patient clinical encounters, documentation statuses, and diagnosis trends.</p>
            </div>

            <!-- 4 Enhanced KPI Summary Metrics -->
            <div class="rep-kpi-grid">
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-blue"><i class="fas fa-clipboard-list"></i></div>
                    <div>
                        <div class="rep-kpi-label">Total Encounters</div>
                        <div class="rep-kpi-value" id="kpi-clin-encounters">0</div>
                        <div class="rep-kpi-sub" id="kpi-clin-sub">Activity for selected dates</div>
                    </div>
                </div>
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-green"><i class="fas fa-file-circle-check"></i></div>
                    <div>
                        <div class="rep-kpi-label">Completed &amp; Signed</div>
                        <div class="rep-kpi-value" id="kpi-clin-signed">0</div>
                        <div class="rep-kpi-sub">Locked Encounter Charts</div>
                    </div>
                </div>
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-amber"><i class="fas fa-file-pen"></i></div>
                    <div>
                        <div class="rep-kpi-label">Pending Sign-off</div>
                        <div class="rep-kpi-value" id="kpi-clin-draft">0</div>
                        <div class="rep-kpi-sub">Drafts awaiting signature</div>
                    </div>
                </div>
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-purple"><i class="fas fa-chart-pie"></i></div>
                    <div>
                        <div class="rep-kpi-label">Documentation Rate</div>
                        <div class="rep-kpi-value" id="kpi-clin-completion">0%</div>
                        <div class="rep-kpi-sub">Signed vs Total Ratio</div>
                    </div>
                </div>
            </div>

            <!-- Sub-Tabs: Encounters vs. Top Diagnoses -->
            <div class="clin-rep-tabs">
                <button type="button" class="clin-rep-tab active" id="clin-tab-encounters" data-subtab="encounters">
                    <i class="fas fa-notes-medical"></i> Encounters Log
                </button>
                <button type="button" class="clin-rep-tab" id="clin-tab-diagnoses" data-subtab="diagnoses">
                    <i class="fas fa-stethoscope"></i> Most Frequent Diagnoses
                </button>
            </div>

            <!-- Encounters Sub-Tab Content -->
            <div id="clin-subpane-encounters" class="clin-subpane active">
                <!-- Filters & Search Bar -->
                <div class="clin-filter-bar">
                    <div class="clin-search-wrap">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="text" id="clin-search-input" class="clin-search-input" placeholder="Search patient, provider, or diagnosis...">
                    </div>
                    
                    <div class="clin-filters-group">
                        <select id="clin-filter-provider" class="clin-select" title="Filter by Provider">
                            <option value="0">All Providers</option>
                        </select>

                        <select id="clin-filter-status" class="clin-select" title="Filter by Status">
                            <option value="">All Statuses</option>
                            <option value="signed">Signed</option>
                            <option value="draft">Unsigned Draft</option>
                        </select>

                        <select id="clin-filter-type" class="clin-select" title="Filter by Encounter Type">
                            <option value="">All Visit Types</option>
                            <option value="Initial Consult">Initial Consult</option>
                            <option value="Follow-up">Follow-up</option>
                            <option value="Annual Physical">Annual Physical</option>
                            <option value="Standard Visit">Standard Visit</option>
                            <option value="Procedure">Procedure</option>
                        </select>

                        <button type="button" class="btn btn-sm btn-primary" id="clin-filter-apply-btn">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-sm btn-outline" id="clin-filter-reset-btn" title="Reset Filters">
                            <i class="fas fa-arrow-rotate-left"></i>
                        </button>
                    </div>
                </div>

                <!-- Encounters Table Card -->
                <div class="user-rep-card">
                    <div class="user-rep-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>Encounter Documentation Log</span>
                        <span id="clin-enc-count" style="font-size:0.85rem; font-weight:normal; color:#64748b;">0 records</span>
                    </div>
                    <div class="table-container" style="border: none;">
                        <table class="user-rep-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Provider</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Chief Complaint</th>
                                </tr>
                            </thead>
                            <tbody id="rep-encounters-list">
                                <tr><td colspan="6" class="rep-empty">Loading encounters...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="clin-pagination" style="margin-top: 1rem;"></div>
                </div>
            </div>

            <!-- Diagnoses Sub-Tab Content -->
            <div id="clin-subpane-diagnoses" class="clin-subpane" style="display:none;">
                <div class="user-rep-card">
                    <div class="user-rep-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>Most Frequent Diagnoses (ICD-10)</span>
                        <span style="font-size:0.85rem; font-weight:normal; color:#64748b;">Period Frequency Breakdown</span>
                    </div>
                    <div class="table-container" style="border: none;">
                        <table class="user-rep-table">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">ICD-10 Code</th>
                                    <th>Diagnosis Description</th>
                                    <th style="width: 140px; text-align: right;">Occurrences</th>
                                </tr>
                            </thead>
                            <tbody id="rep-diagnoses-list">
                                <tr><td colspan="3" class="rep-empty">Loading top diagnoses...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. APPOINTMENT REPORTS TAB -->
        <div class="rep-tab-pane" id="pane-appointments">
            <div class="user-rep-header">
                <h2 class="user-rep-title">Appointment Reports</h2>
                <p class="user-rep-subtitle">This report shows an overview of scheduling volume, completed visits, no-shows, and cancellations.</p>
            </div>

            <!-- 4 KPI Metrics for Appointments -->
            <div class="rep-kpi-grid">
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-blue"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <div class="rep-kpi-label">Total Booked</div>
                        <div class="rep-kpi-value" id="kpi-appt-total">0</div>
                        <div class="rep-kpi-sub">Scheduled appointments</div>
                    </div>
                </div>
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-green"><i class="fas fa-circle-check"></i></div>
                    <div>
                        <div class="rep-kpi-label">Completed Visits</div>
                        <div class="rep-kpi-value" id="kpi-appt-completed">0</div>
                        <div class="rep-kpi-sub">Completed &amp; Checked-In</div>
                    </div>
                </div>
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-amber"><i class="fas fa-calendar-xmark"></i></div>
                    <div>
                        <div class="rep-kpi-label">No-Shows</div>
                        <div class="rep-kpi-value" id="kpi-appt-noshow">0</div>
                        <div class="rep-kpi-sub">Missed consultations</div>
                    </div>
                </div>
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-purple"><i class="fas fa-ban"></i></div>
                    <div>
                        <div class="rep-kpi-label">Cancelled</div>
                        <div class="rep-kpi-value" id="kpi-appt-cancelled">0</div>
                        <div class="rep-kpi-sub">Patient or clinic cancelled</div>
                    </div>
                </div>
            </div>

            <!-- Sub-Tabs: Appointments Log vs Provider Utilization -->
            <div class="clin-rep-tabs">
                <button type="button" class="clin-rep-tab active" id="appt-tab-log" data-subtab="log">
                    <i class="fas fa-list-check"></i> Appointments Log
                </button>
                <button type="button" class="clin-rep-tab" id="appt-tab-util" data-subtab="util">
                    <i class="fas fa-user-doctor"></i> Provider Utilization
                </button>
            </div>

            <!-- Sub-Pane 1: Appointments Log -->
            <div id="appt-subpane-log" class="clin-subpane active">
                <!-- Filters & Search Bar -->
                <div class="clin-filter-bar">
                    <div class="clin-search-wrap">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="text" id="appt-search-input" class="clin-search-input" placeholder="Search patient, provider, or reason...">
                    </div>
                    
                    <div class="clin-filters-group">
                        <select id="appt-filter-provider" class="clin-select" title="Filter by Provider">
                            <option value="0">All Providers</option>
                        </select>

                        <select id="appt-filter-status" class="clin-select" title="Filter by Status">
                            <option value="">All Statuses</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Checked-In">Checked-In</option>
                            <option value="Completed">Completed</option>
                            <option value="No Show">No Show</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>

                        <select id="appt-filter-type" class="clin-select" title="Filter by Visit Type">
                            <option value="">All Visit Types</option>
                            <option value="Regular">Regular</option>
                            <option value="Follow-up">Follow-up</option>
                            <option value="Consultation">Consultation</option>
                            <option value="Urgent">Urgent</option>
                        </select>

                        <button type="button" class="btn btn-sm btn-primary" id="appt-filter-apply-btn">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-sm btn-outline" id="appt-filter-reset-btn" title="Reset Filters">
                            <i class="fas fa-arrow-rotate-left"></i>
                        </button>
                    </div>
                </div>

                <div class="user-rep-card">
                    <div class="user-rep-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>Appointments Schedule &amp; Activity Log</span>
                        <span id="appt-count-badge" style="font-size:0.85rem; font-weight:normal; color:#64748b;">0 records</span>
                    </div>
                    <div class="table-container" style="border: none;">
                        <table class="user-rep-table">
                            <thead>
                                <tr>
                                    <th>Date &amp; Time</th>
                                    <th>Patient</th>
                                    <th>Provider</th>
                                    <th>Visit Type</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody id="rep-appts-list">
                                <tr><td colspan="6" class="rep-empty">Loading appointments...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="appt-log-pagination" style="margin-top: 1rem;"></div>
            </div>

            <!-- Sub-Pane 2: Provider Utilization -->
            <div id="appt-subpane-util" class="clin-subpane" style="display:none;">
                <div class="user-rep-card">
                    <div class="user-rep-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>Provider Appointment Volume &amp; Attendance</span>
                        <span style="font-size:0.85rem; font-weight:normal; color:#64748b;">Clinical Staff Performance</span>
                    </div>
                    <div class="table-container" style="border: none;">
                        <table class="user-rep-table">
                            <thead>
                                <tr>
                                    <th>Provider</th>
                                    <th>Total Booked</th>
                                    <th>Completed</th>
                                    <th>No Shows</th>
                                    <th>Cancelled</th>
                                </tr>
                            </thead>
                            <tbody id="rep-providers-util-list">
                                <tr><td colspan="5" class="rep-empty">Loading provider statistics...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. USER REPORTS TAB (Matching Reference UI) -->
        <div class="rep-tab-pane" id="pane-users">
            <div class="user-rep-header">
                <h2 class="user-rep-title">User Report</h2>
                <p class="user-rep-subtitle">This report shows an overview of all of the user accounts for your practice, including all Providers and Staff.</p>
                
                <div class="user-rep-tabs">
                    <button type="button" class="user-rep-tab" id="user-tab-providers" data-user-cat="providers">Providers</button>
                    <button type="button" class="user-rep-tab active" id="user-tab-staff" data-user-cat="staff">Staff</button>
                </div>

                <div class="user-rep-search-bar">
                    <div class="user-rep-search-wrap">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="text" id="user-rep-search-input" class="user-rep-search-input" placeholder="Search Staff">
                    </div>
                    <button type="button" class="btn btn-primary" id="user-rep-search-btn">Search</button>
                </div>
            </div>

            <div class="user-rep-card">
                <div class="user-rep-card-header" id="user-rep-card-title">Staff List</div>
                <div class="table-container" style="border: none;">
                    <table class="user-rep-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="rep-users-list">
                            <tr>
                                <td colspan="4" style="padding: 0;">
                                    <div class="user-rep-empty-state">
                                        <svg class="user-rep-empty-icon" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="80" cy="80" r="64" fill="#EEF2FF" />
                                            <rect x="52" y="44" width="46" height="66" rx="4" fill="#FFFFFF" stroke="#3B82F6" stroke-width="2.5" />
                                            <rect x="68" y="52" width="50" height="70" rx="4" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" />
                                            <line x1="78" y1="68" x2="106" y2="68" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" />
                                            <line x1="78" y1="76" x2="106" y2="76" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" />
                                            <line x1="78" y1="84" x2="102" y2="84" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" />
                                            <line x1="78" y1="92" x2="96" y2="92" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" />
                                            <line x1="60" y1="58" x2="88" y2="58" stroke="#93C5FD" stroke-width="2" stroke-linecap="round" />
                                            <line x1="60" y1="66" x2="74" y2="66" stroke="#93C5FD" stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                        <p style="color: #64748b; font-size: 0.95rem; margin: 0;">No records found.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="users-pagination" style="margin-top: 1rem;"></div>
            </div>
        </div>

        <!-- 4. SPECIALTY REPORTS TAB -->
        <div class="rep-tab-pane" id="pane-specialties">
            <div class="user-rep-header">
                <h2 class="user-rep-title">Specialty Reports</h2>
                <p class="user-rep-subtitle">This report shows an overview of clinical encounters, active providers, and unique patient volume distributed across medical specialties.</p>
            </div>

            <!-- Specialty KPIs -->
            <div class="rep-kpi-grid">
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-blue"><i class="fas fa-stethoscope"></i></div>
                    <div>
                        <div class="rep-kpi-label">Active Specialties</div>
                        <div class="rep-kpi-value" id="kpi-spec-count">0</div>
                        <div class="rep-kpi-sub">Specialties with providers</div>
                    </div>
                </div>
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-green"><i class="fas fa-notes-medical"></i></div>
                    <div>
                        <div class="rep-kpi-label">Total Encounters</div>
                        <div class="rep-kpi-value" id="kpi-spec-encounters">0</div>
                        <div class="rep-kpi-sub">Across all specialties</div>
                    </div>
                </div>
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-amber"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="rep-kpi-label">Unique Patients</div>
                        <div class="rep-kpi-value" id="kpi-spec-patients">0</div>
                        <div class="rep-kpi-sub">Distinct patients seen</div>
                    </div>
                </div>
                <div class="rep-kpi-card">
                    <div class="rep-kpi-icon rep-kpi-purple"><i class="fas fa-file-signature"></i></div>
                    <div>
                        <div class="rep-kpi-label">Documentation Rate</div>
                        <div class="rep-kpi-value" id="kpi-spec-rate">0%</div>
                        <div class="rep-kpi-sub">Signed clinical notes</div>
                    </div>
                </div>
            </div>

            <!-- Filters & Search Bar -->
            <div class="clin-filter-bar">
                <div class="clin-search-wrap">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="spec-search-input" class="clin-search-input" placeholder="Search specialty by name...">
                </div>

                <div class="clin-filters-group">
                    <select id="spec-filter-activity" class="clin-select" title="Filter by Activity">
                        <option value="all">All Specialties</option>
                        <option value="active">Active Only (Encounters > 0)</option>
                        <option value="has_providers">Has Providers</option>
                    </select>

                    <button type="button" class="btn btn-secondary" id="spec-filter-reset-btn" title="Reset Filters" style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 0.85rem; font-size:0.85rem;">
                        <i class="fas fa-rotate-left"></i> Reset
                    </button>
                </div>
            </div>

            <div class="user-rep-card mb-4">
                <div class="user-rep-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Specialty-Wise Clinical Activity &amp; Patient Volume</span>
                    <span id="spec-count-badge" class="badge" style="font-weight: 600; font-size: 0.82rem; background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 999px;">0 specialties</span>
                </div>
                <div class="table-container" style="border: none;">
                    <table class="user-rep-table">
                        <thead>
                            <tr>
                                <th>Medical Specialty</th>
                                <th>Active Providers</th>
                                <th>Total Encounters</th>
                                <th>Unique Patients</th>
                                <th>Signed Notes</th>
                                <th>Volume Share</th>
                            </tr>
                        </thead>
                        <tbody id="rep-specialties-list">
                            <tr><td colspan="6" class="rep-empty">Loading specialty reports...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="spec-pagination" style="margin-top: 1rem;"></div>
            </div>
        </div>

        <!-- 5. INVOICES TAB -->
        <div class="rep-tab-pane" id="pane-invoices">
            <div class="user-rep-header">
                <h2 class="user-rep-title">Invoices</h2>
                <p class="user-rep-subtitle">This report shows an overview of billing invoices, total amounts, paid charges, and outstanding patient balances.</p>
            </div>

            <div class="user-rep-card">
                <div class="user-rep-card-header">Invoices &amp; Patient Balances Ledger</div>
                <div class="table-container" style="border: none;">
                    <table class="user-rep-table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Total Amount ($)</th>
                                <th>Paid ($)</th>
                                <th>Balance Due ($)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="rep-financial-list">
                            <tr><td colspan="7" class="rep-empty">Loading invoices...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="invoices-pagination" style="margin-top: 1rem;"></div>
            </div>
        </div>

        <!-- 6. AUDIT LOG TAB (Redesigned matching all report screens) -->
        <div class="rep-tab-pane" id="pane-audit">
            <!-- Shown instead of the log for anyone who is not a Super Admin -->
            <div class="user-rep-card mb-4" id="rep-restricted" hidden style="padding: 2.5rem; text-align: center;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #fef2f2; color: #ef4444; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem;">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Access Restricted</h3>
                <p class="user-rep-subtitle" style="margin-bottom: 0;">Only a Super Admin can view the tamper-evident security audit log.</p>
            </div>

            <div id="rep-main">
                <div class="user-rep-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h2 class="user-rep-title" style="display: flex; align-items: center; gap: 0.5rem;">
                            Security Audit Log
                            <button type="button" class="info-tip-btn" id="rep-info-btn" aria-label="About the audit log"
                                    data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="bottom"
                                    data-bs-content="Every sign-in, and every time patient data is opened or changed, is recorded here. Entries are append-only and chained together with a secret-keyed fingerprint, so an edited or removed entry is detected when you verify.">i</button>
                        </h2>
                        <p class="user-rep-subtitle">Tamper-evident chain of security events, administrative activities, and clinical access trails.</p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                        <span id="rep-integrity" class="rep-pill rep-pill-idle" role="status" aria-live="polite">Not verified yet</span>
                        <button type="button" class="btn btn-outline" id="rep-verify-btn" style="background:#fff; border:1px solid #cbd5e1; font-weight:600; font-size:0.85rem; padding:0.5rem 0.9rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.4rem;">
                            <i class="fas fa-shield-check"></i> Verify now
                        </button>
                        <button type="button" class="btn btn-outline" id="rep-export-btn" style="background:#fff; border:1px solid #cbd5e1; font-weight:600; font-size:0.85rem; padding:0.5rem 0.9rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.4rem;">
                            <i class="fas fa-file-arrow-down"></i> Export CSV
                        </button>
                    </div>
                </div>

                <!-- 4 Security Audit KPI Cards -->
                <div class="rep-kpi-grid">
                    <div class="rep-kpi-card">
                        <div class="rep-kpi-icon rep-kpi-blue"><i class="fas fa-shield-halved"></i></div>
                        <div>
                            <div class="rep-kpi-label">Logged Events</div>
                            <div class="rep-kpi-value" id="kpi-audit-total">0</div>
                            <div class="rep-kpi-sub">Total verified audit records</div>
                        </div>
                    </div>
                    <div class="rep-kpi-card">
                        <div class="rep-kpi-icon rep-kpi-green"><i class="fas fa-fingerprint"></i></div>
                        <div>
                            <div class="rep-kpi-label">Chain Integrity</div>
                            <div class="rep-kpi-value" id="kpi-audit-status" style="font-size:1.3rem;">Verified</div>
                            <div class="rep-kpi-sub">Cryptographic hash chain</div>
                        </div>
                    </div>
                    <div class="rep-kpi-card">
                        <div class="rep-kpi-icon rep-kpi-amber"><i class="fas fa-user-shield"></i></div>
                        <div>
                            <div class="rep-kpi-label">Active Modules</div>
                            <div class="rep-kpi-value" id="kpi-audit-modules">0</div>
                            <div class="rep-kpi-sub">Audited system surfaces</div>
                        </div>
                    </div>
                    <div class="rep-kpi-card">
                        <div class="rep-kpi-icon rep-kpi-purple"><i class="fas fa-clock-rotate-left"></i></div>
                        <div>
                            <div class="rep-kpi-label">Current Page</div>
                            <div class="rep-kpi-value" id="kpi-audit-page">1</div>
                            <div class="rep-kpi-sub">Page events window</div>
                        </div>
                    </div>
                </div>

                <!-- Filters & Search Bar in Modern Style -->
                <form class="clin-filter-bar mb-4" id="rep-filters" autocomplete="off" style="padding: 1rem 1.25rem;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; width: 100%;">
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">From:</span>
                            <input type="date" id="rep-from" class="clin-select" style="padding: 0.45rem 0.6rem;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">To:</span>
                            <input type="date" id="rep-to" class="clin-select" style="padding: 0.45rem 0.6rem;">
                        </div>

                        <div class="clin-search-wrap" style="min-width: 170px; flex: 1 1 170px; max-width: 240px;">
                            <i class="fas fa-user" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem;"></i>
                            <input type="text" id="rep-user" class="clin-search-input" style="padding-left: 2rem;" placeholder="Filter Username">
                        </div>

                        <select id="rep-role" class="clin-select" title="Filter by Role">
                            <option value="">All Roles</option>
                        </select>

                        <select id="rep-module" class="clin-select" title="Filter by Module">
                            <option value="">All Modules</option>
                        </select>

                        <div class="clin-search-wrap" style="min-width: 180px; flex: 1 1 180px; max-width: 260px;">
                            <i class="fas fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem;"></i>
                            <input type="text" id="rep-action" class="clin-search-input" style="padding-left: 2rem;" placeholder="Action contains...">
                        </div>

                        <div style="width: 105px;">
                            <input type="number" id="rep-patient" class="clin-select" style="width: 100%;" min="1" placeholder="Pt ID #">
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
                            <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.48rem 0.9rem; font-size: 0.85rem;">
                                <i class="fas fa-filter"></i> Apply
                            </button>
                            <button type="button" class="btn btn-secondary" id="rep-clear-btn" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.48rem 0.9rem; font-size: 0.85rem;">
                                <i class="fas fa-rotate-left"></i> Clear
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Modern Table Card Container -->
                <div class="user-rep-card mb-4">
                    <div class="user-rep-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Security Event Logs &amp; Integrity Hashes</span>
                        <span id="rep-audit-count-badge" class="badge" style="font-weight: 600; font-size: 0.82rem; background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 999px;">0 events</span>
                    </div>
                    <div class="table-container" style="border: none;">
                        <table class="user-rep-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                    <th>Module</th>
                                    <th>IP Address</th>
                                    <th>Fingerprint</th>
                                </tr>
                            </thead>
                            <tbody id="audit-trail-list">
                                <tr><td colspan="7" class="rep-empty">Loading audit log...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="rep-pagination" style="margin-top: 1rem;"></div>
            </div>
        </div>
    </main>
</div>

