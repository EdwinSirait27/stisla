{{-- @extends('layouts.app')
@section('title', 'Employee Dashboard')

@push('style')
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



            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary"><i class="fas fa-clock"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Kehadiran Bulan Ini</h4></div>
                            <div class="card-body">95%</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success"><i class="fas fa-plane"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Sisa Cuti</h4></div>
                            <div class="card-body">8 Hari</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning"><i class="fas fa-business-time"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Lembur</h4></div>
                            <div class="card-body">14 Jam</div>
                        </div>
                    </div>
                </div>

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

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h4>Grafik Kehadiran Bulanan</h4></div>
                        <div class="card-body">
                            <canvas id="attendanceChart" height="130"></canvas>
                        </div>
                    </div>
                </div>

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
@endpush --}}
@extends('layouts.app')
@section('title', 'Employee Dashboard')

@push('styles')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --orange-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            --card-hover-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        /* ========== Personal Profile Card ========== */
        .profile-header-card {
            background: var(--primary-gradient);
            border-radius: 20px;
            padding: 40px;
            color: white;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
        }

        .profile-header-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .profile-header-card::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .profile-content {
            position: relative;
            z-index: 1;
        }

        .profile-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .profile-info h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .profile-meta {
            display: flex;
            gap: 24px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            opacity: 0.95;
        }

        .profile-meta-item i {
            font-size: 1.1rem;
        }

        /* ========== Attendance Clock Card ========== */
        .clock-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: var(--card-shadow);
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .clock-display {
            font-size: 3.5rem;
            font-weight: 700;
            color: #344767;
            margin: 24px 0;
            font-family: 'Courier New', monospace;
        }

        .clock-date {
            font-size: 1.1rem;
            color: #64748b;
            margin-bottom: 24px;
        }

        .clock-in-btn {
            background: var(--success-gradient);
            border: none;
            color: white;
            padding: 16px 48px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
        }

        .clock-in-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
            color: white;
        }

        .clock-out-btn {
            background: var(--warning-gradient);
            border: none;
            color: white;
            padding: 16px 48px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
        }

        .clock-out-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(245, 87, 108, 0.4);
            color: white;
        }

        .clock-status {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .clock-status strong {
            color: #344767;
        }

        /* ========== Quick Stats Mini Cards ========== */
        .mini-stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
            height: 100%;
        }

        .mini-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-hover-shadow);
        }

        .mini-stat-card.primary {
            border-left-color: #667eea;
        }

        .mini-stat-card.success {
            border-left-color: #11998e;
        }

        .mini-stat-card.warning {
            border-left-color: #f59e0b;
        }

        .mini-stat-card.danger {
            border-left-color: #f5576c;
        }

        .mini-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 12px;
        }

        .mini-stat-icon.primary {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .mini-stat-icon.success {
            background: rgba(17, 153, 142, 0.1);
            color: #11998e;
        }

        .mini-stat-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .mini-stat-icon.danger {
            background: rgba(245, 87, 108, 0.1);
            color: #f5576c;
        }

        .mini-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #344767;
            margin: 8px 0;
        }

        .mini-stat-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }

        /* ========== Leave Balance Card ========== */
        .leave-balance-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .leave-balance-header {
            background: var(--info-gradient);
            color: white;
            padding: 24px;
        }

        .leave-balance-header h4 {
            margin: 0;
            font-weight: 600;
            color: white;
        }

        .leave-balance-body {
            padding: 24px;
        }

        .leave-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .leave-item:last-child {
            border-bottom: none;
        }

        .leave-type {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .leave-type-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .leave-type-icon.annual {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .leave-type-icon.sick {
            background: rgba(245, 87, 108, 0.1);
            color: #f5576c;
        }

        .leave-type-icon.casual {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .leave-type-name {
            font-weight: 600;
            color: #344767;
            margin-bottom: 4px;
        }

        .leave-type-period {
            font-size: 0.8rem;
            color: #64748b;
        }

        .leave-days {
            text-align: right;
        }

        .leave-days-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #344767;
        }

        .leave-days-label {
            font-size: 0.75rem;
            color: #64748b;
        }

        .leave-progress {
            margin-top: 8px;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
            background: #f1f3f5;
        }

        .progress-bar {
            border-radius: 10px;
        }

        /* ========== My Submissions Card ========== */
        .submissions-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .submissions-header {
            background: white;
            border-bottom: 2px solid #f1f3f5;
            padding: 20px 24px;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .submissions-header h4 {
            margin: 0;
            font-weight: 600;
            color: #344767;
        }

        .submission-item {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f3f5;
            transition: background-color 0.2s;
        }

        .submission-item:hover {
            background-color: #f8f9fa;
        }

        .submission-item:last-child {
            border-bottom: none;
        }

        .submission-header-row {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .submission-type-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .submission-type-badge.annual-leave {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .submission-type-badge.sick-leave {
            background: rgba(245, 87, 108, 0.1);
            color: #f5576c;
        }

        .submission-type-badge.overtime {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .submission-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .submission-status.pending {
            background: rgba(255, 171, 0, 0.15);
            color: #f59e0b;
        }

        .submission-status.approved {
            background: rgba(56, 239, 125, 0.15);
            color: #11998e;
        }

        .submission-status.rejected {
            background: rgba(245, 87, 108, 0.15);
            color: #f5576c;
        }

        .submission-meta {
            display: flex;
            gap: 20px;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 8px;
        }

        .submission-meta i {
            width: 14px;
        }

        .submission-notes {
            font-size: 0.9rem;
            color: #344767;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #e9ecef;
        }

        /* ========== Announcements Card ========== */
        .announcements-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .announcements-header {
            background: var(--orange-gradient);
            color: white;
            padding: 20px 24px;
        }

        .announcements-header h4 {
            margin: 0;
            font-weight: 600;
            color: white;
        }

        .announcement-item {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f3f5;
            transition: all 0.2s;
            cursor: pointer;
        }

        .announcement-item:hover {
            background-color: #f8f9fa;
        }

        .announcement-item:last-child {
            border-bottom: none;
        }

        .announcement-title {
            font-weight: 600;
            color: #344767;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .announcement-badge-new {
            background: #f5576c;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .announcement-excerpt {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .announcement-date {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        /* ========== Attendance History Card ========== */
        .attendance-history-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .attendance-history-header {
            background: white;
            border-bottom: 2px solid #f1f3f5;
            padding: 20px 24px;
        }

        .attendance-history-header h4 {
            margin: 0;
            font-weight: 600;
            color: #344767;
        }

        .attendance-calendar {
            padding: 24px;
        }

        .calendar-month {
            text-align: center;
            font-weight: 600;
            color: #344767;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }

        .calendar-day-header {
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            padding: 8px;
        }

        .calendar-day {
            aspect-ratio: 1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
        }

        .calendar-day.empty {
            background: transparent;
            cursor: default;
        }

        .calendar-day.present {
            background: rgba(56, 239, 125, 0.15);
            color: #11998e;
        }

        .calendar-day.absent {
            background: rgba(245, 87, 108, 0.15);
            color: #f5576c;
        }

        .calendar-day.leave {
            background: rgba(255, 171, 0, 0.15);
            color: #f59e0b;
        }

        .calendar-day.weekend {
            background: #f8f9fa;
            color: #94a3b8;
        }

        .calendar-day.today {
            border: 2px solid #667eea;
            font-weight: 700;
        }

        .calendar-day:hover:not(.empty) {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .calendar-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            color: #64748b;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }

        /* ========== Empty State ========== */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .empty-state h6 {
            color: #64748b;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #94a3b8;
            font-size: 0.875rem;
            margin: 0;
        }

        /* ========== Modal Improvements ========== */
        .modal-content {
            border-radius: 16px;
            border: none;
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
        }

        .modal-header .modal-title {
            font-weight: 600;
            color: white;
        }

        .modal-header .close {
            color: white;
            opacity: 0.9;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .form-label {
            font-weight: 600;
            color: #344767;
            margin-bottom: 8px;
            font-size: 0.875rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 10px 14px;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* ========== Buttons ========== */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* ========== Responsive Design ========== */
        @media (max-width: 768px) {
            .profile-header-card {
                padding: 24px;
            }

            .profile-info h2 {
                font-size: 1.5rem;
            }

            .profile-meta {
                flex-direction: column;
                gap: 12px;
            }

            .clock-display {
                font-size: 2.5rem;
            }

            .clock-in-btn,
            .clock-out-btn {
                padding: 12px 32px;
                font-size: 1rem;
            }

            .calendar-grid {
                gap: 4px;
            }

            .calendar-day {
                font-size: 0.75rem;
            }

            .mini-stat-card {
                margin-bottom: 16px;
            }
        }

        /* ========== Animations ========== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <!-- Profile Header -->
            <div class="profile-header-card animate-fade-in-up">
                <div class="profile-content">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-4">
                                <img src="{{ asset('img/avatar/avatar-1.png') }}" alt="Profile"
                                    class="profile-avatar-large">
                                <div class="profile-info">
                                    <h2>{{ Auth::user()->employee->employee_name ?? 'Edwin Sirait' }}</h2>
                                    <div class="profile-meta">
                                        <div class="profile-meta-item">
                                            <i class="fas fa-briefcase"></i>
                                            <span>{{ Auth::user()->employee->position->name ?? 'Edwin Sirait' }} </span>
                                            {{-- <span>{{ $employee->position ?? 'Software Engineer' }}</span> --}}
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-building"></i>
                                            <span>{{ Auth::user()->employee->department->department_name ?? 'Edwin Sirait' }}</span>
                                            {{-- <span>{{ $employee->department ?? 'Engineering' }}</span> --}}
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-id-badge"></i>
                                            <span>{{ Auth::user()->employee->employee_pengenal ?? 'Edwin Sirait' }}</span>
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>{{ now()->format('l, F d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-lg-3 text-lg-end mt-3 mt-lg-0">
                            <button  class="btn btn-light btn-lg" id="requestLeaveBtn">
                                <i class="fas fa-paper-plane me-2"></i>
                                Request Leave
                            </button>
                        </div> --}}
                        <div class="col-lg-3 mt-3 mt-lg-0 d-flex justify-content-end">
                            <button class="btn btn-light btn-lg" id="requestLeaveBtn">
                                <i class="fas fa-paper-plane me-2"></i>
                                Request Leave
                            </button>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card primary">
                        <div class="mini-stat-icon primary">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="mini-stat-value">22</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->present ?? 22 }}</div> --}}
                        <div class="mini-stat-label">Days Present</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card success">
                        <div class="mini-stat-icon success">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="mini-stat-value">95%</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->rate ?? 95 }}%</div> --}}
                        <div class="mini-stat-label">Attendance Rate</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card warning">
                        <div class="mini-stat-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="mini-stat-value">2</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->late ?? 2 }}</div> --}}
                        <div class="mini-stat-label">Times Late</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card danger">
                        <div class="mini-stat-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="mini-stat-value">1</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->absent ?? 1 }}</div> --}}
                        <div class="mini-stat-label">Days Absent</div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="row">
                <!-- Clock In/Out Section -->
                <div class="col-lg-4 col-12 mb-4">
                    <div class="clock-card">
                        <h4 class="mb-3">
                            <i class="fas fa-clock me-2"></i>
                            Attendance Clock
                        </h4>
                        <div class="clock-display" id="currentTime">00:00:00</div>
                        <div class="clock-date" id="currentDate">Monday, January 01, 2024</div>

                        @if (true)
                            {{-- @if (!$hasCheckedIn ?? true) --}}
                            <button class="btn clock-in-btn pulse-animation" id="clockInBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Clock In
                            </button>
                        @else
                            <button class="btn clock-out-btn" id="clockOutBtn">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Clock Out
                            </button>
                            <div class="clock-status">
                                <strong>Clocked in at:</strong>08:00 AM
                                {{-- <strong>Clocked in at:</strong> {{ $clockInTime ?? '08:00 AM' }} --}}
                            </div>
                        @endif
                    </div>

                    <!-- Leave Balance -->
                    <div class="leave-balance-card mt-4">
                        <div class="leave-balance-header">
                            <h4>
                                <i class="fas fa-umbrella-beach me-2"></i>
                                Leave Balance
                            </h4>
                        </div>
                        <div class="leave-balance-body">
                            <div class="leave-item">
                                <div class="leave-type">
                                    <div class="leave-type-icon annual">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div>
                                        <div class="leave-type-name">Annual Leave</div>
                                        <div class="leave-type-period">2024</div>
                                    </div>
                                </div>
                                <div class="leave-days">
                                    <div class="leave-days-value">12</div>
                                    {{-- <div class="leave-days-value">{{ $leaveBalance->annual->remaining ?? 12 }}</div> --}}
                                    <div class="leave-days-label">of 14 days</div>
                                    {{-- <div class="leave-days-label">of {{ $leaveBalance->annual->total ?? 14 }} days</div> --}}
                                </div>
                            </div>
                            <div class="leave-progress">
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 85%"
                                        {{-- style="width: {{ $leaveBalance->annual->percentage ?? 85 }}%" --}} aria-valuenow= "85" {{-- aria-valuenow="{{ $leaveBalance->annual->percentage ?? 85 }}"  --}} aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="leave-item mt-3">
                                <div class="leave-type">
                                    <div class="leave-type-icon sick">
                                        <i class="fas fa-hospital"></i>
                                    </div>
                                    <div>
                                        <div class="leave-type-name">Sick Leave</div>
                                        <div class="leave-type-period">2024</div>
                                    </div>
                                </div>
                                <div class="leave-days">
                                    {{-- <div class="leave-days-value">{{ $leaveBalance->sick->remaining ?? 5 }}</div> --}}
                                    <div class="leave-days-value">5</div>
                                    <div class="leave-days-label">of 7 days</div>
                                    {{-- <div class="leave-days-label">of {{ $leaveBalance->sick->total ?? 7 }} days</div> --}}
                                </div>
                            </div>
                            <div class="leave-progress">
                                <div class="progress">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 71%"
                                        {{-- style="width: {{ $leaveBalance->sick->percentage ?? 71 }}%" --}} aria-valuenow="71" {{-- aria-valuenow="{{ $leaveBalance->sick->percentage ?? 71 }}"  --}} aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="leave-item mt-3">
                                <div class="leave-type">
                                    <div class="leave-type-icon casual">
                                        <i class="fas fa-coffee"></i>
                                    </div>
                                    <div>
                                        <div class="leave-type-name">Casual Leave</div>
                                        <div class="leave-type-period">2024</div>
                                    </div>
                                </div>
                                <div class="leave-days">
                                    <div class="leave-days-value">3</div>
                                    {{-- <div class="leave-days-value">{{ $leaveBalance->casual->remaining ?? 3 }}</div> --}}
                                    <div class="leave-days-label">of 5 days</div>
                                    {{-- <div class="leave-days-label">of {{ $leaveBalance->casual->total ?? 5 }} days</div> --}}
                                </div>
                            </div>
                            <div class="leave-progress">
                                <div class="progress">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 60%"
                                        {{-- style="width: {{ $leaveBalance->casual->percentage ?? 60 }}%" --}} aria-valuenow="60" {{-- aria-valuenow="{{ $leaveBalance->casual->percentage ?? 60 }}"  --}} aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Submissions -->
                <div class="col-lg-8 col-12 mb-4">
                    <div class="submissions-card">
                        <div class="submissions-header">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4>
                                    <i class="fas fa-file-alt me-2"></i>
                                    My Submissions
                                </h4>
                                <button class="btn btn-primary btn-sm" id="newSubmissionBtn">
                                    <i class="fas fa-plus me-1"></i>
                                    New Request
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <!-- Submission Item 1 -->
                            <div class="submission-item">
                                <div class="submission-header-row">
                                    <span class="submission-type-badge annual-leave">
                                        <i class="fas fa-umbrella-beach me-1"></i>
                                        Annual Leave
                                    </span>
                                    <span class="submission-status pending">
                                        <i class="fas fa-clock me-1"></i>
                                        Pending
                                    </span>
                                </div>
                                <div class="submission-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        Dec 20, 2024 - Dec 24, 2024
                                    </span>
                                    <span>
                                        <i class="fas fa-hourglass-half"></i>
                                        5 days
                                    </span>
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        2 days ago
                                    </span>
                                </div>
                                <div class="submission-notes">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    <strong>Note:</strong> Family vacation to Bali
                                </div>
                            </div>

                            <!-- Submission Item 2 -->
                            <div class="submission-item">
                                <div class="submission-header-row">
                                    <span class="submission-type-badge overtime">
                                        <i class="fas fa-clock me-1"></i>
                                        Overtime
                                    </span>
                                    <span class="submission-status approved">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Approved
                                    </span>
                                </div>
                                <div class="submission-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        Nov 28, 2024
                                    </span>
                                    <span>
                                        <i class="fas fa-hourglass-half"></i>
                                        4 hours
                                    </span>
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        5 days ago
                                    </span>
                                </div>
                                <div class="submission-notes">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    <strong>Note:</strong> Project deadline completion
                                </div>
                            </div>

                            <!-- Submission Item 3 -->
                            <div class="submission-item">
                                <div class="submission-header-row">
                                    <span class="submission-type-badge sick-leave">
                                        <i class="fas fa-hospital me-1"></i>
                                        Sick Leave
                                    </span>
                                    <span class="submission-status approved">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Approved
                                    </span>
                                </div>
                                <div class="submission-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        Nov 15, 2024 - Nov 16, 2024
                                    </span>
                                    <span>
                                        <i class="fas fa-hourglass-half"></i>
                                        2 days
                                    </span>
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        2 weeks ago
                                    </span>
                                </div>
                                <div class="submission-notes">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    <strong>Note:</strong> Medical checkup and recovery
                                </div>
                            </div>

                            <!-- Submission Item 4 -->
                            <div class="submission-item">
                                <div class="submission-header-row">
                                    <span class="submission-type-badge annual-leave">
                                        <i class="fas fa-umbrella-beach me-1"></i>
                                        Annual Leave
                                    </span>
                                    <span class="submission-status rejected">
                                        <i class="fas fa-times-circle me-1"></i>
                                        Rejected
                                    </span>
                                </div>
                                <div class="submission-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        Nov 10, 2024 - Nov 12, 2024
                                    </span>
                                    <span>
                                        <i class="fas fa-hourglass-half"></i>
                                        3 days
                                    </span>
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        3 weeks ago
                                    </span>
                                </div>
                                <div class="submission-notes">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    <strong>Rejection reason:</strong> Peak season - insufficient coverage
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-center">
                            <a href="#" class="text-decoration-none">
                                View All Submissions
                                <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Announcements & Attendance History Row -->
            <div class="row">
                <!-- Company Announcements -->
                <div class="col-lg-6 col-12 mb-4">
                    <div class="announcements-card">
                        <div class="announcements-header">
                            <h4>
                                <i class="fas fa-bullhorn me-2"></i>
                                Company Announcements
                            </h4>
                        </div>
                        <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                            <!-- Announcement 1 -->
                            <div class="announcement-item" data-toggle="modal" data-target="#announcementModal">
                                <div class="announcement-title">
                                    <i class="fas fa-star text-warning"></i>
                                    Holiday Schedule for December
                                    <span class="announcement-badge-new">New</span>
                                </div>
                                <div class="announcement-excerpt">
                                    Dear Team, Please note the following holiday schedule for December 2024. The office will
                                    be closed from December 24-26 and December 31 - January 1...
                                </div>
                                <div class="announcement-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Posted 1 day ago
                                </div>
                            </div>

                            <!-- Announcement 2 -->
                            <div class="announcement-item">
                                <div class="announcement-title">
                                    <i class="fas fa-gift text-danger"></i>
                                    Year-End Bonus Announcement
                                    <span class="announcement-badge-new">New</span>
                                </div>
                                <div class="announcement-excerpt">
                                    We're pleased to announce that year-end bonuses will be distributed on December 15,
                                    2024. The amount will be based on individual performance...
                                </div>
                                <div class="announcement-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Posted 2 days ago
                                </div>
                            </div>

                            <!-- Announcement 3 -->
                            <div class="announcement-item">
                                <div class="announcement-title">
                                    <i class="fas fa-laptop-code text-primary"></i>
                                    New HR System Implementation
                                </div>
                                <div class="announcement-excerpt">
                                    Starting January 2025, we will be implementing a new HR management system. All employees
                                    are required to attend training sessions...
                                </div>
                                <div class="announcement-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Posted 5 days ago
                                </div>
                            </div>

                            <!-- Announcement 4 -->
                            <div class="announcement-item">
                                <div class="announcement-title">
                                    <i class="fas fa-heartbeat text-success"></i>
                                    Health Insurance Update
                                </div>
                                <div class="announcement-excerpt">
                                    Our company health insurance coverage has been upgraded to include dental and vision
                                    care for all employees and their families...
                                </div>
                                <div class="announcement-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Posted 1 week ago
                                </div>
                            </div>

                            <!-- Announcement 5 -->
                            <div class="announcement-item">
                                <div class="announcement-title">
                                    <i class="fas fa-users text-info"></i>
                                    Team Building Event - December
                                </div>
                                <div class="announcement-excerpt">
                                    Join us for our annual team building event on December 18, 2024 at Nusa Dua Beach
                                    Resort. Activities include team games, BBQ dinner...
                                </div>
                                <div class="announcement-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Posted 1 week ago
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-center">
                            <a href="#" class="text-decoration-none">
                                View All Announcements
                                <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Attendance History Calendar -->
                <div class="col-lg-6 col-12 mb-4">
                    <div class="attendance-history-card">
                        <div class="attendance-history-header">
                            <h4>
                                <i class="fas fa-calendar-check me-2"></i>
                                Attendance History
                            </h4>
                        </div>
                        <div class="attendance-calendar">
                            <div class="calendar-month">
                                <i class="fas fa-chevron-left" style="cursor: pointer;"></i>
                                <span class="mx-4">December 2024</span>
                                <i class="fas fa-chevron-right" style="cursor: pointer;"></i>
                            </div>

                            <div class="calendar-grid">
                                <!-- Day Headers -->
                                <div class="calendar-day-header">Sun</div>
                                <div class="calendar-day-header">Mon</div>
                                <div class="calendar-day-header">Tue</div>
                                <div class="calendar-day-header">Wed</div>
                                <div class="calendar-day-header">Thu</div>
                                <div class="calendar-day-header">Fri</div>
                                <div class="calendar-day-header">Sat</div>

                                <!-- Week 1 -->
                                <div class="calendar-day weekend">1</div>
                                <div class="calendar-day present">2</div>
                                <div class="calendar-day present">3</div>
                                <div class="calendar-day present">4</div>
                                <div class="calendar-day present">5</div>
                                <div class="calendar-day present">6</div>
                                <div class="calendar-day weekend">7</div>

                                <!-- Week 2 -->
                                <div class="calendar-day weekend">8</div>
                                <div class="calendar-day present">9</div>
                                <div class="calendar-day present">10</div>
                                <div class="calendar-day present">11</div>
                                <div class="calendar-day present">12</div>
                                <div class="calendar-day present">13</div>
                                <div class="calendar-day weekend">14</div>

                                <!-- Week 3 -->
                                <div class="calendar-day weekend">15</div>
                                <div class="calendar-day absent">16</div>
                                <div class="calendar-day present">17</div>
                                <div class="calendar-day present">18</div>
                                <div class="calendar-day present">19</div>
                                <div class="calendar-day leave">20</div>
                                <div class="calendar-day weekend">21</div>

                                <!-- Week 4 -->
                                <div class="calendar-day weekend">22</div>
                                <div class="calendar-day leave">23</div>
                                <div class="calendar-day leave">24</div>
                                <div class="calendar-day leave">25</div>
                                <div class="calendar-day leave">26</div>
                                <div class="calendar-day present">27</div>
                                <div class="calendar-day weekend">28</div>

                                <!-- Week 5 -->
                                <div class="calendar-day weekend">29</div>
                                <div class="calendar-day present">30</div>
                                <div class="calendar-day today">31</div>
                                <div class="calendar-day empty"></div>
                                <div class="calendar-day empty"></div>
                                <div class="calendar-day empty"></div>
                                <div class="calendar-day empty"></div>
                            </div>

                            <!-- Calendar Legend -->
                            <div class="calendar-legend">
                                <div class="legend-item">
                                    <div class="legend-color" style="background: rgba(56, 239, 125, 0.15);"></div>
                                    <span>Present</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: rgba(245, 87, 108, 0.15);"></div>
                                    <span>Absent</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: rgba(255, 171, 0, 0.15);"></div>
                                    <span>On Leave</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: #f8f9fa;"></div>
                                    <span>Weekend</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Request Leave Modal -->
    <div class="modal fade" id="requestLeaveModal" tabindex="-1" aria-labelledby="requestLeaveLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('Submissions.store') }}" method="POST" id="leaveRequestForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="requestLeaveLabel">
                            <i class="fas fa-paper-plane me-2"></i>
                            Request Leave
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Leave Type -->
                        <div class="mb-4">
                            <label class="form-label" for="leave_type">
                                <i class="fas fa-clipboard-list me-1"></i> Leave Type
                            </label>
                            <select name="type" id="leave_type" class="form-control" required>
                                <option value="">Choose leave type</option>
                                <option value="Annual Leave">Annual Leave</option>
                                <option value="Sick Leave">Sick Leave</option>
                                <option value="Casual Leave">Casual Leave</option>
                                <option value="Emergency Leave">Emergency Leave</option>
                            </select>
                        </div>

                        <!-- Leave Balance Info -->
                        <div class="alert alert-info mb-4" id="leaveBalanceInfo">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Available Balance:</strong>
                                    <span id="availableBalance">12 days</span>
                                </div>
                                <div>
                                    <strong>Total Allocation:</strong>
                                    <span id="totalAllocation">14 days</span>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="start_date">
                                    <i class="fas fa-calendar-alt me-1"></i> Start Date
                                </label>
                                <input type="date" name="leave_date_from" id="start_date" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="end_date">
                                    <i class="fas fa-calendar-check me-1"></i> End Date
                                </label>
                                <input type="date" name="leave_date_to" id="end_date" class="form-control" required>
                            </div>
                        </div>

                        <!-- Duration Display -->
                        <div class="alert alert-light border mb-4" id="durationInfo">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hourglass-half me-2"></i><strong>Duration:</strong></span>
                                <span id="calculatedDuration" class="text-primary font-weight-bold">0 days</span>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="mb-3">
                            <label class="form-label" for="leave_reason">
                                <i class="fas fa-sticky-note me-1"></i> Reason
                            </label>
                            <textarea name="notes" id="leave_reason" class="form-control" rows="4"
                                placeholder="Please provide a brief reason for your leave request..." required></textarea>
                        </div>

                        <!-- Emergency Contact (for longer leaves) -->
                        <div class="mb-3" id="emergencyContactDiv" style="display: none;">
                            <label class="form-label" for="emergency_contact">
                                <i class="fas fa-phone me-1"></i> Emergency Contact (Optional)
                            </label>
                            <input type="text" name="emergency_contact" id="emergency_contact" class="form-control"
                                placeholder="Contact number during leave">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Announcement Preview Modal -->
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-bullhorn me-2"></i>
                        Holiday Schedule for December
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4 pb-3 border-bottom">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Published By</small>
                                <p class="mb-0 font-weight-bold">HR Department</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Date</small>
                                <p class="mb-0 font-weight-bold">December 1, 2024</p>
                            </div>
                        </div>
                    </div>
                    <div style="line-height: 1.8;">
                        <p>Dear Team,</p>
                        <p>Please note the following holiday schedule for December 2024:</p>
                        <ul>
                            <li><strong>Christmas Eve & Christmas:</strong> December 24-26, 2024 (Office Closed)</li>
                            <li><strong>New Year's Eve & New Year:</strong> December 31, 2024 - January 1, 2025 (Office
                                Closed)</li>
                        </ul>
                        <p>Regular office hours will resume on January 2, 2025.</p>
                        <p>For urgent matters during the holiday period, please contact the emergency hotline.</p>
                        <p>Wishing you and your families a wonderful holiday season!</p>
                        <p>Best regards,<br>HR Department</p>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <small class="text-muted text-center w-100">
                        <i class="fas fa-shield-alt me-2"></i>
                        Official announcement from HR Department
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection
