@extends('layouts.app')

@section('title', 'Create New Role')

@section('main')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Create New Role</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('roles.store') }}">
                        @csrf

                        <div class="form-group row mb-3">
                            <label for="name" class="col-md-2 col-form-label">Role Name</label>
                            <div class="col-md-10">
                                <input id="name" type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" 
                                       required autofocus
                                       placeholder="Enter role name (letters, numbers, underscore, hyphen only)">
                                
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-md-2 col-form-label">Permissions</label>
                            <div class="col-md-10">
                                @if($permissions->isEmpty())
                                    <div class="alert alert-info">No permissions available</div>
                                @else
                                    <div class="row">
                                        @foreach($permissions as $permission)
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       id="permission-{{ $permission->id }}" 
                                                       value="{{ $permission->id }}"
                                                       @checked(in_array($permission->id, old('permissions', $rolePermissions ?? [])))>
                                                <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-10 offset-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Role
                                </button>
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-check-label {
        word-break: break-word;
    }
</style>
@endpush