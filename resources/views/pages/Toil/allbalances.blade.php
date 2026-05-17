@extends('layouts.app')

@section('title', 'All TOIL Balances')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush

<style>
    /* Card Styles (sesuai pattern project) */
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

    /* Summary Cards */
    .summary-card {
        background: #fff;
        border-radius: 0.5rem;
        padding: 1.5rem;
        text-align: center;
        height: 100%;
    }

    .summary-card .label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .summary-card .value {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .summary-card .unit {
        font-size: 0.75rem;
        color: #9ca3af;
    }

    .summary-card.cash .value { color: #10b981; }
    .summary-card.toil .value { color: #5e72e4; }
    .summary-card.used .value { color: #f59e0b; }
    .summary-card.expired .value { color: #ef4444; }
    .summary-card.records .value { color: #6366f1; }
    .summary-card.employees .value { color: #ec4899; }

    /* Filter Bar */
    .filter-card {
        background: #fff;
        border-radius: 0.5rem;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .filter-card .form-group {
        margin-bottom: 0.75rem;
    }

    .filter-card label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.35rem;
    }

    .filter-card .form-control {
        font-size: 0.85rem;
    }

    /* Badge styling (sama dengan Balance view) */
    .badge-type {
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.375rem;
    }

    .badge-cash { background-color: #d1fae5; color: #065f46; }
    .badge-toil { background-color: #dbeafe; color: #1e40af; }
    .badge-status-active { background-color: #d1fae5; color: #065f46; }
    .badge-status-expired { background-color: #fee2e2; color: #991b1b; }
    .badge-status-fully_used { background-color: #e5e7eb; color: #374151; }
    .badge-status-paid { background-color: #fef3c7; color: #92400e; }
    .badge-status-cancelled { background-color: #f3f4f6; color: #6b7280; }

    /* Table Styles */
    .table-responsive {
        padding: 0 1.5rem;
        overflow-x: auto;
    }

    .table { width: 100%; border-collapse: separate; border-spacing: 0; }

    .table thead th {
        background-color: #f8fafc;
        color: #4a5568;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
        white-space: nowrap;
    }

    .table tbody tr { transition: all 0.25s ease; }
    .table tbody tr:hover { background-color: rgba(94, 114, 228, 0.03); }

    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        color: #4a5568;
        font-size: 0.85rem;
        border: none;
        background: #fff;
    }

    .text-center { text-align: center; }

    .section-header h1 {
        font-weight: 600;
        color: #2d3748;
        font-size: 1.5rem;
    }

    .employee-info {
        text-align: left;
    }
    .employee-info .name {
        font-weight: 600;
        color: #2d3748;
    }
    .employee-info .pin {
        font-size: 0.7rem;
        color: #9ca3af;
    }

    /* Days left coloring */
    .days-left-warning { color: #f59e0b; font-weight: 600; }
    .days-left-danger { color: #ef4444; font-weight: 600; }
    .days-left-ok { color: #10b981; font-weight: 600; }

    /* Select2 overrides — sesuai fingerprint list */
    .filter-card .select2-container .select2-selection--single {
        height: 31px !important;
        border: 1px solid #ced4da;
        border-radius: 0.2rem;
    }

    .filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 31px;
        font-size: 0.85rem;
        color: #495057;
    }

    .filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 31px;
    }

    .filter-card .select2-container {
        width: 100% !important;
    }

    @media (max-width: 768px) {
        .table-responsive { padding: 0 0.5rem; }
        .summary-card .value { font-size: 1.4rem; }
    }
</style>

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>All TOIL Balances</h1>
            </div>

            <div class="section-body">

                {{-- ════════════════════════════════════════ --}}
                {{-- Summary Cards Aggregate                   --}}
                {{-- ════════════════════════════════════════ --}}
                <div class="row">
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="summary-card cash">
                            <div class="label"><i class="fas fa-money-bill-wave"></i> Total Cash Pending</div>
                            <div class="value" id="summary-cash">0.00</div>
                            <div class="unit">jam — belum dibayar</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="summary-card toil">
                            <div class="label"><i class="fas fa-clock"></i> Total TOIL Aktif</div>
                            <div class="value" id="summary-toil">0.00</div>
                            <div class="unit">jam tersisa</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="summary-card used">
                            <div class="label"><i class="fas fa-check-circle"></i> Total Used</div>
                            <div class="value" id="summary-used">0.00</div>
                            <div class="unit">jam terpakai</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="summary-card expired">
                            <div class="label"><i class="fas fa-times-circle"></i> Total Expired</div>
                            <div class="value" id="summary-expired">0.00</div>
                            <div class="unit">jam hangus</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="summary-card records">
                            <div class="label"><i class="fas fa-database"></i> Total Records</div>
                            <div class="value" id="summary-records">0</div>
                            <div class="unit">saldo records</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="summary-card employees">
                            <div class="label"><i class="fas fa-users"></i> Karyawan Aktif</div>
                            <div class="value" id="summary-employees">0</div>
                            <div class="unit">karyawan punya saldo</div>
                        </div>
                    </div>
                </div>

                {{-- ════════════════════════════════════════ --}}
                {{-- Filter Bar                                --}}
                {{-- ════════════════════════════════════════ --}}
                <div class="filter-card">
                    <h6 style="font-weight: 600; color: #4a5568; margin-bottom: 1rem;">
                        <i class="fas fa-filter"></i> Filter
                    </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Store</label>
                                <select class="form-control form-control-sm select2-store" id="filter-store">
                                    <option value="">Semua Store</option>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control form-control-sm select2-status" id="filter-status">
                                    <option value="">Semua</option>
                                    <option value="active">Active</option>
                                    <option value="fully_used">Fully Used</option>
                                    <option value="expired">Expired</option>
                                    <option value="paid">Paid</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" class="form-control form-control-sm" id="filter-start-date" style="width:100%;height:31px">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" class="form-control form-control-sm" id="filter-end-date" style="width:100%;height:31px">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ════════════════════════════════════════ --}}
                {{-- DataTable                                 --}}
                {{-- ════════════════════════════════════════ --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-list"></i> All TOIL Balances</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="all-balances-table">
                                        <thead>
                                            <tr>
                                                <th>Employee Name</th>
                                                <th class="text-center">Store</th>
                                                <th class="text-center">Ovt Date</th>
                                                <th class="text-center">Type</th>
                                                <th class="text-center">Earned</th>
                                                <th class="text-center">Used</th>
                                                <th class="text-center">Remaining</th>
                                                <th class="text-center">Expires</th>
                                                <th class="text-center">Days Left</th>
                                                <th class="text-center">Status</th>
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
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ── Init Select2 ──
            $('.select2-store').select2({
                width: '100%',
                placeholder: 'Semua Store',
                allowClear: true
            });

            $('.select2-status').select2({
                width: '100%',
                placeholder: 'Semua',
                allowClear: true,
                minimumResultsForSearch: Infinity
            });

            // ── Initialize DataTable ──
            var table = $('#all-balances-table').DataTable({
                processing: true,
                serverSide: false,
                autoWidth: false,
                ajax: {
                    url: '{{ route('toil.all-balances.data') }}',
                    type: 'GET',
                    data: function(d) {
                        d.store_id   = $('#filter-store').val();
                        d.status     = $('#filter-status').val();
                        d.start_date = $('#filter-start-date').val();
                        d.end_date   = $('#filter-end-date').val();
                    },
                    dataSrc: function(json) {
                        updateSummary(json.summary);
                        return json.data;
                    }
                },
                responsive: true,
                order: [[2, 'desc']], // sort by Ovt Date
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    emptyTable: "Belum ada data saldo TOIL."
                },
                columns: [
                    // Kolom 0: Employee Name + PIN
                    {
                        data: 'employee_name',
                        render: function(data, type, row) {
                            return '<div class="employee-info">' +
                                '<div class="name">' + (data ?? '-') + '</div>' +
                                '<div class="pin">PIN: ' + (row.employee_pin ?? '-') + '</div>' +
                                '</div>';
                        }
                    },
                    // Kolom 1: Store
                    {
                        data: 'store',
                        className: 'text-center'
                    },
                    // Kolom 2: Ovt Date (work_date dari controller)
                    {
                        data: 'work_date',
                        className: 'text-center'
                    },
                    // Kolom 3: Type (Cash / Toil)
                    {
                        data: 'compensation_type',
                        className: 'text-center',
                        render: function(data) {
                            if (!data || data === '-') return '<span class="text-muted">-</span>';
                            var cls = data === 'Cash' ? 'badge-cash' : 'badge-toil';
                            return '<span class="badge-type ' + cls + '">' + data + '</span>';
                        }
                    },
                    // Kolom 4: Earned
                    {
                        data: 'earned_hours',
                        className: 'text-center'
                    },
                    // Kolom 5: Used
                    {
                        data: 'used_hours',
                        className: 'text-center'
                    },
                    // Kolom 6: Remaining
                    {
                        data: 'remaining_hours',
                        className: 'text-center',
                        render: function(data) {
                            return '<strong>' + (data ?? '0.00') + '</strong>';
                        }
                    },
                    // Kolom 7: Expires
                    {
                        data: 'expires_at',
                        className: 'text-center'
                    },
                    // Kolom 8: Days Left
                    {
                        data: 'days_left',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (row.status !== 'active' || data === null) {
                                return '<span class="text-muted">-</span>';
                            }
                            var cls = 'days-left-ok';
                            if (data <= 7)       cls = 'days-left-danger';
                            else if (data <= 14) cls = 'days-left-warning';
                            return '<span class="' + cls + '">' + data + ' hari</span>';
                        }
                    },
                    // Kolom 9: Status
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function(data) {
                            if (!data) return '-';
                            var labels = {
                                'active':     'ACTIVE',
                                'fully_used': 'FULLY USED',
                                'expired':    'EXPIRED',
                                'paid':       'PAID',
                                'cancelled':  'CANCELLED',
                            };
                            var label = labels[data] ?? data.toUpperCase();
                            return '<span class="badge-type badge-status-' + data + '">' + label + '</span>';
                        }
                    }
                ]
            });

            // ── Update Summary Cards ──
            function updateSummary(summary) {
                if (!summary) return;
                $('#summary-cash').text(parseFloat(summary.total_cash_pending || 0).toFixed(2));
                $('#summary-toil').text(parseFloat(summary.total_toil_active  || 0).toFixed(2));
                $('#summary-used').text(parseFloat(summary.total_used         || 0).toFixed(2));
                $('#summary-expired').text(parseFloat(summary.total_expired   || 0).toFixed(2));
                $('#summary-records').text(summary.total_records   || 0);
                $('#summary-employees').text(summary.active_employees || 0);
            }

            // ── Auto-apply filter on change ──
            $('#filter-store, #filter-status').on('change', function() {
                table.ajax.reload();
            });
            $('#filter-start-date, #filter-end-date').on('change', function() {
                table.ajax.reload();
            });

        });
    </script>
@endpush