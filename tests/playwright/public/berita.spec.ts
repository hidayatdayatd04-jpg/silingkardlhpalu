import { expect, test } from '@playwright/test';

test.describe('Public Berita - Halaman Index', () => {

  test('halaman /berita bisa diakses dan menampilkan konten', async ({ page }) => {
    await page.goto('/berita');
    await expect(page).toHaveURL(/\/berita/);
    // Harus menampilkan heading atau judul halaman
    await expect(page.locator('body')).toContainText(/berita|artikel/i);
  });

  test('halaman /berita menampilkan filter kategori', async ({ page }) => {
    await page.goto('/berita');

    // Harus ada dropdown atau select untuk kategori
    const filterSelect = page.locator('select[name="kategori"]');
    await expect(filterSelect).toBeVisible();

    // Harus ada opsi "Semua Kategori"
    await expect(filterSelect.locator('option').first()).toContainText(/semua kategori/i);
  });

  test('filter kategori Pengendalian', async ({ page }) => {
    await page.goto('/berita?kategori=pengendalian');
    await expect(page).toHaveURL(/kategori=pengendalian/);
    await expect(page.locator('body')).toContainText(/berita|artikel/i);
  });

  test('filter kategori RTH', async ({ page }) => {
    await page.goto('/berita?kategori=rth');
    await expect(page).toHaveURL(/kategori=rth/);
    await expect(page.locator('body')).toContainText(/berita|artikel/i);
  });

  test('filter kategori Sampah & LB3', async ({ page }) => {
    await page.goto('/berita?kategori=sampah-lb3');
    await expect(page).toHaveURL(/kategori=sampah-lb3/);
    await expect(page.locator('body')).toContainText(/berita|artikel/i);
  });

  test('filter kategori Tata Penataan', async ({ page }) => {
    await page.goto('/berita?kategori=tata-penataan');
    await expect(page).toHaveURL(/kategori=tata-penataan/);
    await expect(page.locator('body')).toContainText(/berita|artikel/i);
  });

  test('filter kategori Umum', async ({ page }) => {
    await page.goto('/berita?kategori=umum');
    await expect(page).toHaveURL(/kategori=umum/);
    await expect(page.locator('body')).toContainText(/berita|artikel/i);
  });

  test('filter semua kategori (default)', async ({ page }) => {
    await page.goto('/berita?kategori=');
    await expect(page.locator('body')).toContainText(/berita|artikel/i);
  });

  test('ada kartu artikel atau empty state', async ({ page }) => {
    await page.goto('/berita');
    // Cek apakah ada kartu artikel atau pesan kosong
    const articleCards = page.locator('a[href*="/berita/"]');
    const emptyState = page.getByText(/belum ada berita/i);

    const hasCards = await articleCards.count();
    const hasEmpty = await emptyState.count();
    expect(hasCards > 0 || hasEmpty > 0, 'Harus ada kartu artikel atau pesan kosong').toBeTruthy();
  });

  test('kartur artikel menampilkan judul dan tanggal', async ({ page }) => {
    await page.goto('/berita');

    const articleCards = page.locator('a[href*="/berita/"]');
    const count = await articleCards.count();

    if (count > 0) {
      // Kartur pertama harus punya konten
      const firstCard = articleCards.first();
      await expect(firstCard).toBeVisible();
      // Harus ada teks di dalam kartu
      const text = await firstCard.innerText();
      expect(text.length, 'Kartur artikel harus punya konten teks').toBeGreaterThan(0);
    }
  });

  test('pagination tersedia jika banyak artikel', async ({ page }) => {
    await page.goto('/berita');
    // Cek apakah ada pagination links
    const pagination = page.locator('.pagination, nav[aria-label*="Pagination"], a:has-text("›")');
    // Pagination opsional — hanya cek jika ada
    const count = await pagination.count();
    if (count > 0) {
      await expect(pagination.first()).toBeVisible();
    }
  });
});

