<!-- public/modules/calendar.php -->
<link rel="stylesheet" href="css/modules/calendar.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'calendar'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content" style="padding: 0;">
        <!-- Top Sub-Navigation Tabs (Only Calendar & Waiting List) -->
        <div style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; height: 50px;">
            <div style="display: flex; gap: 28px; align-items: center; height: 100%;">
                <div id="tab-nav-calendar" style="height: 100%; display: flex; align-items: center; font-weight: 700; font-size: 0.95rem; color: #0284c7; border-bottom: 3px solid #0284c7; cursor: pointer; box-sizing: border-box;">
                    Calendar
                </div>
                <div id="tab-nav-waitinglist" style="height: 100%; display: flex; align-items: center; gap: 6px; font-weight: 600; font-size: 0.95rem; color: #64748b; border-bottom: 3px solid transparent; cursor: pointer; box-sizing: border-box;">
                    Waiting List <span id="calendar-waiting-list-count" style="background: #ef4444; color: white; border-radius: 10px; padding: 2px 7px; font-size: 0.72rem; font-weight: 800; display: none;">0</span>
                </div>
            </div>
            <?php include __DIR__ . '/topbar.php'; ?>
        </div>

        <!-- MAIN CALENDAR WORKSPACE -->
        <div id="main-calendar-workspace" style="padding: 16px;">
            <header class="workspace-header">
                <div class="month-nav">
                    <button class="nav-arrow-btn" id="cal-prev-month" aria-label="Previous Month"><i class="fas fa-chevron-left"></i></button>
                    <h1 class="month-label" id="cal-month-label">Loading Month...</h1>
                    <button class="nav-arrow-btn" id="cal-next-month" aria-label="Next Month"><i class="fas fa-chevron-right"></i></button>
                    <button class="btn btn-secondary btn-today" id="cal-today-btn">Today</button>
                </div>

                <!-- Right Side: Action buttons and View/Mode controls -->
                <div class="header-actions">
                    <!-- View Selector: Calendar vs List -->
                    <div class="view-toggle">
                        <button class="toggle-btn active" id="toggle-view-calendar">Calendar View</button>
                        <button class="toggle-btn" id="toggle-view-list">List View</button>
                    </div>

                    <!-- Unified Primary + Schedule Button -->
                    <button class="btn btn-primary mod-calendar-style-1" id="schedule-appt-btn">
                        <i class="fas fa-plus"></i> Schedule
                    </button>

                    <!-- Unified Secondary Profile Button -->
                    <button class="btn btn-secondary mod-calendar-style-2" id="calendar-profile-btn" aria-label="Provider Profile Options" title="Provider Profile">
                        <i class="fas fa-user-md"></i>
                    </button>

                    <!-- Unified Secondary Sync Button -->
                    <button class="btn btn-secondary mod-calendar-style-2" id="calendar-sync-btn" aria-label="Sync Schedule" title="Sync Schedule">
                        <i class="fas fa-sync-alt"></i>
                    </button>

                    <!-- Mode Switcher: Day, Week, Month -->
                    <div class="view-toggle" id="calendar-modes-toggle">
                        <button class="toggle-btn" id="btn-mode-day">Day</button>
                        <button class="toggle-btn" id="btn-mode-week">Week</button>
                        <button class="toggle-btn active" id="btn-mode-month">Month</button>
                    </div>
                </div>
            </header>

            <!-- Monthly Calendar Grid Card -->
            <div class="card calendar-grid-card" id="calendar-grid-card">
                <div class="calendar-header-bar">
                    <div class="calendar-header-title">Workspace Calendar</div>
                    <div class="calendar-legend">
                        <div class="legend-item"><span class="legend-dot status-scheduled"></span>Scheduled</div>
                        <div class="legend-item"><span class="legend-dot status-completed"></span>Completed</div>
                        <div class="legend-item"><span class="legend-dot status-cancelled"></span>Cancelled</div>
                    </div>
                </div>
                
                <div class="calendar-grid-container">
                    <div class="calendar-weekdays">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                    </div>
                    <div class="calendar-days" id="calendar-days-grid">
                        <!-- Javascript will populate days here -->
                    </div>
                </div>
            </div>

            <!-- Table List View (hidden by default) -->
            <div class="card hidden" id="calendar-list-card">
                <h2>Upcoming Scheduled Sessions</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Clinician</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="calendar-appointments-list">
                            <tr>
                                <td colspan="7">Loading schedule...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- WAITING LIST WORKSPACE CONTAINER (Hidden by default) -->
        <div id="waitinglist-workspace-container" class="hidden" style="padding: 16px;">
            <!-- Waiting List Sub-Header & Controls -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 20px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <!-- View Mode Toggle: Day, Week, Month -->
                    <div style="display: flex; background: #f1f5f9; border-radius: 6px; padding: 3px;">
                        <button type="button" id="wl-sub-day" style="padding: 6px 14px; border: none; background: transparent; font-size: 0.86rem; font-weight: 700; color: #0284c7; border-bottom: 2px solid #0284c7; cursor: pointer;">Day</button>
                        <button type="button" id="wl-sub-week" style="padding: 6px 14px; border: none; background: transparent; font-size: 0.86rem; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; cursor: pointer;">Week</button>
                        <button type="button" id="wl-sub-month" style="padding: 6px 14px; border: none; background: transparent; font-size: 0.86rem; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; cursor: pointer;">Month</button>
                    </div>

                    <!-- Date Title -->
                    <h2 id="wl-header-date-title" style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #1e293b;">
                        Wednesday, Sep 02, 2026
                    </h2>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" class="btn btn-secondary btn-today" id="wl-today-btn">Today</button>
                    <div style="display: flex; gap: 2px;">
                        <button type="button" class="nav-arrow-btn" id="wl-prev-btn"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" class="nav-arrow-btn" id="wl-next-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <!-- + Waiting List Orange Button -->
                    <button type="button" class="btn" id="wl-add-btn" style="background: #f97316; border-color: #f97316; color: #ffffff; font-weight: 700; padding: 8px 18px; border-radius: 6px; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-plus"></i> Waiting List
                    </button>
                </div>
            </div>

            <!-- DAY VIEW CARD: Data Table (Matching Screenshot 1) -->
            <div class="card" id="wl-day-view-card" style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 800; font-size: 0.95rem; color: #1e293b;">
                    Waiting List Patients
                </div>
                <div class="table-container" style="margin: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.82rem; color: #475569; font-weight: 800; letter-spacing: 0.5px;">
                                <th style="padding: 12px 16px;">PATIENT</th>
                                <th style="padding: 12px 16px;">PROVIDER</th>
                                <th style="padding: 12px 16px;">TIME PREFERENCE</th>
                                <th style="padding: 12px 16px;">MOBILE PHONE</th>
                                <th style="padding: 12px 16px;">REASON</th>
                                <th style="padding: 12px 16px; text-align: right;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="wl-day-table-body">
                            <tr>
                                <td colspan="6" style="padding: 28px; text-align: center; color: #94a3b8; font-size: 0.9rem;">
                                    Loading waiting list patients...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- WEEK VIEW CARD: Grid (Matching Screenshot 2) -->
            <div class="card hidden" id="wl-week-view-card" style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div id="wl-week-grid-container"></div>
            </div>

            <!-- MONTH VIEW CARD: Grid (Matching Screenshot 3) -->
            <div class="card hidden" id="wl-month-view-card" style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div id="wl-month-grid-container"></div>
            </div>
        </div>
    </main>
</div>