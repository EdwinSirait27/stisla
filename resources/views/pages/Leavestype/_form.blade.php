{{-- Form jenis cuti, dipakai create & edit. Butuh: $type (null saat create), $isAnnual, $usage, $genderRules,
     $rosterDayTypes, $statusOptions, $selectedStatus, $eligibility, $blockedStatuses, $action, $method. --}}
@php
    $isEdit = (bool) $type;
    $val = fn(string $f, $default = null) => old($f, $type?->{$f} ?? $default);
    $on = fn(string $f, bool $default = false) => (string) old($f, $type ? (int) (bool) $type->{$f} : (int) $default) === '1';
    $statusSel = old('allowed_status', $selectedStatus);
    $durMode = old('duration_mode', $val('fixed_days') ? 'fixed' : ($val('max_days') ? 'max' : 'free'));
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $days = fn($v) => rtrim(rtrim(number_format((float) $v, 1, ',', '.'), '0'), ',');
@endphp

<div class="main-content pr">
    <section class="section">
        <header class="pr-head">
            <div>
                <nav aria-label="Breadcrumb">
                    <ol class="pr-crumbs">
                        <li><a href="{{ route('pages.Leavestype') }}">Jenis cuti</a></li>
                        <li aria-current="page">{{ $isEdit ? $type->name : 'Tambah' }}</li>
                    </ol>
                </nav>
                <h1 class="pr-title">{{ $isEdit ? 'Ubah ' . $type->name : 'Tambah jenis cuti' }}</h1>
                <p class="pr-lede">Aturan di sini dicek saat karyawan mengajukan cuti. Tanda {!! $req !!} wajib diisi.</p>
            </div>
        </header>

        <form action="{{ $action }}" method="POST" id="lt-form" novalidate>
            @csrf
            @if ($method !== 'POST') @method($method) @endif

            @if ($errors->any())
                <div class="emp-errors" role="alert">
                    <strong>{{ $errors->count() }} isian perlu diperbaiki.</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="lt-grid">
                <div class="emp-sections">
                    @if ($isAnnual)
                        <p class="lt-callout">Annual Leave dibagikan otomatis oleh command <code>leave:anniversary</code> setiap hari jam 23:10, 12 hari per tahun (15 untuk manager). Jatah di form ini tidak dipakai command itu, dan jenis cuti ini tidak bisa dijadikan khusus atau dinonaktifkan.</p>
                    @endif

                    {{-- 1. Dasar --}}
                    <section class="pr-panel" aria-labelledby="h-basic">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-basic">Dasar</h2></div>
                        <div class="emp-fields">
                            <div class="emp-field">
                                <label for="name">Nama jenis cuti {!! $req !!}</label>
                                <input type="text" id="name" name="name" maxlength="100" required value="{{ $val('name') }}" class="emp-input @error('name') is-invalid @enderror" placeholder="Mis. Cuti Melahirkan" autocomplete="off">
                                <span class="emp-help">Muncul di form pengajuan cuti karyawan dan di approval atasan.</span>
                                @error('name')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="default_balance">Jatah saldo per tahun</label>
                                <div class="lt-suffix"><input type="number" id="default_balance" name="default_balance" min="0.5" max="365" step="0.5" value="{{ $val('default_balance') !== null ? $days($val('default_balance')) : '' }}" class="emp-input @error('default_balance') is-invalid @enderror" placeholder="Mis. 90"><span>hari</span></div>
                                <span class="emp-help">
                                    @if ($isAnnual) Tidak dipakai: Annual Leave diatur oleh leave:anniversary.
                                    @else Dipakai <code>leave:generate-special</code> untuk membuat saldo cuti khusus. @endif
                                </span>
                                @error('default_balance')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <span class="emp-label">Status</span>
                                <input type="hidden" name="is_active" value="0">
                                <label class="emp-check"><input type="checkbox" name="is_active" value="1" @checked($on('is_active', true)) @disabled($isAnnual)>
                                    <span><strong>Aktif</strong><small>Muncul di filter cuti dan dibagikan oleh leave:generate-special. Nonaktifkan, jangan hapus: saldo karyawan ikut terhapus kalau jenis cuti dihapus.</small></span></label>
                                @if ($isAnnual)<input type="hidden" name="is_active" value="1">@endif
                                @error('is_active')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <span class="emp-label">Pembayaran</span>
                                <input type="hidden" name="is_paid" value="0">
                                <label class="emp-check"><input type="checkbox" name="is_paid" value="1" @checked($on('is_paid', true))>
                                    <span><strong>Cuti dibayar</strong><small>Hanya penanda. Payroll saat ini menghitung semua hari cuti di roster sebagai hari dibayar.</small></span></label>
                            </div>
                        </div>
                    </section>

                    {{-- 2. Syarat khusus --}}
                    <section class="pr-panel" aria-labelledby="h-special">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-special">Syarat khusus</h2></div>
                        <div class="emp-fields">
                            <div class="emp-field is-wide">
                                <input type="hidden" name="is_special" value="0">
                                <label class="emp-check"><input type="checkbox" id="is_special" name="is_special" value="1" @checked($on('is_special')) @disabled($isAnnual) aria-controls="special-box">
                                    <span><strong>Cuti ini punya syarat</strong><small>Untuk cuti yang dibatasi gender, status menikah, status kepegawaian, lampiran, atau durasinya. Biarkan kosong untuk cuti biasa. Kalau dimatikan, semua syarat di bawah dihapus saat disimpan.</small></span></label>
                                @error('is_special')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div id="special-box">
                            <div class="emp-fields">
                                <div class="emp-field">
                                    <span class="emp-label">Gender</span>
                                    <div class="lt-seg" role="radiogroup" aria-label="Gender">
                                        @foreach (['all' => 'Semua', 'female' => 'Perempuan', 'male' => 'Laki-laki'] as $g => $label)
                                            <label><input type="radio" name="gender_rule" value="{{ $g }}" @checked($val('gender_rule', 'all') === $g)><span>{{ $label }}</span></label>
                                        @endforeach
                                    </div>
                                    @error('gender_rule')<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="emp-field">
                                    <span class="emp-label">Status menikah</span>
                                    <input type="hidden" name="require_married" value="0">
                                    <label class="emp-check"><input type="checkbox" name="require_married" value="1" @checked($on('require_married'))>
                                        <span><strong>Wajib sudah menikah</strong><small>Dibaca dari data karyawan, kolom menikah = Yes.</small></span></label>
                                </div>
                                <div class="emp-field is-wide">
                                    <span class="emp-label">Status kepegawaian</span>
                                    <div class="lt-chips">
                                        @foreach ($statusOptions as $s)
                                            <label class="emp-check lt-chip"><input type="checkbox" name="allowed_status[]" value="{{ $s }}" @checked(in_array($s, (array) $statusSel, true))><span><strong>{{ $s }}</strong></span></label>
                                        @endforeach
                                    </div>
                                    <span class="emp-help">Tidak dicentang sama sekali berarti semua status. DW, On Job Training, dan Resign selalu ditolak karena tidak punya hak cuti.</span>
                                    @error('allowed_status')<span class="emp-error">{{ $message }}</span>@enderror
                                    @error('allowed_status.*')<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="emp-field is-wide">
                                    <span class="emp-label">Durasi</span>
                                    <div class="lt-seg" role="radiogroup" aria-label="Durasi">
                                        @foreach (['free' => 'Bebas', 'fixed' => 'Tepat N hari', 'max' => 'Maksimal N hari'] as $m => $label)
                                            <label><input type="radio" name="duration_mode" value="{{ $m }}" @checked($durMode === $m)><span>{{ $label }}</span></label>
                                        @endforeach
                                    </div>
                                    <div class="lt-dur" data-mode="fixed">
                                        <div class="lt-suffix"><input type="number" id="fixed_days" name="fixed_days" min="1" max="365" value="{{ $val('fixed_days') }}" class="emp-input @error('fixed_days') is-invalid @enderror" aria-label="Durasi tepat, hari" placeholder="90"><span>hari kalender</span></div>
                                        <span class="emp-help">Tanggal selesai dikunci: mulai + N hari kalender, termasuk akhir pekan dan tanggal merah.</span>
                                        @error('fixed_days')<span class="emp-error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="lt-dur" data-mode="max">
                                        <div class="lt-suffix"><input type="number" id="max_days" name="max_days" min="1" max="365" value="{{ $val('max_days') }}" class="emp-input @error('max_days') is-invalid @enderror" aria-label="Durasi maksimal, hari" placeholder="3"><span>hari</span></div>
                                        <span class="emp-help">Batas atas per pengajuan.</span>
                                        @error('max_days')<span class="emp-error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="emp-field">
                                    <span class="emp-label">Lampiran</span>
                                    <input type="hidden" name="require_attachment" value="0">
                                    <label class="emp-check"><input type="checkbox" name="require_attachment" value="1" @checked($on('require_attachment'))>
                                        <span><strong>Wajib melampirkan bukti</strong><small>Mis. surat dokter. JPG, PNG, atau PDF, maksimal 5 MB.</small></span></label>
                                </div>
                                <div class="emp-field">
                                    <label for="roster_day_type">Tampil di roster sebagai</label>
                                    <select id="roster_day_type" name="roster_day_type" class="emp-select">
                                        @foreach ($rosterDayTypes as $d)<option value="{{ $d }}" @selected($val('roster_day_type', 'Leave') === $d)>{{ $d }}</option>@endforeach
                                    </select>
                                    <span class="emp-help">Pilihan dibatasi karena tiap tipe punya warna sendiri di grid roster.</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="emp-savebar">
                        <span>{{ $isEdit ? 'Aturan baru berlaku untuk pengajuan berikutnya, pengajuan lama tidak berubah.' : 'Cuti khusus perlu leave:generate-special setelah disimpan agar karyawan punya saldo.' }}</span>
                        <a href="{{ route('pages.Leavestype') }}" class="pr-btn">Batal</a>
                        <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ $isEdit ? 'Simpan perubahan' : 'Simpan jenis cuti' }}</button>
                    </div>
                </div>

                {{-- Ringkasan --}}
                <aside class="pr-panel lt-aside" aria-labelledby="h-sum">
                    <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-sum">Siapa yang bisa mengajukan</h2></div>
                    <div class="lt-eligible">
                        <strong id="sum-count">–</strong>
                        <span id="sum-of">karyawan aktif</span>
                        <div class="lt-meter" aria-hidden="true"><i id="sum-bar"></i></div>
                    </div>
                    <ul class="lt-rules" id="sum-rules"></ul>
                    @if ($usage)
                        <dl class="lt-usage">
                            <div><dt>Saldo {{ $usage['year'] }}</dt><dd>{{ number_format($usage['employees'], 0, ',', '.') }} karyawan</dd></div>
                            <div><dt>Pengajuan</dt><dd>{{ number_format($usage['requests'], 0, ',', '.') }}@if ($usage['pending']), {{ $usage['pending'] }} menunggu @endif</dd></div>
                        </dl>
                    @endif
                </aside>
            </div>
        </form>
    </section>
</div>