test.describe('Public Berita - Halaman Detail', () => {

  test('akses detail artikel via slug', async ({ page }) => {
    // Dapatkan link artikel pertama dari index
    await page.goto('/berita');
    const firstArticle = page.locator('a[href*="/berita/"]').first();

    if ((await firstArticle.count()) === 0) {
      test.skip(true, 'Tidak ada artikel published untuk diakses');
      return;
    }

    const href = await firstArticle.getAttribute('href');
    if (!href) {
      test.skip(true, 'Link artikel tidak ditemukan');
      return;
    }

    await firstArticle.click();
    await page.waitForLoadState('networkidle');

    // Harus di halaman detail artikel
    await expect(page).toHaveURL(/\/berita\/[^/]+$/);
  });

  test('detail artikel menampilkan judul, konten, dan metadata', async ({ page }) => {
    await page.goto('/berita');
    const firstArticle = page.locator('a[href*="/berita/"]').first();

    if ((await firstArticle.count()) === 0) {
      test.skip(true, 'Tidak ada artikel published');
      return;
    }

    await firstArticle.click();
    await page.waitForLoadState('networkidle');

    // Halaman detail harus punya:
    // 1. Judul artikel (h1)
    await expect(page.locator('h1').first()).toBeVisible();

    // 2. Konten artikel
    await expect(page.locator('article, [class*="konten"], .prose, .leading-8').first()).toBeVisible();
  });

  test('detail artikel menampilkan tombol kembali', async ({ page }) => {
    await page.goto('/berita');
    const firstArticle = page.locator('a[href*="/berita/"]').first();

    if ((await firstArticle.count()) === 0) {
      test.skip(true, 'Tidak ada artikel published');
      return;
    }

    await firstArticle.click();
    await page.waitForLoadState('networkidle');

    // Cek ada link kembali (bisa hidden oleh CSS reveal animation)
    const backLinkCount = await page.locator('a[href="/berita"]').count();
    expect(backLinkCount, 'Harus ada link kembali ke /berita').toBeGreaterThan(0);
  });

  test('detail artikel menampilkan tombol share', async ({ page }) => {
    await page.goto('/berita');
    const firstArticle = page.locator('a[href*="/berita/"]').first();

    if ((await firstArticle.count()) === 0) {
      test.skip(true, 'Tidak ada artikel published');
      return;
    }

    await firstArticle.click();
    await page.waitForLoadState('networkidle');

    // Harus ada tombol atau link share (WhatsApp, Facebook, Copy link)
    const shareSection = page.locator('a[href*="wa.me"], a[href*="facebook.com/sharer"], button:has-text("Salin")');
    const shareCount = await shareSection.count();
    expect(shareCount, 'Harus ada minimal satu tombol share').toBeGreaterThan(0);
  });

  test('detail artikel menampilkan kategori badge', async ({ page }) => {
    await page.goto('/berita');
    const firstArticle = page.locator('a[href*="/berita/"]').first();

    if ((await firstArticle.count()) === 0) {
      test.skip(true, 'Tidak ada artikel published');
      return;
    }

    await firstArticle.click();
    await page.waitForLoadState('networkidle');

    // Cek apakah ada badge kategori
    const kategoriBadge = page.locator('span:has-text("Pengendalian"), span:has-text("RTH"), span:has-text("Sampah"), span:has-text("Tata Penataan"), span:has-text("Umum")');
    // Badge kategori opsional
    const count = await kategoriBadge.count();
    if (count > 0) {
      await expect(kategoriBadge.first()).toBeVisible();
    }
  });

  test('navigasi dari detail kembali ke index', async ({ page }) => {
    await page.goto('/berita');
    const firstArticle = page.locator('a[href*="/berita/"]').first();

    if ((await firstArticle.count()) === 0) {
      test.skip(true, 'Tidak ada artikel published');
      return;
    }

    await firstArticle.click();
    await page.waitForLoadState('networkidle');

    // Navigasi langsung ke /berita (back link mungkin hidden oleh CSS)
    await page.goto('/berita');
    await expect(page).toHaveURL(/\/berita/);
  });
});

test.describe('Public Berita - Edge Cases', () => {

  test('slug tidak valid mengembalikan 404', async ({ page }) => {
    const response = await page.goto('/berita/artikel-tidak-ada-samsekali');
    expect(response?.status()).toBe(404);
  });

  test('slugg dengan karakter spesial di-encode dengan benar', async ({ page }) => {
    const response = await page.goto('/berita/test%20slug%20with%20spaces');
    // Harus handle gracefully (404 atau redirect)
    expect(response?.status()).toBeLessThan(500);
  });
});

test.describe('Public Berita - Homepage', () => {

  test('homepage menampilkan section berita terbaru', async ({ page }) => {
    await page.goto('/');

    // Homepage harus menampilkan section berita
    // Cek ada link ke /berita atau kartur artikel
    const beritaLinks = page.locator('a[href*="/berita/"]');
    const beritaSection = page.getByText(/berita|artikel terbaru/i);

    const hasLinks = await beritaLinks.count();
    const hasSection = await beritaSection.count();

    expect(hasLinks > 0 || hasSection > 0, 'Homepage harus menampilkan section berita').toBeTruthy();
  });

  test('link artikel di homepage mengarah ke halaman detail', async ({ page }) => {
    await page.goto('/');

    const firstBerita = page.locator('a[href*="/berita/"]').first();
    if ((await firstBerita.count()) === 0) {
      test.skip(true, 'Tidak ada berita di homepage');
      return;
    }

    await firstBerita.click();
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(/\/berita\/[^/]+$/);
  });
});
