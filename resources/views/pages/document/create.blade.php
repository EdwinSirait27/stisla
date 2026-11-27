@extends('layouts.app')
@section('title', 'Buat dokumen')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    @include('pages.Documenttype._styles')
    <style>
        .dc-type { display: grid; grid-template-columns: minmax(0, 480px) auto; gap: var(--pr-space-sm); align-items: center; }
        .dc-type-note { margin: var(--pr-space-xs) 0 0; font-size: var(--pr-text-sm); color: var(--pr-muted); max-width: 72ch; line-height: 1.5; }
        .dc-type-note a { color: var(--pr-accent); }
        .st-form .select2-container { width: 100% !important; }
        .st-form .select2-container .select2-selection--single,
        .st-form .select2-container .select2-selection--multiple { min-height: 40px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); font-family: var(--pr-font); font-size: var(--pr-text-md); }
        .st-form .select2-container .select2-selection--single { display: flex; align-items: center; }
        .st-form .select2-container--focus .select2-selection { outline: 2px solid var(--pr-focus); }
        .st-form .select2-selection--multiple .select2-search__field { margin-top: 8px; font-family: var(--pr-font); }
        @media (max-width: 40rem) { .dc-type { grid-template-columns: minmax(0, 1fr); } }
    </style>
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li><a href="{{ route('document.index') }}">Dokumen karyawan</a></li><li aria-current="page">Buat</li></ol>
                    <h1 class="pr-title">Buat dokumen</h1>
                    <p class="pr-lede">Dokumen yang diterbitkan HR. Surat tugas langsung terbit. Berita acara diajukan dan disetujui di menu Berita acara.</p>
                </div>
            </header>

            {{-- Jenis dokumen: menentukan form di bawahnya --}}
            <section class="pr-panel" aria-labelledby="h-jenis">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="h-jenis">Jenis dokumen</h2>
                        <span class="pr-panel-sub">Saat ini yang dibuat manual di sini hanya surat tugas.</span>
                    </div>
                </div>
                <div class="emp-fields">
                    <div class="emp-field">
                        <label for="jenis">Jenis</label>
                        <div class="dc-type">
                            <select id="jenis" class="dt-select">
                                <optgroup label="Terbit langsung">
                                    <option value="ST" selected>Surat Tugas (ST)</option>
                                </optgroup>
                                @if ($autoTypes->isNotEmpty())
                                    <optgroup label="Terbit otomatis oleh sistem">
                                        @foreach ($autoTypes as $t)
                                            <option disabled>{{ $t->document_name }} ({{ $t->nickname }})</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                        <p class="dc-type-note">
                            Jenis di kelompok "Terbit otomatis" diterbitkan oleh sistem dari proses lain (SPK, PAK, SP, dan lainnya), jadi tidak dibuat di sini.
                        </p>
                        @if ($canCreateBa)
                            <p class="dc-type-note">
                                Berita acara dan dokumen lain yang butuh approval dibuat di menu Berita acara.
                                <a href="{{ route('beritaacara.create') }}">Buat berita acara</a>
                            </p>
                        @endif
                    </div>
                </div>
            </section>

            @if ($errors->any())
                <div class="emp-errors" role="alert">
                    <strong>Belum bisa dibuat.</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('documents.store') }}" class="pr-panel st-form" id="st-form" aria-labelledby="h-st">
                @csrf
                <input type="hidden" name="document_type" value="ST">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="h-st">Surat tugas</h2>
                        <span class="pr-panel-sub">Pilih minimal satu tujuan pemindahan. Karyawan dipindah pada tanggal mulai, dan kembali ke penempatan semula sehari setelah tanggal selesai.</span>
                    </div>
                </div>
                <div class="emp-fields dt-fields-2">
                    <div class="emp-field dt-wide">
                        <label for="employee_id">Karyawan <span class="emp-req" aria-hidden="true">*</span></label>
                        <select id="employee_id" name="employee_id" class="select2" data-placeholder="Cari nama atau NIP" required>
                            <option value=""></option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(old('employee_id', $preselectEmployee ?? null) == $employee->id)>{{ $employee->employee_name }}{{ $employee->employee_pengenal ? ' ('.$employee->employee_pengenal.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="emp-field">
                        <label for="department_id">Departemen tujuan</label>
                        <select id="department_id" name="department_id" class="select2" data-placeholder="Tidak diubah">
                            <option value="">Tidak diubah</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="emp-field">
                        <label for="position_id">Posisi tujuan</label>
                        <select id="position_id" name="position_id" class="select2" data-placeholder="Tidak diubah">
                            <option value="">Tidak diubah</option>
                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="emp-field dt-wide">
                        <label for="store_id">Lokasi tujuan</label>
                        <select id="store_id" name="store_id[]" class="select2" multiple data-placeholder="Tidak diubah">
                            {{-- Yang sudah dipilih ditulis lebih dulu sesuai urutan pilihan: form mengirim sesuai urutan opsi --}}
                            @php($pickedStores = array_values(array_filter((array) old('store_id', []), 'is_string')))
                            @foreach ($pickedStores as $id)
                                @if ($s = $stores->firstWhere('id', $id))<option value="{{ $s->id }}" selected>{{ $s->name }}</option>@endif
                            @endforeach
                            @foreach ($stores->whereNotIn('id', $pickedStores) as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                        <span class="emp-help">Bisa lebih dari satu. Lokasi yang dipilih pertama (paling kiri) jadi lokasi utama selama surat tugas.</span>
                    </div>
                    <div class="emp-field">
                        <label for="issued_date">Tanggal mulai <span class="emp-req" aria-hidden="true">*</span></label>
                        <input type="date" id="issued_date" name="issued_date" class="emp-input" value="{{ old('issued_date') }}" required>
                    </div>
                    <div class="emp-field">
                        <label for="expired_date">Tanggal selesai <span class="emp-req" aria-hidden="true">*</span></label>
                        <input type="date" id="expired_date" name="expired_date" class="emp-input" value="{{ old('expired_date') }}" min="{{ today()->toDateString() }}" required>
                        <span class="emp-help">Masih termasuk masa tugas.</span>
                    </div>
                </div>
                <div class="dt-savebar">
                    <a href="{{ route('document.index') }}" class="pr-btn">Batal</a>
                    <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> Terbitkan surat tugas</button>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        jQuery(function($) {
            $('#st-form .select2').each(function() {
                $(this).select2({ width: '100%', placeholder: $(this).data('placeholder') || '', allowClear: !this.required && !this.multiple });
            });
            // Form mengirim lokasi sesuai urutan opsi, bukan urutan klik: opsi yang dipilih dipindah ke
            // belakang pilihan sebelumnya supaya lokasi pertama yang dipilih tetap jadi lokasi utama.
            $('#store_id').on('select2:select', function(e) {
                const opt = e.params.data.element;
                $(opt).detach();
                const lastPicked = $(this).find('option:selected').last();
                lastPicked.length ? lastPicked.after(opt) : $(this).prepend(opt);
                $(this).trigger('change.select2');
            });
        });
    </script>
@endpush
