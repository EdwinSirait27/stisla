{{-- resources/views/auth/two-factor-setup.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Two-Factor Authentication</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.4);
        }

        .icon-wrap {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .icon-wrap i {
            color: #fff;
            font-size: 22px;
        }

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
            margin-bottom: 28px;
        }

        /* Steps */
        .steps {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 28px;
        }

        .step {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #2a2d3a;
            border: 1px solid #3a3d4a;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .step-content p {
            color: #cbd5e1;
            font-size: 14px;
            line-height: 1.5;
        }

        .step-content small {
            color: #475569;
            font-size: 12px;
        }

        /* QR Code */
        .qr-wrap {
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            display: inline-flex;
            margin: 12px 0;
        }

        .qr-wrap svg {
            display: block;
        }

        /* Manual secret */
        .secret-box {
            background: #0f1117;
            border: 1px solid #2a2d3a;
            border-radius: 10px;
            padding: 12px 16px;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .secret-text {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #a78bfa;
            letter-spacing: 2px;
            word-break: break-all;
        }

        .copy-btn {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: color 0.2s;
            flex-shrink: 0;
        }

        .copy-btn:hover {
            color: #a78bfa;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #2a2d3a;
            margin: 24px 0;
        }

        /* Form */
        .form-label {
            display: block;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }

        .otp-inputs {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .otp-input {
            width: 48px;
            height: 56px;
            background: #0f1117;
            border: 1px solid #2a2d3a;
            border-radius: 10px;
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            color: #f1f5f9;
            outline: none;
            transition: border-color 0.2s;
            caret-color: transparent;
        }

        .otp-input:focus {
            border-color: #6366f1;
        }

        .otp-input.filled {
            border-color: #4f46e5;
            color: #a78bfa;
        }

        /* Hidden real input */
        #code {
            display: none;
        }

        .hint {
            color: #475569;
            font-size: 12px;
            margin-bottom: 20px;
        }

        /* Error */
        .alert-error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Warning */
        .alert-warning {
            background: rgba(251, 191, 36, 0.08);
            border: 1px solid rgba(251, 191, 36, 0.2);
            color: #fbbf24;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
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
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .app-badges {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .badge-app {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0f1117;
            border: 1px solid #2a2d3a;
            border-radius: 8px;
            padding: 6px 12px;
            color: #64748b;
            font-size: 12px;
        }

        .badge-app i {
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="icon-wrap">
            <i class="fas fa-shield-halved"></i>
        </div>

        <h1>Setup Two-Factor Authentication</h1>
        <p class="subtitle">Tambahkan lapisan keamanan ekstra ke akun Anda. Setelah setup, setiap login memerlukan kode
            6-digit dari aplikasi Authenticator.</p>

        @if (session('warning'))
            <div class="alert-warning">
                <i class="fas fa-triangle-exclamation"></i>
                {{ session('warning') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <i class="fas fa-circle-exclamation"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="steps">
            {{-- Step 1 --}}
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-content">
                    <p>Install aplikasi Authenticator di HP Anda</p>
                    <div class="app-badges" style="margin-top:8px">
                        <span class="badge-app"><i class="fab fa-google" style="color:#4285f4"></i> Google
                            Authenticator</span>
                        <span class="badge-app"><i class="fas fa-key" style="color:#0070ba"></i> Authy</span>
                        <span class="badge-app"><i class="fas fa-lock" style="color:#4f46e5"></i> Microsoft
                            Authenticator</span>
                    </div>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-content">
                    <p>Scan QR code berikut menggunakan aplikasi Authenticator</p>

                    <div style="margin-top:12px">
                        <div class="qr-wrap">
                            {!! $qrCodeSvg !!}
                        </div>
                    </div>

                    <p style="margin-top:12px; color:#475569; font-size:13px">
                        Tidak bisa scan? Masukkan kode ini secara manual:
                    </p>
                    <div class="secret-box">
                        <span class="secret-text" id="secretText">{{ $secret }}</span>
                        <button type="button" class="copy-btn" onclick="copySecret()" title="Salin kode">
                            <i class="fas fa-copy" id="copyIcon"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-content">
                    <p>Masukkan 6-digit kode dari aplikasi Authenticator untuk mengkonfirmasi</p>
                </div>
            </div>
        </div>

        <hr class="divider">

        <form method="POST" action="{{ route('2fa.setup.confirm') }}" id="setupForm">
            @csrf

            <label class="form-label">Kode Verifikasi</label>
            <div class="otp-inputs" id="otpInputs">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]">
            </div>
            <input type="hidden" name="code" id="code">

            <p class="hint"><i class="fas fa-clock" style="margin-right:4px"></i>Kode berubah setiap 30 detik</p>

            <button type="submit" class="btn-primary" id="submitBtn" disabled>
                <i class="fas fa-check-circle" style="margin-right:8px"></i>Aktifkan 2FA
            </button>
        </form>
    </div>

    <script>
        // ── OTP Input Handler ─────────────────────────────────────────
        const inputs = document.querySelectorAll('.otp-input');
        const hidden = document.getElementById('code');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('setupForm');

        function syncHidden() {
            const val = Array.from(inputs).map(i => i.value).join('');
            hidden.value = val;
            submitBtn.disabled = val.length < 6;
            inputs.forEach(inp => inp.classList.toggle('filled', inp.value !== ''));
            return val;
        }

        function tryAutoSubmit() {
            const val = syncHidden(); // ✅ sync DULU baru cek panjang
            if (val.length === 6) {
                setTimeout(() => form.submit(), 200);
            }
        }
        inputs.forEach((inp, idx) => {
            inp.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 1);
                syncHidden();
                if (this.value && idx < inputs.length - 1) {
                    inputs[idx + 1].focus();
                }
                tryAutoSubmit();
            });
            inp.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && idx > 0) {
                    inputs[idx - 1].value = '';
                    inputs[idx - 1].focus();
                    syncHidden();
                }
            });

            inp.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData)
                    .getData('text').replace(/\D/g, '').slice(0, 6);
                paste.split('').forEach((char, i) => {
                    if (inputs[i]) inputs[i].value = char;
                });
                tryAutoSubmit(); // ✅ paste juga trigger auto-submit
                inputs[Math.min(paste.length, inputs.length - 1)]?.focus();
            });
        });

        // ✅ Safety net — force sync tepat sebelum form submit
        form.addEventListener('submit', function() {
            hidden.value = Array.from(inputs).map(i => i.value).join('');
            console.log('SUBMIT with code:', hidden.value);
        });

        inputs[0].focus();

        // ── Copy secret ───────────────────────────────────────────────
        function copySecret() {
            const text = document.getElementById('secretText').innerText;
            navigator.clipboard.writeText(text).then(() => {
                const icon = document.getElementById('copyIcon');
                icon.className = 'fas fa-check';
                icon.style.color = '#22c55e';
                setTimeout(() => {
                    icon.className = 'fas fa-copy';
                    icon.style.color = '';
                }, 2000);
            });
        }
    </script>
</body>

</html>
