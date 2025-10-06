@extends('layouts.app')
@section('title', 'HR Manager Dashboard')
@push('styles')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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

        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal {
            z-index: 1050 !important;
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
                    <div class="col-lg-8 col-md-12 col-12 col-sm-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Monthly Attendance Rate
                                </h4>

                                {{-- <div class="card-header-action">
                                    <input type="month" id="monthPicker" class="form-control"
                                        value="{{ now()->format('Y-m') }}">
                                </div> --}}
                                <div class="card-header-action d-flex gap-2">
                                    <input type="date" id="startDate" class="form-control"
                                        value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                    <input type="date" id="endDate" class="form-control"
                                        value="{{ now()->endOfMonth()->format('Y-m-d') }}">

                                    <button id="filterButton" class="btn btn-primary">
                                        Filter
                                    </button>
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

                   

                    <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>
                                    <i class="fas fa-atom me-2"></i>
                                    Submission Pending
                                </h4>
                                <button id="btn-submission" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#createSubmissionModal">
                                    <i class="fas fa-plus me-1"></i>
                                    Create Submission
                                </button>
                            </div>

                            <div class="card-body">
                                <ul class="list-unstyled list-unstyled-border">
                                    @forelse($pendingSubmissions as $submission)
                                        <li class="media">
                                            <img class="mr-3 rounded-circle" width="50"
                                                src="{{ asset('img/avatar/avatar-' . rand(1, 4) . '.png') }}"
                                                alt="avatar">

                                            <div class="media-body">
                                                <div class="float-right">
                                                    <small>{{ $submission->created_at->diffForHumans() }}</small>
                                                </div>

                                                <div class="media-title">
                                                    {{ $submission->employee->employee_name }}
                                                </div>

                                                <span class="text-small text-muted">
                                                    {{ ucfirst($submission->type) }} -
                                                    {{ $submission->duration }}
                                                    {{ Str::plural('Day/Hour', $submission->duration) }}
                                                </span>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="media">
                                            <div class="media-body text-center text-muted">
                                                There is no pending applications yet :)
                                            </div>
                                        </li>
                                    @endforelse
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


                    <div class="col-lg-12 col-md-12 col-12 col-sm-12">


                        <div class="card">

                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>
                                    <i class="fas fa-book me-2"></i>
                                    List of Announcements
                                </h4>

                                <button id="btn-announcement" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>
                                    Make an Announcement
                                </button>
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
                    <div class="mb-4">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                <tr>
                                    <th scope="row" class="text-dark" style="width: 130px;">Publish Date</th>
                                    <td style="width: 180px;"><span id="previewDate" class="fw-semibold"></span></td>

                                    <th scope="row" class="text-dark" style="width: 500px; text-align: right;">End
                                        Date</th>
                                    <td style="width: 210px; text-align: right; "><span id="previewEndDate"
                                            class="fw-semibold"></span></td>
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
    <div class="modal fade" id="createSubmissionModal" tabindex="-1" role="dialog"
        aria-labelledby="createSubmissionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('Submissions.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createSubmissionLabel">
                            <i class="fas fa-plus me-2"></i> Create New Submission
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>

                    {{-- <div class="modal-body">
                     
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" id="type" class="form-control select2" required>
                                <option value="" disabled selected>-- Select Type --</option>
                                <option value="Annual Leave">Annual Leave</option>
                                <option value="Overtime">Overtime</option>
                            </select>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">Leave Date From</label>
                            <input type="date" name="leave_date_from" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Leave Date To</label>
                            <input type="date" name="leave_date_to" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration</label>
                            <input type="number" name="duration" class="form-control" required>
                        </div>
                    </div> --}}
                    <div class="modal-body">
    <div class="mb-3">
        <label class="form-label">Type</label>
        <select name="type" id="type" class="form-control select2" required>
            <option value="" disabled selected>-- Select Type --</option>
            <option value="Annual Leave">Annual Leave</option>
            <option value="Overtime">Overtime</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Leave Date From</label>
        <input type="date" name="leave_date_from" id="leave_date_from" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Leave Date To</label>
        <input type="date" name="leave_date_to" id="leave_date_to" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Duration</label>
        <input type="number" name="duration" class="form-control" required>
    </div>
