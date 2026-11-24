@extends('layouts.app')
@section('title', 'Departemen')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @include('pages.Department._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Master data</li><li aria-current="page">Departemen</li></ol>
                    <h1 class="pr-title">Departemen</h1>
                    <p class="pr-lede">Siapa saja di tiap departemen: karyawan aktif, managernya, serta jabatan dan lokasi yang dicakup. Dihitung dari departemen, jabatan, dan lokasi karyawan saat ini.</p>
                </div>
                <div class="pr-actions">
                    <a href="{{ route('Department.create') }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Tambah departemen</a>
                </div>
            </header>

            <section class="pr-panel" aria-label="Ringkasan">
                <dl class="tl-stats">
                    <div><dt>Departemen</dt><dd id="st-total">–</dd><span id="st-used">–</span></div>
                    <div><dt>Karyawan aktif</dt><dd id="st-employees">–</dd><span>terbagi di semua departemen</span></div>
                    <div id="nomgr-box"><dt>Tanpa manager</dt><dd id="st-nomgr">–</dd><span>departemen berkaryawan aktif, tidak ada karyawan aktif bertanda manager</span></div>
                    <div><dt>Tidak dipakai</dt><dd id="st-unused">–</dd><span>tanpa karyawan aktif, Pending, atau On Leave</span></div>
                </dl>
            </section>

            <section class="pr-panel" aria-labelledby="dp-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="dp-title">Daftar departemen</h2>
                        <span class="pr-panel-sub" id="dp-count">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="dp-search" class="sr-only">Cari departemen</label>
                        <input type="search" id="dp-search" placeholder="Cari nama atau singkatan">
                    </div>
                </div>
                <div class="dp-flags-wrap">
                    <div class="dp-flags" role="group" aria-label="Saring departemen">
                        <button type="button" data-flag="" aria-pressed="true">Semua</button>
                        <button type="button" data-flag="used" aria-pressed="false">Dipakai</button>
                        <button type="button" data-flag="no_manager" aria-pressed="false">Tanpa manager</button>
                        <button type="button" data-flag="unused" aria-pressed="false">Tidak dipakai</button>
                    </div>
                </div>
                <div class="pr-table-wrap">
                    <table class="table" id="department-table">
                        <thead>
                            <tr>
                                <th>Departemen</th>
                                <th>Karyawan aktif</th>
                                <th>Manager</th>
                                <th class="dp-col-opt">Jabatan</th>
                                <th class="dp-col-opt">Lokasi</th>
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
    @include('partials.datatables-error', ['permission' => 'ManageDepartments'])
    <script>
        $(function() {
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const n = v => Number(v || 0).toLocaleString('id-ID');
            const list = (names, total, unit) => {
                if (!total) return '<span class="dp-zero">Belum ada</span>';
                const rest = total - names.length;
                return `<span class="pr-cell-name" style="font-weight:600">${n(total)} ${unit}</span><span class="pr-cell-sub dp-names">${names.map(esc).join(', ')}${rest > 0 ? `, +${n(rest)} lainnya` : ''}</span>`;
            };
            let flag = '', totalEmployees = 0;

            const table = $('#department-table').DataTable({
                processing: true, serverSide: true, autoWidth: false, ordering: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data',
                    infoFiltered: '', zeroRecords: 'Tidak ada departemen yang cocok.', emptyTable: 'Belum ada departemen.',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                ajax: {
                    url: '{{ route('departments.departments') }}', data: d => { d.flag = flag; },
                    // Total karyawan aktif dibutuhkan sebelum baris digambar (porsi per departemen)
                    dataSrc: j => { totalEmployees = (j.stats && j.stats.employees) || 0; return j.data; },
                },
                columns: [
                    { data: 'name', render: (d, t, r) => {
                        const code = r.nickname && r.nickname !== '-'
                            ? `<span class="dp-code">${esc(r.nickname)}</span>`
                            : '<span class="dp-code is-missing">Tanpa singkatan</span>';
                        return `<span class="pr-cell-name">${esc(d)}${code}</span>`;
                    } },
                    { data: 'employees', searchable: false, render: (d, t, r) => {
                        const other = r.employees_other ? `<span class="pr-cell-sub">+${n(r.employees_other)} Pending/On Leave</span>` : '';
                        if (!d) return `<span class="dp-zero">0</span>${other}`;
                        const pct = totalEmployees ? Math.round(d / totalEmployees * 100) : 0;
                        return `<div class="dp-share" title="${pct}% dari karyawan aktif"><span class="dp-bar"><i style="transform:scaleX(${(pct / 100).toFixed(3)})"></i></span><span>${n(d)}</span></div>${other}`;
                    } },
                    { data: 'managers', searchable: false, render: (d, t, r) => d.length
                        ? `<span class="dp-names">${d.map(esc).join(', ')}</span>`
                        : (r.employees ? '<span class="dp-warn">Belum ada manager</span>' : '<span class="dp-zero">Belum ada</span>') },
                    { data: 'positions', searchable: false, className: 'dp-col-opt', render: (d, t, r) => list(r.top_positions, d, 'jabatan') },
                    { data: 'stores', searchable: false, className: 'dp-col-opt', render: (d, t, r) => list(r.top_stores, d, 'lokasi') },
                    { data: 'edit_url', searchable: false, className: 'text-right', render: (d, t, r) => `<a href="${esc(d)}" class="pr-btn pr-btn-sm" aria-label="Ubah ${esc(r.name)}">Ubah</a>` },
                    // Tidak tampil, hanya supaya pencarian juga mencocokkan singkatan dan nama manager
                    { data: 'nickname', visible: false, defaultContent: '' },
                    { data: 'manager_text', visible: false, defaultContent: '' },
                ],
                drawCallback: function(settings) {
                    const j = settings.json; if (!j) return;
                    const s = j.stats || {};
                    $('#dp-count').text(`${n(j.recordsFiltered)} departemen`);
                    $('#st-total').text(n(s.total)); $('#st-used').text(`${n(s.used)} dipakai karyawan aktif`);
                    $('#st-employees').text(n(s.employees));
                    $('#st-nomgr').text(n(s.no_manager)); $('#nomgr-box').toggleClass('is-warn', s.no_manager > 0);
                    $('#st-unused').text(n(s.unused));
                },
            });

            $('.dp-flags button').on('click', function() {
                flag = this.dataset.flag;
                $('.dp-flags button').attr('aria-pressed', 'false'); $(this).attr('aria-pressed', 'true');
                table.ajax.reload();
            });
            let ts; $('#dp-search').on('input', function() { clearTimeout(ts); const v = this.value; ts = setTimeout(() => table.search(v).draw(), 300); });

            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Tersimpan', text: @json(session('success')) });
            @endif
            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
            @endif
        });
    </script>
@endpush
