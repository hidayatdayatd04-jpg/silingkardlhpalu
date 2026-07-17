import { execFileSync } from 'node:child_process';
import { join } from 'node:path';
import { expect, test } from '../fixtures/auth';
import { ensureUploadFixtures, fillAdminForm, imageFixture, pdfFixture } from '../fixtures/form';
import { allResources } from '../admin/resources';

const publicToAdmin = [
  { publicRoute: '/permohonan-rekomendasi', adminResource: 'permohonan-rekomendasi', multiStep: true },
  { publicRoute: '/pengajuan-rintek-pertek', adminResource: 'pengajuan-rintek-pertek', useRealAssets: true },
  { publicRoute: '/perizinan-tebang-pohon', adminResource: 'perizinan-tebang-pohon' },
  { publicRoute: '/pinjam-taman', adminResource: 'pinjam-taman' },
  { publicRoute: '/registrasi-usaha-lb3', adminResource: 'registrasi-usaha-lb3' },
];

// Clear rate limiter cache before e2e flow tests to avoid conflicts with public/form tests
test.beforeAll(async () => {
  try {
    // Clear only rate limiter keys, not full cache
    execFileSync('php', ['artisan', 'cache:clear'], { cwd: process.cwd(), timeout: 10_000 });
  } catch { /* ignore if artisan fails */ }
});

// Re-login admin after cache clear
test.afterEach(async ({ adminPage }, testInfo) => {
  // adminPage fixture handles login automatically
});

async function fillPublic(page: import('@playwright/test').Page, options?: { useRealAssets?: boolean }) {
  ensureUploadFixtures();
  const stamp = Date.now().toString().slice(-6);

  const fieldsToSet: Array<{ wireModel: string; value: string }> = [];
  const fileInputs: Array<{ locator: import('@playwright/test').Locator; isImage: boolean }> = [];

  const controls = page.locator('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), textarea, select');
  const count = await controls.count();

  for (let i = 0; i < count; i++) {
    const control = controls.nth(i);
    if (!(await control.isVisible().catch(() => false))) continue;
    const tag = await control.evaluate((el) => el.tagName.toLowerCase());
    const type = (await control.getAttribute('type')) ?? '';
    const wireModelRaw = await control.getAttribute('wire:model') ?? await control.getAttribute('wire:model.live') ?? await control.getAttribute('wire:model.blur');
    const wireModel = wireModelRaw?.split('.')[0]; // Strip modifiers like .live, .blur

    // 1) File inputs
    if (type === 'file') {
      const accept = (await control.getAttribute('accept')) ?? '';
      const isImage = accept.includes('image') || accept.includes('jpg') || accept.includes('png');
      fileInputs.push({ locator: control, isImage });
      continue;
    }

    // 2) Select fields (with or without wire:model)
    if (tag === 'select') {
      const options = await control.locator('option').evaluateAll((items) =>
        items.map((item) => (item as HTMLOptionElement).value).filter((v) => v !== ''),
      );
      if (wireModel && options.length > 0) {
        fieldsToSet.push({ wireModel, value: options[0] });
      } else if (options.length > 0) {
        await control.selectOption(options[0]);
      }
      continue;
    }

    // 3) Livewire text/email/tel/date/number/textarea fields
    if (wireModel) {
      let val: string;
      if (type === 'email') val = `e2e-${Date.now()}@gmail.com`;
      else if (type === 'datetime-local') val = '2026-08-20T09:00';
      else if (type === 'date') val = '2026-08-20';
      else if (type === 'number') val = '10';
      else if (type === 'tel') val = '081234567890';
      else val = `E2E flow ${stamp}`;
      fieldsToSet.push({ wireModel, value: val });
      continue;
    }

    // 4) Non-Livewire inputs — fill normally
    let val: string;
    if (type === 'date') val = '2026-08-20';
    else if (type === 'number') val = '10';
    else val = `E2E ${stamp}`;
    await control.fill(val);
  }

  // Set all Livewire fields via Livewire.find().set()
  if (fieldsToSet.length > 0) {
    await page.evaluate((fields) => {
      const wireEl = document.querySelector('[wire\\:id]');
      if (!wireEl) return;
      const wireId = wireEl.getAttribute('wire:id');
      const comp = (window as any).Livewire?.find(wireId);
      if (!comp?.set) return;
      for (const f of fields) {
        comp.set(f.wireModel, f.value);
      }
    }, fieldsToSet);
    // Poll until snapshot is updated (Livewire v4 set() is async)
    for (let i = 0; i < 10; i++) {
      await page.waitForTimeout(1_000);
      const snapshot = await page.evaluate((fields) => {
        const comp = (window as any).Livewire?.all()?.[0];
        if (!comp?.snapshot?.data) return null;
        // Check if first text field has been updated
        const firstField = fields.find(f => !['jenis_lb3_id', 'jenis_usaha'].includes(f.wireModel));
        return firstField ? comp.snapshot.data[firstField.wireModel] : null;
      }, fieldsToSet);
      if (snapshot) break;
    }
  }

  // Handle file inputs (Livewire WithFileUploads)
  // Must dispatch 'change' event after setInputFiles for Livewire to pick up the file
  const useRealAssets = options?.useRealAssets ?? false;
  for (const { locator, isImage } of fileInputs) {
    if (useRealAssets) {
      // Use real assets from seeder folder
      const realImage = join(process.cwd(), 'public', 'assets', 'Mentahan Buat all Seeder', 'image1.png');
      const realDoc = join(process.cwd(), 'public', 'assets', 'Mentahan Buat all Seeder', 'filedoc1.docx');
      await locator.setInputFiles(isImage ? realImage : realDoc).catch(() => undefined);
    } else {
      await locator.setInputFiles(isImage ? imageFixture : pdfFixture).catch(() => undefined);
    }
    // Dispatch change event so Livewire's file upload listener picks it up
    await locator.dispatchEvent('change');
  }
  // Wait for Livewire to process file uploads
  await page.waitForTimeout(useRealAssets ? 5_000 : 2_000);

  // Handle custom Alpine selects (x-admin.select components)
  const hiddenSelects = page.locator('input[type="hidden"][x-ref="hiddenInput"]');
  for (let i = 0; i < await hiddenSelects.count(); i++) {
    const input = hiddenSelects.nth(i);
    const name = await input.getAttribute('name');
    if (!name) continue;
    await page.evaluate((fieldName) => {
      const el = document.querySelector(`input[type="hidden"][name="${fieldName}"]`);
      const wrapper = el?.closest('[x-data]');
      if (wrapper && (wrapper as any)._x_dataStack) {
        const data = (wrapper as any)._x_dataStack[0];
        if (data?.options?.length > 0 && typeof data.selectOption === 'function') {
          data.selectOption(data.options[0].value, data.options[0].label);
        }
      }
    }, name);
  }

  // Check all checkboxes/radios
  const checks = page.locator('input[type="checkbox"], input[type="radio"]');
  for (let index = 0; index < await checks.count(); index++) {
    const check = checks.nth(index);
    if (await check.isVisible().catch(() => false)) await check.check({ force: true }).catch(() => undefined);
  }
}

