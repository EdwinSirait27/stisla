@extends('layouts.app')
@section('title', 'Employee Salary')

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

        .stat-card-value.blue {
            color: #1d4ed8;
        }

        .stat-card-value.purple {
            color: #6d28d9;
        }

        .stat-card-value.amber {
            color: #d97706;
        }

        .stat-card-sub {
            font-size: .7rem;
            color: #94a3b8;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
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
        .dt-toolbar {
            padding: .75rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #fafafa;
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
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

        .dt-toolbar input,
        .dt-toolbar select {
            height: 32px;
            font-size: .775rem;
            border: 1px solid #e2e8f0;
            border-radius: .4rem;
            padding: 0 .6rem;
        }

        /* Table */
        #salary-table {
            width: 100% !important;
            font-size: .8rem;
        }

        #salary-table thead th {
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

        #salary-table tbody td {
            padding: .75rem .9rem;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #f8fafc;
            color: #334155;
        }

        #salary-table tbody tr:last-child td {
            border-bottom: none;
        }

        #salary-table tbody tr:hover td {
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
            padding: .18rem .6rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-pkwt {
            background: #eff6ff;
            color: #1e40af;
        }

        .badge-ojt {
            background: #fdf4ff;
            color: #6b21a8;
        }

        .badge-dw {
            background: #fffbeb;
            color: #92400e;
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
            color: #1e293b;
        }

        /* Hint bar */
        .hint-bar {
            padding: .65rem 1.25rem;
            background: #fafafa;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex-wrap: wrap;
        }

        .hint-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .72rem;
            color: #94a3b8;
        }

        .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }

        /* Number column */
        .num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        /* Modal import */
        .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
        }

        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
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
                        <a href="#" style="color:#64748b;text-decoration:none">Payroll</a> /
                        <span style="color:#1e293b">Employee Salary</span>
                    </div>
                    <h1>Employee Salary</h1>
                </div>
                <div class="page-actions">
                    <button class="btn btn-light" onclick="downloadTemplate()">
                        <i class="fas fa-download"></i> Download Template
                    </button>
                    <button class="btn btn-warning" data-toggle="modal" data-target="#importModal">
                        <i class="fas fa-file-import"></i> Import Excel
                    </button>
                    <a href="{{ route('employeesalary.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Salary
                    </a>
                </div>
            </div>
            @if(session('skipped'))
    <div class="alert alert-warning">
        <strong>Beberapa data diskip karena sudah ada:</strong>
        <ul class="mb-0 mt-1">
            @foreach(session('skipped') as $skip)
                <li>{{ $skip }}</li>
            @endforeach
        </ul>
    </div>
