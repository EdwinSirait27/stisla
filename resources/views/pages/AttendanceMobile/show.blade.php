@extends('layouts.app')

@section('title', 'Attendance Log Detail')

@push('styles')
    <style>
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 0.95rem;
            color: #212529;
        }
        .badge-lg {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
        }
        .attendance-photo {
            width: 100%;
            max-width: 300px;
            border-radius: 12px;
            border: 3px solid #e9ecef;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .attendance-photo:hover {
            transform: scale(1.02);
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Attendance Log Detail</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route(getDashboardRoute()) }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="{{ route('pages.AttendanceMobile') }}">Attendance Log</a></div>
                    <div class="breadcrumb-item">Detail</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">

                    {{-- Kolom Kiri --}}
                    <div class="col-12 col-md-8">

                        {{-- Info Utama --}}
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-clipboard-list mr-2"></i>Attendance Info</h4>
                                <div class="card-header-action">
                                    @php
                                        $statusColor = match($log->status) {
                                            'approved' => 'success',
                                            'flagged'  => 'danger',
                                            'pending'  => 'warning',
                                            default    => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $statusColor }} badge-lg">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="info-label">Employee</div>
                                        <div class="info-value">{{ $log->employee?->employee_name ?? '-' }}</div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="info-label">Location</div>
                                        <div class="info-value">{{ $log->store?->name ?? 'Work From Anywhere' }}</div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="info-label">Type</div>
                                        <div class="info-value">
                                            @if($log->type === 'checkin')
                                                <span class="badge badge-primary badge-lg">Check In</span>
                                            @else
                                                <span class="badge badge-warning badge-lg">Check Out</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="info-label">Work Date</div>
                                        <div class="info-value">
                                            {{ $log->work_date ? \Carbon\Carbon::parse($log->work_date)->translatedFormat('d F Y') : '-' }}
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="info-label">Logged At</div>
                                        <div class="info-value">
                                            {{ optional($log->logged_at)->timezone('Asia/Makassar')->translatedFormat('d F Y H:i') ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="info-label">Device ID</div>
                                        <div class="info-value text-monospace">{{ $log->device_id ?? '-' }}</div>
                                    </div>
                                    @if($log->flag_reason)
                                        <div class="col-12 mb-4">
                                            <div class="info-label">Flag Reason</div>
                                            <div class="alert alert-danger mb-0">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                {{ $log->flag_reason }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Lokasi --}}
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-map-marker-alt mr-2"></i>Location Info</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="info-label">Latitude</div>
                                        <div class="info-value text-monospace">{{ $log->latitude ?? '-' }}</div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="info-label">Longitude</div>
                                        <div class="info-value text-monospace">{{ $log->longitude ?? '-' }}</div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="info-label">Distance from Location</div>
                                        <div class="info-value">
                                            {{ $log->distance_from_store !== null ? number_format($log->distance_from_store, 2) . ' meters' : 'Work From Anywhere' }}
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="info-label">Within Geofence</div>
                                        <div class="info-value">
                                            @if($log->is_within_geofence)
                                                <span class="badge badge-success badge-lg">
                                                    <i class="fas fa-check-circle mr-1"></i> Within
                                                </span>
                                            @else
                                                <span class="badge badge-danger badge-lg">
                                                    <i class="fas fa-times-circle mr-1"></i> Outside
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-4">
                                        <div class="info-label">Mock Location</div>
                                        <div class="info-value">
                                            @if($log->is_mock_location)
                                                <span class="badge badge-danger badge-lg">
                                                    <i class="fas fa-exclamation-circle mr-1"></i> Detected
                                                </span>
                                            @else
                                                <span class="badge badge-success badge-lg">
                                                    <i class="fas fa-check-circle mr-1"></i> Real
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Google Maps embed --}}
                                @if($log->latitude && $log->longitude)
                                    <div class="mt-2">
                                        <div class="info-label mb-2">Map Preview</div>
                                        <a href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary mb-2">
                                            <i class="fas fa-external-link-alt mr-1"></i> Open in Google Maps
                                        </a>
                                        <iframe
                                            src="https://maps.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}&z=17&output=embed"
                                            width="100%"
                                            height="250"
                                            frameborder="0"
                                            style="border-radius: 8px; border: 1px solid #e9ecef;"
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                    {{-- Kolom Kanan --}}
                    <div class="col-12 col-md-4">

                        {{-- Foto Selfie --}}
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-camera mr-2"></i>Attendance Photo</h4>
                            </div>
                            <div class="card-body text-center">
                                @if($photoUrl)
                                    <img src="{{ $photoUrl }}"
                                        alt="Attendance Photo"
                                        class="attendance-photo"
                                        onclick="openPhotoModal('{{ $photoUrl }}')"
                                        id="attendancePhoto">
                                    <p class="text-muted text-sm mt-2">Click to enlarge</p>
                                @else
                                    <div class="py-5 text-muted">
                                        <i class="fas fa-image fa-3x mb-3 d-block"></i>
                                        <span>No photo available</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Liveness --}}
                        {{-- <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-shield-alt mr-2"></i>Liveness Check</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <div class="info-label">Liveness Result</div>
                                    <div class="info-value mt-1">
                                        @if($log->liveness_passed)
                                            <span class="badge badge-success badge-lg">
                                                <i class="fas fa-check-circle mr-1"></i> Passed
                                            </span>
                                        @else
                                            <span class="badge badge-danger badge-lg">
                                                <i class="fas fa-times-circle mr-1"></i> Failed
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label mb-1">Liveness Score</div>
                                    @php
                                        $score        = $log->liveness_score ?? 0;
                                        $scorePercent = min(100, round($score * 100));
                                        $scoreColor   = $scorePercent >= 80 ? 'success' : ($scorePercent >= 50 ? 'warning' : 'danger');
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-1 w-100" style="height: 10px; border-radius: 5px;">
                                            <div class="progress-bar bg-{{ $scoreColor }}"
                                                role="progressbar"
                                                style="width: {{ $scorePercent }}%"
                                                aria-valuenow="{{ $scorePercent }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span class="text-{{ $scoreColor }} font-weight-bold ml-2">
                                            {{ $scorePercent }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        {{-- Liveness Check --}}
{{-- <div class="card">
    <div class="card-header">
        <h4><i class="fas fa-shield-alt mr-2"></i>Liveness Check</h4>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <div class="info-label">Liveness Result</div>
            <div class="info-value mt-1">
                @if($log->liveness_passed)
                    <span class="badge badge-success badge-lg">
                        <i class="fas fa-check-circle mr-1"></i> Passed
                    </span>
                @else
                    <span class="badge badge-danger badge-lg">
                        <i class="fas fa-times-circle mr-1"></i> Failed
                    </span>
                @endif
            </div>
        </div>

        <div class="mb-2">
            <div class="info-label mb-1">Face Distance</div>
            @php
                $distance     = $log->liveness_score; // field ini sebenarnya face distance
                $threshold    = 0.4; // threshold umum DeepFace (verified = distance < threshold)
                $similarity   = $distance !== null
                    ? max(0, round((1 - ($distance / $threshold)) * 100))
                    : null;
                $barColor = match(true) {
                    $similarity === null      => 'secondary',
                    $similarity >= 80         => 'success',
                    $similarity >= 50         => 'warning',
                    default                   => 'danger',
                };
            @endphp

            @if($distance !== null)
                <div class="d-flex align-items-center mb-1">
                    <div class="progress flex-1 w-100 mr-2" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar bg-{{ $barColor }}"
                            role="progressbar"
                            style="width: {{ $similarity }}%">
                        </div>
                    </div>
                    <span class="text-{{ $barColor }} font-weight-bold" style="min-width: 45px;">
                        {{ $similarity }}%
                    </span>
                </div>
                <small class="text-muted">
                    Face distance: <code>{{ number_format($distance, 4) }}</code>
                    (threshold: <code>{{ $threshold }}</code> — lower is better)
                </small>
            @else
                <span class="text-muted">No data</span>
            @endif
        </div>
    </div>
</div> --}}
{{-- Verifikasi Wajah --}}
<div class="card">
    <div class="card-header">
        <h4><i class="fas fa-shield-alt mr-2"></i>Verifikasi Wajah</h4>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <div class="info-label">Hasil Verifikasi</div>
            <div class="info-value mt-1">
                @if($log->liveness_passed)
                    <span class="badge badge-success badge-lg">
                        <i class="fas fa-check-circle mr-1"></i> Wajah Terverifikasi
                    </span>
                @else
                    <span class="badge badge-danger badge-lg">
                        <i class="fas fa-times-circle mr-1"></i> Wajah Tidak Dikenali
                    </span>
                @endif
            </div>
        </div>

        {{-- <div class="mb-2">
            <div class="info-label mb-1">Tingkat Kecocokan Wajah</div>
            @php
                $distance  = $log->liveness_score;
                $threshold = 0.4;
                $similarity = $distance !== null
                    ? max(0, round((1 - ($distance / $threshold)) * 100))
                    : null;
                $barColor = match(true) {
                    $similarity === null => 'secondary',
                    $similarity >= 80    => 'success',
                    $similarity >= 50    => 'warning',
                    default              => 'danger',
                };
                $label = match(true) {
                    $similarity === null => 'Tidak ada data',
                    $similarity >= 80    => 'Sangat cocok',
                    $similarity >= 50    => 'Cukup cocok',
                    default              => 'Kurang cocok',
                };
            @endphp

            @if($distance !== null)
                <div class="d-flex align-items-center mb-1">
                    <div class="progress flex-1 w-100 mr-2" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar bg-{{ $barColor }}"
                            role="progressbar"
                            style="width: {{ $similarity }}%">
                        </div>
                    </div>
                    <span class="text-{{ $barColor }} font-weight-bold" style="min-width: 45px;">
                        {{ $similarity }}%
                    </span>
                </div>
                <small class="text-muted">{{ $label }}</small>
            @else
                <span class="text-muted">Tidak ada data</span>
            @endif
        </div> --}}
    </div>
</div>

                        {{-- Back Button --}}
                        <a href="{{ route('pages.AttendanceMobile') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left mr-2"></i> Back to List
                        </a>

                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Modal Photo --}}
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white">Attendance Photo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-2">
                    <img id="photoModalImg" src="" alt="Attendance Photo"
                        style="max-width: 100%; border-radius: 8px;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openPhotoModal(url) {
            document.getElementById('photoModalImg').src = url;
            $('#photoModal').modal('show');
        }
    </script>
@endpush