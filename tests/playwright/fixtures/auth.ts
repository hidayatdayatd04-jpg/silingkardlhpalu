import { expect, test as base, type Page } from '@playwright/test';

/**
 * Auth fixture — logs in as admin and preserves session across tests.
 *
 * Usage:
 *   import { test, expect } from '../fixtures/auth';
 *   test('my test', async ({ adminPage }) => { ... });
 *
 * Environment variables:
 *   ADMIN_LOGIN    — username or email (default: "admin")
 *   ADMIN_PASSWORD — password           (default: "password")
 */

const ADMIN_LOGIN = process.env.ADMIN_LOGIN || 'superadmin';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'superadmin123';

type Fixtures = {
  adminPage: Page;
};

export const test = base.extend<Fixtures>({
  adminPage: async ({ page }, use) => {
    await page.goto('/admin/login');
    await page.fill('input[name="login"]', ADMIN_LOGIN);
    await page.fill('#password', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin', { timeout: 15_000 });
    await expect(page).not.toHaveURL(/admin\/login/);
    await use(page);
  },
});

export { expect } from '@playwright/test';
