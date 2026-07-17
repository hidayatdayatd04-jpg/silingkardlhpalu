
# ============================================================
# Script: scrape_dlh_sulteng.ps1
# Tujuan: Scraping berita dari https://dlh.sultengprov.go.id/berita
#          dan menghasilkan file PHP seeder untuk Laravel
# ============================================================

$baseUrl = "https://dlh.sultengprov.go.id"
$totalPages = 7  # Berdasarkan pagination yang ditemukan
$articles = @()

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Scraping Berita DLH Sulteng" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# ---- Fungsi: Bersihkan HTML tag ----
function Strip-HtmlTags {
    param([string]$html)
    # Load assembly untuk HttpUtility jika belum ada
    try {
        Add-Type -AssemblyName System.Web -ErrorAction SilentlyContinue
    } catch {}
    
    $html = $html -replace '<[^>]+>', ' '
    $html = $html -replace '\s+', ' '
    
    # Coba gunakan HttpUtility jika sukses diload, jika tidak gunakan basic decoding
    try {
        $html = [System.Web.HttpUtility]::HtmlDecode($html)
    }
    catch {
        # Basic decoding fallback jika assembly tidak tersedia
        $html = $html -replace '&nbsp;', ' '
        $html = $html -replace '&lt;', '<'
        $html = $html -replace '&gt;', '>'
        $html = $html -replace '&amp;', '&'
        $html = $html -replace '&quot;', '"'
        $html = $html -replace '&#039;', "'"
    }
    return $html.Trim()
}

# ---- Fungsi: Escape PHP string ----
function Escape-PhpString {
    param([string]$str)
    $str = $str -replace "\\", "\\\\"
    $str = $str -replace "'", "\\'"
    return $str
}

# ---- Fungsi: Parse tanggal Indonesia ----
function Parse-IndonesianDate {
    param([string]$dateStr)
    $bulan = @{
        'Januari' = '01'; 'Februari' = '02'; 'Maret' = '03'; 'April' = '04';
        'Mei' = '05'; 'Juni' = '06'; 'Juli' = '07'; 'Agustus' = '08';
        'September' = '09'; 'Oktober' = '10'; 'November' = '11'; 'Desember' = '12'
    }
    $dateStr = $dateStr.Trim()
    foreach ($b in $bulan.Keys) {
        if ($dateStr -match $b) {
            $dateStr = $dateStr -replace $b, $bulan[$b]
        }
    }
    # Format: "23 09 2025" -> "2025-09-23"
    if ($dateStr -match '(\d{1,2})\s+(\d{2})\s+(\d{4})') {
        $day = $Matches[1].PadLeft(2, '0')
        $month = $Matches[2]
        $year = $Matches[3]
        return "$year-$month-$day"
    }
    return $null
}

# ---- Fungsi: Ambil detail artikel ----
function Get-ArticleDetail {
    param([string]$url, [string]$slug)

    try {
        $webClient = New-Object System.Net.WebClient
        $webClient.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36")
        $webClient.Encoding = [System.Text.Encoding]::UTF8
        
        $html = $webClient.DownloadString($url)
        $webClient.Dispose()

        # Ambil judul
        if ($html -match '<div class="judul-single-konten">\s*(.*?)\s*</div>') {
            $judul = Strip-HtmlTags $Matches[1]
        } else {
            $judul = $slug -replace '-', ' '
        }

        # Ambil tanggal
        $tanggal = $null
        if ($html -match '<i class="fas fa-calendar-alt"></i>\s*(\d{1,2}\s+\w+\s+\d{4})') {
            $tanggal = Parse-IndonesianDate $Matches[1]
        }

        # Ambil thumbnail
        $thumbnail = $null
        if ($html -match 'img src="(https://dlh\.sultengprov\.go\.id/img/informasi/berita/[^"]+)"') {
            $thumbnail = $Matches[1]
        }

        # Ambil konten - area antara "ISI KONTEN" comment dan div share
        $konten = ""
        if ($html -match '<!-- ISI KONTEN -->\s*<div class="mt-0">\s*(.*?)\s*</div>\s*\r?\n\s*<div class="a2a_kit') {
            $konten = $Matches[1].Trim()
        } elseif ($html -match '<div class="mt-0">\s*(<p>.*?</p>(?:\s*<p>.*?</p>)*)\s*</div>') {
            $konten = $Matches[1].Trim()
        }

        # Bersihkan konten dari tag-tag yang tidak diperlukan
        if ($konten) {
            $konten = $konten -replace '<o:p[^>]*>.*?</o:p>', ''
            $konten = $konten -replace '<span[^>]*>', ''
            $konten = $konten -replace '</span>', ''
            $konten = $konten -replace '\s+', ' '
            $konten = $konten.Trim()
        }

        if (-not $konten -or $konten.Length -lt 20) {
            $konten = "<p>Berita dari Dinas Lingkungan Hidup Provinsi Sulawesi Tengah.</p>"
        }

        return @{
            judul = $judul
            tanggal = $tanggal
            thumbnail = $thumbnail
            konten = $konten
        }
    }
    catch {
        Write-Host "  [ERROR] Gagal ambil detail: $url - $($_.Exception.Message)" -ForegroundColor Red
        return $null
    }
}

