@extends('layouts.app')
@section('title', 'Master Shifts')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Master Shifts</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6><i class="fas fa-clock"></i> List Shifts</h6>
                            <button class="btn btn-primary btn-sm" onclick="openAdd()">
                                <i class="fas fa-plus"></i> Tambah Shift
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>Filter by Store</label>
                                    <select id="filterStore" class="form-control select2">
                                        <option value="">Semua Store</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button id="filterBtn" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="shifts-table">
                                    <thead>
                                        <tr>
                                            <th>Store</th>
                                            <th>Nama Shift</th>
                                            <th>Jam Masuk</th>
                                            <th>Jam Keluar</th>
                                            <th>Action</th>
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

{{-- Modal Tambah/Edit --}}
<div class="modal fade" id="modalShift" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Shift</h5>
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="shiftId">
                <div class="form-group">
                    <label>Store / Lokasi</label>
                    <select id="shiftStore" class="form-control select2">
                        <option value="">-- Pilih Store --</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Nama Shift</label>
                    <input type="text" id="shiftName" class="form-control" placeholder="Contoh: Shift Pagi">
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Jam Masuk</label>
                            <input type="time" id="shiftStart" class="form-control">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Jam Keluar</label>
                            <input type="time" id="shiftEnd" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveShift()">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $('.select2').select2();

    var table = $('#shifts-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('shifts.data') }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: function(d) {
                d.store_id = $('#filterStore').val();
            }
        },
        columns: [
            { data: 'store_name',  name: 'store_name' },
            { data: 'shift_name',  name: 'shift_name' },
            { data: 'start_time',  name: 'start_time' },
            { data: 'end_time',    name: 'end_time' },
            { data: 'action',      name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#filterBtn').on('click', function() { table.ajax.reload(); });

    function openAdd() {
        $('#modalTitle').text('Tambah Shift');
        $('#shiftId').val('');
        $('#shiftStore').val('').trigger('change');
        $('#shiftName').val('');
        $('#shiftStart').val('');
        $('#shiftEnd').val('');
        $('#modalShift').modal('show');
    }

    // Edit
    $(document).on('click', '.btn-edit', function() {
        $('#modalTitle').text('Edit Shift');
        $('#shiftId').val($(this).data('id'));
        $('#shiftStore').val($(this).data('store')).trigger('change');
        $('#shiftName').val($(this).data('name'));
        $('#shiftStart').val($(this).data('start'));
        $('#shiftEnd').val($(this).data('end'));
        $('#modalShift').modal('show');
    });

    function saveShift() {
        const id      = $('#shiftId').val();
        const isEdit  = id !== '';
        const url     = isEdit ? `/shifts/${id}` : '{{ route('shifts.store') }}';
        const method  = isEdit ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: {
                store_id:   $('#shiftStore').val(),
                shift_name: $('#shiftName').val(),
                start_time: $('#shiftStart').val(),
                end_time:   $('#shiftEnd').val(),
            },
            success: function(res) {
                if (res.success) {
                    $('#modalShift').modal('hide');
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500 });
                }
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.' });
            }
        });
    }

    // Hapus
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Shift?',
            text: 'Data shift ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ef4444',
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/shifts/${id}`,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        table.ajax.reload();
                        Swal.fire({ icon: 'success', title: 'Dihapus!', text: res.message, timer: 1500 });
                    }
                }
            });
        });
    });
</script>
@endpush