</div>




                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </form>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function(){
    $('#type').on('change', function() {
        const type = $(this).val();

        if(type === 'Overtime') {
            $('#leave_date_from').attr('type', 'datetime-local');
            $('#leave_date_to').attr('type', 'datetime-local');
        } else {
            $('#leave_date_from').attr('type', 'date');
            $('#leave_date_to').attr('type', 'date');
        }
    });
});
</script>
    <script>
       
        let ctx = document.getElementById('attendanceChart').getContext('2d');

        let attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [], // tanggal (misal: 2025-10-01)
                datasets: [{
                    label: 'Attendance Percentage (%)',
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
                        max: 100, // karena data sekarang dalam persen
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Persentase Kehadiran'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal Scan'
                        }
                    }
                }
            }
        });

        // --- Fungsi Load Data ---
        function loadChartData(startDate, endDate) {
            fetch(`{{ route('dashboardHR.data') }}?start_date=${startDate}&end_date=${endDate}`)
                .then(res => res.json())
                .then(data => {
                    const labels = data.data.map(item => item.date);
                    const percentages = data.data.map(item => item.percentage);

                    attendanceChart.data.labels = labels;
                    attendanceChart.data.datasets[0].data = percentages;
                    attendanceChart.update();
                });
        }

        // --- Saat halaman pertama kali dimuat ---
        document.addEventListener("DOMContentLoaded", function() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            loadChartData(start, end);
        });

        // --- Saat tombol Filter ditekan ---
        document.getElementById('filterButton').addEventListener('click', function() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            loadChartData(start, end);
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
            });
        @endif
        @if (session('error'))
            Swal.fire({
                title: 'Gagal!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        @endif
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

    <script>
        document.getElementById('btn-announcement').addEventListener('click', function() {
            Swal.fire({
                title: 'Make an Announcement',
                html: `
                <form id="announcementForm" action="{{ route('dashboardHR.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3 text-start">
                        <label for="title">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="form-group mb-3 text-start">
                        <label for="content">Announcement Contents</label>
                        <textarea id="editor" name="content" class="form-control"></textarea>
                    </div>

                    <div class="form-group mb-3 text-start">
                        <label for="publish_date">Publish Date</label>
                        <input type="date" name="publish_date" class="form-control" required>
                    </div>

                    <div class="form-group mb-3 text-start">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </form>
            `,
                showCancelButton: true,
                confirmButtonText: 'Save',
                cancelButtonText: 'Cancel',
                focusConfirm: false,

                didOpen: () => {
                    // reset TinyMCE sebelumnya kalau ada
                    if (tinymce.get('editor')) {
                        tinymce.get('editor').remove();
                    }

                    // init TinyMCE setelah modal muncul
                    tinymce.init({
                        selector: '#editor',
                        plugins: 'lists link image table code',
                        toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
                        menubar: false,
                        height: 300,
                        license_key: 'gpl'
                    });
                },

                willClose: () => {
                    // hapus editor saat modal ditutup supaya tidak nempel di memory
                    if (tinymce.get('editor')) {
                        tinymce.get('editor').remove();
                    }
                },

                preConfirm: () => {
                    // sinkronkan isi TinyMCE ke textarea
                    tinymce.triggerSave();

                    // validasi manual
                    let title = document.querySelector('input[name="title"]').value.trim();
                    let content = document.querySelector('textarea[name="content"]').value.trim();
                    let publish_date = document.querySelector('input[name="publish_date"]').value;

                    if (!title) {
                        Swal.showValidationMessage('Title is required');
                        return false;
                    }
                    if (!content) {
                        Swal.showValidationMessage('Announcement content is required');
                        return false;
                    }
                    if (!publish_date) {
                        Swal.showValidationMessage('Publish date is required');
                        return false;
                    }

                    // submit form kalau lolos validasi
                    document.getElementById('announcementForm').submit();
                }
            });
        });
    </script>
    <script>
        $(document).on('hidden.bs.modal', function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        });
    </script>
    @push('scripts')
        <script>
            $(document).ready(function() {
                // Inisialisasi select2
                $('#type').select2({
                    theme: 'bootstrap4',
                    placeholder: '-- Select Type --',
                    width: '100%'
                });

                // Saat modal dibuka, refresh select2 agar tampil dengan benar
                $('#createSubmissionModal').on('shown.bs.modal', function() {
                    $('#type').select2({
                        dropdownParent: $('#createSubmissionModal')
                    });
                });
            });
        </script>
    @endpush
@endpush
 {{-- <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                        <div class="card">

                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>
                                    <i class="fas fa-atom me-2"></i>
                                    Submission Pending
                                </h4>
                                <button id="btn-submission" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#createSubmissionModal">
                                    <i class="fas fa-plus me-1"></i>
                                    Create Submission
                                </button>


                            </div>

                            <div class="card-body">

                                <ul class="list-unstyled list-unstyled-border">
                                    @forelse($pendingSubmissions as $submission)
                                        <li class="media">
                                            <img class="mr-3 rounded-circle" width="50"
                                                src="{{ asset('img/avatar/avatar-' . rand(1, 4) . '.png') }}"
                                                alt="avatar">

                                            <div class="media-body">
                                                <div class="float-right">
                                                    <small>{{ $submission->created_at->diffForHumans() }}</small>
                                                </div>

                                                <div class="media-title">
                                                    {{ $submission->employee->employee_name }}
                                                </div>

                                                <span class="text-small text-muted">
                                                    {{ ucfirst($submission->type) }} -
                                                    {{ $submission->duration }}
                                                    {{ Str::plural('Day', $submission->duration) }}
                                                </span>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="media">
                                            <div class="media-body text-center text-muted">
                                                Belum ada pengajuan pending.
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>

                                <div class="text-center pt-1 pb-1">
                                    <a href="#" class="btn btn-primary btn-lg btn-round">
                                        View All Submissions
                                    </a>
                                </div>
                            </div>



                            <div class="modal fade" id="createSubmissionModal" tabindex="-1"
                                aria-labelledby="createSubmissionLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('Submissions.store') }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createSubmissionLabel">
                                                    <i class="fas fa-plus me-2"></i> Create New Submission
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Type</label>
                                                    <input type="text" name="type" class="form-control"
                                                        placeholder="Example: Annual Leave" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Leave Date From</label>
                                                    <input type="date" name="leave_date_from" class="form-control"
                                                        required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Leave Date To</label>
                                                    <input type="date" name="leave_date_to" class="form-control"
                                                        required>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-1"></i> Save
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>





                        </div>
                    </div> --}}
                     {{-- // let ctx = document.getElementById('attendanceChart').getContext('2d');

        // let attendanceChart = new Chart(ctx, {
        //     type: 'bar',
        //     data: {
        //         labels: [], // Senin–Sabtu nanti dari AJAX
        //         datasets: [{
        //             label: 'Number of Attendees',
        //             data: [],
        //             backgroundColor: 'rgba(54, 162, 235, 0.6)',
        //             borderColor: 'rgba(54, 162, 235, 1)',
        //             borderWidth: 1
        //         }]
        //     },
        //     options: {
        //         responsive: true,
        //         scales: {
        //             y: {
        //                 beginAtZero: true,
        //                 // tidak perlu max: 100 karena bukan persen lagi
        //                 ticks: {
        //                     precision: 0 // biar tidak ada koma
        //                 },
        //                 title: {
        //                     display: true,
        //                     text: 'Jumlah Karyawan Hadir'
        //                 }
        //             },
        //             x: {
        //                 title: {
        //                     display: true,
        //                     text: 'Hari (Senin–Sabtu)'
        //                 }
        //             }
        //         }
        //     }
        // });

        // function loadChartData(month) {
        //     fetch(`{{ route('dashboardHR.data') }}?month=${month}`)
        //         .then(res => res.json())
        //         .then(data => {
        //             attendanceChart.data.labels = data.days;
        //             attendanceChart.data.datasets[0].data = data.counts; // pakai counts dari controller
        //             attendanceChart.update();
        //         });
        // }

        // document.addEventListener("DOMContentLoaded", function() {
        //     loadChartData(document.getElementById('monthPicker').value);
        // });

        // document.getElementById('monthPicker').addEventListener('change', function() {
        //     loadChartData(this.value);
        // });
        //    flatpickr("#monthPicker", {
        //     locale: "en", // Bahasa Indonesia
        //     plugins: [
        //         new monthSelectPlugin({
        //             shorthand: true, // Jan, Feb, ...
        //             dateFormat: "Y-m", // format kirim ke backend
        //             altFormat: "F Y", // format tampilan
        //             theme: "light",
        //             // bisa diganti "dark", "material_blue", dll
        //         })
        //     ]
        // }); --}}
           {{-- <div class="mb-3">
                        <label class="form-label">Type</label>
                        <input type="text" name="type" class="form-control"
                            placeholder="Example: Annual Leave" required>
                    </div> --}}