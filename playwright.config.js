const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  timeout: 30000,
  expect: {
    timeout: 8000
  },
  fullyParallel: false,
  workers: 1,
  use: {
    baseURL: 'http://localhost/specialty_ehr/public',
    headless: true,
    viewport: { width: 1440, height: 900 },
    actionTimeout: 10000,
    screenshot: 'only-on-failure',
    video: 'retain-on-failure'
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    }
  ],
});
