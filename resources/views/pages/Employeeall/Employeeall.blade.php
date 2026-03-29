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
                                {{-- <label class="form-label">Filter</label> --}}
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
                                    {{-- <table class="table table-hover" id="users-table"> --}}
                                    <table id="users-table" class="table table-bordered table-striped table-hover"
                                        style="width:100%">
                                        {{-- <thead> --}}
                                        <thead class="table-light">
                                            <tr>
                                                {{-- <th class="text-center">No.</th> --}}
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
@endpush