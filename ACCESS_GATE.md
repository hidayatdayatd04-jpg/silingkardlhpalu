# Panduan Hapus Fitur Access Gate

Fitur ini menambahkan halaman kode akses sebelum pengunjung bisa masuk ke website.
Berguna untuk testing di hosting sebelum go public.

**Kode akses:** `DLH-483921`

---

## File yang Dibuat (3 file baru)

| # | File | Keterangan |
|---|------|------------|
| 1 | `app/Http/Middleware/EnsureSiteAccess.php` | Middleware yang mengecek session `site_access_granted`. Jika belum ada, redirect ke halaman gate. |
| 2 | `app/Http/Controllers/AccessGateController.php` | Controller untuk menampilkan halaman gate, verifikasi kode akses, dan logout gate. |
| 3 | `resources/views/public/access-gate.blade.php` | View halaman input kode akses (standalone, tanpa layout utama). |
| 4 | `ACCESS_GATE.md` | File dokumentasi ini. |

---

## File yang Diubah (2 file)

### 1. `bootstrap/app.php`

**Yang diubah:**
- Tambahkan `use App\Http\Middleware\EnsureSiteAccess;` (line 4)
- Tambahkan alias middleware: `'site.access' => EnsureSiteAccess::class`
- Tambahkan ke web group: `$middleware->appendToGroup('web', EnsureSiteAccess::class);`

### 2. `routes/web.php`

**Yang diubah:**
- Tambahkan `use App\Http\Controllers\AccessGateController;` (line 3)
- Tambahkan route gate di awal file (line 30-35):
  ```php
  Route::withoutMiddleware(\App\Http\Middleware\EnsureSiteAccess::class)->group(function () {
      Route::get('/gate', [AccessGateController::class, 'show'])->name('access-gate.show');
      Route::post('/gate', [AccessGateController::class, 'verify'])->name('access-gate.verify');
      Route::post('/gate/logout', [AccessGateController::class, 'logout'])->name('access-gate.logout');
  });
  ```
- Tambahkan `->withoutMiddleware(\App\Http\Middleware\EnsureSiteAccess::class)` pada kedua route group admin (line 92 dan 101)

---

## Cara Menghapus Fitur Ini

### Langkah 1: Hapus 3 file baru
```
del app\Http\Middleware\EnsureSiteAccess.php
del app\Http\Controllers\AccessGateController.php
del resources\views\public\access-gate.blade.php
del ACCESS_GATE.md  (file ini)
```

### Langkah 2: Edit `bootstrap/app.php`
Hapus/undo 3 bagian:
1. Hapus baris `use App\Http\Middleware\EnsureSiteAccess;`
2. Hapus `'site.access' => EnsureSiteAccess::class,` dari array alias
3. Hapus `$middleware->appendToGroup('web', EnsureSiteAccess::class);`

### Langkah 3: Edit `routes/web.php`
1. Hapus baris `use App\Http\Controllers\AccessGateController;`
2. Hapus blok route gate (line 30-35):
   ```php
   // ══════════════════ Access Gate ... ══════════════════
   Route::withoutMiddleware(...)->group(function () { ... });
   ```
3. Hapus `->withoutMiddleware(\App\Http\Middleware\EnsureSiteAccess::class)` dari kedua route group admin:
   - Baris 92: `Route::withoutMiddleware(...)->prefix('admin')->name('admin.')->group(`
   - Baris 101: `Route::withoutMiddleware(...)->middleware(['auth', 'admin.access'])->prefix('admin')->name('admin.')->group(`
   Kembalikan ke bentuk semula:
   - `Route::prefix('admin')->name('admin.')->group(`
   - `Route::middleware(['auth', 'admin.access'])->prefix('admin')->name('admin.')->group(`

### Langkah 4: Clear cache
```bash
php artisan cache:clear
php artisan route:clear
```

---

## Cara Kerja

1. Pengunjung mengakses halaman apapun di website
2. Middleware `EnsureSiteAccess` mengecek session `site_access_granted`
3. Jika belum ada → redirect ke `/gate` (halaman input kode)
4. Pengunjung memasukkan kode akses → controller verifikasi → set session → redirect ke `/`
5. Admin panel (`/admin/*`) **tidak terpengaruh** — tetap pakai login admin seperti biasa
6. Untuk logout dari gate: POST ke `/gate/logout` (bisa ditambahkan tombol jika diperlukan)
