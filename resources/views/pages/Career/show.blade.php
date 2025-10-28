@extends('layouts.career')

{{-- @section('title', $job->title . ' - CareerHub') --}}

@push('styles')
<style>
    .job-detail-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 0;
        color: white;
        margin-bottom: 3rem;
    }
    .company-logo-large {
        width: 100px;
        height: 100px;
        border-radius: 12px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: #2563eb;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .job-info-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-icon {
        width: 40px;
        height: 40px;
        background: #eff6ff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2563eb;
        margin-right: 1rem;
    }
    
    .apply-card {
        position: sticky;
        top: 20px;
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .btn-apply-large {
        background: #2563eb;
        color: white;
        padding: 1rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1.1rem;
        width: 100%;
        transition: all 0.3s;
    }
    
    .btn-apply-large:hover {
        background: #1e40af;
        transform: translateY(-2px);
    }
    
    .share-buttons a {
        display: inline-block;
        width: 40px;
        height: 40px;
        background: #f3f4f6;
        border-radius: 50%;
        text-align: center;
        line-height: 40px;
        margin-right: 0.5rem;
        color: #6b7280;
        transition: all 0.3s;
    }
    
    .share-buttons a:hover {
        background: #2563eb;
        color: white;
    }
    
    .requirement-list li {
        padding: 0.5rem 0;
        position: relative;
        padding-left: 1.5rem;
    }
    
    .requirement-list li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #10b981;
        font-weight: bold;
    }
    
    .similar-job-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        border: 1px solid #e5e7eb;
        transition: all 0.3s;
    }
    
    .similar-job-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transform: translateY(-3px);
    }
    
    .modal-content {
        border-radius: 12px;
    }
    
    .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<!-- Header Section -->
