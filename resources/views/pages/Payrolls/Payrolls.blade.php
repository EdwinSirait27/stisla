@extends('layouts.app')
@section('title', 'Payrolls')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush
<style>
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

    .text-center {
        text-align: center;
    }

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

    .section-header h1 {
        font-weight: 600;
        color: #2d3748;
        font-size: 1.5rem;
    }

    .table-responsive {
        -webkit-overflow-scrolling: touch;
    }

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
    .d-flex.gap-3 > * {
    margin-right: 0.75rem !important;
}

.d-flex.gap-3 > *:last-child {
    margin-right: 1 !important;
}
.payroll-buttons form,
.payroll-buttons a {
    display: flex;
    align-items: center;
}

.payroll-buttons .btn {
    min-width: 100px;
    height: 52px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
}

</style>
{{-- @section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Payrolls</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-user-shield"></i> List Payrolls</h4>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4 align-items-end">
                                    <div class="col-md-3">
                                        <label for="filter_month_year" class="form-label">Filter Month - Year</label>
                                        <input type="text" id="filter_month_year" class="form-control"
                                            placeholder="Choose Month - Year">
                                    </div>
                                    <div class="col-md-auto">
                                        <button id="btn_filter" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                    <div class="col-md-auto">
                                        <button id="btn_reset" class="btn btn-secondary">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                    </div>
                                </div>
                                <form id="bulk-delete-form" method="POST" action="{{ route('payrolls.bulkDelete') }}">
                                    @csrf
                                    @method('DELETE')
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered" id="users-table">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="text-center">
                                                        <button type="button" id="select-all"
                                                            class="btn btn-primary btn-sm">
                                                            Select All
                                                        </button>
                                                    </th>
                                                    <th class="text-center">No.</th>
                                                    <th class="text-center">Employee Name</th>
                                                    <th class="text-center">Attendance</th>
                                                    <th class="text-center">Daily Allowance</th>
                                                    <th class="text-center">House Allowance</th>
                                                    <th class="text-center">Meal Allowance</th>
                                                    <th class="text-center">Transport Allowance</th>
                                                    <th class="text-center">Bonus</th>
                                                    <th class="text-center">Overtime</th>
                                                    <th class="text-center">Late Fine</th>
                                                    <th class="text-center">Punishment</th>
                                                    <th class="text-center">BPJS Kesehatan</th>
                                                    <th class="text-center">BPJS Ketenagakerjaan</th>
                                                    <th class="text-center">Tax</th>
                                                    <th class="text-center">Debt</th>
                                                    <th class="text-center">Total Outcome</th>
                                                    <th class="text-center">Total Income</th>
                                                    <th class="text-center">Take Home</th>
                                                    <th class="text-center">Month</th>
                                                    <th class="text-center">Period</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 align-items-stretch">
                                        <form id="bulk-delete-form" action="{{ route('payrolls.bulkDelete') }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="payroll_ids" id="bulk-delete-hidden">
                                            <button type="submit" class="btn btn-danger h-100 d-flex align-items-center">
                                                <i class="fas fa-trash me-1"></i> Delete Payroll
                                            </button>
                                        </form>
                                        <form action="{{ route('Payrolls.generateAll') }}" method="POST"
                                            onsubmit="return confirm('Generate Payrolls?')">
                                            @csrf
                                            <button type="submit" class="btn btn-primary h-100 d-flex align-items-center">
                                                <i class="fas fa-book me-1"></i> Generate All
                                            </button>
                                        </form>
                                        <a href="{{ route('pages.Importpayroll') }}"
                                            class="btn btn-dark h-100 d-flex align-items-center">
                                            <i class="fas fa-users me-1"></i> Import Payrolls
                                        </a>
                                    </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </section>
    </div>
@endsection --}}
@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Payrolls</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-user-shield"></i> List Payrolls</h4>
                        </div>

                        <div class="card-body">
                            <div class="row mb-4 align-items-end">
                                <div class="col-md-3">
                                    <label for="filter_month_year" class="form-label">Filter Month - Year</label>
                                    <input type="text" id="filter_month_year" class="form-control"
                                        placeholder="Choose Month - Year">
                                </div>
                                <div class="col-md-auto">
                                    <button id="btn_filter" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-auto">
                                    <button id="btn_reset" class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </div>
                            </div>

                            {{-- Form utama untuk bulk delete --}}
                            <form id="bulk-delete-form" method="POST" action="{{ route('payrolls.bulkDelete') }}">
                                @csrf
                                @method('DELETE')

                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered" id="users-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">
                                                    <button type="button" id="select-all" class="btn btn-primary btn-sm">
                                                        Select All
                                                    </button>
                                                </th>
                                                <th class="text-center">No.</th>
                                                <th class="text-center">Employee Name</th>
                                                <th class="text-center">NIP</th>
                                                <th class="text-center">Attendance</th>
                                                <th class="text-center">Basic Salary</th>
                                                <th class="text-center">Daily Allowance</th>
                                                <th class="text-center">House Allowance</th>
                                                <th class="text-center">Meal Allowance</th>
                                                <th class="text-center">Transport Allowance</th>
                                                <th class="text-center">Allowance</th>
                                                <th class="text-center">Reamburse</th>
                                                <th class="text-center">Bonus</th>
                                                <th class="text-center">Overtime</th>
                                                <th class="text-center">Late Fine</th>
                                                <th class="text-center">Punishment</th>
                                                <th class="text-center">BPJS Kesehatan</th>
                                                <th class="text-center">BPJS Ketenagakerjaan</th>
                                                <th class="text-center">Tax</th>
                                                <th class="text-center">Debt</th>
                                                <th class="text-center">Total Outcome</th>
                                                <th class="text-center">Total Income</th>
                                                <th class="text-center">Take Home</th>
                                                <th class="text-center">Month</th>
                                                <th class="text-center">Period</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>

                                {{-- <div class="d-flex flex-wrap gap-2 align-items-stretch mt-3">
                                    <input type="hidden" name="payroll_ids" id="bulk-delete-hidden">
                                    <button type="submit" class="btn btn-danger h-100 d-flex align-items-center">
                                        <i class="fas fa-trash me-1"></i> Delete Payroll
                                    </button>
                                </div>
                            </form>

                            <div class="d-flex flex-wrap gap-2 align-items-stretch mt-3">
                                <form action="{{ route('Payrolls.generateAll') }}" method="POST"
                                    onsubmit="return confirm('Generate Payrolls?')">
                                    @csrf
                                    <button type="submit" class="btn btn-primary h-100 d-flex align-items-center">
                                        <i class="fas fa-book me-1"></i> Generate All
                                    </button>
                                </form>

                                <a href="{{ route('pages.Importpayroll') }}"
                                    class="btn btn-dark h-100 d-flex align-items-center">
                                    <i class="fas fa-users me-1"></i> Import Payrolls
                                </a>
                            </div> --}}
    
