@extends('layouts.app')

@section('title', 'Overtime Assignment')

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

    /* ── Nonaktifkan hover effect pada card DataTable ── */
    .card-table,
    .card-table:hover {
        transform: none !important;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08) !important;
        transition: none !important;
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

    /* Form Section */
    .form-section {
        background: #fff;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
    }

    .form-section .form-group label {
        font-weight: 600;
        color: #4a5568;
        font-size: 0.85rem;
        margin-bottom: 0.35rem;
    }

    .form-section .form-control {
        font-size: 0.9rem;
    }

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

    /* Badge Styling */
    .badge-type {
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.375rem;
    }

    .badge-cash            { background-color: #d1fae5; color: #065f46; }
    .badge-toil            { background-color: #dbeafe; color: #1e40af; }
    .badge-status-Approved { background-color: #d1fae5; color: #065f46; }
    .badge-status-Pending  { background-color: #fef3c7; color: #92400e; }
    .badge-status-Rejected { background-color: #fee2e2; color: #991b1b; }

    /* Table Styles */
    .table-responsive {
        padding: 0 1.5rem;
        overflow-x: auto;
    }

    .table { width: auto !important; border-collapse: separate; border-spacing: 0; }

    .table thead th {
        background-color: #f8fafc;
        color: #4a5568;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 0.75rem 0.65rem;
        white-space: nowrap;
    }

    .table tbody tr { transition: all 0.25s ease; }

    .table tbody tr:hover { background-color: rgba(94, 114, 228, 0.03); }

    .table tbody td {
        padding: 0.75rem 0.65rem;
        vertical-align: middle;
        color: #4a5568;
        font-size: 0.85rem;
        border: none;
        background: #fff;
        white-space: nowrap;
    }

    .text-center { text-align: center; }

    .section-header h1 {
        font-weight: 600;
        color: #2d3748;
        font-size: 1.5rem;
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

    .btn-danger {
        background-color: #ef4444;
        border-color: #ef4444;
    }

    /* ── Employee info ── */
    .employee-info { text-align: left; }
    .employee-info .name { font-weight: 600; color: #2d3748; }

    /* ── Row hover effect seperti Approval ── */
    #assignment-table tbody tr:hover {
        background-color: #f8f9fa !important;
        cursor: pointer;
    }

    /* ── Select2: multi-select (Select Employee) ── */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        min-height: 38px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #5e72e4;
        color: white;
        border: none;
        padding: 0.15rem 0.5rem;
        font-size: 0.85rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 0.3rem;
    }

    /* ── Select2: single-select (Compensation Type, Filter Type & Status) ── */
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
        padding-right: 2.5rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px;
        top: 0;
        right: 0;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af;
    }

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

    /* ── Processing button state ── */
    button:disabled {
        opacity: 0.75;
        cursor: not-allowed;
    }

    @keyframes fa-spin {
        0%   { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .fa-spin {
        animation: fa-spin 1s infinite linear;
    }

    /* ── Auto-calculated hours indicator ── */
    .total-hours-auto {
        border-color: #5e72e4 !important;
        background-color: #f0f4ff !important;
    }

    .hours-hint {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
</style>

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Overtime Assignment</h1>
                <div class="section-header-breadcrumb">
                    <span class="text-muted">Manager: {{ $manager->employee_name ?? '-' }}</span>
                </div>
            </div>

            <div class="section-body">

                {{-- ════════════════════════════════════════ --}}
                {{-- Form Assign Overtime                      --}}
                {{-- ════════════════════════════════════════ --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h6><i class="fas fa-plus-circle"></i> Assign New Overtime</h6>
                    </div>
                    <div class="card-body">
                        <form id="formAssignment">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Select Employee <span class="text-danger">*</span></label>
                                        <select class="form-control" name="employee_ids[]" id="employee_ids"
                                            multiple required>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp->id }}">
                                                    {{ $emp->employee_name }} ({{ $emp->pin ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            Can select more than 1 employee. Total subordinates: <strong>{{ $employees->count() }}</strong>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="date" id="date" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Start Time</label>
                                        <input type="time" class="form-control" name="start_time" id="start_time">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>End Time</label>
                                        <input type="time" class="form-control" name="end_time" id="end_time">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Total Hours <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="total_hours" id="total_hours"
                                            step="0.5" min="0.5" max="24" required>
                                        <small class="form-text hours-hint text-muted" id="hours-hint">
                                            0.5 - 24 hours. Auto-calculated from Start & End Time.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Compensation Type <span class="text-danger">*</span></label>
                                        <select name="compensation_type" id="compensation_type" required>
                                            <option value="">-- Choose Type --</option>
                                            <option value="Cash">Cash (Auto to Payroll)</option>
                                            <option value="Toil">Toil Leave (Holiday Balance)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label>Reasons/Explanations <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="reason" id="reason" rows="2" required
                                            minlength="10" maxlength="1000" placeholder="Min 10 characters"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 text-right">
                                    <button type="reset" class="btn btn-secondary btn-sm" id="btn-form-reset">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-sm" id="btn-submit">
                                        <i class="fas fa-paper-plane"></i> Submit Assignment
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ════════════════════════════════════════ --}}
                {{-- Filter Bar                                --}}
                {{-- ════════════════════════════════════════ --}}
                <div class="filter-card">
                    <h6 style="font-weight: 600; color: #4a5568; margin-bottom: 1rem;">
                        <i class="fas fa-filter"></i> Filter History
                    </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" class="form-control form-control-sm" id="filter-start-date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" class="form-control form-control-sm" id="filter-end-date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type</label>
                                <select id="filter-type">
                                    <option value="">All</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Toil">Toil</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select id="filter-status">
                                    <option value="">All</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
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

                {{-- ════════════════════════════════════════ --}}
                {{-- History Assignment Table                  --}}
                {{-- ════════════════════════════════════════ --}}
                <div class="card card-table">
                    <div class="card-header">
                        <h6><i class="fas fa-history"></i> History Assignment</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="assignment-table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th class="text-center">Overtime Date</th>
                                        <th class="text-center">Hours</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Type</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Status Balance</th>
                                        <th class="text-center">Remaining Hours</th>
                                        <th class="text-center">Expired</th>
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

    {{-- ════════════════════════════════════════ --}}
    {{-- Modal Edit Assignment                     --}}
    {{-- ════════════════════════════════════════ --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formEdit">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit"></i> Edit Assignment
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" class="form-control" id="edit_date" name="date">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Time</label>
                                    <input type="time" class="form-control" id="edit_start_time" name="start_time">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Time</label>
                                    <input type="time" class="form-control" id="edit_end_time" name="end_time">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Total Hours</label>
                            <input type="number" class="form-control" id="edit_total_hours" name="total_hours"
                                step="0.5" min="0.5" max="24">
                            <small class="form-text hours-hint text-muted" id="edit-hours-hint">
                                Cannot be less than already used. Auto-calculated from Start & End Time.
                            </small>
                        </div>
                        <div class="form-group">
                            <label>Reason</label>
                            <textarea class="form-control" id="edit_reason" name="reason" rows="2" minlength="10"
                                maxlength="1000"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btn-update">
                            <i class="fas fa-save"></i> Update
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

            // ── Init Select2: multi-select Employee (form) ──
            $('#employee_ids').select2({
                placeholder: 'Select one or more employees...',
                allowClear: true,
                width: '100%'
            });

            // ── Init Select2: Compensation Type (form) ──
            $('#compensation_type').select2({
                placeholder: '-- Choose Type --',
                allowClear: true,
                width: '100%'
            });

            // ── Init Select2: filter Type ──
            $('#filter-type').select2({
                placeholder: 'All',
                allowClear: true,
                width: '100%'
            });

            // ── Init Select2: filter Status ──
            $('#filter-status').select2({
                placeholder: 'All',
                allowClear: true,
                width: '100%'
            });

            // ── Reset form (termasuk Select2) ──
            $('#btn-form-reset').on('click', function () {
                $('#formAssignment')[0].reset();
                $('#employee_ids').val(null).trigger('change');
                $('#compensation_type').val('').trigger('change');
                $('#total_hours').removeClass('total-hours-auto');
                $('#hours-hint').text('0.5 - 24 hours. Auto-calculated from Start & End Time.')
                    .removeClass('text-success text-danger')
                    .addClass('text-muted');
            });

            // ════════════════════════════════════════
            // AUTO HITUNG TOTAL HOURS
            // ════════════════════════════════════════
            function autoCalcHours(startId, endId, totalId, hintId) {
                var start = $(startId).val();
                var end   = $(endId).val();

                if (!start || !end) return;

                var startParts   = start.split(':');
                var endParts     = end.split(':');
                var startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
                var endMinutes   = parseInt(endParts[0])   * 60 + parseInt(endParts[1]);

                if (endMinutes <= startMinutes) {
                    endMinutes += 24 * 60;
                }

                var diffMinutes = endMinutes - startMinutes;
                var diffHours   = diffMinutes / 60;
                var rounded     = Math.round(diffHours * 2) / 2;

                if (rounded < 0.5) {
                    $(hintId).text('End time must be after start time.')
                        .removeClass('text-muted text-success')
                        .addClass('text-danger');
                    return;
                }

                $(totalId).val(rounded).addClass('total-hours-auto');
                $(hintId).text('Auto-calculated: ' + start + ' → ' + end + ' = ' + rounded + ' hours.')
                    .removeClass('text-muted text-danger')
                    .addClass('text-success');
            }

            $('#start_time, #end_time').on('change', function () {
                autoCalcHours('#start_time', '#end_time', '#total_hours', '#hours-hint');
            });

            $('#edit_start_time, #edit_end_time').on('change', function () {
                autoCalcHours('#edit_start_time', '#edit_end_time', '#edit_total_hours', '#edit-hours-hint');
            });

            // ── Initialize DataTable ──
            var table = $('#assignment-table').DataTable({
                processing: true,
                serverSide: false,
                autoWidth: true,
                scrollX: true,
                ajax: {
                    url: '{{ route('toil.assignment.data') }}',
                    type: 'GET',
                    data: function (d) {
                        d.start_date        = $('#filter-start-date').val();
                        d.end_date          = $('#filter-end-date').val();
                        d.compensation_type = $('#filter-type').val();
                        d.status            = $('#filter-status').val();
                    }
                },
                responsive: true,
                order: [[1, 'desc']],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    emptyTable: "No overtime assignments found."
                },
                columns: [
                    {
                        data: 'employee_name',
                        render: function (data) {
                            return '<div class="employee-info">' +
                                '<div class="name">' + (data ?? '-') + '</div>' +
                                '</div>';
                        }
                    },
                    { data: 'date',        className: 'text-center' },
                    { data: 'time_range',  className: 'text-center' },
                    { data: 'total_hours', className: 'text-center' },
                    {
                        data: 'compensation_type',
                        className: 'text-center',
                        render: function (data) {
                            var cls = data === 'Cash' ? 'badge-cash' : 'badge-toil';
                            return '<span class="badge-type ' + cls + '">' + data + '</span>';
                        }
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function (data) {
                            return '<span class="badge-type badge-status-' + data + '">' + data + '</span>';
                        }
                    },
                    { data: 'balance_status',  className: 'text-center' },
                    { data: 'remaining_hours', className: 'text-center' },
                    { data: 'expires_at',      className: 'text-center' },
                    {
                        data: 'id',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            var buttons = '';
                            if (row.status === 'Approved') {
                                buttons += '<button class="btn btn-sm btn-primary btn-edit" data-id="' + data + '" title="Edit">' +
                                    '<i class="fas fa-edit"></i></button> ';
                                buttons += '<button class="btn btn-sm btn-danger btn-cancel" data-id="' + data + '" title="Cancel">' +
                                    '<i class="fas fa-times"></i> Cancel</button>';
                            }
                            return buttons || '<span class="text-muted">-</span>';
                        }
                    }
                ]
            });

            // ── Apply Filter ──
            $('#btn-filter').on('click', function () {
                table.ajax.reload();
            });

            // ── Reset Filter ──
            $('#btn-reset').on('click', function () {
                $('#filter-start-date').val('');
                $('#filter-end-date').val('');
                $('#filter-type').val('').trigger('change');
                $('#filter-status').val('').trigger('change');
                table.ajax.reload();
            });

            // ── Submit Assignment ──
            $('#formAssignment').on('submit', function (e) {
                e.preventDefault();

                var $btn = $('#btn-submit');
                $btn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                var formData = {
                    employee_ids:      $('#employee_ids').val(),
                    date:              $('#date').val(),
                    start_time:        $('#start_time').val() || null,
                    end_time:          $('#end_time').val() || null,
                    total_hours:       $('#total_hours').val(),
                    compensation_type: $('#compensation_type').val(),
                    reason:            $('#reason').val()
                };

                $.ajax({
                    url:    '{{ route('toil.assignment.store') }}',
                    method: 'POST',
                    data:   formData,
                    success: function (res) {
                        $btn.prop('disabled', false)
                            .html('<i class="fas fa-paper-plane"></i> Submit Assignment');

                        Swal.fire({ icon: 'success', title: 'Success', text: res.message });
                        $('#formAssignment')[0].reset();
                        $('#employee_ids').val(null).trigger('change');
                        $('#compensation_type').val('').trigger('change');
                        $('#total_hours').removeClass('total-hours-auto');
                        $('#hours-hint').text('0.5 - 24 hours. Auto-calculated from Start & End Time.')
                            .removeClass('text-success text-danger')
                            .addClass('text-muted');
                        table.ajax.reload();
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false)
                            .html('<i class="fas fa-paper-plane"></i> Submit Assignment');

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

            // ── Edit Assignment (open modal) ──
            $('#assignment-table').on('click', '.btn-edit', function () {
                var id      = $(this).data('id');
                var rowData = table.row($(this).closest('tr')).data();

                $('#edit_id').val(id);
                $('#edit_date').val(rowData.date_raw || '');
                $('#edit_start_time').val(rowData.start_time_raw || '');
                $('#edit_end_time').val(rowData.end_time_raw || '');
                $('#edit_total_hours').val(parseFloat(rowData.total_hours));
                $('#edit_reason').val(rowData.reason || '');

                $('#edit_total_hours').removeClass('total-hours-auto');
                $('#edit-hours-hint').text('Cannot be less than already used. Auto-calculated from Start & End Time.')
                    .removeClass('text-success text-danger')
                    .addClass('text-muted');

                $('#modalEdit').modal('show');
            });

            // ── Submit Edit ──
            $('#formEdit').on('submit', function (e) {
                e.preventDefault();
                var id = $('#edit_id').val();

                var $btnUpdate = $('#btn-update');
                $btnUpdate.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                var formData = {
                    _method:     'PUT',
                    date:        $('#edit_date').val(),
                    start_time:  $('#edit_start_time').val() || null,
                    end_time:    $('#edit_end_time').val() || null,
                    total_hours: $('#edit_total_hours').val(),
                    reason:      $('#edit_reason').val()
                };

                $.ajax({
                    url:    '{{ url('toil/assignment') }}/' + id,
                    method: 'POST',
                    data:   formData,
                    success: function (res) {
                        $btnUpdate.prop('disabled', false)
                            .html('<i class="fas fa-save"></i> Update');

                        Swal.fire({ icon: 'success', title: 'Success', text: res.message });
                        $('#modalEdit').modal('hide');
                        table.ajax.reload();
                    },
                    error: function (xhr) {
                        $btnUpdate.prop('disabled', false)
                            .html('<i class="fas fa-save"></i> Update');

                        var msg = xhr.responseJSON?.message || 'Failed to update';
                        Swal.fire({ icon: 'error', title: 'Failed', text: msg });
                    }
                });
            });

            // ── Cancel Assignment ──
            $('#assignment-table').on('click', '.btn-cancel', function () {
                var id = $(this).data('id');

                Swal.fire({
                    title: 'Cancel Assignment?',
                    text: 'The TOIL/Cash balance will be cancelled (if not yet used)',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, Cancel',
                    cancelButtonText: 'Back'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url:    '{{ url('toil/assignment') }}/' + id,
                            method: 'DELETE',
                            success: function (res) {
                                Swal.fire({ icon: 'success', title: 'Success', text: res.message });
                                table.ajax.reload();
                            },
                            error: function (xhr) {
                                var msg = xhr.responseJSON?.message || 'Failed to cancel';
                                Swal.fire({ icon: 'error', title: 'Failed', text: msg });
                            }
                        });
                    }
                });
            });

            // ── Set min date = today ──
            var today = new Date().toISOString().split('T')[0];
            $('#date').attr('min', today);

        });
    </script>
@endpush