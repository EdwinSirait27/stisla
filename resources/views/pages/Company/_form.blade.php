{{-- Form perusahaan, dipakai create & edit. Butuh: $company (null saat create), $remarks, $usage, $logoUrl, $action, $method. --}}
@php
    $isEdit = (bool) $company;
    $val = fn(string $f, $default = null) => old($f, $company?->{$f} ?? $default);
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
@endphp

<div class="main-content pr">
    <section class="section">
        <header class="pr-head">
            <div>
                <nav aria-label="Breadcrumb">
                    <ol class="pr-crumbs">
                        <li><a href="{{ route('pages.Company') }}">Perusahaan</a></li>
                        <li aria-current="page">{{ $isEdit ? $company->nickname : 'Tambah' }}</li>
                    </ol>
                </nav>
                <h1 class="pr-title">{{ $isEdit ? 'Ubah ' . $company->name : 'Tambah perusahaan' }}</h1>
                <p class="pr-lede">Data ini dicetak sebagai kop surat di SK, PKWT, dan dokumen karyawan. Tanda {!! $req !!} wajib diisi.</p>
            </div>
        </header>

        <form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="co-form" novalidate>
            @csrf
            @if ($method !== 'POST') @method($method) @endif

            @if ($errors->any())
                <div class="emp-errors" role="alert">
                    <strong>{{ $errors->count() }} isian perlu diperbaiki.</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="lt-grid co-grid">
                <div class="emp-sections">
                    <section class="pr-panel" aria-labelledby="h-id">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-id">Identitas</h2></div>
                        <div class="emp-fields">
                            <div class="emp-field is-wide">
                                <label for="name">Nama perusahaan {!! $req !!}</label>
                                <input type="text" id="name" name="name" maxlength="150" required value="{{ $val('name') }}" class="emp-input @error('name') is-invalid @enderror" placeholder="Mis. PT. ASIAN BAY DEVELOPMENT" autocomplete="organization">
                                <span class="emp-help">Lengkap dengan bentuk usaha (PT., CV.). Disimpan dalam huruf kapital.</span>
                                @error('name')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="nickname">Singkatan {!! $req !!}</label>
                                <input type="text" id="nickname" name="nickname" maxlength="10" required value="{{ $val('nickname') }}" class="emp-input @error('nickname') is-invalid @enderror" placeholder="Mis. ABD" autocomplete="off">
                                <span class="emp-help">Huruf dan angka saja. Dipakai di daftar dan filter.</span>
                                @error('nickname')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <span class="emp-label">Jenis {!! $req !!}</span>
                                <div class="lt-seg" role="radiogroup" aria-label="Jenis perusahaan">
                                    @foreach ($remarks as $r)
                                        <label><input type="radio" name="remark" value="{{ $r }}" @checked($val('remark', 'Unit') === $r)><span>{{ $r }}</span></label>
                                    @endforeach
                                </div>
                                <span class="emp-help">Holding untuk induk, Unit untuk anak usaha.</span>
                                @error('remark')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="npwp">NPWP {!! $req !!}</label>
                                <input type="text" id="npwp" name="npwp" maxlength="25" required inputmode="numeric" value="{{ $val('npwp') }}" class="emp-input @error('npwp') is-invalid @enderror" placeholder="15 atau 16 digit" autocomplete="off">
                                <span class="emp-help" id="npwp-help">Titik dan strip boleh, akan dibuang.</span>
                                @error('npwp')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </section>

                    <section class="pr-panel" aria-labelledby="h-kop">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-kop">Kop surat</h2></div>
                        <div class="emp-fields">
                            <div class="emp-field is-wide">
                                <label for="foto">Logo {!! $isEdit ? '' : $req !!}</label>
                                <div class="co-upload">
                                    <div class="co-upload-prev"><img id="logo-prev" src="{{ $logoUrl ?? '' }}" alt="Logo saat ini" @if (!$logoUrl) hidden @endif><span id="logo-none" @if ($logoUrl) hidden @endif>Belum ada logo</span></div>
                                    <div>
                                        <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp" class="emp-file @error('foto') is-invalid @enderror">
                                        <span class="emp-help">PNG dengan latar transparan paling rapi. JPG, PNG, atau WEBP, maksimal 1 MB, minimal 64 x 64 piksel.{{ $isEdit ? ' Kosongkan kalau tidak diganti.' : '' }}</span>
                                    </div>
                                </div>
                                @error('foto')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field is-wide">
                                <label for="header">Subjudul kop</label>
                                <input type="text" id="header" name="header" maxlength="150" value="{{ $val('header') }}" class="emp-input @error('header') is-invalid @enderror" placeholder="Mis. GENERAL TRADING AND DISTRIBUTION" autocomplete="off">
                                <span class="emp-help">Baris kecil di bawah nama perusahaan pada sebagian dokumen.</span>
                                @error('header')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field is-wide">
                                <label for="address">Alamat {!! $req !!}</label>
                                <textarea id="address" name="address" rows="2" maxlength="255" required class="emp-input @error('address') is-invalid @enderror" placeholder="Jalan, nomor, kota, kode pos">{{ $val('address') }}</textarea>
                                @error('address')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" maxlength="150" value="{{ $val('email') }}" class="emp-input @error('email') is-invalid @enderror" placeholder="info@perusahaan.co.id" autocomplete="email">
                                @error('email')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="website">Website</label>
                                <input type="text" id="website" name="website" maxlength="150" value="{{ $val('website') }}" class="emp-input @error('website') is-invalid @enderror" placeholder="perusahaan.co.id" autocomplete="url">
                                <span class="emp-help">Tanpa https://.</span>
                                @error('website')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </section>

                    <div class="emp-savebar">
                        <span>{{ $isEdit ? 'Kop baru dipakai untuk dokumen yang dibuat setelah disimpan.' : 'Perusahaan baru bisa langsung dipilih di data karyawan.' }}</span>
                        <a href="{{ route('pages.Company') }}" class="pr-btn">Batal</a>
                        <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ $isEdit ? 'Simpan perubahan' : 'Simpan perusahaan' }}</button>
                    </div>
                </div>

                <aside class="pr-panel lt-aside" aria-labelledby="h-prev">
                    <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-prev">Pratinjau kop surat</h2></div>
                    <div class="co-paper">
                        @include('pages.Company._kop', ['kopId' => 'kop-live', 'logo' => $logoUrl, 'name' => strtoupper((string) $val('name')), 'header' => strtoupper((string) $val('header')), 'address' => strtoupper((string) $val('address')), 'email' => $val('email'), 'website' => $val('website')])
                        <div class="co-paper-lines" aria-hidden="true"><i></i><i></i><i></i></div>
                    </div>
                    <p class="po-names">Perkiraan tampilan. Di PDF, nama, subjudul, dan alamat dicetak huruf kapital.</p>
                    @if ($usage)
                        <dl class="lt-usage co-usage">
                            <div><dt>Karyawan aktif</dt><dd>{{ number_format($usage['employees'], 0, ',', '.') }}</dd></div>
                            <div><dt>Lokasi</dt><dd>{{ number_format($usage['stores'], 0, ',', '.') }}</dd></div>
                            <div><dt>SK</dt><dd>{{ number_format($usage['sk'], 0, ',', '.') }}</dd></div>
                            <div><dt>Kontrak</dt><dd>{{ number_format($usage['contracts'], 0, ',', '.') }}</dd></div>
                        </dl>
                    @endif
                </aside>
            </div>
        </form>
    </section>
</div>
