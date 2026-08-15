<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#04140f">
    <title>Sedang Pemeliharaan — DLH Kota Palu</title>
    <style>
        :root {
            --bg-0: #04140f;
            --bg-1: #07251b;
            --emerald: #10b981;
            --emerald-soft: rgba(16, 185, 129, 0.16);
            --text: #e6f4ee;
            --text-dim: rgba(230, 244, 238, 0.62);
            --card: rgba(255, 255, 255, 0.045);
            --card-border: rgba(16, 185, 129, 0.18);
            --font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue",
                    Arial, "Noto Sans", sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { height: 100%; }

        body {
            font-family: var(--font);
            color: var(--text);
            background:
                radial-gradient(1200px 600px at 12% -10%, rgba(16, 185, 129, 0.18), transparent 60%),
                radial-gradient(1000px 500px at 110% 20%, rgba(5, 150, 105, 0.14), transparent 55%),
                linear-gradient(160deg, var(--bg-1) 0%, var(--bg-0) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
            position: relative;
        }

        /* Ornamen blur halus */
        .orb {
            position: fixed;
            border-radius: 999px;
            filter: blur(70px);
            opacity: 0.5;
            pointer-events: none;
            z-index: 0;
        }
        .orb-1 { width: 360px; height: 360px; background: rgba(16,185,129,0.35); top: -120px; left: -80px; animation: float1 14s ease-in-out infinite; }
        .orb-2 { width: 300px; height: 300px; background: rgba(5,150,105,0.30); bottom: -110px; right: -70px; animation: float2 18s ease-in-out infinite; }

        @keyframes float1 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(30px, 40px); } }
        @keyframes float2 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-30px, -30px); } }

        .wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 560px;
            text-align: center;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 16px;
            border-radius: 999px;
            background: var(--emerald-soft);
            border: 1px solid var(--card-border);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #a7f3d0;
            animation: pulse 2.6s ease-in-out infinite;
        }
        .badge .dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--emerald);
            box-shadow: 0 0 0 0 rgba(16,185,129,0.7);
            animation: ping 2s cubic-bezier(0,0,0.2,1) infinite;
        }

        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.72; } }
        @keyframes ping {
            0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.6); }
            70% { box-shadow: 0 0 0 10px rgba(16,185,129,0); }
            100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
        }

        .logo {
            display: block;
            margin: 28px auto 22px;
            height: 84px;
            width: auto;
            filter: drop-shadow(0 6px 22px rgba(16,185,129,0.28));
        }

        h1 {
            font-size: clamp(26px, 5vw, 34px);
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin-bottom: 14px;
        }

        .sub {
            color: var(--text-dim);
            font-size: 15px;
            line-height: 1.7;
            max-width: 440px;
            margin: 0 auto;
        }

        .message {
            margin: 18px auto 0;
            max-width: 460px;
            padding: 14px 18px;
            border-radius: 14px;
            background: var(--card);
            border: 1px solid var(--card-border);
            color: #d7f0e6;
            font-size: 14px;
            line-height: 1.65;
        }

        .countdown {
            margin-top: 30px;
        }
        .cd-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        .cd-box {
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 18px 8px 14px;
            backdrop-filter: blur(6px);
        }
        .cd-num {
            display: block;
            font-size: clamp(26px, 7vw, 38px);
            font-weight: 800;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            color: #ffffff;
            text-shadow: 0 2px 18px rgba(16,185,129,0.35);
        }
        .cd-lab {
            display: block;
            margin-top: 8px;
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-dim);
        }
        .cd-soon {
            font-size: 22px;
            font-weight: 700;
            color: #a7f3d0;
            letter-spacing: 0.02em;
        }
        .cd-passed {
            font-size: 20px;
            font-weight: 700;
            color: #fcd34d;
        }
        .cd-sub {
            margin-top: 8px;
            font-size: 14px;
            color: var(--text-dim);
        }

        .footer {
            margin-top: 34px;
            font-size: 12px;
            color: rgba(230, 244, 238, 0.42);
        }

        .admin-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 26px;
            padding: 9px 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--card-border);
            color: var(--text);
            font-size: 13px;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .admin-pill:hover { background: rgba(255, 255, 255, 0.12); transform: translateY(-1px); }
        .admin-pill-dot { width: 7px; height: 7px; border-radius: 50%; background: #fcd34d; }
        .admin-pill-link { color: #6ee7b7; font-weight: 600; }

        @media (max-width: 420px) {
            .cd-grid { gap: 8px; }
            .cd-box { padding: 14px 4px 10px; }
            .logo { height: 68px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .orb, .badge, .badge .dot { animation: none; }
        }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <main class="wrap">
        <span class="badge">
            <span class="dot"></span> Sedang Pemeliharaan
        </span>

        <img src="{{ $logo }}" alt="Logo DLH Kota Palu" class="logo"
             onerror="this.style.display='none'">

        <h1>Situs Sedang Dalam Pemeliharaan</h1>

        <p class="sub">
            Kami sedang melakukan pembaruan dan peningkatan layanan untuk memberikan
            pengalaman terbaik bagi Anda. Situs akan segera kembali dapat diakses.
        </p>

        <div class="countdown" id="countdown" aria-live="polite"></div>

        @if($isAdmin ?? false)
            <a href="?preview=1" class="admin-pill">
                <span class="admin-pill-dot"></span>
                Anda login sebagai admin &middot; <span class="admin-pill-link">Buka pratinjau situs &rarr;</span>
            </a>
        @endif

        <p class="footer">Dinas Lingkungan Hidup Kota Palu &middot; &copy; {{ date('Y') }}</p>
    </main>

    <script>
        (function () {
            var el = document.getElementById('countdown');
            if (!el) return;

            var estimatedRaw = @json($estimatedAt);

            if (!estimatedRaw) {
                el.innerHTML = '<div class="cd-soon">Segera Kembali</div>';
                return;
            }

            // "Y-m-d H:i:s" -> "Y-m-dTH:i:s" (waktu lokal)
            var target = new Date(String(estimatedRaw).replace(' ', 'T'));
            var hasTarget = !isNaN(target.getTime());

            function pad(n) { return String(n).padStart(2, '0'); }

            function box(val, label) {
                return '<div class="cd-box"><span class="cd-num">' + pad(val) +
                       '</span><span class="cd-lab">' + label + '</span></div>';
            }

            function render() {
                if (!hasTarget) {
                    el.innerHTML = '<div class="cd-soon">Estimasi waktu tidak valid</div>';
                    return;
                }

                var diff = Math.floor((target.getTime() - Date.now()) / 1000);

                if (diff <= 0) {
                    el.innerHTML = '<div class="cd-passed">Estimasi waktu telah terlampaui</div>' +
                        '<div class="cd-sub">Kami segera menormalkan layanan. Terima kasih atas kesabaran Anda.</div>';
                    if (window.__mtimer) clearInterval(window.__mtimer);
                    return;
                }

                var days = Math.floor(diff / 86400); diff %= 86400;
                var hours = Math.floor(diff / 3600); diff %= 3600;
                var minutes = Math.floor(diff / 60);
                var seconds = diff % 60;

                el.innerHTML = '<div class="cd-grid">' +
                    box(days, 'Hari') + box(hours, 'Jam') +
                    box(minutes, 'Menit') + box(seconds, 'Detik') +
                    '</div>';
            }

            render();
            window.__mtimer = setInterval(render, 1000);
        })();
    </script>
</body>
</html>
