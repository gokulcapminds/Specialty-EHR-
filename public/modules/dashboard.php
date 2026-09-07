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
                <a href="#patients" class="btn btn-primary">Register New Patient</a>
                <a href="#calendar" class="btn btn-primary">Schedule Appointment</a>
                <a href="#messaging" class="btn btn-primary">Send Secure Message</a>
            </div>
        </div>
    </main>
</div>
