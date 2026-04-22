@extends('layouts.app')
@section('title', 'Show Employee')

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
            box-shadow: 0 1px 3px rgba(0,0,0,.07);
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
            box-shadow: 0 0 0 3px rgba(59,130,246,.1);
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

        .alert-danger-custom ul { margin: 0; padding-left: 1rem; }

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

        .btn-back:hover { background: #f8fafc; color: #1e293b; }

        .btn-save {
            background: #1d4ed8;
            border: none;
            color: #fff;
        }

        .btn-save:hover { background: #1e40af; color: #fff; }

        /* ─── Responsive ─────────────────────────────────────── */
        @media (max-width: 576px) {
            .form-body { padding: 1rem; }
            .emp-form-footer { padding: .75rem 1rem; }
            .field-grid { grid-template-columns: 1fr; }
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
                    <a href="{{ route('pages.Employee') }}" style="color:#64748b;text-decoration:none">Employees</a> /
                    <span style="color:#1e293b">Show</span>
                </div>
                <h1>Show employee</h1>
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
                    <span class="emp-form-header-title">Show employee data</span>
                    <div class="emp-name-pill">
                        <div class="emp-name-pill-avatar">
                            {{ collect(explode(' ', $employee->employee->employee_name ?? 'U'))
                                ->take(2)->map(fn($w) => strtoupper($w[0]))->implode('') }}
                        </div>
                        <span>{{ $employee->employee->employee_name ?? 'Employee' }}</span>
                    </div>
                </div>

              

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
                                    <label><i class="fas fa-user"></i> Employee name</label>
                                    <input type="text" name="employee_name" id="employee_name"
                                        class="form-control @error('employee_name') is-invalid @enderror"
                                        value="{{ old('employee_name', $employee->employee->employee_name) }}"
                                        placeholder="Employee name" disabled>
                                    @error('employee_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-id-card"></i> NIK</label>
                                    <input type="text" name="nik" id="nik"
                                        class="form-control @error('nik') is-invalid @enderror"
                                        value="{{ old('nik', $employee->Employee->nik ?? '') }}"
                                        placeholder="NIK" maxlength="30" disabled>
                                    @error('nik')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-mosque"></i> Religion</label>
                                    <select name="religion" class="form-control @error('religion') is-invalid @enderror" disabled>
                                        <option value="">-- Choose religion --</option>
                                        @foreach ($religion as $value)
                                            <option value="{{ $value }}"
                                                {{ old('religion', $employee->Employee->religion ?? '') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('religion')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="field-group">
                                    <label><i class="fas fa-venus-mars"></i> Gender</label>
                                    <select name="gender" class="form-control @error('gender') is-invalid @enderror" disabled>
                                        <option value="">-- Choose gender --</option>
                                        @foreach ($gender as $value)
                                            <option value="{{ $value }}"
                                                {{ old('gender', $employee->Employee->gender ?? '') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('gender')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-map-marker-alt"></i> Place of birth</label>
                                    <input type="text" name="place_of_birth"
                                        class="form-control @error('place_of_birth') is-invalid @enderror"
                                        value="{{ old('place_of_birth', $employee->Employee->place_of_birth ?? '') }}"
                                        placeholder="Place of birth" disabled>
                                    @error('place_of_birth')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-calendar"></i> Date of birth</label>
                                    <input type="date" name="date_of_birth"
                                        class="form-control @error('date_of_birth') is-invalid @enderror"
                                        value="{{ old('date_of_birth', $employee->Employee->date_of_birth ?? '') }}"
                                        disabled>
                                    @error('date_of_birth')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-user"></i> Biological mother name</label>
                                    <input type="text" name="biological_mother_name"
                                        class="form-control @error('biological_mother_name') is-invalid @enderror"
                                        value="{{ old('biological_mother_name', $employee->Employee->biological_mother_name ?? '') }}"
                                        placeholder="Biological mother name" disabled>
                                    @error('biological_mother_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-ring"></i> Marital status</label>
                                    <select name="marriage" class="form-control @error('marriage') is-invalid @enderror" disabled>
                                        <option value="">-- Choose marital status --</option>
                                        @foreach ($marriage as $value)
                                            <option value="{{ $value }}"
                                                {{ old('marriage', $employee->Employee->marriage ?? '') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('marriage')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-baby"></i> Child</label>
                                    <select name="child" class="form-control @error('child') is-invalid @enderror" disabled>
                                        <option value="">-- Number of children --</option>
                                        @foreach ($child as $value)
                                            <option value="{{ $value }}"
                                                {{ old('child', $employee->Employee->child ?? '') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('child')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-phone-volume"></i> Emergency contact</label>
                                    <input type="text" name="emergency_contact_name"
                                        class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                        value="{{ old('emergency_contact_name', $employee->Employee->emergency_contact_name ?? '') }}"
                                        placeholder="e.g. (Mother) 081234567890" disabled>
                                    @error('emergency_contact_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-home"></i> Current address</label>
                                    <input type="text" name="current_address"
                                        class="form-control @error('current_address') is-invalid @enderror"
                                        value="{{ old('current_address', $employee->Employee->current_address ?? '') }}"
                                        placeholder="Current address" disabled>
                                    @error('current_address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-id-card"></i> ID card address</label>
                                    <input type="text" name="id_card_address"
                                        class="form-control @error('id_card_address') is-invalid @enderror"
                                        value="{{ old('id_card_address', $employee->Employee->id_card_address ?? '') }}"
                                        placeholder="ID card address" disabled>
                                    @error('id_card_address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-graduation-cap"></i> Last education</label>
                                    <select name="last_education" class="form-control @error('last_education') is-invalid @enderror" disabled>
                                        <option value="">-- Choose education --</option>
                                        @foreach ($last_education as $value)
                                            <option value="{{ $value }}"
                                                {{ old('last_education', $employee->Employee->last_education ?? '') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('last_education')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-university"></i> Institution</label>
                                    <input type="text" name="institution"
                                        class="form-control @error('institution') is-invalid @enderror"
                                        value="{{ old('institution', $employee->Employee->institution ?? '') }}"
                                        placeholder="Institution" disabled>
                                    @error('institution')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- ── Section 2: Employment ── --}}
                        <div class="form-section">
                            <div class="form-section-label">Employment</div>
                            <div class="field-grid">

                                <div class="field-group">
                                    <label><i class="fas fa-briefcase"></i> Position</label>
                                    <select name="position_id" id="position_id"
                                        class="form-control select2 @error('position_id') is-invalid @enderror" disabled>
                                        <option value="">-- Choose position --</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}"
                                                {{ old('position_id', $employee->Employee->position_id ?? '') == $position->id ? 'selected' : '' }}>
                                                {{ $position->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('position_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-store"></i> Location</label>
                                    <select name="store_id" id="store_id"
                                        class="form-control select2 @error('store_id') is-invalid @enderror" disabled>
                                        <option value="">-- Choose location --</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}"
                                                {{ old('store_id', $employee->Employee->store_id ?? '') == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('store_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-circle-dot"></i> Status employee</label>
                                    <select name="status_employee" class="form-control @error('status_employee') is-invalid @enderror" disabled>
                                        <option value="">-- Choose status --</option>
                                        @foreach ($status_employee as $value)
                                            <option value="{{ $value }}"
                                                {{ old('status_employee', $employee->Employee->status_employee ?? '') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status_employee')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-calendar-check"></i> Join date</label>
                                    <input type="date" name="join_date"
                                        class="form-control @error('join_date') is-invalid @enderror"
                                        value="{{ $employee->Employee->join_date ? \Carbon\Carbon::parse($employee->Employee->join_date)->format('Y-m-d') : '' }}">
                                    @error('join_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-toggle-on"></i> Status</label>
                                    <select id="status" name="status"
                                        class="form-control select2 @error('status') is-invalid @enderror" disabled>
                                        <option value="">-- Choose status --</option>
                                        @foreach ($status as $value)
                                            <option value="{{ $value }}"
                                                {{ old('status', $employee->Employee->status ?? '') == $value ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-layer-group"></i> Grading</label>
                                    <select name="grading_id" id="grading_id"
                                        class="form-control select2 @error('grading_id') is-invalid @enderror" disabled>
                                        <option value="">-- Choose grading --</option>
                                        @foreach ($gradings as $grading)
                                            <option value="{{ $grading->id }}"
                                                {{ old('grading_id', $employee->Employee->grading_id ?? '') == $grading->id ? 'selected' : '' }}>
                                                {{ $grading->grading_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('grading_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-users"></i> Group class</label>
                                    <select name="group_id" id="group_id"
                                        class="form-control select2 @error('group_id') is-invalid @enderror"disabled>
                                        <option value="">-- Choose group --</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}"
                                                {{ old('group_id', $employee->Employee->group_id ?? '') == $group->id ? 'selected' : '' }}>
                                                {{ $group->group_name }} — {{ $group->remark }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('group_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-sitemap"></i> Structure</label>
                                    <select name="structure_id"
                                        class="form-control select2 @error('structure_id') is-invalid @enderror" disabled>
                                        <option value="">-- Choose structure --</option>
                                        @foreach ($structures as $structure)
                                            <option value="{{ $structure->id }}"
                                                {{ old('structure_id', $employee->Employee?->structure_id) == $structure->id ? 'selected' : '' }}>
                                                {{ $structure->submissionposition->positionRelation->name ?? '-' }}
                                                — {{ $structure->submissionposition->company->name ?? '-' }}
                                                — {{ $structure->submissionposition->department->department_name ?? '-' }}
                                                — {{ $structure->submissionposition->store->name ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('structure_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-fingerprint"></i> Pin fingerspot</label>
                                    <input type="text" name="pin" id="pin"
                                        class="form-control @error('pin') is-invalid @enderror"
                                        value="{{ old('pin', $employee->Employee->pin ?? '') }}"
                                        placeholder="Pin fingerspot"
                                        style="font-family:monospace" disabled>
                                    @error('pin')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>

                            {{-- Conditional: end date + notes (shown when status = Resign / On Leave) --}}
                            <div class="conditional-section" id="conditional-status-fields">
                                <div class="conditional-section-label">
                                    <i class="fas fa-triangle-exclamation"></i>
                                    disabled when status is "Resign" or "On Leave"
                                </div>
                                <div class="field-grid">
                                    <div class="field-group">
                                        <label><i class="fas fa-calendar-xmark"></i> End date</label>
                                        <input type="date" id="end_date" name="end_date"
                                            class="form-control @error('end_date') is-invalid @enderror"
                                            value="{{ $employee->Employee->end_date ? \Carbon\Carbon::parse($employee->Employee->end_date)->format('Y-m-d') : '' }}"disabled>
                                        @error('end_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="field-group">
                                        <label><i class="fas fa-comment-dots"></i> Reason / notes</label>
                                        <input type="text" id="notes" name="notes"
                                            class="form-control @error('notes') is-invalid @enderror"
                                            value="{{ old('notes', $employee->Employee->notes ?? '') }}"
                                            placeholder="Reason for status change" disabled>
                                        @error('notes')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 3: Documents & identity numbers ── --}}
                        <div class="form-section">
                            <div class="form-section-label">Documents & identity numbers</div>
                            <div class="field-grid">

                                <div class="field-group">
                                    <label><i class="fas fa-file-invoice"></i> NPWP</label>
                                    <input type="text" name="npwp"
                                        class="form-control @error('npwp') is-invalid @enderror"
                                        value="{{ old('npwp', $employee->Employee->npwp ?? '') }}"
                                        placeholder="NPWP" disabled>
                                    @error('npwp')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-heart-pulse"></i> BPJS Kesehatan</label>
                                    <input type="text" name="bpjs_kes"
                                        class="form-control @error('bpjs_kes') is-invalid @enderror"
                                        value="{{ old('bpjs_kes', $employee->Employee->bpjs_kes ?? '') }}"
                                        placeholder="BPJS Kesehatan" disabled>
                                    @error('bpjs_kes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-shield-halved"></i> BPJS Ketenagakerjaan</label>
                                    <input type="text" name="bpjs_ket"
                                        class="form-control @error('bpjs_ket') is-invalid @enderror"
                                        value="{{ old('bpjs_ket', $employee->Employee->bpjs_ket ?? '') }}"
                                        placeholder="BPJS Ketenagakerjaan" disabled>
                                    @error('bpjs_ket')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- ── Section 4: Bank & contact ── --}}
                        <div class="form-section">
                            <div class="form-section-label">Bank & contact</div>
                            <div class="field-grid">

                                <div class="field-group">
                                    <label><i class="fas fa-building-columns"></i> Bank name</label>
                                    <select name="banks_id" class="form-control @error('banks_id') is-invalid @enderror" disabled>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank->id }}"
                                                {{ $employee->Employee->banks_id == $bank->id ? 'selected' : '' }}>
                                                {{ $bank->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('banks_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-credit-card"></i> Bank account number</label>
                                    <input type="text" name="bank_account_number"
                                        class="form-control @error('bank_account_number') is-invalid @enderror"
                                        value="{{ old('bank_account_number', $employee->Employee->bank_account_number ?? '') }}"
                                        placeholder="Bank account number"
                                        style="font-family:monospace" disabled>
                                    @error('bank_account_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-envelope"></i> Personal email</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $employee->Employee->email ?? '') }}"
                                        placeholder="Personal email" disabled>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-envelope-circle-check"></i> Company email</label>
                                    <input type="email" name="company_email"
                                        class="form-control @error('company_email') is-invalid @enderror"
                                        value="{{ old('company_email', $employee->Employee->company_email ?? '') }}"
                                        placeholder="Company email" disabled>
                                    @error('company_email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field-group">
                                    <label><i class="fas fa-phone"></i> Phone number</label>
                                    <input type="tel" name="telp_number"
                                        class="form-control @error('telp_number') is-invalid @enderror"
                                        value="{{ old('telp_number', $employee->Employee->telp_number ?? '') }}"
                                        placeholder="Phone number" disabled>
                                    @error('telp_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- ── Section 5: Profile photo ── --}}
                        <div class="form-section">
                            <div class="form-section-label">Profile photo</div>
                            <div class="photo-upload-wrap">
                                <div class="photo-thumb" onclick="showImageSwal(document.getElementById('preview-image').src)">
                                    @if (!empty($employee->Employee->photos))
                                        <img id="preview-image"
                                             src="{{ asset('storage/' . $employee->Employee->photos) }}"
                                             alt="Preview">
                                    @else
                                        <img id="preview-image" src="https://via.placeholder.com/60"
                                             alt="No photo" style="display:none">
                                        <i class="fas fa-user" id="photo-placeholder"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="photo-upload-hint">
                                        JPG, PNG, or WEBP — max 2 MB. Click image to preview full size.
                                    </div>
                                    <label for="photos" class="photo-upload-btn">
                                        <i class="fas fa-arrow-up-from-bracket" style="font-size:.7rem"></i>
                                        Upload new photo
                                    </label>
                                    <input type="file" name="photos" id="photos"
                                        class="d-none @error('photos') is-invalid @enderror"
                                        accept="image/*" onchange="previewPhoto(event)"disabled>
                                    @error('photos')
                                        <div class="invalid-feedback d-block mt-1" style="font-size:.7rem">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Note --}}
                        <div class="note-box">
                            <i class="fas fa-circle-info"></i>
                            <div>
                                Leave <strong>Status</strong> unchanged if the employee is still active.
                                End date and reason fields appear automatically when status is set to
                                <em>Resign</em> or <em>On Leave</em>.
                                Please double-check for duplicate records before saving.
                            </div>
                        </div>

                    </div>{{-- /.form-body --}}

                    <div class="emp-form-footer">
                        <a href="{{ route('pages.Employee') }}" class="btn btn-back">
                            <i class="fas fa-arrow-left"></i> Back to employees
                        </a>
                     
                    </div>

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
    function previewPhoto(event) {
        const file = event.target.files[0];
        if (!file) return;
        const url  = URL.createObjectURL(file);
        const img  = document.getElementById('preview-image');
        const ph   = document.getElementById('photo-placeholder');
        img.src    = url;
        img.style.display = 'block';
        if (ph) ph.style.display = 'none';
    }

    function showImageSwal(src) {
        if (!src || src.includes('placeholder')) return;
        Swal.fire({ imageUrl: src, imageAlt: 'Employee photo', showConfirmButton: false });
    }

    $(function () {
        /* ── Select2 ── */
        $('.select2').select2({ width: '100%' });

        /* ── Conditional status fields ── */
        const INACTIVE_STATUSES = ['Resign', 'On Leave'];

        function toggleConditionalFields() {
            const val = $('#status').val();
            const $section = $('#conditional-status-fields');
            if (INACTIVE_STATUSES.includes(val)) {
                $section.addClass('visible');
            } else {
                $section.removeClass('visible');
                $('#notes').val('');
                $('#end_date').val('');
            }
        }

        toggleConditionalFields();
        $('#status').on('change', toggleConditionalFields);

        /* ── Session flash ── */
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
@endpush
