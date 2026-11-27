{{--
    Form jenis dokumen bersama untuk create & edit.
    Variabel: $formId, $submitId, $action, $method, $submitLabel, $type (null saat create),
              $templates, $existing, $companyNick, $usage, $lockCode, $special
--}}
@php
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $isEdit = (bool) $type;
    $name = old('document_name', $type?->document_name);
    $code = old('nickname', $type?->nickname);
    $view = old('view_name', $type?->view_name);
    $active = (bool) old('is_active', $type?->is_active ?? true);
    $approval = (bool) old('requires_approval', $type?->requires_approval ?? false);
    $missing = $isEdit && $type->view_name && !isset($templates[$type->view_name]);
    $fmt = fn($v) => number_format((int) $v, 0, ',', '.');
@endphp

@if ($errors->any())
    <div class="emp-errors" role="alert">
        <strong>Belum bisa disimpan.</strong>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="dt-layout">
    <form id="{{ $formId }}" action="{{ $action }}" method="POST" class="pr-panel" novalidate>
        @csrf
        @if ($method === 'PUT') @method('PUT') @endif

        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title">Data jenis dokumen</h2>
                <span class="pr-panel-sub">Tanda {!! $req !!} wajib diisi.</span>
            </div>
        </div>

        <div class="emp-fields dt-fields-2">
            <div class="emp-field">
                <label for="document_name">Nama dokumen {!! $req !!}</label>
                <input type="text" class="emp-input @error('document_name') is-invalid @enderror" id="document_name" name="document_name"
                    value="{{ $name }}" required maxlength="255" autocomplete="off" placeholder="Mis. BERITA ACARA SERAH TERIMA"
                    aria-describedby="document_name-help name-similar" @error('document_name') aria-invalid="true" @enderror>
                @error('document_name')<span class="emp-error">{{ $message }}</span>@enderror
                <span class="emp-help" id="document_name-help">Judul yang tampil di daftar dokumen. Disimpan dengan huruf kapital.</span>
                <p class="dt-similar" id="name-similar" role="status" hidden></p>
            </div>
            <div class="emp-field">
                <label for="nickname">Singkatan {!! $req !!}</label>
                <input type="text" class="emp-input dt-code-input @error('nickname') is-invalid @enderror" id="nickname" name="nickname"
                    value="{{ $code }}" required minlength="2" maxlength="10" autocomplete="off" autocapitalize="characters" spellcheck="false"
                    placeholder="Mis. BAST" aria-describedby="nickname-help code-taken" @readonly($lockCode)
                    @error('nickname') aria-invalid="true" @enderror>
                @error('nickname')<span class="emp-error">{{ $message }}</span>@enderror
                <span class="emp-help" id="nickname-help">
                    @if ($lockCode)
                        Dikunci: sudah ada {{ $fmt($usage['documents']) }} dokumen bernomor dengan singkatan ini. Mengubahnya memutus urutan nomor surat.
                    @else
                        2 sampai 10 huruf, angka, atau tanda hubung (mis. OL-DW). Dipakai di nomor surat.
                    @endif
                </span>
                <p class="dt-similar" id="code-taken" role="status" hidden></p>
            </div>

            <div class="emp-field dt-wide">
                <label for="view_name">Template {!! $req !!}</label>
                <select id="view_name" name="view_name" class="dt-select @error('view_name') is-invalid @enderror" required aria-describedby="view_name-help">
                    <option value="" @selected(!$view)>Pilih template</option>
                    @foreach ($templates as $value => $label)
                        <option value="{{ $value }}" @selected($view === $value)>{{ $label }} ({{ $value }})</option>
                    @endforeach
                    @if ($missing)
                        <option value="{{ $type->view_name }}" @selected($view === $type->view_name)>{{ $type->view_name }} (file belum ada)</option>
                    @endif
                </select>
                @error('view_name')<span class="emp-error">{{ $message }}</span>@enderror
                <span class="emp-help" id="view_name-help">File Blade di <code>resources/views/documents/types</code> yang dipakai untuk membuat PDF. Template baru dibuat programmer, lalu otomatis muncul di pilihan ini.</span>
                @if ($missing)
                    <p class="dt-similar">File template <strong>{{ $type->view_name }}</strong> belum ada, jadi PDF jenis ini belum bisa dibuat.</p>
                @endif
            </div>

            <div class="emp-field dt-wide">
                <label class="dt-check">
                    <input type="hidden" name="requires_approval" value="0">
                    <input type="checkbox" name="requires_approval" value="1" @checked($approval) @disabled($lockCode && !$approval)>
                    <span>
                        <strong>Butuh approval bertingkat (berita acara)</strong>
                        <small>Diajukan karyawan lewat menu Berita acara, disetujui berurutan oleh approver yang dipilih pengaju dengan tanda tangan, dan punya tembusan. Template yang cocok: <code>documents.types.BA</code>.@if ($lockCode && !$approval) Tidak bisa diaktifkan untuk jenis yang sudah punya dokumen terbit.@endif</small>
                    </span>
                </label>
                @if ($lockCode && !$approval)<input type="hidden" name="requires_approval" value="0">@endif
            </div>

            <div class="emp-field dt-wide">
                <label class="dt-check">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked($active)>
                    <span>
                        <strong>Aktif</strong>
                        <small>Jenis nonaktif tidak bisa dipilih saat menerbitkan dokumen baru. Dokumen yang sudah terbit tidak terpengaruh.</small>
                    </span>
                </label>
            </div>
        </div>

        <ul class="dt-rules">
            <li>Nama dan singkatan tidak boleh sama dengan jenis dokumen lain.</li>
            <li>Setelah disimpan, aktifkan jenis ini untuk perusahaan di pengaturan dokumen perusahaan supaya bisa diterbitkan.</li>
            @if ($isEdit && in_array($type->nickname, $special, true))
                <li>Singkatan {{ $type->nickname }} punya aturan nomor dan form khusus di sistem.</li>
            @endif
        </ul>

        <div class="dt-savebar">
            <a href="{{ route('documenttype.index') }}" class="pr-btn">Batal</a>
            <button type="submit" id="{{ $submitId }}" class="pr-btn pr-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> {{ $submitLabel }}</button>
        </div>
    </form>

    <aside class="pr-panel" aria-labelledby="dt-list-title">
        <dl class="dt-number" aria-live="polite">
            <dt>Contoh nomor surat</dt>
            <dd><span id="number-preview">–</span><small>Nomor urut per jenis, per perusahaan, per bulan. Contoh memakai perusahaan {{ $companyNick }}.</small></dd>
        </dl>

        @if ($isEdit && $usage)
            <dl class="dt-usage">
                <div><dt>Perusahaan aktif</dt><dd>{{ $fmt($usage['companies']) }}</dd></div>
                <div><dt>Dokumen terbit</dt><dd>{{ $fmt($usage['documents']) }}</dd></div>
            </dl>
        @endif

        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="dt-list-title">{{ $isEdit ? 'Jenis dokumen lain' : 'Jenis dokumen yang sudah ada' }}</h2>
                <span class="pr-panel-sub">{{ $existing->count() }} jenis. Nama mirip dan singkatan yang sama ditandai saat Anda mengetik.</span>
            </div>
        </div>
        <ul class="dt-list" id="dt-list">
            @foreach ($existing as $d)
                <li data-name="{{ $d->document_name }}" data-code="{{ $d->nickname }}"><span>{{ $d->document_name }}</span><b>{{ $d->nickname }}</b></li>
            @endforeach
        </ul>
    </aside>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const form = document.getElementById(@json($formId));
            const nameEl = document.getElementById('document_name');
            const codeEl = document.getElementById('nickname');
            const viewEl = document.getElementById('view_name');
            const preview = document.getElementById('number-preview');
            const nameNote = document.getElementById('name-similar');
            const codeNote = document.getElementById('code-taken');
            const items = [...document.querySelectorAll('#dt-list li')];
            const COMPANY = @json($companyNick);
            const ROMAN = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            const now = new Date();
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const key = s => String(s).toUpperCase().replace(/\b(SURAT|BERITA|ACARA|DOKUMEN)\b/g, '').replace(/[^A-Z0-9]/g, '');
            const cleanCode = s => String(s).toUpperCase().replace(/[^A-Z0-9-]/g, '').replace(/-{2,}/g, '-').slice(0, 10);

            function render() {
                const code = cleanCode(codeEl.value);
                preview.textContent = `001/${code || 'SINGKATAN'}-${COMPANY}/${ROMAN[now.getMonth()]}/${now.getFullYear()}`;

                const k = key(nameEl.value), names = [], codes = [];
                items.forEach(li => {
                    const o = key(li.dataset.name);
                    const nameHit = k.length >= 3 && o.length >= 3 && (k === o || k.includes(o) || o.includes(k));
                    const codeHit = code.length >= 2 && li.dataset.code === code;
                    li.classList.toggle('is-match', nameHit || codeHit);
                    if (nameHit) names.push(li.dataset.name);
                    if (codeHit) codes.push(li.dataset.name);
                });
                nameNote.hidden = !names.length;
                nameNote.innerHTML = names.length ? `<strong>Mirip dengan ${names.map(esc).join(', ')}.</strong> Pastikan ini bukan jenis yang sama.` : '';
                codeNote.hidden = !codes.length;
                codeNote.innerHTML = codes.length ? `<strong>${esc(code)} sudah dipakai ${codes.map(esc).join(', ')}.</strong>` : '';
            }
            codeEl.addEventListener('input', () => {
                const after = cleanCode(codeEl.value);
                if (codeEl.value !== after) codeEl.value = after;
                render();
            });
            nameEl.addEventListener('input', render);
            render();

            document.getElementById(@json($submitId)).addEventListener('click', function(e) {
                e.preventDefault();
                const name = nameEl.value.trim(), code = cleanCode(codeEl.value);
                if (!name) { nameEl.focus(); return Swal.fire({ icon: 'error', title: 'Nama dokumen belum diisi' }); }
                if (code.replace(/-/g, '').length < 2) { codeEl.focus(); return Swal.fire({ icon: 'error', title: 'Singkatan belum diisi' }); }
                if (!codeNote.hidden) { codeEl.focus(); return Swal.fire({ icon: 'error', title: 'Singkatan sudah dipakai' }); }
                if (!viewEl.value) { viewEl.focus(); return Swal.fire({ icon: 'error', title: 'Pilih template' }); }
                Swal.fire({
                    icon: nameNote.hidden ? 'question' : 'warning',
                    title: @json($isEdit ? 'Simpan perubahan?' : 'Tambah jenis dokumen ini?'),
                    html: `<b>${esc(name.toUpperCase())}</b> (${esc(code)})<br>Nomor: ${esc(preview.textContent)}`,
                    showCancelButton: true, confirmButtonText: @json($submitLabel), cancelButtonText: 'Periksa lagi',
                    confirmButtonColor: '#4f46e5', focusCancel: !nameNote.hidden,
                }).then(r => { if (!r.isConfirmed) return; this.disabled = true; form.submit(); });
            });
        });
    </script>
@endpush