# ============================================================
# STEP 1: Scrape listing semua halaman
# ============================================================
Write-Host "`n[STEP 1] Scraping listing berita dari $totalPages halaman..." -ForegroundColor Yellow

$articleLinks = @()

for ($page = 1; $page -le $totalPages; $page++) {
    $listUrl = "$baseUrl/berita?page_hal=$page"
    Write-Host "  Halaman $page/$totalPages : $listUrl" -ForegroundColor Gray

    try {
        $webClient = New-Object System.Net.WebClient
        $webClient.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36")
        $webClient.Encoding = [System.Text.Encoding]::UTF8
        
        $html = $webClient.DownloadString($listUrl)
        $webClient.Dispose()

        # Cari semua artikel: href="/detail/..." dengan judul
        $matches_all = [regex]::Matches($html, 'href="(https://dlh\.sultengprov\.go\.id/detail/([^"]+))"')
        
        $pageLinks = @()
        foreach ($m in $matches_all) {
            $link = $m.Groups[1].Value
            $slug = $m.Groups[2].Value
            
            # Hindari duplikat
            if (-not ($articleLinks | Where-Object { $_.url -eq $link }) -and 
                -not ($pageLinks | Where-Object { $_.url -eq $link })) {
                $pageLinks += @{ url = $link; slug = $slug }
            }
        }
        
        Write-Host "    Ditemukan $($pageLinks.Count) artikel baru" -ForegroundColor Green
        $articleLinks += $pageLinks

        # Delay kecil agar tidak overload server
        Start-Sleep -Milliseconds 300
    }
    catch {
        Write-Host "  [ERROR] Gagal load halaman $page : $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`nTotal artikel ditemukan di listing: $($articleLinks.Count)" -ForegroundColor Cyan

# ============================================================
# STEP 2: Scrape detail setiap artikel dan download gambar
# ============================================================
Write-Host "`n[STEP 2] Scraping detail setiap artikel dan download gambar..." -ForegroundColor Yellow

$storageDir = Join-Path (Split-Path $PSScriptRoot -Parent) "storage\app\public\berita"
if (-not (Test-Path $storageDir)) {
    New-Item -ItemType Directory -Path $storageDir -Force | Out-Null
}

$i = 0
foreach ($link in $articleLinks) {
    $i++
    Write-Host "  [$i/$($articleLinks.Count)] $($link.slug)" -ForegroundColor Gray
    
    $detail = Get-ArticleDetail -url $link.url -slug $link.slug
    
    if ($detail) {
        $localThumbnailPath = ""
        if ($detail.thumbnail) {
            # Ambil nama file dari URL thumbnail
            $fileName = Split-Path $detail.thumbnail -Leaf
            # Bersihkan karakter aneh pada nama file jika ada
            $fileName = $fileName -replace '[^\w\.-]', ''
            
            if ($fileName) {
                $destPath = Join-Path $storageDir $fileName
                $localThumbnailPath = "berita/$fileName"
                
                # Unduh gambar jika belum ada di lokal
                if (-not (Test-Path $destPath)) {
                    try {
                        Write-Host "    Downloading: $fileName ..." -ForegroundColor DarkGray
                        $dlClient = New-Object System.Net.WebClient
                        $dlClient.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36")
                        $dlClient.DownloadFile($detail.thumbnail, $destPath)
                        $dlClient.Dispose()
                    }
                    catch {
                        Write-Host "    [WARNING] Gagal download gambar: $_" -ForegroundColor Yellow
                        $localThumbnailPath = "" # fallback ke kosong jika gagal download
                    }
                }
            }
        }

        $articles += @{
            judul     = $detail.judul
            slug      = $link.slug
            tanggal   = $detail.tanggal
            thumbnail = $localThumbnailPath
            konten    = $detail.konten
            url       = $link.url
        }
        Write-Host "    OK: $($detail.judul)" -ForegroundColor Green
    }

    # Delay agar tidak banned
    Start-Sleep -Milliseconds 800
}

Write-Host "`nTotal artikel berhasil diambil: $($articles.Count)" -ForegroundColor Cyan

# ============================================================
# STEP 3: Generate PHP Seeder
# ============================================================
Write-Host "`n[STEP 3] Membuat file PHP seeder..." -ForegroundColor Yellow

$seederPath = Join-Path (Split-Path $PSScriptRoot -Parent) "database\seeders\ArtikelSeeder.php"

$phpContent = @'
<?php

namespace Database\Seeders;

use App\Enums\ArtikelKategori;
use App\Enums\ArtikelStatus;
use App\Models\Artikel;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * ArtikelSeeder
 * 
 * Data berita diambil (scraping) dari website resmi:
 * Dinas Lingkungan Hidup Provinsi Sulawesi Tengah
 * https://dlh.sultengprov.go.id/berita
 * 
 * Total artikel: TOTAL_COUNT
 * Terakhir diperbarui: LAST_UPDATED
 */
class ArtikelSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('username', 'superadmin')->first();

        $articles = [
'@

$seederPath2 = "database\seeders\ArtikelSeeder.php"
$phpContent = $phpContent -replace "TOTAL_COUNT", $articles.Count
$phpContent = $phpContent -replace "LAST_UPDATED", (Get-Date -Format "yyyy-MM-dd HH:mm:ss")

foreach ($art in $articles) {
    $judul = $art.judul
    $slug = $art.slug
    $konten = $art.konten
    $thumbnail = if ($art.thumbnail) { $art.thumbnail } else { "" }
    $tanggal = if ($art.tanggal) { $art.tanggal } else { (Get-Date).ToString("yyyy-MM-dd") }

    $phpContent += @"
            [
                'judul'          => <<<'EOT'
$judul
EOT
,
                'slug'           => '$slug',
                'thumbnail'      => '$thumbnail',
                'konten'         => <<<'EOT'
$konten
EOT
,
                'kategori'       => ArtikelKategori::UMUM->value,
                'tanggal_publish'=> '$tanggal',
            ],
"@
}

$phpContent += @'
        ];

        foreach ($articles as $data) {
            Artikel::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'judul'           => $data['judul'],
                    'konten'          => $data['konten'],
                    'thumbnail'       => $data['thumbnail'] ?: null,
                    'kategori'        => $data['kategori'],
                    'tanggal_publish' => $data['tanggal_publish'],
                    'status'          => ArtikelStatus::PUBLISHED->value,
                    'user_id'         => $author?->id,
                ],
            );
        }
    }
}
'@

# Tulis ke file UTF-8 tanpa BOM
$outputPath = Join-Path (Split-Path $PSScriptRoot -Parent) "database\seeders\ArtikelSeeder.php"
[System.IO.File]::WriteAllText($outputPath, $phpContent)

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  SELESAI!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Total artikel: $($articles.Count)" -ForegroundColor White
Write-Host "File seeder:   $outputPath" -ForegroundColor White
Write-Host "`nJalankan seeder dengan:" -ForegroundColor Yellow
Write-Host "  php artisan db:seed --class=ArtikelSeeder" -ForegroundColor White
Write-Host "  atau" -ForegroundColor Gray
Write-Host "  php artisan migrate:fresh --seed" -ForegroundColor White
