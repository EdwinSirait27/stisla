{{-- Form karyawan bersama untuk create & edit. Variabel dari EmployeeController@create / @edit (nama berbeda, dinormalkan di sini). --}}
@php
    $isEdit = isset($employee);
    $e = $isEdit ? $employee->Employee : null;
    $v = fn($field, $default = '') => old($field, $isEdit ? ($e->{$field} ?? $default) : $default);
    $toMap = fn($items, $label) => collect($items)->mapWithKeys(fn($x, $k) => is_object($x) ? [$x->id => $x->{$label}] : [$k => $x])->all();
    $list = fn($a) => collect($a)->values()->all();

    $opt = [
        'company'    => $isEdit ? $toMap($companys, 'name') : $companys,
        'bank'       => $isEdit ? $toMap($banks, 'name') : $banks,
        'grading'    => $isEdit ? $toMap($gradings, 'grading_name') : $gradings,
        'type'       => $list($isEdit ? $status_employee : $employeestatuses),
        'gender'     => $list($isEdit ? $gender : $status_gender),
        'religion'   => $list($isEdit ? $religion : $status_religion),
        'education'  => $list($isEdit ? $last_education : $status_last_education),
        'marriage'   => $list($isEdit ? $marriage : $status_marriage),
        'child'      => $list($isEdit ? $child : $status_child),
        'blood'      => $list($bloodtypes),
        'attendance' => $attendancetypes,
    ];
    $genderLabel = ['Male' => 'Laki-laki', 'Female' => 'Perempuan', 'MD' => 'MD'];
    $marriageLabel = ['Yes' => 'Menikah', 'No' => 'Belum menikah'];
    $attTitle = \App\Models\Employee::getAttendanceTypeLabels();

    $sections = [
        'sec-personal' => ['Data pribadi', ['employee_name', 'gender', 'religion', 'place_of_birth', 'date_of_birth', 'blood_type', 'marriage', 'child', 'biological_mother_name', 'last_education', 'institution']],
        'sec-contact' => ['Kontak dan alamat', ['telp_number', 'email', 'company_email', 'emergency_contact_name', 'current_address', 'id_card_address']],
        'sec-identity' => ['Identitas, BPJS, bank', ['nik', 'kk_number', 'npwp', 'bpjs_kes', 'bpjs_ket', 'banks_id', 'bank_account_number']],
        'sec-job' => ['Pekerjaan', ['company_id', 'status_employee', 'status', 'join_date', 'end_date', 'grading_id', 'group_id', 'attendance_type', 'pin', 'can_approve', 'notes']],
        'sec-place' => ['Penempatan dan atasan', ['store_id', 'position_id', 'department_id', 'atasan_id', 'stores', 'positions', 'departments', 'atasans']],
        'sec-files' => ['Foto dan dokumen', ['photos', 'ktp_photos', 'kk_photos', 'signature_file']],
    ];
    $errCount = fn($fields) => collect($fields)->filter(fn($f) => $errors->has($f) || $errors->has($f . '.*'))->count();

    $req = '<span class="emp-req" aria-hidden="true">*</span>';
@endphp

