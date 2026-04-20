{{-- @extends('layouts.app')
@section('title', 'Detail Employees')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">
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

    .DTFC_LeftBodyLiner,
    .DTFC_LeftHeadWrapper,
    .DTFC_RightBodyLiner,
    .DTFC_RightHeadWrapper {
        background-color: #fff !important;
        z-index: 999 !important;
    }

    table.dataTable thead th,
    table.dataTable thead td {
        background-color: #f8f9fa !important;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .dataTables_scrollBody {
        overflow-x: auto !important;
    }

    table.dataTable,
    table.dataTable th,
    table.dataTable td {
        border-color: #dee2e6 !important;
    }

    .DTFC_LeftBodyLiner {
        border-right: none !important;
    }
</style>
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Employee Details Table</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i> List Employee Details</h6>
                            </div>
                            <div class="col-md-2">
                                <label for="filter-store" class="form-label">Filter</label>
                                <select id="filter-store" class="form-select select2">
                                    <option value="">All</option>
                                    @foreach ($storeList as $name)
                                        <option value="{{ $name }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div id="filter-status">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="status-all" value=""
                                            checked>
                                    </div>
                                    @foreach ($statusList as $status)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="status[]"
                                                id="status-{{ $loop->index }}" value="{{ $status }}">
                                            <label class="form-check-label"
                                                for="status-{{ $loop->index }}">{{ $status }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="users-table" class="table table-bordered table-striped table-hover"
                                        style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">Action</th>
                                                <th class="text-center">Employee Name</th>
                                                <th class="text-center">Nomor Induk</th>
                                                <th class="text-center">Pin Finger</th>
                                                <th class="text-center">NIK</th>
                                                <th class="text-center">Religion</th>
                                                <th class="text-center">Gender</th>
                                                <th class="text-center">Date of Birth</th>
                                                <th class="text-center">Place of Birth</th>
                                                <th class="text-center">Mother's Name</th>
                                                <th class="text-center">Current Address</th>
                                                <th class="text-center">ID Card Address</th>
                                                <th class="text-center">Last Education</th>
                                                <th class="text-center">Institution</th>
                                                <th class="text-center">Marriage</th>
                                                <th class="text-center">Child</th>
                                                <th class="text-center">Emergency Contact Name</th>
                                                <th class="text-center">Email</th>
                                                <th class="text-center">Company Email</th>
                                                <th class="text-center">Phone Number</th>
                                                <th class="text-center">BPJS Kesehatan</th>
                                                <th class="text-center">BPJS Ketenagakerjaan</th>
                                                <th class="text-center">NPWP</th>
                                                <th class="text-center">Bank Account</th>
                                                <th class="text-center">Bank Account Number</th>
                                                <th class="text-center">Company</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Location</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Grd Name</th>
                                                <th class="text-center">Grouping</th>
                                                <th class="text-center">Status Employee</th>
                                                <th class="text-center">Join Date</th>
                                                <th class="text-center">LOS</th>

                                                <th class="text-center">Account Creation</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Notes</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="action-buttons">
                                    <button type="button" onclick="window.location='{{ route('pages.Employee') }}'"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-plus-circle"></i> Back to Employee
                                    </button>
                                </div>
                                <div class="alert alert-secondary mt-4" role="alert">
                                    <span class="text-dark">
                                        <strong>Important Note:</strong> <br>
                                        - <i class="fas fa-user"></i> - Import the employee's data first then import the
                                        users aight. <br>
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
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            var table = $('#users-table').DataTable({
                dom: '<"top row mb-2"<"col-sm-12 col-md-6 d-flex align-items-center"lB><"col-sm-12 col-md-6"f>>rt<"bottom"ip>',
                    buttons: [{
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel"></i> Excel',
                            className: 'btn btn-sm btn-success',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19,
                                    20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35
                                ],
                                format: {
                                    body: function(data, row, column, node) {
                                        if (typeof data === 'string' && /^\d{16,}$/.test(data)) {
                                            return '\u200C' + data;
                                        }
                                        return data;
                                    }
                                }
                            }
                        },
                        {
                            extend: 'csv',
                            text: '<i class="fas fa-file-csv"></i> CSV',
                            className: 'btn btn-sm btn-primary',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19,
                                    20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35
                                ],
                                format: {
                                    body: function(data, row, column, node) {
                                        if (typeof data === 'string' && /^\d{16,}$/.test(data)) {
                                            return '\u200C' + data;
                                        }
                                        return data;
                                    }
                                }
                            }
                        }
                    ],
                processing: true,
                serverSide: true,
                scrollY: "700px",
                scrollX: true,
                autoWidth: false,
                fixedColumns: {
                    leftColumns: 3 },
                ajax: {
                    url: '{{ route('employeesall.employeesall') }}',
                    type: 'POST',
                    data: function(d) {
                        d._token = '{{ csrf_token() }}';
                        d.name = $('#filter-store').val();
                        d.status = [];
                        $('#filter-status input[type="checkbox"]:checked').each(function() {
                            d.status.push($(this).val());
                        });
                    }
                },
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                language: {
                    lengthMenu: "Show _MENU_ entries",
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)"
                },
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        className: 'text-center'
                    },
                    {
                        data: 'employee_pengenal',
                        name: 'employee_pengenal',
                        className: 'text-center'
                    },
                    {
                        data: 'pin',
                        name: 'pin',
                        className: 'text-center'
                    },
                    {
                        data: 'nik',
                        name: 'nik',
                        className: 'text-center'
                    },
                    {
                        data: 'religion',
                        name: 'religion',
                        className: 'text-center'
                    },
                    {
                        data: 'gender',
                        name: 'gender',
                        className: 'text-center'
                    },
                    {
                        data: 'date_of_birth',
                        name: 'date_of_birth',
                        className: 'text-center'
                    },
                    {
                        data: 'place_of_birth',
                        name: 'place_of_birth',
                        className: 'text-center'
                    },
                    {
                        data: 'biological_mother_name',
                        name: 'biological_mother_name',
                        className: 'text-center'
                    },
                    {
                        data: 'current_address',
                        name: 'current_address',
                        className: 'text-center'
                    },
                    {
                        data: 'id_card_address',
                        name: 'id_card_address',
                        className: 'text-center'
                    },
                    {
                        data: 'last_education',
                        name: 'last_education',
                        className: 'text-center'
                    },
                    {
                        data: 'institution',
                        name: 'institution',
                        className: 'text-center'
                    },
                    {
                        data: 'marriage',
                        name: 'marriage',
                        className: 'text-center'
                    },
                    {
                        data: 'child',
                        name: 'child',
                        className: 'text-center'
                    },
                    {
                        data: 'emergency_contact_name',
                        name: 'emergency_contact_name',
                        className: 'text-center'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        className: 'text-center'
                    },
                    {
                        data: 'company_email',
                        name: 'company_email',
                        className: 'text-center'
                    },
                    {
                        data: 'telp_number',
                        name: 'telp_number',
                        className: 'text-center'
                    },
                    {
                        data: 'bpjs_kes',
                        name: 'bpjs_kes',
                        className: 'text-center'
                    },
                    {
                        data: 'bpjs_ket',
                        name: 'bpjs_ket',
                        className: 'text-center'
                    },
                    {
                        data: 'npwp',
                        name: 'npwp',
                        className: 'text-center'
                    },
                    {
                        data: 'bank_name',
                        name: 'bank_name',
                        className: 'text-center'
                    },
                    {
                        data: 'bank_account_number',
                        name: 'bank_account_number',
                        className: 'text-center'
                    },
                    {
                        data: 'name_company',
                        name: 'name_company',
                        className: 'text-center'
                    },
                    {
                        data: 'department_name',
                        name: 'department_name',
                        className: 'text-center'
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'text-center'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name',
                        className: 'text-center'
                    },
                    {
                        data: 'grading_name',
                        name: 'grading_name',
                        className: 'text-center'
                    },
                    {
                        data: 'group_name',
                        name: 'group_name',
                        className: 'text-center'
                    },

                    {
                        data: 'status_employee',
                        name: 'status_employee',
                        className: 'text-center'
                    },
                    {
                        data: 'join_date',
                        name: 'join_date',
                        className: 'text-center'
                    },
                    {
                        data: 'length_of_service',
                        className: 'text-center'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center'
                    },
                    {
                        data: 'notes',
                        name: 'notes',
                        className: 'text-center'
                    }
                ],
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    $('.dataTables_length select').addClass('form-select form-select-sm');
                }
            });
            $('#filter-store').on('change', function() {
                table.ajax.reload();
            });
            $('#filter-status input[type="checkbox"]').on('change', function() {
                table.ajax.reload();
            });
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    timer: 3000
                });
            @endif
        });
    </script>
