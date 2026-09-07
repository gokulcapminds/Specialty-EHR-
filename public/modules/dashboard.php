<!-- public/modules/dashboard.php --><link rel="stylesheet" href="css/modules/dashboard.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'dashboard'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h1 id="workspace-title">Workspace Dashboard</h1>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- Dynamic Statistics Widget Area -->
        <div class="grid-stats">
            <div class="card stat-card">
                <h3>Total Patients</h3>
                <p class="stat-number" id="stat-total-patients">Loading...</p>
            </div>
            <div class="card stat-card">
                <h3>Appointments Today</h3>
                <p class="stat-number" id="stat-appointments-today">Loading...</p>
            </div>
            <div class="card stat-card">
                <h3>Unread Messages</h3>
                <p class="stat-number" id="stat-unread-messages">Loading...</p>
            </div>
            <div class="card stat-card">
                <h3>Claims Ledger</h3>
                <p class="stat-number" id="stat-open-claims">Loading...</p>
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
