<!-- public/modules/administration.php --><link rel="stylesheet" href="css/modules/administration.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'administration'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h1>Administration Workspace</h1>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <div class="card">
            <div class="mod-administration-style-1">
                <h2 class="mod-administration-style-2">RBAC Permission Policies Matrix</h2>
                <button class="btn btn-primary mod-administration-style-3" id="save-rbac-policies-btn">
                    <i class="fas fa-save mod-administration-style-4"></i> Save RBAC Policies
                </button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Encounter Workspace</th>
                            <th>Demographics Directory</th>
                            <th>Billing Claims</th>
                            <th>Audit Trail Logs</th>
                        </tr>
                    </thead>
                    <tbody id="rbac-policies-list">
                        <tr>
                            <td colspan="5">Loading RBAC permission matrix...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mod-administration-style-5" id="user-management-card">
            <div class="mod-administration-style-6">
                <h2 class="mod-administration-style-2">User Management Directory</h2>
                <button class="btn btn-primary mod-administration-style-7" id="add-user-btn">+ Add New User</button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Specialty</th>
                            <th>Created Date</th>
                            <th>Last Login</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="administration-users-list">
                        <tr>
                            <td colspan="8">Loading users directory...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="administration-pagination"></div>
        </div>

    </main>
</div>