@endpush --}}
@extends('layouts.app')
@section('title', 'Detail Employees')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">
@endpush

<style>
    /* ─── Card ─── */
    .card {
        border: none;
        box-shadow: 0 0.15rem 0.5rem rgba(0,0,0,0.07);
        border-radius: 0.75rem;
        overflow: hidden;
        background-color: #fff;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-header h6 {
        margin: 0;
        font-weight: 600;
        color: #2d3748;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .card-header h6 i {
        color: #5e72e4;
    }

    /* ─── Filter Bar ─── */
    .filter-bar {
        padding: 0.9rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        display: flex;
        align-items: flex-end;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .filter-label {
        font-size: 0.7rem;
        font-weight: 600;
        /* text-transform: uppercase; */
        letter-spacing: 0.4px;
        color: #718096;
    }

    .filter-group .form-select,
    .filter-group .form-control {
        height: 36px;
        font-size: 0.82rem;
        border-color: #e2e8f0;
        border-radius: 0.4rem;
    }

    /* ─── Status Badges ─── */
    .status-checkboxes {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.1rem;
    }

    .status-checkboxes .form-check {
        margin: 0;
        padding: 0;
    }

    .status-checkboxes .form-check-input {
        display: none;
    }

    .status-checkboxes .form-check-label {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        border: 1.5px solid transparent;
        transition: all 0.2s ease;
        background: #edf2f7;
        color: #4a5568;
        user-select: none;
    }

    .status-checkboxes .form-check-input:checked + .form-check-label {
        border-color: currentColor;
    }

    .status-checkboxes .check-all .form-check-label { color: #5e72e4; }
    .status-checkboxes .check-all .form-check-input:checked + .form-check-label { background: #ebedfd; }

    /* ─── Toolbar (above table) ─── */
    .table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 1rem 1.5rem 0;
    }

    /* ─── Table ─── */
    .card-body {
        padding: 0.75rem 1.5rem 1.25rem;
    }

    .table-responsive {
        overflow-x: auto;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        margin-top: 0.75rem;
    }

    table.dataTable thead th {
        background-color: #f8fafc !important;
        color: #718096;
        font-weight: 600;
        /* text-transform: uppercase; */
        font-size: 0.68rem;
        letter-spacing: 0.4px;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 0.75rem;
        white-space: nowrap;
    }

    table.dataTable tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        font-size: 0.82rem;
        color: #4a5568;
        border-bottom: 1px solid #f1f5f9 !important;
        white-space: nowrap;
    }

    table.dataTable tbody tr:hover td {
        background: #f8fafc;
    }

    /* ─── Card Footer ─── */
    .card-footer-custom {
        padding: 0.9rem 1.5rem;
        border-top: 1px solid rgba(0,0,0,0.06);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .note-inline {
        font-size: 0.78rem;
        color: #718096;
        border-left: 3px solid #5e72e4;
        padding-left: 0.75rem;
        line-height: 1.5;
    }

    /* ─── Fixed Columns ─── */
    .DTFC_LeftBodyLiner,
    .DTFC_LeftHeadWrapper {
        background-color: #fff !important;
        z-index: 999 !important;
    }

    .dataTables_scrollBody {
        overflow-x: auto !important;
    }

    /* ─── Select2 ─── */
    .select2-container .select2-selection--single {
        height: 36px;
        border-color: #e2e8f0;
        border-radius: 0.4rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 34px;
        font-size: 0.82rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px;
    }

    @media (max-width: 768px) {
        .filter-bar { gap: 1rem; }
        .card-header,
        .card-body,
        .filter-bar,
        .card-footer-custom { padding-left: 1rem; padding-right: 1rem; }
    }
</style>

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Employee Details Table</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">

                        {{-- ─── Card Header ─── --}}
                        <div class="card-header">
                            <h6>
                                <i class="fas fa-user-shield"></i>
                                List Employee Details
                            </h6>
                            <span class="text-muted" style="font-size: 0.78rem;">
                                <i class="fas fa-info-circle me-1"></i>
                                Import employee data first before importing users
                            </span>
                        </div>

                        {{-- ─── Filter Bar ─── --}}
                        <div class="filter-bar">
                            <div class="filter-group">
                                <span class="filter-label">Location / Store</span>
                                <select id="filter-store" class="form-select select2" style="min-width: 200px;">
                                    <option value="">All Locations</option>
                                    @foreach ($storeList as $name)
                                        <option value="{{ $name }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="filter-group">
                                <span class="filter-label">Employment Status</span>
                                <div class="status-checkboxes" id="filter-status">
                                    <div class="form-check check-all">
                                        <input class="form-check-input" type="checkbox" id="status-all" value="" checked>
                                        <label class="form-check-label" for="status-all">
                                            <i class="fas fa-layer-group" style="font-size:10px;"></i> All
                                        </label>
                                    </div>
                                    @foreach ($statusList as $status)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="status[]"
                                                   id="status-{{ $loop->index }}"
                                                   value="{{ $status }}">
                                            <label class="form-check-label" for="status-{{ $loop->index }}">
                                                {{ $status }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- ─── Table ─── --}}
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="users-table"
                                       class="table table-bordered table-hover"
                                       style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Action</th>
                                            <th class="text-center">Employee Name</th>
                                            <th class="text-center">Nomor Induk</th>
                                            <th class="text-center">Pin Finger</th>
                                            <th class="text-center">NIK</th>
                                            <th class="text-center">Religion</th>
                                            <th class="text-center">Gender</th>
                                            <th class="text-center">Date of Birth</th>
                                            <th class="text-center">Place of Birth</th>
                                            <th class="text-center">Mother's Name</th>
                                            <th class="text-center">Current Address</th>
                                            <th class="text-center">ID Card Address</th>
                                            <th class="text-center">Last Education</th>
                                            <th class="text-center">Institution</th>
                                            <th class="text-center">Marriage</th>
                                            <th class="text-center">Child</th>
                                            <th class="text-center">Emergency Contact</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">Company Email</th>
                                            <th class="text-center">Phone</th>
                                            <th class="text-center">BPJS Kesehatan</th>
                                            <th class="text-center">BPJS Ketenagakerjaan</th>
                                            <th class="text-center">NPWP</th>
                                            <th class="text-center">Bank</th>
                                            <th class="text-center">Account No.</th>
                                            <th class="text-center">Company</th>
                                            <th class="text-center">Department</th>
                                            <th class="text-center">Location</th>
                                            <th class="text-center">Position</th>
                                            <th class="text-center">Grade</th>
                                            <th class="text-center">Grouping</th>
                                            <th class="text-center">Status Employee</th>
                                            <th class="text-center">Join Date</th>
                                            <th class="text-center">LOS</th>
                                            <th class="text-center">Account Created</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Notes</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                        {{-- ─── Card Footer ─── --}}
                        <div class="card-footer-custom">
                            <button type="button"
                                    onclick="window.location='{{ route('pages.Employee') }}'"
                                    class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Employee
                            </button>
                            <div class="note-inline">
                                <strong>Note:</strong> Import employee data first, then import users.
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
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

<script>
$(document).ready(function () {
    $('.select2').select2();

    var table = $('#users-table').DataTable({
        dom: '<"d-flex align-items-center justify-content-between mb-2"lB>rt<"d-flex align-items-center justify-content-between mt-2"ip>',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel me-1"></i> Excel',
                className: 'btn btn-sm btn-success',
                exportOptions: {
                    columns: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35],
                    format: {
                        body: function (data, row, column, node) {
                            if (typeof data === 'string' && /^\d{16,}$/.test(data)) {
                                return '\u200C' + data;
                            }
                            return data;
                        }
                    }
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv me-1"></i> CSV',
                className: 'btn btn-sm btn-primary',
                exportOptions: {
                    columns: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35],
                    format: {
                        body: function (data, row, column, node) {
                            if (typeof data === 'string' && /^\d{16,}$/.test(data)) {
                                return '\u200C' + data;
                            }
                            return data;
                        }
                    }
                }
            }
        ],
        processing: true,
        serverSide: true,
        scrollY: '600px',
        scrollX: true,
        autoWidth: false,
        fixedColumns: { leftColumns: 3 },
        ajax: {
            url: '{{ route('employeesall.employeesall') }}',
            type: 'POST',
            data: function (d) {
                d._token = '{{ csrf_token() }}';
                d.name = $('#filter-store').val();
                d.status = [];
                $('#filter-status input[type="checkbox"]:checked').each(function () {
                    d.status.push($(this).val());
                });
            }
        },
        responsive: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        pageLength: 10,
        language: {
            lengthMenu: 'Show _MENU_ entries',
            search: '_INPUT_',
            searchPlaceholder: 'Search...',
            paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Prev' },
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'No entries found',
            infoFiltered: '(filtered from _MAX_ total entries)',
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...'
        },
        columns: [
            { data: 'action',                name: 'action',                orderable: false, searchable: false, className: 'text-center' },
            { data: 'employee_name',          name: 'employee_name',          className: 'text-center' },
            { data: 'employee_pengenal',      name: 'employee_pengenal',      className: 'text-center' },
            { data: 'pin',                    name: 'pin',                    className: 'text-center' },
            { data: 'nik',                    name: 'nik',                    className: 'text-center' },
            { data: 'religion',               name: 'religion',               className: 'text-center' },
            { data: 'gender',                 name: 'gender',                 className: 'text-center' },
            { data: 'date_of_birth',          name: 'date_of_birth',          className: 'text-center' },
            { data: 'place_of_birth',         name: 'place_of_birth',         className: 'text-center' },
            { data: 'biological_mother_name', name: 'biological_mother_name', className: 'text-center' },
            { data: 'current_address',        name: 'current_address',        className: 'text-center' },
            { data: 'id_card_address',        name: 'id_card_address',        className: 'text-center' },
            { data: 'last_education',         name: 'last_education',         className: 'text-center' },
            { data: 'institution',            name: 'institution',            className: 'text-center' },
            { data: 'marriage',               name: 'marriage',               className: 'text-center' },
            { data: 'child',                  name: 'child',                  className: 'text-center' },
            { data: 'emergency_contact_name', name: 'emergency_contact_name', className: 'text-center' },
            { data: 'email',                  name: 'email',                  className: 'text-center' },
            { data: 'company_email',          name: 'company_email',          className: 'text-center' },
            { data: 'telp_number',            name: 'telp_number',            className: 'text-center' },
            { data: 'bpjs_kes',               name: 'bpjs_kes',               className: 'text-center' },
            { data: 'bpjs_ket',               name: 'bpjs_ket',               className: 'text-center' },
            { data: 'npwp',                   name: 'npwp',                   className: 'text-center' },
            { data: 'bank_name',              name: 'bank_name',              className: 'text-center' },
            { data: 'bank_account_number',    name: 'bank_account_number',    className: 'text-center' },
            { data: 'name_company',           name: 'name_company',           className: 'text-center' },
            { data: 'department_name',        name: 'department_name',        className: 'text-center' },
            { data: 'name',                   name: 'name',                   className: 'text-center' },
            { data: 'position_name',          name: 'position_name',          className: 'text-center' },
            { data: 'grading_name',           name: 'grading_name',           className: 'text-center' },
            { data: 'group_name',             name: 'group_name',             className: 'text-center' },
            { data: 'status_employee',        name: 'status_employee',        className: 'text-center' },
            { data: 'join_date',              name: 'join_date',              className: 'text-center' },
            { data: 'length_of_service',                                      className: 'text-center' },
            { data: 'created_at',             name: 'created_at',             className: 'text-center' },
            { data: 'status',                 name: 'status',                 className: 'text-center' },
            { data: 'notes',                  name: 'notes',                  className: 'text-center' }
        ],
        initComplete: function () {
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_length select').addClass('form-select form-select-sm');
        }
    });

    $('#filter-store').on('change', function () { table.ajax.reload(); });
    $('#filter-status input[type="checkbox"]').on('change', function () { table.ajax.reload(); });
    @if (session('success'))
        Swal.fire({ icon: 'success', title: 'Success', text: '{{ session('success') }}', timer: 3000 });
    @endif
});
</script>
@endpush