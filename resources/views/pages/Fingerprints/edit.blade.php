@extends('layouts.app')

@section('title', 'Edit Fingerprint')

@push('style')
    <!-- CSS Libraries if needed -->
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Edit Fingerprint</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('Fingerprints.update', [$data->pin, $data->scan_date]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="pin">PIN</label>
                            <input type="text" name="pin" class="form-control" value="{{ $data->pin }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="scan_date">Tanggal Scan</label>
                            <input type="date" name="scan_date" class="form-control" value="{{ $data->scan_date }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="in_1">Scan Masuk 1</label>
                            <input type="time" name="in_1" class="form-control" value="{{ $data->in_1 ?? '' }}">
                        </div>

                        <div class="form-group">
                            <label for="out_1">Scan Keluar 1</label>
                            <input type="time" name="out_1" class="form-control" value="{{ $data->out_1 ?? '' }}">
                        </div>

                        {{-- Tambahkan field tambahan jika ada scan tambahan seperti in_2, out_2, dst --}}
                        {{-- <div class="form-group">
                            <label for="in_2">Scan Masuk 2</label>
                            <input type="time" name="in_2" class="form-control" value="{{ $data->in_2 ?? '' }}">
                        </div> --}}

                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('pages.Fingerprints') }}" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <!-- JS Libraries if needed -->
@endpush
