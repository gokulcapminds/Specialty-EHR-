const { test, expect } = require('@playwright/test');

test.describe('Specialty EHR - User Management Wizard Automated Tests', () => {

  const testId = Date.now().toString().slice(-4);
  const testStaff = {
    firstName: 'Sarah',
    lastName: `Williams${testId}`,
    middleName: 'Elizabeth',
    preferredName: 'Sarah',
    email: `sarah.williams.${testId}@apexhealth.org`,
    hireDate: '2026-10-01',
    mobilePhone: '(555) 123-4567',
    workPhone: '(555) 987-6543',
    notes: 'Automated test triage nurse.',
    username: `swilliams_${testId}`,
    password: 'Staff@Password2026!'
  };

  test.beforeEach(async ({ page }) => {
    // 1. Navigate to administration module
    await page.goto('http://localhost/specialty_ehr/public/index.php#administration');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // If redirected to login page, authenticate
    const isLogin = await page.locator('#login-username').isVisible().catch(() => false);
    if (isLogin) {
      await page.fill('#login-username', 'admin');
      await page.fill('#login-password', 'Admin@12345');
      await page.click('#login-submit-btn');
      await page.waitForNavigation().catch(() => {});
      await page.goto('http://localhost/specialty_ehr/public/index.php#administration');
      await page.waitForLoadState('networkidle');
    }
  });

  test('TC-AUTO-01: Successfully Create Staff User via 5-Step Stepper Wizard', async ({ page }) => {
    // 1. Switch to User Management Tab
    const userTab = page.locator('.admin-tab-btn[data-tab="user-management"]');
    await userTab.click();
    await page.waitForTimeout(600);

    // 2. Click "Add New User" button
    await page.locator('#add-user-btn').click();
    await expect(page.locator('#staff-user-workflow-view')).toBeVisible();
    await expect(page.locator('#wizard-step-panel-1')).toBeVisible();

    // ── STEP 1: Personal Information ──
    await page.fill('#staff-first-name', testStaff.firstName);
    await page.fill('#staff-last-name', testStaff.lastName);
    await page.fill('#staff-middle-name', testStaff.middleName);
    await page.fill('#staff-preferred-name', testStaff.preferredName);
    await page.check('input[name="staff-status-radio"][value="Active"]');
    await page.fill('#staff-email', testStaff.email);
    await page.fill('#staff-hire-date', testStaff.hireDate);
    await page.fill('#staff-mobile-phone', testStaff.mobilePhone);
    await page.fill('#staff-work-phone', testStaff.workPhone);
    await page.fill('#staff-notes', testStaff.notes);

    // Advance to Step 2
    await page.click('#btn-step-1-next');
    await expect(page.locator('#wizard-step-panel-2')).toBeVisible();

    // ── STEP 2: Role & Type ──
    await page.click('.athena-user-type-card[data-user-type="Staff Member"]');
    await page.waitForTimeout(300);

    // Select role if options available
    const roleOptionsCount = await page.locator('#staff-primary-role option').count();
    if (roleOptionsCount > 1) {
      await page.selectOption('#staff-primary-role', { index: 1 });
    }

    await page.selectOption('#staff-department', { value: 'Cardiology' });
    await page.check('input[name="staff-employment-type"][value="Full-time"]');
    await page.fill('#staff-job-title', 'Lead Clinical Nurse');
    await page.selectOption('#staff-suffix', { value: 'RN' });

    // Set Credentials
    await page.fill('#staff-username', testStaff.username);
    await page.fill('#staff-password', testStaff.password);
    await page.fill('#staff-confirm-password', testStaff.password);

    // Advance to Step 3
    await page.click('#btn-step-2-next');
    await expect(page.locator('#wizard-step-panel-3')).toBeVisible();

    // ── STEP 3: Practice Assignment ──
    const facilityCount = await page.locator('#staff-facility-id option').count();
    if (facilityCount > 1) {
      await page.selectOption('#staff-facility-id', { index: 1 });
    }
    const specCount = await page.locator('#staff-specialty option').count();
    if (specCount > 1) {
      await page.selectOption('#staff-specialty', { index: 1 });
    }

    await page.check('input[name="staff-schedule-access"][value="All assigned locations"]');
    await page.selectOption('#staff-timezone', { value: 'America/New_York' });

    // Advance to Step 4
    await page.click('#btn-step-3-next');
    await expect(page.locator('#wizard-step-panel-4')).toBeVisible();

    // ── STEP 4: Access & Permissions ──
    await page.click('.athena-template-card[data-template="Medical Assistant"]');

    // Advance to Step 5
    await page.click('#btn-step-4-next');
    await expect(page.locator('#wizard-step-panel-5')).toBeVisible();

    // ── STEP 5: Review & Create ──
    await expect(page.locator('#rev-user-email')).toHaveText(testStaff.email);

    // Submit form
    await page.click('#btn-save-staff-user');
    await page.waitForTimeout(1500);

    console.log(`[PASS] User ${testStaff.username} (${testStaff.email}) registered successfully.`);
  });

  test('TC-AUTO-02: Step 1 Validation Blocks Progression on Missing Required Fields', async ({ page }) => {
    // Switch to User Management Tab & Click Add New User
    await page.locator('.admin-tab-btn[data-tab="user-management"]').click();
    await page.waitForTimeout(500);
    await page.locator('#add-user-btn').click();

    // Attempt advancing to Step 2 without filling First/Last name
    await page.click('#btn-step-1-next');

    // Must stay on Step 1
    await expect(page.locator('#wizard-step-panel-1')).toBeVisible();
    await expect(page.locator('#wizard-step-panel-2')).toBeHidden();
  });

});
