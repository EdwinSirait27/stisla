<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Two-Factor Authentication</title>
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
            max-width: 420px;
            box-shadow: 0 24px 48px rgba(0,0,0,0.4);
            text-align: center;
        }
        .icon-wrap {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .icon-wrap i { color: #fff; font-size: 26px; }

        h1 {
            color: #f1f5f9;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        /* OTP */
        .otp-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 8px;
        }

        .otp-input {
            width: 52px;
            height: 60px;
            background: #0f1117;
            border: 1px solid #2a2d3a;
            border-radius: 12px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #f1f5f9;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            caret-color: transparent;
        }

        .otp-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }

        .otp-input.filled { border-color: #4f46e5; color: #a78bfa; }

        #code { display: none; }

        .timer-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #475569;
            font-size: 12px;
            margin-bottom: 24px;
        }

        .timer-badge {
            background: #0f1117;
            border: 1px solid #2a2d3a;
            border-radius: 6px;
            padding: 2px 8px;
            font-family: monospace;
            font-size: 13px;
            color: #94a3b8;
            min-width: 36px;
        }

        /* Error */
        .alert-error {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            color: #f87171;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: left;
        }

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
            margin-bottom: 16px;
        }

        .btn-primary:hover { opacity: 0.9; }
        .btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            color: #334155;
            font-size: 12px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-top: 1px solid #2a2d3a;
        }

        /* Recovery code toggle */
        .recovery-section { display: none; }
        .recovery-section.visible { display: block; }

        .recovery-label {
            display: block;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
            text-align: left;
        }

        .recovery-input {
            width: 100%;
            background: #0f1117;
            border: 1px solid #2a2d3a;
            border-radius: 10px;
            padding: 12px 16px;
            color: #f1f5f9;
            font-size: 14px;
            font-family: monospace;
            letter-spacing: 2px;
            outline: none;
            transition: border-color 0.2s;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .recovery-input:focus { border-color: #6366f1; }

        .toggle-recovery {
            background: none;
            border: none;
            color: #6366f1;
            font-size: 13px;
            cursor: pointer;
            text-decoration: underline;
            padding: 0;
        }

        .toggle-recovery:hover { color: #a78bfa; }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #475569;
            font-size: 13px;
            text-decoration: none;
            margin-top: 20px;
            transition: color 0.2s;
        }

        .back-link:hover { color: #94a3b8; }
    </style>
</head>
<body>

<div class="card">
    <div class="icon-wrap">
        <i class="fas fa-mobile-screen-button"></i>
    </div>

    <h1>Verifikasi 2FA</h1>
    <p class="subtitle">Buka aplikasi Authenticator di HP Anda dan masukkan kode 6-digit yang ditampilkan.</p>

    @if($errors->any())
        <div class="alert-error">
            <i class="fas fa-circle-exclamation"></i>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Form TOTP --}}
    <form method="POST" action="{{ route('2fa.verify.post') }}" id="verifyForm">
        @csrf

        <div class="otp-inputs" id="otpInputs">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
        </div>
        <input type="hidden" name="code" id="code">

        <div class="timer-row">
            <i class="fas fa-clock"></i>
            Kode berlaku <span class="timer-badge" id="timer">30</span> detik
        </div>

        <button type="submit" class="btn-primary" id="submitBtn" disabled>
            <i class="fas fa-arrow-right-to-bracket" style="margin-right:8px"></i>Verifikasi
        </button>
    </form>

    <div class="divider">atau</div>

    {{-- Recovery code --}}
    <button type="button" class="toggle-recovery" id="toggleRecovery">
        <i class="fas fa-key" style="margin-right:4px"></i>Gunakan recovery code
    </button>

    <div class="recovery-section" id="recoverySection">
        <form method="POST" action="{{ route('2fa.verify.post') }}" style="margin-top:16px">
            @csrf
            <label class="recovery-label">Recovery Code</label>
            <input type="text"
                name="code"
                class="recovery-input"
                placeholder="XXXXXXXXXX"
                maxlength="10"
                autocomplete="off"
                autofocus>
            <button type="submit" class="btn-primary">
                <i class="fas fa-unlock" style="margin-right:8px"></i>Masuk dengan Recovery Code
            </button>
        </form>
    </div>

    <a href="{{ route('login') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Kembali ke halaman login
    </a>
</div>

<script>
    // ── OTP Handler ───────────────────────────────────────────────
    const inputs    = document.querySelectorAll('.otp-input');
    const hidden    = document.getElementById('code');
    const submitBtn = document.getElementById('submitBtn');

    function syncHidden() {
        const val = Array.from(inputs).map(i => i.value).join('');
        hidden.value = val;
        submitBtn.disabled = val.length < 6;
        inputs.forEach(inp => inp.classList.toggle('filled', inp.value !== ''));
    }

    inputs.forEach((inp, idx) => {
        inp.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 1);
            syncHidden();
            if (this.value && idx < inputs.length - 1) inputs[idx + 1].focus();
            if (Array.from(inputs).map(i => i.value).join('').length === 6) {
                setTimeout(() => document.getElementById('verifyForm').submit(), 200);
            }
        });

        inp.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && idx > 0) {
                inputs[idx - 1].focus();
                inputs[idx - 1].value = '';
                syncHidden();
            }
        });

        inp.addEventListener('paste', function (e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData)
                .getData('text').replace(/\D/g, '').slice(0, 6);
            paste.split('').forEach((char, i) => { if (inputs[i]) inputs[i].value = char; });
            syncHidden();
            inputs[Math.min(paste.length, inputs.length - 1)]?.focus();
        });
    });

    inputs[0].focus();

    // ── Countdown timer ───────────────────────────────────────────
    function startTimer() {
        const timerEl = document.getElementById('timer');
        function tick() {
            const seconds = 30 - (Math.floor(Date.now() / 1000) % 30);
            timerEl.textContent = seconds;
            timerEl.style.color = seconds <= 5 ? '#f87171' : '#94a3b8';
        }
        tick();
        setInterval(tick, 1000);
    }
    startTimer();

    // ── Toggle recovery section ───────────────────────────────────
    document.getElementById('toggleRecovery').addEventListener('click', function () {
        const section = document.getElementById('recoverySection');
        const isVisible = section.classList.toggle('visible');
        this.innerHTML = isVisible
            ? '<i class="fas fa-mobile-screen-button" style="margin-right:4px"></i>Gunakan kode Authenticator'
            : '<i class="fas fa-key" style="margin-right:4px"></i>Gunakan recovery code';
    });
</script>
</body>
</html>