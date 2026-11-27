{{--
    Form kontrak bersama untuk buat & edit.
    Variabel: $contract (null saat buat), $prefill (dari SK, opsional), $fromSk, $employees (id => data), $types,
              $companies, $positions, $departments, $stores, $gradings, $groups, $approvers, $needPassword,
              $docOnly (kontrak sudah berlaku: hanya dokumen & catatan yang bisa diubah)
--}}
@php
    $isEdit = (bool) $contract;
    $docOnly = $docOnly ?? false;
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $src = $prefill ?? [];
    $val = function ($field) use ($isEdit, $contract, $src) {
        if (old($field) !== null) return old($field);
        if ($isEdit) {
            $v = $contract->{$field};
            return $v instanceof \DateTimeInterface ? $v->format('Y-m-d') : $v;
        }
        return $src[$field] ?? null;
    };
    $money = fn($field) => ($v = $val($field)) !== null && $v !== '' ? number_format((float) str_replace('.', '', (string) $v), 0, ',', '.') : '';
    $select = function ($name, $list, $placeholder) use ($val) {
        $cur = $val($name);
        $html = '<option value="">' . e($placeholder) . '</option>';
        foreach ($list as $id => $label) $html .= '<option value="' . e($id) . '"' . ($cur == $id ? ' selected' : '') . '>' . e($label) . '</option>';
        return $html;
    };
@endphp

@if ($errors->any())
    <div class="emp-errors" role="alert">
        <strong>Belum bisa disimpan.</strong>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
@if (session('error'))<p class="sk-notice is-warn" role="alert">{{ session('error') }}</p>@endif
@if ($docOnly)
    <p class="sk-notice is-info">Kontrak ini sudah disetujui dan berlaku, jadi isinya terkunci. Yang bisa diubah hanya dokumen bertanda tangan dan catatan. Kalau syarat kontrak berubah, akhiri kontrak ini lalu buat kontrak baru.</p>
@endif
@if ($isEdit && $contract->contract_status === 'Draft' && $contract->returned_at)
    <p class="sk-notice is-warn"><strong>Dikembalikan untuk diperbaiki:</strong> {{ $contract->returned_note }}</p>
@endif
@if ($fromSk)
    <p class="sk-notice is-info">Diisi dari SK <a href="{{ route('SkLetters.show', $fromSk->id) }}">{{ $fromSk->sk_number }}</a>. Periksa lalu simpan.</p>
@endif

@if ($needPassword)
    {{-- Kontrak memuat gaji: konfirmasi password dulu (berlaku 30 menit) --}}
    <section class="pr-panel" aria-labelledby="h-pw" id="pw-panel">
        <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-pw">Konfirmasi password</h2><span class="pr-panel-sub">Kontrak memuat data gaji. Masukkan password akun Anda untuk membuka form (berlaku 30 menit).</span></div></div>
        <div class="ct-pw">
            <div class="emp-field">
                <label for="confirm_password">Password</label>
                <input type="password" id="confirm_password" class="emp-input" autocomplete="current-password">
                <span class="emp-help" id="pw-error" role="alert" hidden style="color:var(--pr-error);font-weight:700">Password salah.</span>
            </div>
            <button type="button" id="btn-pw" class="pr-btn pr-btn-primary">Konfirmasi</button>
        </div>
    </section>
@endif

