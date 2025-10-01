@extends('layouts.app')
@section('title', 'Dashboard Karyawan')

@push('style')
    <!-- CSS Libraries -->
    <style>
        .card-icon {
            font-size: 2.5rem;
            color: #4e73df;
        }
    </style>
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Dashboard Karyawan</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <!-- Cuti -->
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Sisa Cuti</h4>
                            </div>
                            <div class="card-body">
                                {{ $sisaCuti ?? 0 }} Hari
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slip Gaji -->
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Slip Gaji Terbaru</h4>
                            </div>
                            <div class="card-body">
                                {{ $slipGajiBulan ?? 'Belum Ada' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kontrak -->
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Masa Kontrak</h4>
                            </div>
                            <div class="card-body">
                                {{ $kontrakSelesai ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evaluasi -->
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Evaluasi Terakhir</h4>
                            </div>
                            <div class="card-body">
                                {{ $evaluasi ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Tambahan -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Informasi Perusahaan</h4>
                        </div>
                        <div class="card-body">
                            <p>Selamat datang, <strong>{{ Auth::user()->name }}</strong> di sistem HRIS.  
                            Silakan gunakan menu di sidebar untuk mengakses cuti, slip gaji, kontrak kerja, dan evaluasi kinerja Anda.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection

@push('scripts')
    <!-- JS Libraies -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
@endpush
