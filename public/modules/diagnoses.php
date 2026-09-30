<!-- public/modules/diagnoses.php -->
<link rel="stylesheet" href="css/modules/dashboard.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'diagnoses'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-notes-medical" style="color: #0284c7;"></i> Diagnoses &amp; Allergies
                </h1>
                <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;">Maintain each patient's structured problem list and allergy record.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 14px;">
                <button type="button" class="btn btn-primary" id="btn-workspace-add-problem" style="background: #0284c7; border-color: #0284c7; font-weight: 700; font-size: 0.9rem; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px; color: #ffffff; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.2);">
                    <i class="fas fa-plus"></i> Add Diagnosis
                </button>
                <button type="button" class="btn btn-primary" id="btn-workspace-add-allergy" style="display: none; background: #dc2626; border-color: #dc2626; font-weight: 700; font-size: 0.9rem; border-radius: 8px; padding: 9px 18px; align-items: center; gap: 8px; color: #ffffff; cursor: pointer; box-shadow: 0 2px 4px rgba(220,38,38,0.2);">
                    <i class="fas fa-plus"></i> Add Allergy
                </button>
                <?php include __DIR__ . '/topbar.php'; ?>
            </div>
        </header>

        <!-- Tab Switcher -->
        <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0;">
            <button type="button" id="diag-tab-problems" class="diag-tab-btn" style="padding: 10px 20px; font-weight: 700; font-size: 0.9rem; border: none; background: none; cursor: pointer; color: #0284c7; border-bottom: 3px solid #0284c7; margin-bottom: -2px;">
                <i class="fas fa-diagnoses"></i> Problem List
            </button>
            <button type="button" id="diag-tab-allergies" class="diag-tab-btn" style="padding: 10px 20px; font-weight: 700; font-size: 0.9rem; border: none; background: none; cursor: pointer; color: #64748b; border-bottom: 3px solid transparent; margin-bottom: -2px;">
                <i class="fas fa-allergies"></i> Allergies
            </button>
        </div>

        <!-- PROBLEM LIST PANEL -->
        <div id="diag-panel-problems">
            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 18px 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: center;">
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Filter by Patient</label>
                        <select id="problem-filter-patient" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                            <option value="">All Patients</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Filter by Status</label>
                        <select id="problem-filter-status" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                            <option value="">All Statuses</option>
                            <option value="Active">Active</option>
                            <option value="Chronic">Chronic</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Search Keywords</label>
                        <input type="text" id="problem-filter-search" class="form-control" placeholder="Search patient, diagnosis, ICD-10..." style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                    </div>
                </div>
            </div>

            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div class="table-container" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.5px;">
                                <th style="padding: 14px 16px;">DIAGNOSIS</th>
                                <th style="padding: 14px 16px;">ICD-10</th>
                                <th style="padding: 14px 16px;">PATIENT</th>
                                <th style="padding: 14px 16px;">CHRONICITY</th>
                                <th style="padding: 14px 16px;">STATUS</th>
                                <th style="padding: 14px 16px;">ONSET DATE</th>
                                <th style="padding: 14px 16px;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="workspace-problems-list">
                            <tr>
                                <td colspan="7" style="padding: 24px; text-align: center; color: #64748b;">
                                    <i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading problem list...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="problems-workspace-pagination"></div>
            </div>
        </div>

        <!-- ALLERGIES PANEL -->
        <div id="diag-panel-allergies" style="display: none;">
            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 18px 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: center;">
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Filter by Patient</label>
                        <select id="allergy-filter-patient" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                            <option value="">All Patients</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Filter by Category</label>
                        <select id="allergy-filter-category" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                            <option value="">All Categories</option>
                            <option value="Medication">Medication</option>
                            <option value="Food">Food</option>
                            <option value="Environmental">Environmental</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Filter by Severity</label>
                        <select id="allergy-filter-severity" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                            <option value="">All Severities</option>
                            <option value="Mild">Mild</option>
                            <option value="Moderate">Moderate</option>
                            <option value="Severe">Severe</option>
                            <option value="Life-Threatening">Life-Threatening</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Filter by Status</label>
                        <select id="allergy-filter-status" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                            <option value="">All Statuses</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Search Keywords</label>
                        <input type="text" id="allergy-filter-search" class="form-control" placeholder="Search patient, allergen..." style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                    </div>
                </div>
            </div>

            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div class="table-container" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.5px;">
                                <th style="padding: 14px 16px;">ALLERGEN</th>
                                <th style="padding: 14px 16px;">CATEGORY</th>
                                <th style="padding: 14px 16px;">PATIENT</th>
                                <th style="padding: 14px 16px;">REACTION</th>
                                <th style="padding: 14px 16px;">SEVERITY</th>
                                <th style="padding: 14px 16px;">STATUS</th>
                                <th style="padding: 14px 16px;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="workspace-allergies-list">
                            <tr>
                                <td colspan="7" style="padding: 24px; text-align: center; color: #64748b;">
                                    <i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading allergies...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="allergies-workspace-pagination"></div>
            </div>
        </div>
    </main>
</div>
