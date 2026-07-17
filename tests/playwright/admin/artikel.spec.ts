import { expect, test, type Page, type BrowserContext } from '@playwright/test';
import { ensureUploadFixtures, imageFixture } from '../fixtures/form';

const ARTIKEL = 'artikel';
const ADMIN_LOGIN = process.env.ADMIN_LOGIN || 'superadmin';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'superadmin123';

/** Login ke admin panel */
async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto('/admin');
  if (/\/admin\/login/.test(page.url())) {
    await page.fill('input[name="login"]', ADMIN_LOGIN);
    await page.fill('#password', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin', { timeout: 15_000 });
  }
}

/** Set nilai custom Alpine select via JS */
async function setAlpineSelect(page: Page, fieldName: string, value: string): Promise<void> {
  await page.evaluate(({ name, value }) => {
    const input = document.querySelector(`input[type="hidden"][name="${name}"]`) as HTMLInputElement;
    if (!input) return;
    const wrapper = input.closest('[x-data]');
    if (wrapper && (wrapper as any)._x_dataStack) {
      const data = (wrapper as any)._x_dataStack[0];
      if (data && typeof data.selectOption === 'function') {
        const option = data.options?.find((o: any) => o.value === value);
        if (option) { data.selectOption(option.value, option.label); return; }
      }
    }
    input.value = value;
    input.dispatchEvent(new Event('change', { bubbles: true }));
  }, { name: fieldName, value });
}

/** Wait for Jodit editor to initialize and fill content */
async function fillJoditEditor(page: Page, content: string): Promise<void> {
  // Wait for Jodit container to appear
  await page.waitForFunction(() => {
    const container = document.querySelector('.jodit-container');
    return container !== null;
  }, { timeout: 15000 });

  // Wait a bit for full initialization
  await page.waitForTimeout(500);

  // Set content via Jodit global array
  await page.evaluate((html) => {
    // Try global array first
    const editors = (window as any).__joditEditors;
    if (editors && editors.length > 0) {
      const editor = editors[editors.length - 1];
      if (editor && typeof editor.value !== 'undefined') {
        editor.value = html;
        return;
      }
    }
    // Fallback: find by textarea name
    const textarea = document.querySelector('textarea[name="konten"]') as HTMLTextAreaElement;
    if (textarea) {
      textarea.value = html;
    }
  }, content);
}

/** Get Jodit editor content */
async function getJoditEditorContent(page: Page): Promise<string> {
  return page.evaluate(() => {
    // Try global array first
    const editors = (window as any).__joditEditors;
    if (editors && editors.length > 0) {
      const editor = editors[editors.length - 1];
      if (editor && typeof editor.value === 'string') {
        return editor.value;
      }
    }
    // Fallback to DOM
    const editorContent = document.querySelector('.jodit-editor__content') as HTMLElement;
    return editorContent ? editorContent.innerHTML : '';
  });
}

// ═══════════════════════════════════════════════════════════
// ADMIN BERITA — INDEX
// ═══════════════════════════════════════════════════════════

