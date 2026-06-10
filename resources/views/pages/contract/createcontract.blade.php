@extends('layouts.app')
@section('title', 'Create Contracts')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* ─── Page header ────────────────────────────────────── */
        .section-header h1 {
            font-size: 1.35rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        /* ─── Card shell ─────────────────────────────────────── */
        .emp-form-card {
            border: none;
            border-radius: .625rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .07);
            background: #fff;
            overflow: hidden;
        }

        /* ─── Card header ────────────────────────────────────── */
        .emp-form-header {
            padding: .875rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
            display: flex;
            align-items: center;
            gap: .625rem;
        }

        .emp-form-header-icon {
            width: 30px;
            height: 30px;
            background: #eff6ff;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            color: #1d4ed8;
            flex-shrink: 0;
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
            gap: 6px;
            background: #eff6ff;
            border-radius: 20px;
            padding: .2rem .625rem .2rem .25rem;
        }

        .emp-name-pill-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #1d4ed8;
            color: #fff;
            font-size: .6rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .emp-name-pill span {
            font-size: .75rem;
            font-weight: 600;
            color: #1e40af;
        }

        /* ─── Section groups ─────────────────────────────────── */
        .form-body {
            padding: 1.5rem;
        }

        .form-section {
            margin-bottom: 1.5rem;
        }

        .form-section-label {
            font-size: .67rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #94a3b8;
            margin-bottom: .875rem;
            display: flex;
            align-items: center;
            gap: .625rem;
        }

        .form-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f1f5f9;
        }

        /* ─── Field grid ─────────────────────────────────────── */
        .field-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: .3rem;
        }

        .field-group label {
            font-size: .72rem;
            font-weight: 600;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
        }

        .field-group label i {
            font-size: .67rem;
            color: #94a3b8;
            width: 12px;
            text-align: center;
        }

        .field-group .form-control {
            height: 38px;
            font-size: .825rem;
            border: 1px solid #e2e8f0;
            border-radius: .5rem;
            color: #1e293b;
            padding: 0 .75rem;
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }

        .field-group .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
            outline: none;
        }

        .field-group .form-control.is-invalid {
            border-color: #ef4444;
        }

        .field-group select.form-control {
            height: 38px;
            cursor: pointer;
        }

        .field-group .invalid-feedback {
            font-size: .7rem;
            color: #dc2626;
            margin-top: .2rem;
        }

        /* Select2 override */
        .field-group .select2-container .select2-selection--single {
            height: 38px !important;
            border-color: #e2e8f0 !important;
            border-radius: .5rem !important;
        }

        .field-group .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            font-size: .825rem;
            color: #1e293b;
        }

        .field-group .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        /* ─── Conditional status fields ──────────────────────── */
        .conditional-section {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: .5rem;
            padding: .875rem 1rem;
            margin-top: .875rem;
            display: none;
        }

        .conditional-section.visible {
            display: block;
        }

        .conditional-section-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #92400e;
            margin-bottom: .625rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .conditional-section-label i {
            font-size: .65rem;
        }

        /* ─── Photo upload ───────────────────────────────────── */
        .photo-upload-wrap {
            display: flex;
            align-items: center;
            gap: .875rem;
        }

        .photo-thumb {
            width: 60px;
            height: 60px;
            border-radius: .5rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
        }

        .photo-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-thumb i {
            color: #cbd5e1;
            font-size: 1.5rem;
        }

        .photo-upload-hint {
            font-size: .7rem;
            color: #94a3b8;
            margin-bottom: 5px;
        }

        .photo-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 32px;
            padding: 0 12px;
            border: 1px dashed #cbd5e1;
            border-radius: .5rem;
            background: #f8fafc;
            color: #64748b;
            font-size: .775rem;
            font-weight: 500;
            cursor: pointer;
            transition: all .15s;
        }

        .photo-upload-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #eff6ff;
        }

        /* ─── Alerts ─────────────────────────────────────────── */
        .alert-danger-custom {
            padding: .75rem 1rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: .5rem;
            font-size: .8rem;
            color: #991b1b;
            margin-bottom: 1.25rem;
        }

        .alert-danger-custom ul {
            margin: 0;
            padding-left: 1rem;
        }

        .alert-success-custom {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: .625rem 1rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: .5rem;
            font-size: .8rem;
            color: #166534;
            margin-bottom: 1.25rem;
        }

        /* ─── Note box ───────────────────────────────────────── */
        .note-box {
            display: flex;
            align-items: flex-start;
            gap: .625rem;
            padding: .75rem 1rem;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: .5rem;
            font-size: .78rem;
            color: #64748b;
            margin-top: 1.25rem;
        }

        .note-box i {
            font-size: .75rem;
            color: #94a3b8;
            margin-top: .15rem;
            flex-shrink: 0;
        }

        /* ─── Card footer ────────────────────────────────────── */
        .emp-form-footer {
            padding: .875rem 1.25rem;
            border-top: 1px solid #f1f5f9;
            background: #fafafa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .emp-form-footer .btn {
            height: 38px;
            font-size: .825rem;
            font-weight: 500;
            padding: 0 1.125rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: .5rem;
        }

        .btn-back {
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #475569;
        }

        .btn-back:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .btn-save {
            background: #1d4ed8;
            border: none;
            color: #fff;
        }

        .btn-save:hover {
            background: #1e40af;
            color: #fff;
        }

        /* ─── Responsive ─────────────────────────────────────── */
        @media (max-width: 576px) {
            .form-body {
                padding: 1rem;
            }

            .emp-form-footer {
                padding: .75rem 1rem;
            }

            .field-grid {
                grid-template-columns: 1fr;
            }
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
                        <a href="{{ route('contract') }}" style="color:#64748b;text-decoration:none">Employees</a> /
                        <span style="color:#1e293b">Create</span>
                    </div>
                    <h1>Create Contracts</h1>
                </div>
                <div class="section-header-breadcrumb" style="display:none">{{-- hidden, handled above --}}</div>
            </div>

            <div class="section-body">
                <div class="emp-form-card">

                    {{-- Card header --}}
                    <div class="emp-form-header">
                        <div class="emp-form-header-icon">
                            <i class="fas fa-user-pen"></i>
                        </div>
                        <span class="emp-form-header-title">Create Contracts</span>
                        <div class="emp-name-pill">
                            <div class="emp-name-pill-avatar"></div>
                        </div>
                    </div>
{{-- <div id="contractFormWrapper" style="{{ $isHeadHR ? 'pointer-events:none; opacity:0.5;' : '' }}"> --}}
    <div id="contractFormWrapper"
     style="{{ ($isHeadHR && $isPasswordExpired) ? 'pointer-events:none; opacity:0.5;' : '' }}">
                    <form id="employee-create" action="{{ route('storecontract') }}" method="POST">
                        @csrf
                        <div class="form-body">

                            {{-- Flash messages --}}
                            @if ($errors->any())
                                <div class="alert-danger-custom">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('success'))
                                <div class="alert-success-custom">
                                    <i class="fas fa-check-circle"></i>
                                    {{ session('success') }}
                                </div>
                            @endif

                            {{-- ── Section 1: Personal information ── --}}
                            <div class="form-section">
                                <div class="form-section-label">Personal information</div>
                                <div class="field-grid">
                                    <div class="field-group">
                                        <label><i class="fas fa-user"></i> Employee Name</label>
                                        <select name="employee_id" id="employee_id" class="select2 form-control" required>
                                            <option value="">Choose Employee</option>
                                            {{-- @foreach ($employees as $id => $employee)
                                                <option value="{{ $id }}"
                                                    {{ old('employee_id') == $id ? 'selected' : '' }}>
                                                    {{ $employee }} - {{$employee->structuresnew->submissionposition->positionRelation->name}}
                                                </option>
                                            @endforeach --}}
                                            @foreach ($employees as $employee)
    <option value="{{ $employee->id }}"
        {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
        
        {{ $employee->employee_name }} 
        - {{ $employee->structuresnew?->submissionposition?->positionRelation?->name ?? 'No Structure' }}
        
    </option>
@endforeach
                                        </select>
                                        @error('employee_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="field-group">
                                        <label><i class="fas fa-map-marker-alt"></i> Contract Start</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control">
                                        @error('start_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="field-group">
                                        <label><i class="fas fa-map-marker-alt"></i> Contract End</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control">
                                        @error('end_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="field-group">
                                        <label><i class="fas fa-venus-mars"></i> Contract Type</label>
                                        <select name="contract_type" id="contract_type" class="select2 form-control"
                                            required>
                                            <option value="">Choose Contract Type</option>
                                            @foreach ($typeOptions as $value)
                                                <option value="{{ $value }}"
                                                    {{ old('contract_type') == $value ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('contract_type')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                   {{-- Basic Salary --}}
<div class="field-group">
    <label><i class="fas fa-money-bill"></i> Basic Salary</label>
    <input type="text"
           id="basic_salary_display"
           class="form-control @error('basic_salary') is-invalid @enderror"
           value="{{ old('basic_salary') ? number_format(old('basic_salary'), 2, ',', '.') : (isset($contract) ? number_format($contract->basic_salary, 2, ',', '.') : '') }}"
           placeholder="0,00">
    <input type="hidden" name="basic_salary" id="basic_salary"
           value="{{ old('basic_salary', $contract->basic_salary ?? '') }}">
    @error('basic_salary')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

{{-- Positional Allowance --}}
<div class="field-group">
    <label><i class="fas fa-money-bill"></i> Positional Allowance</label>
    <input type="text"
           id="positional_allowance_display"
           class="form-control @error('positional_allowance') is-invalid @enderror"
           value="{{ old('positional_allowance') ? number_format(old('positional_allowance'), 2, ',', '.') : (isset($contract) ? number_format($contract->positional_allowance, 2, ',', '.') : '') }}"
           placeholder="0,00">
    <input type="hidden" name="positional_allowance" id="positional_allowance"
           value="{{ old('positional_allowance', $contract->positional_allowance ?? '') }}">
    @error('positional_allowance')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

{{-- Daily Rate --}}
<div class="field-group">
    <label><i class="fas fa-money-bill"></i> Daily Rate</label>
    <input type="text"
           id="daily_rate_display"
           class="form-control @error('daily_rate') is-invalid @enderror"
           value="{{ old('daily_rate') ? number_format(old('daily_rate'), 2, ',', '.') : (isset($contract) ? number_format($contract->daily_rate, 2, ',', '.') : '') }}"
           placeholder="0,00">
    <input type="hidden" name="daily_rate" id="daily_rate"
           value="{{ old('daily_rate', $contract->daily_rate ?? '') }}">
    @error('daily_rate')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>


                                </div>
                            </div>{{-- /.form-section --}}

                        </div>{{-- /.form-body --}}

                        <div class="emp-form-footer">
                            <a href="{{ route('pages.Employee') }}" class="btn btn-back">
                                <i class="fas fa-arrow-left"></i> Back to employees
                            </a>
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-floppy-disk"></i> Save changes
                            </button>
                        </div>

                    </form>
                </div>

                </div>{{-- /.emp-form-card --}}
            </div>{{-- /.section-body --}}

        </section>
    </div>{{-- /.main-content --}}

    @if($isHeadHR)
<div class="modal fade" id="passwordModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Konfirmasi Password (Head HR)</h5>
      </div>
      <div class="modal-body">
        <input type="password" id="confirm_password" class="form-control">
        <small class="text-danger d-none" id="error_password">Password salah</small>
      </div>
      <div class="modal-footer">
        <button id="btnConfirmPassword" class="btn btn-primary">Konfirmasi</button>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@if($isHeadHR && $isPasswordExpired)
<script>
document.addEventListener("DOMContentLoaded", function () {

    let modal = new bootstrap.Modal(document.getElementById('passwordModal'), {
        backdrop: 'static',
        keyboard: false
    });

    modal.show();

    document.getElementById('btnConfirmPassword').addEventListener('click', function () {
        let password = document.getElementById('confirm_password').value;

        fetch("{{ route('contract.password.ajax') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ password: password })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                modal.hide();

                let wrapper = document.getElementById('contractFormWrapper');
                wrapper.style.pointerEvents = 'auto';
                wrapper.style.opacity = '1';

                // reset error
                document.getElementById('error_password').classList.add('d-none');
                document.getElementById('confirm_password').value = '';
            } else {
                document.getElementById('error_password').classList.remove('d-none');
            }
        });
    });

});
</script>
@endif
    <script>
        $(function() {
            $('.select2').select2({
                width: '100%'
            });

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
    <script>
        const employeeJoinDates = @json($employeeJoinDates);
        $('#employee_id').on('change', function() {
            const employeeId = $(this).val();
            $('#start_date').val(employeeJoinDates[employeeId] || '');
        });
    </script>
    <script>
    function setupCurrencyInput(displayId, hiddenId) {
        const display = document.getElementById(displayId);
        const hidden  = document.getElementById(hiddenId);

        display.addEventListener('focus', function () {
            let raw = hidden.value;
            this.value = raw ? parseFloat(raw).toFixed(2).replace('.', ',') : '';
        });

        display.addEventListener('blur', function () {
            let raw = this.value.replace(',', '.').replace(/[^0-9.]/g, '');

            if (raw === '' || isNaN(parseFloat(raw))) {
                this.value = '';
                hidden.value = '';
                return;
            }

            let number = parseFloat(raw).toFixed(2);
            hidden.value = number;

            let parts = number.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            this.value = parts.join(',');
        });
    }

    // Panggil untuk setiap field currency
    setupCurrencyInput('basic_salary_display', 'basic_salary');
    setupCurrencyInput('positional_allowance_display', 'positional_allowance');
    setupCurrencyInput('daily_rate_display', 'daily_rate');
</script>
    
@endpush
