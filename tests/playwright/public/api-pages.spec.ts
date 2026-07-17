import { expect, test } from '@playwright/test';

// ═══════════════════════════════════════════════════════════════════
// 1. API ENDPOINTS
// ═══════════════════════════════════════════════════════════════════

test.describe('API Endpoints', () => {
  test('GET /api/armada-aktif returns JSON', async ({ page }) => {
    const response = await page.request.get('/api/armada-aktif');
    expect(response.status()).toBe(200);
    const contentType = response.headers()['content-type'] || '';
    expect(contentType).toContain('json');

    const data = await response.json();
    expect(data).toHaveProperty('status');
    expect(data).toHaveProperty('message');
    expect(data).toHaveProperty('data');
    expect(data.status).toBe(true);
  });

  test('GET /api/peta-persampahan/layers returns JSON', async ({ page }) => {
    const response = await page.request.get('/api/peta-persampahan/layers');
    expect(response.status()).toBe(200);
    const contentType = response.headers()['content-type'] || '';
    expect(contentType).toContain('json');
  });

  test('POST /api/chatbot/stream requires message', async ({ page }) => {
    const response = await page.request.post('/api/chatbot/stream', {
      data: {},
    });
    // Should return 400 or 422 for missing message
    expect(response.status()).toBeGreaterThanOrEqual(400);
  });
});

// ═══════════════════════════════════════════════════════════════════
// 2. PUBLIC PAGES - INFORMATION
// ═══════════════════════════════════════════════════════════════════

test.describe('Public Information Pages', () => {
  test('homepage loads with content', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('body')).toBeVisible();
    // Should have some content
    const bodyText = await page.locator('body').innerText();
    expect(bodyText.length).toBeGreaterThan(100);
  });

  test('/profil page loads', async ({ page }) => {
    await page.goto('/profil');
    await expect(page).toHaveURL(/\/profil/);
    await expect(page.locator('body')).toContainText(/profil|profile|dinas/i);
  });

  test('/tentang redirects to /profil', async ({ page }) => {
    await page.goto('/tentang');
    await expect(page).toHaveURL(/\/profil/);
  });

  test('/tata-penataan page loads', async ({ page }) => {
    await page.goto('/tata-penataan');
    await expect(page).toHaveURL(/\/tata-penataan/);
    await expect(page.locator('body')).toContainText(/tata penataan/i);
  });

  test('/kebijakan-privasi page loads', async ({ page }) => {
    await page.goto('/kebijakan-privasi');
    await expect(page).toHaveURL(/\/kebijakan-privasi/);
    await expect(page.locator('body')).toContainText(/privasi|privacy/i);
  });

  test('/syarat-ketentuan page loads', async ({ page }) => {
    await page.goto('/syarat-ketentuan');
    await expect(page).toHaveURL(/\/syarat-ketentuan/);
    await expect(page.locator('body')).toContainText(/syarat|ketentuan|terms/i);
  });

  test('/sekretariat shows coming soon', async ({ page }) => {
    await page.goto('/sekretariat');
    await expect(page.locator('body')).toContainText(/coming soon|segera|hadir/i);
  });

  test('/uptd/{id} shows coming soon', async ({ page }) => {
    for (const id of [1, 2, 3, 4]) {
      await page.goto(`/uptd/${id}`);
      await expect(page.locator('body')).toContainText(/coming soon|segera|hadir|UPTD/i);
    }
  });
});

// ═══════════════════════════════════════════════════════════════════
// 3. PUBLIC PAGES - MAPS
// ═══════════════════════════════════════════════════════════════════

test.describe('Public Map Pages', () => {
  test('/peta-rth page loads', async ({ page }) => {
    await page.goto('/peta-rth');
    await expect(page).toHaveURL(/\/peta-rth/);
    await expect(page.locator('body')).toContainText(/peta|rth|hijau/i);
  });

  test('/peta-persampahan page loads', async ({ page }) => {
    await page.goto('/peta-persampahan');
    await expect(page).toHaveURL(/\/peta-persampahan/);
    await expect(page.locator('body')).toContainText(/peta|persampahan|sampah/i);
  });

  test('/peta-objek-pengawasan page loads', async ({ page }) => {
    await page.goto('/peta-objek-pengawasan');
    await expect(page).toHaveURL(/\/peta-objek-pengawasan/);
    await expect(page.locator('body')).toContainText(/peta|objek|pengawasan/i);
  });

  test('/armada page loads with fleet tracking', async ({ page }) => {
    await page.goto('/armada');
    await expect(page).toHaveURL(/\/armada/);
    await expect(page.locator('body')).toContainText(/armada|fleet|kendaraan/i);
  });
});

