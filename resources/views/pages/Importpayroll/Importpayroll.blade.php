@extends('layouts.app')
@section('title', 'Blank Page')
@push('style')
    <!-- CSS Libraries -->
@endpush
@section('main')<div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>The moment we've been waiting for</h1>
            </div>
            @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

            <div class="section-body">
                <form action="{{ route('Importpayroll.user') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" required>
                    <button type="submit">Import</button>
                </form>
                
            </div>
        </section>
        <div class="alert alert-secondary mt-4" role="alert">
            <span class="text-dark">
                <strong>Important Note:</strong> <br>
                - for the file use excel xlsx type, csv may not work.<br>
                
            </span>
        </div>
    </div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
        });
    </script>
@endif

@if (session('errorr'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('errorr') }}',
        });
    </script>
@endif
@endpush