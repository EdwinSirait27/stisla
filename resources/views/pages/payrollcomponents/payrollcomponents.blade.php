@extends('layouts.app')
@section('title', 'Komponen payroll')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @include('pages.payrollcomponents._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Payroll</li><li aria-current="page">Komponen payroll</li></ol>
                    <h1 class="pr-title">Komponen payroll</h1>
                    <p class="pr-lede">Pendapatan dan potongan di slip gaji: dari mana nilainya diambil, dan berapa yang terpakai di periode payroll terakhir.</p>
                </div>
                @can('payroll.component.create')
                    <div class="pr-actions">
                        <a href="{{ route('payrollcomponents.create') }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Tambah komponen</a>
                    </div>
                @endcan
            </header>

            <section class="pr-panel" aria-label="Ringkasan">
                <dl class="tl-stats">
                    <div><dt>Komponen</dt><dd id="st-total">–</dd><span id="st-split">–</span></div>
                    <div><dt>Otomatis dari data gaji</dt><dd id="st-auto">–</dd><span>ditambahkan sendiri setiap payroll dibuat</span></div>
                    <div id="attn-box"><dt>Perlu dicek</dt><dd id="st-attn">–</dd><span>nilainya selalu 0 atau belum dipakai perhitungan</span></div>
                    <div><dt>Periode terakhir</dt><dd id="st-period">–</dd><span>dasar kolom "periode terakhir" di tabel</span></div>
                </dl>
            </section>

            <section class="pr-panel" aria-labelledby="pc-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="pc-title">Daftar komponen</h2>
                        <span class="pr-panel-sub" id="pc-count">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="pc-search" class="sr-only">Cari komponen</label>
                        <input type="search" id="pc-search" placeholder="Cari nama komponen">
                    </div>
                </div>
                <div class="pc-flags-wrap">
                    <div class="pc-flags" role="group" aria-label="Saring komponen">
                        <button type="button" data-flag="" aria-pressed="true">Semua</button>
                        <button type="button" data-flag="Income" aria-pressed="false">Pendapatan</button>
                        <button type="button" data-flag="Deduction" aria-pressed="false">Potongan</button>
                        <button type="button" data-flag="auto" aria-pressed="false">Otomatis</button>
                        <button type="button" data-flag="manual" aria-pressed="false">Diisi per payroll</button>
                        <button type="button" data-flag="attention" aria-pressed="false">Perlu dicek</button>
                    </div>
                </div>
                <div class="pr-table-wrap">
                    <table class="table" id="component-table">
                        <thead>
                            <tr>
                                <th>Komponen</th>
                                <th>Dari mana nilainya</th>
                                <th class="num" id="th-latest">Periode terakhir</th>
                                <th class="num pc-col-opt">Dipakai</th>
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
    @include('partials.datatables-error', ['permission' => 'payroll.component.view'])
    <script>
        $(function() {
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const n = v => Number(v || 0).toLocaleString('id-ID');
            const rp = v => 'Rp' + Number(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
            const MODE = { auto: 'Otomatis', manual: 'Diisi per payroll', warn: 'Perlu dicek', none: 'Belum dipakai' };
            let flag = '';

            const table = $('#component-table').DataTable({
                processing: true, serverSide: true, autoWidth: false, ordering: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data',
                    infoFiltered: '', zeroRecords: 'Tidak ada komponen yang cocok.', emptyTable: 'Belum ada komponen payroll.',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                ajax: { url: '{{ route('payrollcomponents.payrollcomponents') }}', data: d => { d.flag = flag; } },
                columns: [
                    { data: 'component_name', render: (d, t, r) => {
                        const type = r.type === 'Income' ? '<span class="pc-tag is-income">Pendapatan</span>' : '<span class="pc-tag is-deduction">Potongan</span>';
                        const burden = r.is_employer_burden ? ' <span class="pc-tag is-burden">Tanggungan perusahaan</span>' : '';
                        return `<span class="pr-cell-name">${esc(d)}</span><span class="pr-cell-sub">${type}${burden}</span>`;
                    } },
                    { data: 'how_text', searchable: false, render: (d, t, r) =>
                        `<span class="pc-mode">${MODE[r.how_state]}</span><span class="pc-how${r.how_state === 'warn' ? ' is-warn' : ''}">${esc(d)}</span>` },
                    { data: 'latest_total', searchable: false, className: 'num', render: (d, t, r) => d
                        ? `<span class="pc-money">${rp(d)}</span><span class="pr-cell-sub">${n(r.latest_people)} karyawan</span>`
                        : '<span class="pc-zero">Rp0</span>' },
                    { data: 'payrolls', searchable: false, className: 'num pc-col-opt', render: d => d ? `${n(d)} slip` : '<span class="pc-zero">Belum</span>' },
                    { data: 'edit_url', searchable: false, className: 'text-right', render: (d, t, r) => d ? `<a href="${esc(d)}" class="pr-btn pr-btn-sm" aria-label="Ubah ${esc(r.component_name)}">Ubah</a>` : '' },
                ],
                drawCallback: function(settings) {
                    const j = settings.json; if (!j) return;
                    const s = j.stats || {};
                    $('#pc-count').text(`${n(j.recordsFiltered)} komponen`);
                    $('#st-total').text(n(s.total)); $('#st-split').text(`${n(s.income)} pendapatan, ${n(s.deduction)} potongan`);
                    $('#st-auto').text(n(s.auto));
                    $('#st-attn').text(n(s.attention)); $('#attn-box').toggleClass('is-warn', s.attention > 0);
                    $('#st-period').text(s.latest_period || 'Belum ada');
                    $('#th-latest').text(s.latest_period ? `Periode ${s.latest_period}` : 'Periode terakhir');
                },
            });

            $('.pc-flags button').on('click', function() {
                flag = this.dataset.flag;
                $('.pc-flags button').attr('aria-pressed', 'false'); $(this).attr('aria-pressed', 'true');
                table.ajax.reload();
            });
            let ts; $('#pc-search').on('input', function() { clearTimeout(ts); const v = this.value; ts = setTimeout(() => table.search(v).draw(), 300); });

            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Tersimpan', text: @json(session('success')) });
            @endif
            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
            @endif
        });
    </script>
@endpush