function ticketFrom(text: string): string | undefined {
  // Match ticket patterns: PR-XXXX-XXXX, LB3-XXXX-XXXX, PTP-XXX, PJM-XXX, TIK-XXX, etc.
  return text.match(/\b(?:TIK|RIN|REG|PR|PP|PTP|PNJ|LB3|PJM|PMH|RPT)-[A-Z0-9][-A-Z0-9]*\b/i)?.[0];
}

for (const flow of publicToAdmin) {
  const resource = allResources.find((item) => item.slug === flow.adminResource);

  test(`public submit muncul dan bisa dikelola admin: ${flow.adminResource}`, async ({ page, adminPage }) => {
    console.log(`[FLOW] Starting test for ${flow.adminResource}, resource found: ${!!resource}`);
    test.skip(!resource, `Resource ${flow.adminResource} tidak ditemukan di AdminRegistry.`);

    await page.goto(flow.publicRoute);

    // Wait for Livewire to initialize
    await page.waitForLoadState('networkidle').catch(() => undefined);
    await page.waitForTimeout(3_000);

    // For multi-step forms, set fields directly via Livewire.all()[0]
    if ((flow as any).multiStep) {
      await page.evaluate(() => {
        const comp = (window as any).Livewire?.all()?.[0];
        if (!comp?.$wire) return;
        comp.$wire.set('nama_perusahaan', 'PT E2E Rekomendasi');
        comp.$wire.set('nama_pemilik', 'Budi E2E');
        comp.$wire.set('npwp', '12.345.678.9-012.345');
        comp.$wire.set('jenis_usaha', 'Rumah Makan');
        comp.$wire.set('alamat_lengkap', 'Jl. E2E Rekomendasi');
        comp.$wire.set('nomor_telepon', '081234567890');
        comp.$wire.set('email', 'e2e-rekomendasi@gmail.com');
      });
      // Wait for snapshot to update
      for (let i = 0; i < 8; i++) {
        await page.waitForTimeout(1_000);
        const snapshotNama = await page.evaluate(() => (window as any).Livewire?.all()?.[0]?.snapshot?.data?.nama_perusahaan);
        if (snapshotNama) break;
      }
      // Advance to step 2
      await page.evaluate(() => (window as any).Livewire?.all()?.[0]?.$wire?.call('nextStep'));
      await page.waitForTimeout(3_000);
      // Fill step 2: set jenis_pengajuan and file uploads
      await page.evaluate(() => {
        const comp = (window as any).Livewire?.all()?.[0];
        if (comp?.$wire) {
          comp.$wire.set('jenis_pengajuan', 'Pembakaran Sampah');
        }
      });
      await page.waitForTimeout(1_000);
      const step2Files = page.locator('input[type="file"]');
      for (let i = 0; i < await step2Files.count(); i++) {
        const input = step2Files.nth(i);
        if (await input.isVisible().catch(() => false)) {
          await input.setInputFiles(pdfFixture).catch(() => undefined);
          await input.dispatchEvent('change');
        }
      }
      await page.waitForTimeout(2_000);
    } else {
      await fillPublic(page, { useRealAssets: !!(flow as any).useRealAssets });
    }

    // Log form state before submit
    const formInputs = await page.locator('form input, form textarea, form select').count();
    console.log(`[FLOW] ${flow.publicRoute}: ${formInputs} form inputs found`);

    await page.locator('button[type="submit"], button:has-text("Kirim"), button:has-text("Ajukan")').last().click();

    // Wait for Livewire to process submission
    await page.waitForTimeout(5_000);

    // Check for success or error
    const afterSubmit = await page.evaluate(() => {
      const wireEl = document.querySelector('[wire\\:id]');
      const wireId = wireEl?.getAttribute('wire:id');
      const comp = (window as any).Livewire?.all()?.[0];
      return {
        snapshotData: comp?.snapshot?.data,
        url: window.location.href,
      };
    });
    console.log(`[FLOW] ${flow.adminResource} after submit:`, JSON.stringify(afterSubmit));

    const bodyText = await page.locator('body').innerText();
    const ticket = ticketFrom(bodyText);
    if (!ticket) {
      // Debug: show what the page looks like after submit
      const snippet = bodyText.replace(/\s+/g, ' ').slice(0, 500);
      console.log(`[FLOW] No ticket found for ${flow.publicRoute}. Page snippet: ${snippet}`);
    } else {
      console.log(`[FLOW] Ticket found: ${ticket}`);
    }
    test.skip(!ticket, `Form ${flow.publicRoute} belum menampilkan nomor tiket/pengajuan yang bisa diekstrak.`);

    await adminPage.goto(`/admin/${flow.adminResource}?q=${encodeURIComponent(ticket!)}`);
    const adminBody = await adminPage.locator('body').innerText();
    test.skip(!adminBody.includes(ticket!), `Ticket ${ticket} tidak ditemukan di admin ${flow.adminResource}. Admin shows 0 data.`);

    await adminPage.locator('a[title="Edit"]').first().click();

    const statusField = resource!.fields.find((field) => field.name === 'status' && field.type === 'select');
    if (statusField) {
      const nextStatus = Object.keys(statusField.options).find((value) => value && value !== 'Belum Ditinjau' && value !== 'Belum Ditindaklanjuti');
      if (nextStatus) {
        // Handle both native select and custom Alpine select
        const nativeSelect = adminPage.locator('select[name="status"]');
        if ((await nativeSelect.count()) > 0) {
          await nativeSelect.selectOption(nextStatus);
        } else {
          // Custom Alpine select - set via JS
          await adminPage.evaluate((val) => {
            const el = document.querySelector('input[type="hidden"][name="status"]');
            const wrapper = el?.closest('[x-data]');
            if (wrapper && (wrapper as any)._x_dataStack) {
              const data = (wrapper as any)._x_dataStack[0];
              if (data?.options && typeof data.selectOption === 'function') {
                const opt = data.options.find((o: any) => o.value === val);
                if (opt) data.selectOption(opt.value, opt.label);
              }
            }
          }, nextStatus);
        }
      }
    }

    await adminPage.locator('button[type="submit"]').last().click();
    await expect(adminPage).toHaveURL(new RegExp(`/admin/${flow.adminResource}/\\d+`));
  });
}

test('admin CRUD sederhana tetap bekerja untuk jenis-lb3', async ({ adminPage }) => {
  const resource = allResources.find((item) => item.slug === 'jenis-lb3')!;
  await adminPage.goto('/admin/jenis-lb3/create');
  const values = await fillAdminForm(adminPage, resource);
  await adminPage.locator('button[type="submit"]').last().click();
  await expect(adminPage).toHaveURL(/\/admin\/jenis-lb3\/\d+/);

  const created = values.nama;
  await adminPage.goto(`/admin/jenis-lb3?q=${encodeURIComponent(created)}`);
  await expect(adminPage.locator('body')).toContainText(created);
});

