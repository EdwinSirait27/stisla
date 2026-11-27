@extends('layouts.app')
@section('title', 'Kontrak kerja')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.contract._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Karyawan</li><li aria-current="page">Kontrak</li></ol>
                    <h1 class="pr-title">Kontrak kerja</h1>
                    <p class="pr-lede">
                        @if ($viewAll)
                            Kontrak PKWT, PKWTT, On Job Training, dan DW. HR menyusun Draft lalu mengajukannya; approver yang dipilih menyetujui berurutan.
                            Kontrak berlaku setelah disetujui penuh dan berakhir otomatis sehari setelah tanggal berakhirnya.
                        @else
                            Kontrak yang menunggu atau pernah melewati persetujuan Anda.
                        @endif
                    </p>
                </div>
                @if ($canManage)
                    <div class="pr-actions">
                        <a href="{{ route('createcontract') }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Buat kontrak</a>
                    </div>
                @endif
            </header>

            @if (session('success'))<p class="sk-notice is-ok" role="status">{{ session('success') }}</p>@endif
            @if (session('error'))<p class="sk-notice is-warn" role="alert">{{ session('error') }}</p>@endif

            <section class="pr-panel" aria-label="Ringkasan kontrak">
                <dl class="tl-stats">
                    <div><dt>Perlu persetujuan saya</dt><dd>{{ number_format($stats['my_turn'], 0, ',', '.') }}</dd><span>{{ $stats['pending'] }} kontrak menunggu persetujuan, {{ $stats['draft'] }} Draft</span></div>
                    <div><dt>Berlaku</dt><dd>{{ number_format($stats['active'], 0, ',', '.') }}</dd><span>kontrak yang sedang berjalan</span></div>
                    <div><dt>Berakhir dalam {{ $endingSoonDays }} hari</dt><dd>{{ number_format($stats['ending_soon'], 0, ',', '.') }}</dd><span>perlu diperpanjang atau diakhiri</span></div>
                    <div><dt>Karyawan tanpa kontrak berlaku</dt><dd>{{ number_format($stats['without'], 0, ',', '.') }}</dd><span>karyawan aktif</span></div>

                </dl>
            </section>

            <section class="pr-panel" aria-labelledby="ct-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="ct-title">Daftar kontrak</h2>
                        <span class="pr-panel-sub" id="ct-count" role="status">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="ct-search" class="sr-only">Cari kontrak</label>
                        <input type="search" id="ct-search" placeholder="Nama atau NIP karyawan" autocomplete="off">
                    </div>
                </div>

                <div class="sk-flags" role="group" aria-label="Saring status">
                    <button type="button" data-view="my_turn" aria-pressed="false">Perlu persetujuan saya<b>{{ $stats['my_turn'] ?: '' }}</b></button>
                    <button type="button" data-view="pending" aria-pressed="false">Menunggu persetujuan</button>
                    <button type="button" data-view="draft" aria-pressed="false">Draft</button>
                    <button type="button" data-view="active" aria-pressed="true">Berlaku</button>
                    <button type="button" data-view="ending_soon" aria-pressed="false">Berakhir dalam {{ $endingSoonDays }} hari</button>
                    <button type="button" data-view="expired" aria-pressed="false">Berakhir</button>
                    <button type="button" data-view="terminated" aria-pressed="false">Diakhiri lebih awal</button>
                    <button type="button" data-view="cancelled" aria-pressed="false">Dibatalkan</button>
                    <button type="button" data-view="" aria-pressed="false">Semua</button>
                </div>

                <div class="sk-filters">
                    <div>
                        <label for="filter-type">Jenis</label>
                        <select id="filter-type"><option value="">Semua jenis</option>@foreach ($types as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                    </div>
                    <div>
                        <label for="filter-company">Perusahaan</label>
                        <select id="filter-company"><option value="">Semua perusahaan</option>@foreach ($companies as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
                    </div>
                    <div>
                        <label for="filter-department">Departemen</label>
                        <select id="filter-department"><option value="">Semua departemen</option>@foreach ($departments as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
                    </div>
                    <div>
                        <label for="filter-store">Lokasi</label>
                        <select id="filter-store"><option value="">Semua lokasi</option>@foreach ($stores as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
                    </div>
                    <button type="button" id="btn-reset" class="pr-btn"><i class="fas fa-rotate-left" aria-hidden="true"></i> Reset</button>
                </div>

                <div class="pr-table-wrap">
                    <table id="contract-table" class="table">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th class="sk-col-opt">Penempatan</th>
                                <th>Jenis</th>
                                <th>Masa kontrak</th>
                                <th>Status</th>
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
    @include('partials.datatables-error', ['permission' => 'ManageContracts'])
    <script>
        $(function() {
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const n = v => Number(v || 0).toLocaleString('id-ID');
            const BADGE = { 'Draft': 'is-draft', 'Pending': 'is-waiting', 'Active': 'is-approved', 'Expired': 'is-draft', 'Terminated': 'is-cancelled', 'Cancelled': 'is-cancelled' };
            const SOON = {{ $endingSoonDays }};

            const url = new URL(location.href);
            let view = url.searchParams.has('view') ? url.searchParams.get('view') : @json($viewAll ? 'active' : 'my_turn');
            const FIELDS = { type: '#filter-type', company: '#filter-company', department: '#filter-department', store: '#filter-store' };
            Object.entries(FIELDS).forEach(([k, sel]) => { const v = url.searchParams.get(k); if (v) $(sel).val(v); });
            $('#ct-search').val(url.searchParams.get('q') || '');
            const press = () => $('.sk-flags button').each(function() { this.setAttribute('aria-pressed', String(this.dataset.view === view)); });
            press();

            const params = () => ({
                filter_contract_type: $('#filter-type').val(), filter_company: $('#filter-company').val(),
                filter_department: $('#filter-department').val(), filter_store: $('#filter-store').val(), filter_view: view,
            });
            function syncUrl() {
                const u = new URL(location.href);
                const set = (k, v) => v ? u.searchParams.set(k, v) : u.searchParams.delete(k);
                Object.entries(FIELDS).forEach(([k, sel]) => set(k, $(sel).val()));
                view === @json($viewAll ? 'active' : 'my_turn') ? u.searchParams.delete('view') : u.searchParams.set('view', view);
                set('q', $('#ct-search').val().trim());
                history.replaceState(null, '', u);
            }
            function left(d, status) {
                if (status !== 'Active' || d === null) return '';
                if (d < 0) return '<span class="pr-cell-sub ct-left is-late">lewat, menunggu diproses</span>';
                if (d === 0) return '<span class="pr-cell-sub ct-left is-soon">berakhir hari ini</span>';
                return `<span class="pr-cell-sub ct-left ${d <= SOON ? 'is-soon' : ''}">${n(d)} hari lagi</span>`;
            }

            const table = $('#contract-table').DataTable({
                processing: true, serverSide: true, autoWidth: false, order: [[3, 'asc']],
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>', pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                search: { search: $('#ct-search').val() },
                language: {
                    processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data', infoFiltered: '',
                    zeroRecords: 'Tidak ada kontrak yang cocok dengan filter ini.', emptyTable: 'Belum ada kontrak.',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                ajax: { url: @json(route('contracts.contracts')), data: d => Object.assign(d, params()) },
                createdRow: (row, r) => { if (r.my_turn) row.classList.add('is-turn'); },
                columns: [
                    { data: 'employee_name', render: (d, t, r) => `<span class="pr-cell-name">${esc(d || '-')}</span><span class="pr-cell-sub">${r.employee_pengenal ? 'NIP ' + esc(r.employee_pengenal) : 'Tanpa NIP'}${r.employee_status && r.employee_status !== 'Active' ? ', ' + esc(r.employee_status) : ''}</span>` },
                    { data: 'position_name', className: 'sk-col-opt', render: (d, t, r) => `<span class="pr-cell-name">${esc(d || 'Jabatan belum diisi')}</span><span class="pr-cell-sub">${[r.department_name, r.store_name, r.company_name].filter(Boolean).map(esc).join(', ') || '-'}</span>` },
                    { data: 'contract_type', searchable: false, render: (d, t, r) => `<span class="sk-badge is-waiting">${esc(d)}</span>${r.sk_letter_id ? '<span class="pr-cell-sub">dari SK</span>' : ''}` },
                    { data: 'end_date', searchable: false, render: (d, t, r) => `<span class="pr-cell-name">${esc(r.start_label)} sampai ${r.end_label ? esc(r.end_label) : 'tanpa batas'}</span>${left(r.days_left, r.contract_status)}` },
                    { data: 'contract_status', searchable: false, render: (d, t, r) => `<span class="sk-badge ${d === 'Draft' && r.returned_at ? 'is-returned' : (BADGE[d] || '')}">${esc(r.status_label)}</span>`
                        + (r.my_turn ? '<span class="sk-turn">Giliran Anda</span>' : (d === 'Active' && !r.has_file ? '<span class="pr-cell-sub">dokumen belum diunggah</span>' : '')) },
                    { data: 'show_url', orderable: false, searchable: false, render: (d, t, r) => `<div class="sk-row-actions"><a href="${esc(d)}" class="pr-btn pr-btn-sm ${r.my_turn ? 'pr-btn-primary' : ''}" aria-label="Buka kontrak ${esc(r.employee_name)}">${r.my_turn ? 'Periksa' : 'Buka'}</a></div>` },
                ],
                drawCallback: s => { const j = s.json; if (j) $('#ct-count').text(`${n(j.recordsFiltered)} kontrak`); },
            });

            const reload = () => { syncUrl(); table.ajax.reload(); };
            Object.values(FIELDS).forEach(sel => $(sel).on('change', reload));
            $('.sk-flags button').on('click', function() { view = this.dataset.view; press(); reload(); });
            let ts; $('#ct-search').on('input', function() { clearTimeout(ts); const v = this.value; ts = setTimeout(() => { table.search(v).draw(); syncUrl(); }, 300); });
            $('#btn-reset').on('click', () => { Object.values(FIELDS).forEach(sel => $(sel).val('')); view = @json($viewAll ? 'active' : 'my_turn'); press(); $('#ct-search').val(''); table.search(''); reload(); });
            syncUrl();
        });
    </script>
@endpush
