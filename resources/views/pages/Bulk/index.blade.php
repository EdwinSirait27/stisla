@extends('layouts.app')

@section('title', 'Bulk Assign Position & Atasan')

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Bulk Assign Position & Atasan</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('pages.Employee') }}">Karyawan</a></div>
                <div class="breadcrumb-item">Bulk Assign</div>
            </div>
        </div>

        <div class="section-body">

            {{-- ── PANEL AKSI BULK ── --}}
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-users-cog mr-2"></i>Panel Aksi Massal</h4>
                    <div class="card-header-action">
                        <span class="badge badge-pill badge-light border" id="selectedCount">
                            <i class="fas fa-check-square mr-1 text-primary"></i>
                            <span id="selectedCountText">0</span> dipilih
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">

                        {{-- ── POSISI ── --}}
                        <div class="col-md-6">
                            <div class="card border-primary shadow-sm mb-0">
                                <div class="card-header bg-primary text-white py-2 px-3">
                                    <h6 class="mb-0">
                                        <i class="fas fa-briefcase mr-1"></i> Posisi
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-sm">Pilih Posisi</label>
                                        <select id="selectPosition" class="form-control select2" style="width:100%">
                                            <option value="">— Pilih Posisi —</option>
                                            @foreach($positions as $pos)
                                                <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Kosongkan saat <strong>Delete</strong> untuk hapus semua posisi karyawan terpilih.
                                        </small>
                                    </div>

                                    <div class="form-group mb-1">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="chkSetPrimaryPosition">
                                            <label class="custom-control-label" for="chkSetPrimaryPosition">
                                                Jadikan Primary
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="chkReplaceAllPosition">
                                            <label class="custom-control-label" for="chkReplaceAllPosition">
                                                Hapus posisi lama sebelum assign
                                            </label>
                                        </div>
                                    </div>

                                    <button id="btnBulkAssignPosition" class="btn btn-primary mr-2">
                                        <i class="fas fa-plus-circle mr-1"></i> Assign Posisi
                                    </button>
                                    <button id="btnBulkDeletePosition" class="btn btn-danger">
                                        <i class="fas fa-trash mr-1"></i> Delete Posisi
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ── ATASAN ── --}}
                        <div class="col-md-6 mt-3 mt-md-0">
                            <div class="card border-success shadow-sm mb-0">
                                <div class="card-header bg-success text-white py-2 px-3">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user-tie mr-1"></i> Atasan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-sm">Pilih Atasan</label>
                                        <select id="selectAtasan" class="form-control select2" style="width:100%">
                                            <option value="">— Pilih Atasan —</option>
                                            @foreach($atasanList as $atasan)
                                                <option value="{{ $atasan->id }}">
                                                    {{ $atasan->employee_name }} ({{ $atasan->employee_pengenal }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Kosongkan saat <strong>Delete</strong> untuk hapus semua atasan karyawan terpilih.
                                        </small>
                                    </div>

                                    <div class="form-group mb-1">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="chkSetPrimaryAtasan">
                                            <label class="custom-control-label" for="chkSetPrimaryAtasan">
                                                Jadikan Primary
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="chkReplaceAllAtasan">
                                            <label class="custom-control-label" for="chkReplaceAllAtasan">
                                                Hapus atasan lama sebelum assign
                                            </label>
                                        </div>
                                    </div>

                                    <button id="btnBulkAssignAtasan" class="btn btn-success mr-2">
                                        <i class="fas fa-plus-circle mr-1"></i> Assign Atasan
                                    </button>
                                    <button id="btnBulkDeleteAtasan" class="btn btn-danger">
                                        <i class="fas fa-trash mr-1"></i> Delete Atasan
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /row --}}
                </div>
            </div>{{-- /card panel --}}

            {{-- ── TABEL KARYAWAN ── --}}
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-list mr-2"></i>Daftar Karyawan</h4>
                    <div class="card-header-action d-flex align-items-center">
                        <div class="custom-control custom-checkbox mr-3">
                            <input type="checkbox" class="custom-control-input" id="selectAll">
                            <label class="custom-control-label" for="selectAll">Pilih Semua di Halaman Ini</label>
                        </div>
                    </div>
                </div>

                {{-- Filter --}}
                <div class="card-body border-bottom pb-2 pt-3">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-sm font-weight-bold">Company</label>
                                <select id="filterCompany" class="form-control form-control-sm select2" style="width:100%">
                                    <option value="">Semua Company</option>
                                    @foreach($companies ?? [] as $company)
                                        <option value="{{ $company->name }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-sm font-weight-bold">Location</label>
                                <select id="filterLocation" class="form-control form-control-sm select2" style="width:100%">
                                    <option value="">Semua Location</option>
                                    @foreach($stores ?? [] as $store)
                                        <option value="{{ $store->name }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-sm font-weight-bold">Department</label>
                                <select id="filterDepartment" class="form-control form-control-sm select2" style="width:100%">
                                    <option value="">Semua Department</option>
                                    @foreach($departments ?? [] as $department)
                                        <option value="{{ $department->department_name }}">{{ $department->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label class="text-sm font-weight-bold">Status</label>
                                <select id="filterStatus" class="form-control form-control-sm select2" style="width:100%">
                                    <option value="">Semua Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-group mb-2">
                                <button id="btnFilter" class="btn btn-info btn-sm">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                                <button id="btnResetFilter" class="btn btn-secondary btn-sm ml-1">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tableEmployees" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="40"><i class="fas fa-check-square text-muted"></i></th>
                                    <th>Pengenal</th>
                                    <th>Nama Karyawan</th>
                                    <th>Posisi Primary</th>
                                    <th>Semua Posisi</th>
                                    <th>Atasan Primary</th>
                                    <th>Company</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>{{-- /card tabel --}}

        </div>{{-- /section-body --}}
    </section>
</div>
@endsection

@push('styles')
 <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

<style>
    /* Row highlight saat dicentang */
    #tableEmployees tbody tr.row-selected td {
        background-color: rgba(63, 114, 175, 0.07) !important;
    }

    /* Selected counter badge */
    #selectedCount {
        font-size: 0.82rem;
        padding: 0.4em 0.85em;
        letter-spacing: 0.02em;
    }

    /* Tombol card header sedikit lebih rapat */
    .card-header-action .badge {
        line-height: 1.6;
    }
    .dt-filter-bar .select2-container {
            min-width: 140px;
            flex: 1 1 140px;
        }

        .select2-container--default .select2-selection--single {
            height: 32px !important;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection__rendered {
            font-size: .775rem;
        }
</style>
@endpush

@push('scripts')
{{-- SweetAlert2 (jika belum di-load global) --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function () {

    // ── Select2 ──────────────────────────────────────────────
    $('.select2').select2({ theme: 'bootstrap4' });

    // ── DataTable ────────────────────────────────────────────
    const table = $('#tableEmployees').DataTable({
        processing : true,
        serverSide : true,
        ajax: {
            url : '{{ route("Employee.positionAtasans") }}',
            data: function (d) {
                d.filter_company = $('#filterCompany').val();
                d.filter_status  = $('#filterStatus').val();
                d.filter_store  = $('#filterLocation').val();
                d.filter_department  = $('#filterDepartment').val();
            }
        },
        columns: [
            { data: 'checkbox',              name: 'checkbox',              orderable: false, searchable: false },
            { data: 'employee_pengenal',     name: 'employee_pengenal' },
            { data: 'employee_name',         name: 'employee_name' },
            { data: 'primary_position_name', name: 'primary_position_name' },
            { data: 'all_positions',         name: 'all_positions',         orderable: false, searchable: false },
            { data: 'primary_atasan',        name: 'primary_atasan',        orderable: false, searchable: false },
            { data: 'company_name',          name: 'company_name' },
            { data: 'status',                name: 'status' },
        ],
         lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
        order: [[2, 'asc']],
        language: {
            processing : '<i class="fas fa-spinner fa-spin fa-2x fa-fw text-primary"></i>',
            emptyTable : 'Tidak ada data karyawan.',
            zeroRecords: 'Karyawan tidak ditemukan.',
            search     : 'Cari:',
            lengthMenu : 'Tampilkan _MENU_ data',
            info       : 'Menampilkan _START_ - _END_ dari _TOTAL_ karyawan',
            paginate   : { previous: 'Sebelumnya', next: 'Selanjutnya' },
        },
    });

    // Reset checkbox & counter tiap draw
    table.on('draw', function () {
        $('#selectAll').prop('checked', false);
        updateCounter();
    });

    // ── Delegated: checkbox per baris ────────────────────────
    $('#tableEmployees').on('change', '.employee-checkbox', function () {
        $(this).closest('tr').toggleClass('row-selected', this.checked);

        // Sinkron Select All
        const total   = $('#tableEmployees .employee-checkbox').length;
        const checked = $('#tableEmployees .employee-checkbox:checked').length;
        $('#selectAll').prop('checked', total > 0 && total === checked);

        updateCounter();
    });

    // ── Select All (halaman aktif saja) ──────────────────────
    $('#selectAll').on('change', function () {
        const checked = this.checked;
        $('#tableEmployees .employee-checkbox').each(function () {
            $(this).prop('checked', checked).closest('tr').toggleClass('row-selected', checked);
        });
        updateCounter();
    });

    // ── Counter helper ───────────────────────────────────────
    function getCheckedIds() {
        return $('#tableEmployees .employee-checkbox:checked').map(function () {
            return $(this).val();
        }).get();
    }

    function updateCounter() {
        const n = getCheckedIds().length;
        $('#selectedCountText').text(n);
        $('#selectedCount')
            .removeClass('badge-light badge-primary')
            .addClass(n > 0 ? 'badge-primary text-white' : 'badge-light');
    }

    // ── Filter ───────────────────────────────────────────────
    $('#btnFilter').on('click', function () { table.ajax.reload(); });

    $('#btnResetFilter').on('click', function () {
        $('#filterCompany, #filterStatus, #filterLocation, #filterDepartment').val(null).trigger('change');
        table.ajax.reload();
    });

    // ── Ajax helper ──────────────────────────────────────────
    function ajaxBulk(url, extraData) {
        const ids = getCheckedIds();
        if (!ids.length) {
            Swal.fire('Perhatian', 'Pilih minimal 1 karyawan terlebih dahulu.', 'warning');
            return Promise.reject();
        }

        return $.ajax({
            url,
            method: 'POST',
            data  : { _token: '{{ csrf_token() }}', employee_ids: ids, ...extraData },
        }).then(function (res) {
            Swal.fire('Berhasil!', res.message, 'success');
            table.ajax.reload(null, false);
            $('#selectAll').prop('checked', false);
            updateCounter();
        }).catch(function (xhr) {
            Swal.fire('Error', xhr.responseJSON?.message ?? 'Terjadi kesalahan.', 'error');
        });
    }

    // ── Konfirmasi helper ────────────────────────────────────
    function confirmThen(title, html, icon, confirmText, confirmColor, cb) {
        const opts = {
            title, html, icon,
            showCancelButton : true,
            confirmButtonText: confirmText,
            cancelButtonText : 'Batal',
        };
        if (confirmColor) opts.confirmButtonColor = confirmColor;
        Swal.fire(opts).then(r => { if (r.isConfirmed) cb(); });
    }

    // ────────────────────────────────────────────────────────
    // BULK ASSIGN POSITION
    // ────────────────────────────────────────────────────────
    $('#btnBulkAssignPosition').on('click', function () {
        const positionId = $('#selectPosition').val();
        if (!positionId) return Swal.fire('Perhatian', 'Pilih posisi terlebih dahulu.', 'warning');
        const ids = getCheckedIds();
        if (!ids.length) return Swal.fire('Perhatian', 'Pilih minimal 1 karyawan.', 'warning');

        confirmThen(
            'Assign Posisi?',
            `Assign posisi ke <strong>${ids.length}</strong> karyawan terpilih?`,
            'question', 'Ya, assign!', null,
            () => ajaxBulk('{{ route("Employee.bulkAssignPosition") }}', {
                position_id:    positionId,
                set_as_primary: $('#chkSetPrimaryPosition').is(':checked') ? 1 : 0,
                replace_all:    $('#chkReplaceAllPosition').is(':checked') ? 1 : 0,
            })
        );
    });

    // ────────────────────────────────────────────────────────
    // BULK DELETE POSITION
    // ────────────────────────────────────────────────────────
    $('#btnBulkDeletePosition').on('click', function () {
        const positionId = $('#selectPosition').val() || null;
        const ids = getCheckedIds();
        if (!ids.length) return Swal.fire('Perhatian', 'Pilih minimal 1 karyawan.', 'warning');

        confirmThen(
            'Hapus Posisi?',
            positionId
                ? `Hapus posisi terpilih dari <strong>${ids.length}</strong> karyawan?`
                : `Hapus <strong>SEMUA</strong> posisi dari <strong>${ids.length}</strong> karyawan?`,
            'warning', 'Ya, hapus!', '#d33',
            () => ajaxBulk('{{ route("Employee.bulkDeletePosition") }}', {
                position_id: positionId,
            })
        );
    });

    // ────────────────────────────────────────────────────────
    // BULK ASSIGN ATASAN
    // ────────────────────────────────────────────────────────
    $('#btnBulkAssignAtasan').on('click', function () {
        const atasanId = $('#selectAtasan').val();
        if (!atasanId) return Swal.fire('Perhatian', 'Pilih atasan terlebih dahulu.', 'warning');
        const ids = getCheckedIds();
        if (!ids.length) return Swal.fire('Perhatian', 'Pilih minimal 1 karyawan.', 'warning');

        confirmThen(
            'Assign Atasan?',
            `Assign atasan ke <strong>${ids.length}</strong> karyawan terpilih?`,
            'question', 'Ya, assign!', null,
            () => ajaxBulk('{{ route("Employee.bulkAssignAtasan") }}', {
                atasan_id:      atasanId,
                set_as_primary: $('#chkSetPrimaryAtasan').is(':checked') ? 1 : 0,
                replace_all:    $('#chkReplaceAllAtasan').is(':checked') ? 1 : 0,
            })
        );
    });

    // ────────────────────────────────────────────────────────
    // BULK DELETE ATASAN
    // ────────────────────────────────────────────────────────
    $('#btnBulkDeleteAtasan').on('click', function () {
        const atasanId = $('#selectAtasan').val() || null;
        const ids = getCheckedIds();
        if (!ids.length) return Swal.fire('Perhatian', 'Pilih minimal 1 karyawan.', 'warning');

        confirmThen(
            'Hapus Atasan?',
            atasanId
                ? `Hapus atasan terpilih dari <strong>${ids.length}</strong> karyawan?`
                : `Hapus <strong>SEMUA</strong> atasan dari <strong>${ids.length}</strong> karyawan?`,
            'warning', 'Ya, hapus!', '#d33',
            () => ajaxBulk('{{ route("Employee.bulkDeleteAtasan") }}', {
                atasan_id: atasanId,
            })
        );
    });

});
</script>
@endpush