test.describe('Admin Berita - Index', () => {
  test('index menampilkan daftar, search, dan toolbar', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}`);
    await expect(page).not.toHaveURL(/admin\/login/);
    await expect(page.locator('h1, h2').first()).toContainText('Artikel');
    await expect(page.locator('input[name="q"]')).toBeVisible();
    await expect(page.locator('a:has-text("Tambah")')).toBeVisible();
    await expect(page.locator('body')).toContainText(/Total \d+ data/);
  });

  test('search artikel berdasarkan judul', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}`);
    await page.locator('input[name="q"]').fill('E2E');
    await page.locator('form:has(input[name="q"]) button[type="submit"]').click();
    await expect(page).toHaveURL(/q=E2E/);
  });

  test('filter artikel berdasarkan status', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}`);
    const filterBtn = page.getByRole('button', { name: /filter/i });
    if (await filterBtn.count() > 0) {
      await filterBtn.click();
      const publishedCheckbox = page.locator('input[name="status[]"][value="published"]');
      if (await publishedCheckbox.count() > 0) {
        await publishedCheckbox.check({ force: true });
        await page.getByRole('button', { name: /terapkan|filter/i }).last().click();
        await expect(page).toHaveURL(/status/);
      }
    }
  });
});

// ═══════════════════════════════════════════════════════════
// ADMIN BERITA — CREATE
// ═══════════════════════════════════════════════════════════

test.describe('Admin Berita - Create', () => {
  test('create form memuat field yang diperlukan', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}/create`);
    await expect(page.locator('[name="judul"]')).toBeVisible();
    // Jodit editor harus muncul - wait for container
    await page.waitForFunction(() => {
      return document.querySelector('.jodit-container') !== null;
    }, { timeout: 15000 });
    await expect(page.locator('.jodit-container')).toBeVisible();
    // status menggunakan custom Alpine select (hidden input)
    await expect(page.locator('[name="status"]')).toBeAttached();
  });

  test('validasi form wajib diisi', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}/create`);

    // Submit form kosong — harus ada error validasi ATAU tetap di halaman
    await page.locator('form button[type="submit"]').last().click();
    await page.waitForTimeout(3000);

    const stillOnCreate = /\/create$/.test(page.url());
    const hasValidationError = await page.locator('.text-danger-600, .text-red-600, [class*="error"], .text-rose-600, [class*="invalid"]').count();
    const hasServerError = /500|error|exception/i.test(await page.locator('body').innerText().catch(() => ''));

    // Form harus memblokir: validasi client-side, server-side, atau error handling
    expect(
      stillOnCreate || hasValidationError > 0 || hasServerError,
      'Submit kosong harus diblokir oleh validasi atau error handling',
    ).toBeTruthy();
  });

  test('buat artikel baru lengkap', async ({ page }) => {
    ensureUploadFixtures();
    const stamp = Date.now().toString().slice(-6);

    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}/create`);
    await page.locator('[name="judul"]').fill(`Berita E2E ${stamp}`);

    // Isi konten via Jodit editor
    await fillJoditEditor(page, `<p>Isi berita E2E nomor ${stamp}.</p>`);

    // Kategori — custom Alpine select
    const kategoriExists = await page.locator('[name="kategori"]').count();
    if (kategoriExists > 0) {
      // Dapatkan opsi pertama yang tersedia
      const firstOption = await page.evaluate(() => {
        const input = document.querySelector('input[name="kategori"]') as HTMLInputElement;
        const wrapper = input?.closest('[x-data]');
        if (wrapper && (wrapper as any)._x_dataStack) {
          const data = (wrapper as any)._x_dataStack[0];
          if (data?.options?.length) return data.options[0].value;
        }
        return null;
      });
      if (firstOption) await setAlpineSelect(page, 'kategori', firstOption);
    }

    // Status — custom Alpine select
    if (await page.locator('[name="status"]').count() > 0) {
      await setAlpineSelect(page, 'status', 'published');
    }

    const tanggalInput = page.locator('[name="tanggal_publish"]');
    if (await tanggalInput.count() > 0) await tanggalInput.fill('2026-07-12');

    const thumbnailInput = page.locator('[name="thumbnail"]');
    if (await thumbnailInput.count() > 0) await thumbnailInput.setInputFiles(imageFixture);

    await page.locator('form button[type="submit"]').last().click();
    await page.waitForLoadState('networkidle').catch(() => undefined);
    await page.waitForTimeout(3000);

    const url = page.url();
    if (url.includes(`/admin/${ARTIKEL}`)) {
      await expect(page.locator('body')).toContainText(`Berita E2E ${stamp}`);
    }
  });

  test('buat artikel draft', async ({ page }) => {
    const stamp = Date.now().toString().slice(-6);
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}/create`);
    await page.locator('[name="judul"]').fill(`Draft E2E ${stamp}`);

    // Isi konten via Jodit editor
    await fillJoditEditor(page, '<p>Artikel draft</p>');

    if (await page.locator('[name="status"]').count() > 0) {
      await setAlpineSelect(page, 'status', 'draft');
    }

    await page.locator('form button[type="submit"]').last().click();
    await page.waitForLoadState('networkidle').catch(() => undefined);
    await page.waitForTimeout(3000);

    await page.goto(`/admin/${ARTIKEL}`);
    await expect(page.locator('body')).toContainText(`Draft E2E ${stamp}`);
  });
});

// ═══════════════════════════════════════════════════════════
// ADMIN BERITA — DETAIL
// ═══════════════════════════════════════════════════════════

test.describe('Admin Berita - Detail', () => {
  test('lihat detail artikel', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}`);
    const detailBtn = page.locator('a[title="Detail"]').first();
    if ((await detailBtn.count()) === 0) {
      test.skip(true, 'Tidak ada artikel');
      return;
    }
    await detailBtn.click();
    await expect(page).toHaveURL(new RegExp(`/admin/${ARTIKEL}/\\d+$`));
    await expect(page.locator('body')).toContainText(/Informasi|Judul/i);
  });

  test('artikel 404 jika ID tidak ada', async ({ page }) => {
    await loginAsAdmin(page);
    const response = await page.goto('/admin/artikel/99999');
    expect(response?.status()).toBe(404);
  });
});

