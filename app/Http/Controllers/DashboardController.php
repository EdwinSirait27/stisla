<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('pages.Dashboard.Dashboard');
    }
}
// @extends('layouts.app')
// @section('title', 'Manager Dashboard')

// @push('style')
// <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.min.css" />
// <style>
// .card-icon { font-size: 2rem; color: #fff; }
// .quick-action-card { transition: all 0.25s ease-in-out; border-radius: 12px; }
// .quick-action-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
// .icon-circle {
//     display: inline-flex; align-items: center; justify-content: center;
//     width: 60px; height: 60px; border-radius: 50%;
// }
// </style>
// @endpush

// @section('main')
// <div class="main-content">
//     <section class="section">
//         <div class="section-header">
//             <h1>Dashboard Manager</h1>
//         </div>

//         <div class="section-body">
//             <!-- Header -->
//             <div class="row align-items-center mb-4">
//                 <div class="col-md-8 d-flex align-items-center">
//                     <img alt="image" src="{{ asset('img/avatar/avatar-2.png') }}" class="rounded-circle mr-3" width="70">
//                     <div>
//                         <h4>{{ Auth::user()->employee->name ?? Auth::user()->username }}</h4>
//                         <p class="mb-1 text-muted">
//                             {{ Auth::user()->employee->position->name ?? 'Manager' }} - 
//                             {{ Auth::user()->employee->department->name ?? 'Department' }}
//                         </p>
//                         <span class="badge badge-primary">Manager</span>
//                     </div>
//                 </div>
//                 <div class="col-md-4 text-right">
//                     <small>Bergabung sejak {{ Auth::user()->employee->join_date ?? '2021-01-01' }}</small><br>
//                     <small>Lama kerja: 3 Tahun 2 Bulan</small>
//                 </div>
//             </div>

//             <!-- Quick Actions -->
//             <div class="row mb-4">
//                 @php
//                     $actions = [
//                         ['icon' => 'fa-user-check', 'label' => 'Setujui Cuti', 'url' => route('manager.approvals'), 'color' => '#4e73df'],
//                         ['icon' => 'fa-users', 'label' => 'Data Tim', 'url' => route('manager.team'), 'color' => '#1cc88a'],
//                         ['icon' => 'fa-chart-bar', 'label' => 'Evaluasi Kinerja', 'url' => route('manager.evaluation'), 'color' => '#36b9cc'],
//                         ['icon' => 'fa-clock', 'label' => 'Monitoring Absensi', 'url' => route('manager.attendance'), 'color' => '#f6c23e'],
//                         ['icon' => 'fa-file-alt', 'label' => 'Laporan Bulanan', 'url' => route('manager.reports'), 'color' => '#e74a3b'],
//                         ['icon' => 'fa-calendar-alt', 'label' => 'Agenda Tim', 'url' => route('manager.calendar'), 'color' => '#858796'],
//                     ];
//                 @endphp

//                 @foreach ($actions as $action)
//                     <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-4">
//                         <a href="{{ $action['url'] }}" class="text-decoration-none">
//                             <div class="card text-center shadow-sm border-0 quick-action-card h-100">
//                                 <div class="card-body py-4">
//                                     <div class="icon-circle mb-2" style="background: {{ $action['color'] }}20;">
//                                         <i class="fas {{ $action['icon'] }}" style="color: {{ $action['color'] }}; font-size: 28px;"></i>
//                                     </div>
//                                     <div class="text-dark font-weight-bold">{{ $action['label'] }}</div>
//                                 </div>
//                             </div>
//                         </a>
//                     </div>
//                 @endforeach
//             </div>

//             <!-- Statistik -->
//             <div class="row">
//                 <div class="col-lg-3 col-md-6">
//                     <div class="card card-statistic-1">
//                         <div class="card-icon bg-primary"><i class="fas fa-users"></i></div>
//                         <div class="card-wrap">
//                             <div class="card-header"><h4>Total Anggota Tim</h4></div>
//                             <div class="card-body">12</div>
//                         </div>
//                     </div>
//                 </div>

