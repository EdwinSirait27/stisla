@extends('layouts.app')
@section('title', 'HR Manager Dashboard')
@push('styles')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">

    <style>
        .metric-card {
            transition: transform 0.2s;
        }

        .metric-card:hover {
            transform: translateY(-5px);
        }

        .progress-sm {
            height: 8px;
        }

        .attendance-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .metric-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .metric-card .card-icon {
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        /* Tooltip styling (opsional untuk kustomisasi lebih lanjut) */
        .tooltip-custom {
            position: relative;
        }

        .tooltip-custom:hover::after {
            content: attr(data-title);
            position: absolute;
            bottom: 110%;
            left: 50%;
            transform: translateX(-50%);
            background: #343a40;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            white-space: nowrap;
            font-size: 13px;
            opacity: 0.9;
            z-index: 999;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>HR Manager Dashboard</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item">HR Manager</div>
                </div>
            </div>

            <div class="section-body">
                <!-- Overview Cards -->
                <div class="row">


                    <div class="col-lg-3 col-md-6 col-sm-6 col-12" title="View list of all employees">
                        <div onclick="window.location='{{ route('pages.Employee') }}';" style="cursor: pointer;"
                            title="Lihat daftar semua karyawan" class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-primary">
                                <i class="far fa-user"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Active</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalEmployees ?? null }} Employees
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-success">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Hadir Hari Ini</h4>
                                </div>
                                <div class="card-body">
                                    {{ $presentToday ?? null }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Izin/Cuti</h4>
                                </div>
                                <div class="card-body">
                                    {{ $onLeave ?? null }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1 metric-card">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-user-times"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Tidak Hadir</h4>
                                </div>
                                <div class="card-body">
                                    {{ $absent ?? null }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Tingkat Kehadiran -->
                    <div class="col-lg-8 col-md-12 col-12 col-sm-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Monthly Attendance Rate</h4>
                               <div class="card-header-action">
        <input type="month" id="monthPicker" class="form-control" value="{{ now()->format('Y-m') }}">
    </div>



                            </div>
                            {{-- <div class="card-body">
                                <canvas id="attendanceChart" height="180"></canvas>
                                <div class="statistic-details mt-3">
                                    <div class="statistic-details-item">
                                        <span class="text-muted"><span class="text-primary"><i
                                                    class="fas fa-caret-up"></i></span> 7%</span>
                                        <div class="detail-value">$243</div>
                                        <div class="detail-name">Peningkatan dari bulan lalu</div>
                                    </div>
                                    <div class="statistic-details-item">
                                        <span class="text-muted"><span class="text-danger"><i
                                                    class="fas fa-caret-down"></i></span> 23%</span>
                                        <div class="detail-value">$2,902</div>
                                        <div class="detail-name">Tingkat absensi</div>
                                    </div>
                                    <div class="statistic-details-item">
                                        <span class="text-muted"><span class="text-primary"><i
                                                    class="fas fa-caret-up"></i></span>9%</span>
                                        <div class="detail-value">$12,821</div>
                                        <div class="detail-name">Rata-rata kehadiran</div>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="card-body">
    <canvas id="attendanceChart" height="180"></canvas>
</div>
                        </div>
                    </div>

                    <!-- Pengajuan Pending -->
                    <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Submission Pending</h4>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled list-unstyled-border">
                                    <li class="media">
                                        <img class="mr-3 rounded-circle" width="50"
                                            src="{{ asset('img/avatar/avatar-1.png') }}" alt="avatar">
                                        <div class="media-body">
                                            <div class="float-right"><small>2 jam lalu</small></div>
                                            <div class="media-title">John Doe</div>
                                            <span class="text-small text-muted">Cuti Tahunan - 3 hari</span>
                                        </div>
                                    </li>
                                    <li class="media">
                                        <img class="mr-3 rounded-circle" width="50"
                                            src="{{ asset('img/avatar/avatar-2.png') }}" alt="avatar">
                                        <div class="media-body">
                                            <div class="float-right"><small>4 jam lalu</small></div>
                                            <div class="media-title">Jane Smith</div>
                                            <span class="text-small text-muted">Izin Sakit - 1 hari</span>
                                        </div>
                                    </li>
                                    <li class="media">
                                        <img class="mr-3 rounded-circle" width="50"
                                            src="{{ asset('img/avatar/avatar-3.png') }}" alt="avatar">
                                        <div class="media-body">
                                            <div class="float-right"><small>6 jam lalu</small></div>
                                            <div class="media-title">Michael Johnson</div>
                                            <span class="text-small text-muted">Lembur - 2 jam</span>
                                        </div>
                                    </li>
                                    <li class="media">
                                        <img class="mr-3 rounded-circle" width="50"
                                            src="{{ asset('img/avatar/avatar-4.png') }}" alt="avatar">
                                        <div class="media-body">
                                            <div class="float-right"><small>1 hari lalu</small></div>
                                            <div class="media-title">Sarah Wilson</div>
                                            <span class="text-small text-muted">Cuti Melahirkan</span>
                                        </div>
                                    </li>
                                </ul>
                                <div class="text-center pt-1 pb-1">
                                    <a href="#" class="btn btn-primary btn-lg btn-round">
                                        Lihat Semua Pengajuan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Departemen Overview -->
                    <div class="col-lg-6 col-md-12 col-12 col-sm-12">
                        {{-- <div class="card">
                            <div class="card-header">
                                <h4>Presence of each Departments</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <div class="text-small float-right font-weight-bold text-muted">IT Department</div>
                                    <div class="font-weight-bold mb-1">95% (38/40)</div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 95%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="text-small float-right font-weight-bold text-muted">Marketing</div>
                                    <div class="font-weight-bold mb-1">88% (22/25)</div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 88%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="text-small float-right font-weight-bold text-muted">Finance</div>
                                    <div class="font-weight-bold mb-1">92% (23/25)</div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 92%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="text-small float-right font-weight-bold text-muted">Operations</div>
                                    <div class="font-weight-bold mb-1">85% (51/60)</div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 85%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="text-small float-right font-weight-bold text-muted">HR</div>
                                    <div class="font-weight-bold mb-1">100% (8/8)</div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        <div class="card">
    <div class="card-header">
                                <h4>Make an Annauncement</h4>
        
    </div>
    <div class="card-body">
        <form action="{{ route('dashboardHR.store') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label for="title">Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="content">Announcement Contents</label>
                <textarea name="content" id="editor" class="form-control"></textarea>
            </div>

            <div class="form-group mb-3">
                <label for="publish_date">Publish Date</label>
                <input type="date" name="publish_date" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
                    </div>

                    <!-- Aktivitas Terbaru -->
                    <div class="col-lg-6 col-md-12 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Aktivitas HR Terbaru</h4>
                            </div>
                            <div class="card-body">
                                <div class="activities">
                                    <div class="activity">
                                        <div class="activity-icon bg-primary text-white shadow-primary">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                        <div class="activity-detail">
                                            <div class="mb-2">
                                                <span class="text-job">10 menit lalu</span>
                                            </div>
                                            <p>Karyawan baru <a href="#">Alex Thompson</a> telah ditambahkan ke
                                                sistem</p>
                                        </div>
                                    </div>
                                    <div class="activity">
                                        <div class="activity-icon bg-success text-white shadow-success">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="activity-detail">
                                            <div class="mb-2">
                                                <span class="text-job">25 menit lalu</span>
                                            </div>
                                            <p>Cuti <a href="#">Maria Garcia</a> telah disetujui</p>
                                        </div>
                                    </div>
                                    <div class="activity">
                                        <div class="activity-icon bg-warning text-white shadow-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div class="activity-detail">
                                            <div class="mb-2">
                                                <span class="text-job">1 jam lalu</span>
                                            </div>
                                            <p>Peringatan: Tingkat absensi departemen Operations meningkat</p>
                                        </div>
                                    </div>
                                    <div class="activity">
                                        <div class="activity-icon bg-info text-white shadow-info">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                        <div class="activity-detail">
                                            <div class="mb-2">
                                                <span class="text-job">2 jam lalu</span>
                                            </div>
                                            <p>Meeting evaluasi kinerja dijadwalkan untuk minggu depan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                {{-- <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="{{ route('Employee.create') }}"
                                            class="btn btn-primary btn-lg btn-block btn-icon-split">
                                            <i class="fas fa-user-plus"></i> Tambah Karyawan
                                        </a>

                                    </div>
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="#" class="btn btn-success btn-lg btn-block btn-icon-split">
                                            <i class="fas fa-calendar-check"></i> Kelola Cuti
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="#" class="btn btn-warning btn-lg btn-block btn-icon-split">
                                            <i class="fas fa-clock"></i> Timesheet
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="#" class="btn btn-info btn-lg btn-block btn-icon-split">
                                            <i class="fas fa-chart-bar"></i> Laporan
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="#" class="btn btn-secondary btn-lg btn-block btn-icon-split">
                                            <i class="fas fa-users"></i> Kelola Tim
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="#" class="btn btn-dark btn-lg btn-block btn-icon-split">
                                            <i class="fas fa-cog"></i> Pengaturan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
                <div class="max-w-3xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Daftar Pengumuman</h1>

    @foreach($announcements as $announcement)
        <div class="bg-white rounded-xl shadow p-4 mb-4">
            <h2 class="text-lg font-semibold text-gray-800">
                {{ $announcement->title }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ $announcement->created_at->format('d M Y H:i') }}
            </p>
            <button 
                @click="open = true; selected = {{ $announcement->id }}" 
                class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                Lihat Detail
            </button>
        </div>
    @endforeach
</div>

<!-- Modal -->
<div 
    x-data="{ open: false, selected: null }" 
    x-cloak
>
    <template x-for="announcement in {{ $announcements->toJson() }}">
        <div 
            x-show="open && selected === announcement.id"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        >
            <div class="bg-white w-11/12 md:w-2/3 rounded-2xl shadow-lg p-6 relative">
                <button 
                    @click="open = false" 
                    class="absolute top-3 right-3 text-gray-500 hover:text-gray-700"
                >
                    ✖
                </button>
                <h2 class="text-xl font-bold text-gray-800 mb-2" x-text="announcement.title"></h2>
                <p class="text-sm text-gray-500 mb-4" 
                   x-text="'Dipublikasikan: ' + new Date(announcement.created_at).toLocaleString()"></p>
                <div class="prose max-w-none" x-html="announcement.content"></div>
            </div>
        </div>
    </template>
</div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraries -->
    <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script>
    <script src="{{ asset('library/jqvmap/dist/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('library/jqvmap/dist/maps/jquery.vmap.world.js') }}"></script>
    <script src="{{ asset('library/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
<script>
  tinymce.init({
    selector: '#editor',
    plugins: 'lists link image table code',
    toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
    menubar: false,
    height: 300,
    license_key: 'gpl' // <-- ini wajib ditambahkan untuk free GPL license
  });
</script>




    <!-- Page Specific JS File -->
    <script>
        // Attendance Chart
        // var ctx = document.getElementById("attendanceChart").getContext('2d');
        // var attendanceChart = new Chart(ctx, {
        //     type: 'line',
        //     data: {
        //         labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        //         datasets: [{
        //             label: 'Presence',
        //             data: [95, 92, 88, 94, 96, 85],
        //             borderWidth: 2,
        //             backgroundColor: '#6777ef',
        //             borderColor: '#6777ef',
        //             borderWidth: 2.5,
        //             pointBackgroundColor: '#ffffff',
        //             pointRadius: 4
        //         }]
        //     },
        //     options: {
        //         legend: {
        //             display: false
        //         },
        //         scales: {
        //             yAxes: [{
        //                 gridLines: {
        //                     drawBorder: false,
        //                     color: '#f2f2f2',
        //                 },
        //                 ticks: {
        //                     beginAtZero: true,
        //                     stepSize: 10,
        //                     callback: function(value, index, values) {
        //                         return value + '%';
        //                     }
        //                 }
        //             }],
        //             xAxes: [{
        //                 gridLines: {
        //                     display: false,
        //                     tickMarkLength: 15,
        //                 }
        //             }]
        //         },
        //         tooltips: {
        //             callbacks: {
        //                 label: function(tooltipItem, data) {
        //                     return data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltipItem.yLabel +
        //                         '%';
        //                 }
        //             }
        //         }
        //     }
        // });
        let ctx = document.getElementById('attendanceChart').getContext('2d');

let attendanceChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [], // Senin–Sabtu nanti dari AJAX
        datasets: [{
            label: 'number of attendees',
            data: [],
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                // tidak perlu max: 100 karena bukan persen lagi
                ticks: {
                    precision: 0 // biar tidak ada koma
                },
                title: {
                    display: true,
                    text: 'Jumlah Karyawan Hadir'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Hari (Senin–Sabtu)'
                }
            }
        }
    }
});

function loadChartData(month) {
    fetch(`{{ route('dashboardHR.data') }}?month=${month}`)
        .then(res => res.json())
        .then(data => {
            attendanceChart.data.labels = data.days;
            attendanceChart.data.datasets[0].data = data.counts; // pakai counts dari controller
            attendanceChart.update();
        });
}

document.addEventListener("DOMContentLoaded", function() {
    loadChartData(document.getElementById('monthPicker').value);
});

document.getElementById('monthPicker').addEventListener('change', function() {
    loadChartData(this.value);
});

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
            });
        @endif
        flatpickr("#monthPicker", {
            locale: "en", // Bahasa Indonesia
            plugins: [
                new monthSelectPlugin({
                    shorthand: true, // Jan, Feb, ...
                    dateFormat: "Y-m", // format kirim ke backend
                    altFormat: "F Y", // format tampilan
                    theme: "light",
                     // bisa diganti "dark", "material_blue", dll
                })
            ]
        });
    </script>
@endpush
