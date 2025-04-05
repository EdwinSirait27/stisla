@extends('layouts.app')

@section('title', 'Edit Role')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}">
    <style>
        /* Card styling */
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
        
        /* Form controls */
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
        
        /* Button styling */
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
                            <form action="{{ route('roles.update', $hashedId) }}" method="POST">
                          
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
                                                <div class="col-md-3 mb-3">
                                                    <div class="permission-group">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" 
                                                                   class="custom-control-input" 
                                                                   id="permission-{{ $permission->id }}" 
                                                                   name="permissions[]" 
                                                                   value="{{ $permission->id }}"
                                                                   {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="permission-{{ $permission->id }}">
                                                                {{ $permission->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <a href="{{ route('roles.index') }}" class="btn btn-secondary mr-2">
                                        <i class="fas fa-arrow-left"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
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

    <!-- Page Specific JS File -->
    <script>
        // You can add any specific JavaScript for this page here
    </script>
@endpush