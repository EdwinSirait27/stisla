@extends('layouts.app')

@section('title', 'TOIL Balance')

@push('styles')
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
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .summary-card .unit {
        font-size: 0.8rem;
        color: #9ca3af;
    }

    .summary-card.cash .value { color: #10b981; }
    .summary-card.toil .value { color: #5e72e4; }
    .summary-card.used .value { color: #f59e0b; }
    .summary-card.expired .value { color: #ef4444; }

    /* Info Banner */
    .info-banner {
        background: linear-gradient(to right, #eff6ff, #dbeafe);
        border-left: 4px solid #5e72e4;
        border-radius: 0.5rem;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        color: #1e3a8a;
        font-size: 0.875rem;
    }

    .info-banner i {
        color: #5e72e4;
        margin-right: 0.5rem;
    }

    /* Badge styling */
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
        overflow: hidden;
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
    }

    .table tbody tr {
        transition: all 0.25s ease;
        position: relative;
    }

    .table tbody tr:hover {
        background-color: rgba(94, 114, 228, 0.03);
    }

    .table tbody td {
        padding: 1.1rem 0.75rem;
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

    /* Days left coloring */
    .days-left-warning { color: #f59e0b; font-weight: 600; }
    .days-left-danger { color: #ef4444; font-weight: 600; }
    .days-left-ok { color: #10b981; font-weight: 600; }

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
                <h1>TOIL Balance</h1>
            </div>

            <div class="section-body">

                {{-- ── Summary Cards ── --}}
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="summary-card cash">
                            <div class="label"><i class="fas fa-money-bill-wave"></i> Cash Balance</div>
                            <div class="value" id="summary-cash">0.00</div>
                            <div class="unit">Time</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="summary-card toil">
                            <div class="label"><i class="fas fa-clock"></i> TOIL Balance</div>
                            <div class="value" id="summary-toil">0.00</div>
                            <div class="unit">Hours Remaining</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="summary-card used">
                            <div class="label"><i class="fas fa-check-circle"></i> Total Used</div>
                            <div class="value" id="summary-used">0.00</div>
                            <div class="unit">Hours</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="summary-card expired">
                            <div class="label"><i class="fas fa-times-circle"></i> Expired</div>
                            <div class="value" id="summary-expired">0.00</div>
                            <div class="unit">Hours</div>
                        </div>
                    </div>
                </div>

                {{-- ── Tabel List Saldo ── --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-list"></i> TOIL Balance Detail</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="balance-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Overtime Date</th>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ── DataTable Saldo ──
            var table = $('#balance-table').DataTable({
                processing: true,
                serverSide: false,
                autoWidth: false,
                ajax: {
                    url: '{{ route('toil.balance.data') }}',
                    type: 'GET',
                    dataSrc: function(json) {
                        updateSummary(json.summary);
                        return json.data;
                    }
                },
                responsive: true,
                order: [[5, 'asc']],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    emptyTable: "Belum ada saldo TOIL. Saldo akan otomatis terbuat saat manager assign overtime."
                },
                columns: [
                    {
                        data: 'work_date',
                        className: 'text-center'
                    },
                    {
                        data: 'compensation_type',
                        className: 'text-center',
                        render: function(data) {
                            var cls = data === 'Cash' ? 'badge-cash' : 'badge-toil';
                            return '<span class="badge-type ' + cls + '">' + data + '</span>';
                        }
                    },
                    { data: 'earned_hours', className: 'text-center' },
                    { data: 'used_hours', className: 'text-center' },
                    {
                        data: 'remaining_hours',
                        className: 'text-center',
                        render: function(data) {
                            return '<strong>' + data + '</strong>';
                        }
                    },
                    { data: 'expires_at', className: 'text-center' },
                    {
                        data: 'days_left',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (row.status !== 'active') {
                                return '<span class="text-muted">-</span>';
                            }
                            var cls = 'days-left-ok';
                            if (data <= 7) cls = 'days-left-danger';
                            else if (data <= 14) cls = 'days-left-warning';
                            return '<span class="' + cls + '">' + data + ' hari</span>';
                        }
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function(data) {
                            return '<span class="badge-type badge-status-' + data + '">' + data.toUpperCase() + '</span>';
                        }
                    }
                ]
            });

            // ── Update Summary Cards ──
            function updateSummary(summary) {
                if (!summary) return;
                $('#summary-cash').text(parseFloat(summary.total_cash || 0).toFixed(2));
                $('#summary-toil').text(parseFloat(summary.total_toil_remaining || 0).toFixed(2));
                $('#summary-used').text(parseFloat(summary.total_used || 0).toFixed(2));
                $('#summary-expired').text(parseFloat(summary.total_expired || 0).toFixed(2));
            }

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