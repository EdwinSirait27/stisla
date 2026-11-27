@extends('layouts.app')
@section('title', 'Jenis dokumen')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @include('pages.Documenttype._styles')
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Dokumen</li><li aria-current="page">Jenis dokumen</li></ol>
                    <h1 class="pr-title">Jenis dokumen</h1>
                    <p class="pr-lede">Jenis surat yang bisa diterbitkan: singkatannya di nomor surat, template yang dipakai, dan seberapa sering diterbitkan.</p>
                </div>
                <div class="pr-actions">
                    <a href="{{ route('documenttype.create') }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Tambah jenis dokumen</a>
                </div>
            </header>

            <section class="pr-panel" aria-label="Ringkasan">
                <dl class="tl-stats">
                    <div><dt>Jenis dokumen</dt><dd id="st-total">–</dd><span id="st-active">–</span></div>
                    <div id="tpl-box"><dt>Template belum ada</dt><dd id="st-notpl">–</dd><span>file template tidak ditemukan, PDF jenis ini tidak bisa dibuat</span></div>
                    <div><dt>Dokumen terbit</dt><dd id="st-docs">–</dd><span>dari semua jenis</span></div>
                </dl>
            </section>

            <section class="pr-panel" aria-labelledby="dt-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="dt-title">Daftar jenis dokumen</h2>
                        <span class="pr-panel-sub" id="dt-count">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="dt-search" class="sr-only">Cari jenis dokumen</label>
                        <input type="search" id="dt-search" placeholder="Cari nama atau singkatan">
                    </div>
                </div>
                <div class="dt-flags-wrap">
                    <div class="dt-flags" role="group" aria-label="Saring jenis dokumen">
                        <button type="button" data-flag="" aria-pressed="true">Semua</button>
                        <button type="button" data-flag="active" aria-pressed="false">Aktif</button>
                        <button type="button" data-flag="inactive" aria-pressed="false">Nonaktif</button>
                        <button type="button" data-flag="no_template" aria-pressed="false">Template belum ada</button>
                    </div>
                </div>
                <div class="pr-table-wrap">
                    <table class="table" id="doctype-table">
                        <thead>
                            <tr>
                                <th>Jenis dokumen</th>
                                <th>Template</th>
                                <th class="num dt-col-opt">Perusahaan aktif</th>
                                <th class="num">Dokumen terbit</th>
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
    @include('partials.datatables-error', ['permission' => 'ManageDocumentType'])
    <script>
        $(function() {
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const n = v => Number(v || 0).toLocaleString('id-ID');
            let flag = '';

            const table = $('#doctype-table').DataTable({
                processing: true, serverSide: true, autoWidth: false, ordering: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data',
                    infoFiltered: '', zeroRecords: 'Tidak ada jenis dokumen yang cocok.', emptyTable: 'Belum ada jenis dokumen.',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                ajax: { url: '{{ route('documenttype.data') }}', data: d => { d.flag = flag; } },
                columns: [
                    { data: 'name', render: (d, t, r) => `<span class="pr-cell-name">${esc(d)}<span class="dt-code">${esc(r.nickname)}</span></span>`
                        + `<span class="pr-cell-sub"><span class="dt-status ${r.is_active ? 'is-on' : 'is-off'}">${r.is_active ? 'Aktif' : 'Nonaktif'}</span>${r.requires_approval ? ' <span class="dt-status is-on">Approval bertingkat</span>' : ''}</span>` },
                    { data: 'template', searchable: false, render: (d, t, r) => r.template_ok
                        ? `<span class="dt-file">${esc(d)}</span>`
                        : `<span class="dt-file">${esc(d) || 'Belum dipilih'}</span><span class="pr-cell-sub"><span class="dt-warn">File template belum ada</span></span>` },
                    { data: 'companies', searchable: false, className: 'num dt-col-opt', render: d => d ? n(d) : '<span class="dt-zero">Belum ada</span>' },
                    { data: 'documents', searchable: false, className: 'num', render: (d, t, r) => d
                        ? `<span class="pr-cell-name" style="font-weight:700">${n(d)}</span><span class="pr-cell-sub">terakhir ${esc(r.last_issued)}</span>`
                        : '<span class="dt-zero">Belum ada</span>' },
                    { data: 'edit_url', searchable: false, className: 'text-right', render: (d, t, r) => `<a href="${esc(d)}" class="pr-btn pr-btn-sm" aria-label="Ubah ${esc(r.name)}">Ubah</a>` },
                    { data: 'nickname', visible: false, defaultContent: '' },
                ],
                drawCallback: function(settings) {
                    const j = settings.json; if (!j) return;
                    const s = j.stats || {};
                    $('#dt-count').text(`${n(j.recordsFiltered)} jenis dokumen`);
                    $('#st-total').text(n(s.total)); $('#st-active').text(`${n(s.active)} aktif`);
                    $('#st-notpl').text(n(s.no_template)); $('#tpl-box').toggleClass('is-warn', s.no_template > 0);
                    $('#st-docs').text(n(s.documents));
                },
            });

            $('.dt-flags button').on('click', function() {
                flag = this.dataset.flag;
                $('.dt-flags button').attr('aria-pressed', 'false'); $(this).attr('aria-pressed', 'true');
                table.ajax.reload();
            });
            let ts; $('#dt-search').on('input', function() { clearTimeout(ts); const v = this.value; ts = setTimeout(() => table.search(v).draw(), 300); });

            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Tersimpan', text: @json(session('success')) });
            @endif
            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
            @endif
        });
    </script>
@endpush
