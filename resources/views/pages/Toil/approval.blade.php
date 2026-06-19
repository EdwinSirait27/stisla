@extends('layouts.app')

@section('title', 'TOIL Approval')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush

<style>
    .card { border: none; box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.08); border-radius: 0.5rem; overflow: hidden; transition: all 0.3s cubic-bezier(0.25,0.8,0.25,1); background-color: #fff; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.12); }
    .card-header { background-color: #f8fafc; border-bottom: 1px solid rgba(0,0,0,0.03); padding: 1.25rem 1.5rem; }
    .card-header h6 { margin: 0; font-weight: 600; color: #4a5568; display: flex; align-items: center; font-size: 0.95rem; }
    .card-header h6 i { margin-right: 0.75rem; color: #5e72e4; }
    .form-section .form-group label { font-weight: 600; color: #4a5568; font-size: 0.85rem; margin-bottom: 0.35rem; }
    .form-section .form-control { font-size: 0.9rem; }
    .filter-card { background: #fff; border-radius: 0.5rem; padding: 1.25rem 1.5rem; box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    .filter-card label { font-size: 0.8rem; font-weight: 600; color: #4a5568; margin-bottom: 0.35rem; display: block; }
    .badge-type { padding: 0.4rem 0.8rem; font-size: 0.75rem; font-weight: 600; border-radius: 0.375rem; }
    .badge-status-Approved  { background-color: #d1fae5; color: #065f46; }
    .badge-status-Cancelled { background-color: #f3f4f6; color: #6b7280; }
    .badge-status-Pending   { background-color: #fef3c7; color: #92400e; }
    .badge-status-Rejected  { background-color: #fee2e2; color: #991b1b; }
    .table-responsive { padding: 0 1.5rem; overflow-x: auto; }
    .table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table thead th { background-color: #f8fafc; color: #4a5568; font-weight: 600; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px; border: none; padding: 1rem 0.75rem; white-space: nowrap; }
    .table tbody tr { transition: all 0.25s ease; }
    .table tbody tr:hover { background-color: rgba(94,114,228,0.03); }
    .table tbody td { padding: 1rem 0.75rem; vertical-align: middle; color: #4a5568; font-size: 0.85rem; border: none; background: #fff; }
    .text-center { text-align: center; }
    table.dataTable { width: 100% !important; }
    table.dataTable thead th { position: relative; padding-left: 1.25rem !important; padding-right: 1.25rem !important; }
    table.dataTable thead th:before, table.dataTable thead th:after { right: 0.4rem; }
    .section-header h1 { font-weight: 600; color: #2d3748; font-size: 1.5rem; }
    .btn-primary { background-color: #5e72e4; border-color: #5e72e4; transition: all 0.3s ease; }
    .btn-primary:hover { background-color: #4a5bd1; border-color: #4a5bd1; transform: translateY(-1px); }
    .btn-danger { background-color: #ef4444; border-color: #ef4444; }
    .employee-info { text-align: left; }
    .employee-info .name { font-weight: 600; color: #2d3748; }
    .employee-info .pin  { font-size: 0.7rem; color: #9ca3af; }
    .select2-container--default .select2-selection--single { height: 36px; border: 1px solid #ced4da; border-radius: 0.25rem; position: relative; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: #4a5568; font-size: 0.875rem; line-height: 34px; padding-left: 0.6rem; padding-right: 2.5rem; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 34px; top: 0; right: 0; }
    .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #9ca3af; }
    .select2-dropdown { border: 1px solid #ced4da; border-radius: 0.25rem; box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.1); font-size: 0.875rem; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: #5e72e4; }
    button:disabled { opacity: 0.75; cursor: not-allowed; }
    @keyframes fa-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .fa-spin { animation: fa-spin 1s infinite linear; }
</style>

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>TOIL Approval</h1>
                <div class="section-header-breadcrumb">
                    <span class="text-muted">Manager: {{ $manager->employee_name ?? '-' }}</span>
                </div>
            </div>

            <div class="section-body">

                {{-- Form Create TOIL Leave --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6><i class="fas fa-plus-circle"></i> Create TOIL Leave</h6>
                    </div>
                    <div class="card-body form-section">
                        <form id="formCreateLeave">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select Employee <span class="text-danger">*</span></label>
                                        <select name="employee_id" id="employee_id" required>
                                            <option value="">-- Select Employee --</option>
                                            @foreach ($subordinates as $emp)
                                                <option value="{{ $emp->id }}">
                                                    {{ $emp->employee_name }} ({{ $emp->pin ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            Total subordinates: <strong>{{ $subordinates->count() }}</strong> employees
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Available TOIL Balance</label>
                                        <div class="border rounded p-2" id="saldo-box" style="background:#f8fafc; min-height:38px;">
                                            <span id="saldo-display" class="text-muted">Select an employee first</span>
                                        </div>
                                        <small class="form-text text-muted" id="saldo-info">
                                            System will deduct from the oldest balance first (FIFO).
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="start_date" id="start_date" required>
                                        <small class="form-text text-muted">Tanggal mulai libur</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>End Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="end_date" id="end_date" required>
                                        <small class="form-text text-muted">Sama dengan start kalau 1 hari</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Total</label>
                                        <div class="border rounded p-2" style="background:#f8fafc; min-height:38px;">
                                            <span id="total-display" class="text-muted">Pilih tanggal dulu</span>
                                        </div>
                                        <small class="form-text text-muted">1 hari = 8 jam TOIL</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Reason / Notes <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="reason" id="reason" rows="2" required
                                            minlength="10" maxlength="1000"
                                            placeholder="Min 10 characters (e.g. Employee requested a day off for a family event)"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 text-right">
                                    <button type="reset" class="btn btn-secondary btn-sm mr-2" id="btn-form-reset">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-sm" id="btn-submit" disabled>
                                        <i class="fas fa-paper-plane"></i> Submit & Approve
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Filter Bar --}}
                <div class="filter-card">
                    <h6 style="font-weight: 600; color: #4a5568; margin-bottom: 1rem;">
                        <i class="fas fa-filter"></i> Filter History
                    </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select id="filter-status">
                                    <option value="">All</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Leave Date From</label>
                                <input type="date" class="form-control form-control-sm" id="filter-start-date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Leave Date To</label>
                                <input type="date" class="form-control form-control-sm" id="filter-end-date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Employee Name</label>
                                <input type="text" class="form-control form-control-sm" id="filter-employee" placeholder="Search name...">
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

                {{-- DataTable History --}}
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-history"></i> TOIL Leave History</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="approval-table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th class="text-center">Leave Date</th>
                                        <th class="text-center">Days</th>
                                        <th class="text-center">Total Hours</th>
                                        <th>Reason</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Created At</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    {{-- Modal Cancel TOIL Leave --}}
    <div class="modal fade" id="modalCancel" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formCancel">
                    @csrf
                    <input type="hidden" id="cancel_id">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-times-circle"></i> Cancel TOIL Leave
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Cancelling this leave will:
                            <ul class="mb-0 mt-2">
                                <li>Restore the employee's TOIL balance</li>
                                <li>Revert the roster back to the original shift (all days)</li>
                                <li>Set the leave status to <strong>Cancelled</strong></li>
                            </ul>
                        </div>
                        <div class="form-group">
                            <label>Cancellation Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3"
                                required minlength="10" maxlength="1000"
                                placeholder="Min 10 characters (e.g. Employee changed their schedule last minute)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Back</button>
                        <button type="submit" class="btn btn-danger btn-sm" id="btn-confirm-cancel">
                            <i class="fas fa-check"></i> Confirm Cancel
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
        $(document).ready(function () {

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            var HOURS_PER_DAY = 8;
            var availableBalance = 0;   // saldo total karyawan terpilih

            $('#employee_id').select2({ placeholder: 'Select an employee...', allowClear: true, width: '100%' });
            $('#filter-status').select2({ placeholder: 'All', allowClear: true, width: '100%' });

            // ── Set min date = today ──
            var today = new Date().toISOString().split('T')[0];
            $('#start_date').attr('min', today);
            $('#end_date').attr('min', today);

            // ── Hitung jumlah hari (inklusif, termasuk weekend) ──
            function countDays(startStr, endStr) {
                if (!startStr || !endStr) return 0;
                var s = new Date(startStr);
                var e = new Date(endStr);
                if (e < s) return 0;
                var diff = Math.round((e - s) / (1000 * 60 * 60 * 24));
                return diff + 1;
            }

            // ── Update tampilan Total + validasi saldo ──
            function refreshTotal() {
                var startStr = $('#start_date').val();
                var endStr   = $('#end_date').val();
                var employeeId = $('#employee_id').val();
                var $total = $('#total-display');

                var days = countDays(startStr, endStr);

                if (!employeeId || days <= 0) {
                    $total.text('Pilih tanggal dulu').addClass('text-muted').removeClass('text-danger');
                    $('#btn-submit').prop('disabled', true);
                    return;
                }

                var neededHours = days * HOURS_PER_DAY;

                if (neededHours > availableBalance) {
                    $total.removeClass('text-muted')
                        .html('<span class="text-danger">' + days + ' hari = ' + neededHours +
                              ' jam — saldo tidak cukup (tersedia ' + availableBalance + ' jam)</span>');
                    $('#btn-submit').prop('disabled', true);
                } else {
                    $total.removeClass('text-muted text-danger')
                        .html('<strong>' + days + ' hari = ' + neededHours + ' jam</strong>');
                    $('#btn-submit').prop('disabled', false);
                }
            }

            // ── Saat end kosong, samakan dengan start (default 1 hari) ──
            $('#start_date').on('change', function () {
                var startStr = $(this).val();
                $('#end_date').attr('min', startStr || today);
                if (startStr && !$('#end_date').val()) {
                    $('#end_date').val(startStr);
                }
                refreshTotal();
            });
            $('#end_date').on('change', refreshTotal);

            // ── Reset form ──
            $('#btn-form-reset').on('click', function () {
                $('#formCreateLeave')[0].reset();
                $('#employee_id').val(null).trigger('change');
                $('#saldo-display').text('Select an employee first').addClass('text-muted');
                $('#saldo-info').text('System will deduct from the oldest balance first (FIFO).');
                $('#total-display').text('Pilih tanggal dulu').addClass('text-muted').removeClass('text-danger');
                availableBalance = 0;
                $('#btn-submit').prop('disabled', true);
            });

            // ── Load TOTAL TOIL balance saat pilih karyawan ──
            $('#employee_id').on('change', function () {
                var employeeId    = $(this).val();
                var $saldoDisplay = $('#saldo-display');
                var $saldoInfo    = $('#saldo-info');

                if (!employeeId) {
                    $saldoDisplay.text('Select an employee first').addClass('text-muted');
                    $saldoInfo.text('System will deduct from the oldest balance first (FIFO).');
                    availableBalance = 0;
                    refreshTotal();
                    return;
                }

                $saldoDisplay.text('Loading balance...').addClass('text-muted');

                $.get('{{ url('toil/approval/saldo') }}/' + employeeId, function (res) {
                    availableBalance = parseFloat(res.total_remaining || 0);

                    if (availableBalance <= 0) {
                        $saldoDisplay.removeClass('text-muted')
                            .html('<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> No active TOIL balance</span>');
                        $saldoInfo.text('This employee has no TOIL balance to claim.');
                        refreshTotal();
                        return;
                    }

                    var nearest = res.nearest_expiry ? ' — nearest expiry ' + res.nearest_expiry : '';
                    $saldoDisplay.removeClass('text-muted')
                        .html('<strong>' + res.total_remaining + ' hours</strong> available (' +
                              res.count + ' record' + (res.count > 1 ? 's' : '') + ')' + nearest);
                    $saldoInfo.text('Akan dipotong ' + HOURS_PER_DAY + ' jam per hari (FIFO, saldo tertua dulu).');

                    refreshTotal();
                }).fail(function (xhr) {
                    availableBalance = 0;
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load balance';
                    $saldoDisplay.removeClass('text-muted').html('<span class="text-danger">' + msg + '</span>');
                    refreshTotal();
                });
            });

            // ── DataTable History ──
            var table = $('#approval-table').DataTable({
                processing: true,
                serverSide: false,
                autoWidth: false,
                scrollX: false,
                ajax: {
                    url: '{{ route('toil.approval.data') }}',
                    type: 'GET',
                    data: function (d) {
                        d.status          = $('#filter-status').val();
                        d.start_date      = $('#filter-start-date').val();
                        d.end_date        = $('#filter-end-date').val();
                        d.employee_search = $('#filter-employee').val();
                    }
                },
                order: [[6, 'desc']],
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    emptyTable: "No TOIL Leave records found."
                },
                columns: [
                    {
                        data: 'employee_name',
                        render: function (data, type, row) {
                            return '<div class="employee-info">' +
                                '<div class="name">' + data + '</div>' +
                                '<div class="pin">PIN: ' + row.employee_pin + '</div>' +
                                '</div>';
                        }
                    },
                    { data: 'leave_date', className: 'text-center' },
                    {
                        data: 'total_days',
                        className: 'text-center',
                        render: function (data) {
                            return '<strong>' + data + ' hari</strong>';
                        }
                    },
                    {
                        data: 'hours_used',
                        className: 'text-center',
                        render: function (data) {
                            return '<strong>' + data + ' hrs</strong>';
                        }
                    },
                    {
                        data: 'reason',
                        render: function (data) {
                            if (!data) return '-';
                            return data.length > 50 ? data.substr(0, 50) + '...' : data;
                        }
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function (data) {
                            return '<span class="badge-type badge-status-' + data + '">' + data + '</span>';
                        }
                    },
                    { data: 'created_at', className: 'text-center' },
                    {
                        data: 'id',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            if (row.can_cancel) {
                                return '<button class="btn btn-sm btn-danger btn-cancel" data-id="' + data + '" title="Cancel">' +
                                    '<i class="fas fa-times"></i> Cancel</button>';
                            }
                            return '<span class="text-muted">-</span>';
                        }
                    }
                ]
            });

            $('#btn-filter').on('click', function () { table.ajax.reload(); });

            $('#btn-reset').on('click', function () {
                $('#filter-status').val('').trigger('change');
                $('#filter-start-date').val('');
                $('#filter-end-date').val('');
                $('#filter-employee').val('');
                table.ajax.reload();
            });

            $('#filter-employee').on('keypress', function (e) {
                if (e.which === 13) table.ajax.reload();
            });

            $('#formCreateLeave').on('keydown', function (e) {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                }
            });

            // ── Submit Create TOIL Leave ──
            $('#formCreateLeave').on('submit', function (e) {
                e.preventDefault();

                var startStr = $('#start_date').val();
                var endStr   = $('#end_date').val();
                var days     = countDays(startStr, endStr);

                if (days <= 0) {
                    Swal.fire({ icon: 'error', title: 'Tanggal tidak valid', text: 'Pastikan End Date tidak sebelum Start Date.' });
                    return;
                }

                var neededHours = days * HOURS_PER_DAY;

                var formData = {
                    employee_id: $('#employee_id').val(),
                    start_date:  startStr,
                    end_date:    endStr,
                    reason:      $('#reason').val()
                };

                Swal.fire({
                    title: 'Confirm Submission',
                    html: 'TOIL Leave <strong>' + days + ' hari (' + neededHours + ' jam)</strong> akan langsung Approved. ' +
                          'Saldo dipotong & roster jadi TOIL Off. Lanjutkan?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#5e72e4',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, Submit',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    var $btn = $('#btn-submit');
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                    $.ajax({
                        url:    '{{ route('toil.approval.store') }}',
                        method: 'POST',
                        data:   formData,
                        success: function (res) {
                            $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit & Approve');
                            Swal.fire({ icon: 'success', title: 'Success', text: res.message });

                            $('#formCreateLeave')[0].reset();
                            $('#employee_id').val(null).trigger('change');
                            $('#saldo-display').text('Select an employee first').addClass('text-muted');
                            $('#saldo-info').text('System will deduct from the oldest balance first (FIFO).');
                            $('#total-display').text('Pilih tanggal dulu').addClass('text-muted').removeClass('text-danger');
                            availableBalance = 0;
                            $('#btn-submit').prop('disabled', true);
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit & Approve');
                            var msg = 'An error occurred';
                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                } else if (xhr.responseJSON.errors) {
                                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                }
                            }
                            Swal.fire({ icon: 'error', title: 'Failed', html: msg });
                        }
                    });
                });
            });

            // ── Open Modal Cancel ──
            $('#approval-table').on('click', '.btn-cancel', function () {
                var id = $(this).data('id');
                $('#cancel_id').val(id);
                $('#cancel_reason').val('');
                $('#modalCancel').modal('show');
            });

            // ── Submit Cancel ──
            $('#formCancel').on('submit', function (e) {
                e.preventDefault();
                var id     = $('#cancel_id').val();
                var reason = $('#cancel_reason').val();

                var $btnCancel = $('#btn-confirm-cancel');
                $btnCancel.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url:    '{{ url('toil/approval') }}/' + id + '/cancel',
                    method: 'POST',
                    data:   { _method: 'PUT', cancel_reason: reason },
                    success: function (res) {
                        $btnCancel.prop('disabled', false).html('<i class="fas fa-check"></i> Confirm Cancel');
                        Swal.fire({ icon: 'success', title: 'Success', text: res.message });
                        $('#modalCancel').modal('hide');
                        table.ajax.reload();
                    },
                    error: function (xhr) {
                        $btnCancel.prop('disabled', false).html('<i class="fas fa-check"></i> Confirm Cancel');
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to cancel';
                        Swal.fire({ icon: 'error', title: 'Failed', text: msg });
                    }
                });
            });

        });
    </script>
@endpush