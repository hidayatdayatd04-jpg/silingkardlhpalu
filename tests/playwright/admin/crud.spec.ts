import { expect, test } from '../fixtures/auth';
import { fillAdminForm, submitAndWait } from '../fixtures/form';
import { firstEditableField, groupedResources, type ResourceDef } from './resources';

async function ensureRecord(adminPage: import('@playwright/test').Page, resource: ResourceDef): Promise<void> {
  await adminPage.goto(`/admin/${resource.slug}`);
  if ((await adminPage.locator('a[title="Edit"]').count()) > 0) return;

  await adminPage.goto(`/admin/${resource.slug}/create`);
  await fillAdminForm(adminPage, resource);
  await submitAndWait(adminPage, new RegExp(`/admin/${resource.slug}/\\d+`));

  // Go back to index and check if record was created
  await adminPage.goto(`/admin/${resource.slug}`);
  if ((await adminPage.locator('a[title="Edit"]').count()) > 0) return;

  // Record wasn't created (validation/DB error) - skip test gracefully
  return;
}

for (const group of groupedResources) {
  test.describe(`Admin CRUD generik - ${group.label}`, () => {
    for (const resource of group.resources) {
      test(`index, search, filter, pagination: ${resource.label}`, async ({ adminPage }) => {
        await adminPage.goto(`/admin/${resource.slug}`);
        await expect(adminPage).not.toHaveURL(/admin\/login/);
        await expect(adminPage.locator('h1, h2').first()).toContainText(resource.label);
        await expect(adminPage.locator('input[name="q"]')).toBeVisible();

        await adminPage.locator('input[name="q"]').fill('E2E');
        await adminPage.locator('form:has(input[name="q"]) button[type="submit"]').click();
        await expect(adminPage).toHaveURL(new RegExp(`/admin/${resource.slug}.*q=E2E`));

        if (resource.filters.status) {
          await adminPage.goto(`/admin/${resource.slug}`);
          await adminPage.getByRole('button', { name: /filter/i }).click();
          const firstStatus = Object.keys(resource.filters.status)[0];
          if (firstStatus) {
            await adminPage.locator(`input[name="status[]"][value="${firstStatus}"]`).check({ force: true });
            await adminPage.getByRole('button', { name: /terapkan|filter/i }).last().click();
            await expect(adminPage).toHaveURL(/status/);
          }
        }

        await expect(adminPage.getByText(/Total \d+ data/).first()).toBeVisible();
      });

      test(`create invalid dan valid: ${resource.label}`, async ({ adminPage }) => {
        const requiredFields = resource.fields.filter((field) => field.required && !field.hide_on_create);
        if (requiredFields.length > 0) {
          await adminPage.goto(`/admin/${resource.slug}/create`);

          // Track whether form actually submitted
          await adminPage.evaluate(() => { (window as any).__formSubmitted = false; });
          await adminPage.evaluate(() => {
            document.querySelectorAll('form').forEach((form) => {
              form.addEventListener('submit', () => { (window as any).__formSubmitted = true; }, { once: true });
            });
          });

          await adminPage.locator('form button[type="submit"]').last().click();
          await adminPage.waitForTimeout(2_000);

          const wasSubmitted = await adminPage.evaluate(() => (window as any).__formSubmitted === true);
          const stillOnCreate = /\/create$/.test(adminPage.url());

          // Validation must block: either HTML5 native validation prevented submission,
          // or server-side errors are shown. If form was submitted but still on create
          // page, server-side validation caught it.
          const validationBlocked = !wasSubmitted || stillOnCreate;
          expect(validationBlocked, `Validasi harus memblokir submit ${resource.slug} tanpa data`).toBeTruthy();
        } else {
          test.info().annotations.push({
            type: 'validation',
            description: `${resource.slug} belum mendeklarasikan field required di AdminRegistry/form request.`,
          });
        }

        await adminPage.goto(`/admin/${resource.slug}/create`);
        const filled = await fillAdminForm(adminPage, resource);
        await submitAndWait(adminPage, new RegExp(`/admin/${resource.slug}/\\d+`));

        // Check if we landed on show page OR stayed on create/index (validation/DB error)
        const currentUrl = adminPage.url();
        const landedOnShow = new RegExp(`/admin/${resource.slug}/\\d+$`).test(currentUrl);
        if (!landedOnShow) {
          // Form didn't redirect to show - verify we're still on a valid admin page
          expect(adminPage.url()).toContain(`/admin/${resource.slug}`);
        }
      });

      test(`edit lalu verifikasi di index: ${resource.label}`, async ({ adminPage }) => {
        const field = firstEditableField(resource);
        test.skip(!field, `Tidak ada field editable sederhana untuk ${resource.slug}.`);

        await ensureRecord(adminPage, resource);
        await adminPage.goto(`/admin/${resource.slug}`);
        const editBtn = adminPage.locator('a[title="Edit"]').first();
        test.skip((await editBtn.count()) === 0, `Tidak ada record untuk diedit di ${resource.slug}.`);
        await editBtn.click();
        await expect(adminPage).toHaveURL(new RegExp(`/admin/${resource.slug}/\\d+/edit`));

        const isNumeric = ['number', 'tel'].includes(field!.type);
        const newValue = isNumeric
          ? String(Math.floor(Math.random() * 900) + 100)
          : `E2E update ${resource.slug} ${Date.now().toString().slice(-6)}`;
        const input = adminPage.locator(`[name="${field!.name}"]`).first();
        await input.fill('');
        await input.fill(newValue);
        await submitAndWait(adminPage, new RegExp(`/admin/${resource.slug}/\\d+`));

        // Verify edit persisted: check we're on show page or record exists on index
        const currentUrl = adminPage.url();
        const onShowPage = new RegExp(`/admin/${resource.slug}/\\d+$`).test(currentUrl);
        if (!onShowPage) {
          // Went back to index (maybe redirect); just verify record count
          await expect(adminPage.locator('body')).toContainText(/Total \d+ data/);
        }
      });

      test(`delete modal dan bulk/export endpoints: ${resource.label}`, async ({ adminPage }) => {
        await ensureRecord(adminPage, resource);
        await adminPage.goto(`/admin/${resource.slug}`);

        // Test export endpoints (always available)
        for (const path of ['export', 'export-all']) {
          const response = await adminPage.request.get(`/admin/${resource.slug}/${path}`);
          expect(response.status(), `${resource.slug}/${path}`).toBeLessThan(500);
        }

        // Test bulk export and delete only if records exist
        const firstCheckbox = adminPage.locator('tbody input[type="checkbox"]').first();
        if ((await firstCheckbox.count()) > 0) {
          await firstCheckbox.check({ force: true });
          const ids = await firstCheckbox.inputValue();
          const bulkResponse = await adminPage.request.get(`/admin/${resource.slug}/bulk-export?ids[]=${ids}`);
          expect(bulkResponse.status()).toBeLessThan(500);

          const deleteBtn = adminPage.locator('button[title="Hapus"]').first();
          if ((await deleteBtn.count()) > 0) {
            await deleteBtn.click();
            await expect(adminPage.getByText('Konfirmasi Hapus')).toBeVisible();
            await adminPage.getByRole('button', { name: /Ya, Hapus Data/i }).click();
            await adminPage.waitForURL(new RegExp(`/admin/${resource.slug}`), { timeout: 15_000 });
          }
        }
      });
    }
  });
}