{{-- <div class="d-flex flex-wrap gap-2 align-items-stretch mt-3">
        <button type="submit" class="btn btn-danger h-100 d-flex align-items-center">
            <i class="fas fa-trash me-1"></i> Delete Payroll
        </button>
    </form>

    <form action="{{ route('Payrolls.generateAll') }}" method="POST"
        onsubmit="return confirm('Generate Payrolls?')">
        @csrf
        <button type="submit" class="btn btn-primary h-100 d-flex align-items-center">
            <i class="fas fa-book me-1"></i> Generate All
        </button>
    </form>

    <a href="{{ route('pages.Importpayroll') }}"
        class="btn btn-dark h-100 d-flex align-items-center">
        <i class="fas fa-users me-1"></i> Import Payrolls
    </a>
</div> --}}
{{-- <div class="d-flex flex-wrap align-items-stretch gap-3 mt-3">
        <button type="submit" class="btn btn-danger d-flex align-items-center">
            <i class="fas fa-trash me-1"></i> Delete Payroll
        </button>
    </form>

    <form action="{{ route('Payrolls.generateAll') }}" method="POST" onsubmit="return confirm('Generate Payrolls?')">
        @csrf
        <button type="submit" class="btn btn-primary d-flex align-items-center">
            <i class="fas fa-book me-1"></i> Generate
        </button>
    </form>

    <a href="{{ route('pages.Importpayroll') }}" class="btn btn-dark d-flex align-items-center">
        <i class="fas fa-users me-1"></i> Import Payrolls
    </a>
</div> --}}
<form id="bulk-delete-form" action="{{ route('payrolls.bulkDelete') }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="payroll_ids" id="bulk-delete-hidden">
<div class="d-flex flex-wrap align-items-stretch mt-3 gap-3 payroll-buttons">
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash me-1"></i> Delete Payroll
        </button>
    </form>

    {{-- Generate All --}}
    <form action="{{ route('Payrolls.generateAll') }}" method="POST" onsubmit="return confirm('Generate Payrolls?')">
        @csrf
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-book me-1"></i> Generate All
        </button>
    </form>

    {{-- Import Payrolls --}}
    <a href="{{ route('pages.Importpayroll') }}" class="btn btn-dark">
        <i class="fas fa-users me-1"></i> Import Payrolls
    </a>
