{{-- @extends('layouts.career')
@section('title', 'About Us | PT. Mahendradata Jaya Mandiri')
@section('content')
<style>
    .about-section {
        background: linear-gradient(to bottom, #1f1f1f, #000000);
        color: #ffffff;
        padding: 6rem 0;
    }
    .about-section h1 {
        color: var(--primary-color);
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .about-section p {
        color: #d1d5db;
        font-size: 1.1rem;
    }
    .value-card {
        background: #242830;
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s;
        height: 100%;
    }

    .value-card:hover {
        background: #2f3542;
        transform: translateY(-5px);
    }

    .value-card i {
        color: var(--primary-color);
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .team-section {
        background: #111827;
        padding: 5rem 0;
    }

    .team-card {
        background: #1f2937;
        border-radius: 16px;
        padding: 2rem;
        color: #fff;
        text-align: center;
        transition: all 0.3s;
    }

    .team-card img {
        border-radius: 50%;
        width: 120px;
        height: 120px;
        object-fit: cover;
        margin-bottom: 1rem;
        border: 3px solid var(--primary-color);
    }

    .team-card:hover {
        transform: translateY(-5px);
        background: #242830;
    }
</style>

<section class="about-section text-center">
    <div class="container">
        <h1>Tentang Kami</h1>
        <p class="lead mb-5">PT. Mahendradata Jaya Mandiri adalah perusahaan teknologi yang berfokus pada solusi digital, infrastruktur IT, dan pengembangan sistem informasi. Kami berkomitmen menghadirkan layanan inovatif untuk membantu bisnis berkembang secara berkelanjutan di era digital.</p>

        <div class="row text-start align-items-center">
            <div class="col-md-6 mb-4">
                <img src="{{ asset('img/about-office.jpg') }}" alt="Office" class="img-fluid rounded-4 shadow">
            </div>
            <div class="col-md-6">
                <h3 class="text-warning">Visi Kami</h3>
                <p>Membangun ekosistem teknologi terintegrasi yang mendukung pertumbuhan bisnis dan menciptakan dampak positif bagi masyarakat.</p>

                <h3 class="text-warning mt-4">Misi Kami</h3>
                <ul>
                    <li>Menghadirkan solusi digital yang efisien dan andal.</li>
                    <li>Mendukung transformasi digital di berbagai sektor industri.</li>
                    <li>Mengutamakan kualitas, inovasi, dan kepuasan pelanggan.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container text-center">
        <h2 class="mb-5 text-warning">Nilai-Nilai Utama Kami</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="value-card">
                    <i class="fas fa-lightbulb"></i>
                    <h4>Inovasi</h4>
                    <p>Kami selalu berinovasi untuk menciptakan solusi yang relevan dan berdampak positif bagi pelanggan kami.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <i class="fas fa-users"></i>
                    <h4>Kolaborasi</h4>
                    <p>Keberhasilan kami dibangun di atas kerja sama yang solid antar tim dan mitra strategis.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Integritas</h4>
                    <p>Kami menjunjung tinggi kejujuran dan profesionalisme dalam setiap langkah dan keputusan.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="team-section">
    <div class="container text-center">
        <h2 class="text-warning mb-5">Tim Kami</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-md-3">
                <div class="team-card">
                    <img src="{{ asset('img/team1.jpg') }}" alt="CEO">
                    <h5>Mahendra Putra</h5>
                    <p class="text-muted">CEO & Founder</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="team-card">
                    <img src="{{ asset('img/team2.jpg') }}" alt="CTO">
                    <h5>Edwin Sirait</h5>
                    <p class="text-muted">CTO & System Architect</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="team-card">
                    <img src="{{ asset('img/team3.jpg') }}" alt="HRD">
                    <h5>Anjas Wibowo</h5>
                    <p class="text-muted">HR & Recruitment Lead</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection --}}
@extends('layouts.career')

@section('title', 'About Us - Asian Bay Development')

@push('styles')
    <style>
        /* Hero Section */
        /* .hero-about {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                        url('/img/bglagi.jpg') center/cover no-repeat;
            color: white;
            text-align: center;
            padding: 10rem 1rem;
            position: relative;
        } */
        .hero-about {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                url('/img/bglagi1.jpg') center/cover no-repeat;
            color: white;
            text-align: center;
            padding: 12rem 1rem;
            position: relative;
            opacity: 0;
            /* mulai dari transparan */
            animation: fadeInBackground 2s ease-in-out forwards;
            /* animasi smooth */
        }

        /* efek keyframe fade-in */
        @keyframes fadeInBackground {
            0% {
                opacity: 0;
                transform: scale(1.05);
                /* sedikit zoom-in di awal */
            }

            100% {
                opacity: 1;
                transform: scale(1);
                /* kembali normal */
            }
        }

        .hero-about h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            animation: fadeInDown 1s ease;
        }

        .hero-about p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
            animation: fadeInUp 1.5s ease;
        }

        /* About Section */
        .about-section {
            padding: 5rem 0;
            background-color: #ffffff;
            color: #000;
        }
        

        .about-section h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .about-section p {
            color: #4b5563;
            font-size: 1.05rem;
        }

        .about-image img {
            background-color: #000000;
 width: 75%; 
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .about-image img:hover {
            transform: scale(1.03);
        }

        /* Vision Mission Section */
        .vision-mission {
            background: #242830;
            color: white;
            padding: 5rem 0;
        }

        .vision-mission h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .vision-mission p {
            color: #d1d5db;
        }

        /* Stats Section */
        .stats {
            background: var(--primary-color);
            color: #000;
            padding: 4rem 0;
            text-align: center;
        }

        .stats h2 {
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .stat-item {
            padding: 1rem;
        }

        .stat-item h3 {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .stat-item p {
            font-size: 1rem;
        }

        /* Team Section */
        .team-section {
            padding: 5rem 0;
            background: #fff;
            color: #000;
        }

        .team-section h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .team-card {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 2rem;
            transition: transform 0.3s ease;
        }

        .team-card:hover {
            transform: translateY(-10px);
        }

        .team-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }

        .team-card h5 {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .team-card span {
            color: var(--primary-color);
            font-size: 0.95rem;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .hero-about h1 {
                font-size: 2.2rem;
            }

            .hero-about p {
                font-size: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-about">
        <div class="container">
            <h1>About Us</h1>
            <p>Find the best shopping experience with us, explore our wide range of products and services for your maximum
                satisfaction.</p>
        </div>
    </section>

    <!-- About Company -->
    <section class="about-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 about-image">
                    <img src="{{ asset('/img/abd.jpg') }}" alt="edwgans"
                        class="img-fluid">
                </div>
                <div class="col-lg-6 about-section">
                    <h2 class="section-title">Who are We?</h2>
                    <p>
                        PT. Asian Bay Development was established on November 23, 2012. Since its inception, we have
                        been committed to becoming one of the leading retail pioneers in Bali.
                    </p>
                    <p>
                        With a focus on the retail industry, we have successfully developed two well-known supermarket
                        brands in Bali: SE Supermarket (Super Ekonomi Supermarket) and Uncle Jo Supermarket.
                    </p>
                      {{-- <p>
        <strong>PT. Mahendradata Jaya Mandiri</strong> was established on <strong>November 23, 2012</strong>. 
        Since its inception, we have been committed to becoming one of the leading retail pioneers in Bali.
    </p>
    <p>
        With a focus on the retail industry, we have successfully developed two well-known supermarket
        brands in Bali:
        <a href="https://superekonomi.co.id/" target="_blank" rel="noopener noreferrer" class="brand-link se-link">
            SE Supermarket (Super Ekonomi Supermarket)
        </a>
        and
        <a href="https://unclejo.co.id/" target="_blank" rel="noopener noreferrer" class="brand-link uj-link">
            Uncle Jo Supermarket
        </a>.
        Our supermarkets are strategically located across Bali, providing easy access to quality products and services 
        for our customers.
    </p> --}}
                </div>
            </div>
        </div>
    </section>

    <!-- Vision & Mission -->
    <section class="vision-mission">
        <div class="container">
            <div class="row text-center mb-5">
                <h2>Our Vision & Mission</h2>
            </div>
            <div class="row text-center">
                <div class="col-md-6 mb-4">
                    <h3><i class="fas fa-bullseye"></i> Vision</h3>
                    <p>Becoming more than just a retailer. We are committed to being a reliable partner for each of our customers, providing the best service, and creating an unforgettable shopping experience.</p>
                </div>
                <div class="col-md-6 mb-4">
                    <h3><i class="fas fa-handshake"></i> Mission</h3>
                    <p>To be the first choice in the supermarket industry in every location we serve.</p>
                    <p>Serving customers with dedication and friendliness.</p>
                    <p>Providing a pleasant and satisfying shopping experience for every customer.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="stats">
        <div class="container">
            <h2>Pencapaian Kami</h2>
            <div class="row">
                <div class="col-md-3 col-6 stat-item">
                    <h3>150+</h3>
                    <p>Cabang Toko</p>
                </div>
                <div class="col-md-3 col-6 stat-item">
                    <h3>500+</h3>
                    <p>Produk Unggulan</p>
                </div>
                <div class="col-md-3 col-6 stat-item">
                    <h3>300+</h3>
                    <p>Karyawan</p>
                </div>
                <div class="col-md-3 col-6 stat-item">
                    <h3>20+</h3>
                    <p>Tahun Pengalaman</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container text-center">
            <h2>Tim Kami</h2>
            <p class="mb-5 text-muted">Bersama, kami berinovasi untuk memberikan pengalaman retail terbaik.</p>
            <div class="row justify-content-center g-4">
                <div class="col-md-4 col-sm-6">
                    <div class="team-card">
                        <img src="/img/team1.jpg" alt="CEO">
                        <h5>Edw</h5>
                        <span>CEO & Founder</span>
                        <p>Memimpin dengan visi dan komitmen untuk menjadikan retail Indonesia lebih modern dan inklusif.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="team-card">
                        <img src="/img/team2.jpg" alt="Marketing Director">
                        <h5>Edw</h5>
                        <span>Marketing Director</span>
                        <p>Mengembangkan strategi pemasaran kreatif untuk memperluas brand awareness di seluruh Indonesia.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="team-card">
                        <img src="/img/team3.jpg" alt="Operation Manager">
                        <h5>Edw</h5>
                        <span>Operation Manager</span>
                        <p>Mengelola operasional toko agar selalu efisien dan berorientasi pada kepuasan pelanggan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
