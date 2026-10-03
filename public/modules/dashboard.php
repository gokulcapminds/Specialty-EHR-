<!-- public/modules/dashboard.php --><link rel="stylesheet" href="css/modules/dashboard.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'dashboard'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content dash-page">
        <header class="workspace-header dash-topbar">
            <h1 id="dash-title">Clinical &amp; Operations Dashboard</h1>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- Welcome row -->
        <section class="dash-welcome">
            <div>
                <div class="dash-welcome-line">
                    <h2 id="dash-welcome-name">Welcome</h2>
                    <span class="dash-role-badge" id="dash-role-badge"></span>
                </div>
                <div class="dash-welcome-sub" id="dash-welcome-sub"></div>
            </div>
            <div class="dash-welcome-actions">
                <a href="#patients?action=register" class="btn btn-primary dash-btn" id="dash-btn-register">+ Create New Patient</a>
                <button type="button" class="btn btn-primary dash-btn" id="dash-btn-schedule">+ Schedule Appointment</button>
            </div>
        </section>

        <!-- KPI row -->
        <section class="dash-kpis">
            <div class="dash-kpi">
                <div class="dash-kpi-label">Total Patients</div>
                <div class="dash-kpi-value" id="dash-kpi-patients">&ndash;</div>
            </div>
            <div class="dash-kpi">
                <div class="dash-kpi-label">Appointments Today</div>
                <div class="dash-kpi-value" id="dash-kpi-appts">&ndash;</div>
            </div>
            <div class="dash-kpi">
                <div class="dash-kpi-label">Patients Arrived Today</div>
                <div class="dash-kpi-value" id="dash-kpi-arrived">&ndash;</div>
            </div>
            <div class="dash-kpi" id="dash-kpi-billing-card">
                <div class="dash-kpi-label">Billing Attention</div>
                <div class="dash-kpi-value" id="dash-kpi-billing">&ndash;</div>
            </div>
        </section>

        <!-- Schedule + Needs attention -->
        <section class="dash-grid">
            <div class="dash-card" id="dash-schedule-card">
                <div class="dash-card-head">
                    <h3>Today's Clinical Schedule</h3>
                    <a href="#calendar" class="dash-link">Full Schedule &rarr;</a>
                </div>
                <div class="dash-table-wrap">
                    <table class="dash-table">
                        <thead>
                            <tr><th>Time</th><th>Patient</th><th>Provider</th><th>Type</th><th>Status</th><th class="dash-th-right">Action</th></tr>
                        </thead>
                        <tbody id="dash-schedule-body">
                            <tr><td colspan="6" class="dash-empty">Loading&hellip;</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-head">
                    <h3>Needs Attention</h3>
                    <span class="dash-head-right">
                        <span class="dash-muted" id="dash-attention-count"></span>
                        <a href="#encounters" class="dash-chip-link">View All</a>
                    </span>
                </div>
                <ul class="dash-attention" id="dash-attention-list">
                    <li class="dash-empty">Loading&hellip;</li>
                </ul>
            </div>
        </section>

        <!-- Activity + Overview -->
        <section class="dash-grid">
            <div class="dash-card">
                <div class="dash-card-head dash-activity-head">
                    <h3>Practice Activity <span class="dash-sub-title" id="dash-activity-title">(Last 30 Days)</span></h3>
                    <select id="dash-activity-range" class="dash-select" aria-label="Activity range">
                        <option value="30">Month</option>
                        <option value="7">Week</option>
                    </select>
                </div>
                <div class="dash-chart-box">
                    <canvas id="dash-activity-chart"></canvas>
                    <div id="dash-activity-empty" class="dash-empty" style="display:none;">No activity in this period.</div>
                </div>
            </div>

            <div class="dash-card dash-overview-card">
                <div class="dash-card-head"><h3>Patient Overview</h3></div>
                <div class="dash-tiles">
                    <div class="dash-tile">
                        <div class="dash-tile-top"><span class="dash-tile-label">Active Patients</span><i class="fas fa-users dash-tile-icon dash-ic-blue"></i></div>
                        <div class="dash-tile-value" id="dash-ov-active">&ndash;</div>
                    </div>
                    <div class="dash-tile">
                        <div class="dash-tile-top"><span class="dash-tile-label">New Patients</span><i class="fas fa-user-plus dash-tile-icon dash-ic-green"></i></div>
                        <div class="dash-tile-value" id="dash-ov-new">&ndash;</div>
                    </div>
                    <div class="dash-tile" id="dash-ov-followups-tile">
                        <div class="dash-tile-top"><span class="dash-tile-label">Follow-ups Due</span><i class="fas fa-calendar-check dash-tile-icon dash-ic-purple"></i></div>
                        <div class="dash-tile-value" id="dash-ov-followups">&ndash;</div>
                    </div>
                    <div class="dash-tile">
                        <div class="dash-tile-top"><span class="dash-tile-label">Inactive Patients</span><i class="fas fa-user-slash dash-tile-icon dash-ic-slate"></i></div>
                        <div class="dash-tile-value" id="dash-ov-inactive">&ndash;</div>
                    </div>
                </div>
                <div class="dash-quick">
                    <div class="dash-quick-title">Quick workspace links</div>
                    <div class="dash-quick-links">
                        <a href="#patients" class="dash-quick-chip">Patients</a>
                        <a href="#calendar" class="dash-quick-chip">Calendar</a>
                        <a href="#billing" class="dash-quick-chip" id="dash-quick-billing">Billing &amp; Claims</a>
                        <a href="#encounters" class="dash-quick-chip">Notes</a>
                        <a href="#referrals" class="dash-quick-chip">Referrals</a>
                        <a href="#recalls" class="dash-quick-chip">Recalls</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