<form id="ct-form" method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('updatecontract', $contract->id) : route('storecontract') }}" novalidate>
    @csrf
    @if ($isEdit) @method('PUT') @endif
    @if (!$isEdit && ($src['sk_letter_id'] ?? old('sk_letter_id')))<input type="hidden" name="sk_letter_id" value="{{ old('sk_letter_id', $src['sk_letter_id'] ?? '') }}">@endif

    <fieldset id="ct-fieldset" {{ $needPassword ? 'disabled' : '' }} style="border:0;margin:0;padding:0;min-width:0">
      <fieldset {{ $docOnly ? 'disabled' : '' }} style="border:0;margin:0;padding:0;min-width:0">
        <section class="pr-panel" aria-labelledby="h-emp">
            <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-emp">Karyawan dan jenis kontrak</h2><span class="pr-panel-sub">Tanda {!! $req !!} wajib diisi.</span></div></div>
            <div class="emp-fields sk-fields-3">
                <div class="emp-field sk-span-2">
                    <label for="employee_id">Karyawan {!! $req !!}</label>
                    @if ($isEdit)
                        <input type="text" class="emp-input" value="{{ $contract->employee?->employee_name }}" readonly>
                    @else
                        <select id="employee_id" name="employee_id" data-placeholder="Cari nama atau NIP" required>
                            <option value=""></option>
                            @foreach ($employees as $id => $e)
                                <option value="{{ $id }}" @selected($val('employee_id') == $id)>{{ $e['name'] }}{{ $e['nip'] ? ' (' . $e['nip'] . ')' : '' }}</option>
                            @endforeach
                        </select>
                    @endif
                    <span class="emp-help" id="emp-info">{{ $isEdit ? 'Karyawan tidak bisa diganti; buat kontrak baru untuk karyawan lain.' : 'Penempatan dan gaji terakhir karyawan diisikan otomatis; ubah yang berbeda di kontrak.' }}</span>
                </div>
                <div class="emp-field">
                    <label for="contract_type">Jenis kontrak {!! $req !!}</label>
                    <select id="contract_type" name="contract_type" class="emp-input" required>{!! $select('contract_type', $types, 'Pilih jenis') !!}</select>
                </div>
                <div class="emp-field">
                    <label for="issuer_company_id">Perusahaan penerbit {!! $req !!}</label>
                    <select id="issuer_company_id" name="issuer_company_id" class="emp-input" required>{!! $select('issuer_company_id', $companies, 'Pilih perusahaan') !!}</select>
                </div>
                <div class="emp-field">
                    <label for="start_date">Mulai {!! $req !!}</label>
                    <input type="date" id="start_date" name="start_date" class="emp-input" required value="{{ $val('start_date') }}">
                    <span class="emp-help" id="join-info"></span>
                </div>
                <div class="emp-field">
                    <label for="end_date">Berakhir <span class="emp-req" id="end-req" aria-hidden="true">*</span></label>
                    <input type="date" id="end_date" name="end_date" class="emp-input" value="{{ $val('end_date') }}">
                    <span class="emp-help" id="end-help">Wajib untuk PKWT dan On Job Training. Tanggal ini masih termasuk masa kontrak.</span>
                </div>
            </div>
        </section>

        <section class="pr-panel" aria-labelledby="h-place">
            <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-place">Penempatan di kontrak</h2><span class="pr-panel-sub">Tercatat di kontrak; tidak mengubah data karyawan. Perubahan penempatan karyawan lewat SK.</span></div></div>
            <div class="emp-fields sk-fields-3">
                <div class="emp-field"><label for="position_id">Jabatan</label><select id="position_id" name="position_id" class="emp-input">{!! $select('position_id', $positions, 'Belum dipilih') !!}</select></div>
                <div class="emp-field"><label for="department_id">Departemen</label><select id="department_id" name="department_id" class="emp-input">{!! $select('department_id', $departments, 'Belum dipilih') !!}</select></div>
                <div class="emp-field"><label for="store_id">Lokasi</label><select id="store_id" name="store_id" class="emp-input">{!! $select('store_id', $stores, 'Belum dipilih') !!}</select></div>
                <div class="emp-field"><label for="grading_id">Grading</label><select id="grading_id" name="grading_id" class="emp-input">{!! $select('grading_id', $gradings, 'Belum dipilih') !!}</select></div>
                <div class="emp-field"><label for="group_id">Group</label><select id="group_id" name="group_id" class="emp-input">{!! $select('group_id', $groups, 'Belum dipilih') !!}</select></div>
            </div>
        </section>

        <section class="pr-panel" aria-labelledby="h-pay">
            <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-pay">Gaji di kontrak (Rupiah)</h2><span class="pr-panel-sub">Tercatat di kontrak; gaji yang dipakai payroll diatur di Employee Salary atau lewat SK.</span></div></div>
            <div class="emp-fields sk-fields-3">
                <div class="emp-field"><label for="basic_salary">Gaji pokok</label><input id="basic_salary" name="basic_salary" class="emp-input sk-money" inputmode="numeric" autocomplete="off" value="{{ $money('basic_salary') }}"></div>
                <div class="emp-field"><label for="positional_allowance">Tunjangan jabatan</label><input id="positional_allowance" name="positional_allowance" class="emp-input sk-money" inputmode="numeric" autocomplete="off" value="{{ $money('positional_allowance') }}"></div>
                <div class="emp-field"><label for="daily_rate">Daily rate (DW)</label><input id="daily_rate" name="daily_rate" class="emp-input sk-money" inputmode="numeric" autocomplete="off" value="{{ $money('daily_rate') }}"></div>
            </div>
        </section>

        <section class="pr-panel" aria-labelledby="h-approval">
            <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-approval">Persetujuan</h2><span class="pr-panel-sub">Yang mengajukan (Admin / HeadHR) tercatat sebagai penanda tangan pertama; approver di bawah menyetujui berurutan. Kontrak berlaku setelah disetujui penuh.</span></div></div>
            <div class="emp-fields sk-fields-3">
                <div class="emp-field">
                    <label for="approver_2">Approver 2 {!! $req !!}</label>
                    <select id="approver_2" name="approver_2" class="ct-person" data-placeholder="Pilih karyawan" required>
                        <option value=""></option>
                        @foreach ($approvers as $p)<option value="{{ $p->id }}" @selected($val('approver_2') == $p->id)>{{ $p->employee_name }}{{ $p->position ? ' - ' . $p->position : '' }}</option>@endforeach
                    </select>
                </div>
                <div class="emp-field">
                    <label for="approver_3">Approver 3</label>
                    <select id="approver_3" name="approver_3" class="ct-person" data-placeholder="Tidak ada">
                        <option value=""></option>
                        @foreach ($approvers as $p)<option value="{{ $p->id }}" @selected($val('approver_3') == $p->id)>{{ $p->employee_name }}{{ $p->position ? ' - ' . $p->position : '' }}</option>@endforeach
                    </select>
                    <span class="emp-help">Opsional. Kalau kosong, kontrak berlaku setelah approver 2 menyetujui.</span>
                </div>
            </div>
        </section>
      </fieldset>

        <section class="pr-panel" aria-labelledby="h-doc">
            <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-doc">Dokumen dan catatan</h2></div></div>
            <div class="emp-fields sk-fields-3">
                <div class="emp-field">
                    <label for="file_path">Dokumen kontrak yang ditandatangani (PDF)</label>
                    <input type="file" id="file_path" name="file_path" class="emp-input" accept="application/pdf" style="padding-top:8px">
                    <span class="emp-help">{{ $isEdit && $contract->file_path ? 'Sudah ada dokumen; unggah baru untuk menggantinya.' : 'Maksimal 5 MB. Bisa diunggah nanti.' }}</span>
                </div>
                <div class="emp-field sk-span-2">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" name="notes" class="emp-input" rows="3" maxlength="2000">{{ $val('notes') }}</textarea>
                </div>
            </div>
        </section>
    </fieldset>

    <div class="sk-savebar">
        <span class="sk-savebar-note" id="save-note">{{ $docOnly ? 'Hanya dokumen dan catatan yang disimpan.' : 'Disimpan sebagai Draft. Ajukan untuk persetujuan dari halaman detail.' }}</span>
        <a href="{{ $isEdit ? route('showcontract', $contract->id) : route('contract') }}" class="pr-btn">Batal</a>
        <button type="submit" form="ct-form" class="pr-btn pr-btn-primary" id="btn-save" {{ $needPassword ? 'disabled' : '' }}><i class="fas fa-check" aria-hidden="true"></i> {{ $isEdit ? 'Simpan perubahan' : 'Simpan kontrak' }}</button>
    </div>
