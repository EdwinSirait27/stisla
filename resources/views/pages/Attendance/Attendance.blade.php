@extends('layouts.app')
@section('title', 'Employees Attendance')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush
<style>
    /* Card Styles */
    .card {
        border: none;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08);
        border-radius: 0.5rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        background-color: #fff;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.12);
    }

    .card-header {
        background-color: #f8fafc;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        padding: 1.25rem 1.5rem;
    }

    .card-header h6 {
        margin: 0;
        font-weight: 600;
        color: #4a5568;
        display: flex;
        align-items: center;
        font-size: 0.95rem;
    }

    .card-header h6 i {
        margin-right: 0.75rem;
        color: #5e72e4;
        transition: color 0.3s ease;
    }

    /* Table Styles */
    .table-responsive {
        padding: 0 1.5rem;
        overflow: hidden;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background-color: #f8fafc;
        color: #4a5568;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
        position: sticky;
        top: 0;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .table tbody tr {
        transition: all 0.25s ease;
        position: relative;
    }

    .table tbody tr:not(:last-child)::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: rgba(0, 0, 0, 0.05);
    }

    .table tbody tr:hover {
        background-color: rgba(94, 114, 228, 0.03);
        transform: scale(1.002);
    }

    .table tbody td {
        padding: 1.1rem 0.75rem;
        vertical-align: middle;
        color: #4a5568;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        border: none;
        background: #fff;
    }

    .table tbody tr:hover td {
        color: #2d3748;
    }

    /* Text alignment for specific columns */
    .text-center {
        text-align: center;
    }

    /* Action Buttons */
    .action-buttons {
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: flex-end;
    }

    .btn-primary {
        background-color: #5e72e4;
        border-color: #5e72e4;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #4a5bd1;
        border-color: #4a5bd1;
        transform: translateY(-1px);
    }

    /* Section Header */
    .section-header h1 {
        font-weight: 600;
        color: #2d3748;
        font-size: 1.5rem;
    }

    /* Smooth scroll for table */
    .table-responsive {
        -webkit-overflow-scrolling: touch;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            padding: 0 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header {
            padding: 1rem;
        }

        .table thead th {
            font-size: 0.65rem;
            padding: 0.75rem 0.5rem;
        }

        .table tbody td {
            padding: 0.85rem 0.5rem;
            font-size: 0.8rem;
        }
    }
</style>


