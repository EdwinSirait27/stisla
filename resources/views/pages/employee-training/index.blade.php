@extends('layouts.app')
@section('title', 'Direktori karyawan')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    @include('pages.employee-training._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Training</li><li aria-current="page">Karyawan</li></ol>
                    <h1 class="pr-title">Direktori karyawan</h1>
                    <p class="pr-lede">Cari karyawan yang akan dilatih: nomor yang bisa dihubungi, jabatan, departemen, dan lokasi utamanya. Export mengikuti filter yang sedang aktif.</p>
                </div>
                <div class="pr-actions">
                    <a id="btn-export-excel" href="{{ route('Employeetraining.export') }}" class="pr-btn pr-btn-primary"><i class="fas fa-file-excel" aria-hidden="true"></i> Export Excel</a>
                    <a id="btn-export-csv" href="{{ route('Employeetraining.export') }}?type=csv" class="pr-btn"><i class="fas fa-file-csv" aria-hidden="true"></i> Export CSV</a>
                </div>
            </header>

            <section class="pr-panel" aria-label="Ringkasan karyawan">
                <dl class="tl-stats">
                    <div><dt>Karyawan aktif</dt><dd id="stat-active">…</dd><span>status Active, semua perusahaan</span></div>
                    <div><dt>On Job Training</dt><dd id="stat-ojt">…</dd><span>karyawan aktif dengan jenis On Job Training</span></div>
                    <div><dt>Pending</dt><dd id="stat-pending">…</dd><span>belum aktif, biasanya baru masuk</span></div>
                    <div><dt>Resign</dt><dd id="stat-resign">…</dd><span>tampil kalau filter status diubah</span></div>
                </dl>
            </section>

            <section class="pr-panel" aria-labelledby="et-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="et-title">Daftar karyawan</h2>
                        <span class="pr-panel-sub" id="emp-count" role="status">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="et-search" class="sr-only">Cari karyawan</label>
                        <input type="search" id="et-search" placeholder="Cari nama, NIP, atau telepon" autocomplete="off">
                    </div>
                </div>

                <div class="et-flags-wrap">
                    <div class="et-flags" role="group" aria-label="Jenis karyawan">
                        <button type="button" data-type="" aria-pressed="true">Semua jenis</button>
                        @foreach ($empTypes as $t)
                            <button type="button" data-type="{{ $t }}" aria-pressed="false">{{ $t }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="et-filters dt-filter-bar">
                    <div class="et-filter">
                        <label for="filter-status">Status</label>
                        <select id="filter-status" class="et-select" data-placeholder="Semua status">
                            <option value="">Semua status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="et-filter">
                        <label for="filter-company">Perusahaan</label>
                        <select id="filter-company" class="et-select" data-placeholder="Semua perusahaan">
                            <option value="">Semua perusahaan</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company }}">{{ $company }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="et-filter">
                        <label for="filter-department">Departemen</label>
                        <select id="filter-department" class="et-select" data-placeholder="Semua departemen">
                            <option value="">Semua departemen</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->department_name }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="et-filter">
                        <label for="filter-store">Lokasi</label>
                        <select id="filter-store" class="et-select" data-placeholder="Semua lokasi">
                            <option value="">Semua lokasi</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->name }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" id="btn-reset-filter" class="pr-btn"><i class="fas fa-rotate-left" aria-hidden="true"></i> Reset</button>
                </div>

                <div class="pr-table-wrap">
                    <table id="employees-table" class="table">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th class="et-col-opt">Telepon</th>
                                <th>Penempatan</th>
                                <th class="et-col-opt">Perusahaan</th>
                                <th class="et-col-opt">Jenis</th>
                                <th class="et-col-opt">Status</th>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('partials.datatables-error', ['permission' => 'ManageEmployeeTraining'])
    <script>
        // Setelah ready: inisialisasi global Stisla sudah jalan, jadi select2 di sini tidak dibungkus ulang
        $(function() {
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const n = v => Number(v || 0).toLocaleString('id-ID');
            const none = t => `<span class="et-none">${t}</span>`;
            const phone = d => d ? `<a class="et-phone" href="tel:${esc(String(d).replace(/[^\d+]/g, ''))}">${esc(d)}</a>` : '';
            const statusBadge = d => d ? `<span class="et-badge ${STATUS_CLASS[d] || ''}">${esc(d)}</span>` : '';
            const STATUS_CLASS = { 'Active': 'is-active', 'Pending': 'is-pending', 'Mutation': 'is-moving', 'On Leave': 'is-moving' };
            const FILTERS = { company: '#filter-company', department: '#filter-department', store: '#filter-store', status: '#filter-status' };

            // Filter disimpan di URL (bisa dibagikan / tetap saat dimuat ulang). Status default: Active.
            const url = new URL(location.href);
            let empType = url.searchParams.get('type') || '';
            Object.entries(FILTERS).forEach(([key, sel]) => {
                let v = url.searchParams.get(key);
                if (key === 'status') v = v === null ? 'Active' : (v === 'all' ? '' : v);
                if (v && $(sel).find(`option[value="${CSS.escape(v)}"]`).length) $(sel).val(v);
            });
            $('#et-search').val(url.searchParams.get('q') || '');
            $('.et-flags button').each(function() { this.setAttribute('aria-pressed', String(this.dataset.type === empType)); });

            $('.et-select').each(function() {
                $(this).select2({ width: '100%', placeholder: this.dataset.placeholder, allowClear: false, dropdownAutoWidth: true });
            });

            function params() {
                return {
                    filter_company: $('#filter-company').val() || '',
                    filter_department: $('#filter-department').val() || '',
                    filter_store: $('#filter-store').val() || '',
                    filter_status: $('#filter-status').val() || '',
                    filter_emp_status: empType,
                };
            }

            function syncState() {
                const p = params();
                const u = new URL(location.href);
                const set = (k, v) => v ? u.searchParams.set(k, v) : u.searchParams.delete(k);
                set('company', p.filter_company); set('department', p.filter_department); set('store', p.filter_store);
                set('type', empType); set('q', $('#et-search').val().trim());
                p.filter_status === 'Active' ? u.searchParams.delete('status') : u.searchParams.set('status', p.filter_status || 'all');
                history.replaceState(null, '', u);

                // Export ikut filter yang aktif
                const qs = new URLSearchParams(p).toString();
                const base = @json(route('Employeetraining.export'));
                $('#btn-export-excel').attr('href', `${base}?${qs}`);
                $('#btn-export-csv').attr('href', `${base}?${qs}&type=csv`);
                Object.values(FILTERS).forEach(sel => $(sel).closest('.et-filter').toggleClass('is-set', !!$(sel).val() && !(sel === '#filter-status' && $(sel).val() === 'Active')));
            }

            const empTable = $('#employees-table').DataTable({
                processing: true, serverSide: true, autoWidth: false, ordering: true, order: [[0, 'asc']],
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                search: { search: $('#et-search').val() },
                language: {
                    processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data',
                    infoFiltered: '', emptyTable: 'Belum ada data karyawan.',
                    zeroRecords: '<div class="et-empty">Tidak ada karyawan yang cocok dengan filter ini.<button type="button" class="pr-btn pr-btn-sm" data-reset>Reset filter</button></div>',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                ajax: {
                    url: @json(route('employeestraining.employeestraining')),
                    data: d => Object.assign(d, params()),
                },
                columns: [
                    { data: 'employee_name', render: (d, t, r) => `<span class="pr-cell-name">${esc(d || '-')}</span><span class="pr-cell-sub et-nip">${r.employee_pengenal ? 'NIP ' + esc(r.employee_pengenal) : 'Tanpa NIP'}</span>`
                        // Di HP kolom telepon & status disembunyikan: isinya tampil di bawah nama
                        + `<span class="et-inline">${phone(r.telp_number)}${statusBadge(r.status)}</span>` },
                    { data: 'telp_number', orderable: false, className: 'et-col-opt', render: d => phone(d) || none('Belum diisi') },
                    { data: 'position_name', render: (d, t, r) => {
                        const place = [r.department_name, r.store_name].filter(Boolean).map(esc).join(', ');
                        if (!d && !place) return none('Belum ada penempatan');
                        return `<span class="pr-cell-name">${d ? esc(d) : 'Jabatan belum diisi'}</span><span class="pr-cell-sub">${place || 'Departemen dan lokasi belum diisi'}</span>`;
                    } },
                    { data: 'name_company', className: 'et-col-opt', render: d => d ? esc(d) : none('-') },
                    { data: 'status_employee', className: 'et-col-opt', render: d => d ? `<span class="et-badge ${d === 'On Job Training' ? 'is-ojt' : ''}">${esc(d)}</span>` : none('-') },
                    { data: 'status', className: 'et-col-opt', render: d => statusBadge(d) || none('-') },
                    // Tidak tampil: supaya pencarian juga mencocokkan NIP
                    { data: 'employee_pengenal', visible: false, defaultContent: '' },
                ],
                drawCallback: function(settings) {
                    const j = settings.json; if (!j) return;
                    $('#emp-count').text(`${n(j.recordsFiltered)} karyawan${j.recordsFiltered !== j.recordsTotal ? ' dari ' + n(j.recordsTotal) : ''}`);
                    const s = j.stats || {};
                    $('#stat-active').text(n(s.active)); $('#stat-ojt').text(n(s.ojt));
                    $('#stat-pending').text(n(s.pending)); $('#stat-resign').text(n(s.resign));
                },
            });

            const reload = () => { syncState(); empTable.ajax.reload(); };
            Object.values(FILTERS).forEach(sel => $(sel).on('change', reload));
            $('.et-flags button').on('click', function() {
                empType = this.dataset.type;
                $('.et-flags button').attr('aria-pressed', 'false'); $(this).attr('aria-pressed', 'true');
                reload();
            });
            let ts;
            $('#et-search').on('input', function() { clearTimeout(ts); const v = this.value; ts = setTimeout(() => { empTable.search(v).draw(); syncState(); }, 300); });

            function resetAll() {
                $('#filter-company, #filter-department, #filter-store').val('').trigger('change.select2');
                $('#filter-status').val('Active').trigger('change.select2');
                empType = ''; $('.et-flags button').each(function() { this.setAttribute('aria-pressed', String(this.dataset.type === '')); });
                $('#et-search').val(''); empTable.search('');
                reload();
            }
            $('#btn-reset-filter').on('click', resetAll);
            $('#employees-table').on('click', '[data-reset]', resetAll);

            syncState();
        });
    </script>
@endpush
