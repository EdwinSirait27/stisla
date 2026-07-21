{{-- resources/views/auth/two-factor-recovery-codes.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery Codes - Two-Factor Authentication</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: #0f1117;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            padding: 24px;
        }

        .card {
            background: #1a1d27;
            border: 1px solid #2a2d3a;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 24px 48px rgba(0,0,0,0.4);
        }

        .icon-wrap {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .icon-wrap i { color: #fff; font-size: 22px; }

        h1 {
            color: #f1f5f9;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        /* Warning banner */
        .alert-warning {
            background: rgba(251,191,36,0.08);
            border: 1px solid rgba(251,191,36,0.25);
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .alert-warning i { color: #fbbf24; margin-top: 2px; flex-shrink: 0; }

        .alert-warning p { color: #fbbf24; font-size: 13px; line-height: 1.6; }

        .alert-warning strong { display: block; margin-bottom: 4px; }

        /* Codes grid */
        .codes-box {
            background: #0f1117;
            border: 1px solid #2a2d3a;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .codes-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .codes-label {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .copy-all-btn {
            background: none;
            border: 1px solid #2a2d3a;
            border-radius: 6px;
            padding: 4px 10px;
            color: #64748b;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .copy-all-btn:hover {
            border-color: #6366f1;
            color: #a78bfa;
        }

        .codes-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .code-item {
            background: #1a1d27;
            border: 1px solid #2a2d3a;
            border-radius: 8px;
            padding: 10px 14px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: 700;
            color: #a78bfa;
            letter-spacing: 2px;
            text-align: center;
            transition: background 0.2s;
            cursor: default;
            user-select: all;
        }

        .code-item:hover { background: #252836; }
        .code-item.used { color: #334155; text-decoration: line-through; }

        /* Divider */
        .divider { border: none; border-top: 1px solid #2a2d3a; margin: 24px 0; }

        /* Info list */
        .info-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 24px;
        }

        .info-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }

        .info-list li i { color: #475569; margin-top: 2px; flex-shrink: 0; width: 14px; text-align: center; }

        /* Button */
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-primary:hover { opacity: 0.9; color: #fff; }

        /* Success badge */
        .success-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.2);
            border-radius: 20px;
            padding: 4px 12px;
            color: #10b981;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="icon-wrap">
        <i class="fas fa-circle-check"></i>
    </div>

    <span class="success-badge">
        <i class="fas fa-shield-check"></i> 2FA Berhasil Diaktifkan
    </span>

    <h1>Simpan Recovery Codes</h1>
    <p class="subtitle">Kode-kode ini hanya ditampilkan <strong style="color:#f1f5f9">satu kali</strong>. Simpan di tempat yang aman.</p>

    <div class="alert-warning">
        <i class="fas fa-triangle-exclamation"></i>
        <p>
            <strong>Penting — simpan sekarang!</strong>
            Recovery codes digunakan untuk masuk jika Anda kehilangan akses ke aplikasi Authenticator.
            Setiap kode hanya bisa dipakai <strong>satu kali</strong>.
        </p>
    </div>

    <div class="codes-box">
        <div class="codes-header">
            <span class="codes-label"><i class="fas fa-key" style="margin-right:6px"></i>Recovery Codes</span>
            <button type="button" class="copy-all-btn" onclick="copyAll()">
                <i class="fas fa-copy" id="copyAllIcon"></i> Salin Semua
            </button>
        </div>
        <div class="codes-grid" id="codesGrid">
            @foreach($codes as $code)
                <div class="code-item">{{ $code }}</div>
            @endforeach
        </div>
    </div>

    <hr class="divider">

    <ul class="info-list">
        <li>
            <i class="fas fa-circle-check" style="color:#10b981"></i>
            Setiap kode hanya bisa digunakan satu kali — setelah dipakai, kode tersebut hangus
        </li>
        <li>
            <i class="fas fa-file-export"></i>
            Simpan di password manager, cetak, atau simpan di lokasi offline yang aman
        </li>
        <li>
            <i class="fas fa-refresh"></i>
            Jika semua kode habis atau HP hilang, hubungi admin untuk reset 2FA
        </li>
        <li>
            <i class="fas fa-eye-slash"></i>
            Halaman ini tidak akan bisa diakses lagi setelah Anda menutupnya
        </li>
    </ul>

    <a href="{{ url('/dashboard') }}" class="btn-primary">
        <i class="fas fa-arrow-right-to-bracket" style="margin-right:8px"></i>Lanjut ke Dashboard
    </a>
</div>

<script>
    function copyAll() {
        const codes = Array.from(document.querySelectorAll('.code-item'))
            .map(el => el.textContent.trim())
            .join('\n');

        navigator.clipboard.writeText(codes).then(() => {
            const icon = document.getElementById('copyAllIcon');
            icon.className = 'fas fa-check';
            icon.style.color = '#10b981';

            const btn = icon.closest('button');
            btn.style.borderColor = '#10b981';
            btn.style.color = '#10b981';

            setTimeout(() => {
                icon.className = 'fas fa-copy';
                icon.style.color = '';
                btn.style.borderColor = '';
                btn.style.color = '';
            }, 2500);
        });
    }

    // Cegah user klik kanan / print halaman ini
    document.addEventListener('contextmenu', e => e.preventDefault());
</script>
</body>
</html>