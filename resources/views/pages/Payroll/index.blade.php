@extends('layouts.app')
@section('title', 'Payroll')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .section-header h1 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }

        .page-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .page-actions .btn {
            height: 36px;
            font-size: .825rem;
            padding: 0 1rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: .5rem;
        }

        /* Period info bar */
        .period-bar {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: .625rem;
            padding: .75rem 1.25rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .period-bar-label {
            font-size: .78rem;
            font-weight: 600;
            color: #1e40af;
        }

        .period-bar-range {
            font-size: .78rem;
            color: #3b82f6;
        }

        .period-bar-status {
            display: inline-flex;
            align-items: center;
            padding: .18rem .6rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
        }

        /* Stat cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 1.25rem;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #f1f5f9;
            border-radius: .625rem;
            padding: 14px 16px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .stat-card-label {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .7px;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .stat-card-value {
            font-size: 1.5rem;
            font-weight: 600;
            line-height: 1;
            color: #1e293b;
        }

        .stat-card-value.amber {
            color: #d97706;
        }

        .stat-card-value.green {
            color: #16a34a;
        }

        .stat-card-value.blue {
            color: #1d4ed8;
        }

        .stat-card-sub {
            font-size: .7rem;
            color: #94a3b8;
            margin-top: 5px;
        }

        /* Card */
        .emp-card {
            border: none;
            border-radius: .625rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .07);
            background: #fff;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        .emp-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            padding: .875rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .emp-card-header-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            flex-shrink: 0;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .emp-card-header-title {
            font-size: .9rem;
            font-weight: 600;
            color: #334155;
            flex: 1;
        }

        .emp-card-header-count {
            font-size: .72rem;
            color: #64748b;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: .15rem .7rem;
        }

        /* Toolbar */
        /* .dt-toolbar {
                        padding: .75rem 1.25rem;
                        border-bottom: 1px solid #f1f5f9;
                        background: #fafafa;
                        display: flex;
                        align-items: center;
                        gap: .5rem;
                        flex-wrap: nowrap;
                    } */
        .dt-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        .dt-toolbar .btn {
            height: 32px;
            font-size: .775rem;
            padding: 0 .75rem;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: .4rem;
        }

        /* .dt-toolbar select {
                        height: 32px;
                        font-size: .775rem;
                        border: 1px solid #e2e8f0;
                        border-radius: .4rem;
                        padding: 0 .6rem;
                    } */
        .dt-toolbar select,
        .dt-toolbar button {
            min-height: 38px;
            font-size: 14px;
            padding: 6px 10px;
        }

        .dt-toolbar select {
            flex: 1;
            min-width: 120px;
        }

        .dt-toolbar button {
            flex-shrink: 0;
            white-space: nowrap;
        }

        .dt-toolbar .flex-spacer {
            flex: 1;
        }

        /* Mobile: Stack everything vertically */
        @media (max-width: 768px) {
            .dt-toolbar {
                flex-direction: column;
                gap: 10px;
            }

            .dt-toolbar select,
            .dt-toolbar button {
                width: 100%;
                min-height: 44px;
                font-size: 16px;
            }

            .dt-toolbar .flex-spacer {
                display: none;
            }

            .dt-toolbar button {
                padding: 10px;
            }
        }

        /* Tablet: 2 columns */
        @media (min-width: 769px) and (max-width: 1024px) {
            .dt-toolbar select {
                flex: 0 1 calc(50% - 4px);
            }
        }

        /* Desktop: Original layout */
        @media (min-width: 1025px) {
            .dt-toolbar select {
                flex: 0 0 auto;
            }
        }

        /* Bulk action bar */
        .bulk-bar {
            display: none;
            padding: .6rem 1.25rem;
            background: #eff6ff;
            border-bottom: 1px solid #bfdbfe;
            align-items: center;
            gap: .75rem;
        }

        .bulk-bar.show {
            display: flex;
        }

        .bulk-bar-count {
            font-size: .8rem;
            font-weight: 600;
            color: #1e40af;
        }

        .bulk-bar .btn {
            height: 30px;
            font-size: .775rem;
        }

        /* Table */
        #payroll-table {
            width: 100% !important;
            font-size: .8rem;
        }

        #payroll-table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .5px;
            padding: .7rem .9rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
        }

        #payroll-table tbody td {
            padding: .75rem .9rem;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #f8fafc;
            color: #334155;
        }

        #payroll-table tbody tr:last-child td {
            border-bottom: none;
        }

        #payroll-table tbody tr:hover td {
            background: #f8fafc;
        }

        /* Employee cell */
        .emp-cell {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .emp-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: .65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .emp-avatar-name {
            font-weight: 600;
            font-size: .8rem;
            line-height: 1.2;
        }

        .emp-avatar-id {
            font-size: .7rem;
            color: #94a3b8;
        }

        /* Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .18rem .6rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Action */
        .action-wrap {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .act-btn {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            font-size: .75rem;
            text-decoration: none;
            transition: all .15s;
        }

        .act-btn:hover {
            background: #f8fafc;
        }

        .act-btn.act-warning {
            border-color: #fde68a;
            background: #fffbeb;
            color: #d97706;
        }

        .act-btn.act-success {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #16a34a;
        }

        .act-btn.act-danger {
            border-color: #fecaca;
            background: #fef2f2;
            color: #dc2626;
        }

        /* Number */
        .num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">

            {{-- Header --}}
            <div class="section-header">
                <div>
                    <div style="font-size:.72rem;color:#94a3b8;margin-bottom:3px">
                        Dashboard /
                        <a href="{{ route('payrollperiod.index') }}" style="color:#64748b;text-decoration:none">Payroll
                            Periods</a> /
                        <span style="color:#1e293b">{{ $period->period_label }}</span>
                    </div>
                    <h1>Payroll — {{ $period->period_label }}</h1>
                </div>
                <div class="page-actions">
                    @if ($period->isOpen())
                        <a href="{{ route('payroll.generate', $period->id) }}" class="btn btn-primary"
                            onclick="return confirm('Generate payroll untuk semua employee?')">
                            <i class="fas fa-cog"></i> Generate Payroll
                        </a>
                        <button class="btn btn-warning" data-toggle="modal" data-target="#importAttendanceModal">
                            <i class="fas fa-file-import"></i> Import External Data
                        </button>

                        <a href="{{ route('payroll.attendanceTemplate', $period->id) }}" class="btn btn-light">
                            <i class="fas fa-download"></i> Download Template External Data
                        </a>
                    @endif

                    <button class="btn btn-light" id="btn-export">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </div>
            </div>

            {{-- Period info bar --}}
            <div class="period-bar">
                <span class="period-bar-label">
                    <i class="fas fa-calendar-alt"></i> {{ $period->period_label }}
                </span>
                <span class="period-bar-range">
                    {{ $period->period_start->format('d/m/Y') }} — {{ $period->period_end->format('d/m/Y') }}
                </span>
                @php
                    $statusColor = match ($period->status) {
                        'open' => 'background:#f0fdf4;color:#166534',
                        'closed' => 'background:#fffbeb;color:#92400e',
                        'locked' => 'background:#fef2f2;color:#991b1b',
                        default => '',
                    };
                @endphp
                <span class="period-bar-status" style="{{ $statusColor }}">
                    {{ ucfirst($period->status) }}
                </span>
                @if ($period->note)
                    <span style="font-size:.78rem;color:#64748b">— {{ $period->note }}</span>
                @endif
            </div>

            {{-- Stat Cards --}}
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-card-label">Draft</div>
                    <div class="stat-card-value amber">{{ $stats['draft'] }}</div>
                    <div class="stat-card-sub">Belum diapprove</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Approved</div>
                    <div class="stat-card-value green">{{ $stats['approved'] }}</div>
                    <div class="stat-card-sub">Sudah diapprove</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Paid</div>
                    <div class="stat-card-value blue">{{ $stats['paid'] }}</div>
                    <div class="stat-card-sub">Sudah dibayar</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Total Net Salary</div>
                    <div class="stat-card-value" style="font-size:1.1rem">
                        Rp {{ number_format($stats['total_net'], 0, ',', '.') }}
                    </div>
                    <div class="stat-card-sub">Semua status</div>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="emp-card">
                <div class="emp-card-header">
                    <div class="emp-card-header-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <span class="emp-card-header-title">Data Payroll</span>
                    <span class="emp-card-header-count" id="record-count">— records</span>
                </div>


                <div class="dt-toolbar">
                    <select id="filter-status">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
                    </select>

                    <select id="filter-company">
                        <option value="">All Companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->name }}">{{ $company->name }}</option>
                        @endforeach
                    </select>

                    <select id="filter-department">
                        <option value="">All Departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->department_name }}">{{ $department->department_name }}</option>
                        @endforeach
                    </select>

                    <select id="filter-grading">
                        <option value="">All Gradings</option>
                        @foreach ($gradings as $grading)
                            <option value="{{ $grading->grading_name }}">{{ $grading->grading_name }}</option>
                        @endforeach
                    </select>

                    <select id="filter-status-employee">
                        <option value="">All Type</option>
                        <option value="PKWT">PKWT</option>
                        <option value="On Job Training">On Job Training</option>
                        <option value="DW">Daily Worker</option>
                    </select>

                    <select id="filter-store">
                        <option value="">All Location</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->name }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                    <select id="filter-untuk-active-inactive">
                        <option value="">All Emp. Status</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <button class="btn btn-light" id="btn-search">
                        <i class="fas fa-search"></i> Search
                    </button>

                    <button class="btn btn-light" id="btn-reset">
                        <i class="fas fa-redo"></i> Reset
                    </button>

                    <div class="flex-spacer"></div>

                  </div>
                <div class="bulk-bar" id="bulk-bar">
                    <span class="bulk-bar-count"><span id="selected-count">0</span> dipilih</span>
                    <button class="btn btn-success" id="btn-do-bulk-approve">
                        <i class="fas fa-check-double"></i> Approve
                    </button>
                    {{-- ← tambahan --}}
                    <button class="btn btn-primary" id="btn-do-bulk-send-slip">
                        <i class="fas fa-paper-plane"></i> Kirim Slip Email
                    </button>
                    <a href="#" id="btn-do-bulk-download-slip" class="btn btn-info" target="_blank">
                        <i class="fas fa-file-pdf"></i> Download Slip
                    </a>
                    <button class="btn btn-danger" id="btn-do-bulk-delete">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                    <button class="btn btn-light" id="btn-clear-select">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>

                <div class="table-responsive" style="padding:.75rem 1.25rem 1rem">
                    <table id="payroll-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="check-all"></th>
                                <th>#</th>
                                <th>NIP</th>
                                <th>Employee</th>
                                <th>Company</th>
                                <th>Department</th>
                                <th>Grading</th>
                                <th>Location</th>
                                <th>Position</th>
                                <th>Type</th>
                                <th>Emp. Status</th>
                                <th>Wrk Days</th>
                                <th>Attndnce</th>
                                <th class="num">Basic Salary</th>
                                <th class="num">Pos Allowance</th>
                                <th class="num">Daily Rate</th>
                                <th class="num">Gross</th>
                                <th class="num">Meal All</th>
                                <th class="num">Trans All</th>
                                <th class="num">House All</th>
                                <th class="num">Overtime</th>
                                <th class="num">Reimburse</th>
                                <th class="num">Income</th>

                                <th class="num">BPJS Kes</th>
                                <th class="num">BPJS Ket</th>
                                <th class="num">Punish</th>
                                <th class="num">SO Punish</th>
                                <th class="num">Debt</th>
                                <th class="num">Tax</th>
                                <th class="num">Deduction</th>
                                <th class="num">Net Salary</th>
                                {{-- <th>Prorate</th> --}}
                                <th>Status</th>
                                <th style="text-align:center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>



        </section>
    </div>


    {{-- Modal --}}
    <div class="modal fade" id="importAttendanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-import"></i> Import External Data
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('payroll.importAttendance', $period->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info" style="font-size:.8rem">
                            <i class="fas fa-info-circle"></i>
                            Download template dulu, isi kolom <code>reamburse_amount, punishment, punishment_so, debt,
                                tax</code>,
                            lalu upload di sini.
                        </div>
                        <div class="form-group">
                            <label>File Excel</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(function() {

            // ── DataTable ──
            const table = $('#payroll-table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ route('payroll.data', $period->id) }}',
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.status_type = $('#filter-status-employee').val();
                        d.status_employee = $('#filter-untuk-active-inactive').val();
                        d.store_name = $('#filter-store').val();
                        d.company_name = $('#filter-company').val();
                        d.department_name = $('#filter-department').val();
                        d.grading_name = $('#filter-grading').val();
                    }
                },
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        // ← checkbox sekarang muncul untuk semua status
                        // backend (approveBulk/destroyBulk) sudah filter where('status','draft')
                        // jadi tidak akan ada efek samping kalau dicentang untuk approved/paid
                        render: (data, type, row) =>
                            `<input type="checkbox" class="row-check" data-id="${row.id}">`,
                    },
                    {
                        data: null,
                        render: (d, t, r, m) => m.row + 1,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'employee_pengenal',
                        defaultContent: '-'
                    },
                    {
                        data: 'employee_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'company_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'department_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'grading_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'grading_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'position_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'status_type_badge',
                        // orderable: false
                    },
                    {
                        data: 'status_employee',
                        // orderable: false
                    },
                    {
                        data: null,
                        render: (d, t, row) =>
                            `<span style="font-size:.78rem">${row.working_days} hari</span>`,
                        orderable: false,
                    },
                    {
                        data: null,
                        render: (d, t, row) =>
                            `<span style="font-size:.78rem">${row.attendance_days} hari</span>`,
                        orderable: false,
                    },
                    {
                        data: 'basic_salary_fmt',
                        className: 'num'
                    },
                    {
                        data: 'position_allowance_fmt',
                        className: 'num'
                    },
                    {
                        data: 'daily_rate_fmt',
                        className: 'num'
                    },
                    {
                        data: 'gross_salary_fmt',
                        className: 'num'
                    },
                    {
                        data: 'meal_allowance_fmt',
                        className: 'num'
                    },
                    {
                        data: 'transport_allowance_fmt',
                        className: 'num'
                    },
                    {
                        data: 'house_allowance_fmt',
                        className: 'num'
                    },
                    {
                        data: 'overtime_amount_fmt',
                        className: 'num'
                    },
                    {
                        data: 'reimburse_amount_fmt',
                        className: 'num'
                    },
                    {
                        data: 'total_income_fmt',
                        className: 'num',
                        render: d => `<span style="color:#16a34a">${d}</span>`
                    },
                    {
                        data: 'bpjs_kesehatan_fmt',
                        className: 'num'
                    },
                    {
                        data: 'bpjs_ketenagakerjaan_fmt',
                        className: 'num'
                    },
                    {
                        data: 'punishment_fmt',
                        className: 'num'
                    },
                    {
                        data: 'punishment_so_fmt',
                        className: 'num'
                    },
                    {
                        data: 'debt_fmt',
                        className: 'num'
                    },
                    {
                        data: 'tax_fmt',
                        className: 'num'
                    },
                    {
                        data: 'total_deduction_fmt',
                        className: 'num',
                        render: d => `<span style="color:#dc2626">${d}</span>`
                    },
                    {
                        data: 'net_salary_fmt',
                        className: 'num',
                        render: d => `<strong>${d}</strong>`
                    },
                    {
                        data: 'status_badge',
                        orderable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                    },
                ],
                drawCallback: function() {
                    const info = this.api().page.info();
                    $('#record-count').text(info.recordsTotal + ' records');
                    updateBulkBar();
                },
                language: {
                    processing: 'Memuat data...',
                    emptyTable: 'Belum ada data payroll. Klik Generate Payroll.',
                    zeroRecords: 'Data tidak ditemukan.',
                },
                order: [
                    [2, 'asc']
                ],
                pageLength: 25,
            });

            // ── Filter ──
            $('#btn-search').on('click', () => table.ajax.reload());
            $('#btn-reset').on('click', () => {
                $('#filter-status, #filter-status-employee, #filter-store, #filter-company, #filter-department, #filter-grading, #filter-untuk-active-inactive')
                    .val('');
                table.ajax.reload();
            });

            // ── Checkbox select all ──
            $(document).on('change', '#check-all', function() {
                $('.row-check').prop('checked', this.checked);
                updateBulkBar();
            });

            $(document).on('change', '.row-check', function() {
                updateBulkBar();
            });

            function updateBulkBar() {
                const count = $('.row-check:checked').length;
                $('#selected-count').text(count);
                if (count > 0) {
                    $('#bulk-bar').addClass('show');
                } else {
                    $('#bulk-bar').removeClass('show');
                }

                // ← update href download slip setiap kali jumlah checked berubah
                const ids = $('.row-check:checked').map((i, el) => $(el).data('id')).get();
                const url = '{{ route('payroll.slipBulk', $period->id) }}' +
                    (ids.length ? '?' + ids.map(id => 'ids[]=' + id).join('&') : '');
                $('#btn-do-bulk-download-slip').attr('href', url);
            }

            $('#btn-clear-select').on('click', () => {
                $('.row-check, #check-all').prop('checked', false);
                updateBulkBar();
            });

            // ── Approve satu ──
            $(document).on('click', '.btn-approve', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Approve payroll ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Approve',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.post('{{ url('payroll') }}/' + id + '/approve', {
                            _token: '{{ csrf_token() }}'
                        })
                        .done(res => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Approved!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                        })
                        .fail(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.responseJSON?.error ?? 'Terjadi kesalahan.'
                            });
                        });
                });
            });

            // ── Bulk Approve ──
            $('#btn-do-bulk-approve').on('click', function() {
                const ids = $('.row-check:checked').map((i, el) => $(el).data('id')).get();

                if (ids.length === 0) return;

                Swal.fire({
                    title: `Approve ${ids.length} payroll?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Approve Semua',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.post('{{ route('payroll.approveBulk') }}', {
                            _token: '{{ csrf_token() }}',
                            ids: ids,
                        })
                        .done(res => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Bulk Approved!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $('.row-check, #check-all').prop('checked', false);
                            updateBulkBar();
                            table.ajax.reload();
                        })
                        .fail(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.responseJSON?.error ?? 'Terjadi kesalahan.'
                            });
                        });
                });
            });

            $(document).on('click', '.btn-delete-single', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Hapus Payroll?',
                    text: 'Data payroll dan detail akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '{{ url('payroll') }}/' + id,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE',
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Dihapus!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: res.message
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message ??
                                    'Terjadi kesalahan.'
                            });
                        }
                    });
                });
            });

            // ── Bulk delete ──
            $('#btn-do-bulk-delete').on('click', function() {
                const ids = $('.row-check:checked').map((i, el) => $(el).data('id')).get();

                if (ids.length === 0) return;

                Swal.fire({
                    title: `Hapus ${ids.length} Payroll?`,
                    text: 'Semua data payroll dan detail yang dipilih akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus Semua!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.post('{{ route('payroll.destroyBulk') }}', {
                            _token: '{{ csrf_token() }}',
                            ids: ids,
                        })
                        .done(res => {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Dihapus!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                $('.row-check, #check-all').prop('checked', false);
                                updateBulkBar();
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: res.message
                                });
                            }
                        })
                        .fail(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.responseJSON?.message ?? 'Terjadi kesalahan.'
                            });
                        });
                });
            });

            // ── Export ──
            $('#btn-export').on('click', () => {
                const params = new URLSearchParams({
                    status: $('#filter-status').val(),
                    status_employee: $('#filter-status-employee').val(),
                    store_name: $('#filter-store').val(),
                    company_name: $('#filter-company').val(),
                    department_name: $('#filter-department').val(),
                    grading_name: $('#filter-grading').val(),
                });
                window.location.href = '{{ route('payroll.export', $period->id) }}?' + params.toString();
            });

            // ── Kirim slip per-baris ──
            $(document).on('click', '.btn-send-slip', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Kirim slip gaji?',
                    text: 'Email akan diproses di background.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1d4ed8',
                    confirmButtonText: 'Ya, Kirim',
                    cancelButtonText: 'Batal',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.post('{{ url('payroll') }}/' + id + '/slip/send', {
                            _token: '{{ csrf_token() }}'
                        })
                        .done(res => Swal.fire({
                            icon: 'success',
                            title: 'Terkirim!',
                            text: res.message ?? res,
                            timer: 2500,
                            showConfirmButton: false
                        }))
                        .fail(err => Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: err.responseJSON?.message ?? 'Terjadi kesalahan.'
                        }));
                });
            });

            // ── Kirim slip bulk ──
            $('#btn-do-bulk-send-slip').on('click', function() {
                const ids = $('.row-check:checked').map((i, el) => $(el).data('id')).get();

                if (ids.length === 0) return;

                Swal.fire({
                    title: `Kirim slip ke ${ids.length} karyawan?`,
                    text: 'Email akan diproses di background, mungkin butuh beberapa menit.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1d4ed8',
                    confirmButtonText: 'Ya, Kirim',
                    cancelButtonText: 'Batal',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.post('{{ route('payroll.slipSendBulk', $period->id) }}', {
                            _token: '{{ csrf_token() }}',
                            ids: ids,
                        })
                        .done(res => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Masuk Antrian!',
                                text: res.message
                            });
                            $('.row-check, #check-all').prop('checked', false);
                            updateBulkBar();
                        })
                        .fail(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.responseJSON?.message ?? 'Terjadi kesalahan.'
                            });
                        });
                });
            });

            // ── Flash messages ──
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#1d4ed8',
                    timer: 4000,
                    timerProgressBar: true,
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#dc2626',
                });
            @endif
        });
    </script>
@endpush
