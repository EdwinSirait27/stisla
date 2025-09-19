@extends('layouts.app')

@section('title', 'Edit Fingerprint')

@push('style')
    <!-- CSS Libraries if needed -->
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Fingerprint (Urgent)</h1>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-body">
                        {{-- <form action="{{ route('Fingerprints.update', [$data->pin, $data->scan_date]) }}" method="POST">
                        @csrf --}}
                        {{-- @method('POST') --}}
                        <form
                            action="{{ route('Fingerprints.update', ['pin' => $data->pin, 'scan_date' => $data->scan_date]) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="pin" value="{{ $data->pin }}">
                            <input type="hidden" name="scan_date" value="{{ $data->scan_date }}">
                            <input type="hidden" name="position_name" value="{{ $data->position_name }}">
                            <input type="hidden" name="store_name" value="{{ $data->name }}">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="employee_name">Employee Name</label>
                                    <input type="text" name="employee_name" class="form-control"
                                        value="{{ $data->employee_name }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="pin">Fingerprint PIN</label>
                                    <input type="text" name="pin" class="form-control" value="{{ $data->pin }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="scan_date">Scan Date</label>
                                    <input type="date" name="scan_date" class="form-control"
                                        value="{{ $data->scan_date }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="in_1">Scan 1</label>
                                    <input type="time" name="in_1" class="form-control"
                                        value="{{ $data->in_1 ?? '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="device_1">Location 1</label>
                                    <input type="text" name="device_1" class="form-control"
                                        value="{{ $data->device_1 ?? '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="in_2">Scan 2</label>
                                    <input type="time" name="in_2" class="form-control"
                                        value="{{ $data->in_2 ?? '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="device_2">Location 2</label>
                                    <input type="text" name="device_2" class="form-control"
                                        value="{{ $data->device_2 ?? '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="in_3">Scan 3</label>
                                    <input type="time" name="in_3" class="form-control"
                                        value="{{ $data->in_3 ?? '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="device_3">Location 3</label>
                                    <input type="text" name="device_3" class="form-control"
                                        value="{{ $data->device_3 ?? '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="in_4">Scan 4</label>
                                    <input type="time" name="in_4" class="form-control"
                                        value="{{ $data->in_4 ?? '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="device_4">Location 4</label>
                                    <input type="text" name="device_4" class="form-control"
                                        value="{{ $data->device_4 ?? '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="in_5">Scan 5</label>
                                    <input type="time" name="in_5" class="form-control"
                                        value="{{ $data->in_5 ?? '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="device_5">Location 5</label>
                                    <input type="text" name="device_5" class="form-control"
                                        value="{{ $data->device_5 ?? '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="in_6">Scan 6</label>
                                    <input type="time" name="in_6" class="form-control"
                                        value="{{ $data->in_6 ?? '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="device_6">Location 6</label>
                                    <input type="text" name="device_6" class="form-control"
                                        value="{{ $data->device_6 ?? '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="in_7">Scan 7</label>
                                    <input type="time" name="in_7" class="form-control"
                                        value="{{ $data->in_7 ?? '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="device_7">Location 7</label>
                                    <input type="text" name="device_7" class="form-control"
                                        value="{{ $data->device_7 ?? '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="in_8">Scan 8</label>
                                    <input type="time" name="in_8" class="form-control"
                                        value="{{ $data->in_8 ?? '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="device_8">Location 8</label>
                                    <input type="text" name="device_8" class="form-control"
                                        value="{{ $data->device_8 ?? '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="in_9">Scan 9</label>
                                    <input type="time" name="in_9" class="form-control"
                                        value="{{ $data->in_9 ?? '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="device_9">Location 9</label>
                                    <input type="text" name="device_9" class="form-control"
                                        value="{{ $data->device_9 ?? '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="in_10">Scan 10</label>
                                    <input type="time" name="in_10" class="form-control"
                                        value="{{ $data->in_10 ?? '' }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="device_10">Location 10</label>
                                    <input type="text" name="device_10" class="form-control"
                                        value="{{ $data->device_10 ?? '' }}">
                                </div>

                            </div>





                            <div class="form-group">

                            </div>

                            <div class="form-group">

                            </div>
                            <div class="form-group">

                            </div>
                            <div class="form-group">
                                <label for="attachment">Attachment</label>
                                <input type="file" name="attachment" class="form-control"
                                    value="{{ $data->attachment ?? '' }}">
                            </div>
  <div class="alert alert-secondary mt-4" role="alert">
                                            <span class="text-dark">
                                                <strong>Important Note:</strong> <br>
                                                - FOR URGENT PURPOSE ONLY.<br>
                                                - Attachment is filled with photo evidence that has been signed by the manager. <br>
                                                - Attachments only support image files such as jpg, jpeg and png. <br>
                                                - more than 512 kb will be rejected.
                                            </span>
                                        </div>

                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('pages.Fingerprints') }}" class="btn btn-secondary">Back</a>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraries if needed -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        @if (session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: 'Gagal!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        @endif
    </script>
@endpush
