@extends('layouts.app')
@section('title', 'Edit Employee Salary')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .emp-form-card {
            background: #fff;
            border-radius: .625rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .07);
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        .emp-form-header {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            padding: .875rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .emp-form-header-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .emp-form-header-title {
            font-size: .9rem;
            font-weight: 600;
            color: #334155;
            flex: 1;
        }

        .emp-name-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #f1f5f9;
            border-radius: 20px;
            padding: .25rem .75rem .25rem .35rem;
            font-size: .78rem;
            color: #475569;
        }

        .emp-name-pill-avatar {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #1d4ed8;
            color: #fff;
            font-size: .6rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-body {
            padding: 1.5rem 1.25rem;
        }

        .form-section {
            margin-bottom: 1.5rem;
        }

        .form-section-label {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .7px;
            color: #94a3b8;
            text-transform: uppercase;
            margin-bottom: .75rem;
            padding-bottom: .4rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }

        .field-group label {
            font-size: .78rem;
            font-weight: 600;
            color: #475569;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .field-group .form-control {
            height: 38px;
            font-size: .825rem;
            border: 1px solid #e2e8f0;
            border-radius: .5rem;
        }

        .field-group .form-control:focus {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, .08);
        }

        .field-group .is-invalid {
            border-color: #dc2626;
        }

        .invalid-feedback {
            font-size: .75rem;
            color: #dc2626;
        }

        .emp-form-footer {
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            padding: .875rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-back {
            height: 36px;
            font-size: .825rem;
            padding: 0 1rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: .5rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #64748b;
            text-decoration: none;
        }

        .btn-save {
            height: 36px;
            font-size: .825rem;
            padding: 0 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: .5rem;
            background: #1d4ed8;
            border: none;
            color: #fff;
            cursor: pointer;
        }

        .alert-danger-custom {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: .5rem;
            padding: .75rem 1rem;
            margin-bottom: 1rem;
            font-size: .8rem;
            color: #dc2626;
        }

        .alert-danger-custom ul {
            margin: 0;
            padding-left: 1.25rem;
        }

        .readonly-field {
            background: #f8fafc;
            color: #64748b;
            cursor: not-allowed;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            padding: .15rem .55rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
        }

        .badge-pkwt {
            background: #eff6ff;
            color: #1e40af;
        }

        .badge-ojt {
            background: #fdf4ff;
            color: #6b21a8;
        }

        .badge-dw {
            background: #fffbeb;
            color: #92400e;
        }

        .request-text h3 {
            font-size: 14px;
            font-weight: 600;
            color: #fbbf24;
            margin-bottom: 4px;
        }

        .request-text p {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            margin: 0 0 4px 0;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">

            <div class="section-header">
                <div>
                    <div style="font-size:.72rem;color:#94a3b8;margin-bottom:3px">
                        Dashboard /
                        <a href="{{ route('employeesalary.index') }}" style="color:#64748b;text-decoration:none">Employee
                            Salary</a> /
                        <span style="color:#1e293b">Edit</span>
                    </div>
                    <h1>Edit Employee Salary</h1>
                </div>
            </div>

            <div class="emp-form-card">
                <div class="emp-form-header">
                    <div class="emp-form-header-icon"><i class="fas fa-dollar-sign"></i></div>
                    <span class="emp-form-header-title">Update data salary karyawan</span>
                    <div class="emp-name-pill">
                        <div class="emp-name-pill-avatar">
                            {{ collect(explode(' ', $salary->employee->employee_name ?? 'U'))->take(2)->map(fn($w) => strtoupper($w[0]))->implode('') }}
                        </div>
                        <span>{{ $salary->employee->employee_name ?? '-' }}</span>
                        @php
                            $status = $salary->employee->status_employee ?? '';
                            $badgeClass = match ($status) {
                                'PKWT' => 'badge-pkwt',
                                'On Job Training' => 'badge-ojt',
                                'DW' => 'badge-dw',
                                default => '',
                            };
                        @endphp
                        <span class="badge-status {{ $badgeClass }}">{{ $status }}</span>
                    </div>
                </div>

                <form action="{{ route('employeesalary.update', $salary->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-body">

                        @if ($errors->any())
                            <div class="alert-danger-custom">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Section 1: Info Employee (readonly) --}}
                        <div class="form-section">
                            <div class="form-section-label">Info Employee</div>
                            <div class="field-grid">

                                <div class="field-group">
                                    <label><i class="fas fa-id-card"></i> NIP</label>
                                    <input type="text" class="form-control readonly-field"
                                        value="{{ $salary->employee->employee_pengenal ?? '-' }}" readonly>
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-user"></i>Employee Name</label>
                                    <input type="text" class="form-control readonly-field"
                                        value="{{ $salary->employee->employee_name ?? '-' }}" readonly>
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-calendar"></i> Status</label>
                                    <input type="text" class="form-control readonly-field"
                                        value="{{ $salary->employee->status_employee ?? '-' }}" readonly>

                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-calendar"></i> Effective Date</label>
                                    <input type="date" name="effective_date"
                                        class="form-control @error('effective_date') is-invalid @enderror"
                                        value="{{ old('effective_date', $salary->effective_date?->format('Y-m-d')) }}"
                                        required>
                                    @error('effective_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- Section 2: Salary Fields --}}
                        <div class="form-section">
                            <div class="form-section-label">Data Salary</div>
                            <div class="field-grid">

                                @php $isDW = strtoupper($salary->employee->status_employee ?? '') === 'DW'; @endphp

                                {{-- PKWT / OJT --}}
                                @if (!$isDW)
                                    <div class="field-group">
                                        <label><i class="fas fa-money-bill"></i> Basic Salary</label>
                                        <input type="text" name="basic_salary"
                                            class="form-control currency-format @error('basic_salary') is-invalid @enderror"
                                            value="{{ old('basic_salary', $salary->basic_salary) }}" min="0">
                                        @error('basic_salary')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="field-group">
                                        <label><i class="fas fa-briefcase"></i> Position Allowance</label>
                                        <input type="text" name="position_allowance"
                                            class="form-control currency-format @error('position_allowance') is-invalid @enderror"
                                            value="{{ old('position_allowance', $salary->position_allowance) }}"
                                            min="0">
                                        @error('position_allowance')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                {{-- DW --}}
                                @if ($isDW)
                                    <div class="field-group">
                                        <label><i class="fas fa-calendar-day"></i> Daily Rate</label>
                                        <input type="text" name="daily_rate"
                                            class="form-control currency-format @error('daily_rate') is-invalid @enderror"
                                            value="{{ old('daily_rate', $salary->daily_rate) }}" min="0">
                                        @error('daily_rate')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                {{-- Semua status --}}
                                <div class="field-group">
                                    <label><i class="fas fa-briefcase"></i>Meal Allowance</label>
                                    <input type="text" name="meal_allowance"
                                        class="form-control currency-format @error('meal_allowance') is-invalid @enderror"
                                        value="{{ old('meal_allowance', $salary->meal_allowance) }}" min="0">
                                    @error('meal_allowance')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-briefcase"></i>House Allowance</label>
                                    <input type="text" name="house_allowance"
                                        class="form-control currency-format @error('house_allowance') is-invalid @enderror"
                                        value="{{ old('house_allowance', $salary->house_allowance) }}" min="0">
                                    @error('house_allowance')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-briefcase"></i>Transport Allowance</label>
                                    <input type="text" name="transport_allowance"
                                        class="form-control currency-format @error('transport_allowance') is-invalid @enderror"
                                        value="{{ old('transport_allowance', $salary->transport_allowance) }}"
                                        min="0">
                                    @error('transport_allowance')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-briefcase"></i>BPJS Ketenagakerjaan</label>
                                    <input type="text" name="bpjs_ketenagakerjaan"
                                        class="form-control currency-format @error('bpjs_ketenagakerjaan') is-invalid @enderror"
                                        value="{{ old('bpjs_ketenagakerjaan', $salary->bpjs_ketenagakerjaan) }}"
                                        min="0">
                                    @error('bpjs_ketenagakerjaan')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-briefcase"></i>BPJS Kesehatan</label>
                                    <input type="text" name="bpjs_kesehatan"
                                        class="form-control currency-format @error('bpjs_kesehatan') is-invalid @enderror"
                                        value="{{ old('bpjs_kesehatan', $salary->bpjs_kesehatan) }}" min="0">
                                    @error('bpjs_kesehatan')
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
            <div class="request-text">
                <h3>Information</h3>
                <p>
                    - jika employee itu tidak mempunyai meal, house, transport allowance tetap di nolkan ya, berlaku juga
                    untuk BPJS.<br>
                </p>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#1d4ed8',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#dc2626'
                });
            @endif
        });
    </script>
    {{-- tanda baca --}}
    <script>
        document.querySelectorAll('.currency-format').forEach(function(input) {

            // Format saat halaman pertama kali dibuka
            let value = input.value;

            if (value && !isNaN(value)) {
                input.value = Number(value).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Format saat user mengetik
            input.addEventListener('input', function(e) {

                let value = e.target.value.replace(/[^\d]/g, '');

                if (!value) {
                    e.target.value = '';
                    return;
                }

                value = (parseInt(value, 10) / 100).toFixed(2);

                e.target.value = new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(value);
            });

        });

        // Sebelum submit form, ubah ke format database
        document.querySelector('form').addEventListener('submit', function() {

            document.querySelectorAll('.currency-format').forEach(function(input) {

                let value = input.value;

                if (value) {
                    value = value
                        .replace(/\./g, '')
                        .replace(',', '.');

                    input.value = value;
                }
            });

        });
    </script>
@endpush
