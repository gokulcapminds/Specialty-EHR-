<!-- public/modules/telehealth.php -->
<link rel="stylesheet" href="css/modules/telehealth.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'telehealth'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-color, #0f172a); margin: 0;">HIPAA Compliant Video Consultation</h1>
                <p style="font-size: 0.88rem; color: #64748b; margin: 4px 0 0 0;">Create unique telehealth links and send them via secure email to patients.</p>
            </div>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- Dispatcher / Session Creation Panel -->
        <div class="card" style="margin-bottom: 24px; padding: 20px; border-radius: 12px; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
            <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-paper-plane" style="color: #0284c7;"></i> Invite Patient to Telehealth Consultation
            </h2>
            <form id="telehealth-invite-form" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label for="telehealth-patient-select" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; display: block;">Select Patient <span style="color:#ef4444;">*</span></label>
                    <select id="telehealth-patient-select" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;" required>
                        <option value="">Loading patients...</option>
                    </select>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label for="telehealth-patient-email" style="font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; display: block;">Patient Email Address <span style="color:#ef4444;">*</span></label>
                    <input type="email" id="telehealth-patient-email" class="form-control" placeholder="patient@example.com" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;" required>
                </div>

                <button type="submit" class="btn btn-primary" id="create-telehealth-btn" style="padding: 10px 20px; font-weight: 600; height: 42px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-envelope"></i> Generate & Send Link
                </button>
            </form>
        </div>

        <!-- Telehealth Sessions History & Active Calls Table -->
        <div class="card" style="margin-top: 24px; padding: 20px; border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0;">
                    <i class="fas fa-list-alt" style="color: #0d9488;"></i> Active & Recent Telehealth Sessions
                </h2>
                <button class="btn btn-secondary" id="refresh-telehealth-sessions-btn" style="padding: 6px 14px; font-size: 0.85rem;">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>

            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--bg-hover, #f8fafc); text-align: left; font-size: 0.82rem; color: #475569; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 10px 14px;">Patient Name</th>
                            <th style="padding: 10px 14px;">Recipient Email</th>
                            <th style="padding: 10px 14px;">Room Identifier</th>
                            <th style="padding: 10px 14px;">Status</th>
                            <th style="padding: 10px 14px;">Scheduled</th>
                            <th style="padding: 10px 14px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="telehealth-sessions-tbody">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 24px; color: #64748b;">Loading telehealth sessions...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