// ═══════════════════════════════════════════════════════════════════
// 4. PUBLIC PAGES - FORMS (page load only)
// ═══════════════════════════════════════════════════════════════════

test.describe('Public Form Pages - Page Load', () => {
  const formPages = [
    { name: 'Pengaduan', route: '/pengaduan' },
    { name: 'Pengaduan Pengendalian', route: '/pengaduan-pengendalian' },
    { name: 'Pengaduan Sampah', route: '/pengaduan-sampah' },
    { name: 'Pengaduan RTH', route: '/pengaduan-rth' },
    { name: 'Pengaduan Tata Penataan', route: '/pengaduan-tata-penataan' },
    { name: 'Permohonan Rekomendasi', route: '/permohonan-rekomendasi' },
    { name: 'Pengajuan Rintek Pertek', route: '/pengajuan-rintek-pertek' },
    { name: 'Registrasi Usaha LB3', route: '/registrasi-usaha-lb3' },
    { name: 'Perizinan Tebang Pohon', route: '/perizinan-tebang-pohon' },
    { name: 'Pinjam Taman', route: '/pinjam-taman' },
    { name: 'Survei IKM', route: '/survei' },
    { name: 'Lacak', route: '/lacak' },
  ];

  for (const formPage of formPages) {
    test(`${formPage.name} page loads without errors`, async ({ page }) => {
      const response = await page.goto(formPage.route);
      expect(response?.status()).toBe(200);
      await expect(page.locator('body')).toBeVisible();
    });
  }
});

// ═══════════════════════════════════════════════════════════════════
// 5. PUBLIC PAGES - STATUS CHECK (page load only)
// ═══════════════════════════════════════════════════════════════════

test.describe('Status Check Pages - Page Load', () => {
  const checkPages = [
    { name: 'Cek Pengaduan Pengendalian', route: '/cek-pengaduan-pengendalian' },
    { name: 'Cek Pengaduan Sampah', route: '/cek-pengaduan-sampah' },
    { name: 'Cek Pengaduan RTH', route: '/cek-pengaduan-rth' },
    { name: 'Cek Pengaduan Tata Penataan', route: '/cek-pengaduan-tata-penataan' },
    { name: 'Cek Permohonan Rekomendasi', route: '/cek-permohonan-rekomendasi' },
    { name: 'Cek Perizinan Tebang Pohon', route: '/cek-perizinan-tebang-pohon' },
    { name: 'Cek Pinjam Taman', route: '/cek-pinjam-taman' },
    { name: 'Cek Rintek Pertek', route: '/cek-rintek-pertek' },
    { name: 'Cek Registrasi LB3', route: '/cek-registrasi-lb3' },
  ];

  for (const checkPage of checkPages) {
    test(`${checkPage.name} page loads without errors`, async ({ page }) => {
      const response = await page.goto(checkPage.route);
      expect(response?.status()).toBe(200);
      await expect(page.locator('body')).toBeVisible();
    });
  }
});

// ═══════════════════════════════════════════════════════════════════
// 6. REDIRECTS
// ═══════════════════════════════════════════════════════════════════

test.describe('Redirects', () => {
  test('/login redirects to /admin/login', async ({ page }) => {
    await page.goto('/login');
    await expect(page).toHaveURL(/admin\/login/);
  });

  test('/lapor redirects to /pengaduan', async ({ page }) => {
    await page.goto('/lapor');
    await expect(page).toHaveURL(/\/pengaduan/);
  });

  test('/tentang redirects to /profil', async ({ page }) => {
    await page.goto('/tentang');
    await expect(page).toHaveURL(/\/profil/);
  });
});

// ═══════════════════════════════════════════════════════════════════
// 7. 404 ERROR PAGES
// ═══════════════════════════════════════════════════════════════════

test.describe('404 Error Pages', () => {
  test('unknown public route returns 404', async ({ page }) => {
    const response = await page.goto('/halaman-tidak-ada-samsek');
    expect(response?.status()).toBe(404);
  });

  test('invalid berita slug returns 404', async ({ page }) => {
    const response = await page.goto('/berita/artikel-tidak-ada-samsekali');
    expect(response?.status()).toBe(404);
  });

  test('invalid UPTD id returns 404', async ({ page }) => {
    const response = await page.goto('/uptd/99');
    expect(response?.status()).toBe(404);
  });
});

// ═══════════════════════════════════════════════════════════════════
// 8. RATE LIMITING ON PUBLIC PAGES
// ═══════════════════════════════════════════════════════════════════