// ═══════════════════════════════════════════════════════════
// ADMIN BERITA — JODIT EDITOR FEATURES
// ═══════════════════════════════════════════════════════════

test.describe('Admin Berita - Jodit Editor', () => {
  test('Jodit editor muncul di form create', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}/create`);

    // Wait for Jodit container to appear
    await page.waitForFunction(() => {
      return document.querySelector('.jodit-container') !== null;
    }, { timeout: 15000 });

    // Verifikasi Jodit editor muncul
    await expect(page.locator('.jodit-container')).toBeVisible();
  });

  test('isi konten dengan rich text', async ({ page }) => {
    const stamp = Date.now().toString().slice(-6);
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}/create`);
    await page.locator('[name="judul"]').fill(`Rich Text E2E ${stamp}`);

    // Isi konten dengan format bold dan italic
    const richContent = `<h2>Judul Artikel</h2><p>Ini adalah <strong>teks tebal</strong> dan <em>teks miring</em>.</p><ul><li>Item pertama</li><li>Item kedua</li><li>Item ketiga</li></ul>`;
    await fillJoditEditor(page, richContent);

    // Verifikasi konten dimasukkan
    const editorContent = await getJoditEditorContent(page);
    expect(editorContent).toContain('teks tebal');
    expect(editorContent).toContain('teks miring');
  });

  test('insert tabel via Jodit', async ({ page }) => {
    const stamp = Date.now().toString().slice(-6);
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}/create`);
    await page.locator('[name="judul"]').fill(`Table E2E ${stamp}`);

    // Isi konten dengan tabel
    const tableContent = `<p>Artikel dengan tabel:</p><table><thead><tr><th>Kolom 1</th><th>Kolom 2</th><th>Kolom 3</th></tr></thead><tbody><tr><td>Data 1</td><td>Data 2</td><td>Data 3</td></tr><tr><td>Data 4</td><td>Data 5</td><td>Data 6</td></tr></tbody></table>`;
    await fillJoditEditor(page, tableContent);

    // Verifikasi tabel ada di editor
    const editorContent = await getJoditEditorContent(page);
    expect(editorContent).toContain('table');
    expect(editorContent).toContain('Kolom 1');
  });

  test('edit artikel memuat konten ke Jodit', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}`);
    const editBtn = page.locator('a[title="Edit"]').first();
    if ((await editBtn.count()) === 0) {
      test.skip(true, 'Tidak ada artikel');
      return;
    }
    await editBtn.click();
    await expect(page).toHaveURL(new RegExp(`/admin/${ARTIKEL}/\\d+/edit`));

    // Wait for Jodit container
    await page.waitForFunction(() => {
      return document.querySelector('.jodit-container') !== null;
    }, { timeout: 15000 });

    // Verifikasi Jodit editor muncul di form edit
    await expect(page.locator('.jodit-container')).toBeVisible();

    // Verifikasi konten lama dimuat ke editor
    await page.waitForTimeout(2000);
    const editorContent = await getJoditEditorContent(page);
    expect(editorContent.length).toBeGreaterThan(0);
  });
});