<section class="job-detail-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-2">
                <div class="company-logo-large">
                    {{ substr($job->company->name, 0, 1) }}
                </div>
            </div>
            <div class="col-md-7">
                <h1 class="mb-2">{{ $job->title }}</h1>
                <p class="lead mb-3">
                    <i class="fas fa-building"></i> {{ $job->company->name }}
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="fas fa-briefcase"></i> {{ ucfirst($job->job_type) }}
                    </span>
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="fas fa-map-marker-alt"></i> {{ $job->location }}
                    </span>
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="fas fa-layer-group"></i> {{ ucfirst($job->experience_level) }}
                    </span>
                </div>
            </div>
            <div class="col-md-3 text-end">
                <div class="text-white">
                    <small>Gaji</small>
                    <h3 class="mb-0">Rp {{ number_format($job->salary_min / 1000000, 0) }}-{{ number_format($job->salary_max / 1000000, 0) }} Jt</h3>
                    <small>per bulan</small>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container mb-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Job Overview -->
            <div class="job-info-card">
                <h4 class="mb-4">Informasi Pekerjaan</h4>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div>
                        <small class="text-muted">Tanggal Posting</small>
                        <div class="fw-bold">{{ $job->created_at->format('d M Y') }}</div>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-hourglass-end"></i>
                    </div>
                    <div>
                        <small class="text-muted">Deadline</small>
                        <div class="fw-bold">{{ \Carbon\Carbon::parse($job->deadline)->format('d M Y') }}</div>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <small class="text-muted">Jumlah Pelamar</small>
                        <div class="fw-bold">{{ $job->applications->count() }} Pelamar</div>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <small class="text-muted">Total Views</small>
                        <div class="fw-bold">{{ number_format($job->views) }} Views</div>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div>
                        <small class="text-muted">Kategori</small>
                        <div class="fw-bold">{{ $job->category->name }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Job Description -->
            <div class="job-info-card">
                <h4 class="mb-3">Deskripsi Pekerjaan</h4>
                <div class="text-muted">
                    {!! nl2br(e($job->description)) !!}
                </div>
            </div>
            
            <!-- Requirements -->
            <div class="job-info-card">
                <h4 class="mb-3">Kualifikasi & Persyaratan</h4>
                <ul class="requirement-list list-unstyled">
                    <li>Pendidikan minimal S1 di bidang terkait</li>
                    <li>Pengalaman minimal {{ $job->min_experience }} tahun di posisi yang sama</li>
                    <li>Menguasai tools dan teknologi yang relevan</li>
                    <li>Kemampuan komunikasi yang baik</li>
                    <li>Dapat bekerja dalam tim maupun individu</li>
                    <li>Bersedia bekerja dengan target dan deadline</li>
                </ul>
            </div>
            
            <!-- Benefits -->
            <div class="job-info-card">
                <h4 class="mb-3">Benefit & Fasilitas</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Asuransi Kesehatan</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>BPJS Ketenagakerjaan</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Bonus Performa</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Cuti Tahunan</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Training & Development</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Work-Life Balance</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Company Info -->
            <div class="job-info-card">
                <h4 class="mb-3">Tentang Perusahaan</h4>
                <div class="d-flex align-items-start mb-3">
                    <div class="company-logo-large me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        {{ substr($job->company->name, 0, 1) }}
                    </div>
                    <div>
                        <h5>{{ $job->company->name }}</h5>
                        <p class="text-muted mb-0">{{ $job->company->industry }}</p>
                    </div>
                </div>
                <p class="text-muted">{{ $job->company->description }}</p>
                <a href="#" class="btn btn-outline-primary">Lihat Profil Perusahaan</a>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Apply Card -->
            <div class="apply-card">
                <h5 class="mb-3">Tertarik dengan posisi ini?</h5>
                <button class="btn btn-apply-large mb-3" data-bs-toggle="modal" data-bs-target="#applyModal">
                    <i class="fas fa-paper-plane"></i> Lamar Sekarang
                </button>
                <button class="btn btn-outline-secondary w-100 mb-3">
                    <i class="far fa-bookmark"></i> Simpan Lowongan
                </button>
                
                <hr>
                
                <h6 class="mb-2">Bagikan Lowongan</h6>
                <div class="share-buttons">
                    <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Email"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
            
            <!-- Similar Jobs -->
            <div class="mt-4">
                <h5 class="mb-3">Lowongan Serupa</h5>
                
                @foreach($similarJobs as $similar)
                <div class="similar-job-card">
                    <h6 class="mb-2">{{ $similar->title }}</h6>
                    <p class="text-muted mb-2 small">
                        <i class="fas fa-building"></i> {{ $similar->company->name }}
                    </p>
                    <p class="text-muted mb-2 small">
                        <i class="fas fa-map-marker-alt"></i> {{ $similar->location }}
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-primary fw-bold small">
                            Rp {{ number_format($similar->salary_min / 1000000, 0) }}-{{ number_format($similar->salary_max / 1000000, 0) }} Jt
                        </span>
                        <a href="{{ route('careers.show', $similar->id) }}" class="btn btn-sm btn-outline-primary">
                            Lihat
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Application Modal -->
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lamar: {{ $job->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('careers.apply', $job->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon *</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Upload CV/Resume * (PDF, DOC, DOCX - Max 5MB)</label>
                        <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cover Letter *</label>
                        <textarea name="cover_letter" class="form-control" rows="5" 
                                  placeholder="Ceritakan tentang diri Anda dan mengapa Anda cocok untuk posisi ini..." 
                                  required></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                        <label class="form-check-label" for="agreeTerms">
                            Saya setuju dengan <a href="#">syarat dan ketentuan</a> yang berlaku
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Kirim Lamaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Handle bookmark button
    document.querySelector('.apply-card .btn-outline-secondary').addEventListener('click', function() {
        const icon = this.querySelector('i');
        icon.classList.toggle('far');
        icon.classList.toggle('fas');
        
        if (icon.classList.contains('fas')) {
            this.innerHTML = '<i class="fas fa-bookmark"></i> Tersimpan';
        } else {
            this.innerHTML = '<i class="far fa-bookmark"></i> Simpan Lowongan';
        }
    });
</script>
@endpush