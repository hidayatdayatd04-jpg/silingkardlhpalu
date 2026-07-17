import { expect, test } from '@playwright/test';

const ADMIN_LOGIN = process.env.ADMIN_LOGIN || 'superadmin';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'superadmin123';

async function login(page: import('@playwright/test').Page, username = ADMIN_LOGIN, password = ADMIN_PASSWORD) {
  await page.goto('/admin/login');
  await page.fill('input[name="login"]', username);
  await page.fill('#password', password);
  await page.click('button[type="submit"]');
}

test.describe('Auth admin', () => {
  test('login page, invalid credentials, valid login', async ({ page }) => {
    await page.goto('/admin/login');
    await expect(page.locator('#login')).toBeVisible();
    await expect(page.locator('#password')).toBeVisible();

    await login(page, 'invalid-user', 'wrong-password');
    await expect(page).toHaveURL(/admin\/login/);
    await expect(page.locator('.text-rose-600, .alert-danger, [class*="error"]').first()).toBeVisible();

    await login(page);
    await page.waitForURL('**/admin', { timeout: 15_000 });
    await expect(page).toHaveURL(/\/admin$/);
  });

  test('logout lewat POST AuthController', async ({ page }) => {
    await login(page);
    await page.waitForURL('**/admin', { timeout: 15_000 });

    await page.getByRole('button', { name: /Kepala Bidang DLH|Super Admin|Admin/i }).click();
    await page.getByRole('button', { name: /Keluar/i }).click();
    await page.waitForURL('**/admin/login', { timeout: 10_000 });
  });

  test('middleware redirect untuk guest', async ({ page }) => {
    await page.goto('/admin/artikel');
    await page.waitForURL('**/admin/login', { timeout: 10_000 });
  });
});

test.describe('admin.access allowedGroups', () => {
  test('superadmin dapat akses semua grup registry', async ({ page }) => {
    await login(page);
    await page.waitForURL('**/admin', { timeout: 15_000 });

    for (const slug of ['pengaduan-pengendalian', 'registrasi-usaha-lb3', 'sidak', 'artikel']) {
      const response = await page.goto(`/admin/${slug}`);
      expect(response?.status(), slug).toBeLessThan(400);
      await expect(page).not.toHaveURL(/admin\/login/);
    }
  });

  test('user bidang dibatasi sesuai allowedGroups()', async ({ page }) => {
    const loginName = process.env.PENGENDALIAN_LOGIN;
    const password = process.env.PENGENDALIAN_PASSWORD;
    test.skip(!loginName || !password, 'Set PENGENDALIAN_LOGIN dan PENGENDALIAN_PASSWORD untuk menguji role bidang.');

    await login(page, loginName!, password!);
    await page.waitForURL('**/admin', { timeout: 15_000 });

    expect((await page.goto('/admin/pengaduan-pengendalian'))?.status()).toBeLessThan(400);
    expect((await page.goto('/admin/registrasi-usaha-lb3'))?.status()).toBe(403);
  });
});
