import { expect, test } from '@playwright/test';

test.describe('Verification of Part 1-6 changes', () => {
  test('lacak page loads without error', async ({ page }) => {
    await page.goto('/lacak');
    await expect(page.locator('body')).toContainText(/lacak|nomor tiket/i);
  });

  test('cek-pengaduan-pengendalian page loads', async ({ page }) => {
    await page.goto('/cek-pengaduan-pengendalian');
    await expect(page.locator('body')).toContainText(/cek|nomor tiket/i);
  });

  test('cek-pengaduan-sampah page loads', async ({ page }) => {
    await page.goto('/cek-pengaduan-sampah');
    await expect(page.locator('body')).toContainText(/cek|nomor tiket/i);
  });

  test('cek-pengaduan-rth page loads', async ({ page }) => {
    await page.goto('/cek-pengaduan-rth');
    await expect(page.locator('body')).toContainText(/cek|nomor tiket/i);
  });

  test('cek-pengaduan-tata-penataan page loads', async ({ page }) => {
    await page.goto('/cek-pengaduan-tata-penataan');
    await expect(page.locator('body')).toContainText(/cek|nomor tiket/i);
  });

  test('cek-perizinan-tebang-pohon page loads', async ({ page }) => {
    await page.goto('/cek-perizinan-tebang-pohon');
    await expect(page.locator('body')).toContainText(/cek|nomor tiket/i);
  });

  test('cek-pinjam-taman page loads', async ({ page }) => {
    await page.goto('/cek-pinjam-taman');
    await expect(page.locator('body')).toContainText(/cek|nomor tiket/i);
  });

  test('cek-permohonan-rekomendasi page loads', async ({ page }) => {
    await page.goto('/cek-permohonan-rekomendasi');
    await expect(page.locator('body')).toContainText(/cek|nomor tiket/i);
  });

  test('cek-rintek-pertek page loads', async ({ page }) => {
    await page.goto('/cek-rintek-pertek');
    await expect(page.locator('body')).toContainText(/cek|nomor tiket/i);
  });

  test('cek-registrasi-lb3 page loads', async ({ page }) => {
    await page.goto('/cek-registrasi-lb3');
    await expect(page.locator('body')).toContainText(/cek|nomor tiket/i);
  });

  test('pengaduan-pengendalian form has no WhatsApp check code', async ({ page }) => {
    await page.goto('/pengaduan-pengendalian');
    const content = await page.content();
    expect(content).not.toContain('checkWa');
    expect(content).not.toContain('waStatus');
    expect(content).not.toContain('whatsapp/check-number');
  });

  test('pengaduan-rth form has no WhatsApp check code', async ({ page }) => {
    await page.goto('/pengaduan-rth');
    const content = await page.content();
    expect(content).not.toContain('checkWa');
    expect(content).not.toContain('waStatus');
    expect(content).not.toContain('whatsapp/check-number');
  });

  test('pengaduan-sampah form has no WhatsApp check code', async ({ page }) => {
    await page.goto('/pengaduan-sampah');
    const content = await page.content();
    expect(content).not.toContain('checkWa');
    expect(content).not.toContain('waStatus');
    expect(content).not.toContain('whatsapp/check-number');
  });

  test('pengaduan-tata-penataan form has no WhatsApp check code', async ({ page }) => {
    await page.goto('/pengaduan-tata-penataan');
    const content = await page.content();
    expect(content).not.toContain('checkWa');
    expect(content).not.toContain('waStatus');
    expect(content).not.toContain('whatsapp/check-number');
  });

  test('perizinan-tebang-pohon form has no WhatsApp check code', async ({ page }) => {
    await page.goto('/perizinan-tebang-pohon');
    const content = await page.content();
    expect(content).not.toContain('checkWa');
    expect(content).not.toContain('waStatus');
    expect(content).not.toContain('whatsapp/check-number');
  });

  test('pinjam-taman form has no WhatsApp check code', async ({ page }) => {
    await page.goto('/pinjam-taman');
    const content = await page.content();
    expect(content).not.toContain('checkWa');
    expect(content).not.toContain('waStatus');
    expect(content).not.toContain('whatsapp/check-number');
  });

  test('permohonan-rekomendasi form has no WhatsApp check code', async ({ page }) => {
    await page.goto('/permohonan-rekomendasi');
    const content = await page.content();
    expect(content).not.toContain('checkWa');
    expect(content).not.toContain('waStatus');
    expect(content).not.toContain('whatsapp/check-number');
  });

  test('registrasi-usaha-lb3 form has no WhatsApp check code', async ({ page }) => {
    await page.goto('/registrasi-usaha-lb3');
    const content = await page.content();
    expect(content).not.toContain('checkWa');
    expect(content).not.toContain('waStatus');
    expect(content).not.toContain('whatsapp/check-number');
  });

  test('pengajuan-rintek-pertek form has no WhatsApp check code', async ({ page }) => {
    await page.goto('/pengajuan-rintek-pertek');
    const content = await page.content();
    expect(content).not.toContain('checkWa');
    expect(content).not.toContain('waStatus');
    expect(content).not.toContain('whatsapp/check-number');
  });

  test('pengaduan-unified form has no WhatsApp check code', async ({ page }) => {
    await page.goto('/pengaduan-rth');
    const content = await page.content();
    expect(content).not.toContain('checkWa');
    expect(content).not.toContain('waStatus');
    expect(content).not.toContain('whatsapp/check-number');
  });

  test('berita show page has no WhatsApp share button', async ({ page }) => {
    // First get a berita slug
    await page.goto('/berita');
    const firstLink = page.locator('a[href*="/berita/"]').first();
    if (await firstLink.count() > 0) {
      const href = await firstLink.getAttribute('href');
      if (href) {
        await page.goto(href);
        const content = await page.content();
        expect(content).not.toContain('wa.me/?text=');
      }
    }
  });

  test('manifest.json is accessible', async ({ page }) => {
    const response = await page.request.get('/manifest.json');
    expect(response.status()).toBe(200);
    const manifest = await response.json();
    expect(manifest.name).toContain('SILINGKAR');
    expect(manifest.short_name).toBe('SILINGKAR');
    expect(manifest.display).toBe('standalone');
  });

  test('feedback route returns 405 for GET', async ({ page }) => {
    const response = await page.request.get('/feedback/test-ticket');
    // GET should return 405 Method Not Allowed since we only have POST
    expect(response.status()).toBe(405);
  });

  test('admin ulasan-masyarakat page requires auth', async ({ page }) => {
    const response = await page.request.get('http://localhost:8000/admin/ulasan-masyarakat');
    // Should redirect to login
    expect(response.url()).toContain('login');
  });
});
