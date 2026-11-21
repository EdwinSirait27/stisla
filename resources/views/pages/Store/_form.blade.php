{{-- Form lokasi, dipakai create & edit. Butuh: $store (null saat create), $usage, $radiusMin, $radiusMax, $action, $method. --}}
@php
    $isEdit = (bool) $store;
    $val = fn(string $f, $default = null) => old($f, $store?->{$f} ?? $default);
    $req = '<span class="emp-req" aria-hidden="true">*</span>';
    $phone = old('phone_num', $store && !preg_match('/^0*$/', (string) $store->phone_num) ? $store->phone_num : '');
@endphp

<div class="main-content pr">
    <section class="section">
        <header class="pr-head">
            <div>
                <nav aria-label="Breadcrumb">
                    <ol class="pr-crumbs">
                        <li><a href="{{ route('pages.Store') }}">Lokasi</a></li>
                        <li aria-current="page">{{ $isEdit ? $store->name : 'Tambah' }}</li>
                    </ol>
                </nav>
                <h1 class="pr-title">{{ $isEdit ? 'Ubah ' . $store->name : 'Tambah lokasi' }}</h1>
                <p class="pr-lede">Titik dan radius dipakai absensi mobile: karyawan hanya bisa absen di dalam lingkaran ini. Tanda {!! $req !!} wajib diisi.</p>
            </div>
        </header>

        <form action="{{ $action }}" method="POST" id="st-form" novalidate>
            @csrf
            @if ($method !== 'POST') @method($method) @endif

            @if ($errors->any())
                <div class="emp-errors" role="alert">
                    <strong>{{ $errors->count() }} isian perlu diperbaiki.</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="emp-sections">
                <section class="pr-panel" aria-labelledby="h-st">
                    <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-st">Lokasi</h2></div>
                    <div class="emp-fields is-3">
                        <div class="emp-field">
                            <label for="name">Nama lokasi {!! $req !!}</label>
                            <input type="text" id="name" name="name" maxlength="100" required value="{{ $val('name') }}" class="emp-input @error('name') is-invalid @enderror" placeholder="Mis. Uncle Jo Blok A" autocomplete="off">
                            @error('name')<span class="emp-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="emp-field">
                            <label for="nickname">Kode {!! $req !!}</label>
                            <input type="text" id="nickname" name="nickname" maxlength="10" required value="{{ $val('nickname') }}" class="emp-input @error('nickname') is-invalid @enderror" placeholder="Mis. BCA" autocomplete="off">
                            <span class="emp-help">Huruf dan angka, harus unik.</span>
                            @error('nickname')<span class="emp-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="emp-field">
                            <label for="phone_num">Telepon</label>
                            <input type="tel" id="phone_num" name="phone_num" maxlength="20" value="{{ $phone }}" class="emp-input @error('phone_num') is-invalid @enderror" placeholder="Mis. 0361234567" autocomplete="off">
                            @error('phone_num')<span class="emp-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="emp-field is-wide">
                            <label for="address">Alamat {!! $req !!}</label>
                            <textarea id="address" name="address" rows="2" maxlength="500" required class="emp-input @error('address') is-invalid @enderror" placeholder="Jalan, nomor, kota">{{ $val('address') }}</textarea>
                            @error('address')<span class="emp-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </section>

                <section class="pr-panel" aria-labelledby="h-geo">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="h-geo">Titik absensi</h2>
                            <span class="pr-panel-sub" id="geo-state">Klik peta atau seret penanda untuk memilih titik.</span>
                        </div>
                    </div>
                    @if ($usage && !$usage['has_location'] && $usage['store_bound'] > 0)
                        <p class="co-missing">{{ $usage['store_bound'] }} karyawan aktif di lokasi ini wajib absen di lokasi, dan saat ini selalu ditolak karena titiknya belum diisi.</p>
                    @endif
                    <div class="st-map-wrap">
                        <div id="st-map" class="st-map" role="application" aria-label="Peta untuk memilih titik lokasi"></div>
                        <noscript><p class="po-names">Peta butuh JavaScript. Isi latitude dan longitude secara manual.</p></noscript>
                    </div>
                    <div class="emp-fields is-3">
                        <div class="emp-field">
                            <label for="latitude">Latitude</label>
                            <input type="text" id="latitude" name="latitude" inputmode="decimal" value="{{ $val('latitude') }}" class="emp-input @error('latitude') is-invalid @enderror" placeholder="-8.6781960" autocomplete="off">
                            @error('latitude')<span class="emp-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="emp-field">
                            <label for="longitude">Longitude</label>
                            <input type="text" id="longitude" name="longitude" inputmode="decimal" value="{{ $val('longitude') }}" class="emp-input @error('longitude') is-invalid @enderror" placeholder="115.1892810" autocomplete="off">
                            @error('longitude')<span class="emp-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="emp-field">
                            <label for="geofence_radius">Radius {!! $req !!}</label>
                            <div class="lt-suffix"><input type="number" id="geofence_radius" name="geofence_radius" min="{{ $radiusMin }}" max="{{ $radiusMax }}" step="10" required value="{{ $val('geofence_radius', 100) }}" class="emp-input @error('geofence_radius') is-invalid @enderror"><span>meter</span></div>
                            @error('geofence_radius')<span class="emp-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="emp-field is-wide">
                            <span class="emp-help">Tip: di Google Maps, klik kanan titik lokasi lalu salin angka koordinatnya (mis. <code>-8.67819, 115.18928</code>) dan tempel ke kolom Latitude; keduanya terisi sendiri. Radius {{ $radiusMin }} sampai {{ $radiusMax }} meter; 50 sampai 100 meter cocok untuk toko, lebih besar untuk area gudang.</span>
                        </div>
                    </div>
                </section>

                <div class="emp-savebar">
                    <span>{{ $usage ? number_format($usage['employees'], 0, ',', '.') . ' karyawan aktif, ' . $usage['shifts'] . ' shift di lokasi ini.' : 'Setelah disimpan, buat shift-nya di menu Shifts.' }}</span>
                    <a href="{{ route('pages.Store') }}" class="pr-btn">Batal</a>
                    <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ $isEdit ? 'Simpan perubahan' : 'Simpan lokasi' }}</button>
                </div>
            </div>
        </form>
    </section>
</div>
