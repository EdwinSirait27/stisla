<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>HRX — Asian Bay Development</title>

    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="{{ asset('library/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/AsianBay logomark.ico') }}">

    <!-- Font: Cormorant Garamond (elegant display) + DM Sans (clean body) -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink:        #0a0b0d;
            --surface:    #111318;
            --panel:      #181b22;
            --border:     rgba(255,255,255,0.07);
            --border-hi:  rgba(255,255,255,0.14);
            --gold:       #c9a84c;
            --gold-dim:   #a8863a;
            --gold-glow:  rgba(201,168,76,0.18);
            --text-hi:    #f0ede8;
            --text-mid:   #9a9694;
            --text-low:   #555;
            --danger:     #e05c5c;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--ink);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* ───── AMBIENT NOISE TEXTURE ───── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        /* ───── LEFT: FORM PANEL ───── */
        .form-side {
            position: relative;
            z-index: 2;
            flex: 0 0 480px;
            background: var(--panel);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 52px;
            border-right: 1px solid var(--border);
        }

        /* Subtle diagonal line accent */
        .form-side::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 1px;
            height: 40%;
            background: linear-gradient(to bottom, var(--gold), transparent);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo {
            width: 88px;
            height: 88px;
            /* border-radius: 10px; */
            object-fit: cover;
            display: block;
            /* border: 1px solid var(--border-hi); */
            -webkit-user-drag: none;
            user-select: none;
        }

        .brand-name {
            /* font-family: 'serif'; */
            font-size: 1.7rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            color: var(--text-hi);
        }

        .brand-tag {
            font-size: 0.68rem;
            letter-spacing: 0.22em;
            /* text-transform: uppercase; */
            color: var(--gold);
            margin-top: 1px;
        }

        /* ── Form center block ── */
        .form-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 0;
        }

        .form-heading {
            margin-bottom: 36px;
        }

        .form-heading h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.6rem;
            font-weight: 600;
            color: var(--text-hi);
            line-height: 1.1;
            letter-spacing: 0.01em;
        }

        .form-heading p {
            margin-top: 10px;
            font-size: 0.88rem;
            color: var(--text-mid);
            font-weight: 300;
            letter-spacing: 0.02em;
        }

        /* ── Inputs ── */
        .field-group {
            margin-bottom: 20px;
        }

        label.field-label {
            display: block;
            font-size: 0.72rem;
            letter-spacing: 0.18em;
            /* text-transform: uppercase; */
            color: var(--text-mid);
            margin-bottom: 8px;
        }

        .field-wrap {
            position: relative;
        }

        .field-wrap input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px 18px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem;
            color: var(--text-hi);
            outline: none;
            transition: border-color 0.25s, background 0.25s, box-shadow 0.25s;
            letter-spacing: 0.02em;
        }

        .field-wrap input::placeholder { color: var(--text-low); }

        .field-wrap input:focus {
            border-color: var(--gold);
            background: rgba(201,168,76,0.05);
            box-shadow: 0 0 0 3px var(--gold-glow);
        }

        .field-wrap .toggle-eye {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-low);
            cursor: pointer;
            transition: color 0.2s;
            font-size: 0.85rem;
        }

        .field-wrap .toggle-eye:hover { color: var(--gold); }

        /* ── Remember ── */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
            margin-top: -4px;
        }

        .remember-row input[type="checkbox"] {
            appearance: none;
            width: 16px;
            height: 16px;
            border: 1px solid var(--border-hi);
            border-radius: 4px;
            background: transparent;
            cursor: pointer;
            position: relative;
            flex-shrink: 0;
            transition: border-color 0.2s, background 0.2s;
        }

        .remember-row input[type="checkbox"]:checked {
            background: var(--gold);
            border-color: var(--gold);
        }

        .remember-row input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            top: 2px; left: 5px;
            width: 4px; height: 8px;
            border: 2px solid #111;
            border-top: none;
            border-left: none;
            transform: rotate(40deg);
        }

        .remember-row label {
            font-size: 0.82rem;
            color: var(--text-mid);
            cursor: pointer;
            user-select: none;
        }

        /* ── Submit button ── */
        .btn-login {
            width: 100%;
            padding: 15px 20px;
            background: linear-gradient(135deg, var(--gold) 0%, #b8922e 100%);
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            letter-spacing: 0.14em;
            /* text-transform: uppercase; */
            color: #1a1408;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.3s;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(201,168,76,0.35);
        }

        .btn-login:hover::before { opacity: 1; }

        .btn-login:active { transform: translateY(0); }

        /* ── Pilihan login lain ── */
        .login-alt {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0 14px;
            font-size: 0.75rem;
            color: var(--text-low);
        }
        .login-alt::before, .login-alt::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .btn-alt {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 13px 20px;
            background: transparent;
            border: 1px solid var(--border-hi);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.86rem;
            color: var(--text-hi);
            text-decoration: none;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
        }
        .btn-alt:hover { border-color: var(--gold); background: rgba(201,168,76,0.06); color: var(--text-hi); text-decoration: none; }
        .btn-alt:focus-visible, .btn-login:focus-visible { outline: 2px solid var(--gold); outline-offset: 2px; }

        .field-hint {
            margin-top: 8px;
            font-size: 0.76rem;
            color: var(--text-low);
            line-height: 1.5;
        }

        /* ── Alerts ── */
        .alert-dark-danger {
            background: rgba(224,92,92,0.12);
            border: 1px solid rgba(224,92,92,0.3);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #f5a0a0;
            font-size: 0.83rem;
        }

        .alert-dark-danger ul { margin: 0; padding-left: 16px; }

        .alert-dark-success {
            background: rgba(92,184,128,0.12);
            border: 1px solid rgba(92,184,128,0.3);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #9fdcb5;
            font-size: 0.83rem;
        }
        .alert-dark-danger li { margin: 2px 0; }

        /* ── Footer ── */
        .form-footer {
            font-size: 0.72rem;
            color: var(--text-low);
            letter-spacing: 0.05em;
        }

        /* ───── RIGHT: VISUAL PANEL ───── */
        .visual-side {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .visual-side .bg-img {
            position: absolute;
            inset: 0;
            background: url('{{ asset('img/unsplash/bg.jpg') }}') center center / cover no-repeat;
            filter: brightness(0.35) saturate(0.7);
            transition: filter 6s ease;
        }

        .visual-side:hover .bg-img {
            filter: brightness(0.42) saturate(0.8);
        }

        /* Vignette */
        .visual-side::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, var(--panel) 0%, transparent 30%),
                        linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 50%);
            z-index: 1;
        }

        /* Gold horizontal rule accent */
        .visual-side::after {
            content: '';
            position: absolute;
            bottom: 130px;
            left: 60px;
            width: 48px;
            height: 2px;
            /* background: var(--gold); */
            z-index: 2;
        }

        .visual-content {
            position: absolute;
            bottom: 60px;
            left: 60px;
            z-index: 2;
            max-width: 480px;
        }

        .greeting-label {
            font-size: 0.72rem;
            letter-spacing: 0.3em;
            /* text-transform: uppercase; */
            color: var(--gold);
            margin-bottom: 14px;
            display: block;
        }

       
        .visual-heading {
            /* font-family: 'Cormorant Garamond', serif; */
            font-size: clamp(2.4rem, 3.5vw, 3.4rem);
            font-weight: 600;
            color: var(--text-hi);
            line-height: 1.1;
            letter-spacing: 0.01em;
        }

        .visual-sub {
            margin-top: 14px;
            font-size: 0.88rem;
            color: rgba(255,255,255,0.45);
            font-weight: 300;
            letter-spacing: 0.04em;
        }

        /* Decorative grid dots */
        .dot-grid {
            position: absolute;
            top: 50px; right: 60px;
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            z-index: 2;
            opacity: 0.25;
        }

        .dot-grid span {
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: var(--gold);
            display: block;
        }

        /* ── Responsive ── */
        @media (max-width: 991px) {
            .visual-side { display: none; }

            .form-side {
                flex: 1;
                border-right: none;
                padding: 40px 32px;
            }
        }

        /* ── Page load fade-in ── */
        .form-side, .visual-side {
            animation: fadeUp 0.7s ease both;
        }

        .visual-side { animation-delay: 0.1s; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .field-group {
            animation: fadeUp 0.55s ease both;
        }
        .field-group:nth-child(1) { animation-delay: 0.25s; }
        .field-group:nth-child(2) { animation-delay: 0.35s; }
    </style>
</head>
<body>
    <div class="form-side">
        <div class="brand">
            <img src="{{ asset('img/AsianBay.png') }}" alt="HRX Logo" class="brand-logo">
            <div>
                <div class="brand-name">HRX</div>
                <div class="brand-tag">Human Resource System</div>
            </div>
        </div>
        <div class="form-body">
            <div class="form-heading">
                <h2>Sign In</h2>
                <p>Access your workspace securely</p>
            </div>

            @php($mode2fa = request('mode') === '2fa' || old('email') !== null)

            <div>
                {{-- Alerts (dipakai kedua mode) --}}
                @if (session('success'))
                    <div class="alert-dark-success" role="status">{{ session('success') }}</div>
                @endif
                @if (session('warning') || session('error'))
                    <div class="alert-dark-danger" role="alert">{{ session('warning') ?? session('error') }}</div>
                @endif
                @if ($errors->has('throttle'))
                    <div class="alert-dark-danger">{{ $errors->first('throttle') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert-dark-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>

            @if (!$mode2fa)
            <form action="{{ route('session') }}" method="POST" novalidate>
                @csrf

                <!-- Username -->
                <div class="field-group">
                    <label class="field-label" for="username">Username</label>
                    <div class="field-wrap">
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                            placeholder="Enter your username">
                    </div>
                </div>

                <!-- Password -->
                <div class="field-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-wrap">
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            placeholder="Enter your password" style="padding-right: 44px;">
                        <span class="toggle-eye" onclick="togglePassword()">
                            <i id="eyeIcon" class="fa fa-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-login">Continue</button>
            </form>

            <div class="login-alt">atau</div>
            <a href="{{ route('login', ['mode' => '2fa']) }}" class="btn-alt">
                <i class="fas fa-mobile-screen-button" aria-hidden="true"></i>Login dengan 2FA
            </a>
            @else
            <form action="{{ route('session.2fa-email') }}" method="POST" novalidate>
                @csrf

                <div class="field-group">
                    <label class="field-label" for="email">Email</label>
                    <div class="field-wrap">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            autocomplete="email" placeholder="nama@perusahaan.com">
                    </div>
                    <p class="field-hint">Khusus akun yang sudah diizinkan HR dan sudah scan QR 2FA. Setelah ini masukkan kode 6 digit dari aplikasi Authenticator.</p>
                </div>

                <button type="submit" class="btn-login">Lanjut ke kode 2FA</button>
            </form>

            <div class="login-alt">atau</div>
            <a href="{{ route('login') }}" class="btn-alt">
                <i class="fas fa-key" aria-hidden="true"></i>Login dengan username dan password
            </a>
            @endif
        </div>
        <!-- Footer -->
        <div class="form-footer">
            &copy; {{ date('Y') }} HRX · Developed by Edwin Sirait
        </div>
    </div>
    <!-- Visual Side -->
    <div class="visual-side">
        <div class="bg-img"></div>
        <div class="visual-content">
            <span class="greeting-label" id="greeting">Good Day</span>
            <div class="visual-heading">
                Welcome Back.
            </div>
            <p class="visual-sub">Bali, Indonesia · {{ date('l, d F Y') }}</p>
        </div>
    </div>
    {{-- SweetAlert Force Login --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('confirm_force_login'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                title: 'Confirm Login',
                text: @json(session('confirm_force_login')['message'] ?? ''),
                icon: 'question',
                background: '#181b22',
                color: '#f0ede8',
                iconColor: '#c9a84c',
                showCancelButton: true,
                confirmButtonColor: '#c9a84c',
                cancelButtonColor: '#555',
                confirmButtonText: 'Yes, Continue',
                cancelButtonText: 'Abort',
                backdrop: 'rgba(0,0,0,0.7)'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.action = '{{ route('session.confirm') }}';
                    form.method = 'POST';
                    form.style.display = 'none';
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden'; csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);
                    const fields = {
                        token: @json(session('confirm_force_login')['token'] ?? ''),
                    };
                    for (const [name, value] of Object.entries(fields)) {
                        const input = document.createElement('input');
                        input.type = 'hidden'; input.name = name; input.value = value;
                        form.appendChild(input);
                    }
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
    @endif

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function getGreeting() {
            const h = new Date().getHours();
            const g = h >= 5 && h < 10  ? 'Good Morning'   :
                      h >= 10 && h < 15 ? 'Good Afternoon' :
                      h >= 15 && h < 19 ? 'Good Evening'   : 'Good Night';
            document.getElementById('greeting').textContent = g;
        }

        window.onload = getGreeting;
    </script>

    <script src="{{ asset('library/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('library/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
