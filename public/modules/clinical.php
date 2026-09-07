<!-- public/modules/clinical.php -->
<link rel="stylesheet" href="css/modules/clinical.css?v=<?= time() ?>">
<div class="app-container">
    <?php $activeNav = 'clinical'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h1>Clinical Encounter Workspace</h1>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <div class="card select-patient-card">
            <div class="form-group">
                <label class="form-label" for="clinical-patient-select">Active Encounter Patient</label>
                <select id="clinical-patient-select" class="form-control">
                    <option value="">Select a patient to begin encounter...</option>
                </select>
            </div>
        </div>

        <!-- Start New Encounter Section -->
        <div id="new-encounter-section" class="card hidden mod-clinical-style-1">
            <div class="mod-clinical-style-2">
                <div>
                    <h3 class="mod-clinical-style-3">Document Patient Encounter</h3>
                    <p class="mod-clinical-style-4">Start a new Cardiology, Orthopedic, Dermatology, Neurology, Oncology, Ophthalmology, or Physical Therapy specialty clinical record.</p>
                </div>
                <button class="btn btn-primary mod-clinical-style-5" id="new-encounter-btn">+ Start New Encounter</button>
            </div>
        </div>

        <!-- Clinical History List Section -->
        <div id="clinical-history-section" class="card hidden">
            <h3 class="mod-clinical-style-6">Encounter History & Reports</h3>
            <div class="mod-clinical-style-7" id="clinical-history-list">
                <!-- Filled dynamically by JavaScript -->
            </div>
        </div>

        <!-- Include Unified Specialty Encounter Modal -->
        <?php include __DIR__ . '/clinical_modal.php'; ?>

    </main>
</div>
