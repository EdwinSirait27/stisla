{{-- Form gaji bersama untuk create & edit.
     create: $employees, $history (employee_id => list)   edit: $salary, $history (list milik karyawan ini) --}}
@php
    $isEdit = isset($salary);
    $emp = $isEdit ? $salary->employee : null;
    $isDwEdit = $isEdit && strtoupper($emp->status_employee ?? '') === 'DW';
    $money = fn($field, $fallback = 0) => old($field, $isEdit ? (float) $salary->{$field} : $fallback);
    $fields = [
        'basic_salary' => ['Gaji pokok', 'Prorata sesuai kehadiran.', 'monthly'],
        'position_allowance' => ['Tunjangan jabatan', 'Prorata sesuai kehadiran, ikut gaji tetap.', 'monthly'],
        'daily_rate' => ['Tarif harian', 'Dibayar per hari hadir.', 'dw'],
    ];
    $allowances = [
        'meal_allowance' => 'Uang makan',
        'house_allowance' => 'Tunjangan rumah',
        'transport_allowance' => 'Tunjangan transport',
    ];
    $bpjs = [
        'bpjs_kesehatan' => 'BPJS Kesehatan',
        'bpjs_ketenagakerjaan' => 'BPJS Ketenagakerjaan',
    ];
@endphp
<div class="main-content pr">
    <section class="section">
        <header class="pr-head">
            <div>
                <nav aria-label="Breadcrumb">
                    <ol class="pr-crumbs">
                        <li><a href="{{ route('employeesalary.index') }}">Gaji karyawan</a></li>
                        <li aria-current="page">{{ $isEdit ? 'Ubah' : 'Tambah' }}</li>
                    </ol>
                </nav>
                <h1 class="pr-title">{{ $isEdit ? 'Ubah gaji ' . ($emp->employee_name ?? '') : 'Tambah gaji karyawan' }}</h1>
                <p class="pr-lede">
                    Payroll memakai gaji dengan tanggal berlaku terakhir yang jatuh sebelum akhir periode gaji.
                </p>
            </div>
        </header>

        <div class="sal-grid">
            <form action="{{ $isEdit ? route('employeesalary.update', $salary->id) : route('employeesalary.store') }}" method="POST" class="pr-panel" id="salary-form" novalidate>
                @csrf
                @if ($isEdit) @method('PUT') @endif

                @if ($errors->any())
                    <div class="sal-errors" role="alert">
                        <strong>Belum bisa disimpan.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- 1. Karyawan & tanggal --}}
                <fieldset class="sal-section">
                    <legend>Karyawan dan tanggal berlaku</legend>
                    <div class="sal-row">
                        <div class="sal-field is-wide">
                            <label for="employee_id">Karyawan</label>
                            @if ($isEdit)
                                <div class="sal-emp">
                                    <span class="sal-emp-name">{{ $emp->employee_name ?? '-' }}</span>
                                    <span class="sal-emp-sub">
                                        {{ collect([$emp->employee_pengenal, $emp->status_employee, $emp->status])->filter()->implode(', ') }}
                                    </span>
                                </div>
                                <input type="hidden" id="employee_id" value="{{ $salary->employee_id }}" data-status="{{ $emp->status_employee }}">
                            @else
                                <select name="employee_id" id="employee_id" class="select2-employee @error('employee_id') is-invalid @enderror" required>
                                    <option value="">Cari nama atau NIP</option>
                                    @foreach ($employees as $e)
                                        <option value="{{ $e->id }}" data-status="{{ $e->status_employee }}" data-emp-status="{{ $e->status }}"
                                            @selected(old('employee_id', request('employee_id')) == $e->id)>
                                            {{ $e->employee_pengenal }}, {{ $e->employee_name }} ({{ $e->status_employee }}{{ $e->status !== 'Active' ? ', ' . $e->status : '' }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="sal-help">Karyawan resign hanya muncul sampai 2 bulan setelah tanggal keluar.</span>
                            @endif
                            @error('employee_id')<span class="sal-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="sal-field">
                            <label for="effective_date">Berlaku mulai</label>
                            <input type="date" name="effective_date" id="effective_date" required
                                value="{{ old('effective_date', $isEdit ? $salary->effective_date?->format('Y-m-d') : '') }}"
                                class="sal-input @error('effective_date') is-invalid @enderror">
                            <span class="sal-help" id="date-note" aria-live="polite"></span>
                            @error('effective_date')<span class="sal-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <p class="sal-type-note" id="type-note" hidden></p>
                </fieldset>

                <div id="salary-fields" @if (!$isEdit && !old('employee_id', request('employee_id'))) hidden @endif>
                    {{-- 2. Gaji tetap --}}
                    <fieldset class="sal-section">
                        <legend>Gaji tetap</legend>
                        <div class="sal-row">
                            @foreach ($fields as $name => [$label, $help, $kind])
                                <div class="sal-field" data-kind="{{ $kind }}">
                                    <label for="{{ $name }}">{{ $label }}</label>
                                    <div class="sal-money">
                                        <span aria-hidden="true">Rp</span>
                                        <input type="text" inputmode="decimal" name="{{ $name }}" id="{{ $name }}" class="sal-input js-money @error($name) is-invalid @enderror"
                                            value="{{ $money($name) }}" autocomplete="off">
                                    </div>
                                    <span class="sal-help">{{ $help }}</span>
                                    @error($name)<span class="sal-error">{{ $message }}</span>@enderror
                                </div>
                            @endforeach
                        </div>
                    </fieldset>

                    {{-- 3. Tunjangan --}}
                    <fieldset class="sal-section">
                        <legend>Tunjangan bulanan <span>dibayar penuh, tidak prorata. Isi 0 kalau tidak dapat.</span></legend>
                        <div class="sal-row is-3">
                            @foreach ($allowances as $name => $label)
                                <div class="sal-field">
                                    <label for="{{ $name }}">{{ $label }}</label>
                                    <div class="sal-money">
                                        <span aria-hidden="true">Rp</span>
                                        <input type="text" inputmode="decimal" name="{{ $name }}" id="{{ $name }}" class="sal-input js-money @error($name) is-invalid @enderror"
                                            value="{{ $money($name) }}" autocomplete="off">
                                    </div>
                                    @error($name)<span class="sal-error">{{ $message }}</span>@enderror
                                </div>
                            @endforeach
                        </div>
                    </fieldset>

                    {{-- 4. BPJS --}}
                    <fieldset class="sal-section">
                        <legend>Potongan BPJS <span>bagian karyawan, dipotong setiap bulan. Isi 0 kalau belum terdaftar.</span></legend>
                        <div class="sal-row">
                            @foreach ($bpjs as $name => $label)
                                <div class="sal-field">
                                    <label for="{{ $name }}">{{ $label }}</label>
                                    <div class="sal-money">
                                        <span aria-hidden="true">Rp</span>
                                        <input type="text" inputmode="decimal" name="{{ $name }}" id="{{ $name }}" class="sal-input js-money @error($name) is-invalid @enderror"
                                            value="{{ $money($name) }}" autocomplete="off">
                                    </div>
                                    @error($name)<span class="sal-error">{{ $message }}</span>@enderror
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                </div>

                <div class="sal-foot">
                    <button type="button" class="pr-btn pr-btn-sm" id="copy-current" hidden>Salin dari gaji yang berlaku</button>
                    <span class="sal-foot-gap"></span>
                    <a href="{{ route('employeesalary.index') }}" class="pr-btn">Batal</a>
                    <button type="submit" class="pr-btn pr-btn-primary" id="btn-save"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ $isEdit ? 'Simpan perubahan' : 'Simpan' }}</button>
                </div>
            </form>

            <aside class="sal-side">
                <section class="pr-panel" aria-labelledby="sum-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="sum-title">Ringkasan per bulan</h2>
                            <span class="pr-panel-sub" id="sum-sub">Kehadiran penuh, sebelum lembur, pajak, dan potongan lain</span>
                        </div>
                    </div>
                    <dl class="sal-sum">
                        <div><dt id="sum-fixed-label">Gaji tetap</dt><dd id="sum-fixed">Rp0</dd></div>
                        <div><dt>Tunjangan</dt><dd id="sum-allow">Rp0</dd></div>
                        <div><dt>Potongan BPJS</dt><dd id="sum-bpjs">Rp0</dd></div>
                        <div class="is-total"><dt id="sum-net-label">Perkiraan diterima</dt><dd id="sum-net">Rp0</dd></div>
                    </dl>
                    <p class="sal-compare" id="sum-compare" hidden></p>
                </section>

                <section class="pr-panel" aria-labelledby="hist-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="hist-title">Riwayat gaji</h2>
                            <span class="pr-panel-sub" id="hist-sub">{{ $isEdit ? count($history) . ' data' : 'Pilih karyawan dulu' }}</span>
                        </div>
                    </div>
                    <ul class="sal-hist" id="hist-list"></ul>
                    <p class="pr-empty-note" id="hist-empty" hidden>Belum ada data gaji. Ini akan menjadi gaji pertamanya.</p>
                </section>
            </aside>
        </div>
    </section>
</div>
