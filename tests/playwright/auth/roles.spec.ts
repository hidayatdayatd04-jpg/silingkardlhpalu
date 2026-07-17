import { expect, test, type Page } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { fillAdminForm, submitAndWait, ensureUploadFixtures } from '../fixtures/form';

/**
 * Role-based CRUD tests for all 4 bidang accounts.
 * Each role should only access their group's resources.
 */

const roles = [
  {
    name: 'Bidang Pengendalian',
    username: 'pengendalian',
    password: 'pengendalian123',
    allowedSlugs: ['pengaduan-pengendalian', 'permohonan-rekomendasi', 'pengajuan-rintek-pertek'],
    deniedSlugs: ['registrasi-usaha-lb3', 'sidak', 'artikel'],
  },
  {
    name: 'Bidang Sampah & LB3',
    username: 'sampah-lb3',
    password: 'sampah123',
    allowedSlugs: ['registrasi-usaha-lb3', 'jenis-lb3', 'titik-tpa', 'titik-tpst', 'titik-tps', 'bank-sampah', 'jadwal-armada', 'statistik-sampah'],
    deniedSlugs: ['pengaduan-pengendalian', 'sidak', 'artikel'],
  },
  {
    name: 'Bidang Tata Penataan',
    username: 'tata-penataan',
    password: 'tata123',
    allowedSlugs: ['pengaduan-tata-penataan', 'objek-pengawasan', 'sidak', 'pelanggaran', 'sanksi', 'sosialisasi'],
    deniedSlugs: ['pengaduan-pengendalian', 'registrasi-usaha-lb3', 'artikel'],
  },
  {
    name: 'Bidang RTH',
    username: 'rth',
    password: 'rth123',
    allowedSlugs: ['perizinan-tebang-pohon', 'pinjam-taman', 'data-tanam-pohon'],
    deniedSlugs: ['pengaduan-pengendalian', 'registrasi-usaha-lb3', 'sidak', 'artikel'],
  },
];

// Load resource definitions from AdminRegistry
function loadResources() {
  const script = `
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
$groups = App\\Support\\Admin\\AdminRegistry::all();
$out = [];
foreach ($groups as $groupKey => $group) {
    $resources = [];
    foreach ($group['items'] as $item) {
        $item['group'] = $groupKey;
        $item['fields'] = App\\Support\\Admin\\AdminRegistry::formFields($item);
        $item['model'] = class_basename($item['model']);
        $resources[] = $item;
    }
    $out[] = ['key' => $groupKey, 'label' => $group['label'], 'resources' => $resources];
}
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
`;
  const output = execFileSync('php', ['-r', script], {
    cwd: process.cwd(),
    encoding: 'utf8',
    env: { ...process.env, APP_ENV: process.env.APP_ENV ?? 'testing' },
  });
  return JSON.parse(output) as Array<{
    key: string;
    label: string;
    resources: Array<{
      slug: string;
      label: string;
      group: string;
      columns: string[];
      filters: Record<string, Record<string, string>>;
      fields: Array<{
        name: string;
        label: string;
        type: string;
        options: Record<string, string>;
        required?: boolean;
        readonly?: boolean;
        hide_on_create?: boolean;
      }>;
    }>;
  }>;
}

const allGroups = loadResources();

function findResource(slug: string) {
  for (const group of allGroups) {
    const found = group.resources.find((r) => r.slug === slug);
    if (found) return found;
  }
  return undefined;
}

function firstEditableField(resource: { fields: any[] }) {
  const textField = resource.fields.find(
    (f: any) => !f.name.startsWith('_section_') && !f.readonly && !f.hide_on_create && ['text', 'textarea', 'email', 'tel'].includes(f.type),
  );
  if (textField) return textField;
  return resource.fields.find(
    (f: any) => !f.name.startsWith('_section_') && !f.readonly && !f.hide_on_create && f.type === 'number',
  );
}

async function loginAs(page: Page, username: string, password: string) {
  await page.goto('/admin/login');
  await page.fill('#login', username);
  await page.fill('#password', password);
  await page.click('button[type="submit"]');
  await page.waitForURL('**/admin', { timeout: 15_000 });
}

// ── Login tests ─────────────────────────────────────────────
for (const role of roles) {
  test.describe(`Login ${role.name} (${role.username})`, () => {
    test(`login berhasil dan dashboard tampil`, async ({ page }) => {
      await loginAs(page, role.username, role.password);
      await expect(page).toHaveURL(/\/admin$/);
      await expect(page.locator('body')).toContainText(/Dashboard/i);
    });

    test(`login gagal dengan password salah`, async ({ page }) => {
      await page.goto('/admin/login');
      await page.fill('#login', role.username);
      await page.fill('#password', 'wrongpassword');
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(/admin\/login/);
    });

    test(`logout berfungsi`, async ({ page }) => {
      await loginAs(page, role.username, role.password);
      await page.waitForURL('**/admin', { timeout: 15_000 });

      // Click user dropdown
      await page.getByRole('button', { name: /Kepala Bidang|Bidang|Admin/i }).click();
      await page.getByRole('button', { name: /Keluar/i }).click();
      await page.waitForURL('**/admin/login', { timeout: 10_000 });
    });
  });
}

