@extends('layouts.app')
@section('title', 'Fingerprint Recap')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Attendance Recap</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-calendar-check"></i> Summary Employee Attendance</h6>
                        </div>
                        <div class="card-body">

                            {{-- Filter Row --}}
                            <div class="row mb-3 align-items-end g-2">
                                <div class="col-md-2">
                                    <label class="form-label">Filter By Location</label>
                                    <select id="store_name" class="form-control select2">
                                        <option value="">All Locations</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store }}">{{ $store }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Filter By Statuses</label>
                                    <select id="status_name" class="form-control select2">
                                        <option value="">All Statuses</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" id="startDate" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">End Date</label>
                                    <input type="date" id="endDate" class="form-control">
                                </div>
                                <div class="col-md-auto">
                                    <button id="filterBtn" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <button id="resetBtn" class="btn btn-secondary ml-1">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </div>
                                <div class="col-md-auto ml-auto">
                                    {{-- Export Button (Maatwebsite) --}}
                                    <button id="exportBtn" class="btn btn-success">
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </button>
                                </div>
                            </div>

                            {{-- DataTable --}}
                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-hover" id="recap-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">NIP</th>
                                            <th class="text-center">Location</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Attendance Days</th>
                                            <th class="text-center">Total Days Late</th>
                                            <th class="text-center">Remarks</th>
                                            <th class="text-center">Period Start</th>
                                            <th class="text-center">Period End</th>
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

<script>
    {{-- Default date range: 26 bulan lalu s/d 25 bulan ini --}}
    document.addEventListener('DOMContentLoaded', function () {
        const today = new Date();
        const fmt   = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        document.getElementById('startDate').value = fmt(new Date(today.getFullYear(), today.getMonth()-1, 26));
        document.getElementById('endDate').value   = fmt(new Date(today.getFullYear(), today.getMonth(), 25));
    });
</script>

