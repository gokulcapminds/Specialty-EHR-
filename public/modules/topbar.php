<?php
// public/modules/topbar.php - Shared Notification Bell & Specialty Header Component
?>
<!-- Global Active Specialty Badge & Notification Wrap -->
<div style="display: inline-flex; align-items: center; gap: 12px; margin-left: auto;">
    <!-- Active Specialty Badge -->
    <div id="global-specialty-badge-wrap" style="display: inline-flex; align-items: center;">
        <div id="global-active-specialty-badge" style="background: #f0f9ff; border: 1.5px solid #bae6fd; color: #0369a1; padding: 7px 14px; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; font-size: 0.88rem; box-shadow: 0 1px 3px rgba(2,132,199,0.06);">
            <i class="fas fa-heartbeat" id="global-specialty-icon" style="color: #0284c7; font-size: 1rem;"></i>
            <span id="global-specialty-name">Cardiology EHR</span>
        </div>
    </div>

    <!-- Notification Bell Icon Button -->
    <div id="global-notification-bell-wrap" style="position: relative; display: flex; align-items: center; gap: 8px;">
        <button type="button" id="global-bell-btn" style="background: transparent; border: none; padding: 0; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: opacity 0.2s; outline: none;" title="Notifications Center" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
            <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C13.1 22 14 21.1 14 20H10C10 21.1 10.9 22 12 22ZM18 16V11C18 7.93 16.36 5.36 13.5 4.68V4C13.5 3.17 12.83 2.5 12 2.5C11.17 2.5 10.5 3.17 10.5 4V4.68C7.63 5.36 6 7.92 6 11V16L4 18V19H20V18L18 16Z" fill="#3b82f6"/>
                </svg>
                <span id="global-bell-badge" style="display: none; position: absolute; top: -3px; right: -2px; background: #ff4757; color: white; font-size: 10px; font-weight: 700; border-radius: 50px; padding: 0 4px; height: 16px; min-width: 16px; align-items: center; justify-content: center; border: 2px solid #ffffff; box-sizing: border-box; line-height: 1;">0</span>
            </div>
            <span style="color: #64748b; font-size: 13.5px; font-weight: 600;">Notifications</span>
        </button>

        <!-- Dropdown Menu -->
        <div id="global-notification-dropdown" style="display: none; position: absolute; top: 40px; right: 0; width: 400px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 12px 32px rgba(15,23,42,0.2); z-index: 99999; overflow: hidden; font-family: inherit;">
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
</div>
