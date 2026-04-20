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
