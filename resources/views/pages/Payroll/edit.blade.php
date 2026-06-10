@extends('layouts.app')
@section('title', 'Edit Payroll')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .section-header h1 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }

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

        .readonly-field {
            background: #f8fafc;
            color: #64748b;
            cursor: not-allowed;
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

        /* Info box */
        .info-box {
            background: #f8fafc;
            border-radius: .5rem;
            padding: .75rem 1rem;
            font-size: .8rem;
        }

        .info-box-row {
            display: flex;
            justify-content: space-between;
            padding: .3rem 0;
            color: #64748b;
        }

        .info-box-row span:last-child {
            font-weight: 600;
            color: #1e293b;
        }

        /* Prorate toggle */
        .prorate-section {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: .5rem;
            padding: 1rem;
        }

        .prorate-fields {
            margin-top: .75rem;
            display: none;
        }

        .prorate-fields.show {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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
                        <a href="{{ route('payrollperiod.index') }}" style="color:#64748b;text-decoration:none">Payroll
                            Periods</a> /
                        <a href="{{ route('payroll.index', $payroll->payroll_period_id) }}"
                            style="color:#64748b;text-decoration:none">{{ $payroll->period->period_label }}</a> /
                        <a href="{{ route('payroll.show', $payroll->id) }}"
                            style="color:#64748b;text-decoration:none">Detail</a> /
                        <span style="color:#1e293b">Edit</span>
                    </div>
                    <h1>Edit Payroll</h1>
                </div>
            </div>

            <div class="emp-form-card">
                <div class="emp-form-header">
                    <div class="emp-form-header-icon"><i class="fas fa-edit"></i></div>
                    <span class="emp-form-header-title">Koreksi data payroll</span>
                    <div class="emp-name-pill">
                        <div class="emp-name-pill-avatar">
                            {{ collect(explode(' ', $payroll->employee->employee_name ?? 'U'))->take(2)->map(fn($w) => strtoupper($w[0]))->implode('') }}
                        </div>
                        <span>{{ $payroll->employee->employee_name ?? '-' }}</span>
                    </div>
                </div>

                <form action="{{ route('payroll.update', $payroll->id) }}" method="POST">
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

                        {{-- Section 1: Info readonly --}}
                        <div class="form-section">
                            <div class="form-section-label">Info Payroll</div>
                            <div class="info-box">
                                <div class="info-box-row">
                                    <span>Periode</span>
                                    <span>{{ $payroll->period->period_label }}</span>
                                </div>
                                <div class="info-box-row">
                                    <span>Range</span>
                                    <span>{{ $payroll->period_start->format('d/m/Y') }} —
                                        {{ $payroll->period_end->format('d/m/Y') }}</span>
                                </div>
                                <div class="info-box-row">
                                    <span>Gross Salary</span>
                                    <span>Rp {{ number_format($payroll->gross_salary, 0, ',', '.') }}</span>
                                </div>
                                <div class="info-box-row">
                                    <span>Working Days</span>
                                    <span>{{ $payroll->working_days }} hari</span>
                                </div>
                                <div class="info-box-row">
                                    <span>Attendance Days</span>
                                    <span>{{ $payroll->attendance_days }} hari</span>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Income Tambahan --}}
                        <div class="form-section">
                            <div class="form-section-label">Income Tambahan</div>
                            <div class="field-grid">
                                <div class="field-group">
                                    <label><i class="fas fa-clock"></i> Overtime</label>
                                    <input type="number" name="overtime_amount"
                                        class="form-control @error('overtime_amount') is-invalid @enderror"
                                        value="{{ old('overtime_amount', $payroll->overtime_amount) }}" min="0"
                                        placeholder="0">
                                    @error('overtime_amount')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-receipt"></i> Reimburse</label>
                                    <input type="number" name="reimburse_amount"
                                        class="form-control @error('reimburse_amount') is-invalid @enderror"
                                        value="{{ old('reimburse_amount', $payroll->reimburse_amount) }}" min="0"
                                        placeholder="0">
                                    @error('reimburse_amount')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Potongan Manual --}}
                        <div class="form-section">
                            <div class="form-section-label">Potongan Manual</div>
                            <div class="field-grid">
                                <div class="field-group">
                                    <label><i class="fas fa-gavel"></i> Punishment</label>
                                    <input type="number" name="punishment_amount"
                                        class="form-control @error('punishment_amount') is-invalid @enderror"
                                        value="{{ old('punishment_amount', $payroll->details->where('component.component_name', 'PUNISHMENT')->first()?->amount ?? 0) }}"
                                        min="0" placeholder="0">
                                    @error('punishment_amount')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-hand-holding-dollar"></i> Kasbon / Debt</label>
                                    <input type="number" name="kasbon_amount"
                                        class="form-control @error('kasbon_amount') is-invalid @enderror"
                                        value="{{ old('kasbon_amount', $payroll->details->where('component.component_name', 'DEBT')->first()?->amount ?? 0) }}"
                                        min="0" placeholder="0">
                                    @error('kasbon_amount')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: Prorate --}}
                        @if (strtoupper($payroll->employee->status_employee) !== 'DW')
                            <div class="form-section">
                                <div class="form-section-label">Prorate</div>
                                <div class="prorate-section">
                                    <div class="field-group">
                                        <label>
                                            <input type="hidden" name="is_prorate" value="0">
                                            <input type="checkbox" name="is_prorate" id="is_prorate" value="1"
                                                {{ old('is_prorate', $payroll->is_prorate) ? 'checked' : '' }}>
                                            <span style="font-size:.82rem;font-weight:600;color:#c2410c">Aktifkan
                                                Prorate</span>
                                        </label>
                                        <small style="color:#92400e;font-size:.75rem">
                                            Centang jika employee join atau resign di tengah periode ini.
                                        </small>
                                    </div>

                                    <div class="prorate-fields {{ old('is_prorate', $payroll->is_prorate) ? 'show' : '' }}"
                                        id="prorate-fields">
                                        <div class="field-group">
                                            <label><i class="fas fa-calendar-day"></i> Prorate Days</label>
                                            <input type="number" name="prorate_days" id="prorate_days"
                                                class="form-control @error('prorate_days') is-invalid @enderror"
                                                value="{{ old('prorate_days', $payroll->prorate_days) }}" min="0"
                                                max="{{ $payroll->working_days }}" placeholder="Jumlah hari kerja aktual">
                                            <small style="color:#92400e;font-size:.72rem">
                                                Maks: {{ $payroll->working_days }} hari (working days periode ini)
                                            </small>
                                            @error('prorate_days')
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="field-group">
                                            <label><i class="fas fa-percent"></i> Prorate Ratio (preview)</label>
                                            <input type="text" id="prorate_ratio_preview"
                                                class="form-control readonly-field" readonly
                                                value="{{ $payroll->is_prorate ? round($payroll->prorate_ratio * 100, 2) . '%' : '-' }}">
                                            <small style="color:#92400e;font-size:.72rem">
                                                Prorate Days / Working Days
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Note --}}
                        <div class="form-section">
                            <div class="form-section-label">Catatan</div>
                            <div class="field-group">
                                <label><i class="fas fa-note-sticky"></i> Note</label>
                                <textarea name="note" class="form-control" rows="3" style="height:auto" placeholder="Catatan opsional...">{{ old('note', $payroll->note) }}</textarea>
                            </div>
                        </div>

                    </div>

                    <div class="emp-form-footer">
                        <a href="{{ route('payroll.show', $payroll->id) }}" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Kembali
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            // ── Toggle prorate fields ──
            $('#is_prorate').on('change', function() {
                if (this.checked) {
                    $('#prorate-fields').addClass('show');
                } else {
                    $('#prorate-fields').removeClass('show');
                    $('#prorate_days').val('');
                    $('#prorate_ratio_preview').val('-');
                }
            });

            // ── Preview prorate ratio ──
            $('#prorate_days').on('input', function() {
                const days = parseInt($(this).val()) || 0;
                const workingDays = {{ $payroll->working_days }};

                if (days > 0 && workingDays > 0) {
                    const ratio = (days / workingDays * 100).toFixed(2);
                    $('#prorate_ratio_preview').val(ratio + '%');
                } else {
                    $('#prorate_ratio_preview').val('-');
                }
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#1d4ed8',
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#dc2626',
                });
            @endif
        });
    </script>
@endpush
