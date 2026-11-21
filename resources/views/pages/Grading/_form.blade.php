{{-- Form grading, dipakai create & edit. Butuh: $grading (null saat create), $groups, $ladder, $maxLevel, $employees, $action, $method. --}}
@php
    $isEdit = (bool) $grading;
    $val = fn(string $f, $default = null) => old($f, $grading?->{$f} ?? $default);
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $meal = old('meal_allowance', $grading ? (int) $grading->meal_allowance : '');
@endphp

<div class="main-content pr">
    <section class="section">
        <header class="pr-head">
            <div>
                <nav aria-label="Breadcrumb">
                    <ol class="pr-crumbs">
                        <li><a href="{{ route('pages.Grading') }}">Grading</a></li>
                        <li aria-current="page">{{ $isEdit ? $grading->grading_name : 'Tambah' }}</li>
                    </ol>
                </nav>
                <h1 class="pr-title">{{ $isEdit ? 'Ubah ' . $grading->grading_name : 'Tambah grading' }}</h1>
                <p class="pr-lede">Level menentukan rantai atasan: karyawan mendapat atasan otomatis dari level yang lebih kecil. Tanda {!! $req !!} wajib diisi.</p>
            </div>
        </header>

        <form action="{{ $action }}" method="POST" id="gr-form" novalidate>
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
                    <section class="pr-panel" aria-labelledby="h-gr">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-gr">Grading</h2></div>
                        <div class="emp-fields">
                            <div class="emp-field">
                                <label for="grading_name">Nama grading {!! $req !!}</label>
                                <input type="text" id="grading_name" name="grading_name" maxlength="100" required value="{{ $val('grading_name') }}" class="emp-input @error('grading_name') is-invalid @enderror" placeholder="Mis. Supervisor" autocomplete="off">
                                @error('grading_name')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="grading_code">Kode</label>
                                <input type="text" id="grading_code" name="grading_code" maxlength="10" value="{{ $val('grading_code') }}" class="emp-input @error('grading_code') is-invalid @enderror" placeholder="Mis. C2" autocomplete="off">
                                <span class="emp-help">Huruf dan angka, harus unik.</span>
                                @error('grading_code')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field is-wide">
                                <span class="emp-label">Level {!! $req !!}</span>
                                <div class="gr-levels" role="radiogroup" aria-label="Level">
                                    @for ($i = 1; $i <= $maxLevel; $i++)
                                        <label><input type="radio" name="level" value="{{ $i }}" @checked((string) $val('level') === (string) $i)><span>{{ $i }}</span></label>
                                    @endfor
                                </div>
                                <span class="emp-help">1 = jenjang tertinggi (mis. Director), {{ $maxLevel }} = terendah.</span>
                                @error('level')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="group_id">Golongan</label>
                                <select id="group_id" name="group_id" class="emp-select @error('group_id') is-invalid @enderror">
                                    <option value="">Tanpa golongan</option>
                                    @foreach ($groups as $g)<option value="{{ $g->id }}" @selected($val('group_id') === $g->id)>{{ $g->group_name }}{{ $g->remark ? ', ' . $g->remark : '' }}</option>@endforeach
                                </select>
                                @error('group_id')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="meal_allowance">Uang makan acuan {!! $req !!}</label>
                                <div class="gr-money"><span>Rp</span><input type="text" id="meal_allowance" name="meal_allowance" inputmode="numeric" value="{{ $meal === '' ? '' : number_format((int) $meal, 0, ',', '.') }}" class="emp-input @error('meal_allowance') is-invalid @enderror" placeholder="0"></div>
                                <span class="emp-help">Hanya acuan. Gaji memakai uang makan di data gaji karyawan.</span>
                                @error('meal_allowance')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </section>

                    <div class="emp-savebar">
                        <span>{{ $isEdit && $employees ? 'Mengubah level menggeser rantai atasan ' . number_format($employees, 0, ',', '.') . ' karyawan aktif.' : 'Grading baru bisa langsung dipilih di data karyawan.' }}</span>
                        <a href="{{ route('pages.Grading') }}" class="pr-btn">Batal</a>
                        <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ $isEdit ? 'Simpan perubahan' : 'Simpan grading' }}</button>
                    </div>
                </div>

                <aside class="pr-panel lt-aside" aria-labelledby="h-ladder">
                    <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-ladder">Posisi di jenjang</h2></div>
                    <ol class="gr-mini" id="gr-mini" aria-live="polite"></ol>
                    <p class="po-names" id="gr-chain"></p>
                </aside>
            </div>
        </form>
    </section>
</div>
