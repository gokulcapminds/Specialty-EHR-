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
            <span class="badge-role badge-success" style="padding: 6px 14px; font-size: 0.82rem;">
                <i class="fas fa-shield-alt"></i> Peer-to-Peer Encrypted
            </span>
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

        <div class="telehealth-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <!-- Main Video Panel -->
            <div class="card video-panel-card" style="display: flex; flex-direction: column; height: 580px; border-radius: 12px; overflow: hidden;">
                <div style="background: #1e293b; color: white; padding: 12px 16px; font-weight: 700; font-size: 0.95rem; display: flex; justify-content: space-between; align-items: center;">
                    <span id="active-session-title"><i class="fas fa-video"></i> Video Stream Container</span>
                    <span id="active-room-badge" style="font-size: 0.78rem; background: #0284c7; padding: 3px 10px; border-radius: 12px; font-weight: 600; display: none;"></span>
                </div>
                <div class="video-container" id="jitsi-meet-container" style="flex: 1; background: #090d16; display: flex; align-items: center; justify-content: center; position: relative;">
                    <div style="text-align: center; color: #94a3b8; padding: 20px;" id="telehealth-status">
                        <i class="fas fa-video-slash" style="font-size: 3rem; margin-bottom: 12px; color: #475569; display: block;"></i>
                        <p style="font-size: 1rem; font-weight: 600; color: #cbd5e1;">No Active Consultation Joined</p>
                        <p style="font-size: 0.85rem; max-width: 360px; margin: 6px auto 0 auto;">Select an active consultation session from the table below and click "Join Consultation" to launch the video stream.</p>
                    </div>
                </div>
            </div>

            <!-- Session Controls & Controls Panel -->
            <div class="card actions-panel" style="padding: 20px; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 16px;">Session Controls</h2>
                    <div class="form-group call-controls" style="display: flex; flex-direction: column; gap: 10px;">
                        <button class="btn btn-primary" id="toggle-camera-btn" style="width: 100%; justify-content: center; gap: 8px;">
                            <i class="fas fa-video"></i> Mute Camera
                        </button>
                        <button class="btn btn-primary" id="toggle-mic-btn" style="width: 100%; justify-content: center; gap: 8px;">
                            <i class="fas fa-microphone"></i> Mute Microphone
                        </button>
                        <button class="btn btn-danger" id="end-call-btn" style="width: 100%; justify-content: center; gap: 8px; margin-top: 10px;">
                            <i class="fas fa-phone-slash"></i> End Consultation
                        </button>
                    </div>
                </div>

                <div class="session-info" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-color, #e2e8f0);">
                    <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 8px;">HIPAA & Security Metrics</h3>
                    <p class="session-notice" style="font-size: 0.82rem; color: #64748b; line-height: 1.5;">
                        All connection metrics and video streams are end-to-end encrypted in transit. Disconnecting automatically terminates access keys and records compliance logging in the audit log.
                    </p>
                </div>
            </div>
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
                            <th style="padding: 10px 14px;">Created At</th>
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
