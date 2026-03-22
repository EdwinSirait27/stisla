{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>HRX — Asian Bay Develo</title>

    <link rel="stylesheet" href="{{ asset('library/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="icon" type="image/png" href="{{ asset('img/abd.ico') }}">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-dark: #0a58ca;
            --bg-dark: #1f2228;
            --card-bg: #f5f6f8;
            --text-dark: #222;
            --text-muted: #6c757d;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        .login-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            padding: 40px 35px;
            width: 400px;
            margin: auto;
            z-index: 2;
        }

        .login-card img {
            display: block;
            margin: 0 auto 20px;
        }

        .login-card h4 {
            font-weight: 600;
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 10px;
        }

        .login-card p {
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ccc;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            width: 100%;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .bg-image {
            flex: 1;
            background: url('{{ asset('img/unsplash/bg.jpg') }}') center center/cover no-repeat;
            position: relative;
        }

        .bg-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
        }

        .bg-text {
            position: absolute;
            bottom: 50px;
            left: 50px;
            color: #fff;
        }

        .bg-text h1 {
            font-size: 48px;
            font-weight: 700;
        }

        .bg-text p {
            font-size: 18px;
            opacity: 0.85;
        }
         img.no-drag {
        -webkit-user-drag: none;
        user-select: none;
    }

        @media (max-width: 991px) {
            .bg-image {
                display: none;
            }

            .login-card {
                width: 90%;
                border-radius: 10px;
                box-shadow: none;
            }
        }
        
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="d-flex align-items-center justify-content-center flex-column" style="flex: 1;">
            <div class="login-card">
                <img src="{{ asset('img/abd.jpg') }}" alt="logo" width="150"  class="no-drag">
                <h4>Welcome to HRX</h4>
                <p>Please sign in to continue</p>

                <form action="{{ route('session') }}" method="POST" novalidate>
                    @csrf

                    @if ($errors->has('throttle'))
                        <div class="alert alert-danger">{{ $errors->first('throttle') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    @if (session('confirm_force_login'))
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                Swal.fire({
                                    title: 'Confirmation Login',
                                    html: `{{ session('confirm_force_login')['message'] }}`,
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Yes, Continue',
                                    cancelButtonText: 'Abort',
                                    backdrop: 'rgba(0,0,0,0.5)'
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        const form = document.createElement('form');
                                        form.action = '{{ route('session') }}';
                                        form.method = 'POST';
                                        form.style.display = 'none';
                                        const csrf = document.createElement('input');
                                        csrf.type = 'hidden';
                                        csrf.name = '_token';
                                        csrf.value = '{{ csrf_token() }}';
                                        form.appendChild(csrf);

                                        const fields = {
                                            username: '{{ session('confirm_force_login')['username'] }}',
                                            password: '{{ session('confirm_force_login')['password'] }}',
                                            remember: '{{ session('confirm_force_login')['remember'] ? '1' : '0' }}',
                                            force_login: '1'
                                        };
                                        for (const [name, value] of Object.entries(fields)) {
                                            const input = document.createElement('input');
                                            input.type = 'hidden';
                                            input.name = name;
                                            input.value = value;
                                            form.appendChild(input);
                                        }
                                        document.body.appendChild(form);
                                        form.submit();
                                    }
                                });
                            });
                        </script>
                    @endif

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input id="username" type="text" class="form-control" name="username" required autofocus
                            placeholder="Enter your username">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input id="password" type="password" class="form-control" name="password" required
                                placeholder="Enter your password">
                            <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                <i id="eyeIcon" class="fa fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label text-muted" for="remember">Remember me</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>

        <div class="bg-image">
            <div class="bg-overlay"></div>
            <div class="bg-text">
                <h1 id="greeting">Good Day</h1>
                <p>Bali, Indonesia</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function getGreeting() {
            const hours = new Date().getHours();
            const greeting =
                hours >= 5 && hours < 10 ? 'Good Morning' :
                hours >= 10 && hours < 15 ? 'Good Afternoon' :
                hours >= 15 && hours < 19 ? 'Good Evening' :
                'Good Night';
            document.getElementById('greeting').textContent = greeting;
        }

        window.onload = getGreeting;
    </script>

    <script src="{{ asset('library/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('library/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html> --}}
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

            <form action="{{ route('session') }}" method="POST" novalidate>
                @csrf

                {{-- Alerts --}}
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

                <!-- Username -->
                <div class="field-group">
                    <label class="field-label" for="username">Username</label>
                    <div class="field-wrap">
                        <input id="username" type="text" name="username" required autofocus
                            placeholder="Enter your username">
                    </div>
                </div>

                <!-- Password -->
                <div class="field-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-wrap">
                        <input id="password" type="password" name="password" required
                            placeholder="Enter your password" style="padding-right: 44px;">
                        <span class="toggle-eye" onclick="togglePassword()">
                            <i id="eyeIcon" class="fa fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Remember -->
                {{-- <div class="remember-row">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Keep me signed in</label>
                </div> --}}

                <button type="submit" class="btn-login">Continue</button>
            </form>
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
                html: `{{ session('confirm_force_login')['message'] }}`,
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
                    form.action = '{{ route('session') }}';
                    form.method = 'POST';
                    form.style.display = 'none';
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden'; csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);
                    const fields = {
                        username: '{{ session('confirm_force_login')['username'] }}',
                        password: '{{ session('confirm_force_login')['password'] }}',
                        remember: '{{ session('confirm_force_login')['remember'] ? '1' : '0' }}',
                        force_login: '1'
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