//                 <div class="col-lg-3 col-md-6">
//                     <div class="card card-statistic-1">
//                         <div class="card-icon bg-success"><i class="fas fa-plane"></i></div>
//                         <div class="card-wrap">
//                             <div class="card-header"><h4>Cuti Aktif</h4></div>
//                             <div class="card-body">3 Orang</div>
//                         </div>
//                     </div>
//                 </div>

//                 <div class="col-lg-3 col-md-6">
//                     <div class="card card-statistic-1">
//                         <div class="card-icon bg-warning"><i class="fas fa-clock"></i></div>
//                         <div class="card-wrap">
//                             <div class="card-header"><h4>Kehadiran Rata-rata</h4></div>
//                             <div class="card-body">92%</div>
//                         </div>
//                     </div>
//                 </div>

//                 <div class="col-lg-3 col-md-6">
//                     <div class="card card-statistic-1">
//                         <div class="card-icon bg-info"><i class="fas fa-chart-line"></i></div>
//                         <div class="card-wrap">
//                             <div class="card-header"><h4>Performa Tim</h4></div>
//                             <div class="card-body">89 / 100</div>
//                         </div>
//                     </div>
//                 </div>
//             </div>

//             <!-- Grafik -->
//             <div class="row">
//                 <div class="col-lg-8">
//                     <div class="card">
//                         <div class="card-header"><h4>Grafik Kehadiran Tim</h4></div>
//                         <div class="card-body">
//                             <canvas id="teamAttendanceChart" height="130"></canvas>
//                         </div>
//                     </div>
//                 </div>

//                 <div class="col-lg-4">
//                     <div class="card">
//                         <div class="card-header"><h4>Notifikasi HR</h4></div>
//                         <div class="card-body">
//                             <div class="alert alert-info mb-2"><i class="fas fa-bullhorn"></i> Evaluasi Q4 segera dimulai</div>
//                             <div class="alert alert-warning"><i class="fas fa-user-clock"></i> Ada 2 pengajuan cuti menunggu persetujuan</div>
//                         </div>
//                     </div>
//                 </div>
//             </div>

//             <!-- Aktivitas Tim -->
//             <div class="row">
//                 <div class="col-lg-6">
//                     <div class="card">
//                         <div class="card-header"><h4>Aktivitas Tim Terbaru</h4></div>
//                         <div class="card-body">
//                             <ul class="list-unstyled">
//                                 <li><i class="fas fa-check text-success"></i> Rina melakukan clock in 08:05</li>
//                                 <li><i class="fas fa-plane text-primary"></i> Budi mengajukan cuti 3 hari</li>
//                                 <li><i class="fas fa-chart-line text-info"></i> Evaluasi tim marketing selesai</li>
//                             </ul>
//                         </div>
//                     </div>
//                 </div>

//                 <div class="col-lg-6">
//                     <div class="card">
//                         <div class="card-header"><h4>Performa Departemen</h4></div>
//                         <div class="card-body">
//                             <canvas id="performanceChart" height="130"></canvas>
//                         </div>
//                     </div>
//                 </div>
//             </div>

//         </div>
//     </section>
// </div>
// @endsection

// @push('scripts')
// <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
// <script>
// const teamCtx = document.getElementById('teamAttendanceChart');
// new Chart(teamCtx, {
//     type: 'line',
//     data: {
//         labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'],
//         datasets: [{
//             label: 'Kehadiran Tim (%)',
//             data: [88, 90, 91, 92, 93, 94, 92, 95, 94, 96],
//             borderColor: '#4e73df',
//             fill: false,
//             tension: 0.3
//         }]
//     },
//     options: { scales: { y: { beginAtZero: true, max: 100 } } }
// });

// const perfCtx = document.getElementById('performanceChart');
// new Chart(perfCtx, {
//     type: 'bar',
//     data: {
//         labels: ['Finance', 'HR', 'Marketing', 'IT', 'Sales'],
//         datasets: [{
//             label: 'Nilai Kinerja',
//             data: [85, 90, 88, 92, 89],
//             backgroundColor: '#36b9cc'
//         }]
//     },
//     options: { scales: { y: { beginAtZero: true, max: 100 } } }
// });
// </script>
// @endpush
