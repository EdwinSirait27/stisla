@extends('layouts.app')
@section('title', 'Jenis SK')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Sktype._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li><a href="{{ route('SkLetters') }}">SK</a></li><li aria-current="page">Jenis SK</li></ol>
                    <h1 class="pr-title">Jenis SK</h1>
                    <p class="pr-lede">Tiap jenis menentukan apa yang diubah sistem pada data karyawan saat SK-nya disetujui penuh. Singkatannya dipakai di nomor SK.</p>
                </div>
                <div class="pr-actions">
                    <a href="{{ route('Sktype.create') }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Tambah jenis SK</a>
                </div>
            </header>

            @if (session('success'))<p class="sk-notice is-ok" role="status">{{ session('success') }}</p>@endif
            @if (session('error'))<p class="sk-notice is-warn" role="alert">{{ session('error') }}</p>@endif

            <section class="pr-panel" aria-label="Ringkasan jenis SK">
                <dl class="tl-stats">
                    <div><dt>Jenis SK</dt><dd>{{ $stats['total'] }}</dd><span>{{ $stats['used'] }} sudah dipakai di SK</span></div>
                    <div><dt>Mengubah gaji</dt><dd>{{ $stats['salary'] }}</dd><span>membuat baris Employee Salary baru saat SK sah</span></div>
                    <div><dt>Membuat kontrak</dt><dd>{{ $stats['contract'] }}</dd><span>SK-nya bisa langsung dibuatkan kontrak</span></div>
                </dl>
            </section>

            <section class="pr-panel" aria-labelledby="st-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="st-title">Daftar jenis SK</h2>
                        <span class="pr-panel-sub" id="st-count" role="status">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="st-search" class="sr-only">Cari jenis SK</label>
                        <input type="search" id="st-search" placeholder="Nama atau singkatan" autocomplete="off">
                    </div>
                </div>
                <div class="pr-table-wrap">
                    <table class="table" id="sktype-table">
                        <thead>
                            <tr>
                                <th>Jenis SK</th>
                                <th class="sk-col-opt">Kategori</th>
                                <th>Saat SK disetujui penuh</th>
                                <th class="sk-col-opt">Dipakai</th>
                                <th><span class="sr-only">Aksi</span></th>
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
    @include('partials.datatables-error', ['permission' => 'ManageSktypes'])
    <script>
        $(function() {
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const title = s => String(s ?? '').toLowerCase().replace(/(^|\s)\S/g, c => c.toUpperCase());
            const CATEGORY = { Employment: 'Kepegawaian', Mutation: 'Mutasi', Payroll: 'Penggajian', Disciplinary: 'Disiplin', Termination: 'Pemberhentian' };

            function effects(r) {
                const out = [];
                if (r.affects_salary) out.push('<span class="st-effect">Gaji baru</span>');
                if (r.affects_position) out.push('<span class="st-effect">Jabatan dan penempatan</span>');
                if (r.affects_status) out.push(r.termination ? '<span class="st-effect is-warn">Karyawan menjadi Resign</span>' : '<span class="st-effect">Jenis karyawan</span>');
                if (r.generates_contract) out.push('<span class="st-effect">Bisa dibuatkan kontrak</span>');
                return out.length ? `<div class="st-effects">${out.join('')}</div>` : '<span class="st-none">Hanya dokumen, data karyawan tidak berubah</span>';
            }

            const table = $('#sktype-table').DataTable({
                processing: true, serverSide: true, autoWidth: false, ordering: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>', pageLength: 25, lengthMenu: [[10, 25, 50], [10, 25, 50]],
                language: {
                    processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data', infoFiltered: '',
                    zeroRecords: 'Tidak ada jenis SK yang cocok.', emptyTable: 'Belum ada jenis SK. Tambahkan dulu sebelum membuat SK.',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                ajax: { url: @json(route('sktypes.sktypes')) },
                columns: [
                    { data: 'sk_name', render: (d, t, r) => `<span class="pr-cell-name">${esc(title(d))}<span class="st-code">${esc(r.nickname)}</span></span><span class="pr-cell-sub">Nomor: 001/${esc(r.nickname)}-…</span>` },
                    { data: 'categories', className: 'sk-col-opt', render: d => esc(CATEGORY[d] || d || '-') },
                    { data: 'affects_salary', render: (d, t, r) => effects(r) },
                    { data: 'total', className: 'sk-col-opt', render: (d, t, r) => d
                        ? `<span class="pr-cell-name">${d} SK</span><span class="pr-cell-sub">${r.approved} disetujui${r.open ? ', ' + r.open + ' berjalan' : ''}</span>`
                        : '<span class="st-none">Belum dipakai</span>' },
                    { data: 'edit_url', render: (d, t, r) => `<div class="sk-row-actions"><a href="${esc(d)}" class="pr-btn pr-btn-sm" aria-label="Ubah ${esc(title(r.sk_name))}">Ubah</a></div>` },
                    // Tidak tampil: supaya pencarian juga mencocokkan singkatan
                    { data: 'nickname', visible: false },
                ],
                drawCallback: s => { const j = s.json; if (j) $('#st-count').text(`${j.recordsFiltered} jenis`); },
            });
            let ts; $('#st-search').on('input', function() { clearTimeout(ts); const v = this.value; ts = setTimeout(() => table.search(v).draw(), 250); });
        });
    </script>
@endpush
