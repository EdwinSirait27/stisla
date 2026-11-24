{{--
    Form komponen payroll bersama untuk create & edit.
    Variabel: $formId, $submitId, $action, $method ('POST'|'PUT'), $submitLabel, $component (null saat create),
              $types, $sources (Payrollcomponents::SOURCES), $existing, $usage (null saat create), $latestPeriod
--}}
@php
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $isEdit = (bool) $component;
    $name = old('component_name', $component?->component_name);
    $type = old('type', $component?->type);
    $fixed = (bool) old('is_fixed', $component?->is_fixed ?? false);
    $burden = (bool) old('is_employer_burden', $component?->is_employer_burden ?? false);
    $typeLabel = ['Income' => 'Pendapatan', 'Deduction' => 'Potongan'];
    $rp = fn($v) => 'Rp' . number_format((float) $v, 0, ',', '.');
@endphp

@if ($errors->any())
    <div class="emp-errors" role="alert">
        <strong>Belum bisa disimpan.</strong>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="pc-layout">
    <form id="{{ $formId }}" action="{{ $action }}" method="POST" class="pr-panel" novalidate>
        @csrf
        @if ($method === 'PUT') @method('PUT') @endif

        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title">Data komponen</h2>
                <span class="pr-panel-sub">Tampil sebagai baris di slip gaji. Tanda {!! $req !!} wajib diisi.</span>
            </div>
        </div>

        <div class="emp-fields pc-fields-2">
            <div class="emp-field">
                <label for="component_name">Nama komponen {!! $req !!}</label>
                <input type="text" class="emp-input @error('component_name') is-invalid @enderror" id="component_name" name="component_name"
                    value="{{ $name }}" required maxlength="255" autocomplete="off" autocapitalize="characters"
                    placeholder="Mis. MEAL ALLOWANCE"
                    aria-describedby="component_name-help name-similar rename-warn @error('component_name') component_name-error @enderror"
                    @error('component_name') aria-invalid="true" @enderror>
                @error('component_name')<span class="emp-error" id="component_name-error">{{ $message }}</span>@enderror
                <span class="emp-help" id="component_name-help">Disimpan dengan huruf kapital. Beberapa nama dipakai langsung oleh rumus payroll, lihat pratinjau di samping.</span>
                <p class="pc-similar pc-rename" id="rename-warn" role="status" hidden></p>
                <p class="pc-similar" id="name-similar" role="status" hidden></p>
            </div>
            <div class="emp-field">
                <label for="type">Jenis {!! $req !!}</label>
                <select id="type" name="type" class="emp-select @error('type') is-invalid @enderror" required @error('type') aria-invalid="true" @enderror>
                    <option value="" @selected(!$type)>Pilih jenis</option>
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}" @selected($type === $key)>{{ $typeLabel[$key] ?? $label }}</option>
                    @endforeach
                </select>
                @error('type')<span class="emp-error">{{ $message }}</span>@enderror
            </div>

            <div class="emp-field is-wide pc-wide">
                <div class="pc-checks">
                    <label class="pc-check">
                        <input type="hidden" name="is_fixed" value="0">
                        <input type="checkbox" name="is_fixed" id="is_fixed" value="1" @checked($fixed)>
                        <span>
                            <strong>Nilai tetap, ditambahkan otomatis</strong>
                            <small>Ikut ditambahkan ke setiap payroll saat payroll dibuat, nilainya diambil dari data gaji karyawan.</small>
                        </span>
                    </label>
                    <label class="pc-check">
                        <input type="hidden" name="is_employer_burden" value="0">
                        <input type="checkbox" name="is_employer_burden" id="is_employer_burden" value="1" @checked($burden)>
                        <span>
                            <strong>Tanggungan perusahaan</strong>
                            <small>Dibayar perusahaan, tidak menambah atau mengurangi gaji bersih karyawan.</small>
                        </span>
                    </label>
                </div>
                @error('is_fixed')<span class="emp-error">{{ $message }}</span>@enderror
                @error('is_employer_burden')<span class="emp-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <ul class="pc-rules">
            <li>Nama komponen tidak boleh sama dengan komponen lain.</li>
            @if ($isEdit && $usage && $usage['payrolls'] > 0)
                <li>Sudah dipakai di {{ number_format($usage['payrolls'], 0, ',', '.') }} slip. Perubahan jenis atau tanggungan berlaku saat payroll dihitung ulang, payroll yang sudah final tidak berubah.</li>
            @endif
        </ul>

        <div class="pc-savebar">
            <a href="{{ route('payrollcomponents') }}" class="pr-btn">Batal</a>
            <button type="submit" id="{{ $submitId }}" class="pr-btn pr-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> {{ $submitLabel }}</button>
        </div>
    </form>

    <aside class="pr-panel" aria-labelledby="pc-preview-title">
        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="pc-preview-title">Pratinjau perhitungan</h2>
                <span class="pr-panel-sub">Berubah mengikuti isian di samping.</span>
            </div>
        </div>
        <dl class="pc-preview" aria-live="polite">
            <div><dt>Dari mana nilainya</dt><dd id="pv-how">–</dd></div>
            <div><dt>Efek ke gaji bersih karyawan</dt><dd id="pv-effect">–</dd></div>
        </dl>

        @if ($isEdit && $usage)
            <dl class="pc-usage">
                <div><dt>Dipakai di</dt><dd>{{ number_format($usage['payrolls'], 0, ',', '.') }} <small>slip</small></dd></div>
                <div><dt>{{ $latestPeriod ? 'Periode ' . $latestPeriod : 'Periode terakhir' }}</dt><dd>{{ $rp($usage['latest_total']) }}</dd></div>
            </dl>
            @if ($usage['latest_people'] > 0)
                <p class="pc-usage-note">{{ number_format($usage['latest_people'], 0, ',', '.') }} karyawan mendapat komponen ini di periode {{ $latestPeriod }}.</p>
            @endif
        @endif

        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="pc-list-title">{{ $isEdit ? 'Komponen lain' : 'Komponen yang sudah ada' }}</h2>
                <span class="pr-panel-sub">{{ $existing->count() }} komponen. Nama yang mirip ditandai saat Anda mengetik.</span>
            </div>
        </div>
        <ul class="pc-list" id="pc-list">
            @foreach ($existing as $c)
                <li data-name="{{ $c->component_name }}"><span>{{ $c->component_name }}</span><b>{{ $typeLabel[$c->type] ?? $c->type }}</b></li>
            @endforeach
        </ul>
    </aside>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            // Sama dengan PayrollcomponentsController::describe() dan Payrollcomponents::SOURCES
            const SOURCES = @json($sources);
            const ORIGINAL = @json($isEdit ? $component->component_name : null);
            const form = document.getElementById(@json($formId));
            const nameEl = document.getElementById('component_name');
            const typeEl = document.getElementById('type');
            const fixedEl = document.getElementById('is_fixed');
            const burdenEl = document.getElementById('is_employer_burden');
            const howEl = document.getElementById('pv-how');
            const effectEl = document.getElementById('pv-effect');
            const renameEl = document.getElementById('rename-warn');
            const similarEl = document.getElementById('name-similar');
            const items = [...document.querySelectorAll('#pc-list li')];
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const norm = s => String(s).toUpperCase().trim().replace(/\s+/g, ' ');
            const key = s => norm(s).replace(/[^A-Z0-9]/g, '');

            function describe(name, fixed) {
                const src = SOURCES[norm(name)];
                const [mode, from] = src || [null, null];
                if (fixed) {
                    return mode === 'salary'
                        ? [false, `Ditambahkan otomatis saat payroll dibuat, nilainya dari ${from}.`]
                        : [true, 'Ditandai nilai tetap, tetapi tidak ada rumus untuk nama ini, jadi nilainya selalu 0 saat payroll dibuat.'];
                }
                if (mode === 'manual' || mode === 'overtime') return [false, `Nilainya dari ${from}.`];
                if (mode === 'salary') return [true, `Nama ini punya rumus dari ${from}, tetapi tidak ditandai nilai tetap, jadi tidak ditambahkan saat payroll dibuat.`];
                return [true, 'Belum dipakai perhitungan payroll. Nama ini tidak dikenal rumus payroll dan tidak ditandai nilai tetap.'];
            }

            function render() {
                const name = nameEl.value;
                if (!name.trim()) { howEl.textContent = 'Isi nama komponen dulu.'; howEl.classList.remove('is-warn'); }
                else { const [warn, text] = describe(name, fixedEl.checked); howEl.textContent = text; howEl.classList.toggle('is-warn', warn); }

                const t = typeEl.value;
                effectEl.innerHTML = !t ? 'Pilih jenis dulu.'
                    : burdenEl.checked ? '<span class="pc-effect is-none">Tidak berubah.</span> Ditanggung perusahaan, tidak dihitung di gaji bersih.'
                    : t === 'Income' ? '<span class="pc-effect is-up">Menambah gaji bersih.</span>'
                    : '<span class="pc-effect is-down">Mengurangi gaji bersih.</span>';

                // Ganti nama komponen yang dipakai rumus payroll
                const renamed = ORIGINAL && norm(name) !== norm(ORIGINAL) && SOURCES[norm(ORIGINAL)];
                renameEl.hidden = !renamed;
                renameEl.innerHTML = renamed ? `<strong>Nama ${esc(ORIGINAL)} dipakai rumus payroll.</strong> Setelah diganti, nilainya tidak lagi dihitung otomatis atau terbaca di form payroll.` : '';

                const k = key(name), hits = [];
                items.forEach(li => {
                    const o = key(li.dataset.name);
                    const hit = k.length >= 3 && o.length >= 3 && (k === o || k.includes(o) || o.includes(k));
                    li.classList.toggle('is-match', hit);
                    if (hit) hits.push(li.dataset.name);
                });
                similarEl.hidden = !hits.length;
                similarEl.innerHTML = hits.length ? `<strong>Mirip dengan ${hits.map(esc).join(', ')}.</strong> Pastikan ini bukan komponen yang sama.` : '';
            }
            [nameEl, typeEl, fixedEl, burdenEl].forEach(el => el.addEventListener(el === nameEl ? 'input' : 'change', render));
            render();

            document.getElementById(@json($submitId)).addEventListener('click', function(e) {
                e.preventDefault();
                const name = norm(nameEl.value);
                if (!name) { nameEl.focus(); return Swal.fire({ icon: 'error', title: 'Nama komponen belum diisi' }); }
                if (!typeEl.value) { typeEl.focus(); return Swal.fire({ icon: 'error', title: 'Pilih jenis komponen' }); }
                const risky = !renameEl.hidden || !similarEl.hidden || howEl.classList.contains('is-warn');
                Swal.fire({
                    icon: risky ? 'warning' : 'question',
                    title: @json($isEdit ? 'Simpan perubahan?' : 'Tambah komponen ini?'),
                    html: `<b>${esc(name)}</b><br>${esc(howEl.textContent)}`,
                    showCancelButton: true, confirmButtonText: @json($submitLabel), cancelButtonText: 'Periksa lagi',
                    confirmButtonColor: '#4f46e5', focusCancel: risky,
                }).then(r => {
                    if (!r.isConfirmed) return;
                    this.disabled = true;
                    form.submit();
                });
            });
        });
    </script>
@endpush
