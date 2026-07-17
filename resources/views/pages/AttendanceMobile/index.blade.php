@extends('layouts.app')

@section('title', 'Attendance Log')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/datatables/media/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Attendance Log</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route(getDashboardRoute()) }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Attendance Log</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Attendance Log</h4>
                        <div class="card-header-action">
                            <span class="badge badge-primary" id="total-records">Loading...</span>
                        </div>
                    </div>
                    <div class="card-body">

                        {{-- Filter --}}
                        <div class="row mb-3">
                            <div class="col-12 col-md-3 mb-2">
                                <label class="font-weight-bold text-sm">Date From</label>
                                <input type="date" id="filterDateFrom"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="font-weight-bold text-sm">Date To</label>
                                <input type="date" id="filterDateTo"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="font-weight-bold text-sm">Type</label>
                                <select id="filterType" class="form-control form-control-sm select2">
                                    <option value="">All Type</option>
                                    <option value="checkin">Check In</option>
                                    <option value="checkout">Check Out</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="font-weight-bold text-sm">Status</label>
                                <select id="filterStatus" class="form-control form-control-sm select2">
                                    <option value="">All Status</option>
                                    <option value="approved">Approved</option>
                                    <option value="flagged">Flagged</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="font-weight-bold text-sm">Location</label>
                                <select id="filterStore" class="form-control form-control-sm select2">
                                    <option value="">All Location</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="font-weight-bold text-sm">Employee</label>
                                <select id="filterEmployee" class="form-control form-control-sm select2">
                                    <option value="">All Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->employee_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3 mb-2 d-flex align-items-end gap-2">
                                <button id="btnFilter" class="btn btn-primary btn-sm mr-2">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <button id="btnReset" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>

                        {{-- Table --}}
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="attendance-table" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Location</th>
                                        <th>Type</th>
                                        <th>Work Date</th>
                                        <th>Logged At</th>
                                        <th>Geofence</th>
                                        <th>Mock Location</th>
                                        <th>Liveness</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('library/select2/dist/js/select2.min.js') }}"></script>

    <script>
        $(document).ready(function () {

            // Init Select2
            $('.select2').select2({ width: '100%' });

            // Init DataTable
            const table = $('#attendance-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('attendancemobiles.attendancemobiles') }}',
                    data: function (d) {
                        d.date_from   = $('#filterDateFrom').val();
                        d.date_to     = $('#filterDateTo').val();
                        d.type        = $('#filterType').val();
                        d.status      = $('#filterStatus').val();
                        d.store_id    = $('#filterStore').val();
                        d.employee_id = $('#filterEmployee').val();
                    },
                    dataSrc: function (json) {
                        $('#total-records').text(json.recordsTotal + ' Records');
                        return json.data;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex',             name: 'DT_RowIndex',             orderable: false, searchable: false, className: 'text-center' },
                    { data: 'employee_name',            name: 'employee_name',            className: 'text-center' },
                    { data: 'store_name',               name: 'store_name',               className: 'text-center' },
                    { data: 'type_label',               name: 'type',                     className: 'text-center', orderable: false },
                    { data: 'work_date',                name: 'attendance_logs.work_date', className: 'text-center' },
                    { data: 'logged_at',                name: 'attendance_logs.logged_at', className: 'text-center' },
                    { data: 'is_within_geofence_label', name: 'is_within_geofence',       className: 'text-center', orderable: false, searchable: false },
                    { data: 'is_mock_location_label',   name: 'is_mock_location',         className: 'text-center', orderable: false, searchable: false },
                    { data: 'liveness_passed_label',    name: 'liveness_passed',          className: 'text-center', orderable: false, searchable: false },
                    { data: 'status_label',             name: 'status',                   className: 'text-center', orderable: false, searchable: false },
                    { data: 'action',                   name: 'action',                   className: 'text-center', orderable: false, searchable: false },
                ],
                order: [[5, 'desc']], // default sort by logged_at desc
                pageLength: 25,
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
                    emptyTable: 'No attendance log found',
                    zeroRecords: 'No records match your filter',
                },
            });

            // Filter button
            $('#btnFilter').on('click', function () {
                table.ajax.reload();
            });

            // Reset button
            $('#btnReset').on('click', function () {
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                $('#filterType').val('').trigger('change');
                $('#filterStatus').val('').trigger('change');
                $('#filterStore').val('').trigger('change');
                $('#filterEmployee').val('').trigger('change');
                table.ajax.reload();
            });

        });
    </script>
@endpush