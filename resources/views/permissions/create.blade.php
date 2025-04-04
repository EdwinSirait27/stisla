@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ isset($permission) ? 'Edit' : 'Create' }} Permission</h2>
    
    <form action="{{ isset($permission) ? route('permissions.update', $permission->id) : route('permissions.store') }}" method="POST">
        @csrf
        @if(isset($permission))
            @method('PUT')
        @endif
        
        <div class="mb-3">
            <label for="name" class="form-label">Permission Name</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="{{ old('name', $permission->name ?? '') }}" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
@endsection