{{-- Form PH bersama untuk create & edit. Butuh: $status_type, $periodStart. Opsional: $pubholi, $hashedId, $rosterCount --}}
@php
    $isEdit = isset($pubholi);
    $d = fn($v, $f = 'd M Y') => \Carbon\Carbon::parse($v)->locale('id')->translatedFormat($f);
    $typeHelp = [
        'All' => ['Semua karyawan', 'Libur nasional untuk semua agama.'],
        'Hindu' => ['Hindu', 'Hanya karyawan beragama Hindu, misalnya Galungan atau Nyepi.'],
        'Non Hindu' => ['Non Hindu', 'Hanya karyawan selain Hindu, misalnya Idul Fitri atau Natal.'],
    ];
    $curType = old('type', $pubholi->type ?? 'All');
@endphp
<div class="main-content pr">
    <section class="section">
        <header class="pr-head">
            <div>
                <nav aria-label="Breadcrumb">
                    <ol class="pr-crumbs">
                        <li><a href="{{ route('pages.Pubholi') }}">Public holiday</a></li>
                        <li aria-current="page">{{ $isEdit ? 'Ubah' : 'Tambah' }}</li>
                    </ol>
                </nav>
                <h1 class="pr-title">{{ $isEdit ? 'Ubah ' . $pubholi->remark : 'Tambah public holiday' }}</h1>
                <p class="pr-lede">
                    @if ($isEdit)
                        {{ $d($pubholi->date, 'l, d F Y') }}.
                        {{ $rosterCount ? $rosterCount . ' karyawan sudah tercatat libur PH di tanggal ini.' : 'Belum ada roster PH di tanggal ini.' }}
                    @else
                        Roster karyawan di tanggal ini akan otomatis disesuaikan setelah disimpan.
                    @endif
                </p>
            </div>
        </header>

        <div class="ph-form-grid">
            <form action="{{ $isEdit ? route('Pubholi.update', $hashedId) : route('Pubholi.store') }}" method="POST" class="pr-panel" novalidate>
                @csrf
                @if ($isEdit) @method('PUT') @endif

                @if ($errors->any())
                    <div class="ph-errors" role="alert">
                        <strong>Belum bisa disimpan.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="ph-fields">
                    <div class="ph-field">
                        <label for="date">Tanggal</label>
                        <input type="date" id="date" name="date" required min="{{ $periodStart->toDateString() }}"
                            value="{{ old('date', $isEdit ? $pubholi->date->toDateString() : '') }}"
                            class="ph-input @error('date') is-invalid @enderror" aria-describedby="date-help">
                        <span class="ph-help" id="date-help">Paling awal {{ $d($periodStart) }}, awal periode gaji berjalan.</span>
                        <span class="ph-help is-day" id="date-day" aria-live="polite"></span>
                        @error('date')<span class="ph-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="ph-field">
                        <label for="remark">Nama hari libur</label>
                        <input type="text" id="remark" name="remark" required maxlength="255" autocomplete="off"
                            value="{{ old('remark', $pubholi->remark ?? '') }}" placeholder="mis. Hari Raya Nyepi"
                            class="ph-input @error('remark') is-invalid @enderror" aria-describedby="remark-help">
                        <span class="ph-help" id="remark-help">Tampil sebagai catatan di roster dan saldo PH karyawan.</span>
                        @error('remark')<span class="ph-error">{{ $message }}</span>@enderror
                    </div>

                    <fieldset class="ph-field">
                        <legend>Berlaku untuk</legend>
                        <div class="ph-types">
                            @foreach ($status_type as $value)
                                <label class="ph-type">
                                    <input type="radio" name="type" value="{{ $value }}" @checked($curType === $value) required>
                                    <span>
                                        <span class="ph-type-name">{{ $typeHelp[$value][0] ?? $value }}</span>
                                        <span class="ph-type-sub">{{ $typeHelp[$value][1] ?? '' }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('type')<span class="ph-error">{{ $message }}</span>@enderror
                    </fieldset>
                </div>

                <div class="ph-foot">
                    <a href="{{ route('pages.Pubholi') }}" class="pr-btn">Batal</a>
                    <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ $isEdit ? 'Simpan perubahan' : 'Simpan' }}</button>
                </div>
            </form>

            <aside class="pr-panel ph-aside" aria-labelledby="ph-aside-title">
                <div class="pr-panel-head">
                    <h2 class="pr-panel-title" id="ph-aside-title">Yang terjadi setelah disimpan</h2>
                </div>
                <ul class="ph-effects">
                    <li>Roster <strong>Work</strong> atau <strong>Off</strong> di tanggal ini berubah menjadi <strong>Public Holiday</strong> untuk karyawan yang berhak.</li>
                    <li>Karyawan yang sudah absen masuk di hari itu tetap <strong>Work</strong> dan mendapat saldo PH (libur pengganti).</li>
                    <li>Roster cuti, sakit, atau TOIL tidak diubah, tapi dihitung untuk dicek manual.</li>
                    <li><strong>DW</strong> tidak mendapat PH. Di <strong>3 store statis</strong>, PH hari Minggu tidak berlaku.</li>
                    @if ($isEdit)
                        <li>Kalau tanggal atau tipe diganti, roster PH lama yang tidak lagi berhak akan dikosongkan dan perlu dijadwalkan ulang.</li>
                    @endif
                </ul>
            </aside>
        </div>
    </section>
</div>