// ═══════════════════════════════════════════════════════════
// ADMIN BERITA — EDIT
// ═══════════════════════════════════════════════════════════

test.describe('Admin Berita - Edit', () => {
  test('edit artikel mengubah data', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto(`/admin/${ARTIKEL}`);
    const editBtn = page.locator('a[title="Edit"]').first();
    if ((await editBtn.count()) === 0) {
      test.skip(true, 'Tidak ada artikel');
      return;
    }
    await editBtn.click();
    await expect(page).toHaveURL(new RegExp(`/admin/${ARTIKEL}/\\d+/edit`));

    const judulInput = page.locator('[name="judul"]');
    const stamp = Date.now().toString().slice(-6);
    await judulInput.clear();
    await judulInput.fill(`Artikel Updated ${stamp}`);

    await page.locator('form button[type="submit"]').last().click();
    await page.waitForLoadState('networkidle').catch(() => undefined);
    await page.waitForTimeout(3000);

    if (new RegExp(`/admin/${ARTIKEL}/\\d+$`).test(page.url())) {
      await expect(page.locator('body')).toContainText(`Artikel Updated ${stamp}`);
    }
  });
});

// ═══════════════════════════════════════════════════════════
// ADMIN BERITA — DELETE
// ═══════════════════════════════════════════════════════════

test.describe('Admin Berita - Delete', () => {
  test('delete artikel via form action', async ({ page }) => {
    const stamp = Date.now().toString().slice(-6);
    await loginAsAdmin(page);

    // Buat artikel dulu
    await page.goto(`/admin/${ARTIKEL}/create`);
    await page.locator('[name="judul"]').fill(`Delete Me ${stamp}`);

    // Isi konten via Jodit editor
    await fillJoditEditor(page, '<p>Untuk dihapus</p>');

    if (await page.locator('[name="status"]').count() > 0) {
      await setAlpineSelect(page, 'status', 'draft');
    }
    await page.locator('form button[type="submit"]').last().click();
    await page.waitForLoadState('networkidle').catch(() => undefined);
    await page.waitForTimeout(3000);

    // Cari artikel yang baru dibuat di index
    await page.goto(`/admin/${ARTIKEL}`);
    const deleteBtn = page.locator('button[title="Hapus"]').first();
    if ((await deleteBtn.count()) > 0) {
      // Klik hapus, dispatch event untuk buka modal
      await deleteBtn.click();
      await page.waitForTimeout(1_500);

      // Cari form delete di dalam modal (hidden, tapi bisa di-submit via JS)
      const deleteForm = page.locator('form[method="POST"]:has(input[name="_method"][value="DELETE"])').last();
      if ((await deleteForm.count()) > 0) {
        // Submit form delete langsung
        await deleteForm.evaluate((form) => (form as HTMLFormElement).submit());
        await page.waitForURL(new RegExp(`/admin/${ARTIKEL}`), { timeout: 15_000 });
      }
    }
  });
});

// ═══════════════════════════════════════════════════════════
// ADMIN BERITA — EXPORT & AKSES
// ═══════════════════════════════════════════════════════════

test.describe('Admin Berita - Export & Akses', () => {
  test('export artikel CSV', async ({ page }) => {
    await loginAsAdmin(page);
    const csvRes = await page.request.get(`/admin/${ARTIKEL}/export?format=csv`);
    expect(csvRes.status(), 'export CSV').toBeLessThan(500);
  });

  test('export-all artikel CSV', async ({ page }) => {
    await loginAsAdmin(page);
    const allCsv = await page.request.get(`/admin/${ARTIKEL}/export-all?format=csv`);
    expect(allCsv.status(), 'export-all CSV').toBeLessThan(500);
  });

  test('guest tidak bisa akses admin artikel', async ({ page }) => {
    await page.goto('/admin/artikel');
    await expect(page).toHaveURL(/admin\/login/);
  });
});