<div class="main-content pr">
    <section class="section">
        <header class="pr-head">
            <div>
                <nav aria-label="Breadcrumb">
                    <ol class="pr-crumbs">
                        <li><a href="{{ route('pages.Employee') }}">Karyawan</a></li>
                        @if ($isEdit)<li><a href="{{ route('Employee.show', $hashedId) }}">{{ $e->employee_name }}</a></li>@endif
                        <li aria-current="page">{{ $isEdit ? 'Ubah' : 'Tambah' }}</li>
                    </ol>
                </nav>
                <h1 class="pr-title">{{ $isEdit ? 'Ubah data ' . $e->employee_name : 'Tambah karyawan' }}</h1>
                <p class="pr-lede">
                    @if ($isEdit)
                        NIP {{ $e->employee_pengenal }}, status {{ $e->status }}. Tanda {!! $req !!} wajib diisi.
                    @else
                        NIP dibuat otomatis. Username dan password awal sama dengan NIP. Tanda {!! $req !!} wajib diisi.
                    @endif
                </p>
            </div>
        </header>

        <form action="{{ $isEdit ? route('Employee.update', $hashedId) : route('Employee.store') }}" method="POST" enctype="multipart/form-data" id="emp-form" novalidate>
            @csrf
            @if ($isEdit) @method('PUT') @endif

            @if ($errors->any())
                <div class="emp-errors" role="alert">
                    <strong>{{ $errors->count() }} isian perlu diperbaiki.</strong> Bagian yang bermasalah ditandai angka merah di menu kiri.
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="emp-form-grid">
                <nav class="pr-panel" aria-label="Bagian form">
                    <ul class="emp-toc">
                        @foreach ($sections as $id => [$label, $fields])
                            <li><a href="#{{ $id }}">{{ $label }} @if ($errCount($fields))<span class="emp-toc-err">{{ $errCount($fields) }}</span>@endif</a></li>
                        @endforeach
                    </ul>
                </nav>

                <div class="emp-sections">
                    {{-- 1. Data pribadi --}}
                    <section class="pr-panel" id="sec-personal" aria-labelledby="h-personal">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-personal">Data pribadi</h2></div>
                        <div class="emp-fields is-3">
                            <div class="emp-field is-wide">
                                <label for="employee_name">Nama lengkap {!! $req !!}</label>
                                <input type="text" id="employee_name" name="employee_name" maxlength="255" required value="{{ $v('employee_name') }}" class="emp-input @error('employee_name') is-invalid @enderror" autocomplete="off">
                                <span class="emp-help">Sesuai KTP. Harus unik, tidak boleh sama dengan karyawan lain.</span>
                                @error('employee_name')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            @foreach ([['gender', 'Jenis kelamin', $opt['gender'], $genderLabel], ['religion', 'Agama', $opt['religion'], []], ['blood_type', 'Golongan darah', $opt['blood'], []]] as [$f, $label, $items, $map])
                                <div class="emp-field">
                                    <label for="{{ $f }}">{{ $label }} @if ($f !== 'blood_type'){!! $req !!}@endif</label>
                                    <select id="{{ $f }}" name="{{ $f }}" class="emp-select @error($f) is-invalid @enderror" @if ($f !== 'blood_type') required @endif>
                                        <option value="">Pilih</option>
                                        @foreach ($items as $it)
                                            <option value="{{ $it }}" @selected($v($f) == $it)>{{ $map[$it] ?? $it }}</option>
                                        @endforeach
                                    </select>
                                    @error($f)<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                            @endforeach
                            <div class="emp-field">
                                <label for="place_of_birth">Tempat lahir {!! $req !!}</label>
                                <input type="text" id="place_of_birth" name="place_of_birth" maxlength="255" required value="{{ $v('place_of_birth') }}" class="emp-input @error('place_of_birth') is-invalid @enderror">
                                @error('place_of_birth')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="date_of_birth">Tanggal lahir {!! $req !!}</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" required max="{{ now()->subYears(15)->toDateString() }}"
                                    value="{{ old('date_of_birth', $isEdit && $e->getRawOriginal('date_of_birth') ? \Carbon\Carbon::parse($e->getRawOriginal('date_of_birth'))->format('Y-m-d') : '') }}"
                                    class="emp-input @error('date_of_birth') is-invalid @enderror" aria-describedby="age-note">
                                <span class="emp-help" id="age-note" aria-live="polite"></span>
                                @error('date_of_birth')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="biological_mother_name">Nama ibu kandung {!! $req !!}</label>
                                <input type="text" id="biological_mother_name" name="biological_mother_name" maxlength="255" required value="{{ $v('biological_mother_name') }}" class="emp-input @error('biological_mother_name') is-invalid @enderror">
                                @error('biological_mother_name')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            @foreach ([['marriage', 'Status menikah', $opt['marriage'], $marriageLabel], ['child', 'Jumlah anak', $opt['child'], []], ['last_education', 'Pendidikan terakhir', $opt['education'], []]] as [$f, $label, $items, $map])
                                <div class="emp-field">
                                    <label for="{{ $f }}">{{ $label }} {!! $req !!}</label>
                                    <select id="{{ $f }}" name="{{ $f }}" class="emp-select @error($f) is-invalid @enderror" required>
                                        <option value="">Pilih</option>
                                        @foreach ($items as $it)
                                            <option value="{{ $it }}" @selected((string) $v($f) === (string) $it)>{{ $map[$it] ?? $it }}</option>
                                        @endforeach
                                    </select>
                                    @error($f)<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                            @endforeach
                            <div class="emp-field is-wide">
                                <label for="institution">Sekolah atau kampus {!! $req !!}</label>
                                <input type="text" id="institution" name="institution" maxlength="255" required value="{{ $v('institution') }}" class="emp-input @error('institution') is-invalid @enderror">
                                @error('institution')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </section>

                    {{-- 2. Kontak --}}
                    <section class="pr-panel" id="sec-contact" aria-labelledby="h-contact">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-contact">Kontak dan alamat</h2></div>
                        <div class="emp-fields">
                            <div class="emp-field">
                                <label for="telp_number">Nomor telepon {!! $req !!}</label>
                                <input type="tel" id="telp_number" name="telp_number" inputmode="numeric" maxlength="13" required value="{{ $v('telp_number') }}" class="emp-input @error('telp_number') is-invalid @enderror" placeholder="08xxxxxxxxxx">
                                <span class="emp-help">10 sampai 13 digit, hanya angka. Dipakai untuk notifikasi WhatsApp.</span>
                                @error('telp_number')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="emergency_contact_name">Kontak darurat {!! $req !!}</label>
                                <input type="text" id="emergency_contact_name" name="emergency_contact_name" maxlength="255" required value="{{ $v('emergency_contact_name') }}" class="emp-input @error('emergency_contact_name') is-invalid @enderror" placeholder="Nama, hubungan, nomor">
                                @error('emergency_contact_name')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="email">Email pribadi {!! $req !!}</label>
                                <input type="email" id="email" name="email" maxlength="255" required value="{{ $v('email') }}" class="emp-input @error('email') is-invalid @enderror">
                                <span class="emp-help">{{ $isEdit ? 'Untuk slip gaji dan reset password.' : 'Email selamat datang dikirim ke sini setelah disimpan.' }}</span>
                                @error('email')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="company_email">Email kantor</label>
                                <input type="email" id="company_email" name="company_email" maxlength="255" value="{{ $v('company_email') }}" class="emp-input @error('company_email') is-invalid @enderror">
                                @error('company_email')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field is-wide">
                                <label for="current_address">Alamat domisili {!! $req !!}</label>
                                <textarea id="current_address" name="current_address" maxlength="255" required rows="2" class="emp-input @error('current_address') is-invalid @enderror">{{ $v('current_address') }}</textarea>
                                @error('current_address')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field is-wide">
                                <label for="id_card_address">Alamat KTP {!! $req !!}</label>
                                <textarea id="id_card_address" name="id_card_address" maxlength="255" required rows="2" class="emp-input @error('id_card_address') is-invalid @enderror">{{ $v('id_card_address') }}</textarea>
                                <label class="emp-inline-check"><input type="checkbox" id="same-address"> Sama dengan alamat domisili</label>
                                @error('id_card_address')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </section>

                    {{-- 3. Identitas --}}
                    <section class="pr-panel" id="sec-identity" aria-labelledby="h-identity">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="h-identity">Identitas, BPJS, bank</h2>
                                <span class="pr-panel-sub">Isi 0 untuk BPJS atau NPWP yang belum ada.</span>
                            </div>
                        </div>
                        <div class="emp-fields is-3">
                            @foreach ([['nik', 'NIK', true, 20, 'Harus unik, 16 digit sesuai KTP.'], ['kk_number', 'Nomor KK', false, 20, null], ['npwp', 'NPWP', true, 50, null], ['bpjs_kes', 'BPJS Kesehatan', true, 255, null], ['bpjs_ket', 'BPJS Ketenagakerjaan', true, 255, null]] as [$f, $label, $required, $max, $help])
                                <div class="emp-field">
                                    <label for="{{ $f }}">{{ $label }} @if ($required){!! $req !!}@endif</label>
                                    <input type="text" id="{{ $f }}" name="{{ $f }}" inputmode="numeric" maxlength="{{ $max }}" @if ($required) required @endif value="{{ $v($f) }}" class="emp-input @error($f) is-invalid @enderror" autocomplete="off">
                                    @if ($help)<span class="emp-help">{{ $help }}</span>@endif
                                    @error($f)<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                            @endforeach
                            <div class="emp-field"></div>
                            <div class="emp-field">
                                <label for="banks_id">Bank gaji {!! $req !!}</label>
                                <select id="banks_id" name="banks_id" required class="emp-select js-s2 @error('banks_id') is-invalid @enderror">
                                    <option value="">Pilih bank</option>
                                    @foreach ($opt['bank'] as $id => $name)
                                        <option value="{{ $id }}" @selected($v('banks_id') == $id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('banks_id')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="bank_account_number">Nomor rekening {!! $req !!}</label>
                                <input type="text" id="bank_account_number" name="bank_account_number" inputmode="numeric" maxlength="20" required value="{{ $v('bank_account_number') }}" class="emp-input @error('bank_account_number') is-invalid @enderror" autocomplete="off">
                                @error('bank_account_number')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </section>

                    {{-- 4. Pekerjaan --}}
                    <section class="pr-panel" id="sec-job" aria-labelledby="h-job">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="h-job">Pekerjaan</h2></div>
                        <div class="emp-fields is-3">
                            <div class="emp-field">
                                <label for="company_id">Perusahaan @if (!$isEdit){!! $req !!}@endif</label>
                                <select id="company_id" name="company_id" @if (!$isEdit) required @endif class="emp-select js-s2 @error('company_id') is-invalid @enderror">
                                    <option value="">Pilih perusahaan</option>
                                    @foreach ($opt['company'] as $id => $name)
                                        <option value="{{ $id }}" @selected($v('company_id') == $id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('company_id')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field">
                                <label for="status_employee">Tipe karyawan {!! $req !!}</label>
                                <select id="status_employee" name="status_employee" required class="emp-select @error('status_employee') is-invalid @enderror">
                                    <option value="">Pilih tipe</option>
                                    @foreach ($opt['type'] as $it)
                                        <option value="{{ $it }}" @selected($v('status_employee') == $it)>{{ $it }}</option>
                                    @endforeach
                                </select>
                                <span class="emp-help" id="type-note" aria-live="polite"></span>
                                @error('status_employee')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            @if ($isEdit)
                                <div class="emp-field">
                                    <label for="status">Status {!! $req !!}</label>
                                    <select id="status" name="status" required class="emp-select @error('status') is-invalid @enderror">
                                        @foreach ($status as $it)
                                            <option value="{{ $it }}" @selected($v('status') == $it)>{{ $it }}</option>
                                        @endforeach
                                    </select>
                                    <span class="emp-help">Hanya status Active yang bisa login.</span>
                                    @error('status')<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                            @else
                                <div class="emp-field">
                                    <span class="emp-label">Status</span>
                                    <span class="status-badge is-pending">Pending</span>
                                    <span class="emp-help">Karyawan baru selalu Pending. Ubah ke Active lewat edit setelah data lengkap.</span>
                                </div>
                            @endif
                            <div class="emp-field">
                                <label for="join_date">Tanggal bergabung {!! $req !!}</label>
                                <input type="date" id="join_date" name="join_date" required
                                    value="{{ old('join_date', $isEdit && $e->getRawOriginal('join_date') ? \Carbon\Carbon::parse($e->getRawOriginal('join_date'))->format('Y-m-d') : '') }}"
                                    class="emp-input @error('join_date') is-invalid @enderror">
                                @error('join_date')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            @if ($isEdit)
                                <div class="emp-field">
                                    <label for="end_date">Akhir kontrak atau tanggal keluar</label>
                                    <input type="date" id="end_date" name="end_date"
                                        value="{{ old('end_date', $e->getRawOriginal('end_date') ? \Carbon\Carbon::parse($e->getRawOriginal('end_date'))->format('Y-m-d') : '') }}"
                                        class="emp-input @error('end_date') is-invalid @enderror">
                                    <span class="emp-help">Wajib diisi saat status Resign. Karyawan resign tetap muncul di input gaji sampai 2 bulan setelah tanggal ini.</span>
                                    @error('end_date')<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                            @endif
                            <div class="emp-field">
                                <label for="grading_id">Grading @if (!$isEdit){!! $req !!}@endif</label>
                                <select id="grading_id" name="grading_id" @if (!$isEdit) required @endif class="emp-select js-s2 @error('grading_id') is-invalid @enderror">
                                    <option value="">Pilih grading</option>
                                    @foreach ($opt['grading'] as $id => $name)
                                        <option value="{{ $id }}" @selected($v('grading_id') == $id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <span class="emp-help">Menentukan siapa yang bisa menjadi atasannya.</span>
                                @error('grading_id')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            @if ($isEdit)
                                <div class="emp-field">
                                    <label for="group_id">Group</label>
                                    <select id="group_id" name="group_id" class="emp-select js-s2 @error('group_id') is-invalid @enderror">
                                        <option value="">Pilih group</option>
                                        @foreach ($groups as $g)
                                            <option value="{{ $g->id }}" @selected($v('group_id') == $g->id)>{{ $g->group_name }}, {{ $g->remark }}</option>
                                        @endforeach
                                    </select>
                                    @error('group_id')<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                            @endif
                            <div class="emp-field">
                                <label for="pin">PIN fingerprint @if ($isEdit){!! $req !!}@endif</label>
                                <input type="text" id="pin" name="pin" maxlength="50" @if ($isEdit) required @endif value="{{ $v('pin') }}" class="emp-input @error('pin') is-invalid @enderror" autocomplete="off">
                                <span class="emp-help">Sama dengan PIN di mesin fingerprint. Harus unik.</span>
                                @error('pin')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field is-wide">
                                <span class="emp-label">Tipe absen lewat aplikasi</span>
                                <div class="emp-fields" style="padding:0;gap:var(--pr-space-xs)">
                                    @foreach ($opt['attendance'] as $key => $label)
                                        <label class="emp-check">
                                            <input type="radio" name="attendance_type" value="{{ $key }}" @checked($v('attendance_type') === $key)>
                                            <span><strong>{{ $attTitle[$key] ?? $key }}</strong><small>{{ $label }}</small></span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('attendance_type')<span class="emp-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="emp-field is-wide">
                                <input type="hidden" name="can_approve" value="0">
                                <label class="emp-check">
                                    <input type="checkbox" name="can_approve" value="1" @checked((bool) $v('can_approve', false))>
                                    <span><strong>Bisa menyetujui pengajuan bawahan</strong><small>Dipakai saat sistem mencari atasan penyetuju berdasarkan grading, lokasi, dan departemen.</small></span>
                                </label>
                            </div>
                            @if ($isEdit)
                                <div class="emp-field is-wide">
                                    <label for="notes">Catatan</label>
                                    <textarea id="notes" name="notes" maxlength="255" rows="2" class="emp-input @error('notes') is-invalid @enderror" placeholder="mis. alasan resign atau mutasi">{{ $v('notes') }}</textarea>
                                    @error('notes')<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                            @endif
                        </div>
                    </section>

                    {{-- 5. Penempatan --}}
                    <section class="pr-panel" id="sec-place" aria-labelledby="h-place">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="h-place">Penempatan dan atasan</h2>
                                <span class="pr-panel-sub">{{ $isEdit ? 'Boleh lebih dari satu. Yang utama dipakai roster, dashboard, payroll, dan approval.' : 'Tambahan lokasi, jabatan, atau atasan bisa diatur lewat edit setelah disimpan.' }}</span>
                            </div>
                        </div>
                        @if ($isEdit)
                            @php
                                $activeSt = \App\Models\Documents::where('employee_id', $e->id)->where('status', 'issued')
                                    ->whereHas('companydocumentconfigs.documenttypes', fn($q) => $q->where('nickname', 'ST'))
                                    ->orderBy('issued_date')->get(['id', 'document_number', 'issued_date', 'expired_date']);
                            @endphp
                            @foreach ($activeSt as $st)
                                @php
                                    $stStarted = \Carbon\Carbon::parse($st->issued_date)->startOfDay()->lte(today());
                                @endphp
                                <div class="emp-errors" role="note" style="margin: 0 0 var(--pr-space-sm, 12px)">
                                    <strong>{{ $stStarted ? 'Sedang menjalani' : 'Punya' }} Surat Tugas {{ $st->document_number }}
                                        ({{ \Carbon\Carbon::parse($st->issued_date)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($st->expired_date)->format('d/m/Y') }}).</strong>
                                    @if ($stStarted)
                                        Penempatan di bawah sudah berisi pemindahan surat tugas. Kalau Anda mengubah lokasi, jabatan, atau departemen utama di sini,
                                        bagian itu tidak dikembalikan otomatis saat surat tugas selesai. Untuk mengubah tujuan sementara, edit surat tugasnya.
                                    @else
                                        Karyawan akan dipindah otomatis pada tanggal mulai. Perubahan penempatan di sini akan jadi penempatan "semula" yang dikembalikan setelah surat tugas selesai.
                                    @endif
                                    @can('ManageDocument')
                                        <a href="{{ route('documents.st.edit', $st->id) }}">Buka surat tugas</a>
                                    @endcan
                                </div>
                            @endforeach
                        @endif
                        <div class="emp-fields">
                            @if ($isEdit)
                                @foreach ([
                                    ['stores', 'Lokasi', $allStores, 'name', $selectedStores, $primaryStoreId, 'primary_store'],
                                    ['positions', 'Jabatan', $allPositions, 'name', $selectedPositions, $primaryPositionId, 'primary_position'],
                                    ['departments', 'Departemen', $allDepartments, 'department_name', $selectedDepartments, $primaryDepartmentId, 'primary_department'],
                                    ['atasans', 'Atasan', $allEmployees, 'employee_name', $selectedAtasans, $primaryEmployeeId, 'primary_atasan'],
                                ] as [$f, $label, $items, $col, $selected, $primaryId, $pf])
                                    @php $chosen = collect(old($f, $selected))->map(fn($x) => (string) $x)->all(); @endphp
                                    <div class="emp-field js-multi" data-field="{{ $f }}">
                                        <label for="{{ $f }}">{{ $label }}</label>
                                        <select id="{{ $f }}" name="{{ $f }}[]" multiple class="emp-select js-s2 @error($f) is-invalid @enderror">
                                            @foreach ($items as $it)
                                                <option value="{{ $it->id }}" @selected(in_array((string) $it->id, $chosen, true))>{{ $it->{$col} }}</option>
                                            @endforeach
                                        </select>
                                        <label for="{{ $pf }}" class="emp-help" style="margin-top:8px">{{ $label }} utama</label>
                                        <select id="{{ $pf }}" name="{{ $pf }}" class="emp-select js-primary" data-current="{{ old($pf, $primaryId) }}"></select>
                                        @if ($f === 'atasans')<span class="emp-help">Hanya karyawan aktif dengan grading lebih tinggi. Kosong = pengajuan cuti tidak punya penyetuju.</span>@endif
                                        @error($f)<span class="emp-error">{{ $message }}</span>@enderror
                                        @error($f . '.*')<span class="emp-error">{{ $message }}</span>@enderror
                                    </div>
                                @endforeach
                            @else
                                @foreach ([['store_id', 'Lokasi utama', $stores, true], ['position_id', 'Jabatan utama', $positions, true], ['department_id', 'Departemen utama', $departments, true], ['atasan_id', 'Atasan langsung', $atasans, false]] as [$f, $label, $items, $required])
                                    <div class="emp-field">
                                        <label for="{{ $f }}">{{ $label }} @if ($required){!! $req !!}@endif</label>
                                        <select id="{{ $f }}" name="{{ $f }}" @if ($required) required @endif class="emp-select js-s2 @error($f) is-invalid @enderror">
                                            <option value="">Pilih</option>
                                            @foreach ($items as $id => $name)
                                                <option value="{{ $id }}" @selected(old($f) == $id)>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($f === 'atasan_id')<span class="emp-help">Yang menyetujui cuti dan lembur. Bisa dikosongkan lalu diisi nanti.</span>@endif
                                        @error($f)<span class="emp-error">{{ $message }}</span>@enderror
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </section>

                    {{-- 6. Foto --}}
                    <section class="pr-panel" id="sec-files" aria-labelledby="h-files">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="h-files">Foto dan dokumen</h2>
                                <span class="pr-panel-sub">JPG, PNG, atau WEBP, maksimal 512 KB per file. {{ $isEdit ? 'Kosongkan kalau tidak diganti.' : 'Boleh diisi nanti.' }}</span>
                            </div>
                        </div>
                        <div class="emp-fields" style="grid-template-columns:repeat(4,minmax(0,1fr))">
                            @foreach ([['photos', 'Foto profil', 'photos', 'useremployee.photo'], ['ktp_photos', 'Foto KTP', 'ktp_photos', 'useremployeektp.photo'], ['kk_photos', 'Foto KK', 'kk_photos', 'useremployeekk.photo'], ['signature_file', 'Tanda tangan', 'signature', 'useremployeesignature.photo']] as [$f, $label, $col, $route])
                                @php $cur = $isEdit ? $e->{$col} : null; @endphp
                                <div class="emp-field emp-upload">
                                    <label for="{{ $f }}">{{ $label }}</label>
                                    <div class="emp-photo-box">
                                        <img id="prev-{{ $f }}" src="{{ $cur ? route($route, basename($cur)) : '' }}" alt="" @if (!$cur) hidden @endif>
                                        <span id="none-{{ $f }}" @if ($cur) hidden @endif>Belum ada</span>
                                    </div>
                                    <input type="file" id="{{ $f }}" name="{{ $f }}" accept=".jpg,.jpeg,.png,.webp" class="emp-file js-file @error($f) is-invalid @enderror" data-max-kb="512" @if ($f === 'signature_file') data-keep-alpha @endif data-prev="prev-{{ $f }}" data-none="none-{{ $f }}">
                                    @error($f)<span class="emp-error">{{ $message }}</span>@enderror
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <div class="emp-savebar">
                        <span id="dirty-note">{{ $isEdit ? 'Perubahan baru tersimpan setelah klik Simpan.' : 'Setelah disimpan, akun login dibuat otomatis dengan role Human.' }}</span>
                        <a href="{{ $isEdit ? route('Employee.show', $hashedId) : route('pages.Employee') }}" class="pr-btn">Batal</a>
                        <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ $isEdit ? 'Simpan perubahan' : 'Simpan karyawan' }}</button>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>
