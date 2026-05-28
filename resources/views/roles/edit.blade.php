{{-- @extends('layouts.app')
@section('title', 'Edit Role')
@push('style')
    <link rel="stylesheet" href="{{ asset('node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}">
    <style>
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 25px;
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
        }
        .card-body {
            padding: 25px;
        }
        .card-footer {
            background-color: #fff;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 15px 25px;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e4e6fc;
            box-shadow: none;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #6777ef;
            box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.1);
        }
        .form-group label {
            font-weight: 600;
            color: #34395e;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #6777ef;
            border-color: #6777ef;
            box-shadow: 0 2px 6px rgba(103, 119, 239, 0.3);
        }
        
        .btn-primary:hover {
            background-color: #5a69e0;
            border-color: #5a69e0;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(103, 119, 239, 0.4);
        }
        
        .btn-secondary {
            background-color: #cdd3f8;
            border-color: #cdd3f8;
            color: #6777ef;
            box-shadow: 0 2px 6px rgba(205, 211, 248, 0.5);
        }
        
        .btn-secondary:hover {
            background-color: #bac1f6;
            border-color: #bac1f6;
            color: #6777ef;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(205, 211, 248, 0.6);
        }
        
        /* Permission checkboxes */
        .permission-group {
            background-color: #f9fafe;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid transparent;
        }
        .permission-group:hover {
            background-color: #f2f4fd;
            border-color: #e4e6fc;
        }
        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #6777ef;
            border-color: #6777ef;
        }
        .custom-checkbox .custom-control-input:checked ~ .custom-control-label::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26l2.974 2.99L8 2.193z'/%3e%3c/svg%3e");
        }
        .custom-control-label {
            font-size: 14px;
            padding-top: 2px;
        }
        
        .custom-control-label::before {
            border-radius: 6px;
            border: 2px solid #e4e6fc;
        }

        /* Section header */
        .section-header {
            padding: 20px 0;
            margin-bottom: 20px;
        }
        
        .section-header h1 {
            font-weight: 700;
            color: #34395e;
        }
        
        .section-header-breadcrumb {
            margin-left: auto;
        }
        
        .breadcrumb-item a {
            color: #6777ef;
        }
        
        /* Animation for checkboxes */
        .custom-checkbox .custom-control-input:checked ~ .custom-control-label::before {
            animation: pulse-blue 0.5s;
        }
        
        @keyframes pulse-blue {
            0% {
                box-shadow: 0 0 0 0 rgba(103, 119, 239, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(103, 119, 239, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(103, 119, 239, 0);
            }
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Role</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></div>
                    <div class="breadcrumb-item">Edit Role</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Editing Role: {{ $role->name }}</h4>
                            </div>
                            <form id="roles-edit" action="{{ route('roles.update', $hashedId) }}" method="POST">
                          
                                @csrf
                                @method('PUT')
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Role Name</label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               name="name" 
                                               value="{{ old('name', $role->name) }}" 
                                               readonly>
                                        @error('name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                 
                                        <div class="form-group">
                                            <label>Permissions</label>
                                            @error('permissions')
                                                <div class="text-danger mb-2">{{ $message }}</div>
                                            @enderror
                                        
                                        
                                <div class="row">
                                    @foreach($permissions as $permission)
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="permission-{{ $permission->id }}" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->id }}" 
                                                       {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                    @error('permissions')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>
                                <div class="card-footer text-right">
                                    <a href="{{ route('roles.index') }}" class="btn btn-secondary mr-2">
                                        <i class="fas fa-arrow-left"></i> Cancel
                                    </a>
                                    <button id="edit-btn" type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Role
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraies -->
    <script src="{{ asset('node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Page Specific JS File -->
    <script>
        document.getElementById('edit-btn').addEventListener('click', function(e) {
            e.preventDefault(); // Mencegah pengiriman form langsung
            Swal.fire({
                title: 'Are You Sure?',
                text: "Make sure the data you entered is correct!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Assign!',
                cancelButtonText: 'Abort'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('roles-edit').submit();
                }
            });
        });
    </script>
@endpush --}}
@extends('layouts.app')
@section('title', 'Edit Role')
@push('styles')
<style>
/* ─── (CSS sama persis dengan Create Role — copy paste dari sana) ─── */
.cr-page { max-width: 860px; margin: 0 auto; padding: 24px 16px 48px; }
.cr-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .72rem; color: #94a3b8; margin-bottom: 20px; }
.cr-breadcrumb a { color: #6777ef; text-decoration: none; }
.cr-breadcrumb i { font-size: .6rem; }
.cr-title { display: flex; align-items: center; gap: 14px; margin-bottom: 24px; }
.cr-title-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #f59e0b, #ef4444); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(245,158,11,.35); }
.cr-title-text h1 { margin: 0; font-size: 1.25rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
.cr-title-text p { margin: 2px 0 0; font-size: .78rem; color: #94a3b8; }
.cr-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04); overflow: hidden; margin-bottom: 16px; }
.cr-section { padding: 20px 24px; border-bottom: 1px solid #f1f5f9; }
.cr-section:last-child { border-bottom: none; }
.cr-section-label { display: flex; align-items: center; gap: 8px; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #94a3b8; margin-bottom: 14px; }
.cr-section-label::after { content: ''; flex: 1; height: 1px; background: #f1f5f9; }

/* Role name readonly */
.cr-input-wrap { position: relative; }
.cr-input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; pointer-events: none; }
.cr-input { width: 100%; height: 46px; padding: 0 14px 0 40px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: .9rem; color: #1e293b; background: #fafbfc; transition: all .2s; outline: none; }
.cr-input[readonly] { background: #f8fafc; color: #64748b; cursor: not-allowed; border-style: dashed; }
.cr-input-hint { margin-top: 6px; font-size: .72rem; color: #94a3b8; }

/* Readonly badge */
.cr-readonly-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 20px; font-size: .68rem; font-weight: 700; color: #92400e; margin-left: 10px; }

/* Permission toolbar */
.perm-toolbar { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
.perm-search { flex: 1; min-width: 160px; height: 34px; padding: 0 12px 0 34px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .8rem; color: #1e293b; background: #fafbfc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' stroke='%2394a3b8' stroke-width='2' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") no-repeat 11px center; outline: none; transition: all .2s; }
.perm-search:focus { border-color: #6777ef; background-color: #fff; box-shadow: 0 0 0 3px rgba(103,119,239,.1); }
.perm-btn { height: 34px; padding: 0 14px; font-size: .78rem; font-weight: 600; border-radius: 8px; border: 1.5px solid #e2e8f0; background: #fff; color: #475569; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; transition: all .15s; }
.perm-btn:hover { background: #f8fafc; border-color: #cbd5e1; }
.perm-btn.select-all { border-color: #6777ef; color: #6777ef; }
.perm-btn.select-all:hover { background: #eff6ff; }
.perm-counter { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 20px; font-size: .68rem; font-weight: 700; background: #f59e0b; color: #fff; }

/* Permission grid */
.perm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px; max-height: 380px; overflow-y: auto; padding-right: 4px; }
.perm-grid::-webkit-scrollbar { width: 4px; }
.perm-grid::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
.perm-grid::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
.perm-item { position: relative; }
.perm-item input[type="checkbox"] { position: absolute; opacity: 0; width: 0; height: 0; }
.perm-item label { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1.5px solid #f1f5f9; border-radius: 10px; cursor: pointer; background: #fafbfc; transition: all .15s; font-size: .8rem; color: #475569; line-height: 1.3; min-height: 44px; }
.perm-item label:hover { border-color: #c7d2fe; background: #f5f3ff; color: #4338ca; }
.perm-item input:checked + label { border-color: #f59e0b; background: #fffbeb; color: #92400e; font-weight: 600; }
.perm-check-box { width: 18px; height: 18px; border-radius: 5px; border: 2px solid #d1d5db; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all .15s; background: #fff; }
.perm-item input:checked + label .perm-check-box { background: #f59e0b; border-color: #f59e0b; }
.perm-check-box i { font-size: 9px; color: #fff; opacity: 0; transform: scale(0); transition: all .15s; }
.perm-item input:checked + label .perm-check-box i { opacity: 1; transform: scale(1); }
.perm-item.hidden { display: none; }
.perm-empty { grid-column: 1 / -1; text-align: center; padding: 32px; color: #94a3b8; font-size: .82rem; display: none; }
.perm-empty i { font-size: 28px; display: block; margin-bottom: 8px; }

/* Diff indicator — changed permissions */
.perm-item.was-checked input:not(:checked) + label { border-color: #fecaca; background: #fef2f2; }
.perm-item:not(.was-checked) input:checked + label { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
.perm-item:not(.was-checked) input:checked + label .perm-check-box { background: #16a34a; border-color: #16a34a; }

/* Change summary bar */
.change-bar { display: none; align-items: center; gap: 10px; padding: 10px 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; margin-bottom: 12px; font-size: .8rem; color: #92400e; }
.change-bar.visible { display: flex; }
.change-bar i { color: #f59e0b; }
.change-chip { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: .72rem; font-weight: 700; }
.chip-add { background: #dcfce7; color: #166534; }
.chip-remove { background: #fee2e2; color: #dc2626; }

/* Footer */
.cr-footer { padding: 18px 24px; background: #f8fafc; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.cr-footer-left { font-size: .78rem; color: #94a3b8; }
.cr-footer-actions { display: flex; gap: 10px; }
.cr-btn { height: 40px; padding: 0 20px; border-radius: 10px; font-size: .85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 7px; cursor: pointer; border: none; transition: all .2s; text-decoration: none; }
.cr-btn-primary { background: linear-gradient(135deg, #f59e0b, #ef4444); color: #fff; box-shadow: 0 2px 8px rgba(245,158,11,.35); }
.cr-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(245,158,11,.45); color: #fff; }
.cr-btn-ghost { background: #fff; border: 1.5px solid #e2e8f0; color: #64748b; }
.cr-btn-ghost:hover { background: #f8fafc; color: #334155; }
.cr-alert-error { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 8px; background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: .8rem; margin-top: 10px; }

@media (max-width: 600px) {
    .cr-section { padding: 16px; }
    .perm-grid { grid-template-columns: repeat(2, 1fr); max-height: 300px; }
    .cr-footer { flex-direction: column; align-items: stretch; }
    .cr-footer-actions { flex-direction: column; }
    .cr-btn { justify-content: center; }
}
</style>
@endpush

@section('main')
<div class="main-content">
<section class="section">
<div class="cr-page">

    {{-- Breadcrumb --}}
    {{-- <div class="cr-breadcrumb">
        <a href="{{ route('roles.index') }}">Roles</a>
        <i class="fas fa-chevron-right"></i>
        <span>Edit Role</span>
    </div> --}}
    <br>
    <br>

    {{-- Title --}}
    <div class="cr-title">
        <div class="cr-title-icon"><i class="fas fa-pen-to-square"></i></div>
        <div class="cr-title-text">
            <h1>Edit Role <span style="color:#f59e0b">{{ $role->name }}</span></h1>
            <p>Modify the permissions assigned to this role</p>
        </div>
    </div>

    <form id="roles-edit" action="{{ route('roles.update', $hashedId) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="cr-card">

            {{-- ── Role Name (readonly) ── --}}
            <div class="cr-section">
                <div class="cr-section-label">
                    <i class="fas fa-tag"></i> Role Name
                    <span class="cr-readonly-badge"><i class="fas fa-lock"></i> Read only</span>
                </div>
                <div class="cr-input-wrap">
                    <i class="fas fa-shield-halved"></i>
                    <input
                        type="text"
                        name="name"
                        class="cr-input"
                        value="{{ old('name', $role->name) }}"
                        readonly
                    >
                </div>
                <div class="cr-input-hint">
                    <i class="fas fa-circle-info" style="color:#f59e0b"></i>
                    Role name cannot be changed after creation
                </div>
                @error('name')
                <div class="cr-alert-error">
                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                </div>
                @enderror
            </div>

            {{-- ── Permissions ── --}}
            <div class="cr-section">
                <div class="cr-section-label">
                    <i class="fas fa-key"></i> Permissions
                    <span class="perm-counter" id="perm-count">0</span>
                </div>

                {{-- Change summary --}}
                <div class="change-bar" id="change-bar">
                    <i class="fas fa-circle-info"></i>
                    <span>Unsaved changes:</span>
                    <span class="change-chip chip-add" id="chip-add" style="display:none">
                        <i class="fas fa-plus"></i> <span id="chip-add-n">0</span> added
                    </span>
                    <span class="change-chip chip-remove" id="chip-remove" style="display:none">
                        <i class="fas fa-minus"></i> <span id="chip-remove-n">0</span> removed
                    </span>
                </div>

                {{-- Toolbar --}}
                <div class="perm-toolbar">
                    <input type="text" class="perm-search" id="perm-search" placeholder="Search permissions...">
                    <button type="button" class="perm-btn select-all" id="btn-select-all">
                        <i class="fas fa-check-double"></i> Select All
                    </button>
                    <button type="button" class="perm-btn" id="btn-clear-all">
                        <i class="fas fa-xmark"></i> Clear
                    </button>
                    <button type="button" class="perm-btn" id="btn-reset-perm" title="Reset to original">
                        <i class="fas fa-rotate-left"></i> Reset
                    </button>
                </div>

                {{-- Grid --}}
                <div class="perm-grid" id="perm-grid">
                    @foreach ($permissions as $permission)
                    @php $isChecked = in_array($permission->id, $rolePermissions); @endphp
                    <div
                        class="perm-item {{ $isChecked ? 'was-checked' : '' }}"
                        data-name="{{ strtolower($permission->name) }}"
                        data-original="{{ $isChecked ? '1' : '0' }}"
                    >
                        <input
                            type="checkbox"
                            name="permissions[]"
                            id="perm-{{ $permission->id }}"
                            value="{{ $permission->id }}"
                            {{ $isChecked ? 'checked' : '' }}
                        >
                        <label for="perm-{{ $permission->id }}">
                            <div class="perm-check-box">
                                <i class="fas fa-check"></i>
                            </div>
                            {{ $permission->name }}
                        </label>
                    </div>
                    @endforeach

                    <div class="perm-empty" id="perm-empty">
                        <i class="fas fa-magnifying-glass"></i>
                        No permissions found
                    </div>
                </div>

                @error('permissions')
                <div class="cr-alert-error" style="margin-top:12px">
                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                </div>
                @enderror
            </div>

            {{-- ── Footer ── --}}
            <div class="cr-footer">
                <div class="cr-footer-left" id="footer-info">
                    <i class="fas fa-circle-check" style="color:#16a34a"></i>
                    No changes yet
                </div>
                <div class="cr-footer-actions">
                    <a href="{{ route('roles.index') }}" class="cr-btn cr-btn-ghost">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <button type="button" id="edit-btn" class="cr-btn cr-btn-primary">
                        <i class="fas fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </div>

        </div>
    </form>

</div>
</section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const checkboxes  = document.querySelectorAll('.perm-item input[type="checkbox"]');
    const counter     = document.getElementById('perm-count');
    const search      = document.getElementById('perm-search');
    const empty       = document.getElementById('perm-empty');
    const changeBar   = document.getElementById('change-bar');
    const chipAdd     = document.getElementById('chip-add');
    const chipRemove  = document.getElementById('chip-remove');
    const footerInfo  = document.getElementById('footer-info');

    /* ── Snapshot original state ── */
    const original = {};
    document.querySelectorAll('.perm-item').forEach(item => {
        const cb = item.querySelector('input');
        original[cb.value] = item.dataset.original === '1';
    });

    /* ── Update counter + change summary ── */
    function updateUI() {
        let checked = 0, added = 0, removed = 0;
        document.querySelectorAll('.perm-item').forEach(item => {
            const cb  = item.querySelector('input');
            const was = original[cb.value];
            if (cb.checked) checked++;
            if (cb.checked && !was)  added++;
            if (!cb.checked && was)  removed++;
        });

        counter.textContent = checked;

        if (added > 0 || removed > 0) {
            changeBar.classList.add('visible');
            chipAdd.style.display    = added   ? 'inline-flex' : 'none';
            chipRemove.style.display = removed ? 'inline-flex' : 'none';
            document.getElementById('chip-add-n').textContent    = added;
            document.getElementById('chip-remove-n').textContent = removed;
            footerInfo.innerHTML = `<i class="fas fa-triangle-exclamation" style="color:#f59e0b"></i> You have unsaved changes`;
        } else {
            changeBar.classList.remove('visible');
            footerInfo.innerHTML = `<i class="fas fa-circle-check" style="color:#16a34a"></i> No changes yet`;
        }
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateUI));
    updateUI();

    /* ── Select / Clear / Reset ── */
    document.getElementById('btn-select-all').addEventListener('click', () => {
        document.querySelectorAll('.perm-item:not(.hidden) input').forEach(cb => cb.checked = true);
        updateUI();
    });
    document.getElementById('btn-clear-all').addEventListener('click', () => {
        checkboxes.forEach(cb => cb.checked = false);
        updateUI();
    });
    document.getElementById('btn-reset-perm').addEventListener('click', () => {
        document.querySelectorAll('.perm-item').forEach(item => {
            item.querySelector('input').checked = original[item.querySelector('input').value];
        });
        updateUI();
    });

    /* ── Search ── */
    search.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        let visible = 0;
        document.querySelectorAll('.perm-item').forEach(item => {
            const match = item.dataset.name.includes(q);
            item.classList.toggle('hidden', !match);
            if (match) visible++;
        });
        empty.style.display = visible === 0 ? 'block' : 'none';
    });

    /* ── Submit ── */
    document.getElementById('edit-btn').addEventListener('click', function () {
        const checked = document.querySelectorAll('.perm-item input:checked').length;
        let added = 0, removed = 0;
        document.querySelectorAll('.perm-item').forEach(item => {
            const cb = item.querySelector('input');
            if (cb.checked && !original[cb.value])  added++;
            if (!cb.checked && original[cb.value]) removed++;
        });

        const changeHtml = (added || removed)
            ? `<div style="display:flex;gap:8px;justify-content:center;margin-top:8px">
                ${added   ? `<span style="background:#dcfce7;color:#166534;padding:2px 10px;border-radius:20px;font-size:.8rem;font-weight:700">+${added} added</span>` : ''}
                ${removed ? `<span style="background:#fee2e2;color:#dc2626;padding:2px 10px;border-radius:20px;font-size:.8rem;font-weight:700">-${removed} removed</span>` : ''}
               </div>`
            : `<p style="color:#94a3b8;font-size:.82rem">No permission changes detected.</p>`;

        Swal.fire({
            title: 'Save changes?',
            html: `Role <strong>{{ $role->name }}</strong> will have <strong>${checked}</strong> permission(s).${changeHtml}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#e2e8f0',
            confirmButtonText: '<i class="fas fa-floppy-disk"></i> Yes, Save',
            cancelButtonText: 'Cancel',
        }).then(result => {
            if (result.isConfirmed) document.getElementById('roles-edit').submit();
        });
    });
})();
</script>
@endpush