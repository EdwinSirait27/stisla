{{-- Form jabatan, dipakai create & edit. Butuh: $position (null saat create), $lists, $listMeta, $usage, $career, $action, $method. --}}
@php
    $isEdit = (bool) $position;
    $val = fn(string $f, $default = null) => old($f, $position?->{$f} ?? $default);
    $dateVal = fn(string $f) => old($f, $position?->{$f}?->format('Y-m-d'));
    $listVal = fn(string $f) => array_values(array_filter((array) old($f, $lists[$f] ?? []), fn($v) => trim((string) $v) !== ''));
    $publish = (string) old('publish_career', $position ? (int) $position->publish_career : 0) === '1';
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $careerLabel = ['open' => 'Sedang tayang', 'scheduled' => 'Terjadwal', 'ended' => 'Sudah berakhir', 'draft' => 'Tidak dipublikasikan'];
    $hints = [
        'key_respon'    => 'Mis. Mengelola kas harian toko',
        'qualification' => 'Mis. Minimal SMA/SMK sederajat',
        'requirement'   => 'Mis. Bersedia bekerja shift',
        'skill'         => 'Mis. Microsoft Excel',
        'benefit'       => 'Mis. BPJS Kesehatan dan Ketenagakerjaan',
        'allowance'     => 'Mis. Tunjangan makan',
    ];
@endphp

@php
    // Satu daftar isian: baris yang ada + satu baris kosong (baris kosong dibuang saat disimpan).
    $renderList = function (string $field) use ($listVal, $listMeta, $hints, $errors) {
        $items = $listVal($field);
        $items[] = '';
        $label = $listMeta[$field][1];
        $html = '<ol class="po-list" data-list="' . e($field) . '">';
        foreach ($items as $i => $item) {
            $html .= '<li class="po-row"><span class="po-num" aria-hidden="true">' . ($i + 1) . '</span>'
                . '<input type="text" name="' . e($field) . '[]" value="' . e($item) . '" maxlength="255" class="emp-input' . ($errors->has($field . '.' . $i) ? ' is-invalid' : '') . '" placeholder="' . e($hints[$field]) . '" aria-label="' . e($label) . ' baris ' . ($i + 1) . '">'
                . '<button type="button" class="po-remove" aria-label="Hapus baris ' . ($i + 1) . '"><i class="fas fa-xmark" aria-hidden="true"></i></button></li>';
        }
        $html .= '</ol><button type="button" class="pr-btn pr-btn-sm po-add" data-target="' . e($field) . '"><i class="fas fa-plus" aria-hidden="true"></i> Tambah baris</button>';
        foreach ($errors->get($field . '.*') as $msgs) {
            foreach ((array) $msgs as $m) $html .= '<span class="emp-error">' . e($m) . '</span>';
        }
        foreach ($errors->get($field) as $m) $html .= '<span class="emp-error">' . e($m) . '</span>';
        return $html;
    };
@endphp