<script>
$(document).ready(function () {

    $('.select2').select2();

    // var table = $('#recap-table').DataTable({
    //     processing : true,
    //     serverSide : true,
    //     autoWidth  : false,
    //     responsive : true,
    //     dom        : "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>>" +
    //                  "<'row'<'col-sm-12'tr>>" +
    //                  "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    //     ajax: {
    //         url    : '{{ route('fingerprint-recap.data') }}',
    //         type   : 'POST',
    //         headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    //         data   : function (d) {
    //             d.start_date = $('#startDate').val();
    //             d.end_date   = $('#endDate').val();
    //             d.store_name = $('#store_name').val();
    //         }
    //     },
    //     lengthMenu : [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
    //     language   : { search: '_INPUT_', searchPlaceholder: 'Search...' },
    //     columns: [
    //         {
    //             data: null, name: 'no', orderable: false, searchable: false,
    //             className: 'text-center',
    //             render: function (data, type, row, meta) {
    //                 return meta.row + meta.settings._iDisplayStart + 1;
    //             }
    //         },
    //         { data: 'employee_name',    name: 'employee_name',    className: 'text-center' },
    //         { data: 'employee_pengenal',name: 'employee_pengenal',className: 'text-center' },
    //         { data: 'store_name',       name: 'store_name',       className: 'text-center' },
    //         {
    //             data: 'total_hari', name: 'total_hari', className: 'text-center',
    //             render: data => `<span class="badge-hari">${data ?? '0 hari'}</span>`
    //         },
    //         {
    //             data: 'total_hari_telat', name: 'total_hari_telat', className: 'text-center',
    //             render: data => `<span style="color:#991b1b;font-weight:700">${data ?? '0 hari'}</span>`
    //         },
    //         {
    //             data: 'remarks', name: 'remarks', className: 'text-center',
    //             render: data => (!data || data === '-')
    //                 ? '<span class="text-muted">-</span>'
    //                 : `<span style="color:#991b1b;font-weight:600">${data}</span>`
    //         },
    //         { data: 'period_in',  name: 'period_in',  className: 'text-center' },
    //         { data: 'period_out', name: 'period_out', className: 'text-center',
    //           render: data => data || '-' },
    //     ],
    // });

    // $('#filterBtn').on('click', function () { table.ajax.reload(); });

    // $('#resetBtn').on('click', function () {
    //     $('#store_name').val('').trigger('change');
    //     $('#startDate').val('');
    //     $('#endDate').val('');
    //     table.ajax.reload();
    // });

    // {{-- Export via Maatwebsite (server-side) --}}
    // $('#exportBtn').on('click', function () {
    //     const params = new URLSearchParams({
    //         start_date : $('#startDate').val(),
    //         end_date   : $('#endDate').val(),
    //         store_name : $('#store_name').val(),
    //     });
    //     window.location.href = '{{ route('fingerprint-recap.export') }}?' + params.toString();
    // });
    var table = $('#recap-table').DataTable({
    processing : true,
    serverSide : true,
    autoWidth  : false,
    responsive : true,
    dom        : "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    ajax: {
        url    : '{{ route('fingerprint-recap.data') }}',
        type   : 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        data   : function (d) {
            d.start_date = $('#startDate').val();
            d.end_date   = $('#endDate').val();
            d.store_name = $('#store_name').val();
            d.status_name = $('#status_name').val();
        }
    },
    lengthMenu : [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
    language   : { 
        search: '_INPUT_', 
        searchPlaceholder: 'Search...',
        processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
    },
    columns: [
        {
            data: null, name: 'no', orderable: false, searchable: false,
            className: 'text-center',
            render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
        { data: 'employee_name',     name: 'employee_name',    className: 'text-center' },
        { data: 'employee_pengenal', name: 'employee_pengenal',className: 'text-center' },
        { data: 'store_name',        name: 'store_name',       className: 'text-center' },
        { data: 'status_name',        name: 'status_name',       className: 'text-center' },
        {
            data: 'total_hari', name: 'total_hari', className: 'text-center',
            render: data => `<span class="badge badge-primary">${data ?? '0 hari'}</span>`
        },
        {
            data: 'total_hari_telat', name: 'total_hari_telat', className: 'text-center',
            render: data => `<span style="color:#991b1b;font-weight:700">${data ?? '0 hari'}</span>`
        },
        {
            data: 'remarks', name: 'remarks', className: 'text-center',
            render: data => (!data || data === '-')
                ? '<span class="text-muted">-</span>'
                : `<span style="color:#991b1b;font-weight:600">${data}</span>`
        },
        { data: 'period_in',  name: 'period_in',  className: 'text-center' },
        { 
            data: 'period_out', name: 'period_out', className: 'text-center',
            render: data => data || '-'
        },
    ],
});

// ── Filter & Reset ──
$('#filterBtn').on('click', function () { table.ajax.reload(); });

$('#store_name').on('change', function () { table.ajax.reload(); }); // ← auto reload saat ganti store
$('#status_name').on('change', function () { table.ajax.reload(); }); // ← auto reload saat ganti store

$('#resetBtn').on('click', function () {
    $('#store_name').val('').trigger('change');
    $('#status_name').val('').trigger('change');

    // Reset date ke default (26 bulan lalu s/d 25 bulan ini)
    const today = new Date();
    const fmt   = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    $('#startDate').val(fmt(new Date(today.getFullYear(), today.getMonth()-1, 26)));
    $('#endDate').val(fmt(new Date(today.getFullYear(), today.getMonth(), 25)));

    table.ajax.reload();
});

// ── Export ──
$('#exportBtn').on('click', function () {
    const params = new URLSearchParams({
        start_date : $('#startDate').val(),
        end_date   : $('#endDate').val(),
        store_name : $('#store_name').val(),
        status_name : $('#status_name').val(),
    });
    window.location.href = '{{ route('fingerprint-recap.export') }}?' + params.toString();
});

});
</script>
@endpush