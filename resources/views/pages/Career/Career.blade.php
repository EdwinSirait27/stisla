@extends('layouts.career')
@section('title', 'Vacancy - Asian Bay Development Career')
@push('styles')
    <style>
        .hero-section {
            position: relative;
            background-image: url('/img/bg4 (1).jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 50vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-align: center;
            margin-bottom: 3rem;
            opacity: 0;
            animation: fadeInBg 2s ease-in-out forwards;
            overflow: hidden;
        }

        .hero-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            /* ubah 0.5 jadi 0.6/0.7 kalau mau lebih gelap */
            z-index: 1;
        }

        .hero-section>* {
            position: relative;
            z-index: 2;
        }

        @keyframes fadeInBg {
            from {
                opacity: 0;
                filter: blur(5px);
            }

            to {
                opacity: 1;
                filter: blur(0);
            }
        }

        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .search-box {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-top: 2rem;
        }


        .filter-section {
            background: #575757;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .job-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: all 0.3s;
            cursor: pointer;
        }

        .job-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
            border-color: #2563eb;
        }

        /* .company-logo {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #2563eb;
            font-weight: bold;
        } */
         .company-logo {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden; /* supaya gambar ikut rounded */
    background: #000000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.company-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* biar gambar proporsional */
}


        .job-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .badge-fulltime {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-remote {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-urgent {
            background: #fee2e2;
            color: #991b1b;
        }

        .job-salary {
            color: #2563eb;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .filter-chip {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-chip:hover,
        .filter-chip.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2563eb;
        }

        .pagination-custom {
            margin-top: 2rem;
        }

        .btn-apply {
            background: #2563eb;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-apply:hover {
            background: #1e40af;
            transform: translateY(-2px);
        }

        .btn-bookmark {
            background: transparent;
            border: 2px solid #e5e7eb;
            color: #6b7280;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-bookmark:hover {
            border-color: #2563eb;
            color: #2563eb;
        }
    </style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <img src="{{ asset('/img/abd.png') }}" alt="Logo MJM"
                        style="width:300px; margin-bottom:10px;">
                    {{-- <img src="{{ asset('/img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.png') }}" alt="Logo MJM"
                        style="width:300px; margin-bottom:20px;"> --}}

                    <h1>Welcome to Asian Bay Development</h1>
                    <h6>Discover Promising Career Opportunities with Us</h6>
                    {{-- <div class="search-box">
                        <form action="{{ url('/careers') }}" method="GET">
                            <div class="row g-3">
                                <div class="col-md-9">
                                    <input type="text" name="keyword" class="form-control form-control-lg"
                                        placeholder="Position">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary-custom w-100 btn-lg">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div> --}}
                </div>
            </div>
        </div>
    </section>
    {{-- <section class="container mb-5">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number">1,523</div>
                    <p class="text-muted mb-0">Active Vacancy</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number">4</div>
                    <p class="text-muted mb-0">Companies</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number">0</div>
                    <p class="text-muted mb-0">Active Employee</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number">7</div>
                    <p class="text-muted mb-0">Berhasil Diterima</p>
                </div>
            </div>
        </div>
    </section> --}}
    {{-- <section class="container mb-5">
        <div class="row">
            <div class="col-lg-3">
                <div class="filter-section">
                    <h5 class="mb-3">Jobs Filter</h5>
                    <div class="mb-4">
                        <h6>Jobs Type</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="fulltime">
                            <label class="form-check-label" for="fulltime">Full Time</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="parttime">
                            <label class="form-check-label" for="parttime">Part Time</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="contract">
                            <label class="form-check-label" for="contract">Contract</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="internship">
                            <label class="form-check-label" for="internship">Internship</label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <h6>Experience</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="entry">
                            <label class="form-check-label" for="entry">Entry Level</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="mid">
                            <label class="form-check-label" for="mid">Mid Level</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="senior">
                            <label class="form-check-label" for="senior">Senior Level</label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <h6>Salary (per bulan)</h6>
                        <select class="form-select">
                            <option>Semua Range</option>
                            <option>
                                < 5 Juta</option>
                            <option>5 - 10 Juta</option>
                            <option>10 - 15 Juta</option>
                            <option>> 15 Juta</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <h6>Positions Category</h6>
                        <select class="form-select">
                            <option>All Position</option>
                        </select>
                    </div>

                    <button class="btn btn-primary-custom w-100 mt-3">Filter</button>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Available Job</h4>
                    <select class="form-select w-auto">
                        <option>Newest</option>
                        <option>Latest</option>
                    </select>
                </div>
                <div class="job-card">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="company-logo">G</div>
                        </div>
                        <div class="col-md-7">
                            <h5 class="mb-2">Senior Full Stack Developer</h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-building"></i> tes
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt"></i> Jakarta, Indonesia
                                <span class="ms-3"><i class="far fa-clock"></i> 2 hari yang lalu</span>
                            </p>
                            <div class="mb-2">
                                <span class="job-badge badge-fulltime">Full Time</span>
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <button class="btn btn-apply mb-2 w-100">Apply Now</button>
                            </div>
                    </div>
                </div>

                <nav class="pagination-custom">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                        <li class="page-item"><a class="page-link" href="#">5</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </section> --}}
    <section class="container mb-5">
    <div class="row">
        <!-- Sidebar Filter -->
        <div class="col-lg-3">
            <div class="filter-section">
                <h5 class="mb-3">Search Filter</h5>
                
                <div class="mb-4">
                    <h6>Job Type</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="fulltime">
                        <label class="form-check-label" for="fulltime">Full Time</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="parttime">
                        <label class="form-check-label" for="parttime">Part Time</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="contract">
                        <label class="form-check-label" for="contract">Contract</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="internship">
                        <label class="form-check-label" for="internship">Internship</label>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6>Grade</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="entry">
                        <label class="form-check-label" for="entry">Staff</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="mid">
                        <label class="form-check-label" for="mid">Manager</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="senior">
                        <label class="form-check-label" for="senior">Senior Level</label>
                    </div>
                </div>
                
                {{-- <div class="mb-4">
                    <h6>Gaji (per bulan)</h6>
                    <select class="form-select">
                        <option>Semua Range</option>
                        <option>< 5 Juta</option>
                        <option>5 - 10 Juta</option>
                        <option>10 - 15 Juta</option>
                        <option>> 15 Juta</option>
                    </select>
                </div> --}}
                
                <div class="mb-3">
                    <h6>Categories</h6>
                    <select class="form-select">
                        <option>All Categories</option>
                        <option>IT & Software</option>
                        <option>Marketing</option>
                        <option>Finance</option>
                        <option>Design</option>
                        <option>Sales</option>
                    </select>
                </div>
                
                <button class="btn btn-primary-custom w-100 mt-3">Filter</button>
            </div>
        </div>
        
        <!-- Job Listings -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                {{-- <h4 style="color">Menampilkan 24 Lowongan</h4> --}}
                <h4 style="color: black;">Showing Vacancies</h4>

                <select class="form-select w-auto">
                    <option>Newest</option>
                    <option>Latest</option>
                </select>
            </div>
            
            <!-- Job Card 1 -->
            <div class="job-card">
                <div class="row">
                    <div class="col-md-2">
                        {{-- <div class="company-logo">G</div> --}}
                        <div class="company-logo">
    <img src="{{ asset('img/abd.jpg') }}" alt="Logo Perusahaan">
</div>

                    </div>
                    <div class="col-md-7">
                        <h5 style="color: black;" class="mb-2">IT</h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-building"></i> Google Indonesia
                        </p>
                        <p class="text-muted mb-2">
                            <i class="fas fa-map-marker-alt"></i> Jakarta, Indonesia
                            <span class="ms-3"><i class="far fa-clock"></i> 2 hari yang lalu</span>
                        </p>
                        <div class="mb-2">
                            <span class="job-badge badge-fulltime">Full Time</span>
                            <span class="job-badge badge-remote">Remote</span>
                            <span class="job-badge badge-urgent">Urgent</span>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="job-salary mb-2">BIG SALARY</div>
                        <button class="btn btn-apply mb-2 w-100">Apply Now</button>
                        {{-- <button class="btn btn-bookmark w-100"><i class="far fa-bookmark"></i> Simpan</button> --}}
                    </div>
                </div>
            </div>
            
            
            
            <!-- Pagination -->
            <nav class="pagination-custom">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">4</a></li>
                    <li class="page-item"><a class="page-link" href="#">5</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</section>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterChips = document.querySelectorAll('.filter-chip');
            filterChips.forEach(chip => {
                chip.addEventListener('click', function() {
                    this.classList.toggle('active');
                });
            });
            const bookmarkButtons = document.querySelectorAll('.btn-bookmark');
            bookmarkButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const icon = this.querySelector('i');
                    icon.classList.toggle('far');
                    icon.classList.toggle('fas');
                    if (icon.classList.contains('fas')) {
                        this.innerHTML = '<i class="fas fa-bookmark"></i> Tersimpan';
                    } else {
                        this.innerHTML = '<i class="far fa-bookmark"></i> Simpan';
                    }
                });
            });
        });
    </script>
@endpush
