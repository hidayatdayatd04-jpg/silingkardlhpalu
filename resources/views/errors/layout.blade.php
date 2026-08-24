<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b3a2a">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <title>@yield('title', 'Terjadi gangguan') | DLH Kota Palu</title>
    <style>
        :root {
            color-scheme: light dark;
            --accent: #087954;
            --accent-deep: #063d2d;
            --ink: #13251d;
            --muted: #587064;
            --surface: #ffffff;
            --canvas: #eff5f1;
            --line: #d3e0d8;
            --shadow: 0 24px 70px rgba(6, 61, 45, .12);
        }

        * { box-sizing: border-box; }

        body {
            min-width: 320px;
            min-height: 100dvh;
            margin: 0;
            background:
                radial-gradient(circle at 9% 7%, rgba(99, 178, 126, .17), transparent 25rem),
                radial-gradient(circle at 95% 94%, rgba(8, 121, 84, .10), transparent 30rem),
                var(--canvas);
            color: var(--ink);
            font-family: "Segoe UI", "Noto Sans", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        a { color: inherit; }

        .shell {
            width: min(1120px, calc(100% - 32px));
            min-height: 100dvh;
            margin: 0 auto;
            padding: 26px 0 32px;
            display: flex;
            flex-direction: column;
        }

        .site-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .brand-name {
            display: block;
            color: var(--accent-deep);
            font-size: 14px;
            font-weight: 800;
            letter-spacing: -.01em;
            line-height: 1.2;
        }

        .brand-place {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.2;
        }

        .home-link {
            border-radius: 10px;
            color: var(--accent-deep);
            font-size: 14px;
            font-weight: 700;
            outline: none;
            padding: 10px 12px;
            text-decoration: none;
            transition: background-color .16s ease, transform .16s ease;
        }

        .home-link:hover { background: rgba(255, 255, 255, .72); }
        .home-link:active { transform: translateY(1px); }
        .home-link:focus-visible, .button:focus-visible { box-shadow: 0 0 0 4px rgba(8, 121, 84, .23); }

        .error-panel {
            flex: 1;
            display: grid;
            grid-template-columns: minmax(250px, .92fr) minmax(0, 1.08fr);
            align-items: stretch;
            overflow: hidden;
            margin: clamp(32px, 8vh, 88px) 0 28px;
            border: 1px solid var(--line);
            border-radius: 24px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        .error-visual {
            position: relative;
            isolation: isolate;
            min-height: 350px;
            display: flex;
            align-items: flex-end;
            overflow: hidden;
            padding: clamp(28px, 5vw, 54px);
            background:
                linear-gradient(145deg, #0b4936 0%, #087954 53%, #b8ddbf 54%, #e0f0e2 100%);
        }

        .error-visual::before,
        .error-visual::after {
            position: absolute;
            z-index: -1;
            border: 1px solid rgba(255, 255, 255, .26);
            border-radius: 999px;
            content: "";
        }

        .error-visual::before {
            width: 24rem;
            height: 24rem;
            top: -10rem;
            right: -8rem;
        }

        .error-visual::after {
            width: 16rem;
            height: 16rem;
            bottom: -10rem;
            left: -7rem;
        }

        .error-code {
            position: relative;
            margin: 0;
            color: rgba(255, 255, 255, .96);
            font-size: clamp(76px, 13vw, 152px);
            font-weight: 900;
            letter-spacing: -.09em;
            line-height: .78;
        }

        .visual-caption {
            position: absolute;
            top: clamp(26px, 4vw, 42px);
            left: clamp(28px, 5vw, 54px);
            margin: 0;
            color: rgba(255, 255, 255, .78);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .error-copy {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(32px, 6vw, 76px);
        }

        .eyebrow {
            margin: 0 0 14px;
            color: var(--accent);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .075em;
            text-transform: uppercase;
        }

        h1 {
            max-width: 12ch;
            margin: 0;
            color: var(--ink);
            font-size: clamp(30px, 4vw, 48px);
            font-weight: 800;
            letter-spacing: -.045em;
            line-height: 1.05;
        }

        .message {
            max-width: 55ch;
            margin: 20px 0 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .button {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            background: var(--accent);
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            outline: none;
            padding: 11px 18px;
            text-decoration: none;
            transition: background-color .16s ease, transform .16s ease;
        }

        .button:hover { background: var(--accent-deep); }
        .button:active { transform: translateY(1px); }

        .helper {
            margin: 22px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .site-footer {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
            text-align: center;
        }

        @media (max-width: 720px) {
            .shell { width: min(100% - 24px, 560px); padding-top: 14px; }
            .site-header { padding: 0 4px; }
            .home-link { font-size: 13px; padding-inline: 8px; }
            .error-panel { grid-template-columns: 1fr; margin: 28px 0 22px; border-radius: 20px; }
            .error-visual { min-height: 190px; padding: 30px; }
            .error-code { font-size: clamp(76px, 27vw, 126px); }
            .error-copy { padding: 34px 30px 38px; }
            h1 { max-width: none; }
            .message { font-size: 15px; }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --accent: #42bd82;
                --accent-deep: #dff9e9;
                --ink: #ebf7ef;
                --muted: #a9c2b2;
                --surface: #102119;
                --canvas: #0a1610;
                --line: #294436;
                --shadow: 0 24px 70px rgba(0, 0, 0, .32);
            }

            body {
                background:
                    radial-gradient(circle at 9% 7%, rgba(35, 131, 77, .24), transparent 25rem),
                    radial-gradient(circle at 95% 94%, rgba(8, 121, 84, .16), transparent 30rem),
                    var(--canvas);
            }

            .home-link:hover { background: rgba(255, 255, 255, .07); }
            .button { color: #062d20; }
        }
    </style>
</head>
<body>
    @php
        $actionUrl = trim((string) $__env->yieldContent('action_url')) ?: url('/');
        $actionLabel = trim((string) $__env->yieldContent('action_label')) ?: 'Kembali ke Beranda';
    @endphp
    <main class="shell">
        <header class="site-header">
            <a class="brand" href="{{ url('/') }}" aria-label="Beranda DLH Kota Palu">
                <img class="brand-mark" src="{{ asset('assets/images/logo-web.webp') }}" width="320" height="337" alt="">
                <span>
                    <span class="brand-name">Dinas Lingkungan Hidup</span>
                    <span class="brand-place">Kota Palu</span>
                </span>
            </a>
            <a class="home-link" href="{{ url('/') }}">Ke Beranda</a>
        </header>

        <section class="error-panel" aria-labelledby="error-title">
            <div class="error-visual" aria-hidden="true">
                <p class="visual-caption">DLH Kota Palu</p>
                <p class="error-code">@yield('code', '404')</p>
            </div>
            <div class="error-copy">
                <p class="eyebrow">@yield('eyebrow', 'Informasi layanan')</p>
                <h1 id="error-title">@yield('heading', 'Terjadi gangguan')</h1>
                <div class="message">@yield('content')</div>
                <div class="actions">
                    <a class="button" href="{{ $actionUrl }}">{{ $actionLabel }}</a>
                </div>
                <p class="helper">Jika kendala berulang, silakan coba kembali beberapa saat lagi.</p>
            </div>
        </section>

        <footer class="site-footer">Dinas Lingkungan Hidup Kota Palu</footer>
    </main>
</body>
</html>
