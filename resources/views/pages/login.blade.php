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
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%)  scale(1.9);
            background: #f8f9fa;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            
        }
        .keyboard button {
            margin: 10px;
            padding: 10px 15px;
            font-size: 16px;
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

                        <h4 class="text-dark font-weight-normal">Welcome to <span class="font-weight-bold">Mahendradata
                                Jaya Mandiri</span>
                        </h4>
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
                            <div class="form-group">
                                <label class="text-muted" for="Username">Username</label>
                                <input id="username" type="text" class="form-control" name="username" tabindex="1"
                                    required autofocus placeholder="Username">
                                    
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
                                        <button onclick="clearInput()" class="btn btn-danger">Clear</button>
                                        <button onclick="closeKeyboard()" class="btn btn-secondary">Close</button>
                                    </div>
                                </div>
                            </div>
                            
                            
                           
                            
                            <div class="form-group">
                                <div class="d-block">
                                    <label class="text-muted" for="password" class="control-label">Password</label>
                                </div>
                                <div class="input-group">
                                    <input id="password" type="password" class="form-control" name="password"
                                        tabindex="2" required placeholder="Password">
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
                                    <input type="checkbox" name="remember" class="custom-control-input" tabindex="3"
                                        id="remember" required>
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
                            Copyright &copy; Edwin Sirait.
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
                               
                                <h1 class="display-4 font-weight-bold mb-2" id="greeting">Selamat</h1>
                                <script>
                                    function getGreeting() {
                                        const now = new Date();
                                        const hours = now.getUTCHours() + 8;
                                        let greeting = 'Selamat';

                                        if (hours >= 5 && hours < 10) {
                                            greeting = 'Selamat Pagi';
                                        } else if (hours >= 10 && hours < 15) {
                                            greeting = 'Selamat Siang';
                                        } else if (hours >= 15 && hours < 18) {
                                            greeting = 'Selamat Sore';
                                        } else {
                                            greeting = 'Selamat Malam';
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
ini yang dipake
<!DOCTYPE html>
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

.keyboard .btn-clear, .keyboard .btn-close {
    background-color: #ff6b6b; 
    color: white; 
}

.keyboard .btn-close {
    background-color: #6b6bff;
}

.keyboard .btn-clear:hover, .keyboard .btn-close:hover {
    opacity: 0.9; 
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

                        {{-- <h4 class="text-dark font-weight-normal">Welcome to <span class="font-weight-bold">Mahendradata
                                Jaya Mandiri</span>
                        </h4> --}}
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
                            <div class="form-group">
                                <label class="text-muted" for="Username">Username</label>
                                <input id="username" type="text" class="form-control" name="username" tabindex="1"
                                    required autofocus placeholder="Username">
                                    
                                <div class="invalid-feedback">
                                    Please fill your username
                                </div>
                            </div>
                         
                            {{-- <div class="keyboard-container" id="keyboardContainer">
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
                                        <button onclick="clearInput()" class="btn btn-danger">Clear</button>
                                        <button onclick="closeKeyboard()" class="btn btn-secondary">Close</button>
                                    </div>
                                </div>
                            </div>
                            
                             --}}
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
                                    <input id="password" type="password" class="form-control" name="password"
                                        tabindex="2" required placeholder="Password">
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
                                    <input type="checkbox" name="remember" class="custom-control-input" tabindex="3"
                                        id="remember" required>
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
                            Copyright &copy; Edwin Sirait.
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
                               
                                <h1 class="display-4 font-weight-bold mb-2" id="greeting">Selamat</h1>
                                <script>
                                    function getGreeting() {
                                        const now = new Date();
                                        const hours = now.getUTCHours() + 8;
                                        let greeting = 'Selamat';

                                        if (hours >= 5 && hours < 10) {
                                            greeting = 'Selamat Pagi';
                                        } else if (hours >= 10 && hours < 15) {
                                            greeting = 'Selamat Siang';
                                        } else if (hours >= 15 && hours < 18) {
                                            greeting = 'Selamat Sore';
                                        } else {
                                            greeting = 'Selamat Malam';
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

    <!-- Menggunakan asset dari AIO -->
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

</html>
 {{-- <script>
                                const usernameInput = document.getElementById('username');
                                const keyboardContainer = document.getElementById('keyboardContainer');
                        
                                usernameInput.addEventListener('focus', () => {
                                    keyboardContainer.style.display = 'block';
                                });
                        
                                function insertChar(char) {
                                    usernameInput.value += char;
                                }
                        
                                function clearInput() {
                                    usernameInput.value = '';
                                }
                        
                                function closeKeyboard() {
                                    keyboardContainer.style.display = 'none';
                                }
                            </script> --}}
                            
                            {{-- <script>
                                const usernameInput = document.getElementById('username');
                                const keyboardContainer = document.getElementById('keyboardContainer');
                            
                                // Menampilkan keyboard hanya saat usernameInput difokuskan
                                usernameInput.addEventListener('focus', () => {
                                    keyboardContainer.style.display = 'block';
                                });
                            
                                // Menyembunyikan keyboard saat klik di luar input username dan keyboard
                                document.addEventListener('click', (event) => {
                                    if (!usernameInput.contains(event.target) && !keyboardContainer.contains(event.target)) {
                                        keyboardContainer.style.display = 'none';
                                    }
                                });
                            
                                function insertChar(char) {
                                    usernameInput.value += char;
                                }
                            
                                function clearInput() {
                                    usernameInput.value = '';
                                }
                            
                                function closeKeyboard() {
                                    keyboardContainer.style.display = 'none';
                                }
                            </script> --}}