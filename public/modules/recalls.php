<!-- public/modules/recalls.php -->
<link rel="stylesheet" href="css/modules/dashboard.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'recalls'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content" style="padding: 24px 32px; background: #f8fafc; min-height: 100vh;">
        <!-- Page Header -->
        <header class="referrals-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-clock-rotate-left" style="color: #0284c7;"></i> Call list
                </h1>
                <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;">Flag patients for future call-backs and track recall conversions.</p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <?php include __DIR__ . '/topbar.php'; ?>
                <button type="button" class="btn btn-outline" id="btn-export-recalls-csv" style="border: 1px solid #cbd5e1; color: #334155; font-weight: 700; font-size: 0.88rem; border-radius: 8px; padding: 9px 16px; background: #ffffff; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-file-csv" style="color: #0284c7;"></i> Export CSV
                </button>
            </div>
        </header>

        <!-- KPI Summary Metrics Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div id="kpi-due-this-week" style="font-size: 1.8rem; font-weight: 800; color: #0f172a; line-height: 1;">0</div>
                <div style="font-size: 0.82rem; color: #64748b; font-weight: 600; margin-top: 6px;">Due this week</div>
            </div>

            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div id="kpi-overdue" style="font-size: 1.8rem; font-weight: 800; color: #dc2626; line-height: 1;">0</div>
                <div style="font-size: 0.82rem; color: #64748b; font-weight: 600; margin-top: 6px;">Overdue</div>
            </div>

            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div id="kpi-scheduled-this-month" style="font-size: 1.8rem; font-weight: 800; color: #0284c7; line-height: 1;">0</div>
                <div style="font-size: 0.82rem; color: #64748b; font-weight: 600; margin-top: 6px;">Scheduled this month</div>
            </div>

            <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div id="kpi-conversion-rate" style="font-size: 1.8rem; font-weight: 800; color: #166534; line-height: 1;">0%</div>
                <div style="font-size: 0.82rem; color: #64748b; font-weight: 600; margin-top: 6px;">Conversion</div>
            </div>
        </div>

        <!-- Filters & Toolbar Card -->
        <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 18px 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: center;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px;">Month</label>
                    <select id="recall-filter-month" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                        <!-- Populated dynamically via JS -->
                    </select>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px;">Reason / Type</label>
                    <select id="recall-filter-reason" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                        <option value="">Any</option>
                        <option value="Hygiene">Hygiene</option>
                        <option value="Check-up">Check-up</option>
                        <option value="Ortho review">Ortho review</option>
                        <option value="Implant review">Implant review</option>
                        <option value="Post-op">Post-op</option>
                        <option value="Treatment Plan">Treatment Plan</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px;">Status</label>
                    <select id="recall-filter-status" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                        <option value="Pending" selected>Pending</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="">Any</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px;">Priority</label>
                    <select id="recall-filter-priority" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                        <option value="">Any</option>
                        <option value="Low">Low</option>
                        <option value="Normal">Normal</option>
                        <option value="High">High</option>
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; margin-top: 22px;">
                    <input type="checkbox" id="recall-filter-overdue" style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="recall-filter-overdue" style="font-weight: 600; font-size: 0.88rem; color: #334155; cursor: pointer;">Show overdue</label>
                </div>
            </div>
        </div>

        <!-- Call List Section Container -->
        <div id="recalls-call-list-container" style="display: flex; flex-direction: column; gap: 14px;">
            <div style="padding: 32px; text-align: center; color: #94a3b8; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0;">
                <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem; margin-bottom: 10px; color: #0284c7;"></i>
                <div>Loading call list records...</div>
            </div>
        </div>
    </main>
</div>
