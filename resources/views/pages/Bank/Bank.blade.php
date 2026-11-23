@extends('layouts.app')
@section('title', 'Bank')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @include('pages.Bank._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Master data</li><li aria-current="page">Bank</li></ol>
                    <h1 class="pr-title">Bank</h1>
                    <p class="pr-lede">Daftar bank tujuan transfer gaji. Setiap karyawan memilih salah satu bank ini di data rekeningnya.</p>
                </div>
                @if ($canCreate)
                    <div class="pr-actions">
                        <a href="{{ route('Bank.create') }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Tambah bank</a>
                    </div>
                @endif
            </header>

            <section class="pr-panel" aria-label="Ringkasan">
                <dl class="tl-stats">
                    <div><dt>Bank terdaftar</dt><dd>{{ number_format($stats['banks'], 0, ',', '.') }}</dd><span>pilihan di data rekening karyawan</span></div>
                    <div><dt>Karyawan aktif dengan bank</dt><dd>{{ number_format($stats['active'], 0, ',', '.') }}</dd><span>sudah punya bank tujuan gaji</span></div>
                    <div class="{{ $stats['without_bank'] > 0 ? 'is-warn' : '' }}"><dt>Karyawan aktif tanpa bank</dt><dd>{{ number_format($stats['without_bank'], 0, ',', '.') }}</dd>
                        <span>{{ $stats['without_bank'] > 0 ? 'lengkapi di data karyawan sebelum payroll' : 'semua karyawan aktif sudah punya bank' }}</span></div>
                    <div><dt>Bank belum dipakai</dt><dd>{{ number_format($stats['unused'], 0, ',', '.') }}</dd><span>tidak ada karyawan yang memilihnya</span></div>
                </dl>
            </section>

            <section class="pr-panel" aria-labelledby="bk-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="bk-title">Daftar bank</h2>
                        <span class="pr-panel-sub" id="bk-count" aria-live="polite">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="bk-search" class="sr-only">Cari nama bank</label>
                        <input type="search" id="bk-search" placeholder="Cari nama bank" autocomplete="off">
                    </div>
                </div>
                <div class="pr-table-wrap">
                    <table class="table" id="bank-table">
                        <thead>
                            <tr>
                                <th>Bank</th>
                                <th class="bk-col-opt">Porsi karyawan aktif</th>
                                <th class="num">Karyawan aktif</th>
                                <th class="num bk-col-opt">Total, termasuk resign</th>
                                <th class="text-right"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </section>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.datatables-error', ['permission' => 'bank.view'])
    <script>
        $(function() {
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const n = v => Number(v || 0).toLocaleString('id-ID');
            let activeTotal = 0;

            const table = $('#bank-table').DataTable({
                processing: true, serverSide: true, autoWidth: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[2, 'desc']],
                language: {
                    processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data',
                    infoFiltered: '', zeroRecords: 'Tidak ada bank dengan nama itu.',
                    emptyTable: @json($canCreate ? 'Belum ada bank. Tambahkan bank pertama lewat tombol Tambah bank.' : 'Belum ada bank terdaftar.'),
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                ajax: {
                    url: '{{ route('banks.banks') }}',
                    dataSrc: json => { activeTotal = Number(json.active_total || 0); return json.data; },
                },
                columns: [
                    { data: 'name', name: 'banks_tables.name', render: (d, t, r) => `<span class="pr-cell-name">${esc(d)}</span>${r.updated_at ? `<span class="pr-cell-sub">Diubah ${esc(r.updated_at)}</span>` : ''}` },
                    { data: 'active_employees', name: 'active_employees', orderable: false, searchable: false, className: 'bk-col-opt', render: d => {
                        const pct = activeTotal ? d / activeTotal * 100 : 0;
                        const label = pct > 0 && pct < 1 ? '<1%' : Math.round(pct) + '%';
                        return `<div class="bk-share" title="${n(d)} dari ${n(activeTotal)} karyawan aktif yang punya bank"><div class="bk-bar" aria-hidden="true"><i style="transform:scaleX(${(pct / 100).toFixed(4)})"></i></div><span>${label}</span></div>`;
                    } },
                    { data: 'active_employees', name: 'active_employees', searchable: false, className: 'num', render: d => d > 0 ? `<span class="bk-count">${n(d)}</span>` : '<span class="bk-zero">Belum dipakai</span>' },
                    { data: 'total_employees', name: 'total_employees', searchable: false, className: 'num bk-col-opt', render: d => `<span class="bk-count">${n(d)}</span>` },
                    { data: 'edit_url', orderable: false, searchable: false, className: 'text-right', render: (d, t, r) => d ? `<a href="${esc(d)}" class="pr-btn pr-btn-sm" aria-label="Ubah bank ${esc(r.name)}">Ubah</a>` : '' },
                ],
                drawCallback: function(settings) {
                    const j = settings.json; if (!j) return;
                    $('#bk-count').text(`${n(j.recordsFiltered)} bank${j.recordsFiltered !== j.recordsTotal ? ' dari ' + n(j.recordsTotal) : ''}`);
                },
            });

            let ts;
            $('#bk-search').on('input', function() { clearTimeout(ts); const v = this.value; ts = setTimeout(() => table.search(v).draw(), 300); });

            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 2400 });
            @endif
            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
            @endif
        });
    </script>
@endpush