</div>



                        </div> {{-- end card-body --}}
                    </div> {{-- end card --}}
                </div> {{-- end col-12 --}}
            </div> {{-- end row --}}
        </div> {{-- end section-body --}}
    </section>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script>
        document.getElementById('bulk-delete-form').addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('input.payroll-checkbox:checked');
            if (checked.length === 0) {
                e.preventDefault();
                Swal.fire("Failed", "Select data first.", "error");
                return;
            }
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure the selected data will be deleted?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Abort!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const ids = Array.from(checked).map(cb => cb.value);
                    document.getElementById('bulk-delete-hidden').value = ids.join(',');
                    e.target.submit();
                }
            });
        });
        flatpickr("#filter_month_year", {
            dateFormat: "Y-m",
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",
                    altFormat: "F Y"
                })
            ]
        });
        $(document).ready(function() {
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                scrollY: "700px",
        scrollX: true,
        autoWidth: false,
                ajax: {
                    url: '{{ route('payrolls.payrolls') }}',
                    data: function(d) {
                        d.month_year = $('#filter_month_year').val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle'
                    },
                    {
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
                        className: 'text-center',
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'employee_pengenal',
                        name: 'employee_pengenal',
                        className: 'text-center',
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'attendance',
                        name: 'attendance',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return data + ' days';
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'basic_salary',
                        name: 'basic_salary',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'daily_allowance',
                        name: 'daily_allowance',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },

                    {
                        data: 'house_allowance',
                        name: 'house_allowance',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'meal_allowance',
                        name: 'meal_allowance',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'transport_allowance',
                        name: 'transport_allowance',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'allowance',
                        name: 'allowance',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'reamburse',
                        name: 'reamburse',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'bonus',
                        name: 'bonus',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'overtime',
                        name: 'overtime',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'late_fine',
                        name: 'late_fine',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'punishment',
                        name: 'punishment',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },


                    {
                        data: 'bpjs_kes',
                        name: 'bpjs_kes',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'bpjs_ket',
                        name: 'bpjs_ket',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },



                    {
                        data: 'tax',
                        name: 'tax',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'debt',
                        name: 'debt',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'deductions',
                        name: 'deductions',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'salary',
                        name: 'salary',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'take_home',
                        name: 'take_home',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return 'Rp. ' + parseInt(data).toLocaleString('id-ID');
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'month_year',
                        name: 'month_year',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                const date = new Date(data);
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                return `${year}-${month}`;
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'period',
                        name: 'period',
                        className: 'text-center',
                        render: function(data) {
                            return data ? data : '-';
                        }
                    }




                ],
                order: [
                    [9, 'desc']
                ],
                dom: 'lBfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19,
                            20
                        ]
                    }
                }],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ]
            });

            $('#btn_filter').click(function() {
                table.ajax.reload();
            });

            $('#btn_reset').click(function() {
                $('#filter_month_year').val('');
                table.ajax.reload();
            });
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
                title: 'Error!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        @endif
        $('#select-all').on('click', function() {
            let isChecked = $(this).data('checked') || false;
            $('input.payroll-checkbox').prop('checked', !isChecked);
            $(this).data('checked', !isChecked);
            $(this).text(!isChecked ? 'Deselect All' : 'Select All');
        });
    </script>
