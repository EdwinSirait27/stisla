@extends('layouts.app')
@section('title', 'Payrolls')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* ─── Layout ─────────────────────────────────────────── */
        .section-header h1 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        /* ─── Card ───────────────────────────────────────────── */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
            background: #fff;
            overflow: hidden;
        }

        .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .card-header-icon {
            width: 30px;
            height: 30px;
            background: #eff6ff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .card-header h4 {
            margin: 0;
            font-size: .925rem;
            font-weight: 600;
            color: #334155;
        }

        /* ─── Filter bar ─────────────────────────────────────── */
        .filter-bar {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #fafafa;
            display: flex;
            align-items: flex-end;
            gap: .6rem;
            flex-wrap: wrap;
        }

        .filter-bar .form-group {
            display: flex;
            flex-direction: column;
            gap: .3rem;
            margin: 0;
        }

        .filter-bar .form-label {
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            margin: 0;
        }

        .filter-bar .form-control {
            height: 36px;
            font-size: .825rem;
            border-color: #e2e8f0;
            border-radius: .4rem;
            min-width: 180px;
        }

        .filter-bar .btn {
            height: 36px;
            font-size: .825rem;
            padding: 0 .9rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        /* ─── Action bar ─────────────────────────────────────── */
        .action-bar {
            padding: .65rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
            display: flex;
            align-items: center;
            gap: .6rem;
            flex-wrap: wrap;
            min-height: 50px;
        }

        .action-bar .selection-info {
            font-size: .775rem;
            color: #64748b;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: .15rem .7rem;
            display: none;
        }

        .action-bar .selection-info.visible {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
        }

        .action-bar .selection-info strong {
            color: #2563eb;
        }

        .action-bar .btn {
            height: 34px;
            font-size: .8rem;
            padding: 0 .8rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .action-bar .ms-auto {
            margin-left: auto;
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
            align-items: center;
        }

        /* ─── Table ──────────────────────────────────────────── */
        .table-responsive {
            padding: 0;
        }

        #payrolls-table {
            width: 100% !important;
            font-size: .8rem;
            border-collapse: collapse;
        }

        #payrolls-table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: .685rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: .75rem .9rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
            text-align: center;
        }

        #payrolls-table tbody td {
            padding: .75rem .9rem;
            vertical-align: middle;
            text-align: center;
            border: none;
            border-bottom: 1px solid #f8fafc;
            color: #334155;
            white-space: nowrap;
        }

        #payrolls-table tbody tr:last-child td {
            border-bottom: none;
        }

        #payrolls-table tbody tr:hover td {
            background: #f8fafc;
        }

        /* employee cell */
        .employee-cell {
            display: flex;
            align-items: center;
            gap: .6rem;
            text-align: left;
            justify-content: flex-start;
        }

        .emp-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: .65rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .emp-name {
            font-weight: 500;
            font-size: .8rem;
            line-height: 1.2;
        }

        .emp-nip {
            font-size: .7rem;
            color: #94a3b8;
        }

        /* numeric cells */
        .num-cell {
            font-variant-numeric: tabular-nums;
        }

        .num-negative {
            color: #dc2626;
        }

        .take-home-cell {
            font-weight: 600;
            color: #2563eb;
        }

        /* period badge */
        .badge-period {
            display: inline-flex;
            padding: .15rem .6rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 500;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .badge-period.p2 {
            background: #f0fdf4;
            color: #16a34a;
        }

        /* ─── DataTables overrides ───────────────────────────── */
        div.dataTables_wrapper div.dataTables_length select,
        div.dataTables_wrapper div.dataTables_filter input {
            height: 32px;
            font-size: .8rem;
            border: 1px solid #e2e8f0;
            border-radius: .375rem;
        }

        div.dataTables_wrapper div.dataTables_info {
            font-size: .78rem;
            color: #64748b;
            padding-top: .65rem;
        }

        div.dataTables_wrapper div.dataTables_paginate {
            padding-top: .4rem;
        }

        div.dataTables_wrapper div.dataTables_paginate .paginate_button {
            font-size: .78rem;
            border-radius: .375rem !important;
            padding: .2rem .55rem;
        }

        .dt-buttons {
            display: none; /* hidden — we use our own Export button */
        }

        .dataTables_wrapper {
            padding: 1rem 1.25rem 1.25rem;
        }

        /* ─── Responsive ─────────────────────────────────────── */
        @media (max-width: 768px) {
            .filter-bar,
            .action-bar {
                padding: .75rem;
            }

            .action-bar .ms-auto {
                margin-left: 0;
            }
        }
    </style>
@endpush

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

                        {{-- Card Header --}}
                        <div class="card-header">
                            <div class="card-header-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <h4>List payrolls</h4>
                        </div>

                        {{-- Filter Bar --}}
                        <div class="filter-bar">
                            <div class="form-group">
                                <label class="form-label" for="filter_month_year">Month – year</label>
                                <input type="text" id="filter_month_year" class="form-control"
                                    placeholder="Choose month – year">
                            </div>
                            <button id="btn_filter" type="button" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button id="btn_reset" type="button" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </button>

                            <div class="ms-auto d-flex gap-2 flex-wrap align-items-center">
                                {{-- Generate All --}}
                                <form action="{{ route('Payrolls.generateAll') }}" method="POST"
                                    onsubmit="return confirm('Generate payrolls for all employees?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success" style="height:36px;font-size:.825rem">
                                        <i class="fas fa-cogs"></i> Generate all
                                    </button>
                                </form>

                                {{-- Import --}}
                                <a href="{{ route('pages.Importpayroll') }}"
                                    class="btn btn-dark" style="height:36px;font-size:.825rem;display:inline-flex;align-items:center;gap:.4rem">
                                    <i class="fas fa-file-import"></i> Import payrolls
                                </a>

                                {{-- Export Excel (triggers DataTables button) --}}
                                <button id="btn_export" type="button"
                                    class="btn btn-outline-success" style="height:36px;font-size:.825rem">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </button>
                            </div>
                        </div>

                        {{-- Action Bar (bulk actions + select-all) --}}
                        <div class="action-bar">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" id="select-all"
                                    style="width:15px;height:15px;cursor:pointer;accent-color:#2563eb">
                                <label for="select-all"
                                    style="font-size:.8rem;color:#64748b;cursor:pointer;user-select:none;margin:0">
                                    Select all
                                </label>
                                <span class="selection-info" id="selection-info">
                                    <strong id="selection-count">0</strong> selected
                                </span>
                            </div>

                            {{-- Bulk Delete --}}
                            <form id="bulk-delete-form"
                                action="{{ route('payrolls.bulkDelete') }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="payroll_ids" id="bulk-delete-ids">
                                <button type="submit" class="btn btn-danger" id="btn-bulk-delete"
                                    style="opacity:.5;pointer-events:none">
                                    <i class="fas fa-trash"></i> Delete selected
                                </button>
                            </form>
                        </div>

                        {{-- Table --}}
                        <div class="table-responsive">
                            <table id="payrolls-table" class="table">
                                <thead>
                                    <tr>
                                        <th></th>{{-- checkbox --}}
                                        <th>No.</th>
                                        <th style="text-align:left;min-width:170px">Employee</th>
                                        <th>Attendance</th>
                                        <th>Basic salary</th>
                                        <th>Allowance</th>
                                        <th>Daily allow.</th>
                                        <th>House allow.</th>
                                        <th>Meal allow.</th>
                                        <th>Transport</th>
                                        <th>Reimburse</th>
                                        <th>Bonus</th>
                                        <th>Overtime</th>
                                        <th>OT deduction</th>
                                        <th>Late fine</th>
                                        <th>Punishment</th>
                                        <th>BPJS Kes.</th>
                                        <th>BPJS Ket.</th>
                                        <th>Tax</th>
                                        <th>Debt</th>
                                        <th>Gross salary</th>
                                        <th>Total outcome</th>
                                        <th>Total income</th>
                                        <th>Take home</th>
                                        <th>Month</th>
                                        <th>Period</th>
                                    </tr>
                                </thead>
                                {{-- tbody filled by DataTables --}}
                            </table>
                        </div>

                    </div>{{-- /.card --}}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    {{-- DataTables --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Flatpickr --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">

    <script>
    $(function () {

        /* ── Flatpickr month picker ── */
        flatpickr('#filter_month_year', {
            dateFormat: 'Y-m',
            plugins: [new monthSelectPlugin({ shorthand: true, dateFormat: 'Y-m', altFormat: 'F Y' })]
        });

        /* ── Helper: format rupiah ── */
        function rupiah(val) {
            if (!val || val == 0) return '-';
            return 'Rp ' + parseInt(val).toLocaleString('id-ID');
        }

        /* ── Helper: rupiah with negative color ── */
        function rupiahNeg(val) {
            if (!val || val == 0) return '-';
            return '<span class="num-negative">Rp ' + parseInt(val).toLocaleString('id-ID') + '</span>';
        }

        /* ── Helper: employee initials ── */
        function initials(name) {
            if (!name) return '?';
            return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
        }

        /* ── DataTable ── */
        var table = $('#payrolls-table').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('payrolls.payrolls') }}',
                data: function (d) {
                    d.month_year = $('#filter_month_year').val();
                }
            },
            columns: [
                /* 0 — checkbox */
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data) {
                        return '<input type="checkbox" class="payroll-checkbox" '
                            + 'value="' + data.id + '" '
                            + 'style="width:15px;height:15px;cursor:pointer;accent-color:#2563eb">';
                    }
                },
                /* 1 — row number */
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                /* 2 — employee (name + nip merged) */
                {
                    data: 'employee_name',
                    name: 'employee_name',
                    render: function (data, type, row) {
                        var name = data || '-';
                        var nip  = row.employee_pengenal || '-';
                        return '<div class="employee-cell">'
                            + '<div class="emp-avatar">' + initials(name) + '</div>'
                            + '<div><div class="emp-name">' + name + '</div>'
                            + '<div class="emp-nip">' + nip + '</div></div>'
                            + '</div>';
                    }
                },
                /* 3 — attendance */
                {
                    data: 'attendance',
                    name: 'attendance',
                    className: 'text-center',
                    render: function (data) { return data ? data + ' days' : '-'; }
                },
                /* 4–13 — income columns */
                { data: 'basic_salary',        name: 'basic_salary',        className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                { data: 'allowance',            name: 'allowance',           className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                { data: 'daily_allowance',      name: 'daily_allowance',     className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                { data: 'house_allowance',      name: 'house_allowance',     className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                { data: 'meal_allowance',       name: 'meal_allowance',      className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                { data: 'transport_allowance',  name: 'transport_allowance', className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                { data: 'reamburse',            name: 'reamburse',           className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                { data: 'bonus',                name: 'bonus',               className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                { data: 'overtime',             name: 'overtime',            className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                /* 13–18 — deduction columns (merah) */
                { data: 'overtime_deduction', name: 'overtime_deduction', className: 'text-center num-cell', render: function(d){ return rupiahNeg(d); } },
                { data: 'late_fine',          name: 'late_fine',          className: 'text-center num-cell', render: function(d){ return rupiahNeg(d); } },
                { data: 'punishment',         name: 'punishment',         className: 'text-center num-cell', render: function(d){ return rupiahNeg(d); } },
                { data: 'bpjs_kes',           name: 'bpjs_kes',           className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                { data: 'bpjs_ket',           name: 'bpjs_ket',           className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                { data: 'tax',                name: 'tax',                className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                { data: 'debt',               name: 'debt',               className: 'text-center num-cell', render: function(d){ return rupiahNeg(d); } },
                /* 20–23 — summary columns */
                { data: 'gross_salary', name: 'gross_salary', className: 'text-center num-cell', render: function(d){ return '<strong>' + rupiah(d) + '</strong>'; } },
                { data: 'deductions',   name: 'deductions',   className: 'text-center num-cell', render: function(d){ return '<span class="num-negative"><strong>' + (d ? 'Rp ' + parseInt(d).toLocaleString('id-ID') : '-') + '</strong></span>'; } },
                { data: 'salary',       name: 'salary',       className: 'text-center num-cell', render: function(d){ return rupiah(d); } },
                {
                    data: 'take_home',
                    name: 'take_home',
                    className: 'text-center num-cell take-home-cell',
                    render: function(d){ return d ? '<strong>Rp ' + parseInt(d).toLocaleString('id-ID') + '</strong>' : '-'; }
                },
                /* 24–25 — month / period */
                {
                    data: 'month_year',
                    name: 'month_year',
                    className: 'text-center',
                    render: function (data) {
                        if (!data) return '-';
                        var d = new Date(data);
                        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
                    }
                },
                {
                    data: 'period',
                    name: 'period',
                    className: 'text-center',
                    render: function (data) {
                        if (!data) return '-';
                        var cls = data == 2 ? 'p2' : '';
                        return '<span class="badge-period ' + cls + '">Period ' + data + '</span>';
                    }
                }
            ],
            order: [[24, 'desc']],
            dom: 'lBfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn btn-success btn-sm d-none', /* hidden — triggered via #btn_export */
                    exportOptions: {
                        columns: [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25]
                    }
                }
            ],
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            language: {
                search: '',
                searchPlaceholder: 'Search...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_–_END_ of _TOTAL_ entries',
                infoEmpty: 'No entries found',
                emptyTable: 'No payroll data available'
            }
        });

        /* ── Filter / Reset buttons ── */
        $('#btn_filter').on('click', function () { table.ajax.reload(); });
        $('#btn_reset').on('click', function () {
            $('#filter_month_year').val('');
            table.ajax.reload();
        });

        /* ── Export Excel proxy button ── */
        $('#btn_export').on('click', function () {
            table.button('.buttons-excel').trigger();
        });

        /* ── Select-all toggle ── */
        $('#select-all').on('change', function () {
            var checked = $(this).is(':checked');
            $('input.payroll-checkbox').prop('checked', checked);
            updateSelectionUI();
        });

        /* ── Per-row checkbox change ── */
        $('#payrolls-table').on('change', 'input.payroll-checkbox', function () {
            var all   = $('input.payroll-checkbox').length;
            var chkd  = $('input.payroll-checkbox:checked').length;
            $('#select-all').prop('indeterminate', chkd > 0 && chkd < all);
            $('#select-all').prop('checked', chkd === all && all > 0);
            updateSelectionUI();
        });

        function updateSelectionUI() {
            var count = $('input.payroll-checkbox:checked').length;
            var $info = $('#selection-info');
            var $btn  = $('#btn-bulk-delete');
            $('#selection-count').text(count);
            if (count > 0) {
                $info.addClass('visible');
                $btn.css({ opacity: 1, pointerEvents: 'auto' });
            } else {
                $info.removeClass('visible');
                $btn.css({ opacity: .5, pointerEvents: 'none' });
            }
        }

        /* ── Bulk delete form submit ── */
        $('#bulk-delete-form').on('submit', function (e) {
            e.preventDefault();
            var checked = $('input.payroll-checkbox:checked');
            if (checked.length === 0) {
                Swal.fire('No selection', 'Please select at least one record.', 'warning');
                return;
            }
            Swal.fire({
                title: 'Delete ' + checked.length + ' record(s)?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    var ids = checked.map(function () { return this.value; }).get();
                    $('#bulk-delete-ids').val(ids.join(','));
                    $('#bulk-delete-form')[0].submit();
                }
            });
        });

        /* ── Session flash messages ── */
        @if (session('success'))
            Swal.fire({ icon: 'success', title: 'Success', text: '{{ session('success') }}' });
        @endif
        @if (session('error'))
            Swal.fire({ icon: 'error', title: 'Error', text: '{{ session('error') }}' });
        @endif

    }); /* end document.ready */
    </script>
@endpush