@extends('layouts.app')
@section('title', 'Add Employee Salary')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .emp-form-card { background:#fff; border-radius:.625rem; box-shadow:0 1px 3px rgba(0,0,0,.07); overflow:hidden; margin-bottom:1.25rem; }
        .emp-form-header { background:#f8fafc; border-bottom:1px solid #f1f5f9; padding:.875rem 1.25rem; display:flex; align-items:center; gap:.6rem; }
        .emp-form-header-icon { width:28px; height:28px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:.8rem; background:#eff6ff; color:#1d4ed8; }
        .emp-form-header-title { font-size:.9rem; font-weight:600; color:#334155; flex:1; }
        .form-body { padding:1.5rem 1.25rem; }
        .form-section { margin-bottom:1.5rem; }
        .form-section-label { font-size:.68rem; font-weight:700; letter-spacing:.7px; color:#94a3b8; text-transform:uppercase; margin-bottom:.75rem; padding-bottom:.4rem; border-bottom:1px solid #f1f5f9; }
        .field-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
        .field-group { display:flex; flex-direction:column; gap:.35rem; }
        .field-group label { font-size:.78rem; font-weight:600; color:#475569; display:flex; align-items:center; gap:.35rem; }
        .field-group .form-control { height:38px; font-size:.825rem; border:1px solid #e2e8f0; border-radius:.5rem; }
        .field-group .form-control:focus { border-color:#1d4ed8; box-shadow:0 0 0 3px rgba(29,78,216,.08); }
        .field-group .is-invalid { border-color:#dc2626; }
        .invalid-feedback { font-size:.75rem; color:#dc2626; }
        .emp-form-footer { background:#f8fafc; border-top:1px solid #f1f5f9; padding:.875rem 1.25rem; display:flex; justify-content:space-between; align-items:center; }
        .btn-back { height:36px; font-size:.825rem; padding:0 1rem; display:inline-flex; align-items:center; gap:.4rem; border-radius:.5rem; background:#fff; border:1px solid #e2e8f0; color:#64748b; text-decoration:none; }
        .btn-save { height:36px; font-size:.825rem; padding:0 1.25rem; display:inline-flex; align-items:center; gap:.4rem; border-radius:.5rem; background:#1d4ed8; border:none; color:#fff; cursor:pointer; }
        .alert-danger-custom { background:#fef2f2; border:1px solid #fecaca; border-radius:.5rem; padding:.75rem 1rem; margin-bottom:1rem; font-size:.8rem; color:#dc2626; }
        .alert-danger-custom ul { margin:0; padding-left:1.25rem; }
        .emp-info-pill { display:inline-flex; align-items:center; gap:.5rem; background:#f1f5f9; border-radius:20px; padding:.25rem .75rem .25rem .35rem; font-size:.78rem; color:#475569; margin-top:.5rem; }
        .emp-info-pill-avatar { width:22px; height:22px; border-radius:50%; background:#1d4ed8; color:#fff; font-size:.6rem; font-weight:700; display:flex; align-items:center; justify-content:center; }
        .badge-status { display:inline-flex; align-items:center; padding:.15rem .55rem; border-radius:20px; font-size:.7rem; font-weight:700; }
        .badge-pkwt { background:#eff6ff; color:#1e40af; }
        .badge-ojt { background:#fdf4ff; color:#6b21a8; }
        .badge-dw { background:#fffbeb; color:#92400e; }
        .field-hidden { display:none; }
        .select2-container--default .select2-selection--single { height:38px !important; border:1px solid #e2e8f0; border-radius:.5rem !important; display:flex; align-items:center; }
        .select2-container--default .select2-selection__rendered { font-size:.825rem; line-height:38px !important; }
        .select2-container--default .select2-selection__arrow { height:38px !important; }
    </style>
@endpush

@section('main')
<div class="main-content">
    <section class="section">

        <div class="section-header">
            <div>
                <div style="font-size:.72rem;color:#94a3b8;margin-bottom:3px">
                    Dashboard /
                    <a href="{{ route('employeesalary.index') }}" style="color:#64748b;text-decoration:none">Employee Salary</a> /
                    <span style="color:#1e293b">Add</span>
                </div>
                <h1>Add Employee Salary</h1>
            </div>
        </div>

        <div class="emp-form-card">
            <div class="emp-form-header">
                <div class="emp-form-header-icon"><i class="fas fa-dollar-sign"></i></div>
                <span class="emp-form-header-title">Input data salary karyawan</span>
            </div>

            <form action="{{ route('employeesalary.store') }}" method="POST">
                @csrf
                <div class="form-body">

                    @if($errors->any())
                        <div class="alert-danger-custom">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Section 1: Pilih Employee --}}
                    <div class="form-section">
                        <div class="form-section-label">Pilih Employee</div>
                        <div class="field-grid">

                            <div class="field-group" style="grid-column:span 2">
                                <label><i class="fas fa-user"></i> Employee</label>
                                <select name="employee_id" id="employee_id"
                                    class="select2-employee w-100 @error('employee_id') is-invalid @enderror" required>
                                    <option value="">-- Cari Employee --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                            data-status="{{ $emp->status_employee }}"
                                            data-name="{{ $emp->employee_name }}"
                                            {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->employee_pengenal }} — {{ $emp->employee_name }} — {{ $emp->status_employee }} - {{ $emp->status }} 
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror

                                {{-- Info pill muncul setelah pilih employee --}}
                                <div id="employee-info" style="display:none">
                                    <div class="emp-info-pill">
                                        <div class="emp-info-pill-avatar" id="emp-initials">--</div>
                                        <span id="emp-name-display">-</span>
                                        <span id="emp-status-badge"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="field-group">
                                <label><i class="fas fa-calendar"></i> Effective Date</label>
                                <input type="date" name="effective_date" id="effective_date"
                                    class="form-control @error('effective_date') is-invalid @enderror"
                                    value="{{ old('effective_date') }}" required>
                                @error('effective_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- Section 2: Salary Fields --}}
                    <div class="form-section" id="salary-section" style="display:none">
                        <div class="form-section-label">Data Salary</div>
                        <div class="field-grid">

                            {{-- PKWT / OJT fields --}}
                            <div class="field-group" id="field-basic-salary">
                                <label><i class="fas fa-money-bill"></i> Basic Salary</label>
                                <input type="number" name="basic_salary" id="basic_salary"
                                    class="form-control @error('basic_salary') is-invalid @enderror"
                                    value="{{ old('basic_salary', 0) }}" min="0" placeholder="0">
                                @error('basic_salary')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field-group" id="field-position-allowance">
                                <label><i class="fas fa-briefcase"></i> Position Allowance</label>
                                <input type="number" name="position_allowance" id="position_allowance"
                                    class="form-control @error('position_allowance') is-invalid @enderror"
                                    value="{{ old('position_allowance', 0) }}" min="0" placeholder="0">
                                @error('position_allowance')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- DW field --}}
                            <div class="field-group" id="field-daily-rate">
                                <label><i class="fas fa-calendar-day"></i> Daily Rate</label>
                                <input type="number" name="daily_rate" id="daily_rate"
                                    class="form-control @error('daily_rate') is-invalid @enderror"
                                    value="{{ old('daily_rate', 0) }}" min="0" placeholder="0">
                                @error('daily_rate')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                           

                        </div>
                    </div>

                </div>

                <div class="emp-form-footer">
                    <a href="{{ route('employeesalary.index') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-floppy-disk"></i> Save
                    </button>
                </div>
            </form>

        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            $('.select2-employee').select2({
                width: '100%',
                placeholder: '-- Cari Employee --',
                allowClear: true,
            });

            // ── Saat employee dipilih ──
            $('#employee_id').on('change', function () {
                const selected = $(this).find(':selected');
                const status   = selected.data('status');
                const name     = selected.data('name');
                const val      = $(this).val();

                if (!val) {
                    $('#employee-info').hide();
                    $('#salary-section').hide();
                    return;
                }

                // Tampilkan info pill
                const initials = name.split(' ').slice(0, 2).map(w => w[0].toUpperCase()).join('');
                $('#emp-initials').text(initials);
                $('#emp-name-display').text(name);

                const badgeMap = {
                    'PKWT':            '<span class="badge-status badge-pkwt">PKWT</span>',
                    'On Job Training': '<span class="badge-status badge-ojt">On Job Training</span>',
                    'DW':              '<span class="badge-status badge-dw">Daily Worker</span>',
                };
                $('#emp-status-badge').html(badgeMap[status] ?? status);
                $('#employee-info').show();

                // Auto hide/show field berdasarkan status
                showSalaryFields(status);
            });

            function showSalaryFields(status) {
                $('#salary-section').show();

                if (status === 'DW') {
                    // DW: hanya daily_rate + allowance
                    $('#field-basic-salary').hide();
                    $('#field-position-allowance').hide();
                    $('#field-daily-rate').show();

                    // Reset nilai PKWT/OJT
                    $('#basic_salary').val(0);
                    $('#position_allowance').val(0);
                } else {
                    // PKWT / OJT: basic + position + allowance
                    $('#field-basic-salary').show();
                    $('#field-position-allowance').show();
                    $('#field-daily-rate').hide();

                    // Reset nilai DW
                    $('#daily_rate').val(0);
                }
            }

            // ── Restore state saat ada old() value (validasi gagal) ──
            @if(old('employee_id'))
                const oldStatus = '{{ old('status_employee') }}';
                if (oldStatus) showSalaryFields(oldStatus);
            @endif
        });
         @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}'
            });
        @endif

    </script>
@endpush