{{--
    Form berita acara, dipakai create & edit (revisi).
    Variabel: $document (null saat create), $types, $people, $returned (approval yang mengembalikan, hanya edit)
--}}
@php
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $isEdit = (bool) $document;
    $content = $document?->content;
    $round = $content?->round ?? 1;
    $approvers = old('approvers', $isEdit ? $document->approvals->where('round', $round)->sortBy('sequence')->pluck('employee_id')->values()->all() : []);
    $recipients = old('recipients', $isEdit ? $document->recipients->pluck('employee_id')->all() : []);
    $eventAt = old('event_at', $content?->event_at?->format('Y-m-d\TH:i'));
    $baStores = \App\Models\Stores::orderBy('name')->get(['id', 'name']);
    // Atasan langsung pengaju: wajib approver pertama (server juga memaksakan ini)
    $baMe = auth()->user()?->employee;
    $baSuperior = $baMe ? \App\Services\BeritaAcaraService::superiorOf($baMe) : null;
    $baLocations = array_values(array_filter((array) old('locations', $content?->store_ids ?? []), 'is_string'));
@endphp

@if ($errors->any())
    <div class="emp-errors" role="alert">
        <strong>Belum bisa diajukan.</strong>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form id="ba-form" action="{{ $isEdit ? route('beritaacara.update', $document->id) : route('beritaacara.store') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="dt-layout">
        <div>
            <section class="pr-panel" aria-labelledby="h-isi">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="h-isi">Isi berita acara</h2>
                        <span class="pr-panel-sub">Tanda {!! $req !!} wajib diisi.</span>
                    </div>
                </div>
                <div class="emp-fields dt-fields-2">
                    <div class="emp-field dt-wide">
                        <label for="document_type_id">Jenis {!! $req !!}</label>
                        @if ($isEdit)
                            <input type="text" class="emp-input" value="{{ $document->companydocumentconfigs?->documenttypes?->document_name }} ({{ $document->document_number }})" readonly>
                            <span class="emp-help">Jenis dan nomor tetap saat revisi.</span>
                        @elseif ($types->isEmpty())
                            <p class="dt-similar">Belum ada jenis berita acara yang aktif untuk perusahaan Anda. Minta HR mengaktifkannya di Pengaturan dokumen perusahaan.</p>
                        @else
                            <select id="document_type_id" name="document_type_id" class="dt-select @error('document_type_id') is-invalid @enderror" required>
                                <option value="">Pilih jenis berita acara</option>
                                @foreach ($types as $t)
                                    <option value="{{ $t->id }}" @selected(old('document_type_id', $preselect ?? null) === $t->id || $types->count() === 1)>{{ $t->document_name }} ({{ $t->nickname }})</option>
                                @endforeach
                            </select>
                            <span class="emp-help">Nomor surat diberikan saat diajukan.</span>
                        @endif
                    </div>
                    <div class="emp-field dt-wide">
                        <label for="subject">Perihal {!! $req !!}</label>
                        <input type="text" id="subject" name="subject" maxlength="255" required class="emp-input @error('subject') is-invalid @enderror"
                            value="{{ old('subject', $content?->subject) }}" placeholder="Mis. Serah terima laptop operasional store" style="max-width:none">
                    </div>
                    <div class="emp-field">
                        <label for="event_at">Waktu kejadian</label>
                        <input type="datetime-local" id="event_at" name="event_at" class="emp-input @error('event_at') is-invalid @enderror" value="{{ $eventAt }}" style="max-width:none">
                    </div>
                    <div class="emp-field dt-wide">
                        <label for="locations">Tempat</label>
                        <select id="locations" name="locations[]" multiple class="ba-location @error('locations') is-invalid @enderror" data-placeholder="Pilih satu atau beberapa lokasi">
                            @foreach ($baStores as $st)
                                <option value="{{ $st->id }}" @selected(in_array($st->id, $baLocations, true))>{{ $st->name }}</option>
                            @endforeach
                        </select>
                        @error('locations')<span class="emp-error">{{ $message }}</span>@enderror
                        @error('locations.*')<span class="emp-error">Lokasi yang dipilih tidak dikenal.</span>@enderror
                    </div>
                    <div class="emp-field dt-wide">
                        <label for="body">Uraian {!! $req !!}</label>
                        <textarea id="body" name="body" maxlength="20000" required class="ba-textarea @error('body') is-invalid @enderror"
                            placeholder="Tuliskan kronologi atau isi berita acara: apa yang terjadi, siapa yang terlibat, barang apa, dan kesimpulannya.">{{ old('body', $content?->body) }}</textarea>
                        <span class="emp-help"><span id="body-count">0</span> dari 20.000 karakter. Baris baru dipertahankan di dokumen.</span>
                    </div>
                    <div class="emp-field dt-wide">
                        <label for="attachments">Lampiran</label>
                        @if ($isEdit && $document->attachments->isNotEmpty())
                            <p class="ba-att-cap">Lampiran tersimpan. Centang Hapus untuk membuangnya saat diajukan ulang.</p>
                            <ul class="ba-att" aria-label="Lampiran tersimpan">
                                @foreach ($document->attachments as $a)
                                    @php($aUrl = route('beritaacara.attachment', [$document->id, $a->id]))
                                    <li class="ba-att-item">
                                        <a href="{{ $aUrl }}" target="_blank" rel="noopener" class="ba-att-thumb" aria-label="Buka {{ $a->original_name }}">
                                            @if (str_starts_with((string) $a->mime, 'image/'))
                                                <img src="{{ $aUrl }}" alt="" loading="lazy">
                                            @else
                                                <span class="ba-att-pdf">PDF</span>
                                            @endif
                                        </a>
                                        <span class="ba-att-name" title="{{ $a->original_name }}">{{ $a->original_name }}</span>
                                        <label class="ba-att-remove"><input type="checkbox" name="remove_attachments[]" value="{{ $a->id }}"> Hapus</label>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <input type="file" id="attachments" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf" class="emp-input" style="max-width:none;height:auto;padding:8px"
                            aria-describedby="att-help att-status">
                        <ul class="ba-att" id="att-preview" aria-label="Lampiran baru" hidden></ul>
                        <span class="emp-help" id="att-status" role="status"></span>
                        <span class="emp-help" id="att-help">Foto atau PDF, maksimal 5 file, 5 MB per file. Foto dikompres otomatis. Bisa dipilih bertahap; klik pratinjau untuk melihat ukuran penuh.</span>
                    </div>
                </div>
            </section>

            <section class="pr-panel" aria-labelledby="h-approver">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="h-approver">Approver berurutan {!! $req !!}</h2>
                        <span class="pr-panel-sub">Disetujui sesuai urutan di bawah. Approver berikutnya baru mendapat giliran setelah yang sebelumnya menyetujui.</span>
                        @if ($baSuperior)
                            <span class="pr-panel-sub">{{ $baSuperior->employee_name }} adalah atasan Anda, jadi otomatis menyetujui pertama dan tidak bisa dipindah atau dihapus.</span>
                        @endif
                    </div>
                </div>
                <div class="emp-fields">
                    <div class="emp-field">
                        <label for="approver-select">Tambah approver</label>
                        <select id="approver-select" class="ba-person" data-key="approvers" data-placeholder="Pilih karyawan sebagai approver berikutnya">
                            <option value=""></option>
                            @foreach ($people as $p)<option value="{{ $p['id'] }}">{{ $p['label'] }}</option>@endforeach
                        </select>
                        <ol class="ba-order" id="approver-list" aria-label="Urutan approver"></ol>
                        <span class="emp-help" id="approver-empty">Belum ada approver. Maksimal 10.</span>
                        @error('approvers')<span class="emp-error">{{ $message }}</span>@enderror
                        @error('approvers.*')<span class="emp-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>

            <section class="pr-panel" aria-labelledby="h-cc">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="h-cc">Tembusan</h2>
                        <span class="pr-panel-sub">Bisa melihat berita acara ini di menu Berita acara, dan mendapat email saat sudah disetujui semua approver.</span>
                    </div>
                </div>
                <div class="emp-fields">
                    <div class="emp-field">
                        <label for="cc-select">Tambah tembusan</label>
                        <select id="cc-select" class="ba-person" data-key="recipients" data-placeholder="Pilih karyawan sebagai tembusan">
                            <option value=""></option>
                            @foreach ($people as $p)<option value="{{ $p['id'] }}">{{ $p['label'] }}</option>@endforeach
                        </select>
                        <div class="ba-chips" id="cc-list" aria-label="Tembusan terpilih"></div>
                        @error('recipients.*')<span class="emp-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>

            <div id="ba-hidden"></div>
            <div class="pr-actions" style="justify-content:flex-end;margin-bottom:var(--pr-space-lg)">
                <a href="{{ $isEdit ? route('beritaacara.show', $document->id) : route('beritaacara.index') }}" class="pr-btn">Batal</a>
                <button type="submit" id="ba-submit" class="pr-btn pr-btn-primary" @disabled(!$isEdit && $types->isEmpty())>
                    <i class="fas fa-paper-plane" aria-hidden="true"></i> {{ $isEdit ? 'Ajukan ulang' : 'Ajukan berita acara' }}
                </button>
            </div>
        </div>

        <aside class="pr-panel">
            @if ($isEdit && $returned)
                <div class="pr-panel-head"><div><h2 class="pr-panel-title">Alasan dikembalikan</h2>
                    <span class="pr-panel-sub">oleh {{ $returned->employee?->employee_name }}, {{ $returned->acted_at?->locale('id')->translatedFormat('d M Y H:i') }}</span></div></div>
                <p class="ba-step-note" style="margin:var(--pr-space-md) var(--pr-space-lg)">{{ $returned->note }}</p>
            @endif
            <div class="pr-panel-head"><div><h2 class="pr-panel-title">Alurnya</h2></div></div>
            <ul class="dt-rules">
                <li>Approver menyetujui satu per satu sesuai urutan, dan setiap persetujuan disertai tanda tangan.</li>
                <li>Kalau dikembalikan, Anda memperbaiki lalu mengajukan ulang. Persetujuan dimulai lagi dari approver pertama.</li>
                <li>Setelah semua menyetujui, berita acara sah, PDF bisa diunduh, dan tembusan diberi tahu.</li>
                <li>Pemberitahuan dikirim ke email kantor (company email). Yang belum punya tetap melihatnya di menu Berita acara.</li>
            </ul>
        </aside>
    </div>
