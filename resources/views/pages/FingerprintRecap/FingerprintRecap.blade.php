@extends('layouts.app')
@section('title', 'Fingerprint Recap')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
<style>
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
    .table-responsive { padding: 0 1.5rem; overflow: hidden; }
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
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .table tbody tr { transition: all 0.25s ease; }
    .table tbody tr:hover { background-color: rgba(94, 114, 228, 0.03); }
    .table tbody td {
        padding: 1.1rem 0.75rem;
        vertical-align: middle;
        color: #4a5568;
        font-size: 0.85rem;
        border: none;
        background: #fff;
    }
    .btn-primary { background-color: #5e72e4; border-color: #5e72e4; transition: all 0.3s ease; }
    .btn-primary:hover { background-color: #4a5bd1; border-color: #4a5bd1; transform: translateY(-1px); }
    .section-header h1 { font-weight: 600; color: #2d3748; font-size: 1.5rem; }
    .badge-hari {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
        border-radius: 5px;
        padding: 3px 10px;
        font-weight: 700;
        font-size: 12px;
    }
</style>

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Fingerprint Recap</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-calendar-check"></i> Summary Hari Kerja Karyawan</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2 align-items-end">
                                <div class="col-md-2">
                                    <label for="store_name">Filter By Location</label>
                                    <select id="store_name" class="form-control select2">
                                        <option value="">All Stores</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store }}">{{ $store }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="startDate">Start Date</label>
                                    <input type="date" id="startDate" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label for="endDate">End Date</label>
                                    <input type="date" id="endDate" class="form-control">
                                </div>
                                <div class="col-md-1">
                                    <div id="custom-length"></div>
                                </div>
                                <div class="col-md-2">
                                    <div id="custom-search"></div>
                                </div>
                                <div class="col-md-1">
                                    <button id="filterBtn" class="btn btn-primary">Filter</button>
                                    <button id="resetBtn" class="btn btn-secondary">Reset</button>
                                </div>
                                <br><br><br>
                                <div class="col-md-2" id="custom-buttons"></div>
                            </div>
                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-hover" id="recap-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Nama</th>
                                            <th class="text-center">Store</th>
                                            <th class="text-center">Total Hari Kerja</th>
                                            <th class="text-center">Periode</th>
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
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const fmt = (d) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        const start = new Date(today.getFullYear(), today.getMonth()-1, 26);
        const end   = new Date(today.getFullYear(), today.getMonth(), 25);
        document.getElementById('startDate').value = fmt(start);
        document.getElementById('endDate').value   = fmt(end);
    });
</script>

<script>
    $(document).ready(function() {
        $('.select2').select2();

        var table = $('#recap-table').DataTable({
            paging: true,
            processing: true,
            autoWidth: false,
            serverSide: true,
            responsive: true,
            dom: "<'d-none'lf>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row mt-2'<'col-sm-12'B>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                {
                    extend: 'csv',
                    className: 'btn btn-sm btn-success',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                },
                {
                    extend: 'excel',
                    className: 'btn btn-sm btn-info',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                }
            ],
            ajax: {
                url: '{{ route('fingerprint-recap.data') }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: function(d) {
                    d.start_date = $('#startDate').val();
                    d.end_date   = $('#endDate').val();
                    d.store_name = $('#store_name').val();
                }
            },
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: { search: "_INPUT_", searchPlaceholder: "Search..." },
            columns: [
                { data: 'employee_name', name: 'employee_name', className: 'text-center' },
                { data: 'store_name',    name: 'store_name',    className: 'text-center' },
                {
                    data: 'total_hari',
                    name: 'total_hari',
                    className: 'text-center',
                    render: function(data) {
                        if (!data || data === '0 Hari') return '<span class="text-muted">-</span>';
                        return '<span class="badge-hari">' + data + '</span>';
                    }
                },
                {
                    data: 'start_date',
                    name: 'start_date',
                    className: 'text-center',
                    render: function(data, type, row) {
                        return row.start_date + ' s/d ' + row.end_date;
                    }
                },
            ],
            initComplete: function() {
                $('#custom-length').html($('.dataTables_length'));
                $('#custom-search').html($('.dataTables_filter'));
                table.buttons().container().appendTo('#custom-buttons');
            }
        });

        $('#filterBtn').on('click', function() { table.ajax.reload(); });
        $('#resetBtn').on('click', function() {
            $('#store_name').val('').trigger('change');
            $('#startDate').val('');
            $('#endDate').val('');
            table.ajax.reload();
        });

        setInterval(function() {
            if ($('.dataTables_filter input').val().trim().length === 0) {
                table.ajax.reload(null, false);
            }
        }, 100000);
    });
</script>
@endpush