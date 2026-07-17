# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: auth\login.spec.ts >> Auth admin >> logout lewat POST AuthController
- Location: tests\playwright\auth\login.spec.ts:28:3

# Error details

```
TimeoutError: page.waitForURL: Timeout 15000ms exceeded.
=========================== logs ===========================
waiting for navigation to "**/admin" until "load"
============================================================
```

# Page snapshot

```yaml
- main [ref=e2]:
  - generic [ref=e3]:
    - generic [ref=e5]:
      - img "Logo Kota Palu" [ref=e6]
      - generic [ref=e7]:
        - paragraph [ref=e8]: DLH Kota Palu
        - paragraph [ref=e9]: Ruang Kendali Operasional
    - generic [ref=e10]:
      - paragraph [ref=e11]: Admin Panel
      - heading "Lebih ringan, lebih rapi, langsung Blade + Tailwind." [level=1] [ref=e12]
      - paragraph [ref=e13]: Kelola permohonan, pengaduan, sampah, RTH, tata penataan, konten, dan pengguna dalam panel Blade yang cepat.
      - generic [ref=e14]:
        - generic [ref=e15]:
          - img [ref=e17]
          - paragraph [ref=e21]: 20+
          - paragraph [ref=e22]: Modul Data
        - generic [ref=e23]:
          - img [ref=e25]
          - paragraph [ref=e28]: Real-time
          - paragraph [ref=e29]: Monitoring
        - generic [ref=e30]:
          - img [ref=e32]
          - paragraph [ref=e35]: Akurat
          - paragraph [ref=e36]: Pelaporan
    - paragraph [ref=e37]: © 2026 Dinas Lingkungan Hidup Kota Palu
  - generic [ref=e39]:
    - generic [ref=e40]:
      - generic [ref=e41]:
        - img [ref=e43]
        - heading "Selamat Datang" [level=1] [ref=e47]
        - paragraph [ref=e48]: Masuk untuk mengakses ruang kendali admin DLH Kota Palu.
      - generic [ref=e49]:
        - generic [ref=e50]:
          - text: Username / Email
          - generic [ref=e51]:
            - generic:
              - img
            - textbox "Username / Email" [active] [ref=e52]:
              - /placeholder: admin@palu.go.id
              - text: admin
          - paragraph [ref=e53]:
            - img [ref=e54]
            - text: Username/email atau password tidak sesuai.
        - generic [ref=e56]:
          - text: Password
          - generic [ref=e57]:
            - generic:
              - img
            - textbox "Password" [ref=e58]:
              - /placeholder: ••••••••
            - button "Tampilkan password" [ref=e59]:
              - img [ref=e60]
        - generic [ref=e63]:
          - generic [ref=e64]:
            - checkbox "Ingat saya" [ref=e65]
            - text: Ingat saya
          - generic "Hubungi administrator untuk reset password" [ref=e66]: Lupa password?
        - button "Masuk ke Dashboard" [ref=e67]:
          - generic [ref=e68]: Masuk ke Dashboard
          - img [ref=e69]
    - paragraph [ref=e72]: © 2026 Dinas Lingkungan Hidup Kota Palu · Ruang Kendali Operasional
```

# Test source

```ts
  1  | import { expect, test } from '@playwright/test';
  2  | 
  3  | const ADMIN_LOGIN = process.env.ADMIN_LOGIN || 'admin';
  4  | const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'password';
  5  | 
  6  | async function login(page: import('@playwright/test').Page, username = ADMIN_LOGIN, password = ADMIN_PASSWORD) {
  7  |   await page.goto('/admin/login');
  8  |   await page.fill('#login', username);
  9  |   await page.fill('#password', password);
  10 |   await page.click('button[type="submit"]');
  11 | }
  12 | 
  13 | test.describe('Auth admin', () => {
  14 |   test('login page, invalid credentials, valid login', async ({ page }) => {
  15 |     await page.goto('/admin/login');
  16 |     await expect(page.locator('#login')).toBeVisible();
  17 |     await expect(page.locator('#password')).toBeVisible();
  18 | 
  19 |     await login(page, 'invalid-user', 'wrong-password');
  20 |     await expect(page).toHaveURL(/admin\/login/);
  21 |     await expect(page.locator('.text-rose-600, .alert-danger, [class*="error"]').first()).toBeVisible();
  22 | 
  23 |     await login(page);
  24 |     await page.waitForURL('**/admin', { timeout: 15_000 });
  25 |     await expect(page).toHaveURL(/\/admin$/);
  26 |   });
  27 | 
  28 |   test('logout lewat POST AuthController', async ({ page }) => {
  29 |     await login(page);
> 30 |     await page.waitForURL('**/admin', { timeout: 15_000 });
     |                ^ TimeoutError: page.waitForURL: Timeout 15000ms exceeded.
  31 | 
  32 |     await page.evaluate(() => {
  33 |       const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
  34 |       const form = document.createElement('form');
  35 |       form.method = 'POST';
  36 |       form.action = '/admin/logout';
  37 |       const input = document.createElement('input');
  38 |       input.type = 'hidden';
  39 |       input.name = '_token';
  40 |       input.value = token;
  41 |       form.appendChild(input);
  42 |       document.body.appendChild(form);
  43 |       form.submit();
  44 |     });
  45 | 
  46 |     await page.waitForURL('**/admin/login', { timeout: 10_000 });
  47 |   });
  48 | 
  49 |   test('middleware redirect untuk guest', async ({ page }) => {
  50 |     await page.goto('/admin/artikel');
  51 |     await page.waitForURL('**/admin/login', { timeout: 10_000 });
  52 |   });
  53 | });
  54 | 
  55 | test.describe('admin.access allowedGroups', () => {
  56 |   test('superadmin dapat akses semua grup registry', async ({ page }) => {
  57 |     await login(page);
  58 |     await page.waitForURL('**/admin', { timeout: 15_000 });
  59 | 
  60 |     for (const slug of ['pengaduan-pengendalian', 'registrasi-usaha-lb3', 'aset-rth', 'sidak', 'artikel']) {
  61 |       const response = await page.goto(`/admin/${slug}`);
  62 |       expect(response?.status(), slug).toBeLessThan(400);
  63 |       await expect(page).not.toHaveURL(/admin\/login/);
  64 |     }
  65 |   });
  66 | 
  67 |   test('user bidang dibatasi sesuai allowedGroups()', async ({ page }) => {
  68 |     const loginName = process.env.PENGENDALIAN_LOGIN;
  69 |     const password = process.env.PENGENDALIAN_PASSWORD;
  70 |     test.skip(!loginName || !password, 'Set PENGENDALIAN_LOGIN dan PENGENDALIAN_PASSWORD untuk menguji role bidang.');
  71 | 
  72 |     await login(page, loginName!, password!);
  73 |     await page.waitForURL('**/admin', { timeout: 15_000 });
  74 | 
  75 |     expect((await page.goto('/admin/pengaduan-pengendalian'))?.status()).toBeLessThan(400);
  76 |     expect((await page.goto('/admin/registrasi-usaha-lb3'))?.status()).toBe(403);
  77 |   });
  78 | });
  79 | 
  80 | 
```