@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Employees Attendance</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i> List Employees Attendance</h6>
                            </div>


                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="filterKantor">Filter Store</label>
                                        <select id="filterKantor" class="form-control select2">
                                            <option value="">All Stores</option>
                                            @foreach ($kantors as $kantor)
                                                <option value="{{ $kantor }}">{{ $kantor }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="startDate">Dari Tanggal</label>
                                        <input type="date" id="startDate" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="endDate">Sampai Tanggal</label>
                                        <input type="date" id="endDate" class="form-control">
                                    </div>
                                    <div class="col-md-3 align-self-end">
                                        <button id="filterBtn" class="btn btn-primary">Filter</button>
                                        <button id="resetBtn" class="btn btn-secondary">Reset</button>
                                    </div>

                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No.</th>
                                                <th class="text-center">Name</th>
                                                <th class="text-center">Pin Fingerspot</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Departments</th>
                                                <th class="text-center">Store</th>
                                                <th class="text-center">Date</th>
                                                <th class="text-center">Scan 1</th>
                                                <th class="text-center">Scan 2</th>
                                                <th class="text-center">Scan 3</th>
                                                <th class="text-center">Scan 4</th>
                                                <th class="text-center">Scan 5</th>
                                                <th class="text-center">Scan 6</th>
                                                <th class="text-center">Scan 7</th>
                                                <th class="text-center">Scan 8</th>
                                                <th class="text-center">Scan 9</th>
                                                <th class="text-center">Scan 10</th>
                                                <th class="text-center">Scan 11</th>
                                                <th class="text-center">Scan 12</th>
                                                <th class="text-center">Scan 13</th>
                                                <th class="text-center">Scan 14</th>
                                                <th class="text-center">Scan 15</th>
                                                <th class="text-center">Scan 16</th>
                                                <th class="text-center">Scan 17</th>
                                                <th class="text-center">Scan 18</th>
                                                <th class="text-center">Scan 19</th>
                                                <th class="text-center">Scan 20</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="action-buttons">
                                    <button type="button" onclick="window.location='{{ route('pages.Importattendance') }}'"
                                        class="btn btn-primary btn-sm ml-2">
                                        <i class="fas fa-users"></i> Import Attendance
                                    </button>
                                </div>

                                <div class="alert alert-secondary mt-4" role="alert">
                                    <span class="text-dark">
                                        <strong>Important Note:</strong> <br>
                                        - If you want to print payroll, ignore the day, just look at the year and month, you
                                        can only print payrolls once a month, okay.<br>
                                        <br>

                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>



    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script>
        $(document).ready(function() {
            $('#kantor').select2({
                placeholder: 'Choose Stores',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    <script>
        jQuery(document).ready(function($) {

            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('attendance.attendance') }}',
                    data: function(d) {
                        d.kantor = $('#filterKantor').val();
                        d.start_date = $('#startDate').val();
                        d.end_date = $('#endDate').val();
                    }
                },
                responsive: true,
                   dom: '<"row mb-3"<"col-md-6"l><"col-md-6 text-end"B>>rt<"row mt-3"<"col-md-6"i><"col-md-6"p>>',
    buttons: [
        'copy', 'excel', 'print'
    ],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                },
                columns: [{
                        data: null,
                        name: 'id',
                        className: 'text-center align-middle',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        className: 'text-center'
                    },
                    {
                        data: 'pin',
                        name: 'pin',
                        className: 'text-center'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name',
                        className: 'text-center'
                    },
                    {
                        data: 'department_name',
                        name: 'department_name',
                        className: 'text-center'
                    },
                    {
                        data: 'kantor',
                        name: 'kantor',
                        className: 'text-center'
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk',
                        name: 'jam_masuk',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar',
                        name: 'jam_keluar',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk2',
                        name: 'jam_masuk2',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar2',
                        name: 'jam_keluar2',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk3',
                        name: 'jam_masuk3',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar3',
                        name: 'jam_keluar3',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk4',
                        name: 'jam_masuk4',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar4',
                        name: 'jam_keluar4',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk5',
                        name: 'jam_masuk5',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar5',
                        name: 'jam_keluar5',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk6',
                        name: 'jam_masuk6',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar6',
                        name: 'jam_keluar6',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk7',
                        name: 'jam_masuk7',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar7',
                        name: 'jam_keluar7',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk8',
                        name: 'jam_masuk8',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar8',
                        name: 'jam_keluar8',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk9',
                        name: 'jam_masuk9',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar9',
                        name: 'jam_keluar9',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk10',
                        name: 'jam_masuk10',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar10',
                        name: 'jam_keluar10',
                        className: 'text-center'
                    },
                ],
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-control');
                }
            });

            $('#filterBtn').on('click', function() {
                table.draw();
            });

            $('#resetBtn').on('click', function() {
                $('#filterKantor').val('').trigger('change');
                $('#startDate').val('');
                $('#endDate').val('');
                table.draw();
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                });
            @endif

        });
    </script>
@endpush
{{-- @push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#kantor').select2({
                placeholder: 'Choose Stores',
                allowClear: true,
                width: '100%'
            });
        });

    </script>
    <script>
        jQuery(document).ready(function($) {

            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('attendance.attendance') }}',
                    data: function(d) {
                        d.kantor = $('#filterKantor').val();
                     d.start_date = $('#startDate').val(); 
    d.end_date = $('#endDate').val(); 
                    }
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
                columns: [{
                        data: null,
                        name: 'id',
                        className: 'text-center align-middle',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        className: 'text-center'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name',
                        className: 'text-center'
                    },
                    {
                        data: 'department_name',
                        name: 'department_name',
                        className: 'text-center'
                    },
                    {
                        data: 'kantor',
                        name: 'kantor',
                        className: 'text-center'
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk',
                        name: 'jam_masuk',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar',
                        name: 'jam_keluar',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk2',
                        name: 'jam_masuk2',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar2',
                        name: 'jam_keluar2',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk3',
                        name: 'jam_masuk3',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar3',
                        name: 'jam_keluar3',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk4',
                        name: 'jam_masuk4',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar4',
                        name: 'jam_keluar4',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk5',
                        name: 'jam_masuk5',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar5',
                        name: 'jam_keluar5',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk6',
                        name: 'jam_masuk6',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar6',
                        name: 'jam_keluar6',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk7',
                        name: 'jam_masuk7',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar7',
                        name: 'jam_keluar7',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk8',
                        name: 'jam_masuk8',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar8',
                        name: 'jam_keluar8',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk9',
                        name: 'jam_masuk9',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar9',
                        name: 'jam_keluar9',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_masuk10',
                        name: 'jam_masuk10',
                        className: 'text-center'
                    },
                    {
                        data: 'jam_keluar10',
                        name: 'jam_keluar10',
                        className: 'text-center'
                    },
                ],
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-control');
                }
            });

               $('#filterBtn').on('click', function () {
        table.draw();
    });

    $('#resetBtn').on('click', function () {
        $('#filterKantor').val('').trigger('change');
        $('#startDate').val('');
        $('#endDate').val('');
        table.draw();
    });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                });
            @endif

        });
    </script>
@endpush --}}
