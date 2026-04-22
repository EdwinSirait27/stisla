@extends('layouts.app')
@section('title', 'Create Payroll Components')

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
            gap: .75rem;
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
            /* background: #1e40af; */
            color: #00b303;
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
                        <a href="{{ route('payrollcomponents') }}" style="color:#64748b;text-decoration:none">Payroll
                            Components</a> /
                        <span style="color:#1e293b">Create</span>
                    </div>
                    <h1>Create Payroll Components</h1>
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
                        <span class="emp-form-header-title">Create Payroll Components data</span>
                        <div class="emp-name-pill">
                            <div class="emp-name-pill-avatar">
                                {{-- {{ collect(explode(' ', $employee->employee->employee_name ?? 'U'))
                                ->take(2)->map(fn($w) => strtoupper($w[0]))->implode('') }} --}}
                            </div>
                            {{-- <span>{{ $employee->employee->employee_name ?? 'Employee' }}</span> --}}
                        </div>
                    </div>

                    <form id="employee-create" action="{{ route('payrollcomponents.store') }}" method="POST">
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
                                <div class="form-section-label">Required Fields</div>
                                <div class="field-grid">
                                    <div class="field-group">
                                        <label><i class="fas fa-user"></i>Component Name</label>
                                        <input type="text" name="component_name" id="component_name"
                                            class="form-control @error('component_name') is-invalid @enderror"
                                            value="{{ old('component_name') }}" placeholder="Reamburse" required>
                                        @error('component_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="field-group">
                                        <label><i class="fas fa-id-card"></i> Type</label>
                                        <select id="type" name="type"
                                            class="select2 w-full sm:w-40 px-3 py-2 border rounded-lg text-sm" required>
                                            <option value="">Choose Type</option>
                                            @foreach ($types as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('type') == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('type')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    {{-- <div class="field-group">
    <label>
        <input type="hidden" name="is_fixed" value="0">
        <input type="checkbox" name="is_fixed" value="1" {{ old('is_fixed') ? 'checked' : '' }}>
        Fixed
    </label>

    @error('is_fixed')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div> --}}
                                    <div class="field-group">
                                        <label>
                                            <input type="hidden" name="is_fixed" value="0">
                                            <input type="checkbox" name="is_fixed" value="1"
                                                {{ old('is_fixed') ? 'checked' : '' }}>
                                            Fixed
                                        </label>

                                        <br>
                                        <small class="text-muted">
                                          Check if this component has a fixed value every period.
                                        </small>

                                        @error('is_fixed')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                </div>
                            </div>

                            {{-- ── Section 3: Documents & identity numbers ── --}}


                            {{-- ── Section 4: Bank & contact ── --}}


                            {{-- ── Section 5: Profile photo ── --}}



                        </div>{{-- /.form-body --}}

                        <div class="emp-form-footer">
                            <a href="{{ route('payrollcomponents') }}" class="btn btn-back">
                                <i class="fas fa-arrow-left"></i> Back to Payroll Components
                            </a>
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-floppy-disk"></i> Create
                            </button>
                        </div>
                    </form>

                </div>{{-- /.emp-form-card --}}
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        /* ── Photo preview ── */

        $(function() {
            /* ── Select2 ── */
            $('.select2').select2({
                width: '100%'
            });
            /* ── Session flash ── */
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#1d4ed8',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Failed!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#dc2626'
                });
            @endif
        });
    </script>
@endpush
