{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Login &mdash; MJM</title>
    <link rel="stylesheet" href="{{ asset('library/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png"
        href="{{ asset('img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.ico') }}">
    <link rel="stylesheet" href="{{ asset('library/bootstrap-social/bootstrap-social.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-keyboard/build/css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/simple-keyboard/build/index.min.js"></script>
    <style>
        .keyboard-container {
            display: none;
            position: absolute;
            bottom: 170px;
            left: 40%;
            transform: translateX(-50%) scale(1.9);
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .keyboard-container.active {
            display: block;
        }
        .keyboard {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .keyboard button {
            margin: 8px;
            padding: 12px 20px;
            font-size: 18px;
            font-weight: bold;
            color: #333333;
            background-color: #f0f0f0;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .keyboard button:hover {
            background-color: #d0d0d0;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        .keyboard button:active {
            background-color: #b0b0b0;
            transform: translateY(0);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .keyboard .btn-clear,
        .keyboard .btn-close {
            background-color: #ff6b6b;
            color: white;
        }
        .keyboard .btn-close {
            background-color: #6b6bff;
        }
        .keyboard .btn-clear:hover,
        .keyboard .btn-close:hover {
            opacity: 0.9;
        }

        .keyboard .btn-clear,
        .keyboard .btn-close {
            padding: 4px 8px;
            font-size: 12px;
            min-width: 50px;
        }
    </style>
</head>

<body>
    <div id="app">
        <section class="section">
            <div class="d-flex align-items-stretch flex-wrap">
                <div class="col-lg-4 col-md-6 col-12 order-lg-1 min-vh-100 order- bg-dark">
                    <div class="m-3 p-4">

                        <img src="{{ asset('img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.png') }}"
                            alt="logo" width="80" class="light mb-5 mt-2">


                        <p class="text-muted">Please Login.</p>
                        <form action="{{ route('session') }}" method="POST" class="needs-validation" novalidate="">
                            @csrf
                            @if ($errors->has('throttle'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('throttle') }}
                                </div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                            @if (session('confirm_force_login'))
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
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
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                // Submit form secara otomatis
                                                const form = document.createElement('form');
                                                form.action = '{{ route('session') }}';
                                                form.method = 'POST';
                                                form.style.display = 'none';
                                                // Tambahkan CSRF token
                                                const csrf = document.createElement('input');
                                                csrf.type = 'hidden';
                                                csrf.name = '_token';
                                                csrf.value = '{{ csrf_token() }}';
                                                form.appendChild(csrf);
                                                // Tambahkan input fields
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
                            <div class="form-group">
                                <label class="text-muted" for="Username">Username</label>
                                <input id="username" type="text" class="form-control" name="username" tabindex="1"
                                    required autofocus placeholder="Username">

                                <div class="invalid-feedback">
                                    Please fill your username
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="d-block">
                                    <label class="text-muted" for="password" class="control-label">Password</label>
                                </div>
                                <div class="input-group">
                                    <input id="password" type="password" class="form-control" name="password"
                                        tabindex="2" required max="20" placeholder="Password">
                                    <div class="input-group-append">
                                        <span class="input-group-text" onclick="togglePassword()"
                                            style="cursor: pointer;">
                                            <i id="eyeIcon" class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Please fill your password
                                    </div>
                                </div>
                            </div>
                            <script>
                                function togglePassword() {
                                    let passwordInput = document.getElementById('password');
                                    let eyeIcon = document.getElementById('eyeIcon');

                                    if (passwordInput.type === "password") {
                                        passwordInput.type = "text";
                                        eyeIcon.classList.remove("fa-eye");
                                        eyeIcon.classList.add("fa-eye-slash");
                                    } else {
                                        passwordInput.type = "password";
                                        eyeIcon.classList.remove("fa-eye-slash");
                                        eyeIcon.classList.add("fa-eye");
                                    }
                                }
                            </script>


                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="remember" class="custom-control-input" tabindex="3"
                                        id="remember">
                                    <label class="custom-control-label" for="remember">Remember Me</label>
                                </div>
                            </div>


                            <div class="form-group text-right">

                                <button type="submit" class="btn btn-primary btn-lg btn-icon icon-right"
                                    tabindex="4">
                                    Login
                                </button>
                            </div>


                        </form>


                        <div class="text-small mt-5 text-center">

                            <div class="mt-2">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-12 order-lg-2 min-vh-100 background-walk-y position-relative overlay-gradient-bottom order-1"
                    data-background="{{ asset('img/unsplash/bg.jpg') }}">
                    <div class="absolute-bottom-left index-2">
                        <div class="text-light p-5 pb-2">
                            <div class="mb-5 pb-3">

                                <h1 class="display-4 font-weight-bold mb-2" id="greeting">Good</h1>
                                <script>
                                    function getGreeting() {
                                        const now = new Date();
                                        const hours = now.getUTCHours() + 8;
                                        let greeting = 'Good';
                                        if (hours >= 5 && hours < 10) {
                                            greeting = 'Good Morning';
                                        } else if (hours >= 10 && hours < 15) {
                                            greeting = 'Good Afternoon';
                                        } else if (hours >= 15 && hours < 18) {
                                            greeting = 'Good Afternoon';
                                        } else {
                                            greeting = 'Good Night';
                                        }

                                        document.getElementById('greeting').textContent = greeting;
                                    }

                                    window.onload = getGreeting;
                                </script>
                                <h5 class="font-weight-normal text-muted-transparent">Bali, Indonesia</h5>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script src="{{ asset('library/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('library/popper.js/dist/umd/popper.js') }}"></script>
    <script src="{{ asset('library/tooltip.js/dist/umd/tooltip.js') }}"></script>
    <script src="{{ asset('library/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('library/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('library/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('js/stisla.js') }}"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
</body>
</html> --}}
{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Login — HRX</title>
    <link rel="stylesheet" href="{{ asset('library/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('img/abd.ico') }}">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #242830, #242830);
            height: 100vh;
            margin: 0;
        }

        .login-wrapper {
            display: flex;
            height: 100vh;
        }

        .login-card {
            background: #B0B0B0;
            color: #5b4b3a;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 400px;
            margin: auto;
            position: relative;
            z-index: 2;
        }

        .login-card img {
            display: block;
            margin: 0 auto 20px;
        }

        .login-card h4 {
            font-weight: 600;
            color: #000000;
            text-align: center;
            margin-bottom: 10px;
        }

        .login-card p {
            color: #000000;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ccc;
        }

        .form-control:focus {
            border-color: #242830;
            box-shadow: 0 0 0 0.2rem rgba(0, 74, 173, 0.15);
        }

        .btn-primary {
            background-color: #000000;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            width: 100%;
            transition: 0.3s;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #191918;
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

        @media (max-width: 991px) {
            .bg-image {
                display: none;
            }

            .login-card {
                width: 90%;
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="d-flex align-items-center justify-content-center flex-column" style="flex: 1;">
            <div class="login-card">
                <img src="{{ asset('img/abd.png') }}" alt="logo" width="150">
                <h4>Welcome to HRX</h4>
                <p>Please sign in to continue</p>

                <form action="{{ route('session') }}" class="needs-validation" method="POST" novalidate>
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
                            document.addEventListener('DOMContentLoaded', function() {
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
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Submit form secara otomatis
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
                        <label for="username" class="form-label ">Username</label>
                        <input id="username" type="text" class="form-control" name="username" required autofocus
                            placeholder="Enter your username">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label ">Password</label>
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
            const now = new Date();
            const hours = now.getHours();
            let greeting = 'Good Day';
            if (hours >= 5 && hours < 10) greeting = 'Good Morning';
            else if (hours >= 10 && hours < 15) greeting = 'Good Afternoon';
            else if (hours >= 15 && hours < 19) greeting = 'Good Evening';
            else greeting = 'Good Night';
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
    <title>Login — HRX</title>

    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="{{ asset('library/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/abd.ico') }}">

    <!-- Font -->
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
        <!-- FORM SECTION -->
        <div class="d-flex align-items-center justify-content-center flex-column" style="flex: 1;">
            <div class="login-card">
                <img src="{{ asset('img/abd.jpg') }}" alt="logo" width="150"  class="no-drag">
                <h4>Welcome to HRX</h4>
                <p>Please sign in to continue</p>

                <form action="{{ route('session') }}" method="POST" novalidate>
                    @csrf

                    {{-- Error Handling --}}
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

                    {{-- SweetAlert Confirmation --}}
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

                    {{-- Input Fields --}}
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

        <!-- BACKGROUND SECTION -->
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

</html>
