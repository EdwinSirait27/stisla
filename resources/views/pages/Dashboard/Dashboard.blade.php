@extends('layouts.app')
@section('title', 'Employee Dashboard')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.min.css" />
    <style>
        .card-icon {
            font-size: 2rem;
            color: #fff;
        }
        .quick-action {
            text-align: center;
            border-radius: 10px;
            padding: 10px;
            transition: 0.3s;
        }
        .quick-action:hover {
            transform: translateY(-5px);
            background: #f8f9fa;
        }
        .quick-action i {
            font-size: 1.8rem;
            color: #6777ef;
        }
        .progress {
            height: 8px;
        }
          .quick-action-card {
        transition: all 0.25s ease-in-out;
        border-radius: 12px;
    }
    .quick-action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    .icon-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
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
            <!-- Header Profil -->
            <div class="row align-items-center mb-4">
                <div class="col-md-8 d-flex align-items-center">
                    <img alt="image" src="{{ asset('img/avatar/avatar-1.png') }}" class="rounded-circle mr-3" width="70">
                    <div>
                        <h4>{{ Auth::user()->employee->name ?? Auth::user()->username }}</h4>
                        <p class="mb-1 text-muted">
                            {{ Auth::user()->employee->position->name ?? 'Staff' }} - 
                            {{ Auth::user()->employee->department->name ?? '-' }}
                        </p>
                        <span class="badge badge-success">Active Employee</span>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <small>Bergabung sejak {{ Auth::user()->employee->join_date ?? '2022-01-01' }}</small><br>
                    <small>Lama kerja: 2 Tahun 3 Bulan</small>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
    @php
        $actions = [
            ['icon' => 'fa-plane', 'label' => 'Ajukan Cuti', 'url' => route('Store.create'), 'color' => '#4e73df'],
            ['icon' => 'fa-clock', 'label' => 'Lihat Absensi', 'url' => route('Store.create'), 'color' => '#1cc88a'],
            ['icon' => 'fa-file-invoice-dollar', 'label' => 'Slip Gaji', 'url' => route('Store.create'), 'color' => '#36b9cc'],
            ['icon' => 'fa-user-check', 'label' => 'Evaluasi', 'url' => route('Store.create'), 'color' => '#f6c23e'],
            ['icon' => 'fa-folder-open', 'label' => 'Dokumen', 'url' => route('Store.create'), 'color' => '#e74a3b'],
            ['icon' => 'fa-calendar-alt', 'label' => 'Kalender', 'url' => route('Store.create'), 'color' => '#858796'],
        ];
    @endphp

    @foreach ($actions as $action)
        <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-4">
            <a href="{{ $action['url'] }}" class="text-decoration-none">
                <div class="card text-center shadow-sm border-0 quick-action-card h-100">
                    <div class="card-body py-4">
                        <div class="icon-circle mb-2" style="background: {{ $action['color'] }}20;">
                            <i class="fas {{ $action['icon'] }}" style="color: {{ $action['color'] }}; font-size: 28px;"></i>
                        </div>
                        <div class="text-dark font-weight-bold">{{ $action['label'] }}</div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>



            <!-- Cards Statistik -->
            <div class="row">
                <!-- Kehadiran -->
                <div class="col-lg-3 col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary"><i class="fas fa-clock"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Kehadiran Bulan Ini</h4></div>
                            <div class="card-body">95%</div>
                        </div>
                    </div>
                </div>

                <!-- Sisa Cuti -->
                <div class="col-lg-3 col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success"><i class="fas fa-plane"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Sisa Cuti</h4></div>
                            <div class="card-body">8 Hari</div>
                        </div>
                    </div>
                </div>

                <!-- Total Lembur -->
                <div class="col-lg-3 col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning"><i class="fas fa-business-time"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Lembur</h4></div>
                            <div class="card-body">14 Jam</div>
                        </div>
                    </div>
                </div>

                <!-- Evaluasi Kinerja -->
                <div class="col-lg-3 col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-info"><i class="fas fa-chart-line"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Kinerja</h4></div>
                            <div class="card-body">87 / 100</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Statistik -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h4>Grafik Kehadiran Bulanan</h4></div>
                        <div class="card-body">
                            <canvas id="attendanceChart" height="130"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Pengumuman -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h4>Pengumuman</h4></div>
                        <div class="card-body">
                            <div class="alert alert-info mb-2"><i class="fas fa-bullhorn"></i> Meeting umum hari Senin</div>
                            <div class="alert alert-success mb-2"><i class="fas fa-gift"></i> Bonus akhir tahun segera cair!</div>
                            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Update data pribadi sebelum 25 Okt</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aktivitas & Dokumen -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h4>Aktivitas Terbaru</h4></div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Clock in pukul 08:10</li>
                                <li><i class="fas fa-paper-plane text-primary"></i> Mengajukan cuti 2 hari</li>
                                <li><i class="fas fa-file-pdf text-danger"></i> Download slip gaji September</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h4>Kelengkapan Dokumen</h4></div>
                        <div class="card-body">
                            <p>KTP: <span class="badge badge-success">Lengkap</span></p>
                            <p>NPWP: <span class="badge badge-success">Lengkap</span></p>
                            <p>Kontrak Kerja: <span class="badge badge-warning">Perlu Diperbarui</span></p>
                            <p>Ijazah: <span class="badge badge-success">Lengkap</span></p>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 85%;">85%</div>
                            </div>
                            <small>Kelengkapan dokumen pribadi</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        // Chart Kehadiran
        const ctx = document.getElementById('attendanceChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'],
                datasets: [{
                    label: 'Persentase Kehadiran',
                    data: [90, 92, 87, 95, 93, 96, 94, 97, 95, 98],
                    borderColor: '#6777ef',
                    fill: false,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
    </script>
@endpush