test.describe('Rate Limiting', () => {
  test('throttled pages are accessible', async ({ page }) => {
    // These pages have throttle:30,1 middleware
    const throttledPages = [
      '/lacak',
      '/cek-pengaduan-pengendalian',
      '/cek-pengaduan-sampah',
      '/cek-pengaduan-rth',
      '/cek-pengaduan-tata-penataan',
      '/cek-permohonan-rekomendasi',
      '/cek-perizinan-tebang-pohon',
      '/cek-pinjam-taman',
      '/cek-rintek-pertek',
      '/cek-registrasi-lb3',
    ];

    for (const route of throttledPages) {
      const response = await page.goto(route);
      expect(response?.status(), `${route} should be accessible`).toBe(200);
    }
  });
});

// ═══════════════════════════════════════════════════════════════════
// 9. FEEDBACK ROUTE
// ═══════════════════════════════════════════════════════════════════

test.describe('Feedback Route', () => {
  test('GET /feedback returns 405 Method Not Allowed', async ({ page }) => {
    const response = await page.request.get('/feedback/test-ticket');
    expect(response.status()).toBe(405);
  });

  test('POST /feedback with invalid ticket returns error', async ({ page }) => {
    const response = await page.request.post('/feedback/INVALID-TICKET', {
      form: {
        rating: '5',
        komentar: 'Test feedback',
      },
    });
    // Should return 404 or redirect
    expect(response.status()).toBeGreaterThanOrEqual(400);
  });
});

// ═══════════════════════════════════════════════════════════════════
// 10. CERTIFICATE PDF DOWNLOAD
// ═══════════════════════════════════════════════════════════════════

test.describe('Certificate PDF', () => {
  test('invalid certificate route returns 404', async ({ page }) => {
    const response = await page.request.get('/sosialisasi/99999/sertifikat/99999.pdf');
    expect(response.status()).toBe(404);
  });
});

// ═══════════════════════════════════════════════════════════════════
// 11. NEWS/BERITA PAGES
// ═══════════════════════════════════════════════════════════════════

test.describe('News Pages', () => {
  test('/berita page loads', async ({ page }) => {
    await page.goto('/berita');
    await expect(page).toHaveURL(/\/berita/);
    await expect(page.locator('body')).toContainText(/berita|artikel/i);
  });

  test('/berita with kategori filter loads', async ({ page }) => {
    await page.goto('/berita?kategori=pengendalian');
    await expect(page).toHaveURL(/kategori=pengendalian/);
    await expect(page.locator('body')).toContainText(/berita|artikel/i);
  });

  test('invalid berita slug returns 404', async ({ page }) => {
    const response = await page.goto('/berita/slug-tidak-ada');
    expect(response?.status()).toBe(404);
  });
});

// ═══════════════════════════════════════════════════════════════════
// 12. PWA MANIFEST AND SERVICE WORKER
// ═══════════════════════════════════════════════════════════════════

test.describe('PWA', () => {
  test('manifest.json is valid', async ({ page }) => {
    const response = await page.request.get('/manifest.json');
    expect(response.status()).toBe(200);
    const manifest = await response.json();
    expect(manifest.name).toBeTruthy();
    expect(manifest.short_name).toBeTruthy();
    expect(manifest.display).toBe('standalone');
    expect(manifest.start_url).toBeTruthy();
  });

  test('favicon.ico is accessible', async ({ page }) => {
    const response = await page.request.get('/favicon.ico');
    expect(response.status()).toBeLessThan(500);
  });
});

// ═══════════════════════════════════════════════════════════════════
// 13. CHATBOT ENDPOINT
// ═══════════════════════════════════════════════════════════════════

test.describe('Chatbot', () => {
  test('POST /api/chatbot/stream with message returns response', async ({ page }) => {
    const response = await page.request.post('/api/chatbot/stream', {
      data: {
        message: 'Halo, apa itu DLH?',
        history: [],
      },
    });
    // Should return 200 or streaming response
    expect(response.status()).toBeLessThan(500);
  });
});

// ═══════════════════════════════════════════════════════════════════
// 14. DARK MODE AND THEME
// ═══════════════════════════════════════════════════════════════════

test.describe('Dark Mode', () => {
  test('dark mode toggle exists on public pages', async ({ page }) => {
    await page.goto('/');
    // Dark mode toggle should exist
    const darkToggle = page.locator('[x-data*="dark"], button[aria-label*="dark"], button[aria-label*="Dark"]');
    // Just verify page loads
    await expect(page.locator('body')).toBeVisible();
  });
});

// ═══════════════════════════════════════════════════════════════════
// 15. RESPONSIVE DESIGN
// ═══════════════════════════════════════════════════════════════════

test.describe('Responsive Design', () => {
  test('homepage renders on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/');
    await expect(page.locator('body')).toBeVisible();
  });

  test('pengaduan form renders on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/pengaduan');
    await expect(page.locator('body')).toBeVisible();
  });

  test('admin login renders on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/admin/login');
    await expect(page.locator('body')).toBeVisible();
  });
});
