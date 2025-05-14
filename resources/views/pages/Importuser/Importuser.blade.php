@extends('layouts.app')
@section('title', 'Blank Page')
@push('style')
    <!-- CSS Libraries -->
@endpush
@section('main')<div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Import Users</h1>
            </div>
            <div class="section-body">
                <form action="{{ route('Importuser.user') }}" method="POST" enctype="multipart/form-data">
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
    <!-- JS Libraies -->

    <!-- Page Specific JS File -->
@endpush