// ── Access control tests ────────────────────────────────────
for (const role of roles) {
  test.describe(`Akses ${role.name}`, () => {
    test(`bisa akses resource yang diizinkan`, async ({ page }) => {
      await loginAs(page, role.username, role.password);
      await page.waitForURL('**/admin', { timeout: 15_000 });

      for (const slug of role.allowedSlugs.slice(0, 3)) { // Test first 3 to save time
        const response = await page.goto(`/admin/${slug}`);
        expect(response?.status(), `${slug} should be accessible`).toBeLessThan(400);
        await expect(page).not.toHaveURL(/admin\/login/);
      }
    });

    test(`ditolak akses resource yang TIDAK diizinkan (403)`, async ({ page }) => {
      await loginAs(page, role.username, role.password);
      await page.waitForURL('**/admin', { timeout: 15_000 });

      for (const slug of role.deniedSlugs.slice(0, 2)) { // Test first 2
        const response = await page.goto(`/admin/${slug}`);
        expect(response?.status(), `${slug} should return 403 for ${role.username}`).toBe(403);
      }
    });
  });
}

// ── CRUD tests per role ─────────────────────────────────────
for (const role of roles) {
  test.describe(`CRUD ${role.name}`, () => {
    // Test CRUD for first allowed resource only (to keep test time reasonable)
    const testSlug = role.allowedSlugs[0];
    const resource = findResource(testSlug);

    test.skip(!resource, `Resource ${testSlug} not found`);

    test(`index — ${resource!.label}`, async ({ page }) => {
      await loginAs(page, role.username, role.password);
      await page.waitForURL('**/admin', { timeout: 15_000 });

      await page.goto(`/admin/${testSlug}`);
      await expect(page).not.toHaveURL(/admin\/login/);
      await expect(page.locator('h1, h2').first()).toContainText(resource!.label);
      await expect(page.locator('input[name="q"]')).toBeVisible();
      await expect(page.locator('body')).toContainText(/Total \d+ data/);
    });

    test(`create — ${resource!.label}`, async ({ page }) => {
      await loginAs(page, role.username, role.password);
      await page.waitForURL('**/admin', { timeout: 15_000 });

      await page.goto(`/admin/${testSlug}/create`);
      await expect(page).not.toHaveURL(/admin\/login/);

      const filled = await fillAdminForm(page, resource!);
      await submitAndWait(page, new RegExp(`/admin/${testSlug}/\\d+`));
      // Should land on show page or index
      expect(page.url()).toContain(`/admin/${testSlug}`);
    });

    test(`edit — ${resource!.label}`, async ({ page }) => {
      await loginAs(page, role.username, role.password);
      await page.waitForURL('**/admin', { timeout: 15_000 });

      await page.goto(`/admin/${testSlug}`);
      const editBtn = page.locator('a[title="Edit"]').first();
      test.skip((await editBtn.count()) === 0, `No records to edit in ${testSlug}`);

      await editBtn.click();
      await expect(page).toHaveURL(new RegExp(`/admin/${testSlug}/\\d+/edit`));

      const field = firstEditableField(resource!);
      if (field) {
        const isNumeric = field.type === 'number';
        const newValue = isNumeric
          ? String(Math.floor(Math.random() * 900) + 100)
          : `E2E role update ${Date.now().toString().slice(-6)}`;
        await page.locator(`[name="${field.name}"]`).first().fill('');
        await page.locator(`[name="${field.name}"]`).first().fill(newValue);
        await submitAndWait(page, new RegExp(`/admin/${testSlug}/\\d+`));
      }
    });

    test(`export — ${resource!.label}`, async ({ page }) => {
      await loginAs(page, role.username, role.password);
      await page.waitForURL('**/admin', { timeout: 15_000 });

      for (const path of ['export', 'export-all']) {
        const response = await page.request.get(`/admin/${testSlug}/${path}`);
        expect(response.status(), `${testSlug}/${path}`).toBeLessThan(500);
      }
    });

    test(`delete — ${resource!.label}`, async ({ page }) => {
      await loginAs(page, role.username, role.password);
      await page.waitForURL('**/admin', { timeout: 15_000 });

      await page.goto(`/admin/${testSlug}`);
      const deleteBtn = page.locator('button[title="Hapus"]').first();
      test.skip((await deleteBtn.count()) === 0, `No records to delete in ${testSlug}`);

      await deleteBtn.click();
      await expect(page.getByText('Konfirmasi Hapus')).toBeVisible();
      await page.getByRole('button', { name: /Ya, Hapus Data/i }).click();
      await page.waitForURL(new RegExp(`/admin/${testSlug}`), { timeout: 15_000 });
    });
  });
}
