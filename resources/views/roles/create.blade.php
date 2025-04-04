@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ isset($role) ? 'Edit' : 'Create' }} Role</h2>
    
    <form action="{{ isset($role) ? route('roles.update', $role->id) : route('roles.store') }}" method="POST">
        @csrf
        @if(isset($role))
            @method('PUT')
        @endif
        
        <div class="mb-3">
            <label for="name" class="form-label">Role Name</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="{{ old('name', $role->name ?? '') }}" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Permissions</label>
            <div class="row">
                @foreach($permissions as $permission)
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               name="permissions[]" 
                               value="{{ $permission->id }}"
                               @if(isset($rolePermissions) && in_array($permission->id, $rolePermissions)) checked @endif>
                        <label class="form-check-label">{{ $permission->name }}</label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
@endsection