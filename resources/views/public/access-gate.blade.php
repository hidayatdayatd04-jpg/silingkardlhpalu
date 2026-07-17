<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Akses Terbatas - DLH Kota Palu</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(16, 185, 129, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 70% 60%, rgba(6, 182, 212, 0.06) 0%, transparent 50%);
            animation: drift 20s ease-in-out infinite;
        }

        @keyframes drift {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(2%, -1%) rotate(1deg); }
            66% { transform: translate(-1%, 2%) rotate(-1deg); }
        }

        .gate-card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            margin: 20px;
            box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #10b981, #06b6d4);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        }

        .logo-icon svg {
            width: 32px;
            height: 32px;
            color: white;
        }

        .gate-title {
            font-size: 22px;
            font-weight: 700;
            color: #f1f5f9;
            text-align: center;
            letter-spacing: -0.02em;
        }

        .gate-subtitle {
            font-size: 14px;
            color: #94a3b8;
            text-align: center;
            margin-top: 8px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
            letter-spacing: 0.02em;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #f1f5f9;
            font-size: 16px;
            font-family: 'SF Mono', 'Fira Code', monospace;
            letter-spacing: 0.15em;
            text-align: center;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input::placeholder {
            color: #475569;
            letter-spacing: 0.1em;
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
            background: rgba(255, 255, 255, 0.08);
        }

        .form-error {
            margin-top: 8px;
            font-size: 13px;
            color: #f87171;
            text-align: center;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            letter-spacing: 0.02em;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #34d399, #10b981);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .gate-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: #475569;
        }

        @media (max-width: 480px) {
            .gate-card {
                padding: 36px 24px;
                margin: 16px;
                border-radius: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="gate-card">
        <div class="logo-wrap">
            <div class="logo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
            <h1 class="gate-title">Akses Terbatas</h1>
            <p class="gate-subtitle">Masukkan kode akses untuk melanjutkan ke portal DLH Kota Palu</p>
        </div>

        <form method="POST" action="{{ route('access-gate.verify') }}">
            @csrf
            <div class="form-group">
                <label for="access_code" class="form-label">Kode Akses</label>
                <input
                    type="text"
                    id="access_code"
                    name="access_code"
                    class="form-input"
                    placeholder="XXXX-XXXXXX"
                    value="{{ old('access_code') }}"
                    required
                    autofocus
                    autocomplete="off"
                >
                @error('access_code')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-submit">Masuk</button>
        </form>

        <p class="gate-footer">Dinas Lingkungan Hidup Kota Palu</p>
    </div>
</body>

</html>
