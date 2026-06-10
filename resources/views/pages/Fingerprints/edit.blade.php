{{-- @extends('layouts.app')

@section('title', 'Edit Fingerprint')

@push('style')
    <!-- CSS Libraries if needed -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
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
                                  
        <input 
    type="text" 
    name="in_1" 
    id="in_1"
    class="form-control timepicker"
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
                                 
    <input 
    type="text" 
    name="in_2" 
    id="in_2"
    class="form-control timepicker"
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
                                       <input 
    type="text" 
    name="in_3" 
    id="in_3"
    class="form-control timepicker"
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
                                        <input 
    type="text" 
    name="in_4" 
    id="in_4"
    class="form-control timepicker"
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
                                         <input 
    type="text" 
    name="in_5" 
    id="in_5"
    class="form-control timepicker"
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
                          <input 
    type="text" 
    name="in_6" 
    id="in_6"
    class="form-control timepicker"
    value="{{ $data->in_6 ?? '' }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="device_6">Location 6</label>
                                        <input type="text" name="device_6" class="form-control"
                                            value="{{ $data->device_6 ?? '' }}">
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i:S",
            time_24hr: true,
            allowInput: true,
        });
    });
</script>

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
        $('.timepicker').inputmask('99:99:99', { placeholder: "HH:MM:SS" });

    </script>
@endpush --}}

@extends('layouts.app')

@section('title', 'Edit Fingerprint')

@push('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"> --}}
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
                                    <label>Employee Name</label>
                                    <input type="text" name="employee_name" class="form-control"
                                        value="{{ $data->employee_name }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Fingerprint PIN</label>
                                    <input type="text" name="pin" class="form-control"
                                        value="{{ $data->pin }}" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Scan Date</label>
                                    <input type="date" name="scan_date" class="form-control"
                                        value="{{ $data->scan_date }}" readonly>
                                </div>
                            </div>

                            @php
                                $scanLabels = [
                                    1 => 'In',
                                    2 => 'Out',
                                    3 => 'Break In',
                                    4 => 'Break Out',
                                    5 => 'Ovt In',
                                    6 => 'Ovt Out',
                                ];
                            @endphp

                            @foreach (range(1, 6) as $i)
<div class="row align-items-end">
    <div class="form-group col-md-6">
        <label>Scan {{ $i }} — {{ $scanLabels[$i] }}</label>
        <input
            type="text"
            name="in_{{ $i }}"
            id="in_{{ $i }}"
            class="form-control timepicker"
            value="{{ $data->{"in_$i"} ?? '' }}"
            placeholder="HH:MM:SS">
    </div>
    <div class="form-group col-md-6">
        <label>Device {{ $i }} — {{ $scanLabels[$i] }}</label>
        @php
            // $currentDevice = trim($data->{"device_$i"} ?? '');
                $currentDevice = trim($data->{"device_$i"} ?? '');

        @endphp
        <select name="device_{{ $i }}" id="device_{{ $i }}" class="form-control device">
            <option value="">-- Select Device --</option>

            {{-- Jika value ada tapi tidak ada di list $devices, tampilkan sebagai option tersendiri --}}
            @if ($currentDevice && !$devices->contains('device_name', $currentDevice))
                <option value="{{ $currentDevice }}" selected>{{ $currentDevice }} (current)</option>
            @endif

            @foreach ($devices as $device)
                <option value="{{ $device->device_name }}"
                    {{-- {{ trim($device->device_name) === $currentDevice ? 'selected' : '' }}> --}}
                    {{ trim($device->device_name) === $currentDevice ? 'selected' : '' }}>

                    {{ $device->device_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
@endforeach

                            <div class="form-group mt-3">
                                <label>Attachment</label>
                                <input type="file" name="attachment" class="form-control">
                            </div>

                            <div class="alert alert-secondary mt-3" role="alert">
                                <span class="text-dark">
                                    <strong>Important Note:</strong><br>
                                    - FOR URGENT PURPOSE ONLY.<br>
                                    - Attachment is filled with photo evidence that has been signed by the manager.<br>
                                    - Attachments only support image files such as jpg, jpeg and png.<br>
                                    - More than 512 kb will be rejected.
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
{{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Timepicker
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i:S",
            time_24hr: true,
            allowInput: true,
        });

        // Select2 untuk dropdown device
        // $('.select2-device').select2({
        //     placeholder: '-- Select Device --',
        //     allowClear: true,
        //     width: '100%',
        // });
    });

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