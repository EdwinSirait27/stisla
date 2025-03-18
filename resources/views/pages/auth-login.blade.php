{{-- @extends('layouts.auth')
@section('title', 'Login ')
@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/bootstrap-social/bootstrap-social.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.ico') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-keyboard/build/css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/simple-keyboard/build/index.min.js"></script>
@endpush
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
</style>
@section('main')
    <div class="card card-primary">
        <div class="card-header">
            <h4>Login</h4>
        </div>

        <div class="card-body">
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
                <div class="form-group">
                    <label class="text-muted" for="Username">Username</label>
                    <input id="username" type="text" class="form-control" name="username" tabindex="1" required
                        autofocus placeholder="Username">

                    <div class="invalid-feedback">
                        Please fill your username
                    </div>
                </div>
                <div class="keyboard-container" id="keyboardContainer">
                    <div class="keyboard">
                        <div>
                            <button onclick="insertChar('1')">1</button>
                            <button onclick="insertChar('2')">2</button>
                            <button onclick="insertChar('3')">3</button>
                        </div>
                        <div>
                            <button onclick="insertChar('4')">4</button>
                            <button onclick="insertChar('5')">5</button>
                            <button onclick="insertChar('6')">6</button>
                        </div>
                        <div>
                            <button onclick="insertChar('7')">7</button>
                            <button onclick="insertChar('8')">8</button>
                            <button onclick="insertChar('9')">9</button>
                        </div>
                        <div>
                            <button onclick="clearInput()" class="btn-clear">Clear</button>
                            <button onclick="closeKeyboard()" class="btn-close">Close</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-block">
                        <label class="text-muted" for="password" class="control-label">Password</label>
                    </div>
                    <div class="input-group">
                        <input id="password" type="password" class="form-control" name="password" tabindex="2" required
                            placeholder="Password">
                        <div class="input-group-append">
                            <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;">
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
                <script>
                    function showKeyboard(inputElement) {
                        activeInput = inputElement;
                        const keyboardContainer = document.getElementById('keyboardContainer');
                        keyboardContainer.classList.add('active'); // Tambahkan class active
                    }

                    function closeKeyboard() {
                        const keyboardContainer = document.getElementById('keyboardContainer');
                        keyboardContainer.classList.remove('active'); // Hapus class active
                        activeInput = null;
                    }

                    const usernameInput = document.getElementById('username');
                    const passwordInput = document.getElementById('password');
                    const keyboardContainer = document.getElementById('keyboardContainer');

                    let activeInput = null; // Menyimpan input yang sedang aktif

                    // Menampilkan keyboard saat fokus pada username atau password
                    function showKeyboard(inputElement) {
                        activeInput = inputElement;
                        keyboardContainer.style.display = 'block';
                    }

                    usernameInput.addEventListener('focus', () => showKeyboard(usernameInput));
                    passwordInput.addEventListener('focus', () => showKeyboard(passwordInput));

                    // Menyembunyikan keyboard saat klik di luar input atau keyboard
                    document.addEventListener('click', (event) => {
                        if (
                            activeInput !== null &&
                            !activeInput.contains(event.target) &&
                            !keyboardContainer.contains(event.target)
                        ) {
                            keyboardContainer.style.display = 'none';
                            activeInput = null;
                        }
                    });

                    // Memasukkan karakter ke input yang sedang aktif
                    function insertChar(char) {
                        if (activeInput) {
                            activeInput.value += char;
                        }
                    }

                    // Menghapus isi input yang sedang aktif
                    function clearInput() {
                        if (activeInput) {
                            activeInput.value = '';
                        }
                    }

                    // Menutup keyboard
                    function closeKeyboard() {
                        keyboardContainer.style.display = 'none';
                        activeInput = null;
                    }
                </script>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="remember" class="custom-control-input" tabindex="3" id="remember"
                            required>
                        <label class="custom-control-label" for="remember">Remember Me</label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                        Login
                    </button>
                </div>
            </form>
            <div class="mt-4 mb-3 text-center">
                <div class="text-job text-muted">Login With Social</div>
            </div>
            <div class="row sm-gutters">
                <div class="col-6">
                    <a class="btn btn-block btn-social btn-facebook">
                        <span class="fab fa-facebook"></span> Facebook
                    </a>
                </div>
                <div class="col-6">
                    <a class="btn btn-block btn-social btn-twitter">
                        <span class="fab fa-twitter"></span> Twitter
                    </a>
                </div>
            </div>

        </div>
    </div>
    <div class="text-muted mt-5 text-center">
        Don't have an account? <a href="auth-register.html">Create One</a>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraies -->

    <!-- Page Specific JS File -->
@endpush --}}
@extends('layouts.auth')
@section('title', 'Login')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/bootstrap-social/bootstrap-social.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.ico') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-keyboard/build/css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/simple-keyboard/build/index.min.js"></script>
@endpush

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

    .keyboard-container.active { display: block; }
    .keyboard { display: flex; flex-direction: column; align-items: center; }
    .keyboard button {
        margin: 8px; padding: 12px 20px; font-size: 18px; font-weight: bold;
        color: #333; background-color: #f0f0f0; border: none; border-radius: 10px;
        cursor: pointer; transition: all 0.2s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .keyboard button:hover {
        background-color: #d0d0d0;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }
    .keyboard .btn-clear, .keyboard .btn-close { background-color: #ff6b6b; color: white; }
    .keyboard .btn-close { background-color: #6b6bff; }
    .keyboard .btn-clear:hover, .keyboard .btn-close:hover { opacity: 0.9; }
</style>

@section('main')
    <div class="card card-primary">
        <div class="card-header"><h4>Login</h4></div>
        <div class="card-body">
            <form action="{{ route('session') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                @if ($errors->has('throttle'))
                    <div class="alert alert-danger">{{ $errors->first('throttle') }}</div>
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

                <div class="form-group">
                    <label class="text-muted" for="username">Username</label>
                    <input id="username" type="text" class="form-control" name="username" required autofocus placeholder="Username">
                    <div class="invalid-feedback">Please fill your username</div>
                </div>

                <div class="keyboard-container" id="keyboardContainer">
                    <div class="keyboard">
                        <div>
                            <button onclick="insertChar('1')">1</button>
                            <button onclick="insertChar('2')">2</button>
                            <button onclick="insertChar('3')">3</button>
                        </div>
                        <div>
                            <button onclick="insertChar('4')">4</button>
                            <button onclick="insertChar('5')">5</button>
                            <button onclick="insertChar('6')">6</button>
                        </div>
                        <div>
                            <button onclick="insertChar('7')">7</button>
                            <button onclick="insertChar('8')">8</button>
                            <button onclick="insertChar('9')">9</button>
                        </div>
                        <div>
                            <button onclick="clearInput()" class="btn-clear">Clear</button>
                            <button onclick="closeKeyboard()" class="btn-close">Close</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="text-muted" for="password">Password</label>
                    <div class="input-group">
                        <input id="password" type="password" class="form-control" name="password" required placeholder="Password">
                        <div class="input-group-append">
                            <span class="input-group-text" onclick="togglePassword()" style="cursor: pointer;">
                                <i id="eyeIcon" class="fa fa-eye"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback">Please fill your password</div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Login</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let activeInput = null;
        
        function togglePassword() {
            let passwordInput = document.getElementById('password');
            let eyeIcon = document.getElementById('eyeIcon');
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
            eyeIcon.classList.toggle("fa-eye-slash");
            eyeIcon.classList.toggle("fa-eye");
        }

        function showKeyboard(inputElement) {
            activeInput = inputElement;
            document.getElementById('keyboardContainer').classList.add('active');
        }

        function closeKeyboard() {
            document.getElementById('keyboardContainer').classList.remove('active');
            activeInput = null;
        }

        function insertChar(char) {
            if (activeInput) activeInput.value += char;
        }

        function clearInput() {
            if (activeInput) activeInput.value = '';
        }

        document.getElementById('username').addEventListener('focus', () => showKeyboard(document.getElementById('username')));
        document.getElementById('password').addEventListener('focus', () => showKeyboard(document.getElementById('password')));

        document.addEventListener('click', (event) => {
            if (!activeInput?.contains(event.target) && !document.getElementById('keyboardContainer').contains(event.target)) {
                closeKeyboard();
            }
        });
    </script>
@endsection
