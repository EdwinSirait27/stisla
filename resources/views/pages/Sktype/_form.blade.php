{{--
    Form jenis SK bersama untuk buat & edit.
    Variabel: $sktype (null saat buat), $hashedId (edit), $categories, $existing (jenis lain), $usage & $latest (edit)
--}}
@php
    $isEdit = (bool) ($sktype ?? null);
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $v = fn($f, $d = null) => old($f, $isEdit ? $sktype->{$f} : $d);
    $on = fn($f) => (bool) old($f, $isEdit ? $sktype->{$f} : false);
    $catLabel = ['Employment' => 'Kepegawaian', 'Mutation' => 'Mutasi', 'Payroll' => 'Penggajian', 'Disciplinary' => 'Disiplin', 'Termination' => 'Pemberhentian'];
    $switches = [
        'affects_salary'     => ['Mengubah gaji', 'Saat SK sah, gaji pokok, tunjangan jabatan, dan daily rate dari SK dicatat sebagai gaji baru di Employee Salary, berlaku mulai tanggal efektif SK. Tunjangan lain dan BPJS disalin dari gaji terakhir.'],
        'affects_position'   => ['Mengubah jabatan dan penempatan', 'Jabatan, departemen, lokasi, grading, dan group baru di SK diterapkan ke data karyawan pada tanggal efektif SK.'],
        'affects_status'     => ['Mengubah status karyawan', 'Jenis karyawan baru (PKWT, PKWTT, On Job Training, DW) di SK diterapkan. Untuk kategori Pemberhentian, karyawan menjadi Resign per tanggal efektif.'],
        'generates_contract' => ['Bisa dibuatkan kontrak', 'SK yang sudah sah menampilkan tombol "Buat kontrak dari SK ini". Kontrak yang isinya sama dengan SK langsung berlaku tanpa approval ulang.'],
    ];
@endphp

@if ($errors->any())
    <div class="emp-errors" role="alert"><strong>Belum bisa disimpan.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if (session('error'))<p class="sk-notice is-warn" role="alert">{{ session('error') }}</p>@endif
@if ($isEdit && $usage['open'] > 0)
    <p class="sk-notice is-warn">{{ $usage['open'] }} SK jenis ini masih berjalan (Draft atau menunggu persetujuan). Perubahan dampak di bawah berlaku untuk SK itu juga saat disahkan nanti; SK yang sudah sah tidak berubah.</p>
@endif

