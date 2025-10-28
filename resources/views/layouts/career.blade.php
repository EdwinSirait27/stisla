<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Career Portal')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">    
    <link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
/>

    <style>
        :root {
            --primary-color: #BE7A14;
            --secondary-color: #000000;
            --text-dark: #ffffff;
            --text-light: #6b7280;
            --bg-light: #000000;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
        }
        .navbar {
            background: #242830;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 1rem 0;
        }
        .navbar-brand {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .btn-primary-custom {
            background: var(--primary-color);
            color: rgb(0, 0, 0);
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary-custom:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        /* Footer Styles */
        footer {
            background: #1f2937;
            color: #fff;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }
        
        footer h5 {
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        footer a {
            color: #d1d5db;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        footer a:hover {
            color: #fff;
        }
        
        .footer-bottom {
            border-top: 1px solid #374151;
            margin-top: 2rem;
            padding-top: 1.5rem;
        }
        
        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: #374151;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin-right: 0.5rem;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }
        
        @media (max-width: 768px) {
            footer {
                text-align: center;
            }
            
            .social-links {
                justify-content: center;
                margin-top: 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('https://mjm-bali.co.id/') }}">
    <img src="{{ asset('img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.png') }}" alt="PT. Mahendradata Jaya Mandiri" height="80">
</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/Career') }}">Home Page</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/careers') }}">Vacancy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/About-us') }}">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/contact') }}">Contact</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="{{ url('/login') }}" class="btn btn-primary-custom">
                            <i class="fas fa-user"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5><i class="fas fa-briefcase"></i> CareerHub</h5>
                    <p class="text-muted">Platform terpercaya untuk menemukan karir impian Anda. Bergabunglah dengan ribuan profesional yang telah menemukan pekerjaan mereka melalui kami.</p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/p/PT-Mahendradata-Jaya-Mandiri-61579008674856/"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        {{-- <a href="#"><i class="fab fa-glints"></i></a> --}}
                     <a href="https://glints.com/id/companies/pt-mahendradata-jaya-mandiri/55743ab7-b370-4e8a-acaf-598134009924"><i class="fas fa-user-tie"></i></a>


                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Perusahaan</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Tentang Kami</a></li>
                        <li><a href="#">Tim Kami</a></li>
                        <li><a href="#">Karir</a></li>
                        <li><a href="#">Press</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Sumber Daya</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Panduan</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Support</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5>Newsletter</h5>
                    <p class="text-muted">Dapatkan informasi lowongan terbaru langsung ke email Anda.</p>
                    <form class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Email Anda">
                        <button type="submit" class="btn btn-primary-custom">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom text-center">
                <p class="mb-0 text-muted">&copy; {{ date('Y') }}PT. Mahendradata Jaya Mandiri. Created By Edwin Sirait Anjas.</a></p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>