@endpush
{{-- @extends('layouts.app')
@section('title', 'Payroll Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
<link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    /* ========== Variables ========== */
    :root {
        --primary-color: #5e72e4;
        --primary-hover: #4a5bd1;
        --secondary-color: #8392ab;
        --success-color: #2dce89;
        --danger-color: #f5365c;
        --warning-color: #fb6340;
        --info-color: #11cdef;
        --dark-color: #32325d;
        --light-bg: #f8fafc;
        --border-color: rgba(0, 0, 0, 0.05);
        --text-primary: #2d3748;
        --text-secondary: #4a5568;
        --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        --shadow-md: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.12);
        --border-radius: 0.75rem;
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    /* ========== Section Header ========== */
    .section-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }

    .section-header h1 {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 1.75rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-header h1 i {
        color: var(--primary-color);
        font-size: 1.5rem;
    }

    /* ========== Card Styles ========== */
    .card {
        border: none;
        box-shadow: var(--shadow-md);
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: var(--transition);
        background-color: #fff;
        margin-bottom: 1.5rem;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #324cdd 100%);
        border: none;
        padding: 1.5rem;
    }

    .card-header h4 {
        margin: 0;
        font-weight: 600;
        color: #fff;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
        gap: 0.75rem;
    }

    .card-header h4 i {
        font-size: 1.25rem;
    }

    .card-body {
        padding: 1.75rem;
    }

    /* ========== Filter Section ========== */
    .filter-section {
        background: var(--light-bg);
        padding: 1.5rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-color);
    }

    .filter-section .form-label {
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-section .form-control {
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        padding: 0.625rem 1rem;
        transition: var(--transition);
        font-size: 0.9rem;
    }

    .filter-section .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.1);
        outline: none;
    }

    /* ========== Button Styles ========== */
    .btn {
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
        box-shadow: var(--shadow-sm);
    }

    .btn i {
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, #324cdd 100%);
        color: #fff;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-hover) 0%, #2a3cb5 100%);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-secondary {
        background-color: var(--secondary-color);
        color: #fff;
    }

    .btn-secondary:hover {
        background-color: #6c7a89;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color) 0%, #ec0c38 100%);
        color: #fff;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #ec0c38 0%, #c10030 100%);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-dark {
        background-color: var(--dark-color);
        color: #fff;
    }

    .btn-dark:hover {
        background-color: #1f1f3d;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8125rem;
    }

    /* ========== Action Buttons Container ========== */
    .action-buttons-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px solid var(--border-color);
    }

    /* ========== Table Styles ========== */
    .table-responsive {
        border-radius: var(--border-radius);
        overflow: hidden;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }

    .table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background: linear-gradient(180deg, #f8fafc 0%, #e9ecef 100%);
        color: var(--text-secondary);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-color);
        padding: 1rem 0.75rem;
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: var(--transition);
        background-color: #fff;
    }

    .table tbody tr:nth-child(even) {
        background-color: #fafbfc;
    }

    .table tbody tr:hover {
        background-color: rgba(94, 114, 228, 0.04);
        transform: scale(1.001);
        box-shadow: 0 2px 8px rgba(94, 114, 228, 0.1);
    }

    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        color: var(--text-secondary);
        font-size: 0.875rem;
        border-bottom: 1px solid var(--border-color);
        transition: var(--transition);
        white-space: nowrap;
    }

    .table tbody tr:hover td {
        color: var(--text-primary);
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* ========== Checkbox Styles ========== */
    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid var(--border-color);
        border-radius: 0.25rem;
        cursor: pointer;
        transition: var(--transition);
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.15);
        outline: none;
    }

    /* ========== Badge Styles ========== */
    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-weight: 600;
        font-size: 0.8125rem;
    }

    /* ========== Loading State ========== */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid var(--border-color);
        border-top-color: var(--primary-color);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ========== Responsive Design ========== */
    @media (max-width: 768px) {
        .section-header h1 {
            font-size: 1.5rem;
        }

        .card-body {
            padding: 1rem;
        }

        .filter-section {
            padding: 1rem;
        }

        .filter-section .row > [class*="col-"] {
            margin-bottom: 1rem;
        }

        .filter-section .row > [class*="col-"]:last-child {
            margin-bottom: 0;
        }

        .table thead th {
            font-size: 0.7rem;
            padding: 0.75rem 0.5rem;
        }

        .table tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8125rem;
        }

        .action-buttons-container {
            flex-direction: column;
        }

        .action-buttons-container .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.8125rem;
        }
    }

    /* ========== Utility Classes ========== */
    .text-center {
        text-align: center !important;
    }

    .gap-2 {
        gap: 0.5rem !important;
    }

    .mb-4 {
        margin-bottom: 1.5rem !important;
    }
