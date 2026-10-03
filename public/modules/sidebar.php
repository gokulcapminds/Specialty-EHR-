<?php
// public/modules/sidebar.php - Shared Sidebar Navigation Component
// Note: this fragment is fetched directly by the SPA router (public/js/router.js) and served
// as-is by Apache, bypassing index.php's bootstrap - so $_SESSION is never started here and
// can't be used for role checks. Role-based hiding of the Administration menu is done in
// public/js/app.js (updateSidebarProfile) using the /api/me response instead.
//
// Menu structure: Dashboard / Schedule > / Patient > / Messages / Billing / Reports / Settings >
// Groups are toggled by the top-level handler initSidebarGroups() in app.js (never inside an init*Handler).
// A group that contains the active page is rendered open. New pages go inside the matching group below.
if (!isset($activeNav)) {
    $activeNav = 'dashboard';
}

// Which $activeNav values belong to which group (used to open the group and highlight its parent).
$navGroups = [
    'schedule' => ['calendar', 'telehealth'],
    'patient'  => ['patients', 'encounters', 'diagnoses', 'medications', 'orders', 'referrals', 'recalls'],
    'settings' => ['settings', 'administration'],
];
$groupHasActive = function (string $group) use ($navGroups, $activeNav): bool {
    return in_array($activeNav, $navGroups[$group], true);
};

// A top-level link (no children).
$topLink = function (string $key, string $icon, string $label) use ($activeNav): void {
    $active = ($activeNav === $key) ? 'active' : '';
    echo '<li data-module="' . $key . '">'
       . '<a href="#' . $key . '" class="nav-link ' . $active . '" id="nav-' . $key . '" data-module="' . $key . '">'
       . '<i class="' . $icon . ' nav-icon"></i>'
       . '<span class="nav-text">' . $label . '</span>'
       . '<span class="sidebar-tooltip">' . $label . '</span>'
       . '</a></li>';
};

// A link inside an expandable group.
$subLink = function (string $key, string $label, ?string $id = null, ?string $href = null, string $extraAttrs = '') use ($activeNav): string {
    $active = ($activeNav === $key) ? ' active' : '';
    $id = $id ?? ('nav-' . $key);
    $href = $href ?? ('#' . $key);
    return '<li data-module="' . $key . '">'
         . '<a href="' . $href . '" class="nav-sublink' . $active . '" id="' . $id . '" data-module="' . $key . '"' . $extraAttrs . '>'
         . '<span class="nav-text">' . $label . '</span>'
         . '</a></li>';
};

