<?php
// public/modules/topbar.php - Shared Notification Bell Header Component
?>
<!-- Notification Bell Icon Button -->
<div id="global-notification-bell-wrap" style="position: relative; display: inline-flex; align-items: center; margin-left: auto;">
    <button type="button" id="global-bell-btn" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #0f172a; padding: 7px 14px; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); cursor: pointer; transition: all 0.15s ease;" title="Notifications Center" onmouseover="this.style.borderColor='#0284c7'; this.style.background='#f0f9ff'" onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff'">
        <i class="fas fa-bell" style="color: #0284c7; font-size: 1.1rem;"></i>
        <span style="font-size: 0.88rem; color: #1e293b; font-weight: 700;">Notifications</span>
        <span id="global-bell-badge" style="display: none; position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 0.7rem; font-weight: 800; align-items: center; justify-content: center; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(239,68,68,0.4);">0</span>
    </button>
    
    <!-- Dropdown Menu -->
    <div id="global-notification-dropdown" style="display: none; position: absolute; top: 46px; right: 0; width: 400px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 12px 32px rgba(15,23,42,0.2); z-index: 99999; overflow: hidden; font-family: inherit;">
        <div style="background: linear-gradient(135deg, #0284c7, #0369a1); color: white; padding: 14px 18px; font-weight: 700; font-size: 0.95rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-bell"></i>
                <span>Notifications Center</span>
            </div>
            <span id="global-unread-count-badge" style="font-size: 0.76rem; background: rgba(255,255,255,0.25); padding: 3px 10px; border-radius: 12px; font-weight: 700;">0 Unread</span>
        </div>
        <!-- 3 Dedicated Category Filter Tabs with Unread Counts -->
        <div style="display: flex; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 4px 6px; gap: 4px;" id="global-notif-tabs">
            <button type="button" class="notif-tab-btn active" data-tab="all" style="flex: 1; padding: 7px 4px; border: none; background: transparent; font-size: 0.76rem; font-weight: 700; color: #0284c7; border-bottom: 2px solid #0284c7; cursor: pointer;">All (<span id="notif-cnt-all">0</span>)</button>
            <button type="button" class="notif-tab-btn" data-tab="intake" style="flex: 1; padding: 7px 4px; border: none; background: transparent; font-size: 0.76rem; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; cursor: pointer;">Intake (<span id="notif-cnt-intake">0</span>)</button>
            <button type="button" class="notif-tab-btn" data-tab="referral" style="flex: 1; padding: 7px 4px; border: none; background: transparent; font-size: 0.76rem; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; cursor: pointer;">Referrals (<span id="notif-cnt-referral">0</span>)</button>
            <button type="button" class="notif-tab-btn" data-tab="message" style="flex: 1; padding: 7px 4px; border: none; background: transparent; font-size: 0.76rem; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; cursor: pointer;">Messages (<span id="notif-cnt-message">0</span>)</button>
        </div>
        <div id="global-notification-list" style="max-height: 340px; overflow-y: auto; padding: 4px 0;">
            <div style="padding: 20px; text-align: center; color: #94a3b8; font-size: 0.88rem;"><i class="fas fa-spinner fa-spin" style="margin-right: 6px;"></i> Loading notifications...</div>
        </div>
        <div style="padding: 10px 16px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 0.76rem; color: #64748b;">Live EHR Alerts</span>
            <a href="javascript:void(0)" id="global-mark-all-read-btn" style="font-size: 0.8rem; font-weight: 700; color: #0284c7; text-decoration: none;">Mark All as Reviewed</a>
        </div>
    </div>
</div>
