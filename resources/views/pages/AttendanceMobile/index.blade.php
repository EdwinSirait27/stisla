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
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                data-target="#modalCreateAttendance">
                                <i class="fas fa-plus"></i> Add Manual Entry
                            </button>
                            <span class="badge badge-primary" id="total-records">Loading...</span>
                        </div>
                    </div>
                    <div class="card-body">

                        {{-- Filter --}}
                        <div class="row mb-3">
                            <div class="col-12 col-md-3 mb-2">
                                <label class="font-weight-bold text-sm">Date From</label>
                                <input type="date" id="filterDateFrom" class="form-control form-control-sm">
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="font-weight-bold text-sm">Date To</label>
                                <input type="date" id="filterDateTo" class="form-control form-control-sm">
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
                            @can('AttendanceMobile')
                                <div class="col-12 col-md-3 mb-2">
                                    <label class="font-weight-bold text-sm">Company</label>
                                    <select id="filterCompany" class="form-control form-control-sm select2">
                                        <option value="">All Company</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3 mb-2">
                                    <label class="font-weight-bold text-sm">Department</label>
                                    <select id="filterDepartment" class="form-control form-control-sm select2">
                                        <option value="">All Department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endcan
                            @canany(['AttendanceMobile', 'ManageAttendanceMobileSPVManager'])

                                <div class="col-12 col-md-3 mb-2">
                                    <label class="font-weight-bold text-sm">Location</label>
                                    <select id="filterStore" class="form-control form-control-sm select2">
                                        <option value="">All Location</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endcanany
                            <div class="col-12 col-md-3 mb-2">
                                <label class="font-weight-bold text-sm">Employee</label>
                                <select id="filterEmployee" class="form-control form-control-sm select2">
                                    <option value="">All Employee</option>
                                    @foreach ($employees as $employee)
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
                                        <th>Company</th>
                                        <th>Department</th>
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
    <div class="modal fade" id="modalCreateAttendance" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formCreateAttendance">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Manual Attendance Entry</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label>Employee <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="create_employee_id" name="employee_id" required>
                                <option value="">-- Select Employee --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="create_type" name="type" required>
                                <option value="">-- Select Type --</option>
                                <option value="checkin">Check In</option>
                                <option value="checkout">Check Out</option>
                            </select>
                        </div>

                        {{-- <div class="form-group">
                            <label>Location</label>
                            <select class="form-control select2" id="create_store_id" name="store_id">
                                <option value="">Work From Anywhere</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div> --}}
                        @canany(['ManageAttendanceMobile', 'ManageAttendanceMobileSPVManager'])
                            <div class="form-group">
                                <label>Location</label>
                                <select class="form-control select2" id="create_store_id" name="store_id">
                                    @can('ManageAttendanceMobile')
                                        <option value="">Work From Anywhere</option>
                                    @endcan

                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endcanany

                        <div class="form-group">
                            <label>Work Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="create_work_date" name="work_date" required>
                        </div>

                        <div class="form-group">
                            <label>Logged At (Date & Time) <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="create_logged_at" name="logged_at"
                                required>
                        </div>

                        @can('ManageAttendanceMobile')
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="form-control" id="create_status" name="status" required>
                                    <option value="approved">Approved</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                        @endcan

                        <div class="form-group">
                            <label>Reason / Notes</label>
                            <textarea class="form-control" id="create_flag_reason" required name="flag_reason" rows="2"
                                placeholder="Contoh: Lupa checkin, ditambahkan manual oleh HR"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Photo / Surat Pendukung</label>
                            <input type="file" class="form-control-file" name="photo_path"
                                accept=".jpg,.jpeg,.png,.pdf" required>
                            <small class="form-text text-muted">Upload foto/surat sakit/izin jika ada. Max 5MB (jpg, png,
                                pdf).</small>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="btnSaveAttendance" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('library/select2/dist/js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        $(document).ready(function() {


            $('.select2').not('#modalCreateAttendance .select2').select2({
                width: '100%'
            });

            // Init DataTable
            const table = $('#attendance-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('attendancemobiles.attendancemobiles') }}',
                    data: function(d) {
                        d.date_from = $('#filterDateFrom').val();
                        d.date_to = $('#filterDateTo').val();
                        d.type = $('#filterType').val();
                        d.status = $('#filterStatus').val();
                        d.store_id = $('#filterStore').val();
                        // d.employee_id = $('#filterEmployee').val();
                        d.company_id = $('#filterCompany').val();
                        d.department_id = $('#filterDepartment').val();
                    },
                    dataSrc: function(json) {
                        $('#total-records').text(json.recordsTotal + ' Records');
                        return json.data;
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        className: 'text-center'
                    },
                    {
                        data: 'company_name',
                        name: 'company_name',
                        className: 'text-center'
                    },
                    {
                        data: 'department_name',
                        name: 'department_name',
                        className: 'text-center'
                    },
                    {
                        data: 'store_name',
                        name: 'store_name',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'type_label',
                        name: 'type',
                        className: 'text-center',
                        orderable: false
                    },
                    {
                        data: 'work_date',
                        name: 'attendance_logs.work_date',
                        className: 'text-center'
                    },
                    {
                        data: 'logged_at',
                        name: 'attendance_logs.logged_at',
                        className: 'text-center'
                    },
                    {
                        data: 'is_within_geofence_label',
                        name: 'is_within_geofence',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'is_mock_location_label',
                        name: 'is_mock_location',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'liveness_passed_label',
                        name: 'liveness_passed',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_label',
                        name: 'status',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [7, 'desc']
                ], // default sort by logged_at desc
                pageLength: 25,
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
                    emptyTable: 'No attendance log found',
                    zeroRecords: 'No records match your filter',
                },
            });

            // Filter button
            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            // Reset button
            $('#btnReset').on('click', function() {
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                $('#filterType').val('').trigger('change');
                $('#filterStatus').val('').trigger('change');
                $('#filterStore').val('').trigger('change');
                // $('#filterEmployee').val('').trigger('change');
                $('#filterCompany').val('').trigger('change');
                $('#filterDepartment').val('').trigger('change');
                table.ajax.reload();
            });

        });


        $('#modalCreateAttendance').on('show.bs.modal', function() {
            $('#formCreateAttendance')[0].reset();
        });
        $('#modalCreateAttendance').on('shown.bs.modal', function() {
            $('#create_employee_id, #create_store_id').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
            $('#create_employee_id').select2({
                width: '100%',
                dropdownParent: $('#modalCreateAttendance')
            });
            $('#create_store_id').select2({
                width: '100%',
                dropdownParent: $('#modalCreateAttendance')
            });
        });




        $('#formCreateAttendance').on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Simpan entry attendance?',
                text: 'Pastikan data yang diinput sudah benar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData(document.getElementById('formCreateAttendance'));

                $.ajax({
                    url: '{{ route('attendancemobile.store') }}',
                    method: 'POST',
                    data: formData,
                    processData: false, // wajib
                    contentType: false, // wajib
                    success: function(res) {
                        $('#modalCreateAttendance').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success');
                        $('#attendance-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            const msg = errors ? Object.values(errors).flat().join('\n') : (xhr
                                .responseJSON.message ?? 'Data tidak valid.');
                            Swal.fire('Validasi Gagal', msg, 'warning');
                        } else {
                            Swal.fire('Error', xhr.responseJSON?.message ??
                                'Terjadi kesalahan.', 'error');
                        }
                    }
                });
            });
        });
    </script>
@endpush