// The parent row of an expandable group.
$groupParent = function (string $group, string $icon, string $label) use ($groupHasActive): void {
    $open = $groupHasActive($group);
    echo '<a href="#" role="button" class="nav-link nav-link-parent' . ($open ? ' has-active' : '') . '"'
       . ' id="nav-parent-' . $group . '" data-submenu-toggle="' . $group . '"'
       . ' aria-expanded="' . ($open ? 'true' : 'false') . '" aria-controls="submenu-' . $group . '">'
       . '<i class="' . $icon . ' nav-icon"></i>'
       . '<span class="nav-text">' . $label . '</span>'
       . '<i class="fas fa-chevron-right submenu-arrow nav-text"></i>'
       . '<span class="sidebar-tooltip">' . $label . '</span>'
       . '</a>';
};
?>
<nav class="sidebar" id="app-sidebar" aria-label="Main Navigation">
    <div class="sidebar-brand">
        <div class="brand-header-flex">
            <div class="brand-logo-emblem">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 3H15V9H21V15H15V21H9V15H3V9H9V3Z" fill="#ffffff"/>
                    <path d="M6 12H9L10.5 9.5L12 14.5L13.5 11L14.5 12H18" stroke="#0284c7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="brand-title-wrap">
                <h2 class="brand-title-text">Specialty EHR</h2>
                <span class="brand-subtitle-text">Clinical EHR Workspace</span>
            </div>
        </div>
    </div>

    <div class="sidebar-collapse-toggle">
        <button type="button" id="sidebar-toggle-btn" class="sidebar-toggle-btn" aria-label="Toggle sidebar">
            <i class="fas fa-angle-double-left" id="sidebar-toggle-icon"></i>
            <span class="sidebar-tooltip">Expand Sidebar</span>
        </button>
    </div>

    <ul class="nav-links">
        <?php $topLink('dashboard', 'fas fa-table-cells-large', 'Dashboard'); ?>

        <li class="nav-item-has-submenu <?= $groupHasActive('schedule') ? 'submenu-open' : '' ?>" id="nav-group-schedule" data-group="schedule">
            <?php $groupParent('schedule', 'far fa-calendar', 'Schedule'); ?>
            <ul class="nav-submenu" id="submenu-schedule">
                <?= $subLink('calendar', 'Calendar') ?>
                <?= $subLink('telehealth', 'Telehealth') ?>
            </ul>
        </li>

        <li class="nav-item-has-submenu <?= $groupHasActive('patient') ? 'submenu-open' : '' ?>" id="nav-group-patient" data-group="patient">
            <?php $groupParent('patient', 'fas fa-users', 'Patient'); ?>
            <ul class="nav-submenu" id="submenu-patient">
                <?= $subLink('patients', 'Patients') ?>
                <li data-module="encounters" id="nav-item-encounters">
                    <a href="#encounters" class="nav-sublink<?= ($activeNav === 'encounters') ? ' active' : '' ?>" id="nav-encounters" data-module="encounters"><span class="nav-text">Encounters</span></a>
                </li>
                <?= $subLink('diagnoses', 'Diagnoses &amp; Allergies') ?>
                <?= $subLink('medications', 'Medications') ?>
                <?= $subLink('orders', 'Orders &amp; Labs') ?>
                <?= $subLink('referrals', 'Referrals') ?>
                <?= $subLink('recalls', 'Recalls') ?>
            </ul>
        </li>

        <?php $topLink('messaging', 'far fa-comments', 'Messages'); ?>
        <?php $topLink('billing', 'far fa-credit-card', 'Billing'); ?>
        <?php $topLink('reports', 'far fa-file-lines', 'Audit &amp; Reports'); ?>

        <li class="nav-item-has-submenu <?= $groupHasActive('settings') ? 'submenu-open' : '' ?>" id="nav-group-settings" data-group="settings">
            <?php $groupParent('settings', 'fas fa-gear', 'Settings'); ?>
            <ul class="nav-submenu" id="submenu-settings">
                <?= $subLink('settings', 'System Settings') ?>
                <!-- Administration pages: every one requires Super Admin server-side, so updateSidebarProfile() hides this whole block for other roles. -->
                <li class="nav-subgroup<?= ($activeNav === 'administration') ? ' subgroup-open' : '' ?>" id="nav-parent-administration" data-module="administration" data-subgroup="administration">
                    <a href="#" role="button" class="nav-subgroup-toggle" data-subgroup-toggle="administration"
                       aria-expanded="<?= ($activeNav === 'administration') ? 'true' : 'false' ?>" aria-controls="subgroup-administration">
                        <span class="nav-text">Administration</span>
                        <i class="fas fa-chevron-down subgroup-arrow nav-text"></i>
                    </a>
                    <ul class="nav-subgroup-list" id="subgroup-administration">
                        <li data-module="admin_facility">
                            <a href="#administration?tab=facility" class="nav-sublink" id="nav-sub-facility" data-admin-tab="facility" data-module="admin_facility"><span class="nav-text">Facility Management</span></a>
                        </li>
                        <li data-module="admin_specialties">
                            <a href="#administration?tab=specialties" class="nav-sublink" id="nav-sub-specialties" data-admin-tab="specialties" data-module="admin_specialties"><span class="nav-text">Specialty Management</span></a>
                        </li>
                        <li data-module="admin_users">
                            <a href="#administration?tab=user-management" class="nav-sublink" id="nav-sub-user-management" data-admin-tab="user-management" data-module="admin_users"><span class="nav-text">User Management</span></a>
                        </li>
                        <li data-module="admin_roles">
                            <a href="#administration?tab=roles-permissions" class="nav-sublink" id="nav-sub-roles-permissions" data-admin-tab="roles-permissions" data-module="admin_roles"><span class="nav-text">Roles &amp; Permissions</span></a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-profile-card">
            <div class="user-avatar-circle">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-info-text">
                <span class="user-fullname" id="current-user-fullname">System Admin</span>
                <span class="user-role-title" id="user-role-badge">Super Admin</span>
            </div>
            <span class="sidebar-tooltip">System Admin</span>
        </div>
        <button class="btn btn-danger btn-block" id="logout-btn">
            <i class="fas fa-sign-out-alt logout-icon"></i>
            <span class="logout-text">Log Out</span>
            <span class="sidebar-tooltip tooltip-danger">Log Out</span>
        </button>
    </div>
</nav>
