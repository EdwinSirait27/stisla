{{--
    Form SK bersama untuk buat & edit (Draft).
    Variabel: $skLetter (null saat buat), $sktypes, $companies, $employees, $approvers, $positions, $departments,
              $stores, $gradings, $groups, $empTypes
--}}
@php
    $isEdit = (bool) $skLetter;
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $v = fn($field, $default = null) => old($field, $isEdit ? $skLetter->{$field} : $default);
    $dateVal = fn($field) => old($field, $isEdit && $skLetter->{$field} ? $skLetter->{$field}->format('Y-m-d') : null);

    // Karyawan di SK: dari old() setelah validasi gagal, atau dari SK yang diedit
    $initialRows = old('employees') ?? ($isEdit ? $skLetter->employees->map(fn($e) => [
        'employee_id'          => $e->id,
        'new_position_id'      => $e->pivot->new_position_id,
        'new_department_id'    => $e->pivot->new_department_id,
        'new_store_id'         => $e->pivot->new_store_id,
        'new_grading_id'       => $e->pivot->new_grading_id,
        'new_group_id'         => $e->pivot->new_group_id,
        'new_status_employee'  => $e->pivot->new_status_employee,
        'basic_salary'         => $e->pivot->basic_salary !== null ? (int) $e->pivot->basic_salary : null,
        'positional_allowance' => $e->pivot->positional_allowance !== null ? (int) $e->pivot->positional_allowance : null,
        'daily_rate'           => $e->pivot->daily_rate !== null ? (int) $e->pivot->daily_rate : null,
        'notes'                => $e->pivot->notes,
    ])->values()->all() : []);

    $clauses = fn($key, $col) => old($key) ?? ($isEdit ? $skLetter->{$key}->pluck($col)->all() : []);
    // Isi "menetapkan" lama berupa HTML dari editor: tampilkan sebagai teks biasa
    $menetapkan = old('menetapkan_text', $isEdit ? trim(html_entity_decode(strip_tags(preg_replace('#<(br|/p|/li)\s*/?>#i', "\n", (string) $skLetter->menetapkan_text)), ENT_QUOTES)) : '');

    $typeData = $sktypes->mapWithKeys(fn($t) => [$t->id => [
        'salary' => (bool) $t->affects_salary, 'position' => (bool) $t->affects_position,
        'status' => (bool) $t->affects_status, 'termination' => $t->categories === 'Termination',
    ]]);
    $empData = $employees->mapWithKeys(fn($e) => [$e->id => [
        'name' => $e->employee_name, 'nip' => $e->employee_pengenal, 'type' => $e->status_employee,
        'position' => $e->position_id, 'department' => $e->department_id, 'store' => $e->store_id,
        'grading' => $e->grading?->grading_name, 'group' => $e->group?->group_name,
    ]]);
@endphp

@if ($errors->any())
    <div class="emp-errors" role="alert">
        <strong>Belum bisa disimpan.</strong>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
@if (session('error'))<p class="sk-notice is-warn" role="alert">{{ session('error') }}</p>@endif
@if ($isEdit && $skLetter->returned_at)
    <p class="sk-notice is-warn" role="status"><strong>Dikembalikan untuk diperbaiki:</strong> {{ $skLetter->returned_note }}</p>
@endif