</style>
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <!-- Section Header -->
        <div class="section-header">
            <h1>
                <i class="fas fa-money-check-alt"></i>
                Payroll Management
            </h1>
        </div>

        <!-- Section Body -->
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <!-- Card Header -->
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-list"></i>
                                Payroll Records
                            </h4>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body">
                            <!-- Filter Section -->
                            <div class="filter-section">
                                <div class="row align-items-end">
                                    <div class="col-md-4 col-lg-3">
                                        <label for="filter_month_year" class="form-label">
                                            <i class="fas fa-calendar-alt"></i> Period
                                        </label>
                                        <input type="text" 
                                               id="filter_month_year" 
                                               class="form-control"
                                               placeholder="Select month and year">
                                    </div>
                                    <div class="col-md-auto">
                                        <button id="btn_filter" class="btn btn-primary">
                                            <i class="fas fa-filter"></i>
                                            Apply Filter
                                        </button>
                                    </div>
                                    <div class="col-md-auto">
                                        <button id="btn_reset" class="btn btn-secondary">
                                            <i class="fas fa-undo"></i>
                                            Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-hover" id="users-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center">
                                                <button type="button" 
                                                        id="select-all"
                                                        class="btn btn-primary btn-sm">
                                                    <i class="fas fa-check-double"></i>
                                                    Select All
                                                </button>
                                            </th>
                                            <th class="text-center">No.</th>
                                            <th class="text-center">Employee Name</th>
                                            <th class="text-center">Attendance</th>
                                            <th class="text-center">Daily Allowance</th>
                                            <th class="text-center">House Allowance</th>
                                            <th class="text-center">Meal Allowance</th>
                                            <th class="text-center">Transport Allowance</th>
                                            <th class="text-center">Bonus</th>
                                            <th class="text-center">Overtime</th>
                                            <th class="text-center">Late Fine</th>
                                            <th class="text-center">Punishment</th>
                                            <th class="text-center">BPJS Health</th>
                                            <th class="text-center">BPJS Employment</th>
                                            <th class="text-center">Tax</th>
                                            <th class="text-center">Debt</th>
                                            <th class="text-center">Total Outcome</th>
                                            <th class="text-center">Total Income</th>
                                            <th class="text-center">Take Home</th>
                                            <th class="text-center">Month</th>
                                            <th class="text-center">Period</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- DataTables will populate this -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Action Buttons -->
                            <div class="action-buttons-container">
                                <form id="bulk-delete-form" 
                                      action="{{ route('payrolls.bulkDelete') }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete selected payrolls?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="payroll_ids" id="bulk-delete-hidden">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash-alt"></i>
                                        Delete Selected
                                    </button>
                                </form>

                                <form action="{{ route('Payrolls.generateAll') }}" 
                                      method="POST"
                                      onsubmit="return confirm('Generate payrolls for all employees?')">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-cogs"></i>
                                        Generate All Payrolls
                                    </button>
                                </form>

                                <a href="{{ route('pages.Importpayroll') }}" 
                                   class="btn btn-dark">
                                    <i class="fas fa-file-import"></i>
                                    Import Payrolls
                                </a>
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
<script>
    $(document).ready(function() {
        // Initialize DataTable
        const table = $('#users-table').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[1, 'asc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        // Select All functionality
        $('#select-all').on('click', function() {
            const checkboxes = $('.payroll-checkbox');
            const allChecked = checkboxes.filter(':checked').length === checkboxes.length;
            checkboxes.prop('checked', !allChecked);
            updateBulkDeleteInput();
        });

        // Update bulk delete input
        function updateBulkDeleteInput() {
            const selectedIds = $('.payroll-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            $('#bulk-delete-hidden').val(selectedIds.join(','));
        }

        // Filter functionality
        $('#btn_filter').on('click', function() {
            const filterValue = $('#filter_month_year').val();
            // Add your filter logic here
            console.log('Filtering by:', filterValue);
        });

        // Reset functionality
        $('#btn_reset').on('click', function() {
            $('#filter_month_year').val('');
            table.search('').draw();
        });
    });
</script>
@endpush --}}
