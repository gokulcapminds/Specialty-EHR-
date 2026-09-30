<!-- public/modules/dashboard.php --><link rel="stylesheet" href="css/modules/dashboard.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'dashboard'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h1 id="workspace-title">Workspace Dashboard</h1>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- Dynamic Statistics Widget Area -->
        <div class="stat-cards-row">
            <div class="stat-card-wrapper">
                <div class="stat-card h-100">
                    <div class="stat-icon-bg bg-blue-light">
                        <i class="fas fa-users text-blue-dark"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Patients</h3>
                        <p class="stat-number" id="stat-total-patients">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="stat-card-wrapper">
                <div class="stat-card h-100">
                    <div class="stat-icon-bg bg-blue-light">
                        <i class="fas fa-calendar-alt text-blue-dark"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Today's Appointments</h3>
                        <p class="stat-number" id="stat-appointments-today">Loading...</p>
                        <small class="stat-subtitle text-gray" id="stat-appointments-breakdown">0 In-person &bull; 0 Telehealth</small>
                    </div>
                </div>
            </div>
            <div class="stat-card-wrapper">
                <div class="stat-card h-100">
                    <div class="stat-icon-bg bg-blue-light">
                        <i class="fas fa-video text-blue-dark"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Telehealth Today</h3>
                        <p class="stat-number" id="stat-telehealth-today">Loading...</p>
                        <small class="stat-subtitle text-gray" id="stat-telehealth-breakdown">0 Waiting &bull; 0 Joined &bull; 0 Completed</small>
                    </div>
                </div>
            </div>
            <div class="stat-card-wrapper">
                <div class="stat-card h-100">
                    <div class="stat-icon-bg bg-blue-light">
                        <i class="fas fa-clipboard-list text-blue-dark"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pending Patient Tasks</h3>
                        <p class="stat-number" id="stat-unread-messages">Loading...</p>
                        <small class="stat-subtitle text-gray">Forms / Follow-ups / Review</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doctor Appointments Chart -->
        <div class="chart-actions-row">
            <div class="dashboard-panel-col">
                <div class="dashboard-panel">
                    <div class="panel-header d-flex justify-content-between align-items-center">
                        <h2>
                            <i class="fas fa-chart-bar text-blue-dark"></i>
                            Doctor Appointments - Month Wise
                        </h2>
                        <div class="chart-filters">
                            <div class="dashboard-filter">
                                <i class="fas fa-user-md filter-icon"></i>
                                <select id="filterBarDoctor" class="dashboard-select">
                                    <option value="all">All Doctors</option>
                                </select>
                                <i class="fas fa-chevron-down filter-arrow"></i>
                            </div>
                            <div class="dashboard-filter year-filter">
                                <i class="far fa-calendar-alt filter-icon"></i>
                                <select id="filterBarYear" class="dashboard-select">
                                    <!-- Years will be loaded using JavaScript -->
                                </select>
                                <i class="fas fa-chevron-down filter-arrow"></i>
                            </div>
                            <div class="dashboard-filter">
                                <i class="fas fa-filter filter-icon"></i>
                                <select id="filterBarType" class="dashboard-select">
                                    <option value="all">All Types</option>
                                </select>
                                <i class="fas fa-chevron-down filter-arrow"></i>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body p-4">
                        <canvas id="appointmentsBarChart"></canvas>
                        <div id="appointmentsBarChartEmpty" class="text-gray text-center" style="display: none;">
                            <i class="fas fa-chart-bar mb-2"></i>
                            <br>
                            No data available for this year.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Column 1: Today's Appointments -->
            <div class="col-12 col-xl-6 mb-4">
                <div class="dashboard-panel appointments-panel h-100">
                    <div class="panel-header">
                        <h2><i class="fas fa-calendar-check"></i> Today's Appointments</h2>
                        <a href="#calendar" class="view-all-link">View Calendar &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-appointments-list">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Column 2: Recent Patient Activity -->
            <div class="col-12 col-xl-6 mb-4">
                <div class="dashboard-panel activity-panel h-100">
                    <div class="panel-header">
                        <h2><i class="fas fa-user-clock"></i> Recent Patient Activity</h2>
                        <a href="#patients" class="view-all-link">View All &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Date / Time</th>
                                    <th>Patient</th>
                                    <th>Action</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-recent-activity-list">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card quick-actions">
            <h2>Quick Clinical Actions</h2>
            <div class="actions-group">
                <a href="#patients" class="btn btn-primary" id="btn-dash-specialty-enc"><i class="fas fa-notes-medical" style="margin-right: 6px;"></i> New Encounter</a>
                <a href="#patients" class="btn btn-primary"><i class="fas fa-user-plus" style="margin-right: 6px;"></i> Register Patient</a>
                <a href="#calendar" class="btn btn-primary"><i class="fas fa-calendar-plus" style="margin-right: 6px;"></i> Schedule Appointment</a>
                <a href="#messaging" class="btn btn-primary"><i class="fas fa-comment-medical" style="margin-right: 6px;"></i> Send Message</a>
            </div>
        </div>
    </main>
</div>