<form id="sk-form" method="POST" action="{{ $isEdit ? route('SkLetters.update', $skLetter->id) : route('SkLetters.store') }}" novalidate>
    @csrf
    @if ($isEdit) @method('PUT') @endif

    {{-- 1. Data SK --}}
    <section class="pr-panel" aria-labelledby="h-data">
        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="h-data">Data SK</h2>
                <span class="pr-panel-sub">{{ $isEdit ? 'Nomor ' . $skLetter->sk_number . ' tetap.' : 'Nomor SK dibuat otomatis saat disimpan.' }} Tanda {!! $req !!} wajib diisi.</span>
            </div>
        </div>
        <div class="emp-fields sk-fields-3">
            <div class="emp-field">
                <label for="sk_type_id">Jenis SK {!! $req !!}</label>
                <select id="sk_type_id" name="sk_type_id" class="emp-input" required>
                    <option value="">Pilih jenis SK</option>
                    @foreach ($sktypes as $t)
                        <option value="{{ $t->id }}" @selected($v('sk_type_id') == $t->id)>{{ $t->sk_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="emp-field">
                <label for="company_id">Perusahaan penerbit {!! $req !!}</label>
                <select id="company_id" name="company_id" class="emp-input" required>
                    <option value="">Pilih perusahaan</option>
                    @foreach ($companies as $id => $name)
                        <option value="{{ $id }}" @selected($v('company_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="emp-field">
                <label for="location">Ditetapkan di</label>
                <input type="text" id="location" name="location" class="emp-input" maxlength="255" value="{{ $v('location', 'Denpasar') }}">
            </div>
            <div class="emp-field sk-span-all">
                <label for="title">Judul SK {!! $req !!}</label>
                <input type="text" id="title" name="title" class="emp-input" maxlength="255" required value="{{ $v('title') }}" placeholder="Mis. Promosi jabatan Supervisor Store">
                <span class="emp-help">Dicetak di PDF dengan huruf kapital.</span>
            </div>
            <div class="emp-field">
                <label for="effective_date">Berlaku mulai {!! $req !!}</label>
                <input type="date" id="effective_date" name="effective_date" class="emp-input" required value="{{ $dateVal('effective_date') }}">
                <span class="emp-help">Jabatan dan penempatan berubah pada tanggal ini; gaji baru berlaku mulai tanggal ini.</span>
            </div>
            <div class="emp-field">
                <label for="inactive_date">Berlaku sampai</label>
                <input type="date" id="inactive_date" name="inactive_date" class="emp-input" value="{{ $dateVal('inactive_date') }}">
                <span class="emp-help">Kosongkan kalau tanpa batas waktu.</span>
            </div>
            <div class="emp-field">
                <label>Yang diubah jenis ini</label>
                <p class="sk-effects" id="sk-effects">Pilih jenis SK dulu.</p>
            </div>
        </div>
    </section>

    {{-- 2. Karyawan --}}
    <section class="pr-panel" aria-labelledby="h-people">
        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="h-people">Karyawan {!! $req !!}</h2>
                <span class="pr-panel-sub" id="people-sub">Isi hanya yang berubah; kolom kosong berarti tetap.</span>
            </div>
        </div>
        <div class="sk-add">
            <div class="emp-field">
                <label for="add-employee">Tambah karyawan</label>
                <select id="add-employee" data-placeholder="Cari nama atau NIP">
                    <option value=""></option>
                    @foreach ($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->employee_name }}{{ $e->employee_pengenal ? ' (' . $e->employee_pengenal . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <ul class="sk-people" id="people" aria-live="polite"></ul>
        <p class="sk-empty" id="people-empty">Belum ada karyawan. Pilih dari daftar di atas.</p>
    </section>

    {{-- 3. Isi SK --}}
    <section class="pr-panel" aria-labelledby="h-content">
        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="h-content">Isi SK</h2>
                <span class="pr-panel-sub">Urutan di sini sama dengan urutan di PDF. Baris kosong diabaikan.</span>
            </div>
        </div>
        <div class="emp-fields">
            @foreach ([['menimbang', 'Menimbang', 'content_menimbang', 'Mis. bahwa karyawan menunjukkan kinerja baik selama masa percobaan'],
                       ['mengingat', 'Mengingat', 'content_mengingat', 'Mis. Peraturan Perusahaan pasal 12']] as [$key, $label, $col, $ph])
                <div class="emp-field">
                    <label>{{ $label }}</label>
                    <ol class="sk-clauses" data-clauses="{{ $key }}" data-placeholder="{{ $ph }}">
                        @foreach ($clauses($key, $col) as $text)
                            <li class="sk-clause"><span class="sk-clause-num"></span><textarea class="emp-input" name="{{ $key }}[]" rows="2" aria-label="{{ $label }}">{{ $text }}</textarea><button type="button" class="sk-icon" data-remove-clause aria-label="Hapus baris"><i class="fas fa-xmark" aria-hidden="true"></i></button></li>
                        @endforeach
                    </ol>
                    <button type="button" class="pr-btn pr-btn-sm" data-add-clause="{{ $key }}" style="margin-top:8px"><i class="fas fa-plus" aria-hidden="true"></i> Tambah {{ strtolower($label) }}</button>
                </div>
            @endforeach
            <div class="emp-field">
                <label for="menetapkan_text">Menetapkan</label>
                <textarea id="menetapkan_text" name="menetapkan_text" class="emp-input" rows="3" maxlength="5000" placeholder="Mis. Keputusan Direksi tentang pengangkatan karyawan tetap">{{ $menetapkan }}</textarea>
            </div>
            <div class="emp-field">
                <label>Memutuskan</label>
                <ol class="sk-clauses" data-clauses="keputusan" data-placeholder="Mis. Terhitung mulai tanggal berlaku, karyawan tersebut menjabat sebagai ...">
                    @foreach ($clauses('keputusan', 'content_keputusan') as $text)
                        <li class="sk-clause"><span class="sk-clause-num"></span><textarea class="emp-input" name="keputusan[]" rows="2" aria-label="Keputusan">{{ $text }}</textarea><button type="button" class="sk-icon" data-remove-clause aria-label="Hapus baris"><i class="fas fa-xmark" aria-hidden="true"></i></button></li>
                    @endforeach
                </ol>
                <button type="button" class="pr-btn pr-btn-sm" data-add-clause="keputusan" style="margin-top:8px"><i class="fas fa-plus" aria-hidden="true"></i> Tambah keputusan</button>
            </div>
        </div>
    </section>

    {{-- 4. Persetujuan --}}
    <section class="pr-panel" aria-labelledby="h-approval">
        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="h-approval">Persetujuan</h2>
                <span class="pr-panel-sub">Yang mengajukan tercatat sebagai penanda tangan pertama. Approver di bawah menyetujui berurutan setelahnya.</span>
            </div>
        </div>
        <div class="emp-fields sk-fields-3">
            <div class="emp-field">
                <label for="approver_2">Approver 2 {!! $req !!}</label>
                <select id="approver_2" name="approver_2" class="sk-person-select" data-placeholder="Pilih karyawan" required>
                    <option value=""></option>
                    @foreach ($approvers as $p)
                        <option value="{{ $p->id }}" @selected($v('approver_2') == $p->id)>{{ $p->employee_name }}{{ $p->position ? ' - ' . $p->position : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="emp-field">
                <label for="approver_3">Approver 3</label>
                <select id="approver_3" name="approver_3" class="sk-person-select" data-placeholder="Tidak ada">
                    <option value=""></option>
                    @foreach ($approvers as $p)
                        <option value="{{ $p->id }}" @selected($v('approver_3') == $p->id)>{{ $p->employee_name }}{{ $p->position ? ' - ' . $p->position : '' }}</option>
                    @endforeach
                </select>
                <span class="emp-help">Opsional. Kalau kosong, SK sah setelah approver 2 menyetujui.</span>
            </div>
            <div class="emp-field">
                <label for="notes">Catatan internal</label>
                <textarea id="notes" name="notes" class="emp-input" rows="2" maxlength="2000">{{ $v('notes') }}</textarea>
                <span class="emp-help">Tidak dicetak di PDF.</span>
            </div>
        </div>
    </section>

    <div class="sk-savebar">
        <span class="sk-savebar-note" id="save-note">Disimpan sebagai Draft. Ajukan untuk persetujuan dari halaman detail.</span>
        <a href="{{ $isEdit ? route('SkLetters.show', $skLetter->id) : route('SkLetters') }}" class="pr-btn">Batal</a>
        <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> {{ $isEdit ? 'Simpan perubahan' : 'Simpan Draft' }}</button>
    </div>
</form>

<template id="clause-template">
    <li class="sk-clause"><span class="sk-clause-num"></span><textarea class="emp-input" rows="2"></textarea><button type="button" class="sk-icon" data-remove-clause aria-label="Hapus baris"><i class="fas fa-xmark" aria-hidden="true"></i></button></li>
</template>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Setelah ready: inisialisasi global Stisla sudah jalan, jadi select2 di sini tidak dibungkus ulang
        $(function() {
            const TYPES = @json($typeData);
            const EMP = @json($empData);
            const LISTS = {
                position: @json($positions->pluck('name', 'id')),
                department: @json($departments->pluck('department_name', 'id')),
                store: @json($stores->pluck('name', 'id')),
                grading: @json($gradings->pluck('grading_name', 'id')),
                group: @json($groups->pluck('group_name', 'id')),
            };
            const EMP_TYPES = @json($empTypes);
            let rows = @json(array_values($initialRows));
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const money = v => (v === null || v === undefined || v === '') ? '' : Number(String(v).replace(/\D/g, '')).toLocaleString('id-ID');
            const peopleEl = document.getElementById('people');

            const type = () => TYPES[$('#sk_type_id').val()] || null;

            function effectsText() {
                const t = type();
                if (!t) return 'Pilih jenis SK dulu.';
                const parts = [];
                if (t.position) parts.push('jabatan, departemen, lokasi, grading, dan group');
                if (t.salary) parts.push('gaji');
                if (t.status) parts.push(t.termination ? 'status karyawan menjadi Resign' : 'jenis karyawan');
                return parts.length ? 'Setelah disetujui penuh, sistem mengubah ' + parts.join(', ') + ' karyawan sesuai isian di bawah.' : 'Jenis ini tidak mengubah data karyawan.';
            }

            const options = (list, selected, empty) => `<option value="">${empty}</option>` + Object.entries(list)
                .sort((a, b) => String(a[1]).localeCompare(String(b[1])))
                .map(([id, name]) => `<option value="${esc(id)}" ${id === selected ? 'selected' : ''}>${esc(name)}</option>`).join('');

            function rowHtml(r, i) {
                const e = EMP[r.employee_id] || { name: 'Karyawan tidak aktif', nip: '' };
                const t = type() || {};
                const now = [LISTS.position[e.position], LISTS.department[e.department], LISTS.store[e.store]].filter(Boolean).map(esc).join(', ');
                const f = (name) => `employees[${i}][${name}]`;
                const sel = (key, name, label) => `<div class="emp-field"><label for="r${i}-${name}">${label}</label><select id="r${i}-${name}" class="emp-input" name="${f(name)}">${options(LISTS[key], r[name] || '', 'Tetap')}</select></div>`;
                const amount = (name, label) => `<div class="emp-field"><label for="r${i}-${name}">${label}</label><input id="r${i}-${name}" class="emp-input sk-money" name="${f(name)}" inputmode="numeric" autocomplete="off" placeholder="Tetap" value="${esc(money(r[name]))}"></div>`;
                return `<li class="sk-person" data-i="${i}">
                    <div class="sk-person-head">
                        <div><strong>${esc(e.name)}</strong><span class="sk-person-now">${e.nip ? 'NIP ' + esc(e.nip) + '. ' : ''}Sekarang: ${now || 'penempatan belum diisi'}${e.type ? ', ' + esc(e.type) : ''}${e.grading ? ', grading ' + esc(e.grading) : ''}</span></div>
                        <button type="button" class="pr-btn pr-btn-sm pr-btn-danger" data-remove-person="${i}" aria-label="Keluarkan ${esc(e.name)} dari SK"><i class="fas fa-xmark" aria-hidden="true"></i> Keluarkan</button>
                    </div>
                    <input type="hidden" name="${f('employee_id')}" value="${esc(r.employee_id)}">
                    <div class="emp-fields">
                        <fieldset class="sk-span-all" ${t.position ? '' : 'disabled hidden'} style="display:contents">
                            <p class="sk-group-title">Jabatan dan penempatan baru</p>
                            ${sel('position', 'new_position_id', 'Jabatan')}${sel('department', 'new_department_id', 'Departemen')}${sel('store', 'new_store_id', 'Lokasi')}
                            ${sel('grading', 'new_grading_id', 'Grading')}${sel('group', 'new_group_id', 'Group')}
                        </fieldset>
                        <fieldset ${t.status && !t.termination ? '' : 'disabled hidden'} style="display:contents">
                            <div class="emp-field"><label for="r${i}-type">Jenis karyawan baru</label><select id="r${i}-type" class="emp-input" name="${f('new_status_employee')}"><option value="">Tetap</option>${EMP_TYPES.map(x => `<option ${r.new_status_employee === x ? 'selected' : ''}>${esc(x)}</option>`).join('')}</select></div>
                        </fieldset>
                        <fieldset ${t.salary ? '' : 'disabled hidden'} style="display:contents">
                            <p class="sk-group-title">Gaji baru (Rupiah)</p>
                            ${amount('basic_salary', 'Gaji pokok')}${amount('positional_allowance', 'Tunjangan jabatan')}${amount('daily_rate', 'Daily rate (DW)')}
                        </fieldset>
                        <div class="emp-field sk-span-all"><label for="r${i}-notes">Keterangan di PDF</label><input id="r${i}-notes" class="emp-input" name="${f('notes')}" maxlength="1000" value="${esc(r.notes)}"></div>
                    </div>
                </li>`;
            }

            // Simpan isian yang sedang diketik sebelum menggambar ulang (ganti jenis / tambah / keluarkan)
            function collect() {
                peopleEl.querySelectorAll('.sk-person').forEach(li => {
                    const r = rows[+li.dataset.i]; if (!r) return;
                    li.querySelectorAll('[name]').forEach(el => {
                        const m = el.name.match(/\[([a-z_]+)\]$/); if (m) r[m[1]] = el.value;
                    });
                });
            }
            function render() {
                peopleEl.innerHTML = rows.map(rowHtml).join('');
                document.getElementById('people-empty').hidden = rows.length > 0;
                document.getElementById('people-sub').textContent = rows.length
                    ? `${rows.length} karyawan. Isi hanya yang berubah; kolom kosong berarti tetap.`
                    : 'Isi hanya yang berubah; kolom kosong berarti tetap.';
                $('#sk-effects').text(effectsText());
                // Karyawan yang sudah ada tidak bisa ditambah dua kali
                $('#add-employee option').each(function() { this.disabled = !!this.value && rows.some(r => r.employee_id === this.value); });
            }

            $('#add-employee').select2({ width: '100%', placeholder: 'Cari nama atau NIP' }).on('select2:select', function(e) {
                collect();
                rows.push({ employee_id: e.params.data.id });
                $(this).val('').trigger('change.select2');
                render();
                peopleEl.lastElementChild?.querySelector('select, input:not([type=hidden])')?.focus();
            });
            $('.sk-person-select').each(function() {
                $(this).select2({ width: '100%', placeholder: this.dataset.placeholder, allowClear: !this.required });
            });
            $('#sk_type_id').on('change', () => { collect(); render(); });
            peopleEl.addEventListener('click', e => {
                const b = e.target.closest('[data-remove-person]'); if (!b) return;
                collect(); rows.splice(+b.dataset.removePerson, 1); render();
            });
            // Nominal: titik ribuan saat diketik
            peopleEl.addEventListener('input', e => {
                if (!e.target.classList.contains('sk-money')) return;
                const pos = e.target.value.length - e.target.selectionStart;
                e.target.value = money(e.target.value);
                const p = Math.max(0, e.target.value.length - pos); e.target.setSelectionRange(p, p);
            });

            // Daftar pasal
            const tpl = document.getElementById('clause-template');
            function renumber(list) { list.querySelectorAll('.sk-clause-num').forEach((s, i) => s.textContent = (i + 1) + '.'); }
            document.querySelectorAll('[data-clauses]').forEach(list => {
                if (!list.children.length) addClause(list, false);
                renumber(list);
            });
            function addClause(list, focus = true) {
                const li = tpl.content.firstElementChild.cloneNode(true);
                const ta = li.querySelector('textarea');
                ta.name = list.dataset.clauses + '[]'; ta.placeholder = list.dataset.placeholder; ta.setAttribute('aria-label', list.dataset.clauses);
                list.appendChild(li); renumber(list); if (focus) ta.focus();
            }
            document.querySelectorAll('[data-add-clause]').forEach(b => b.addEventListener('click', () => addClause(document.querySelector(`[data-clauses="${b.dataset.addClause}"]`))));
            document.getElementById('sk-form').addEventListener('click', e => {
                const b = e.target.closest('[data-remove-clause]'); if (!b) return;
                const list = b.closest('[data-clauses]'); b.closest('li').remove();
                if (!list.children.length) addClause(list, false); renumber(list);
            });

            document.getElementById('sk-form').addEventListener('submit', e => {
                collect();
                if (!rows.length) { e.preventDefault(); $('#add-employee').select2('open'); $('#people-empty').text('Tambahkan minimal satu karyawan sebelum menyimpan.'); }
            });

            render();
        });
    </script>
@endpush
