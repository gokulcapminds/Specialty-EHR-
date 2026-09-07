<!-- public/modules/settings.php --><link rel="stylesheet" href="css/modules/settings.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'settings'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header settings-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h1>System Settings</h1>
            <div style="display: flex; align-items: center; gap: 12px;">
                <?php include __DIR__ . '/topbar.php'; ?>
                <button class="btn btn-primary settings-save-btn" id="save-settings-btn">
                    <i class="fas fa-save"></i> Save All Settings
                </button>
            </div>
        </header>

        <div class="settings-grid-container">
            <!-- Row 1: Theme, Hours & Preferences (2 Columns) -->
            <div class="settings-row-2col">
                <!-- Theme & Preferences Card -->
                <div class="card settings-card">
                    <h2 class="settings-card-title"><i class="fas fa-palette" style="color: #0284c7;"></i> Theme & Customization</h2>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label settings-field-label" for="setting-theme">Select Display Theme</label>
                        <select id="setting-theme" class="form-control settings-field-input">
                            <option value="light" selected>Light Mode (Default)</option>
                            <option value="dark">Dark Mode</option>
                            <option value="classic">OpenEMR Classic</option>
                            <option value="high-contrast">High Contrast</option>
                        </select>
                    </div>

                    <h3 style="font-size: 0.92rem; font-weight: 800; color: #0f172a; margin: 18px 0 10px 0; text-transform: uppercase; letter-spacing: 0.5px;">System Preferences</h3>
                    <div class="settings-pref-list">
                        <label class="settings-pref-item">
                            <input type="checkbox" checked class="custom-accent-checkbox" id="settings-field-8"> Enable Ambient Scribe Auto-Drafting
                        </label>
                        <label class="settings-pref-item">
                            <input type="checkbox" checked class="custom-accent-checkbox" id="settings-field-9"> Auto-Save Notes (Every 60s)
                        </label>
                        <label class="settings-pref-item">
                            <input type="checkbox" class="custom-accent-checkbox" id="settings-field-10"> Enable Email Session Reminders
                        </label>
                    </div>
                </div>

                <!-- Clinic Operating Hours Card -->
                <div class="card settings-card">
                    <h2 class="settings-card-title"><i class="far fa-clock" style="color: #0284c7;"></i> Clinic Operating Hours</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label class="form-label settings-field-label" for="setting-open-time">Opening Time</label>
                            <select id="setting-open-time" class="form-control settings-field-input">
                                <option value="06:00">06:00 AM</option>
                                <option value="07:00">07:00 AM</option>
                                <option value="08:00" selected>08:00 AM (Default)</option>
                                <option value="09:00">09:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label settings-field-label" for="setting-close-time">Closing Time</label>
                            <select id="setting-close-time" class="form-control settings-field-input">
                                <option value="15:00">03:00 PM</option>
                                <option value="16:00">04:00 PM</option>
                                <option value="17:00">05:00 PM</option>
                                <option value="18:00" selected>06:00 PM (Default)</option>
                                <option value="19:00">07:00 PM</option>
                                <option value="20:00">08:00 PM</option>
                                <option value="21:00">09:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <label class="form-label settings-field-label">Weekly Closed Days</label>
                    <div class="settings-weekly-days-grid">
                        <label class="settings-checkbox-item">
                            <input type="checkbox" class="weekly-closed-day custom-accent-checkbox" value="0" id="settings-field-1"> Sunday
                        </label>
                        <label class="settings-checkbox-item">
                            <input type="checkbox" class="weekly-closed-day custom-accent-checkbox" value="6" id="settings-field-2"> Saturday
                        </label>
                        <label class="settings-checkbox-item">
                            <input type="checkbox" class="weekly-closed-day custom-accent-checkbox" value="1" id="settings-field-3"> Monday
                        </label>
                        <label class="settings-checkbox-item">
                            <input type="checkbox" class="weekly-closed-day custom-accent-checkbox" value="2" id="settings-field-4"> Tuesday
                        </label>
                        <label class="settings-checkbox-item">
                            <input type="checkbox" class="weekly-closed-day custom-accent-checkbox" value="3" id="settings-field-5"> Wednesday
                        </label>
                        <label class="settings-checkbox-item">
                            <input type="checkbox" class="weekly-closed-day custom-accent-checkbox" value="4" id="settings-field-6"> Thursday
                        </label>
                        <label class="settings-checkbox-item">
                            <input type="checkbox" class="weekly-closed-day custom-accent-checkbox" value="5" id="settings-field-7"> Friday
                        </label>
                    </div>
                </div>
            </div>

            <!-- Row 2: Clinic Closure & Specific Holidays (Wide Card) -->
            <div class="card settings-card">
                <h2 class="settings-card-title"><i class="far fa-calendar-times" style="color: #0284c7;"></i> Add Specific Clinic Holiday Date</h2>
                <div style="display: flex; gap: 14px; align-items: center; max-width: 720px; margin-bottom: 12px;">
                    <input type="date" id="holiday-date-input" class="form-control settings-field-input" style="width: 210px; flex-shrink: 0;">
                    <input type="text" id="holiday-reason-input" class="form-control settings-field-input" placeholder="Reason (e.g. Independence Day)" style="flex: 1;">
                    <button type="button" class="btn btn-primary" id="add-holiday-btn" style="height: 42px; padding: 0 22px; background: #0284c7; border-color: #0284c7; font-weight: 700; border-radius: 8px;">Add</button>
                </div>
                <div id="holidays-list-container" class="settings-holidays-list" style="margin-top: 10px;">
                    <!-- Loaded dynamically -->
                </div>
            </div>

            <!-- Row 3: Outbound SMTP Email Configuration Card (Wide Card) -->
            <div class="card settings-card">
                <h2 class="settings-card-title"><i class="fas fa-envelope-open-text" style="color:#0284c7;"></i> Outbound SMTP Email Configuration</h2>
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 18px;">Configure your clinic SMTP mail server settings to send patient intake forms & recall notices directly via email.</p>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                    <div class="form-group">
                        <label class="form-label settings-field-label">SMTP Host</label>
                        <input type="text" id="setting-smtp-host" class="form-control settings-field-input" placeholder="smtp.gmail.com" value="smtp.gmail.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label settings-field-label">SMTP Port & Security</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="number" id="setting-smtp-port" class="form-control settings-field-input" placeholder="465" value="465" style="width: 100px;">
                            <select id="setting-smtp-secure" class="form-control settings-field-input" style="flex: 1;">
                                <option value="ssl" selected>SSL (Port 465)</option>
                                <option value="tls">TLS (Port 587)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label settings-field-label">SMTP Username / Email</label>
                        <input type="email" id="setting-smtp-user" class="form-control settings-field-input" placeholder="sivaprasad@capminds.com" value="sivaprasad@capminds.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label settings-field-label">SMTP Password / App Password</label>
                        <input type="password" id="setting-smtp-pass" class="form-control settings-field-input" placeholder="App Password" value="momh nyzn niaa yzvm">
                    </div>
                    <div class="form-group">
                        <label class="form-label settings-field-label">Sender Email Address</label>
                        <input type="email" id="setting-smtp-from" class="form-control settings-field-input" placeholder="sivaprasad@capminds.com" value="sivaprasad@capminds.com">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label settings-field-label">Public EHR Base URL (for Intake & Patient Links)</label>
                        <input type="text" id="setting-app-base-url" class="form-control settings-field-input" placeholder="e.g. http://localhost/Specialty%20EHR or https://myclinic.com" value="">
                        <small style="color: #64748b; font-size: 0.76rem; margin-top: 4px; display: block;">Leave blank for automatic detection of local server network IP, or enter your public domain/IP.</small>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
