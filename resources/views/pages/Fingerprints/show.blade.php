@extends('layouts.app')

@section('title', 'Show Fingerprint')

@push('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"> --}}
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Show Fingerprint</h1>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-body">



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
                                <input type="text" name="pin" class="form-control" value="{{ $data->pin }}"
                                    readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Scan Date</label>
                                <input type="date" name="scan_date" class="form-control" value="{{ $data->scan_date }}"
                                    readonly>
                            </div>
                        </div>

                        @php
                            $scanLabels = [
                                1 => 'In',
                                2 => 'Out',
                            ];
                        @endphp

                        @foreach (range(1, 2) as $i)
                            <div class="row align-items-end">
                                <div class="form-group col-md-6">
                                    <label>Scan {{ $i }} — {{ $scanLabels[$i] }}</label>
                                    <input type="text" name="in_{{ $i }}" id="in_{{ $i }}"
                                        class="form-control timepicker" value="{{ $data->{"in_$i"} ?? '' }}"
                                        placeholder="HH:MM:SS">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Device {{ $i }} — {{ $scanLabels[$i] }}</label>
                                    @php
                                        // $currentDevice = trim($data->{"device_$i"} ?? '');
                                        $currentDevice = trim($data->{"device_$i"} ?? '');

                                    @endphp
                                    <select name="device_{{ $i }}" id="device_{{ $i }}"
                                        class="form-control device">
                                        <option value="">-- Select Device --</option>

                                        {{-- Jika value ada tapi tidak ada di list $devices, tampilkan sebagai option tersendiri --}}
                                        @if ($currentDevice && !$devices->contains('device_name', $currentDevice))
                                            <option value="{{ $currentDevice }}" selected>{{ $currentDevice }} (current)
                                            </option>
                                        @endif

                                        @foreach ($devices as $device)
                                            <option value="{{ $device->device_name }}"
                                                {{ trim($device->device_name) === $currentDevice ? 'selected' : '' }}>

                                                {{ $device->device_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach


                        {{-- <div class="form-group mt-3">
                            <label>Attachment</label>

                            <div id="attachment-list">
                                <div class="attachment-item d-flex align-items-center gap-2 mb-2">
                                    <input type="file" name="attachments[]"
                                        accept="image/jpg,image/jpeg,image/png,image/webp" class="form-control">
                                </div>
                            </div>

                            <button type="button" id="btn-add-attachment" class="btn btn-sm btn-outline-primary mt-1">
                                <i class="fas fa-plus"></i> Tambah Attachment
                            </button>
                            <small class="d-block text-muted mt-1">jpg, jpeg, png, webp — maks 512KB/file</small>
                        </div> --}}
                        <div class="form-group mt-3">
                            <label>Attachment</label>


                            {{-- @if ($isEdited && $data->attachments?->count() > 0)
    <div class="row g-2">
        @foreach ($data->attachments as $attachment)
            @if (!empty($attachment->attachment))
                <div class="col-md-3 col-sm-4 col-6">
                    <div class="border rounded p-1 text-center">
                        @php
                            $url = Storage::disk('s3')->temporaryUrl($attachment->attachment, now()->addMinutes(30));
                        @endphp
                        <a href="{{ $url }}" target="_blank">
                            <img src="{{ $url }}"
                                class="img-fluid rounded"
                                style="max-height:120px;object-fit:cover;width:100%"
                                alt="Attachment {{ $loop->iteration }}">
                        </a>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@else
    <p class="text-muted small mb-0">Belum ada attachment.</p>
@endif --}}
                            @if ($isEdited && $data->attachments?->count() > 0)
                                <div class="row g-2">
                                    @foreach ($data->attachments as $attachment)
                                        @if (!empty($attachment->attachment))
                                            @php
                                                $url = Storage::disk('s3')->temporaryUrl(
                                                    $attachment->attachment,
                                                    now()->addMinutes(30),
                                                );
                                            @endphp
                                            <div class="col-md-3 col-sm-4 col-6">
                                                <div class="border rounded p-1 text-center" style="cursor:pointer"
                                                    onclick="showAttachmentModal('{{ $url }}', '{{ $loop->iteration }}')">
                                                    <img src="{{ $url }}" class="img-fluid rounded"
                                                        style="max-height:120px;object-fit:cover;width:100%"
                                                        alt="Attachment {{ $loop->iteration }}">
                                                    <small class="text-muted d-block mt-1" style="font-size:.7rem">
                                                        Attachment {{ $loop->iteration }}
                                                    </small>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted small mb-0">Belum ada attachment.</p>
                            @endif


                        </div>
                        <div class="form-group mt-3">
    <label>Notes</label>
    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
        rows="3" placeholder="Tambahkan catatan...">{{ old('notes', $data->notes ?? '') }}</textarea>
    @error('notes')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
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

                        <a href="{{ route('pages.Fingerprints') }}" class="btn btn-secondary">Back</a>


                    </div>
                </div>
            </div>
        </section>
    </div>


    {{-- modal --}}
    <div class="modal fade" id="attachmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attachmentModalTitle">Attachment</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-2">
                    <img id="attachmentModalImg" src="" class="img-fluid rounded" alt="Attachment">
                </div>
                <div class="modal-footer">
                    {{-- <a id="attachmentModalDownload" href="" target="_blank" class="btn btn-sm btn-primary">
                    <i class="fas fa-download me-1"></i> Buka di Tab Baru
                </a> --}}
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Timepicker
            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i:S",
                time_24hr: true,
                allowInput: true,
            });
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
    <script>
        function showAttachmentModal(url, index) {
            $('#attachmentModalTitle').text('Attachment ' + index);
            $('#attachmentModalImg').attr('src', url);
            $('#attachmentModalDownload').attr('href', url);
            $('#attachmentModal').modal('show');
        }
    </script>
@endpush