<div class="sk-layout">
    <form id="st-form" method="POST" action="{{ $isEdit ? route('Sktype.update', $hashedId) : route('Sktype.store') }}" novalidate>
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <section class="pr-panel" aria-labelledby="h-data">
            <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-data">Data jenis SK</h2><span class="pr-panel-sub">Nama dan singkatan disimpan dengan huruf kapital. Tanda {!! $req !!} wajib diisi.</span></div></div>
            <div class="emp-fields sk-fields-3">
                <div class="emp-field sk-span-2">
                    <label for="sk_name">Nama jenis SK {!! $req !!}</label>
                    <input type="text" id="sk_name" name="sk_name" class="emp-input @error('sk_name') is-invalid @enderror" maxlength="255" required autocomplete="off" value="{{ $v('sk_name') }}" placeholder="Mis. PROMOSI">
                    <p class="st-warn" id="name-taken" role="status" hidden></p>
                </div>
                <div class="emp-field">
                    <label for="nickname">Singkatan {!! $req !!}</label>
                    <input type="text" id="nickname" name="nickname" class="emp-input st-code-input @error('nickname') is-invalid @enderror" minlength="2" maxlength="10" required autocomplete="off" spellcheck="false" value="{{ $v('nickname') }}" placeholder="Mis. PRM">
                    <p class="st-preview" id="number-preview">Nomor SK: <b>001/<span id="preview-code">{{ $v('nickname') ?: '…' }}</span>-[perusahaan]/{{ \App\Models\SkLetter::getRomanMonth(now()->month) }}/{{ now()->year }}</b></p>
                    <p class="st-warn" id="code-taken" role="status" hidden></p>
                </div>
                <div class="emp-field">
                    <label for="categories">Kategori {!! $req !!}</label>
                    <select id="categories" name="categories" class="emp-input" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $k => $label)
                            <option value="{{ $k }}" @selected($v('categories') === $k)>{{ $catLabel[$k] ?? $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="pr-panel" aria-labelledby="h-effects">
            <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-effects">Saat SK jenis ini disetujui penuh</h2><span class="pr-panel-sub">Pilih yang dilakukan sistem ke data karyawan. Tanpa pilihan, SK hanya menjadi dokumen.</span></div></div>
            <div class="st-switches">
                @foreach ($switches as $field => [$title, $desc])
                    <label class="st-switch" for="{{ $field }}">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1" @checked($on($field))>
                        <span><strong>{{ $title }}</strong><span data-desc="{{ $field }}">{{ $desc }}</span></span>
                    </label>
                @endforeach
                <p class="st-warn" id="termination-note" hidden>Kategori Pemberhentian dengan "Mengubah status karyawan" menjadikan karyawan Resign pada tanggal efektif SK.</p>
            </div>
        </section>

        <div class="sk-savebar">
            <a href="{{ route('pages.Sktype') }}" class="pr-btn">Batal</a>
            <button type="submit" class="pr-btn pr-btn-primary" id="{{ $isEdit ? 'edit-btn' : 'create-btn' }}"><i class="fas fa-check" aria-hidden="true"></i> {{ $isEdit ? 'Simpan perubahan' : 'Tambah jenis SK' }}</button>
        </div>
    </form>

    <aside>
        @if ($isEdit)
            <section class="pr-panel" aria-labelledby="h-usage">
                <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-usage">Dipakai di SK</h2><span class="pr-panel-sub">{{ $usage['total'] }} SK, {{ $usage['approved'] }} sudah disetujui, {{ $usage['open'] }} berjalan.</span></div></div>
                @if ($latest->isEmpty())
                    <p class="sk-empty">Belum ada SK dengan jenis ini.</p>
                @else
                    <ul class="st-list">
                        @foreach ($latest as $l)
                            <li><span><a href="{{ route('SkLetters.show', $l->id) }}">{{ $l->sk_number }}</a> {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($l->title)) }}</span><b>{{ $l->status_label }}</b></li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif
        <section class="pr-panel" aria-labelledby="h-others">
            <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-others">{{ $isEdit ? 'Jenis lain' : 'Jenis yang sudah ada' }}</h2><span class="pr-panel-sub">Nama dan singkatan yang sama ditandai saat Anda mengetik.</span></div></div>
            @if (!$existing)
                <p class="sk-empty">Belum ada jenis lain.</p>
            @else
                <ul class="st-list" id="st-list">
                    @foreach ($existing as $t)<li data-name="{{ $t['name'] }}" data-code="{{ $t['nickname'] }}"><span>{{ \Illuminate\Support\Str::title($t['name']) }}</span><b>{{ $t['nickname'] }}</b></li>@endforeach
                </ul>
            @endif
        </section>
    </aside>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const nameEl = document.getElementById('sk_name'), codeEl = document.getElementById('nickname');
            const items = [...document.querySelectorAll('#st-list li')];
            const cleanCode = s => String(s).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10);
            const cleanName = s => String(s).trim().replace(/\s+/g, ' ').toUpperCase();
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            function check() {
                const name = cleanName(nameEl.value), code = cleanCode(codeEl.value);
                let nameHit = null, codeHit = null;
                items.forEach(li => {
                    const n = li.dataset.name === name && name !== '', c = li.dataset.code === code && code.length >= 2;
                    li.classList.toggle('is-match', n || c);
                    if (n) nameHit = li.dataset.name; if (c) codeHit = li.dataset.name;
                });
                $('#name-taken').prop('hidden', !nameHit).html(nameHit ? '<strong>Nama ini sudah ada.</strong> Pakai nama lain.' : '');
                $('#code-taken').prop('hidden', !codeHit).html(codeHit ? `<strong>${esc(code)} sudah dipakai ${esc(codeHit)}.</strong> Nomor SK bisa tertukar.` : '');
                $('#preview-code').text(code || '…');
                return !nameHit && !codeHit;
            }
            codeEl.addEventListener('input', () => {
                const pos = codeEl.selectionStart, after = cleanCode(codeEl.value);
                if (after !== codeEl.value) { codeEl.value = after; codeEl.setSelectionRange(Math.min(pos, after.length), Math.min(pos, after.length)); }
                check();
            });
            nameEl.addEventListener('input', check);

            const statusEl = document.getElementById('affects_status'), catEl = document.getElementById('categories');
            const termination = () => $('#termination-note').prop('hidden', !(statusEl.checked && catEl.value === 'Termination'));
            statusEl.addEventListener('change', termination); catEl.addEventListener('change', termination);
            check(); termination();

            document.getElementById('st-form').addEventListener('submit', function(e) {
                if (this.dataset.ok) return;
                e.preventDefault();
                if (!cleanName(nameEl.value)) { nameEl.focus(); return Swal.fire({ icon: 'error', title: 'Nama jenis SK belum diisi' }); }
                if (cleanCode(codeEl.value).length < 2) { codeEl.focus(); return Swal.fire({ icon: 'error', title: 'Singkatan belum diisi', text: 'Isi 2 sampai 10 huruf atau angka.' }); }
                if (!catEl.value) { catEl.focus(); return Swal.fire({ icon: 'error', title: 'Pilih kategori' }); }
                if (!check()) return Swal.fire({ icon: 'error', title: 'Nama atau singkatan sudah dipakai', text: 'Ganti dulu supaya nomor SK tidak tertukar.' });
                const effects = [...document.querySelectorAll('.st-switch input[type=checkbox]:checked')].map(i => i.closest('.st-switch').querySelector('strong').textContent);
                Swal.fire({
                    icon: 'question', title: @json($isEdit ? 'Simpan perubahan?' : 'Tambah jenis SK ini?'),
                    html: `<b>${esc(cleanName(nameEl.value))}</b> (${esc(cleanCode(codeEl.value))})<br>${effects.length ? 'Saat SK sah: ' + effects.map(esc).join(', ').toLowerCase() : 'Hanya dokumen, data karyawan tidak berubah'}`,
                    showCancelButton: true, confirmButtonText: @json($isEdit ? 'Simpan' : 'Tambah'), cancelButtonText: 'Periksa lagi',
                }).then(r => { if (r.isConfirmed) { this.dataset.ok = '1'; this.querySelector('button[type=submit]').disabled = true; this.submit(); } });
            });
        });
    </script>
@endpush
