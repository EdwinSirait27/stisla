{{-- @extends('layouts.app')
@section('title', 'Blank Page')
@push('style')
    <!-- CSS Libraries -->
@endpush
@section('main')<div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Import Employee</h1>
            </div>
            <div class="section-body">
                <form action="{{ route('Import.employee') }}" method="POST" enctype="multipart/form-data">
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
@endpush --}}
@extends('layouts.app') 
@section('title', 'Import Employee')

@push('style')
<!-- Tambahkan Tailwind jika belum tersedia -->
@endpush

@section('main')
<div class="main-content p-6">
    <section class="section bg-white rounded-lg shadow p-6">
        <div class="section-header mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Import Employee</h1>
        </div>

        <div class="section-body">
            <form action="{{ route('Import.employee') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700">Choose Excel File</label>
                    <input type="file" name="file" id="file" required 
                        class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition duration-150">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </section>
    <div class="mt-6 p-4 bg-gray-100 border-l-4 border-gray-400 rounded">
        <p class="text-sm text-gray-800">
            <strong>Important Note:</strong><br>
            - Use Excel file (.xlsx).<br>
            - CSV format may not be supported.
        </p>
    </div>
</div>
@endsection
@push('scripts')
<!-- JS Libraries (jika diperlukan) -->
@endpush
