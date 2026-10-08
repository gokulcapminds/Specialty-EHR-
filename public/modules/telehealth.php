<!-- public/modules/telehealth.php -->
<link rel="stylesheet" href="css/modules/telehealth.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'telehealth'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-color, #0f172a); margin: 0;">HIPAA Compliant Video Consultation</h1>
                <p style="font-size: 0.88rem; color: #64748b; margin: 4px 0 0 0;">Telehealth sessions are created automatically when a Telehealth appointment is booked on the Calendar.</p>
            </div>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- Telehealth Sessions History & Active Calls Table -->
        <div class="card" style="margin-top: 24px; padding: 20px; border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0;">
                    Active & Recent Telehealth Sessions
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
