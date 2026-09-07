<!-- public/modules/reports.php --><link rel="stylesheet" href="css/modules/reports.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'reports'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h1>HIPAA Compliance Reports</h1>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <div class="card">
            <h2>Immutable Security Audit Log</h2>
            <p class="reports-notice">
                The table below displays the real-time access trail logs of this health system. In compliance with HIPAA guidelines, these records are immutable. A SHA-256 cryptographic chain prevents deletion or editing of access lines.
            </p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Clinician</th>
                            <th>Role</th>
                            <th>Patient ID</th>
                            <th>Action Type</th>
                            <th>Module</th>
                            <th>IP Address</th>
                            <th>Integrity Hash</th>
                        </tr>
                    </thead>
                    <tbody id="audit-trail-list">
                        <tr>
                            <td colspan="8">Loading compliance log...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