</form>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .emp-field:has(> .ba-person), .emp-field:has(> .ba-location) { min-width: 0; }
        .ba-location + .select2-container { display: block; width: 100% !important; }
        .ba-location + .select2-container .select2-selection--multiple { min-height: 40px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); font-family: var(--pr-font); font-size: var(--pr-text-md); }
        .ba-location + .select2-container .select2-selection--multiple .select2-search__field { margin-top: 8px; font-family: var(--pr-font); }
        .ba-location + .select2-container--focus .select2-selection { outline: 2px solid var(--pr-focus); }
        .ba-location + .select2-container .select2-selection__choice { position: relative; margin-top: 6px; padding: 2px 8px 2px 24px !important; border: 1px solid var(--pr-rule) !important; border-radius: var(--pr-radius-sm) !important; background: var(--pr-accent-soft) !important; color: var(--pr-ink) !important; font-size: var(--pr-text-sm); }
        .ba-location + .select2-container .select2-selection__choice__remove { position: absolute; left: 0; top: 0; bottom: 0; width: 20px; border: 0 !important; border-right: 1px solid var(--pr-rule) !important; background: none !important; color: var(--pr-ink-2) !important; }
        .ba-location + .select2-container .select2-selection__choice__remove:hover { color: var(--pr-error) !important; }
        .ba-person + .select2-container { display: block; width: 100% !important; max-width: 100%; }
        .ba-person + .select2-container .select2-selection--single { display: flex; align-items: center; height: 40px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); font-family: var(--pr-font); font-size: var(--pr-text-md); }
        .ba-person + .select2-container .select2-selection__arrow { height: 38px; }
        .ba-person + .select2-container .select2-selection__rendered { flex: 1; min-width: 0; }
        .ba-person + .select2-container--focus .select2-selection { outline: 2px solid var(--pr-focus); }
        .select2-results__option[aria-disabled="true"] { color: var(--pr-muted); }
        .ba-att { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: var(--pr-space-sm); margin: var(--pr-space-xs) 0; padding: 0; list-style: none; }
        .ba-att[hidden] { display: none; }
        .ba-att-item { display: grid; gap: 4px; min-width: 0; padding: var(--pr-space-xs); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); background: var(--pr-panel); }
        .ba-att-item.is-over { border-color: var(--pr-error); }
        .ba-att-thumb { display: grid; place-items: center; aspect-ratio: 4 / 3; overflow: hidden; border-radius: var(--pr-radius-sm); background: var(--pr-paper); color: var(--pr-ink-2); text-decoration: none; }
        .ba-att-thumb:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
        .ba-att-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .ba-att-pdf { padding: 6px 10px; border: 2px solid var(--pr-error); border-radius: var(--pr-radius-sm); font-size: var(--pr-text-md); font-weight: 700; color: var(--pr-error); }
        .ba-att-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: var(--pr-text-sm); color: var(--pr-ink); }
        .ba-att-meta { display: flex; justify-content: space-between; align-items: center; gap: 4px; font-size: var(--pr-text-sm); color: var(--pr-muted); }
        .ba-att-meta.is-over { color: var(--pr-error); }
        .ba-att-del { padding: 2px 6px; border: 0; border-radius: var(--pr-radius-sm); background: none; font: inherit; color: var(--pr-error); cursor: pointer; }
        .ba-att-del:hover { background: var(--pr-error-soft); }
        .ba-att-del:focus-visible { outline: 2px solid var(--pr-focus); }
        .ba-lock { flex: none; font-size: var(--pr-text-sm); color: var(--pr-muted); }
        .ba-att-remove { display: flex; gap: 4px; align-items: center; font-size: var(--pr-text-sm); color: var(--pr-error); }
        .ba-att-cap { margin: 0; font-size: var(--pr-text-sm); color: var(--pr-muted); }
    </style>
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Setelah ready: inisialisasi global Stisla ($(".select2").select2()) sudah jalan, jadi wadah select2 kita tidak ikut dibungkus ulang
        $(function() {
            const PEOPLE = @json($people);
            const byId = Object.fromEntries(PEOPLE.map(p => [p.id, p]));
            const state = {
                approvers: @json(array_values(array_filter((array) $approvers, fn($id) => is_string($id)))).filter(id => byId[id]),
                recipients: @json(array_values(array_filter((array) $recipients, fn($id) => is_string($id)))).filter(id => byId[id]),
            };
            const MAX = { approvers: 10, recipients: 20 };
            // Atasan pengaju dikunci di urutan pertama dan tidak boleh jadi tembusan
            const LOCK = @json($baSuperior?->id);
            if (LOCK && byId[LOCK]) {
                state.approvers = [LOCK, ...state.approvers.filter(id => id !== LOCK)];
                state.recipients = state.recipients.filter(id => id !== LOCK);
            }
            const locked = i => !!LOCK && i === 0 && state.approvers[0] === LOCK;
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const norm = s => String(s ?? '').toLowerCase();
            const form = document.getElementById('ba-form');

            // ── Lampiran: pratinjau, tambah bertahap, hapus per file ──
            // image-upload.js mengompres foto lalu mengirim ulang "change"; handler ini menerima file hasil kompresi.
            const attInput = document.getElementById('attachments');
            const attList = document.getElementById('att-preview');
            const attStatus = document.getElementById('att-status');
            const ATT_MAX = {{ \App\Services\BeritaAcaraService::MAX_FILES }}, ATT_MAX_BYTES = 5 * 1024 * 1024;
            const ATT_OK = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            const kept = () => document.querySelectorAll('input[name="remove_attachments[]"]:not(:checked)').length;
            let attFiles = [], attUrls = [];
            const fmtSize = b => b >= 1048576 ? (b / 1048576).toFixed(1).replace('.', ',') + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB';

            function attSync() {
                const dt = new DataTransfer();
                attFiles.forEach(f => dt.items.add(f));
                attInput.files = dt.files;
            }
            function attRender() {
                attUrls.forEach(u => URL.revokeObjectURL(u)); attUrls = [];
                attList.hidden = !attFiles.length;
                attList.innerHTML = attFiles.map((f, i) => {
                    const url = URL.createObjectURL(f); attUrls.push(url);
                    const over = f.size > ATT_MAX_BYTES;
                    const thumb = f.type.startsWith('image/')
                        ? `<img src="${url}" alt="">`
                        : '<span class="ba-att-pdf">PDF</span>';
                    return `<li class="ba-att-item${over ? ' is-over' : ''}">
                        <a href="${url}" target="_blank" rel="noopener" class="ba-att-thumb" aria-label="Lihat ${esc(f.name)}">${thumb}</a>
                        <span class="ba-att-name" title="${esc(f.name)}">${esc(f.name)}</span>
                        <span class="ba-att-meta${over ? ' is-over' : ''}">${fmtSize(f.size)}${over ? ', lebih dari 5 MB' : ''}
                            <button type="button" class="ba-att-del" data-att-del="${i}" aria-label="Hapus ${esc(f.name)} dari lampiran">Hapus</button></span>
                    </li>`;
                }).join('');
                const total = attFiles.length + kept();
                attStatus.textContent = attFiles.length ? `${attFiles.length} file baru dipilih, ${total} dari ${ATT_MAX} lampiran terpakai.` : '';
            }
            attInput.addEventListener('change', () => {
                const picked = [...attInput.files].filter(f => !attFiles.includes(f));
                const rejected = [];
                picked.forEach(f => {
                    const okType = ATT_OK.includes(f.type) || /\.(jpe?g|png|webp|pdf)$/i.test(f.name);
                    const dup = attFiles.some(x => x.name === f.name && x.size === f.size);
                    if (!okType) rejected.push(`${f.name} (bukan foto atau PDF)`);
                    else if (dup) return;
                    else if (attFiles.length + kept() >= ATT_MAX) rejected.push(`${f.name} (sudah ${ATT_MAX} lampiran)`);
                    else attFiles.push(f);
                });
                attSync(); attRender();
                if (rejected.length) Swal.fire({ icon: 'info', title: 'Sebagian file tidak ditambahkan', html: rejected.map(esc).join('<br>') });
            });
            attList.addEventListener('click', e => {
                const b = e.target.closest('[data-att-del]');
                if (!b) return;
                attFiles.splice(+b.dataset.attDel, 1);
                attSync(); attRender();
                attInput.focus();
            });
            document.querySelectorAll('input[name="remove_attachments[]"]').forEach(c => c.addEventListener('change', attRender));

            function renderHidden() {
                const box = document.getElementById('ba-hidden');
                box.innerHTML = '';
                ['approvers', 'recipients'].forEach(k => state[k].forEach(id => {
                    const i = document.createElement('input');
                    i.type = 'hidden'; i.name = k + '[]'; i.value = id;
                    box.appendChild(i);
                }));
            }

            function renderApprovers() {
                const list = document.getElementById('approver-list');
                list.innerHTML = state.approvers.map((id, i) => {
                    const p = byId[id];
                    return `<li><span class="ba-num" aria-hidden="true">${i + 1}</span>
                        <span class="ba-who"><strong>${esc(p.name)}</strong><small>${esc(p.sub || p.nip || '')}</small>${p.mail ? '' : '<em>Tanpa email kantor, diberi tahu lewat HRX saja</em>'}</span>
                        ${locked(i) ? '<span class="ba-lock">Atasan Anda, wajib pertama</span>' : `<span class="ba-acts">
                            <button type="button" class="ba-icon" data-move="-1" data-i="${i}" ${i === 0 || locked(i - 1) ? 'disabled' : ''} aria-label="Naikkan ${esc(p.name)}"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>
                            <button type="button" class="ba-icon" data-move="1" data-i="${i}" ${i === state.approvers.length - 1 ? 'disabled' : ''} aria-label="Turunkan ${esc(p.name)}"><i class="fas fa-arrow-down" aria-hidden="true"></i></button>
                            <button type="button" class="ba-icon" data-remove="approvers" data-i="${i}" aria-label="Hapus ${esc(p.name)} dari approver"><i class="fas fa-xmark" aria-hidden="true"></i></button>
                        </span>`}</li>`;
                }).join('');
                document.getElementById('approver-empty').textContent = state.approvers.length
                    ? `${state.approvers.length} approver. Urutan bisa diubah dengan tombol panah.`
                    : 'Belum ada approver. Maksimal 10.';
            }

            function renderRecipients() {
                document.getElementById('cc-list').innerHTML = state.recipients.map((id, i) => {
                    const p = byId[id];
                    return `<span class="ba-chip">${esc(p.name)}<button type="button" data-remove="recipients" data-i="${i}" aria-label="Hapus ${esc(p.name)} dari tembusan"><i class="fas fa-xmark" aria-hidden="true"></i></button></span>`;
                }).join('');
            }

            function render() { renderApprovers(); renderRecipients(); renderHidden(); syncOptions(); }

            form.addEventListener('click', e => {
                const mv = e.target.closest('[data-move]');
                if (mv) {
                    const i = +mv.dataset.i, j = i + +mv.dataset.move;
                    [state.approvers[i], state.approvers[j]] = [state.approvers[j], state.approvers[i]];
                    render();
                    document.querySelectorAll('#approver-list li')[j]?.querySelector(`[data-move="${mv.dataset.move}"]:not(:disabled)`)?.focus();
                    return;
                }
                const rm = e.target.closest('[data-remove]');
                if (rm) { state[rm.dataset.remove].splice(+rm.dataset.i, 1); render(); }
            });

            // Dropdown karyawan (select2, bisa dicari): "Nama - Jabatan". Orang yang sudah dipilih
            // sebagai approver atau tembusan dinonaktifkan di kedua dropdown.
            // Tempat: lokasi (stores), bisa lebih dari satu
            $('.ba-location').select2({ width: '100%', placeholder: $('.ba-location').data('placeholder'), closeOnSelect: false, maximumSelectionLength: 20,
                language: { noResults: () => 'Lokasi tidak ditemukan', maximumSelected: () => 'Maksimal 20 lokasi' } });

            const $sel = $('.ba-person');
            $sel.each(function() {
                $(this).select2({ width: '100%', placeholder: this.dataset.placeholder, language: { noResults: () => 'Tidak ditemukan' } });
            });
            $sel.on('select2:select', function(e) {
                const key = this.dataset.key, id = e.params.data.id;
                $(this).val('').trigger('change.select2');
                if (!id || state.approvers.includes(id) || state.recipients.includes(id)) return;
                if (state[key].length >= MAX[key]) {
                    Swal.fire({ icon: 'info', title: `Maksimal ${MAX[key]} ${key === 'approvers' ? 'approver' : 'tembusan'}` });
                    return;
                }
                state[key].push(id); render();
            });
            function syncOptions() {
                const taken = new Set([...state.approvers, ...state.recipients]);
                $sel.find('option[value!=""]').each(function() { this.disabled = taken.has(this.value); });
            }


            const body = document.getElementById('body'), count = document.getElementById('body-count');
            const updateCount = () => { count.textContent = body.value.length.toLocaleString('id-ID'); };
            body.addEventListener('input', updateCount); updateCount();

            document.getElementById('ba-submit').addEventListener('click', function(e) {
                e.preventDefault();
                const type = document.getElementById('document_type_id');
                if (type && !type.value) { type.focus(); return Swal.fire({ icon: 'error', title: 'Pilih jenis berita acara' }); }
                if (!document.getElementById('subject').value.trim()) { document.getElementById('subject').focus(); return Swal.fire({ icon: 'error', title: 'Perihal belum diisi' }); }
                if (!body.value.trim()) { body.focus(); return Swal.fire({ icon: 'error', title: 'Uraian belum diisi' }); }
                if (attFiles.some(f => f.size > ATT_MAX_BYTES)) { attInput.focus(); return Swal.fire({ icon: 'error', title: 'Ada lampiran lebih dari 5 MB', text: 'Hapus atau ganti file yang ditandai merah.' }); }
                if (!state.approvers.length) { $('#approver-select').select2('open'); return Swal.fire({ icon: 'error', title: 'Pilih minimal satu approver' }); }
                const chain = state.approvers.map((id, i) => `${i + 1}. ${esc(byId[id].name)}${locked(i) ? ' (atasan Anda)' : ''}`).join('<br>');
                Swal.fire({
                    icon: 'question',
                    title: @json($isEdit ? 'Ajukan ulang berita acara?' : 'Ajukan berita acara?'),
                    html: `Disetujui berurutan oleh:<br>${chain}${state.recipients.length ? `<br><br>Tembusan: ${state.recipients.length} orang` : ''}`,
                    showCancelButton: true, confirmButtonText: @json($isEdit ? 'Ajukan ulang' : 'Ajukan'), cancelButtonText: 'Periksa lagi', confirmButtonColor: '#4f46e5',
                }).then(async r => {
                    if (!r.isConfirmed) return;
                    this.disabled = true;
                    if (window.HRXImage) await window.HRXImage.ready(); // tunggu kompresi foto selesai
                    form.submit();
                });
            });

            render();
        });
    </script>
@endpush
