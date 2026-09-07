<?php
// public/modules/sidebar.php - Shared Sidebar Navigation Component
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

    <ul class="nav-links">
        <li>
            <a href="#dashboard" class="nav-link <?= ($activeNav === 'dashboard') ? 'active' : '' ?>" id="nav-dashboard">
                <i class="fas fa-globe nav-icon"></i> 
                <span class="nav-text">Dashboard</span>
                <span class="sidebar-tooltip">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="#calendar" class="nav-link <?= ($activeNav === 'calendar') ? 'active' : '' ?>" id="nav-calendar">
                <i class="fas fa-calendar-alt nav-icon"></i> 
                <span class="nav-text">Calendar</span>
                <span class="sidebar-tooltip">Calendar</span>
            </a>
        </li>
        <li>
            <a href="#patients" class="nav-link <?= ($activeNav === 'patients') ? 'active' : '' ?>" id="nav-patients">
                <i class="fas fa-user nav-icon"></i> 
                <span class="nav-text">Patients</span>
                <span class="sidebar-tooltip">Patients</span>
            </a>
        </li>

        <li>
            <a href="#telehealth" class="nav-link <?= ($activeNav === 'telehealth') ? 'active' : '' ?>" id="nav-telehealth">
                <i class="fas fa-video nav-icon"></i> 
                <span class="nav-text">Telehealth</span>
                <span class="sidebar-tooltip">Telehealth</span>
            </a>
        </li>
        <li>
            <a href="#messaging" class="nav-link <?= ($activeNav === 'messaging') ? 'active' : '' ?>" id="nav-messaging">
                <i class="fas fa-comments nav-icon"></i> 
                <span class="nav-text">Messages</span>
                <span class="sidebar-tooltip">Messages</span>
            </a>
        </li>
        <li>
            <a href="#billing" class="nav-link <?= ($activeNav === 'billing') ? 'active' : '' ?>" id="nav-billing">
                <i class="fas fa-file-invoice-dollar nav-icon"></i> 
                <span class="nav-text">Billing</span>
                <span class="sidebar-tooltip">Billing</span>
            </a>
        </li>
        <li>
            <a href="#referrals" class="nav-link <?= ($activeNav === 'referrals') ? 'active' : '' ?>" id="nav-referrals">
                <i class="fas fa-user-md nav-icon"></i> 
                <span class="nav-text">Referrals</span>
                <span class="sidebar-tooltip">Referrals</span>
            </a>
        </li>
        <li>
            <a href="#recalls" class="nav-link <?= ($activeNav === 'recalls') ? 'active' : '' ?>" id="nav-recalls">
                <i class="fas fa-clock-rotate-left nav-icon"></i> 
                <span class="nav-text">Recalls</span>
                <span class="sidebar-tooltip">Recalls</span>
            </a>
        </li>
        <li>
            <a href="#administration" class="nav-link <?= ($activeNav === 'administration') ? 'active' : '' ?>" id="nav-administration">
                <i class="fas fa-user-shield nav-icon"></i> 
                <span class="nav-text">Administration</span>
                <span class="sidebar-tooltip">Administration</span>
            </a>
        </li>
        <li>
            <a href="#reports" class="nav-link <?= ($activeNav === 'reports') ? 'active' : '' ?>" id="nav-reports">
                <i class="fas fa-chart-line nav-icon"></i> 
                <span class="nav-text">Audit & Reports</span>
                <span class="sidebar-tooltip">Audit & Reports</span>
            </a>
        </li>
        <li>
            <a href="#settings" class="nav-link <?= ($activeNav === 'settings') ? 'active' : '' ?>" id="nav-settings">
                <i class="fas fa-cog nav-icon"></i> 
                <span class="nav-text">Settings</span>
                <span class="sidebar-tooltip">Settings</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-collapse-toggle">
        <button type="button" id="sidebar-toggle-btn" class="sidebar-toggle-btn" aria-label="Toggle sidebar">
            <i class="fas fa-angle-double-left" id="sidebar-toggle-icon"></i>
            <span class="sidebar-tooltip">Expand Sidebar</span>
        </button>
    </div>

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
