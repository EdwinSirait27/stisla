@extends('layouts.app')
@section('title', 'Surat Keputusan')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.SkLetters._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Dokumen</li><li aria-current="page">SK</li></ol>
                    <h1 class="pr-title">Surat keputusan</h1>
                    <p class="pr-lede">
                        @if ($canManage)
                            SK promosi, demosi, mutasi, pengangkatan, dan lainnya. HR menyusun Draft lalu mengajukannya;
                            approver yang dipilih di SK menyetujui berurutan. Setelah disetujui penuh, gaji, jabatan, dan penempatan karyawan diperbarui otomatis.
                        @else
                            SK yang menunggu atau pernah melewati persetujuan Anda.
                        @endif
                    </p>
                </div>
                @if ($canCreate)
                    <div class="pr-actions">
                        <a href="{{ route('SkLetters.create') }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Buat SK</a>
                    </div>
                @endif
            </header>

            @if (session('success'))<p class="sk-notice is-ok" role="status">{{ session('success') }}</p>@endif
            @if (session('error'))<p class="sk-notice is-warn" role="alert">{{ session('error') }}</p>@endif

            <section class="pr-panel" aria-label="Ringkasan SK">
                <dl class="tl-stats">
                    <div><dt>Perlu persetujuan saya</dt><dd>{{ number_format($stats['my_turn'], 0, ',', '.') }}</dd><span>giliran Anda menyetujui</span></div>
                    <div><dt>Menunggu persetujuan</dt><dd>{{ number_format($stats['waiting'], 0, ',', '.') }}</dd><span>sudah diajukan HR, belum disetujui penuh</span></div>
                    <div><dt>Draft</dt><dd>{{ number_format($stats['draft'], 0, ',', '.') }}</dd><span>belum diajukan, atau dikembalikan</span></div>
                    <div><dt>Disetujui</dt><dd>{{ number_format($stats['approved'], 0, ',', '.') }}</dd><span>sah dan diterapkan ke data karyawan</span></div>
                </dl>
            </section>

            <section class="pr-panel" aria-labelledby="sk-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="sk-title">Daftar SK</h2>
                        <span class="pr-panel-sub" id="sk-count" role="status">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="sk-search" class="sr-only">Cari SK</label>
                        <input type="search" id="sk-search" placeholder="Nomor, judul, atau nama karyawan" autocomplete="off">
                    </div>
                </div>

                <div class="sk-flags" role="group" aria-label="Saring status">
                    <button type="button" data-status="" aria-pressed="true">Semua</button>
                    <button type="button" data-status="my_turn" aria-pressed="false">Perlu persetujuan saya<b>{{ $stats['my_turn'] ?: '' }}</b></button>
                    <button type="button" data-status="waiting" aria-pressed="false">Menunggu persetujuan</button>
                    <button type="button" data-status="Draft" aria-pressed="false">Draft</button>
                    <button type="button" data-status="Approved Managing Director" aria-pressed="false">Disetujui</button>
                    <button type="button" data-status="Cancelled" aria-pressed="false">Dibatalkan</button>
                </div>

                <div class="sk-filters">
                    <div>
                        <label for="filter-sk-type">Jenis SK</label>
                        <select id="filter-sk-type">
                            <option value="">Semua jenis</option>
                            @foreach ($sktypes as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter-company">Perusahaan</label>
                        <select id="filter-company">
                            <option value="">Semua perusahaan</option>
                            @foreach ($companies as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter-start">Berlaku dari</label>
                        <input type="date" id="filter-start">
                    </div>
                    <div>
                        <label for="filter-end">Sampai</label>
                        <input type="date" id="filter-end">
                    </div>
                    <button type="button" id="btn-reset" class="pr-btn"><i class="fas fa-rotate-left" aria-hidden="true"></i> Reset</button>
                </div>

                <div class="pr-table-wrap">
                    <table id="sk-table" class="table">
                        <thead>
                            <tr>
                                <th>SK</th>
                                <th>Untuk karyawan</th>
                                <th class="sk-col-opt">Berlaku</th>
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
    @include('partials.datatables-error', ['permission' => 'ManageSkLetters'])
    <script>
        $(function() {
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const n = v => Number(v || 0).toLocaleString('id-ID');
            const BADGE = { 'Draft': 'is-draft', 'Approved HR': 'is-waiting', 'Approved Director': 'is-waiting', 'Approved Managing Director': 'is-approved', 'Cancelled': 'is-cancelled' };

            // Filter di URL supaya tetap saat dimuat ulang
            const url = new URL(location.href);
            let status = url.searchParams.get('status') || '';
            const FIELDS = { type: '#filter-sk-type', company: '#filter-company', from: '#filter-start', to: '#filter-end' };
            Object.entries(FIELDS).forEach(([k, sel]) => { const v = url.searchParams.get(k); if (v) $(sel).val(v); });
            $('#sk-search').val(url.searchParams.get('q') || '');
            const pressFlag = () => $('.sk-flags button').each(function() { this.setAttribute('aria-pressed', String(this.dataset.status === status)); });
            pressFlag();

            function params() {
                return {
                    filter_sk_type: $('#filter-sk-type').val(), filter_company: $('#filter-company').val(),
                    filter_effective_date_start: $('#filter-start').val(), filter_effective_date_end: $('#filter-end').val(),
                    filter_status: status === 'my_turn' ? '' : status, my_turn: status === 'my_turn' ? 1 : 0,
                };
            }
            function syncUrl() {
                const u = new URL(location.href);
                const set = (k, v) => v ? u.searchParams.set(k, v) : u.searchParams.delete(k);
                Object.entries(FIELDS).forEach(([k, sel]) => set(k, $(sel).val()));
                set('status', status); set('q', $('#sk-search').val().trim());
                history.replaceState(null, '', u);
            }

            const table = $('#sk-table').DataTable({
                processing: true, serverSide: true, autoWidth: false, order: [[2, 'desc']],
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>', pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                search: { search: $('#sk-search').val() },
                language: {
                    processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data', infoFiltered: '',
                    zeroRecords: 'Tidak ada SK yang cocok dengan filter ini.', emptyTable: 'Belum ada SK.',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                ajax: { url: @json(route('skletters.skletters')), data: d => Object.assign(d, params()) },
                createdRow: (row, r) => { if (r.my_turn) row.classList.add('is-turn'); },
                columns: [
                    { data: 'sk_number', render: (d, t, r) => `<span class="pr-cell-name">${esc(r.title || '-')}</span><span class="pr-cell-sub">${esc(d || '-')}, ${esc(r.sk_name || '-')}, ${esc(r.company_name || '-')}</span>` },
                    { data: 'employee_names', orderable: false, searchable: false, render: (d, t, r) => r.employee_count
                        ? `<span class="pr-cell-name">${n(r.employee_count)} karyawan</span><span class="sk-names">${esc(d)}</span>`
                        : '<span class="pr-cell-sub">Belum ada karyawan</span>' },
                    { data: 'effective_date', className: 'sk-col-opt', searchable: false, render: (d, t, r) => `<span class="pr-cell-name">${esc(r.effective_label || '-')}</span><span class="pr-cell-sub">${r.inactive_label ? 'sampai ' + esc(r.inactive_label) : 'tanpa batas waktu'}</span>` },
                    { data: 'status', searchable: false, render: (d, t, r) => {
                        const cls = d === 'Draft' && r.returned_at ? 'is-returned' : (BADGE[d] || '');
                        return `<span class="sk-badge ${cls}">${esc(r.status_label)}</span>${r.my_turn ? '<span class="sk-turn">Giliran Anda</span>' : ''}`;
                    } },
                    { data: 'show_url', orderable: false, searchable: false, render: (d, t, r) => `<div class="sk-row-actions">
                        <a href="${esc(d)}" class="pr-btn pr-btn-sm ${r.my_turn ? 'pr-btn-primary' : ''}">${r.my_turn ? 'Periksa' : 'Buka'}</a>
                        <a href="${esc(r.pdf_url)}" class="pr-btn pr-btn-sm" target="_blank" rel="noopener" aria-label="PDF ${esc(r.sk_number)}">PDF</a></div>` },
                ],
                drawCallback: function(s) {
                    const j = s.json; if (!j) return;
                    $('#sk-count').text(`${n(j.recordsFiltered)} SK`);
                },
            });

            const reload = () => { syncUrl(); table.ajax.reload(); };
            Object.values(FIELDS).forEach(sel => $(sel).on('change', reload));
            $('.sk-flags button').on('click', function() { status = this.dataset.status; pressFlag(); reload(); });
            let ts; $('#sk-search').on('input', function() { clearTimeout(ts); const v = this.value; ts = setTimeout(() => { table.search(v).draw(); syncUrl(); }, 300); });
            $('#btn-reset').on('click', function() {
                Object.values(FIELDS).forEach(sel => $(sel).val('')); status = ''; pressFlag(); $('#sk-search').val(''); table.search(''); reload();
            });
            syncUrl();
        });
    </script>
@endpush
