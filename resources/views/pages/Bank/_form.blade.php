{{--
    Form bank bersama untuk create & edit.
    Variabel: $formId, $submitId, $action, $method ('POST'|'PUT'), $submitLabel, $value, $existingNames, $usage (opsional, hanya edit)
--}}
@if ($errors->any())
    <div class="emp-errors" role="alert">
        <strong>Belum bisa disimpan.</strong>
        <ul>
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="bk-layout">
    <form id="{{ $formId }}" action="{{ $action }}" method="POST" class="pr-panel" novalidate>
        @csrf
        @if ($method === 'PUT') @method('PUT') @endif

        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title">Data bank</h2>
                <span class="pr-panel-sub">Nama ini tampil sebagai pilihan bank di data rekening karyawan.</span>
            </div>
        </div>

        <div class="emp-fields">
            <div class="emp-field">
                <label for="name">Nama bank <span class="emp-req" aria-hidden="true">*</span></label>
                <input type="text" class="emp-input @error('name') is-invalid @enderror" id="name" name="name"
                    value="{{ $value }}" required minlength="2" maxlength="255" autocomplete="off"
                    placeholder="Mis. BCA, Mandiri, OCBC"
                    aria-describedby="name-help name-similar @error('name') name-error @enderror"
                    @error('name') aria-invalid="true" @enderror>
                @error('name')
                    <span class="emp-error" id="name-error">{{ $message }}</span>
                @enderror
                <span class="emp-help" id="name-help">Tulis nama yang dikenal karyawan, sama seperti di buku tabungan.</span>
                <p class="bk-similar" id="name-similar" role="status" hidden></p>
            </div>
        </div>

        <ul class="bk-rules">
            <li>Nama bank yang sudah terdaftar tidak bisa didaftarkan lagi.</li>
            <li>Gunakan bahasa Inggris supaya penulisan seragam.</li>
            <li>Periksa daftar bank yang sudah terdaftar sebelum menyimpan, supaya tidak ada data ganda.</li>
        </ul>

        <div class="bk-savebar">
            <a href="{{ route('pages.Bank') }}" class="pr-btn">Batal</a>
            <button type="submit" id="{{ $submitId }}" class="pr-btn pr-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> {{ $submitLabel }}</button>
        </div>
    </form>

    <aside class="pr-panel" aria-labelledby="bk-list-title">
        @isset($usage)
            <div class="pr-panel-head">
                <div>
                    <h2 class="pr-panel-title">Dipakai karyawan</h2>
                    <span class="pr-panel-sub">Perubahan nama langsung tampil di data mereka.</span>
                </div>
            </div>
            <dl class="bk-usage">
                <div><dt>Aktif</dt><dd>{{ number_format($usage['active'], 0, ',', '.') }}</dd></div>
                <div><dt>Total, termasuk resign</dt><dd>{{ number_format($usage['total'], 0, ',', '.') }}</dd></div>
            </dl>
            @if ($usage['total'] > 0)
                <p class="bk-usage-note">Ubah nama hanya untuk memperbaiki penulisan. Kalau karyawan pindah bank, ubah di data karyawannya, bukan di sini.</p>
            @endif
        @endisset

        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="bk-list-title">{{ isset($usage) ? 'Bank lain yang terdaftar' : 'Bank yang sudah terdaftar' }}</h2>
                <span class="pr-panel-sub">{{ $existingNames->count() }} bank. Nama yang mirip ditandai saat Anda mengetik.</span>
            </div>
        </div>
        @if ($existingNames->isEmpty())
            <p class="bk-list-empty">Belum ada bank lain.</p>
        @else
            <ul class="bk-list" id="bk-list">
                @foreach ($existingNames as $existing)<li>{{ $existing }}</li>@endforeach
            </ul>
        @endif
    </aside>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const form = document.getElementById(@json($formId));
            const input = document.getElementById('name');
            const note = document.getElementById('name-similar');
            const items = [...document.querySelectorAll('#bk-list li')];

            // Bandingkan tanpa huruf besar/kecil, tanda baca, dan kata umum ("bank", "pt", "tbk")
            const key = s => s.toLowerCase().replace(/\b(bank|pt|tbk|persero)\b/g, '').replace(/[^a-z0-9]/g, '');
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            function checkSimilar() {
                const k = key(input.value);
                const matches = [];
                items.forEach(li => {
                    const other = key(li.textContent);
                    const hit = k.length >= 3 && other.length >= 3 && (k === other || k.includes(other) || other.includes(k));
                    li.classList.toggle('is-match', hit);
                    if (hit) matches.push(li.textContent.trim());
                });
                note.hidden = !matches.length;
                note.innerHTML = matches.length
                    ? `<strong>Mirip dengan ${matches.map(esc).join(', ')}.</strong> Pastikan ini bukan bank yang sama.`
                    : '';
            }
            input.addEventListener('input', checkSimilar);
            checkSimilar();

            document.getElementById(@json($submitId)).addEventListener('click', function(e) {
                e.preventDefault();
                const name = input.value.trim().replace(/\s+/g, ' ');
                if (name.length < 2) {
                    input.focus();
                    return Swal.fire({ icon: 'error', title: 'Nama bank belum diisi', text: 'Isi nama bank, minimal 2 karakter.' });
                }
                const similar = !note.hidden;
                Swal.fire({
                    icon: similar ? 'warning' : 'question',
                    title: @json($method === 'PUT' ? 'Simpan perubahan?' : 'Tambah bank ini?'),
                    html: `<b>${esc(name)}</b>${similar ? '<br>Namanya mirip bank yang sudah terdaftar.' : ''}`,
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
