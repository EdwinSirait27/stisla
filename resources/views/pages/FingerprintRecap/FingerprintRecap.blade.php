@extends('layouts.app')
@section('title', 'Fingerprint Recap')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<style>
    .card { border:none; box-shadow:0 0.25rem 0.75rem rgba(0,0,0,.08); border-radius:.5rem; background:#fff; }
    .card-header { background:#f8fafc; border-bottom:1px solid rgba(0,0,0,.03); padding:1.25rem 1.5rem; }
    .card-header h6 { margin:0; font-weight:600; color:#4a5568; display:flex; align-items:center; font-size:.95rem; }
    .card-header h6 i { margin-right:.75rem; color:#5e72e4; }

    .table thead th {
        background:#1e293b; color:#fff; font-size:.7rem; font-weight:600;
        text-transform:uppercase; letter-spacing:.4px; padding:10px 8px;
        border:none; white-space:nowrap; text-align:center;
    }
    .table tbody td {
        padding:.9rem .75rem; vertical-align:middle;
        font-size:.85rem; color:#4a5568;
        border-top:1px solid #f1f5f9; text-align:center;
    }
    .table tbody tr:hover td { background:#f8fafc; }

    /* ── Tombol Recap ── */
    #btnRecap {
        background: linear-gradient(135deg, #10b981, #059669);
        color:#fff; border:none; border-radius:8px;
        padding:9px 22px; font-size:14px; font-weight:700;
        cursor:pointer; display:inline-flex; align-items:center; gap:8px;
        box-shadow:0 4px 12px rgba(16,185,129,.3);
        transition:all .2s;
    }
    #btnRecap:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(16,185,129,.4); }
    #btnRecap:disabled { opacity:.65; pointer-events:none; }
    #btnRecap .spinner-border { width:14px; height:14px; border-width:2px; }

    /* ── Roster Badge ── */
    .roster-badge {
        display:inline-flex; flex-direction:column; align-items:center;
        border-radius:6px; padding:3px 10px; min-width:80px;
        border:1px solid transparent;
    }
    .roster-badge .r-name { font-weight:700; font-size:12px; white-space:nowrap; }
    .roster-badge .r-time { font-size:10px; white-space:nowrap; opacity:.85; }
    .r-work    { background:#dbeafe; border-color:#93c5fd; }
    .r-work    .r-name { color:#1d4ed8; }
    .r-work    .r-time { color:#3b82f6; }
    .r-off     { background:#f1f5f9; border-color:#cbd5e1; }
    .r-off     .r-name { color:#64748b; }
    .r-holiday { background:#fef9c3; border-color:#fde047; }
    .r-holiday .r-name { color:#854d0e; }
    .r-leave   { background:#f3e8ff; border-color:#d8b4fe; }
    .r-leave   .r-name { color:#7e22ce; }
    .r-none    { color:#94a3b8; font-style:italic; font-size:12px; }
</style>
@endpush

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
                        <h6><i class="fas fa-fingerprint"></i> List Fingerprint Recap</h6>
                    </div>
                    <div class="card-body">

                        {{-- ── Filter Bar ── --}}
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-2">
                                <label>Filter By Location</label>
                                <select id="storeName" class="form-control select2">
                                    <option value="">All Stores</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store }}">{{ $store }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Start Date</label>
                                <input type="date" id="startDate" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>End Date</label>
                                <input type="date" id="endDate" class="form-control">
                            </div>
                            <div class="col-md-1">
                                <div id="custom-length"></div>
                            </div>
                            <div class="col-md-2">
                                <div id="custom-search"></div>
                            </div>
                            <div class="col-md-1 d-flex gap-1">
                                <button id="filterBtn" class="btn btn-primary">Filter</button>
                                <button id="resetBtn" class="btn btn-secondary">Reset</button>
                            </div>
                            <div class="col-md-2" id="custom-buttons"></div>
                        </div>

                        {{-- ── Tombol Fingerprint Recap Otomatis ── --}}
                        <div class="mb-3 d-flex align-items-center gap-3">
                            <button id="btnRecap" onclick="doRecap()">
                                <i class="fas fa-sync-alt"></i>
                                Fingerprint Recap Otomatis
                            </button>
                            <span id="recapMsg" class="small fw-semibold"></span>
                        </div>

                        {{-- ── Tabel ── --}}
                        <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
                            <table class="table table-hover" id="recapTable">
                                <thead>
                                    <tr>
                                        <th>Location</th>
                                        <th>PIN</th>
                                        <th>Name</th>
                                        <th>NIP</th>
                                        <th>Roster</th>  {{-- ← sebelah NIP --}}
                                        <th>Position</th>
                                        <th>Status</th>
                                        <th>Scan Date</th>
                                        <th>In</th>
                                        <th>Out</th>
                                        <th>Duration</th>
                                        <th>Synced At</th>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
// ── Default date: 26 bulan lalu → 25 bulan ini (sama seperti Fingerprint List) ──
document.addEventListener('DOMContentLoaded', function () {
    const today = new Date();
    const y = today.getFullYear(), m = today.getMonth();
    const fmt = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    document.getElementById('startDate').value = fmt(new Date(y, m-1, 26));
    document.getElementById('endDate').value   = fmt(new Date(y, m, 25));
});

$(document).ready(function () {
    $('.select2').select2();

    var table = $('#recapTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        dom: "<'d-none'lf>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-2'<'col-sm-12'B>>" +
             "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            {
                extend: 'csv', className: 'btn btn-sm btn-success',
                text: '<i class="fas fa-file-csv"></i> CSV',
                exportOptions: { columns: ':not(:last-child)' }
            },
            {
                extend: 'excel', className: 'btn btn-sm btn-info',
                text: '<i class="fas fa-file-excel"></i> Excel',
                exportOptions: { columns: ':not(:last-child)' }
            },
        ],
        ajax: {
            url: '{{ route('fingerprint-recap.data') }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: function (d) {
                d.start_date = $('#startDate').val();
                d.end_date   = $('#endDate').val();
                d.store_name = $('#storeName').val();
            },
        },
        lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
        language: { search: '_INPUT_', searchPlaceholder: 'Search...' },
        columns: [
            { data: 'location',        name: 'location',        className: 'text-center' },
            { data: 'pin',             name: 'pin',             className: 'text-center' },
            { data: 'employee_name',   name: 'employee_name',   className: 'text-center', width: '180px' },
            { data: 'nip',             name: 'nip',             className: 'text-center' },
            // ↓ KOLOM ROSTER — sebelah NIP, tampilkan shift (Pagi/Siang/Malam)
            {
                data: 'roster',
                name: 'roster',
                className: 'text-center',
                render: function (data, type, row) {
                    if (type !== 'display') return data || '';
                    if (!data || data === '-') {
                        return '<span class="r-none">-</span>';
                    }
                    var cls  = 'r-work';
                    var name = data;
                    var time = row.roster_time || '';

                    if (data === 'Off')     cls = 'r-off';
                    if (data === 'Holiday') cls = 'r-holiday';
                    if (data === 'Leave')   cls = 'r-leave';

                    return '<span class="roster-badge ' + cls + '">' +
                               '<span class="r-name">' + name + '</span>' +
                               (time ? '<span class="r-time">' + time + '</span>' : '') +
                           '</span>';
                }
            },
            { data: 'position',        name: 'position',        className: 'text-center' },
            { data: 'status_employee', name: 'status_employee', className: 'text-center' },
            { data: 'date',            name: 'date',            className: 'text-center' },
            { data: 'time_in',         name: 'time_in',         className: 'text-center', defaultContent: '-' },
            { data: 'time_out',        name: 'time_out',        className: 'text-center', defaultContent: '-' },
            { data: 'duration_format', name: 'duration_format', className: 'text-center' },
            { data: 'synced_at',       name: 'synced_at',       className: 'text-center', defaultContent: '-' },
        ],
        initComplete: function () {
            $('#custom-length').html($('.dataTables_length'));
            $('#custom-search').html($('.dataTables_filter'));
            table.buttons().container().appendTo('#custom-buttons');
        },
    });

    $('#filterBtn').on('click', function () { table.ajax.reload(); });
    $('#resetBtn').on('click', function () {
        $('#storeName').val('').trigger('change');
        $('#startDate, #endDate').val('');
        table.ajax.reload();
    });

    setInterval(function () {
        if (!$('.dataTables_filter input').val().trim()) table.ajax.reload(null, false);
    }, 120000);
});

// ── Tombol Fingerprint Recap Otomatis ──
function doRecap() {
    const btn = document.getElementById('btnRecap');
    const msg = document.getElementById('recapMsg');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sedang merekap...';
    msg.textContent = '';

    fetch('{{ route('fingerprint-recap.recap') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            start_date: document.getElementById('startDate').value,
            end_date:   document.getElementById('endDate').value,
            store_name: document.getElementById('storeName').value,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            msg.textContent = '✅ ' + data.message;
            msg.style.color = '#15803d';
            $('#recapTable').DataTable().ajax.reload();
            Swal.fire({
                icon: 'success',
                title: 'Recap Selesai!',
                text: data.message,
                timer: 3000,
                showConfirmButton: false,
            });
        } else {
            msg.textContent = '❌ ' + data.message;
            msg.style.color = '#b91c1c';
        }
    })
    .catch(() => {
        msg.textContent = '❌ Gagal koneksi ke server.';
        msg.style.color = '#b91c1c';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Fingerprint Recap Otomatis';
    });
}
</script>
@endpush