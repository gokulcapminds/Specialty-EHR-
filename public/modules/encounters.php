<!-- public/modules/encounters.php -->
<link rel="stylesheet" href="css/modules/encounters.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'encounters'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header enc-header">
            <div>
                <h1>Encounters</h1>
                <p class="text-sm enc-sub">Appointment &rarr; Check in &rarr; Encounter &rarr; Sign &rarr; Billing</p>
            </div>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- Stage chips: the whole pipeline at a glance -->
        <div class="enc-chips" id="enc-chips" role="tablist" aria-label="Encounter stage"></div>

        <div class="card enc-card">
            <div class="enc-toolbar">
                <div class="enc-filters">
                    <select id="enc-range" class="form-select" aria-label="Date range">
                        <option value="today">Today</option>
                        <option value="week">This week</option>
                        <option value="all">All dates</option>
                    </select>
                    <select id="enc-provider" class="form-select" aria-label="Provider"><option value="">All providers</option></select>
                    <input type="text" id="enc-search" class="form-control" placeholder="Search patient, encounter # or provider" aria-label="Search encounters">
                </div>
                <div class="enc-toolbar-actions">
                    <button type="button" class="btn btn-secondary btn-sm" id="enc-refresh"><i class="fas fa-sync-alt"></i> Refresh</button>
                    <button type="button" class="btn btn-primary btn-sm" id="enc-walkin-btn"><i class="fas fa-person-walking"></i> Walk-in encounter</button>
                </div>
            </div>

            <!-- Walk-in (no appointment): inline panel, not a popup -->
            <div class="enc-walkin" id="enc-walkin" style="display:none;">
                <div class="enc-walkin-grid">
                    <div>
                        <label class="enc-lbl" for="enc-wi-patient">Patient <span class="req">*</span></label>
                        <input type="text" id="enc-wi-patient" class="form-control" list="enc-patient-list" placeholder="Type a name and pick from the list" autocomplete="off">
                        <datalist id="enc-patient-list"></datalist>
                    </div>
                    <div>
                        <label class="enc-lbl" for="enc-wi-provider">Provider</label>
                        <select id="enc-wi-provider" class="form-select"></select>
                    </div>
                    <div>
                        <label class="enc-lbl" for="enc-wi-visit">Visit type</label>
                        <select id="enc-wi-visit" class="form-select"><!-- filled from GET /api/visit-types by applyVisitTypeSelects() --></select>
                    </div>
                </div>
                <div class="enc-walkin-actions">
                    <span class="enc-err" id="enc-wi-error" style="display:none;"></span>
                    <button type="button" class="btn btn-secondary btn-sm" id="enc-wi-cancel">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="enc-wi-start">Start encounter</button>
                </div>
            </div>

            <div class="table-container">
                <table id="enc-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Encounter</th>
                            <th>Provider</th>
                            <th>Stage</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="enc-list">
                        <tr><td colspan="6" class="enc-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
