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
                                <h4>
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Monthly Attendance Rate
                                </h4>

                                <div class="card-header-action">
                                    <input type="month" id="monthPicker" class="form-control"
                                        value="{{ now()->format('Y-m') }}">
                                </div>



                            </div>

                            <div class="card-body">
                                <canvas id="attendanceChart" height="180"></canvas>
                                <div class="alert alert-secondary mt-4" role="alert">
                                    <span class="text-dark">
                                        <strong>Important Note:</strong> <br>
                                        - X-axis means date.<br>
                                        - Y-axis total employee attendance based on the x-axis.

                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pengajuan Pending -->
                    <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    <i class="fas fa-atom me-2"></i>
                                    Submission Pending
                                </h4>

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
                                        View All Submissions
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Departemen Overview -->
                    <div class="col-lg-6 col-md-12 col-12 col-sm-12">

                        <div class="card">
                            <div class="card-header">
                                {{-- <h4></h4> --}}
                                <h4>
                                    <i class="fas fa-bell me-2"></i>
                                    Make an Annauncement
                                </h4>


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
                                        <input type="date" name="publish_date" class="form-control"required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="end_date">End Date</label>
                                        <input type="date" name="end_date" class="form-control">
                                    </div>

                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12 col-12 col-sm-12">


                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    <i class="fas fa-book me-2"></i>
                                    List of Announcements
                                </h4>
                            </div>


                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Title</th>
                                                {{-- <th class="text-center">Title</th> --}}
                                                <th class="text-center">Publish Date</th>
                                                <th class="text-center">End Date</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>


    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-3 shadow-lg border-0">

                <!-- Header -->
                <div class="modal-header bg-white border-bottom justify-content-center">
                    <h5 class="modal-title fw-bold text-dark mb-0" id="previewTitle">
                        Announcement Preview
                    </h5>
                </div>


                <!-- Body -->
                <div class="modal-body p-4">
                    {{-- <div class="mb-4">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                <tr>
                                    <th scope="row" class="text-dark" style="width: 120px;">Publish Date</th>
                                    <td><span id="previewDate" class="fw-semibold"></span></td>

                                    <th scope="row" class="text-dark" style="width: 100px;">End Date</th>
                                    <td><span id="previewEndDate" class="fw-semibold"></span></td>
                                </tr>
                                <tr>
                                    <th scope="row" class="text-dark">Created By</th>
                                    <td><span id="previewEmployee" class="fw-semibold"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div> --}}
                    <div class="mb-4">
    <table class="table table-sm align-middle mb-0">
        <tbody>
            <tr>
                <th scope="row" class="text-dark" style="width: 130px;">Publish Date</th>
                <td style="width: 180px;"><span id="previewDate" class="fw-semibold"></span></td>

                <th scope="row" class="text-dark" style="width: 500px; text-align: right;">End Date</th>
                <td style="width: 210px; text-align: right; "><span id="previewEndDate" class="fw-semibold"></span></td>
            </tr>
            <tr>
                <th scope="row" class="text-dark" style="width: 130px;">Created By</th>
                <td colspan="3"><span id="previewEmployee" class="fw-semibold"></span></td>
            </tr>
        </tbody>
    </table>
</div>


                    <div>
                        <div id="previewContent" class="fs-6 text-dark"
                            style="max-height: 450px; overflow-y: auto; line-height: 1.6;"></div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer bg-light border-top justify-content-center text-center">
                    &copy; This is a valid announcement from HR Department.
                    For more information please contact
                    <div class="bullet d-inline-block mx-2"></div>
                    <a href="https://wa.me/6281138310552" target="_blank" rel="noopener noreferrer"
                        style="color:#25D366; text-decoration:none; font-weight:bold;">
                        HR Department
                    </a>
                </div>


            </div>
        </div>
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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


    <script>
        let ctx = document.getElementById('attendanceChart').getContext('2d');

        let attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [], // Senin–Sabtu nanti dari AJAX
                datasets: [{
                    label: 'Number of Attendees',
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

    <script>
        // Wait for jQuery to be fully loaded
        jQuery(document).ready(function($) {
            // Initialize DataTable with proper configuration
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('announcements.announcements') }}',
                    type: 'GET'
                },
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                },
                columns: [

                    {
                        data: 'title',
                        name: 'title',
                        className: 'text-center'
                    },

                    {
                        data: 'publish_date',
                        name: 'publish_date',
                        className: 'text-center',
                        render: function(data) {
                            if (!data) return '-';
                            let date = new Date(data);
                            let day = String(date.getDate()).padStart(2, '0');
                            let monthNames = [
                                "January", "February", "March", "April", "May", "June",
                                "July", "August", "September", "October", "November", "December"
                            ];
                            let month = monthNames[date.getMonth()];
                            let year = date.getFullYear();
                            return `${day} ${month} ${year}`;
                        }
                    },
                    {
                        data: 'end_date',
                        name: 'end_date',
                        className: 'text-center',
                        render: function(data) {
                            if (!data) return 'Continuesly';
                            let date = new Date(data);
                            let day = String(date.getDate()).padStart(2, '0');
                            let monthNames = [
                                "January", "February", "March", "April", "May", "June",
                                "July", "August", "September", "October", "November", "December"
                            ];
                            let month = monthNames[date.getMonth()];
                            let year = date.getFullYear();
                            return `${day} ${month} ${year}`;
                        }
                    },

                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],

            });



        });

        // preview modal 
        $(document).on('click', '.preview-btn', function() {
            let title = $(this).data('title');
            let content = $(this).data('content');
            let date = $(this).data('date');
            let enddate = $(this).data('enddate');
            let employee = $(this).data('employee');

            $('#previewTitle').text(title);
            $('#previewEmployee').text(employee);
            $('#previewDate').text(date);
            $('#previewEndDate').text(enddate);
            $('#previewContent').html(content);

            $('#previewModal').modal('show');
        });
    </script>
@endpush