@endif


            {{-- Stat Cards --}}
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-card-label">Total Employees</div>
                    <div class="stat-card-value">{{ $stats['total'] }}</div>
                    <div class="stat-card-sub">Terdaftar di sistem</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">PKWT</div>
                    <div class="stat-card-value blue">{{ $stats['pkwt'] }}</div>
                    <div class="stat-card-sub">
                        <span class="stat-dot" style="background:#1d4ed8"></span> Karyawan kontrak
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">On Job Training</div>
                    <div class="stat-card-value purple">{{ $stats['ojt'] }}</div>
                    <div class="stat-card-sub">
                        <span class="stat-dot" style="background:#6d28d9"></span> Karyawan magang
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Daily Worker</div>
                    <div class="stat-card-value amber">{{ $stats['dw'] }}</div>
                    <div class="stat-card-sub">
                        <span class="stat-dot" style="background:#d97706"></span> Karyawan harian
                    </div>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="emp-card">
                <div class="emp-card-header">
                    <div class="emp-card-header-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <span class="emp-card-header-title">Salary Data</span>
                    <span class="emp-card-header-count" id="record-count">— records</span>
                </div>
                {{-- Toolbar --}}
                <div class="dt-toolbar" style="flex-wrap:nowrap">
                    <input type="date" id="filter-effective-date" style="width:140px">
                    <select id="filter-company" style="width:150px">
                        <option value="">All Companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->name }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    <select id="filter-department" style="width:150px">
                        <option value="">All Departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->department_name }}">{{ $department->department_name }}</option>
                        @endforeach
                    </select>

                    <select id="filter-store" style="width:150px">
                        <option value="">All Location</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->name }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                    <select id="filter-grading" style="width:150px">
                        <option value="">All Gradings</option>
                        @foreach ($gradings as $grading)
                            <option value="{{ $grading->grading_name }}">{{ $grading->grading_name }}</option>
                        @endforeach
                    </select>
                    <select id="filter-status" style="width:150px">
                        <option value="">All Status</option>
                        <option value="PKWT">PKWT</option>
                        <option value="On Job Training">On Job Training</option>
                        <option value="DW">Daily Worker</option>
                    </select>

                    <button class="btn btn-light" id="btn-search"><i class="fas fa-search"></i> Cari</button>
                    <button class="btn btn-light" id="btn-reset"><i class="fas fa-redo"></i> Reset</button>
                    <div style="flex:1"></div>
                    <button class="btn btn-light" id="btn-export"><i class="fas fa-file-excel"></i> Export</button>
                </div>

                {{-- Table --}}
                <div class="table-responsive" style="padding:.75rem 1.25rem 1rem">
                    <table id="salary-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th class="text-center">Employee</th>
                                <th class="text-center">NIP</th>
                                <th class="text-center">Company</th>
                                <th class="text-center">Department</th>
                                <th class="text-center">Location</th>
                                <th class="text-center">Grading</th>
                                <th class="text-center">Position</th>
                                <th class="text-center">Status</th>
                                <th class="text-center num">Basic Salary</th>
                                <th class="text-center num">Position Allowance</th>
                                <th class="text-center num">Daily Rate</th>
                                <th class="text-center num">Meal Allowance</th>
                                <th class="text-center num">House Allowance</th>
                                <th class="text-center num">Transport Allowance</th>
                                <th class="text-center num">BPJS Ketenagakerjaan</th>
                                <th class="text-center num">BPJS Keshatan</th>
                                <th class="text-center">Effective Date</th>
                                <th style="text-align:center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                {{-- Hint bar --}}
                <div class="hint-bar">
                    <div class="hint-item">
                        <span class="dot" style="background:#1d4ed8"></span> PKWT — basic salary + position allowance
                    </div>
                    <div class="hint-item">
                        <span class="dot" style="background:#6d28d9"></span> On Job Training — basic salary + position
                        allowance
                    </div>
                    <div class="hint-item">
                        <span class="dot" style="background:#d97706"></span> Daily Worker — daily rate saja
                    </div>
                </div>
            </div>
            {{-- Activity Log Card --}}
            <div class="emp-card">
                <div class="emp-card-header">
                    <div class="emp-card-header-icon" style="background:#f0fdf4;color:#16a34a">
                        <i class="fas fa-history"></i>
                    </div>
                    <span class="emp-card-header-title">Activity Log</span>
                </div>

                <div class="table-responsive" style="padding:.75rem 1.25rem 1rem">
                    <table id="activity-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th class="text-center">Event</th>
                                <th class="text-center">Employee</th>
                                <th class="text-center">Effective Date</th>
                                <th class="text-center num">Basic Salary (Old)</th>
                                <th class="text-center num">Basic Salary (New)</th>
                                <th class="text-center num">Position Allowance (Old)</th>
                                <th class="text-center num">Position Allowance (New)</th>
                                <th class="text-center num">Daily Rate (Old)</th>
                                <th class="text-center num">Daily Rate (New)</th>
                                <th class="text-center num">Meal Allowance (Old)</th>
                                <th class="text-center num">Meal Allowance (New)</th>
                                <th class="text-center num">House Allowance (Old)</th>
                                <th class="text-center num">House Allowance (New)</th>

                                <th class="text-center num">Transport Allowance (Old)</th>
                                <th class="text-center num">Transport Allowance (New)</th>

                                <th class="text-center num">BPJS Ketenagakerjaan (Old)</th>
                                <th class="text-center num">BPJS Ketenagakerjaan (New)</th>

                                <th class="text-center num">BPJS Kesehatan (Old)</th>
                                <th class="text-center num">Meal Kesehatan (New)</th>
                                <th class="text-center">Changed By</th>
                                <th class="text-center">Changed At</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                {{--  --}}

            </div>
        </section>
    </div>



    {{-- Modal Import --}}
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-import"></i> Import Employee Salary
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('employeesalary.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Effective Date <span class="text-danger">*</span></label>
                            <input type="date" name="effective_date" class="form-control" required>
                            <small class="text-muted">Tanggal berlaku salary ini.</small>
                        </div>
                        <div class="form-group">
                            <label>File Excel <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                            <small class="text-muted">Format: .xlsx atau .xls</small>
                        </div>
                        <div class="alert alert-info py-2" style="font-size:.8rem">
                            <i class="fas fa-info-circle"></i>
                            Kolom yang dibutuhkan hanya ini ya dan untuk simbol dan tanda baca tidak diperbolehkan exmpl. . , spasi ! :
                            <code>employee_pengenal</code>,
                            <code>basic_salary</code>,
                            <code>position_allowance</code>,
                            <code>daily_rate</code>,
                            <code>meal_allowance</code>,
                            <code>house_allowance</code>,
                            <code>transport_rate</code>,
                            <code>bpjs_ketenagakerjaan</code>,
                            <code>bpjs_kesehatan</code>,
                            <code>daily_rate</code>
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
            // ── Select2 ──
            $('.select2-status, .select2-store').select2({
                width: '160px'
            });
            // ── DataTable ──
            const table = $('#salary-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('employeesalary.data') }}',
                    data: function(d) {
                        d.effective_date = $('#filter-effective-date').val();
                        d.status_employee = $('#filter-status').val();
                        d.company_name = $('#filter-company').val();
                        d.department_name = $('#filter-department').val();
                        d.store_name = $('#filter-store').val();
                        d.grading_name = $('#filter-grading').val();
                    }
                },
                columns: [{
                        data: null,
                        render: (data, type, row, meta) => meta.row + 1,
                        orderable: false,
                        searchable: false,
                        className: 'text-muted'
                    },
                    {
                        data: 'employee_name',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'employee_pengenal',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'company_name',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'department_name',
                        className: 'text-center',
                        defaultContent: '-'
                    },

                    {
                        data: 'store_name',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'grading_name',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'position_name',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'status_employee',
                        className: 'text-center',

                        render: data => {
                            const map = {
                                'PKWT': '<span class="status-badge badge-pkwt">PKWT</span>',
                                'On Job Training': '<span class="status-badge badge-ojt">On Job Training</span>',
                                'DW': '<span class="status-badge badge-dw">Daily Worker</span>',
                            };
                            return map[data] ?? `<span class="status-badge">${data}</span>`;
                        }
                    },
                    {
                        data: 'basic_salary',
                        className: 'num text-center',
                        render: (data, type, row) =>
                            row.status_employee === 'DW' ?
                            '<span style="color:#cbd5e1">—</span>' : parseInt(data).toLocaleString(
                                'id-ID')
                    },
                    {
                        data: 'position_allowance',
                        className: 'num text-center',
                        render: (data, type, row) =>
                            row.status_employee === 'DW' ?
                            '<span style="color:#cbd5e1">—</span>' : parseInt(data).toLocaleString(
                                'id-ID')
                    },

                    {
                        data: 'daily_rate',
                        className: 'num text-center',
                        render: (data, type, row) =>
                            row.status_employee !== 'DW' ?
                            '<span style="color:#cbd5e1">—</span>' : parseInt(data).toLocaleString(
                                'id-ID')
                    },
                    {
    data: 'meal_allowance',
    className: 'num text-center',
    render: data => parseInt(data).toLocaleString('id-ID')
},
{
    data: 'house_allowance',
    className: 'num text-center',
    render: data => parseInt(data).toLocaleString('id-ID')
},
{
    data: 'transport_allowance',
    className: 'num text-center',
    render: data => parseInt(data).toLocaleString('id-ID')
},
{
    data: 'bpjs_ketenagakerjaan',
    className: 'num text-center',
    render: data => parseInt(data).toLocaleString('id-ID')
},
{
    data: 'bpjs_kesehatan',
    className: 'num text-center',
    render: data => parseInt(data).toLocaleString('id-ID')
},
                    {
                        data: 'effective_date',
                        className: 'num text-center',

                        render: data => {
                            if (!data) return '-';
                            const d = new Date(data);
                            return d.toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });
                        }
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
                drawCallback: function() {
                    const info = this.api().page.info();
                    $('#record-count').text(info.recordsTotal + ' records');
                },
                language: {
                    processing: 'Memuat data...',
                    emptyTable: 'Tidak ada data salary.',
                    zeroRecords: 'Data tidak ditemukan.',
                },
                pageLength: 25,
                order: [
                    [8, 'desc']
                ],
            });

            const activityTable = $('#activity-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('employeesalary.activity') }}',
                },
                // columns: [{
                //         data: null,
                //         render: (data, type, row, meta) => meta.row + 1,
                //         orderable: false,
                //         searchable: false,
                //     },
                //     {
                //         data: 'event_badge',
                //         orderable: false,
                //     },
                //     {
                //         data: 'employee_name',
                //         className: 'text-center',
                //         defaultContent: '-'
                //     },
                //     {
                //         data: 'effective_date',
                //         className: 'text-center',
                //         defaultContent: '-'
                //     },
                //     {
                //         data: 'basic_salary',
                //         className: 'text-center num',
                //         defaultContent: '-'
                //     },
                //     {
                //         data: 'position_allowance',
                //         className: 'text-center num',
                //         defaultContent: '-'
                //     },
                //     {
                //         data: 'allowance',
                //         className: 'text-center num',
                //         defaultContent: '-'
                //     },
                //     {
                //         data: 'daily_rate',
                //         className: 'text-center num',
                //         defaultContent: '-'
                //     },
                //     {
                //         data: 'causer_name',
                //         className: 'text-center',
                //         defaultContent: '-'
                //     },
                //     {
                //         data: 'changed_at',
                //         className: 'text-center',
                //         defaultContent: '-'
                //     },
                // ],
                columns: [{
                        data: null,
                        render: (d, t, r, m) => m.row + 1,
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'event_badge',
                        orderable: false
                    },
                    {
                        data: 'employee_name',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'effective_date',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'basic_salary_old',
                        className: 'text-center num',
                        defaultContent: '-',
                        render: d => d !== '-' ? `<span style="color:#94a3b8">${d}</span>` : '-'
                    },
                    {
                        data: 'basic_salary_new',
                        className: 'text-center num',
                        defaultContent: '-'
                    },
                    {
                        data: 'position_allowance_old',
                        className: 'text-center num',
                        defaultContent: '-',
                        render: d => d !== '-' ? `<span style="color:#94a3b8">${d}</span>` : '-'
                    },
                    {
                        data: 'position_allowance_new',
                        className: 'text-center num',
                        defaultContent: '-'
                    },
                    {
                        data: 'daily_rate_old',
                        className: 'text-center num',
                        defaultContent: '-',
                        render: d => d !== '-' ? `<span style="color:#94a3b8">${d}</span>` : '-'
                    },
                    {
                        data: 'daily_rate_new',
                        className: 'text-center num',
                        defaultContent: '-'
                    },
                    {
                        data: 'meal_allowance_old',
                        className: 'text-center num',
                        defaultContent: '-',
                        render: d => d !== '-' ? `<span style="color:#94a3b8">${d}</span>` : '-'
                    },
                    {
                        data: 'meal_allowance_new',
                        className: 'text-center num',
                        defaultContent: '-'
                    },
                   
                    {
                        data: 'house_allowance_old',
                        className: 'text-center num',
                        defaultContent: '-',
                        render: d => d !== '-' ? `<span style="color:#94a3b8">${d}</span>` : '-'
                    },
                    {
                        data: 'house_allowance_new',
                        className: 'text-center num',
                        defaultContent: '-'
                    },
                     {
                        data: 'transport_allowance_old',
                        className: 'text-center num',
                        defaultContent: '-',
                        render: d => d !== '-' ? `<span style="color:#94a3b8">${d}</span>` : '-'
                    },
                    {
                        data: 'transport_allowance_new',
                        className: 'text-center num',
                        defaultContent: '-'
                    },
                    {
                        data: 'bpjs_ketenagakerjaan_old',
                        className: 'text-center num',
                        defaultContent: '-',
                        render: d => d !== '-' ? `<span style="color:#94a3b8">${d}</span>` : '-'
                    },
                    {
                        data: 'bpjs_ketenagakerjaan_new',
                        className: 'text-center num',
                        defaultContent: '-'
                    },
                    {
                        data: 'bpjs_kesehatan_old',
                        className: 'text-center num',
                        defaultContent: '-',
                        render: d => d !== '-' ? `<span style="color:#94a3b8">${d}</span>` : '-'
                    },
                    {
                        data: 'bpjs_kesehatan_new',
                        className: 'text-center num',
                        defaultContent: '-'
                    },
                    {
                        data: 'causer_name',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'changed_at',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                ],
                rawColumns: ['event_badge'],
                language: {
                    processing: 'Memuat data...',
                    emptyTable: 'Belum ada activity log.',
                },
                order: [
                    [9, 'desc']
                ],
                pageLength: 10,
            });

            // ── Filter ──
            $('#btn-search').on('click', () => table.ajax.reload());
            $('#btn-reset').on('click', () => {
                $('#filter-effective-date').val('');
                $('#filter-status').val('').trigger('change');
                $('#filter-store').val('').trigger('change');
                $('#filter-company').val('').trigger('change');
                $('#filter-department').val('').trigger('change');
                $('#filter-grading').val('').trigger('change');
                table.ajax.reload();
            });

            
            $('#btn-export').on('click', () => {
                const params = new URLSearchParams({
                    effective_date: $('#filter-effective-date').val(),
                    status: $('#filter-status').val(),
                    store_name: $('#filter-store').val(),
                    company_name: $('#filter-company').val(),
                    department_name: $('#filter-department').val(),
                    grading_name: $('#filter-grading').val(),
                });
                window.location.href = '{{ route('employeesalary.export') }}?' + params.toString();
            });

            // ── Download template ──
            window.downloadTemplate = () => {
                window.location.href = '{{ route('employeesalary.template') }}';
            };

            // ── Flash messages ──
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#1d4ed8',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#dc2626'
                });
            @endif
        });
    </script>
@endpush
