{{--
    Form pengaturan dokumen perusahaan, dipakai create & edit.
    Variabel: $formId, $submitId, $action, $method, $submitLabel, $config, $companies, $types, $typeFields,
              $extra, $companyId, $typeId, $activeOthers, $documents, $isDw
--}}
@php
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $isEdit = (bool) $config;
    $locked = $isEdit && $documents > 0;
    $active = (bool) old('is_active', $config?->is_active ?? true);
    $fmt = fn($v) => number_format((int) $v, 0, ',', '.');
@endphp

@if ($errors->any())
    <div class="emp-errors" role="alert">
        <strong>Belum bisa disimpan.</strong>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form id="{{ $formId }}" action="{{ $action }}" method="POST" novalidate>
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    <div class="dt-layout">
        <div>
            <section class="pr-panel">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title">Perusahaan dan jenis dokumen</h2>
                        <span class="pr-panel-sub">Tanda {!! $req !!} wajib diisi.</span>
                    </div>
                </div>
                <div class="emp-fields dt-fields-2">
                    <div class="emp-field">
                        <label for="company_id">Perusahaan {!! $req !!}</label>
                        <select id="company_id" name="company_id" class="dt-select @error('company_id') is-invalid @enderror" required @disabled($locked)>
                            <option value="">Pilih perusahaan</option>
                            @foreach ($companies as $co)
                                <option value="{{ $co->id }}" @selected($companyId === $co->id)>{{ $co->nickname }}, {{ $co->name }}</option>
                            @endforeach
                        </select>
                        @if ($locked)<input type="hidden" name="company_id" value="{{ $config->company_id }}">@endif
                        @error('company_id')<span class="emp-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="emp-field">
                        <label for="document_type_id">Jenis dokumen {!! $req !!}</label>
                        <select id="document_type_id" name="document_type_id" class="dt-select @error('document_type_id') is-invalid @enderror" required @disabled($locked)>
                            <option value="">Pilih jenis</option>
                            @foreach ($types as $t)
                                <option value="{{ $t->id }}" @selected($typeId === $t->id)>{{ $t->nickname }}, {{ $t->document_name }}{{ $t->is_active ? '' : ' (nonaktif)' }}</option>
                            @endforeach
                        </select>
                        @if ($locked)<input type="hidden" name="document_type_id" value="{{ $config->document_type_id }}">@endif
                        @error('document_type_id')<span class="emp-error">{{ $message }}</span>@enderror
                    </div>
                    @if ($locked)
                        <p class="emp-help dt-wide">Perusahaan dan jenis dikunci: sudah ada {{ $fmt($documents) }} dokumen bernomor dari pengaturan ini. Untuk perusahaan atau jenis lain, buat pengaturan baru.</p>
                    @endif
                    <p class="dt-similar dt-wide" id="dup-warn" role="status" hidden></p>
                    <p class="dt-similar dt-wide" id="tpl-warn" role="status" hidden></p>

                    <div class="emp-field dt-wide">
                        <label class="dt-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked($active)>
                            <span>
                                <strong>Aktif</strong>
                                <small>Jenis dokumen ini bisa diterbitkan untuk perusahaan ini. Dokumen yang sudah terbit tidak terpengaruh saat dinonaktifkan.</small>
                            </span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="pr-panel" aria-labelledby="h-extra">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="h-extra">Data tambahan untuk template</h2>
                        <span class="pr-panel-sub" id="extra-sub">Hanya field yang dibaca template jenis terpilih yang ditampilkan.</span>
                    </div>
                </div>
                <p class="dc-extra-note" id="extra-none">Template jenis ini tidak memakai data tambahan. Cukup aktifkan.</p>
                <div class="emp-fields dt-fields-2">
                    @foreach ($extra as $field => [$label, $hint])
                        <div class="emp-field dc-extra" data-field="{{ $field }}">
                            <label for="{{ $field }}">{{ $label }}</label>
                            <input type="{{ str_starts_with($field, 'pic_email') ? 'email' : 'text' }}" id="{{ $field }}" name="{{ $field }}" maxlength="255"
                                class="emp-input @error($field) is-invalid @enderror" value="{{ old($field, $config?->{$field}) }}"
                                @if ($hint) placeholder="Mis. {{ $hint }}" @endif autocomplete="off">
                            @error($field)<span class="emp-error">{{ $message }}</span>@enderror
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="pr-actions" style="justify-content:flex-end;margin-bottom:var(--pr-space-lg)">
                <a href="{{ route('documentconfig.index') }}" class="pr-btn">Batal</a>
                <button type="submit" id="{{ $submitId }}" class="pr-btn pr-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> {{ $submitLabel }}</button>
            </div>
        </div>

        <aside class="pr-panel">
            <div class="pr-panel-head">
                <div>
                    <h2 class="pr-panel-title">Cara sistem memakai pengaturan ini</h2>
                </div>
            </div>
            <ul class="dt-rules">
                <li>Saat menerbitkan, sistem mencari pengaturan <strong>aktif</strong> untuk perusahaan karyawan dan jenis dokumennya.</li>
                <li>Kalau ada lebih dari satu yang aktif untuk kombinasi yang sama, yang dipakai adalah yang pertama ditemukan. Hindari pengaturan ganda.</li>
                <li>Nomor surat dihitung per pengaturan ini, per bulan.</li>
                @if ($isDw)
                    <li><strong>Pengaturan ini dipakai khusus karyawan DW PT Mahendradata</strong> (diatur di kode), jadi tidak dihitung ganda.</li>
                @endif
            </ul>
            @if ($isEdit)
                <dl class="dt-usage">
                    <div><dt>Dokumen terbit</dt><dd>{{ $fmt($documents) }}</dd></div>
                    <div><dt>Status</dt><dd>{{ $config->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
                </dl>
            @endif
        </aside>
    </div>
</form>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const TYPE_FIELDS = @json($typeFields);     // type_id => [field...] | null (template belum ada)
            const ACTIVE_OTHERS = @json($activeOthers); // "company|type" => [label...]
            const form = document.getElementById(@json($formId));
            const companyEl = document.getElementById('company_id');
            const typeEl = document.getElementById('document_type_id');
            const activeEl = form.querySelector('input[type=checkbox][name=is_active]');
            const dupWarn = document.getElementById('dup-warn');
            const tplWarn = document.getElementById('tpl-warn');
            const none = document.getElementById('extra-none');
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            function render() {
                const fields = typeEl.value ? TYPE_FIELDS[typeEl.value] : [];
                // Field berisi data tetap ditampilkan walau template tidak memakainya, supaya data tidak tersembunyi
                let shown = 0;
                document.querySelectorAll('.dc-extra').forEach(el => {
                    const input = el.querySelector('input');
                    const show = (fields || []).includes(el.dataset.field) || input.value.trim() !== '';
                    el.hidden = !show;
                    if (show) shown++;
                });
                none.hidden = shown > 0 || !typeEl.value;

                tplWarn.hidden = !(typeEl.value && fields === null);
                tplWarn.innerHTML = tplWarn.hidden ? '' : '<strong>File template jenis ini belum ada.</strong> Pengaturan bisa disimpan, tetapi PDF belum bisa dibuat sampai templatenya dibuat.';

                const others = ACTIVE_OTHERS[`${companyEl.value}|${typeEl.value}`] || [];
                dupWarn.hidden = !(activeEl.checked && others.length);
                dupWarn.innerHTML = dupWarn.hidden ? '' : `<strong>Sudah ada pengaturan aktif untuk perusahaan dan jenis ini</strong> (${others.map(esc).join(', ')}). Kalau keduanya aktif, sistem memakai yang pertama ditemukan.`;
            }
            [companyEl, typeEl, activeEl].forEach(el => el.addEventListener('change', render));
            render();

            document.getElementById(@json($submitId)).addEventListener('click', function(e) {
                e.preventDefault();
                if (!companyEl.value) { companyEl.focus(); return Swal.fire({ icon: 'error', title: 'Pilih perusahaan' }); }
                if (!typeEl.value) { typeEl.focus(); return Swal.fire({ icon: 'error', title: 'Pilih jenis dokumen' }); }
                const risky = !dupWarn.hidden;
                Swal.fire({
                    icon: risky ? 'warning' : 'question',
                    title: @json($isEdit ? 'Simpan perubahan?' : 'Simpan pengaturan ini?'),
                    html: `${esc(typeEl.options[typeEl.selectedIndex].text)}<br>untuk ${esc(companyEl.options[companyEl.selectedIndex].text)}${risky ? '<br><br>Kombinasi ini akan punya lebih dari satu pengaturan aktif.' : ''}`,
                    showCancelButton: true, confirmButtonText: @json($submitLabel), cancelButtonText: 'Periksa lagi',
                    confirmButtonColor: '#4f46e5', focusCancel: risky,
                }).then(r => { if (!r.isConfirmed) return; this.disabled = true; form.submit(); });
            });
        });
    </script>
@endpush
