<!-- public/modules/reports.php --><link rel="stylesheet" href="css/modules/reports.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'reports'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header">
            <h1>Audit &amp; Reports</h1>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- Shown instead of the log for anyone who is not a Super Admin (the server refuses them too). -->
        <div class="card rep-card" id="rep-restricted" hidden>
            <h2><i class="fas fa-lock"></i> Restricted</h2>
            <p class="rep-sub">Only a Super Admin can view the audit log.</p>
        </div>

        <div class="card rep-card" id="rep-main">
            <div class="rep-head">
                <div class="rep-head-text">
                    <h2>
                        Security Audit Log
                        <button type="button" class="info-tip-btn" id="rep-info-btn" aria-label="About the audit log"
                                data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="bottom"
                                data-bs-content="Every sign-in, and every time patient data is opened or changed, is recorded here. Entries are append-only and chained together with a secret-keyed fingerprint, so an edited or removed entry is detected when you verify.">i</button>
                    </h2>
                </div>
                <div class="rep-actions">
                    <span id="rep-integrity" class="rep-pill rep-pill-idle" role="status" aria-live="polite">Not verified yet</span>
                    <button type="button" class="btn btn-outline" id="rep-verify-btn">Verify now</button>
                    <button type="button" class="btn btn-outline" id="rep-export-btn">Export CSV</button>
                </div>
            </div>

            <form class="rep-filters" id="rep-filters" autocomplete="off">
                <label class="rep-field"><span>From</span><input type="date" id="rep-from" class="form-control"></label>
                <label class="rep-field"><span>To</span><input type="date" id="rep-to" class="form-control"></label>
                <label class="rep-field"><span>User</span><input type="text" id="rep-user" class="form-control" placeholder="Username"></label>
                <label class="rep-field"><span>Role</span><select id="rep-role" class="form-control"><option value="">All roles</option></select></label>
                <label class="rep-field"><span>Module</span><select id="rep-module" class="form-control"><option value="">All modules</option></select></label>
                <label class="rep-field rep-field-wide"><span>Action contains</span><input type="text" id="rep-action" class="form-control" placeholder="e.g. Update, Login"></label>
                <label class="rep-field rep-field-narrow"><span>Patient ID</span><input type="number" id="rep-patient" class="form-control" min="1" placeholder="#"></label>
                <div class="rep-filter-buttons">
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <button type="button" class="btn btn-outline" id="rep-clear-btn">Clear</button>
                </div>
            </form>

            <div class="table-container">
                <table class="rep-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Patient</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>IP Address</th>
                            <th>Fingerprint</th>
                        </tr>
                    </thead>
                    <tbody id="audit-trail-list">
                        <tr><td colspan="8" class="rep-empty">Loading audit log...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="rep-pagination"></div>
        </div>
    </main>
</div>