<div class="main-content pr">
    <section class="section">
        <header class="pr-head">
            <div>
                <nav aria-label="Breadcrumb">
                    <ol class="pr-crumbs">
                        <li><a href="{{ route('pages.Position') }}">Jabatan</a></li>
                        <li aria-current="page">{{ $isEdit ? $position->name : 'Tambah' }}</li>
                    </ol>
                </nav>
                <h1 class="pr-title">{{ $isEdit ? 'Ubah ' . $position->name : 'Tambah jabatan' }}</h1>
                <p class="pr-lede">Uraian jabatan dipakai untuk karyawan, SK, dan lowongan. Tanda {!! $req !!} wajib diisi; baris kosong diabaikan.</p>
            </div>
        </header>

        <form action="{{ $action }}" method="POST" id="po-form" novalidate>
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
                    {{-- 1. Dasar --}}
                    <section class="pr-panel" aria-labelledby="h-basic">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-basic">Jabatan</h2></div>
                        <div class="emp-fields">
                            <div class="emp-field is-wide">
                                <label for="name">Nama jabatan {!! $req !!}</label>
                                <input type="text" id="name" name="name" maxlength="100" required value="{{ $val('name') }}" class="emp-input @error('name') is-invalid @enderror" placeholder="Mis. Kasir, IT Support, Store Manager" autocomplete="off">
                                @if ($isEdit && ($usage['active'] ?? 0) > 0)
                                    <span class="emp-help">Mengganti nama berlaku untuk {{ number_format($usage['active'], 0, ',', '.') }} karyawan aktif yang memegang jabatan ini.</span>
                                @else
                                    <span class="emp-help">Harus unik. Periksa dulu di daftar jabatan supaya tidak dobel.</span>
                                @endif
                                @error('name')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field is-wide">
                                <label for="role_summary">Ringkasan peran</label>
                                <textarea id="role_summary" name="role_summary" rows="3" maxlength="2000" class="emp-input @error('role_summary') is-invalid @enderror" placeholder="Satu atau dua kalimat tentang tujuan jabatan ini.">{{ $val('role_summary') }}</textarea>
                                @error('role_summary')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </section>

                    {{-- 2. Uraian jabatan --}}
                    <section class="pr-panel" aria-labelledby="h-desc">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-desc">Uraian jabatan</h2></div>
                        <div class="emp-fields">
                            @foreach (['key_respon', 'qualification'] as $f)
                                <div class="emp-field is-wide">
                                    <span class="emp-label">{{ $listMeta[$f][1] }}</span>
                                    {!! $renderList($f) !!}
                                </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- 3. Lowongan --}}
                    <section class="pr-panel" aria-labelledby="h-career">
                        <div class="pr-panel-head">
                            <h2 class="pr-panel-title" id="h-career">Lowongan</h2>
                            @if ($isEdit)<span class="status-badge {{ ['open' => 'is-open', 'scheduled' => 'is-pending', 'ended' => 'is-closed', 'draft' => 'is-draft'][$career] }}">{{ $careerLabel[$career] }}</span>@endif
                        </div>
                        <div class="emp-fields">
                            <div class="emp-field is-wide">
                                <input type="hidden" name="publish_career" value="0">
                                <label class="emp-check"><input type="checkbox" id="publish_career" name="publish_career" value="1" @checked($publish) aria-controls="career-box">
                                    <span><strong>Publikasikan lowongan</strong><small>Jumlah lowongan dan tanggal tayang wajib diisi. Data lowongan tetap tersimpan walau publikasi dimatikan.</small></span></label>
                            </div>
                        </div>
                        <div id="career-box">
                            <div class="emp-fields is-3">
                                <div class="emp-field">
                                    <label for="vacancy">Jumlah lowongan <span class="js-pub-req">{!! $req !!}</span></label>
                                    <div class="lt-suffix"><input type="number" id="vacancy" name="vacancy" min="1" max="999" step="1" value="{{ $val('vacancy') }}" class="emp-input @error('vacancy') is-invalid @enderror"><span>orang</span></div>
                                    @error('vacancy')<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="emp-field">
                                    <label for="career_start_date">Mulai tayang <span class="js-pub-req">{!! $req !!}</span></label>
                                    <input type="date" id="career_start_date" name="career_start_date" value="{{ $dateVal('career_start_date') }}" class="emp-input @error('career_start_date') is-invalid @enderror">
                                    @error('career_start_date')<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="emp-field">
                                    <label for="career_end_date">Selesai tayang <span class="js-pub-req">{!! $req !!}</span></label>
                                    <input type="date" id="career_end_date" name="career_end_date" value="{{ $dateVal('career_end_date') }}" class="emp-input @error('career_end_date') is-invalid @enderror">
                                    @error('career_end_date')<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="emp-fields">
                                @foreach (['requirement', 'skill', 'benefit', 'allowance'] as $f)
                                    <div class="emp-field is-wide">
                                        <span class="emp-label">{{ $listMeta[$f][1] }}</span>
                                        {!! $renderList($f) !!}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <div class="emp-savebar">
                        <span>{{ $isEdit ? 'Semua daftar disimpan ulang sesuai urutan di layar.' : 'Jabatan baru bisa langsung dipilih di data karyawan.' }}</span>
                        <a href="{{ route('pages.Position') }}" class="pr-btn">Batal</a>
                        <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ $isEdit ? 'Simpan perubahan' : 'Simpan jabatan' }}</button>
                    </div>
                </div>

                <aside class="pr-panel lt-aside" aria-labelledby="h-sum">
                    <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-sum">Kelengkapan uraian</h2></div>
                    <div class="lt-eligible">
                        <strong id="sum-score">–</strong>
                        <span id="sum-of">dari 3 bagian inti terisi</span>
                        <div class="lt-meter" aria-hidden="true"><i id="sum-bar"></i></div>
                    </div>
                    <ul class="po-checks" id="sum-checks"></ul>
                    @if ($usage)
                        <dl class="lt-usage">
                            <div><dt>Karyawan aktif</dt><dd>{{ number_format($usage['active'], 0, ',', '.') }}</dd></div>
                            <div><dt>Di SK / kontrak</dt><dd>{{ number_format($usage['sk'], 0, ',', '.') }} / {{ number_format($usage['contracts'], 0, ',', '.') }}</dd></div>
                        </dl>
                        @if ($usage['names'])
                            <p class="po-names">{{ implode(', ', $usage['names']) }}@if ($usage['active'] > count($usage['names'])), dan {{ $usage['active'] - count($usage['names']) }} lainnya @endif.@if ($usage['other']) Ditambah {{ $usage['other'] }} karyawan Pending atau On Leave.@endif</p>
                        @endif
                    @endif
                </aside>
            </div>
        </form>
    </section>
</div>
