# Generate NSIS installer branding images for SILINGKAR DLH ADMIN.
#   installer-header.bmp  -> 150x57 (wizard header band)
#   installer-sidebar.bmp -> 164x314 (welcome/finish left panel)
# Uses the same brand palette as the web splash:
#   #065f46 -> #064e3b -> #082f40, accents #6ee7b7 / #28c6e8
#
# Usage (from desktop/): powershell -ExecutionPolicy Bypass -File scripts/make-installer-images.ps1

Add-Type -AssemblyName System.Drawing

$ErrorActionPreference = "Stop"

$root  = Split-Path -Parent $PSScriptRoot          # desktop/
$icons = Join-Path $root "src-tauri\icons"
$logoPath = Join-Path $icons "icon.png"

if (-not (Test-Path $logoPath)) {
    throw "Logo not found: $logoPath"
}
$logo = [System.Drawing.Image]::FromFile($logoPath)

function New-Canvas([int]$w, [int]$h) {
    $bmp = New-Object System.Drawing.Bitmap $w, $h, ([System.Drawing.Imaging.PixelFormat]::Format24bppRgb)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit

    # Brand gradient background
    $rect = New-Object System.Drawing.Rectangle 0, 0, $w, $h
    $c1 = [System.Drawing.Color]::FromArgb(255, 6, 95, 70)    # #065f46
    $c2 = [System.Drawing.Color]::FromArgb(255, 8, 47, 64)    # #082f40
    $bg = New-Object System.Drawing.Drawing2D.LinearGradientBrush $rect, $c1, $c2, 55.0
    $g.FillRectangle($bg, $rect)

    # Soft mint glow, bottom-left
    $glow = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(60, 110, 231, 183)) # #6ee7b7
    $g.FillEllipse($glow, [int](-$w * 0.45), [int]($h * 0.55), [int]($w * 1.9), [int]($h * 0.75))
    $glow.Dispose()
    $bg.Dispose()

    return @($bmp, $g)
}

function Center-Format {
    $fmt = New-Object System.Drawing.StringFormat
    $fmt.Alignment = [System.Drawing.StringAlignment]::Center
    $fmt.LineAlignment = [System.Drawing.StringAlignment]::Center
    return $fmt
}

$white = [System.Drawing.Brushes]::White
$mint  = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(255, 110, 231, 183)) # #6ee7b7

# ---------- Sidebar 164x314 ----------
$w = 164; $h = 314
$bmp, $g = New-Canvas $w $h

$g.DrawImage($logo, 38, 52, 88, 88)

$fontTitle = New-Object System.Drawing.Font "Segoe UI", 13, ([System.Drawing.FontStyle]::Bold)
$fontSub   = New-Object System.Drawing.Font "Segoe UI", 7.5, ([System.Drawing.FontStyle]::Bold)
$fontTiny  = New-Object System.Drawing.Font "Segoe UI", 6.5, ([System.Drawing.FontStyle]::Regular)
$fmt = Center-Format

$g.DrawString("SILINGKAR", $fontTitle, $white, (New-Object System.Drawing.RectangleF 0, 158, $w, 30), $fmt)
$g.DrawString("DLH KOTA PALU", $fontSub, $mint, (New-Object System.Drawing.RectangleF 0, 188, $w, 18), $fmt)
$g.DrawString("Sistem Informasi Lingkungan", $fontTiny, $mint, (New-Object System.Drawing.RectangleF 0, 262, $w, 14), $fmt)
$g.DrawString("dan Kebersihan", $fontTiny, $mint, (New-Object System.Drawing.RectangleF 0, 276, $w, 14), $fmt)

$bmp.Save((Join-Path $icons "installer-sidebar.bmp"), [System.Drawing.Imaging.ImageFormat]::Bmp)
$g.Dispose(); $bmp.Dispose()
$fontTitle.Dispose(); $fontSub.Dispose(); $fontTiny.Dispose(); $fmt.Dispose()

# ---------- Header 150x57 ----------
$w = 150; $h = 57
$bmp, $g = New-Canvas $w $h

$g.DrawImage($logo, 10, 12, 32, 32)

$fontTitle = New-Object System.Drawing.Font "Segoe UI", 8.5, ([System.Drawing.FontStyle]::Bold)
$fontSub   = New-Object System.Drawing.Font "Segoe UI", 6.75, ([System.Drawing.FontStyle]::Regular)

$g.DrawString("SILINGKAR", $fontTitle, $white, (New-Object System.Drawing.RectangleF 48, 10, 98, 18))
$g.DrawString("DLH ADMIN", $fontTitle, $white, (New-Object System.Drawing.RectangleF 48, 26, 98, 16))
$g.DrawString("Kota Palu", $fontSub, $mint, (New-Object System.Drawing.RectangleF 48, 41, 98, 13))

$bmp.Save((Join-Path $icons "installer-header.bmp"), [System.Drawing.Imaging.ImageFormat]::Bmp)
$g.Dispose(); $bmp.Dispose()
$fontTitle.Dispose(); $fontSub.Dispose()

$mint.Dispose()
$logo.Dispose()

Write-Host "OK: installer-header.bmp + installer-sidebar.bmp written to $icons"