</form>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function() {
            const EMP = @json($employees);
            const isEdit = @json($isEdit);
            const fromSk = @json((bool) $fromSk);
            const POS = @json($positions), DEPT = @json($departments), STORE = @json($stores);
            const money = v => (v === null || v === undefined || v === '') ? '' : Number(String(v).replace(/\D/g, '')).toLocaleString('id-ID');
            const fmtDate = d => d ? new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';

            function onType() {
                const t = $('#contract_type').val();
                const permanent = t === 'PKWTT';
                $('#end_date').prop('disabled', permanent).prop('required', t === 'PKWT' || t === 'On Job Training');
                if (permanent) $('#end_date').val('');
                $('#end-req').toggle(t === 'PKWT' || t === 'On Job Training');
                $('#end-help').text(permanent ? 'PKWTT (karyawan tetap) tanpa tanggal berakhir.' : 'Wajib untuk PKWT dan On Job Training. Tanggal ini masih termasuk masa kontrak.');
            }
            $('#contract_type').on('change', onType); onType();

            // Pilih karyawan: isi penempatan, jenis, gaji terakhir (hanya kolom yang masih kosong)
            function onEmployee(fill) {
                const e = EMP[$('#employee_id').val()];
                if (!e) { $('#emp-info').text('Penempatan dan gaji terakhir karyawan diisikan otomatis; ubah yang berbeda di kontrak.'); return; }
                const now = [POS[e.position], DEPT[e.department], STORE[e.store]].filter(Boolean).join(', ') || 'penempatan belum diisi';
                $('#emp-info').html(
                    `Sekarang: ${$('<i>').text(now).html()}${e.type ? ', ' + $('<i>').text(e.type).html() : ''}.` +
                    (e.active_contract ? ` <strong style="color:var(--pr-error)">Masih punya kontrak berlaku; akhiri dulu sebelum membuat yang baru.</strong>` : '')
                );
                $('#join-info').text(e.join ? 'Bergabung ' + fmtDate(e.join) + '.' : '');
                if (!fill) return;
                const setIf = (sel, v) => { if (v && !$(sel).val()) $(sel).val(v); };
                setIf('#position_id', e.position); setIf('#department_id', e.department); setIf('#store_id', e.store);
                setIf('#grading_id', e.grading); setIf('#group_id', e.group); setIf('#issuer_company_id', e.company);
                if (e.type && !$('#contract_type').val() && $(`#contract_type option[value="${CSS.escape(e.type)}"]`).length) { $('#contract_type').val(e.type); onType(); }
                if (e.salary) {
                    const setMoney = (sel, v) => { if (!$(sel).val() && v) $(sel).val(money(v)); };
                    setMoney('#basic_salary', e.salary.basic); setMoney('#positional_allowance', e.salary.position); setMoney('#daily_rate', e.salary.daily);
                }
            }
            $('.ct-person').each(function() { $(this).select2({ width: '100%', placeholder: this.dataset.placeholder, allowClear: !this.required }); });
            if (!isEdit) {
                $('#employee_id').select2({ width: '100%', placeholder: 'Cari nama atau NIP' }).on('change', () => onEmployee(true));
                onEmployee(!fromSk && !@json((bool) old('employee_id')));
            }

            document.querySelectorAll('.sk-money').forEach(el => el.addEventListener('input', () => {
                const pos = el.value.length - el.selectionStart; el.value = money(el.value);
                const p = Math.max(0, el.value.length - pos); el.setSelectionRange(p, p);
            }));

            // Konfirmasi password (form terkunci sampai benar)
            $('#btn-pw').on('click', function() {
                const btn = this; btn.disabled = true;
                fetch(@json(route('contract.password.ajax')), {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                    body: JSON.stringify({ password: $('#confirm_password').val() }),
                }).then(r => r.json()).then(j => {
                    if (j.success) {
                        $('#ct-fieldset').prop('disabled', false); $('#btn-save').prop('disabled', false); $('#pw-panel').remove();
                        $('#employee_id').select2('open');
                    } else { $('#pw-error').prop('hidden', false); btn.disabled = false; $('#confirm_password').trigger('focus').trigger('select'); }
                }).catch(() => { $('#pw-error').text('Gagal memeriksa password. Coba lagi.').prop('hidden', false); btn.disabled = false; });
            });
            $('#confirm_password').on('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); $('#btn-pw').trigger('click'); } });
        });
    </script>
@endpush
