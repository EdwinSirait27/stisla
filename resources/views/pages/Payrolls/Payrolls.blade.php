@extends('layouts.app')
@section('title', 'Payrolls')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
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
                                {{-- Filter --}}
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <label for="filter_month_year" class="form-label">Filter Month - Year</label>
                                        <input type="text" id="filter_month_year" class="form-control"
                                            placeholder="Choose Month - Year">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button id="btn_filter" class="btn btn-primary w-100">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button id="btn_reset" class="btn btn-secondary w-100">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                    </div>
                                </div>

                                {{-- Table --}}
                                <form id="bulk-delete-form" method="POST" action="{{ route('payrolls.bulkDelete') }}">
                                    @csrf
                                    @method('DELETE')

                                
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered" id="users-table">
                                            <thead class="thead-light">
                                                <tr>
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
                                                    {{-- <th class="text-center">Mesh</th> --}}
                                                    <th class="text-center">Month</th>
                                                    <th class="text-center">Period</th>
                                                    {{-- <th class="text-center">Action</th> --}}
                                                    <th>
                                                        <button type="button" id="select-all"
                                                            class="btn btn-primary btn-sm">
                                                            Select All
                                                        </button>
                                                    </th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </form>
                                    <button type="submit" class="btn btn-danger mb-2"
                                        onclick="return confirm('Yakin ingin menghapus data yang dipilih?')">
                                        Hapus Terpilih
                                    </button>

                            <form action="{{ route('payrolls.generateAll') }}" method="POST"
                                onsubmit="return confirm('Generate semua PDF?')">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-pdf"></i> Generate All Payroll PDF
                                </button>
                            </form>

                            <div class="action-buttons">
                                <button type="button" onclick="window.location='{{ route('pages.Importpayroll') }}'"
                                    class="btn btn-dark btn-sm ml-2">
                                    <i class="fas fa-users"></i> Import Payroll
                                </button>
                                <!-- New button added here -->


                            </div>
                        </div> {{-- end card-body --}}

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
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script>
        flatpickr("#filter_month_year", {
            dateFormat: "Y-m", // format output: 2025-05
            plugins: [
                new monthSelectPlugin({
                    shorthand: true, // gunakan Jan, Feb, dll
                    dateFormat: "Y-m",
                    altFormat: "F Y"
                })
            ]
        });
    </script>
    <script>
        $(document).ready(function() {
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('payrolls.payrolls') }}',
                    data: function(d) {
                        d.month_year = $('#filter_month_year').val();
                    }
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
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'daily_allowance',
                        name: 'daily_allowance',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'house_allowance',
                        name: 'house_allowance',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'meal_allowance',
                        name: 'meal_allowance',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'transport_allowance',
                        name: 'transport_allowance',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'bonus',
                        name: 'bonus',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'overtime',
                        name: 'overtime',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'late_fine',
                        name: 'late_fine',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'punishment',
                        name: 'punishment',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },


                    {
                        data: 'bpjs_kes',
                        name: 'bpjs_kes',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'bpjs_ket',
                        name: 'bpjs_ket',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    // {
                    //     data: 'mesh',
                    //     name: 'mesh',
                    //     className: 'text-center',
                    //     render: function(data) {
                    //         return data ? parseInt(data).toLocaleString('id-ID') : '-';
                    //     }
                    // },


                    {
                        data: 'tax',
                        name: 'tax',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'debt',
                        name: 'debt',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'deductions',
                        name: 'deductions',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'salary',
                        name: 'salary',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
                        }
                    },
                    {
                        data: 'take_home',
                        name: 'take_home',
                        className: 'text-center',
                        render: function(data) {
                            return data ? parseInt(data).toLocaleString('id-ID') : '-';
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
                    },
                    // ,
                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false,
                    //     className: 'text-center'
                    // }
                    //                     ,
                    //                     {
                    //                                 data: 'id',
                    //                                 name: 'checkbox',
                    //                                 orderable: false,
                    //                                 searchable: false,
                    //                                 className: 'text-center',
                    //                                 render: function(data, type, row) {
                    //     return `<input type="checkbox" class="user-checkbox" name="payroll_ids[]" value="${row.id}">`;
                    // }

                    //                             }
                    {
                        data: 'checkbox',
                        orderable: false,
                        searchable: false
                    }


                ],
                order: [
                    [9, 'desc']
                ], // urutkan berdasarkan Month Year terbaru
                dom: 'lBfrtip', // Menambahkan 'l' agar Show Entries muncul
                buttons: [{
                        extend: 'copy',
                        text: 'Copy to Clipboard',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        className: 'btn btn-success'
                    }
                ],
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
    </script>
    <script>
        $('#select-all').on('click', function() {
            let isChecked = $(this).data('checked') || false;
            $('.user-checkbox').prop('checked', !isChecked);
            $(this).data('checked', !isChecked);
            $(this).text(!isChecked ? 'Deselect All' : 'Select All');
        });
          $('#select-all').on('click', function(){
        $('input.payroll-checkbox').prop('checked', this.checked);
    });
    </script>
@endpush
