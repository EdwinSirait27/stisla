{{--
    Form departemen bersama untuk create & edit.
    Variabel: $formId, $submitId, $action, $method ('POST'|'PUT'), $submitLabel, $name, $nickname,
              $existing (departemen lain: [['name','nickname'], ...]), $usage (opsional, hanya edit)
--}}
@php
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $fmt = fn($v) => number_format((int) $v, 0, ',', '.');
    $isEdit = isset($usage);
@endphp

@if ($errors->any())
    <div class="emp-errors" role="alert">
        <strong>Belum bisa disimpan.</strong>
        <ul>
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="dp-layout">
    <form id="{{ $formId }}" action="{{ $action }}" method="POST" class="pr-panel" novalidate>
        @csrf
        @if ($method === 'PUT') @method('PUT') @endif

        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title">Data departemen</h2>
                <span class="pr-panel-sub">Tampil sebagai pilihan departemen di data karyawan. Tanda {!! $req !!} wajib diisi.</span>
            </div>
        </div>

        <div class="emp-fields dp-fields-2">
            <div class="emp-field">
                <label for="department_name">Nama departemen {!! $req !!}</label>
                <input type="text" class="emp-input @error('department_name') is-invalid @enderror" id="department_name" name="department_name"
                    value="{{ $name }}" required maxlength="255" autocomplete="off"
                    placeholder="Mis. Human Resource Development"
                    aria-describedby="department_name-help name-similar @error('department_name') department_name-error @enderror"
                    @error('department_name') aria-invalid="true" @enderror>
                @error('department_name')<span class="emp-error" id="department_name-error">{{ $message }}</span>@enderror
                @if ($isEdit && $usage['active'] > 0)
                    <span class="emp-help" id="department_name-help">Nama baru langsung tampil di data {{ $fmt($usage['active']) }} karyawan aktif departemen ini.</span>
                @else
                    <span class="emp-help" id="department_name-help">Nama lengkap dalam bahasa Inggris supaya penulisan seragam.</span>
                @endif
                <p class="dp-similar" id="name-similar" role="status" hidden></p>
            </div>
            <div class="emp-field">
                <label for="nickname">Singkatan {!! $req !!}</label>
                <input type="text" class="emp-input dp-code-input @error('nickname') is-invalid @enderror" id="nickname" name="nickname"
                    value="{{ $nickname }}" required minlength="2" maxlength="10" autocomplete="off" autocapitalize="characters" spellcheck="false"
                    placeholder="Mis. HRD"
                    aria-describedby="nickname-help code-taken @error('nickname') nickname-error @enderror"
                    @error('nickname') aria-invalid="true" @enderror>
                @error('nickname')<span class="emp-error" id="nickname-error">{{ $message }}</span>@enderror
                <span class="emp-help" id="nickname-help">2 sampai 10 huruf atau angka, tanpa spasi. Disimpan dengan huruf kapital.</span>
                <p class="dp-similar" id="code-taken" role="status" hidden></p>
            </div>
        </div>

        <ul class="dp-rules">
            <li>Nama dan singkatan tidak boleh sama dengan departemen lain.</li>
            <li>Periksa daftar di samping sebelum menyimpan, supaya tidak ada departemen ganda dengan nama berbeda.</li>
            @if ($isEdit)
                <li>Kalau karyawan pindah departemen, ubah di data karyawannya, bukan di sini.</li>
            @endif
        </ul>

        <div class="dp-savebar">
            <a href="{{ route('pages.Department') }}" class="pr-btn">Batal</a>
            <button type="submit" id="{{ $submitId }}" class="pr-btn pr-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> {{ $submitLabel }}</button>
        </div>
    </form>

    <aside class="pr-panel" aria-labelledby="dp-list-title">
        @if ($isEdit)
            <div class="pr-panel-head">
                <div>
                    <h2 class="pr-panel-title">Isi departemen ini</h2>
                    <span class="pr-panel-sub">Karyawan yang departemennya saat ini {{ $name }}.</span>
                </div>
            </div>
            <dl class="dp-usage">
                <div><dt>Aktif</dt><dd>{{ $fmt($usage['active']) }}</dd></div>
                <div><dt>Pending atau On Leave</dt><dd>{{ $fmt($usage['other']) }}</dd></div>
            </dl>
            @if ($usage['active'] > 0)
                <p class="dp-breakdown-title">Manager</p>
                @if ($usage['managers'])
                    <ul class="dp-breakdown">@foreach ($usage['managers'] as $m)<li><span>{{ $m }}</span></li>@endforeach</ul>
                @else
                    <p class="dp-usage-note">Belum ada karyawan aktif bertanda manager di departemen ini. Tandai di data karyawan.</p>
                @endif

                @foreach (['positions' => 'Jabatan', 'stores' => 'Lokasi'] as $key => $title)
                    @php($rows = $usage[$key])
                    <p class="dp-breakdown-title">{{ $title }} ({{ count($rows) }})</p>
                    <ul class="dp-breakdown">
                        @foreach (array_slice($rows, 0, 6, true) as $label => $count)
                            <li><span>{{ $label }}</span><b>{{ $fmt($count) }}</b></li>
                        @endforeach
                        @if (count($rows) > 6)
                            <li class="dp-more">dan {{ count($rows) - 6 }} {{ strtolower($title) }} lainnya</li>
                        @endif
                    </ul>
                @endforeach
            @else
                <p class="dp-usage-note">Belum ada karyawan aktif di departemen ini.</p>
            @endif
        @endif

        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="dp-list-title">{{ $isEdit ? 'Departemen lain' : 'Departemen yang sudah ada' }}</h2>
                <span class="pr-panel-sub">{{ count($existing) }} departemen. Nama mirip dan singkatan yang sama ditandai saat Anda mengetik.</span>
            </div>
        </div>
        @if (!$existing)
            <p class="dp-list-empty">Belum ada departemen lain.</p>
        @else
            <ul class="dp-list" id="dp-list">
                @foreach ($existing as $d)
                    <li data-name="{{ $d['name'] }}" data-code="{{ $d['nickname'] }}"><span>{{ $d['name'] }}</span><b>{{ $d['nickname'] ?: 'Tanpa singkatan' }}</b></li>
                @endforeach
            </ul>
        @endif
    </aside>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const form = document.getElementById(@json($formId));
            const nameEl = document.getElementById('department_name');
            const codeEl = document.getElementById('nickname');
            const nameNote = document.getElementById('name-similar');
            const codeNote = document.getElementById('code-taken');
            const items = [...document.querySelectorAll('#dp-list li')];
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            // Bandingkan tanpa huruf besar/kecil, spasi, tanda baca, dan kata umum
            const key = s => String(s).toLowerCase().replace(/\b(department|departemen|dept|divisi|division|and|dan)\b/g, '').replace(/[^a-z0-9]/g, '');
            const prefix = (a, b) => { let i = 0; while (i < a.length && a[i] === b[i]) i++; return i; };
            const cleanCode = s => String(s).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10);

            function check() {
                const k = key(nameEl.value), code = cleanCode(codeEl.value);
                const names = [], codes = [];
                items.forEach(li => {
                    const other = key(li.dataset.name);
                    // Sama, saling memuat, atau berawalan sama minimal 8 huruf ("Human Resources" vs "Human Resource Development")
                    const nameHit = k.length >= 3 && other.length >= 3 && (k === other || k.includes(other) || other.includes(k) || prefix(k, other) >= 8);
                    const codeHit = code.length >= 2 && cleanCode(li.dataset.code) === code;
                    li.classList.toggle('is-match', nameHit || codeHit);
                    if (nameHit) names.push(li.dataset.name);
                    if (codeHit) codes.push(li.dataset.name);
                });
                nameNote.hidden = !names.length;
                nameNote.innerHTML = names.length ? `<strong>Mirip dengan ${names.map(esc).join(', ')}.</strong> Pastikan ini bukan departemen yang sama.` : '';
                codeNote.hidden = !codes.length;
                codeNote.innerHTML = codes.length ? `<strong>${esc(code)} sudah dipakai ${codes.map(esc).join(', ')}.</strong> Pilih singkatan lain.` : '';
            }
            // Singkatan dirapikan saat diketik: huruf kapital, tanpa spasi atau tanda baca
            codeEl.addEventListener('input', () => {
                const pos = codeEl.selectionStart, before = codeEl.value, after = cleanCode(before);
                if (before !== after) { codeEl.value = after; codeEl.setSelectionRange(Math.min(pos, after.length), Math.min(pos, after.length)); }
                check();
            });
            nameEl.addEventListener('input', check);
            check();

            document.getElementById(@json($submitId)).addEventListener('click', function(e) {
                e.preventDefault();
                const name = nameEl.value.trim().replace(/\s+/g, ' '), code = cleanCode(codeEl.value);
                if (!name) { nameEl.focus(); return Swal.fire({ icon: 'error', title: 'Nama departemen belum diisi' }); }
                if (code.length < 2) { codeEl.focus(); return Swal.fire({ icon: 'error', title: 'Singkatan belum diisi', text: 'Isi 2 sampai 10 huruf atau angka.' }); }
                if (!codeNote.hidden) { codeEl.focus(); return Swal.fire({ icon: 'error', title: 'Singkatan sudah dipakai', text: `${code} sudah dipakai departemen lain.` }); }
                const similar = !nameNote.hidden;
                Swal.fire({
                    icon: similar ? 'warning' : 'question',
                    title: @json($method === 'PUT' ? 'Simpan perubahan?' : 'Tambah departemen ini?'),
                    html: `<b>${esc(name)}</b> (${esc(code)})${similar ? '<br>Namanya mirip departemen yang sudah ada.' : ''}`,
                    showCancelButton: true, confirmButtonText: @json($submitLabel), cancelButtonText: 'Periksa lagi',
                    confirmButtonColor: '#4f46e5', focusCancel: similar,
                }).then(r => {
                    if (!r.isConfirmed) return;
                    this.disabled = true;
                    form.submit();
                });
            });

            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
            @endif
        });
    </script>
@endpush
