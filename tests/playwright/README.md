# Playwright E2E - DLH Palu

Suite ini menguji Laravel 12 + Blade + Tailwind CSS + Livewire v4 dari sisi browser.

## Cara Jalan

1. Siapkan database testing atau staging.

```bash
php artisan migrate:fresh --seed
php artisan serve --host=127.0.0.1 --port=8000
```

2. Install dependency browser.

```bash
npm install
npx playwright install chromium
```

3. Jalankan test.

```bash
npm run test:e2e
```

Mode lain:

```bash
npm run test:e2e:ui
npm run test:e2e:report
npx playwright test --config=tests/playwright/playwright.config.ts auth/
npx playwright test --config=tests/playwright/playwright.config.ts admin/
npx playwright test --config=tests/playwright/playwright.config.ts e2e/
npx playwright test --config=tests/playwright/playwright.config.ts public/
```

## Environment

Default base URL adalah `http://localhost:8000`. Bisa dioverride lewat env langsung atau `.env.testing`.

```env
BASE_URL=https://dlh.astrantia.site
```

Akun admin untuk testing:

| User | Password | Role | Grup |
|------|----------|------|------|
| `superadmin` | `superadmin123` | Kepala Bidang | Semua grup |
| `pengendalian` | `pengendalian123` | Bidang Pengendalian | pengendalian |
| `sampah-lb3` | `sampah123` | Sampah & LB3 | sampah-lb3 |
| `tata-penataan` | `tata123` | Tata Penataan | tata-penataan |
| `rth` | `rth123` | RTH | rth |

## Struktur

```text
tests/playwright/
  playwright.config.ts
  tsconfig.json
  README.md
  admin/
    resources.ts          — Load AdminRegistry via PHP exec
    crud.spec.ts          — Generic CRUD for 27 admin resources
  auth/
    login.spec.ts         — Login/logout/auth tests
    roles.spec.ts         — Role-based CRUD for 4 bidang accounts
  e2e/
    flow.spec.ts          — Public submit → admin verify (5 forms)
  public/
    forms.spec.ts         — 10 public form validation tests
  fixtures/
    auth.ts               — Admin login fixture
    form.ts               — Form fill helpers + upload fixtures
```

## Cakupan Test

### Auth & Roles (44 tests)

- Login/Logout semua 5 akun admin
- `admin.access` middleware — setiap role dibatasi sesuai `allowedGroups()`
- 403 untuk resource di luar grup
- CRUD per role: index, create, edit, delete, export

### Admin CRUD Generik (27 resource)

- Grup Pengendalian: pengaduan-pengendalian, permohonan-rekomendasi, pengajuan-rintek-pertek
- Grup Sampah & LB3: registrasi-usaha-lb3, jenis-lb3, titik-tpa, titik-tpst, titik-tps, bank-sampah, jadwal-armada, statistik-sampah
- Grup RTH: perizinan-tebang-pohon, pinjam-taman, aset-rth, taman-kota, hutan-kota, jalur-hijau, pohon-pelindung, data-tanam-pohon
- Grup Tata Penataan: pengaduan-tata-penataan, objek-pengawasan, sidak, pelanggaran, sanksi, sosialisasi
- Grup Konten & Sistem: artikel, ikm-response, email-notification-log, user

### E2E Flow (6 tests)

Submit dari form publik → verifikasi di admin panel:

| Form Publik | Ticket Pattern | Status |
|-------------|---------------|--------|
| permohonan-rekomendasi | `PMH-XXXX-XXXX` | ✓ |
| pengajuan-rintek-pertek | `RPT-2026-XXXX` | ✓ |
| perizinan-tebang-pohon | `PTP-XXXX-XXXX` | ✓ |
| pinjam-taman | `PJM-XXXX-XXXX` | ✓ |
| registrasi-usaha-lb3 | `LB3-XXXX-XXXX` | ✓ |
| jenis-lb3 (admin CRUD) | — | ✓ |

### Public Forms (10 tests)

Validasi input wajib untuk 10 form publik.

## Livewire v4 Integration

Test menggunakan pendekatan hybrid untuk berinteraksi dengan Livewire v4:

- **Text/email/tel fields**: `$wire.set()` via `Livewire.all()[0]` + poll snapshot
- **Select fields**: Native `selectOption()` untuk handle enum values
- **File uploads**: `DataTransfer` API (bukan `setInputFiles()`) agar Livewire v4 memproses upload
- **Radio buttons**: `$wire.set()` via JavaScript

## Report, Screenshot, Trace

Saat gagal, Playwright menyimpan screenshot, video, dan trace ke:

```text
tests/playwright/test-results/
```

HTML report:

```text
tests/playwright/playwright-report/
```

Buka report:

```bash
npm run test:e2e:report
```

Buka trace:

```bash
npx playwright show-trace tests/playwright/test-results/**/trace.zip
```

## Data Test ID Yang Disarankan

Test memakai selector stabil: `name`, `title`, tombol submit, teks modal. Tambahkan `data-testid` berikut untuk ketahanan lebih:

- `admin-resource-form` — `<form id="admin-resource-form">`
- `pengendalian-form` — `<form id="pengendalian-form">`
- `public-form` — wrapper form di setiap Livewire component
- `public-submit` — tombol submit form public
- `public-success-ticket` — area nomor tiket setelah submit

## Troubleshooting

**Test timeout**: Jalankan test spesifik dulu:
```bash
npx playwright test --config=tests/playwright/playwright.config.ts "auth/roles" --grep "Login Bidang Pengendalian"
```

**Lihat screenshot saat gagal**:
```bash
npx playwright show-trace tests/playwright/test-results/<folder-name>/trace.zip
```

**Rate limiting**: Form public punya rate limiter. E2e flow test otomatis clear cache sebelum menjalankan via `artisan cache:clear`.
