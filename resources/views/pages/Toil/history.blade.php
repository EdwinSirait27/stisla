@extends('layouts.app')

@section('title', 'TOIL History')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
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
    }

    /* Summary Cards */
    .summary-card {
        background: #fff;
        border-radius: 0.5rem;
        padding: 1.5rem;
        text-align: center;
        height: 100%;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
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
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .summary-card .unit {
        font-size: 0.8rem;
        color: #9ca3af;
    }

    .summary-card.overtime .value { color: #5e72e4; }
    .summary-card.leave .value    { color: #f59e0b; }
    .summary-card.records .value  { color: #10b981; }

    /* Filter Bar */
    .filter-card {
        background: #fff;
        border-radius: 0.5rem;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .filter-card label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.35rem;
        display: block;
    }

    /* Badge styling */
    .badge-type {
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.375rem;
    }

    .badge-category-overtime { background-color: #dbeafe; color: #1e40af; }
    .badge-category-leave    { background-color: #fef3c7; color: #92400e; }
    .badge-cash              { background-color: #d1fae5; color: #065f46; }
    .badge-toil              { background-color: #dbeafe; color: #1e40af; }
    .badge-status-active     { background-color: #d1fae5; color: #065f46; }
    .badge-status-expired    { background-color: #fee2e2; color: #991b1b; }
    .badge-status-fully_used { background-color: #e5e7eb; color: #374151; }
    .badge-status-paid       { background-color: #fef3c7; color: #92400e; }
    .badge-status-cancelled  { background-color: #f3f4f6; color: #6b7280; }
    .badge-status-Approved   { background-color: #d1fae5; color: #065f46; }
    .badge-status-Pending    { background-color: #fef3c7; color: #92400e; }
    .badge-status-Rejected   { background-color: #fee2e2; color: #991b1b; }
    .badge-status-Cancelled  { background-color: #f3f4f6; color: #6b7280; }

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

    .detail-cell {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ── Select2 Custom Styling ── */
    .select2-container {
        width: 100% !important;
    }

    /* FIX: ganti display:flex dengan position:relative agar tombol × bisa diklik */
    .select2-container--default .select2-selection--single {
        height: 36px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        position: relative;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #4a5568;
        font-size: 0.875rem;
        line-height: 34px;
        padding-left: 0.6rem;
        padding-right: 2.5rem; /* beri ruang untuk × dan arrow */
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px;
        top: 0;
        right: 0;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af;
    }

    /* FIX: posisi tombol × agar bisa diklik */
    .select2-container--default .select2-selection--single .select2-selection__clear {
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1rem;
        cursor: pointer;
        pointer-events: all;
        z-index: 1;
    }

    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
        font-size: 0.875rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #5e72e4;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        font-size: 0.85rem;
        padding: 0.3rem 0.5rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .table-responsive { padding: 0 0.75rem; }
        .card-header { padding: 1rem; }
        .summary-card .value { font-size: 1.5rem; }
    }
</style>

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>TOIL History</h1>
            </div>

            <div class="section-body">

                {{-- ── Summary Cards ── --}}
                <div class="row mb-4">
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="summary-card overtime">
                            <div class="label"><i class="fas fa-business-time"></i> Total Overtime</div>
                            <div class="value" id="summary-overtime">0.00</div>
                            <div class="unit">Hours (all time)</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="summary-card leave">
                            <div class="label"><i class="fas fa-umbrella-beach"></i> Total Leave Taken</div>
                            <div class="value" id="summary-leave">0.00</div>
                            <div class="unit">Hours (all time)</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="summary-card records">
                            <div class="label"><i class="fas fa-history"></i> Total Records</div>
                            <div class="value" id="summary-records">0</div>
                            <div class="unit">Activity</div>
                        </div>
                    </div>
                </div>

                {{-- ── Filter Bar ── --}}
                <div class="filter-card">
                    <h6 style="font-weight: 600; color: #4a5568; margin-bottom: 1rem;">
                        <i class="fas fa-filter"></i> Filter History
                    </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter-category">Category</label>
                                {{-- No form-control class to avoid conflict with Select2 --}}
                                <select id="filter-category">
                                    <option value="">All</option>
                                    <option value="Overtime">Overtime</option>
                                    <option value="Leave">TOIL Leave</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter-status">Status</label>
                                {{-- No form-control class to avoid conflict with Select2 --}}
                                <select id="filter-status">
                                    <option value="">All</option>
                                    <option value="active">Active</option>
                                    <option value="fully_used">Fully Used</option>
                                    <option value="expired">Expired</option>
                                    <option value="paid">Paid</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter-start-date">Start Date</label>
                                <input type="date" class="form-control form-control-sm" id="filter-start-date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter-end-date">End Date</label>
                                <input type="date" class="form-control form-control-sm" id="filter-end-date">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-secondary btn-sm" id="btn-reset">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="btn-filter">
                                <i class="fas fa-search"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── Tabel Mixed Timeline ── --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-history"></i> TOIL Activity History</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="history-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Date</th>
                                                <th class="text-center">Category</th>
                                                <th class="text-center">Hours</th>
                                                <th class="text-center">Detail</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Approver</th>
                                                <th>Description</th>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // ── Init Select2: Category ──
            $('#filter-category').select2({
                placeholder: 'All Categories',
                allowClear: true,
                width: '100%'
            });

            // ── Init Select2: Status ──
            $('#filter-status').select2({
                placeholder: 'All Status',
                allowClear: true,
                width: '100%'
            });

            // ── Storage untuk merged data ──
            var allData = [];

            // ── Init DataTable ──
            var table = $('#history-table').DataTable({
                processing: true,
                serverSide: false,
                autoWidth: false,
                data: [],
                responsive: true,
                order: [[0, 'desc']],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    emptyTable: "There is no history of TOIL activity."
                },
                columns: [
                    {
                        data: 'date_raw',
                        className: 'text-center',
                        render: function (data, type, row) {
                            if (type === 'sort' || type === 'type') return data;
                            return row.date_display;
                        }
                    },
                    {
                        data: 'category',
                        className: 'text-center',
                        render: function (data) {
                            var cls  = data === 'Overtime' ? 'badge-category-overtime' : 'badge-category-leave';
                            var icon = data === 'Overtime' ? 'fa-business-time' : 'fa-umbrella-beach';
                            return '<span class="badge-type ' + cls + '"><i class="fas ' + icon + '"></i> ' + data + '</span>';
                        }
                    },
                    {
                        data: 'hours',
                        className: 'text-center',
                        render: function (data) {
                            return '<strong>' + data + ' hrs</strong>';
                        }
                    },
                    {
                        data: 'detail',
                        className: 'text-center',
                        render: function (data, type, row) {
                            if (row.category === 'Overtime') {
                                var typeCls = data === 'Cash' ? 'badge-cash' : 'badge-toil';
                                return '<span class="badge-type ' + typeCls + '">' + data + '</span>';
                            }
                            return '<span class="text-muted">Leave</span>';
                        }
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function (data) {
                            return '<span class="badge-type badge-status-' + data + '">' + data.toUpperCase() + '</span>';
                        }
                    },
                    { data: 'approver_name', className: 'text-center' },
                    {
                        data: 'reason',
                        className: 'detail-cell',
                        render: function (data) {
                            if (!data || data === '-') return '<span class="text-muted">-</span>';
                            return data.length > 40 ? data.substr(0, 40) + '...' : data;
                        }
                    }
                ]
            });

            // ── Load Both Endpoints & Merge ──
            function loadAllData() {
                table.processing(true);

                Promise.all([
                    fetch('{{ route('toil.history.assignments') }}').then(r => r.json()),
                    fetch('{{ route('toil.history.leave-requests') }}').then(r => r.json())
                ]).then(function ([overtime, leaves]) {

                    var overtimeData = (overtime.data || []).map(function (o) {
                        return {
                            id:            o.id,
                            date_raw:      parseDateForSort(o.date),
                            date_display:  o.date,
                            category:      'Overtime',
                            hours:         o.earned_hours,
                            detail:        o.compensation_type,
                            status:        o.status,
                            approver_name: o.approver_name,
                            reason:        o.reason
                        };
                    });

                    var leaveData = (leaves.data || []).map(function (l) {
                        return {
                            id:            l.id,
                            date_raw:      parseDateForSort(l.leave_date),
                            date_display:  l.leave_date,
                            category:      'Leave',
                            hours:         l.hours_used,
                            detail:        'Leave',
                            status:        l.status,
                            approver_name: l.approver_name,
                            reason:        l.reason || l.rejected_reason || '-'
                        };
                    });

                    allData = [...overtimeData, ...leaveData];

                    applyFilters();
                    updateSummary(overtimeData, leaveData);

                }).catch(function (err) {
                    console.error('Failed to load history:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Load Data',
                        text: 'Unable to load history data.'
                    });
                }).finally(function () {
                    table.processing(false);
                });
            }

            // ── Helper: parse "05 Mar 2026" → "2026-03-05" ──
            function parseDateForSort(dateStr) {
                if (!dateStr || dateStr === '-') return '0000-00-00';
                try {
                    var parts  = dateStr.split(' ');
                    var months = {
                        'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
                        'May': '05', 'Jun': '06', 'Jul': '07', 'Aug': '08',
                        'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12'
                    };
                    return parts[2] + '-' + (months[parts[1]] || '01') + '-' + parts[0].padStart(2, '0');
                } catch (e) {
                    return '0000-00-00';
                }
            }

            // ── Apply Filter ──
            function applyFilters() {
                var category  = $('#filter-category').val();
                var status    = $('#filter-status').val();
                var startDate = $('#filter-start-date').val();
                var endDate   = $('#filter-end-date').val();

                var filtered = allData.filter(function (row) {
                    if (category  && row.category !== category) return false;
                    if (status    && row.status   !== status)   return false;
                    if (startDate && row.date_raw  < startDate) return false;
                    if (endDate   && row.date_raw  > endDate)   return false;
                    return true;
                });

                table.clear().rows.add(filtered).draw();
            }

            // ── Update Summary Cards ──
            function updateSummary(overtimeData, leaveData) {
                var totalOvertime = overtimeData.reduce(function (sum, o) {
                    return sum + parseFloat(o.hours);
                }, 0);

                var totalLeave = leaveData
                    .filter(function (l) { return l.status === 'Approved'; })
                    .reduce(function (sum, l) {
                        return sum + parseFloat(l.hours);
                    }, 0);

                $('#summary-overtime').text(totalOvertime.toFixed(2));
                $('#summary-leave').text(totalLeave.toFixed(2));
                $('#summary-records').text(allData.length);
            }

            // ── Filter Events ──
            $('#btn-filter').on('click', applyFilters);

            $('#btn-reset').on('click', function () {
                $('#filter-category').val('').trigger('change');
                $('#filter-status').val('').trigger('change');
                $('#filter-start-date').val('');
                $('#filter-end-date').val('');
                applyFilters();
            });

            // ── Initial Load ──
            loadAllData();

        });
    </script>
@endpush