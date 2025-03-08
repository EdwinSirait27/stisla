<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no"
        name="viewport">
    <title>Login &mdash; MJM</title>

    <!-- General CSS Files -->
    <link rel="stylesheet"
        href="{{ asset('library/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />
        <link rel="icon" type="image/png" href="{{ asset('img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.ico')}}">


    <!-- CSS Libraries -->
    <link rel="stylesheet"
        href="{{ asset('library/bootstrap-social/bootstrap-social.css') }}">

    <!-- Template CSS -->
    <link rel="stylesheet"
        href="{{ asset('css/style.css') }}">
    <link rel="stylesheet"
        href="{{ asset('css/components.css') }}">
</head>

<body>
    <div id="app">
        <section class="section">
            <div class="d-flex align-items-stretch flex-wrap">
                <div class="col-lg-4 col-md-6 col-12 order-lg-1 min-vh-100 order-2 bg-dark">
                    <div class="m-3 p-4">
                        {{-- <img src="{{ asset('img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.png') }}"
                            alt="logo"
                            width="80"
                            class="shadow-light rounded-circle mb-5 mt-2"> --}}
                            <img src="{{ asset('img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.png') }}"
     alt="logo"
     width="80"
     class="shadow-light mb-5 mt-2">

                        <h4 class="text-dark font-weight-normal">Welcome to <span class="font-weight-bold">Mahendradata Jaya Mandiri</span>
                        </h4>
                        <p class="text-muted">Please Login.</p>
                        <form method="POST"
                            action="#"
                            class="needs-validation"
                            novalidate="">
                            <div class="form-group">
                                <label class="text-muted" for="Username">Username</label>
                                <input id="username"
                                    type="text"
                                    class="form-control"
                                    name="username"
                                    tabindex="1"
                                    required
                                    autofocus placeholder="Masukkan username Anda">
                                <div class="invalid-feedback">
                                    Please fill your username
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="d-block">
                                    <label for="password"
                                        class="control-label">Password</label>
                                </div>
                                <input id="password"
                                    type="password"
                                    class="form-control"
                                    name="password"
                                    tabindex="2"
                                    required placeholder="Password">
                                <div class="invalid-feedback">
                                    Please fill your password
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                        name="remember"
                                        class="custom-control-input"
                                        tabindex="3"
                                        id="remember-me">
                                    <label class="custom-control-label"
                                        for="remember-me">Remember Me</label>
                                </div>
                            </div>

                            <div class="form-group text-right">
                                
                                <button type="submit"
                                    class="btn btn-primary btn-lg btn-icon icon-right"
                                    tabindex="4">
                                    Login
                                </button>
                            </div>

                           
                        </form>

                        <div class="text-small mt-5 text-center">
                            Copyright &copy; Edwin Sirait.
                            <div class="mt-2">
                                {{-- <a href="#">Privacy Policy</a>
                                <div class="bullet"></div>
                                <a href="#">Terms of Service</a> --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-12 order-lg-2 min-vh-100 background-walk-y position-relative overlay-gradient-bottom order-1"
                    data-background="{{ asset('img/unsplash/bg.jpg') }}">
                    <div class="absolute-bottom-left index-2">
                        <div class="text-light p-5 pb-2">
                            <div class="mb-5 pb-3">
                                {{-- <h1 class="display-4 font-weight-bold mb-2">Selamat </h1>
                                 --}}
                                 <h1 class="display-4 font-weight-bold mb-2" id="greeting">Selamat</h1>
                                 <script>
                                    function getGreeting() {
                                        const now = new Date();
                                        const hours = now.getUTCHours() + 8;  // Bali time (UTC+8)
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
                            
                                    // Call the function when the page is loaded
                                    window.onload = getGreeting;
                                </script>
                                <h5 class="font-weight-normal text-muted-transparent">Bali, Indonesia</h5>
                            </div>
                            {{-- Photo by <a class="text-light bb"
                                target="_blank"
                                href="https://unsplash.com/photos/a8lTjWJJgLA">Justin Kauffman</a> on <a
                                class="text-light bb"
                                target="_blank"
                                href="https://unsplash.com">Unsplash</a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('library/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('library/popper.js/dist/umd/popper.js') }}"></script>
    <script src="{{ asset('library/tooltip.js/dist/umd/tooltip.js') }}"></script>
    <script src="{{ asset('library/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('library/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('library/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('js/stisla.js') }}"></script>

    <!-- JS Libraies -->

    <!-- Page Specific JS File -->

    <!-- Template JS File -->
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
</body>

</html>
