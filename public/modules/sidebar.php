<?php
// public/modules/sidebar.php - Shared Sidebar Navigation Component
// Note: this fragment is fetched directly by the SPA router (public/js/router.js) and served
// as-is by Apache, bypassing index.php's bootstrap - so $_SESSION is never started here and
// can't be used for role checks. Role-based hiding of the Administration menu is done in
// public/js/app.js (updateSidebarProfile) using the /api/me response instead.
if (!isset($activeNav)) {
    $activeNav = 'dashboard';
}
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
        <li data-module="dashboard">
            <a href="#dashboard" class="nav-link <?= ($activeNav === 'dashboard') ? 'active' : '' ?>" id="nav-dashboard" data-module="dashboard">
                <i class="fas fa-globe nav-icon"></i> 
                <span class="nav-text">Dashboard</span>
                <span class="sidebar-tooltip">Dashboard</span>
            </a>
        </li>
        <li data-module="calendar">
            <a href="#calendar" class="nav-link <?= ($activeNav === 'calendar') ? 'active' : '' ?>" id="nav-calendar" data-module="calendar">
                <i class="fas fa-calendar-alt nav-icon"></i> 
                <span class="nav-text">Calendar</span>
                <span class="sidebar-tooltip">Calendar</span>
            </a>
        </li>
        <li data-module="patients">
            <a href="#patients" class="nav-link <?= ($activeNav === 'patients') ? 'active' : '' ?>" id="nav-patients" data-module="patients">
                <i class="fas fa-user nav-icon"></i> 
                <span class="nav-text">Patients</span>
                <span class="sidebar-tooltip">Patients</span>
            </a>
        </li>

        <li data-module="telehealth">
            <a href="#telehealth" class="nav-link <?= ($activeNav === 'telehealth') ? 'active' : '' ?>" id="nav-telehealth" data-module="telehealth">
                <i class="fas fa-video nav-icon"></i> 
                <span class="nav-text">Telehealth</span>
                <span class="sidebar-tooltip">Telehealth</span>
            </a>
        </li>
        <li data-module="messaging">
            <a href="#messaging" class="nav-link <?= ($activeNav === 'messaging') ? 'active' : '' ?>" id="nav-messaging" data-module="messaging">
                <i class="fas fa-comments nav-icon"></i> 
                <span class="nav-text">Messages</span>
                <span class="sidebar-tooltip">Messages</span>
            </a>
        </li>
        <li data-module="billing">
            <a href="#billing" class="nav-link <?= ($activeNav === 'billing') ? 'active' : '' ?>" id="nav-billing" data-module="billing">
                <i class="fas fa-file-invoice-dollar nav-icon"></i> 
                <span class="nav-text">Billing</span>
                <span class="sidebar-tooltip">Billing</span>
            </a>
        </li>
        <li data-module="referrals">
            <a href="#referrals" class="nav-link <?= ($activeNav === 'referrals') ? 'active' : '' ?>" id="nav-referrals" data-module="referrals">
                <i class="fas fa-user-md nav-icon"></i>
                <span class="nav-text">Referrals</span>
                <span class="sidebar-tooltip">Referrals</span>
            </a>
        </li>
        <li data-module="orders">
            <a href="#orders" class="nav-link <?= ($activeNav === 'orders') ? 'active' : '' ?>" id="nav-orders" data-module="orders">
                <i class="fas fa-flask nav-icon"></i>
                <span class="nav-text">Orders &amp; Labs</span>
                <span class="sidebar-tooltip">Orders &amp; Labs</span>
            </a>
        </li>
        <li data-module="medications">
            <a href="#medications" class="nav-link <?= ($activeNav === 'medications') ? 'active' : '' ?>" id="nav-medications" data-module="medications">
                <i class="fas fa-pills nav-icon"></i>
                <span class="nav-text">Medications</span>
                <span class="sidebar-tooltip">Medications</span>
            </a>
        </li>
        <li data-module="diagnoses">
            <a href="#diagnoses" class="nav-link <?= ($activeNav === 'diagnoses') ? 'active' : '' ?>" id="nav-diagnoses" data-module="diagnoses">
                <i class="fas fa-notes-medical nav-icon"></i>
                <span class="nav-text">Diagnoses &amp; Allergies</span>
                <span class="sidebar-tooltip">Diagnoses &amp; Allergies</span>
            </a>
        </li>
        <li data-module="recalls">
            <a href="#recalls" class="nav-link <?= ($activeNav === 'recalls') ? 'active' : '' ?>" id="nav-recalls" data-module="recalls">
                <i class="fas fa-clock-rotate-left nav-icon"></i> 
                <span class="nav-text">Recalls</span>
                <span class="sidebar-tooltip">Recalls</span>
            </a>
        </li>
        <li class="nav-item-has-submenu <?= ($activeNav === 'administration') ? 'submenu-open' : '' ?>" data-module="administration" id="nav-parent-administration">
            <a href="#administration" class="nav-link nav-link-parent <?= ($activeNav === 'administration') ? 'active' : '' ?>" id="nav-administration" data-submenu-toggle="admin-submenu" data-module="administration">
                <i class="fas fa-user-shield nav-icon"></i>
                <span class="nav-text">Administration</span>
                <i class="fas fa-chevron-down submenu-arrow nav-text"></i>
                <span class="sidebar-tooltip">Administration</span>
            </a>
            <ul class="nav-submenu" id="admin-submenu">
                <li data-module="admin_facility">
                    <a href="#administration?tab=facility" class="nav-sublink" id="nav-sub-facility" data-admin-tab="facility" data-module="admin_facility">
                        <i class="fas fa-hospital nav-icon"></i>
                        <span class="nav-text">Facility Management</span>
                    </a>
                </li>
                <li data-module="admin_specialties">
                    <a href="#administration?tab=specialties" class="nav-sublink" id="nav-sub-specialties" data-admin-tab="specialties" data-module="admin_specialties">
                        <i class="fas fa-stethoscope nav-icon"></i>
                        <span class="nav-text">Specialty Management</span>
                    </a>
                </li>
                <li data-module="admin_users">
                    <a href="#administration?tab=user-management" class="nav-sublink" id="nav-sub-user-management" data-admin-tab="user-management" data-module="admin_users">
                        <i class="fas fa-users-cog nav-icon"></i>
                        <span class="nav-text">User Management</span>
                    </a>
                </li>
                <li data-module="admin_roles">
                    <a href="#administration?tab=roles-permissions" class="nav-sublink" id="nav-sub-roles-permissions" data-admin-tab="roles-permissions" data-module="admin_roles">
                        <i class="fas fa-shield-alt nav-icon"></i>
                        <span class="nav-text">Roles &amp; Permissions</span>
                    </a>
                </li>
            </ul>
        </li>
        <li data-module="reports">
            <a href="#reports" class="nav-link <?= ($activeNav === 'reports') ? 'active' : '' ?>" id="nav-reports" data-module="reports">
                <i class="fas fa-chart-line nav-icon"></i> 
                <span class="nav-text">Audit & Reports</span>
                <span class="sidebar-tooltip">Audit & Reports</span>
            </a>
        </li>
        <li data-module="settings">
            <a href="#settings" class="nav-link <?= ($activeNav === 'settings') ? 'active' : '' ?>" id="nav-settings" data-module="settings">
                <i class="fas fa-cog nav-icon"></i> 
                <span class="nav-text">Settings</span>
                <span class="sidebar-tooltip">Settings</span>
            </a>
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
