@extends('layouts.app')
@section('title', 'Detail SK Letter')

@push('styles')
    <style>
        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #8898aa;
            /* text-transform: uppercase; */
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 14px;
            color: #34395e;
            font-weight: 500;
        }
        .info-group { margin-bottom: 18px; }
        .divider { border-top: 1px solid #f1f1f1; margin: 20px 0; }
        .employee-item {
            background: #f8f9ff;
            border: 1px solid #e8ecff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .menimbang-item, .mengingat-item, .keputusan-item {
            background: #f8f9ff;
            border-left: 3px solid #6777ef;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .status-badge {
            font-size: 12px;
            padding: 5px 14px;
            border-radius: 20px;
            font-weight: 600;
        }
        .approval-step {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .approval-step:last-child { border-bottom: none; }
        .approval-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .approval-icon.approved { background: #d4edda; color: #28a745; }
        .approval-icon.pending  { background: #fff3cd; color: #856404; }
        .salary-info {
            background: #f0f4ff;
            border-radius: 8px;
            padding: 10px 15px;
            margin-top: 8px;
        }
    </style>
@endpush

@section('main')
<div class="main-content">
<section class="section">

    <div class="section-header">
        <h1>SK Letter Detail</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('SkLetters') }}">SK Letters</a></div>
            <div class="breadcrumb-item">Detail</div>
        </div>
    </div>

    <div class="section-body">
    <div class="row">

        {{-- ══════════════════════════════ --}}
        {{-- KOLOM KIRI --}}
        {{-- ══════════════════════════════ --}}
        <div class="col-lg-8">

            {{-- Card: Informasi SK --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-file-alt mr-2 text-primary"></i>SK Information</h4>
                    <div class="d-flex gap-2 align-items-center">
                        @php
                            $statusColors = [
                                'Draft'                      => 'secondary',
                                'Cancelled'                  => 'danger',
                                'Approved HR'                => 'info',
                                'Approved Director'          => 'primary',
                                'Approved Managing Director' => 'success',
                            ];
                            $color = $statusColors[$skletter->status] ?? 'secondary';
                        @endphp
                        <span class="badge badge-{{ $color }} status-badge">
                            {{ $skletter->status }}
                        </span>

                        {{-- Action Buttons --}}
                        @if($skletter->status === 'Draft')
                            @can('update', $skletter)
                                <a href="{{ route('SkLetters.edit', $skletter->id) }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                            @endcan
                        @endif

                        @if($skletter->status === 'Approved Managing Director')
                            <a href="{{ route('SkLetters.pdf', $skletter->id) }}"
                               class="btn btn-danger btn-sm" target="_blank">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </a>
                        @endif

                        @can('approve', $skletter)
                            @if(!in_array($skletter->status, ['Approved Managing Director', 'Cancelled']))
                                <button type="button" class="btn btn-success btn-sm" id="btn-approve">
                                    <i class="fas fa-check mr-1"></i> Approve
                                </button>
                            @endif
                        @endcan

                        @if(in_array($skletter->status, ['Draft', 'Approved HR']))
                            @can('cancel', $skletter)
                                <button type="button" class="btn btn-danger btn-sm" id="btn-cancel">
                                    <i class="fas fa-ban mr-1"></i> Cancel
                                </button>
                            @endcan
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 info-group">
                            <div class="info-label"><i class="fas fa-hashtag mr-1"></i> SK Number</div>
                            <div class="info-value">
                                <strong>{{ $skletter->sk_number  }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6 info-group">
                            <div class="info-label"><i class="fas fa-tag mr-1"></i> SK Type</div>
                            <div class="info-value">{{ $skletter->sktype->sk_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-12 info-group">
                            <div class="info-label"><i class="fas fa-heading mr-1"></i> Title</div>
                            <div class="info-value">{{ $skletter->title ?? '-' }}</div>
                        </div>
                        <div class="col-md-6 info-group">
                            <div class="info-label"><i class="fas fa-building mr-1"></i> Publishing Company</div>
                            <div class="info-value">{{ $skletter->company->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6 info-group">
                            <div class="info-label"><i class="fas fa-map-marker-alt mr-1"></i> Published At</div>
                            <div class="info-value">{{ $skletter->location ?? '-' }}</div>
                        </div>
                        <div class="col-md-4 info-group">
                            <div class="info-label"><i class="fas fa-calendar mr-1"></i> Effective Date</div>
                            <div class="info-value">
                                {{ optional($skletter->effective_date)->translatedFormat('d F Y') ?? '-' }}
                            </div>
                        </div>
                        <div class="col-md-4 info-group">
                            <div class="info-label"><i class="fas fa-calendar-times mr-1"></i> Inactive Date</div>
                            <div class="info-value">
                                {{ optional($skletter->inactive_date)->translatedFormat('d F Y') ?? 'Continuesly' }}
                            </div>
                        </div>
                        <div class="col-md-4 info-group">
                            <div class="info-label"><i class="fas fa-clock mr-1"></i> Created At</div>
                            <div class="info-value">
                                                               {{ optional($skletter->created_at)->translatedFormat('d F Y') ?? '-' }}

                            </div>
                        </div>
                        @if($skletter->notes)
                            <div class="col-md-12 info-group mb-0">
                                <div class="info-label"><i class="fas fa-sticky-note mr-1"></i> Notes</div>
                                <div class="info-value">{{ $skletter->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card: Employees --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-users mr-2 text-primary"></i>
                        Employee Data
                        <span class="badge badge-primary ml-1">{{ $skletter->employees->count() }}</span>
                    </h4>
                </div>
                <div class="card-body">
                    @forelse($skletter->employees as $i => $employee)
                    @php $pivot = $employee->pivot; @endphp
                    <div class="employee-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong style="font-size:15px">{{ $employee->employee_name }}</strong>
                                <span class="text-muted ml-2" style="font-size:12px">
                                    {{ $employee->employee_pengenal ?? $employee->employee_code ?? '-' }}
                                </span>
                            </div>
                            <span class="badge badge-light" style="font-size:11px">#{{ $i + 1 }}</span>
                        </div>
                        <div class="row">
                            @if($pivot->previous_structure_id)
                            <div class="col-md-6 info-group mb-2">
                                <div class="info-label">Previous Position</div>
                                <div class="info-value">
                                    {{ optional(
                                        \App\Models\Structuresnew::with('submissionposition.positionRelation')
                                            ->find($pivot->previous_structure_id)
                                    )->submissionposition?->positionRelation?->name ?? '-' }} - {{ optional(
                                        \App\Models\Structuresnew::with('submissionposition.company')
                                            ->find($pivot->previous_structure_id)
                                    )->submissionposition?->company?->name ?? '-' }}
                                </div>
                            </div>
                            @endif
                            @if($pivot->new_structure_id)
                            <div class="col-md-6 info-group mb-2">
                                <div class="info-label">New Position</div>
                                <div class="info-value">
                                    <strong>
                                    {{ optional(
                                        \App\Models\Structuresnew::with('submissionposition.positionRelation')
                                            ->find($pivot->new_structure_id)
                                    )->submissionposition?->positionRelation?->name ?? '-' }} -  {{ optional(
                                        \App\Models\Structuresnew::with('submissionposition.company')
                                            ->find($pivot->new_structure_id)
                                    )->submissionposition?->company?->name ?? '-' }}
                                    </strong>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if($pivot->basic_salary || $pivot->positional_allowance || $pivot->daily_rate)
                        <div class="salary-info">
                            <div class="row">
                                @if($pivot->basic_salary)
                                <div class="col-md-4">
                                    <div class="info-label">Basic Salary</div>
                                    <div class="info-value">
                                        Rp {{ number_format($pivot->basic_salary, 0, ',', '.') }}
                                    </div>
                                </div>
                                @endif
                                @if($pivot->positional_allowance)
                                <div class="col-md-4">
                                    <div class="info-label">Positional Allowance</div>
                                    <div class="info-value">
                                        Rp {{ number_format($pivot->positional_allowance, 0, ',', '.') }}
                                    </div>
                                </div>
                                @endif
                                @if($pivot->daily_rate)
                                <div class="col-md-4">
                                    <div class="info-label">Daily Rate</div>
                                    <div class="info-value">
                                        Rp {{ number_format($pivot->daily_rate, 0, ',', '.') }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($pivot->notes)
                            <div class="mt-2 text-muted" style="font-size:13px">
                                <i class="fas fa-sticky-note mr-1"></i> {{ $pivot->notes }}
                            </div>
                        @endif
                    </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-users fa-2x mb-2 d-block"></i>
                            No employees in this SK.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Card: Menetapkan --}}
            {{-- @if($skletter->menetapkan_text)
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-stamp mr-2 text-primary"></i>Establish</h4>
                </div>
                <div class="card-body">
                    <div style="font-size:14px; line-height:1.7;">
                        {!! $skletter->menetapkan_text !!}
                    </div>
                </div>
            </div>
            @endif --}}

            {{-- Card: Keputusan --}}
            @if($skletter->keputusan->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-list-ol mr-2 text-primary"></i>Memutuskan</h4>
                </div>
                <div class="card-body">
                    @foreach($skletter->keputusan as $i => $item)
                        <div class="keputusan-item">
                            <strong>{{ $i + 1 }}.</strong> {{ $item->content_keputusan }}
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- ══════════════════════════════ --}}
        {{-- KOLOM KANAN --}}
        {{-- ══════════════════════════════ --}}
        <div class="col-lg-4">

            {{-- Card: Approval Progress --}}
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-tasks mr-2 text-primary"></i>Approval Progress</h4>
                </div>
                <div class="card-body">

                    {{-- Step 1: HR --}}
                    <div class="approval-step">
                        <div class="approval-icon {{ $skletter->approver_1_at ? 'approved' : 'pending' }}">
                            <i class="fas fa-{{ $skletter->approver_1_at ? 'check' : 'clock' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div style="font-weight:600; font-size:13px;">HR Manager</div>
                            @if($skletter->approver1)
                                <div style="font-size:12px; color:#555;">
                                    {{ $skletter->approver1->employee_name }}
                                </div>
                            @endif
                            @if($skletter->approver_1_at)
                                <div style="font-size:11px; color:#28a745;">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ $skletter->approver_1_at->timezone('Asia/Makassar')
                                        ->translatedFormat('d F Y, H:i') }} WITA
                                </div>
                            @else
                                <div style="font-size:11px; color:#856404;">
                                    <i class="fas fa-clock mr-1"></i> Pending
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Step 2: Director --}}
                    <div class="approval-step">
                        <div class="approval-icon {{ $skletter->approver_2_at ? 'approved' : 'pending' }}">
                            <i class="fas fa-{{ $skletter->approver_2_at ? 'check' : 'clock' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div style="font-weight:600; font-size:13px;">Director</div>
                            @if($skletter->approver2)
                                <div style="font-size:12px; color:#555;">
                                    {{ $skletter->approver2->employee_name }}
                                </div>
                            @endif
                            @if($skletter->approver_2_at)
                                <div style="font-size:11px; color:#28a745;">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ $skletter->approver_2_at->timezone('Asia/Makassar')
                                        ->translatedFormat('d F Y, H:i') }} WITA
                                </div>
                            @else
                                <div style="font-size:11px; color:#856404;">
                                    <i class="fas fa-clock mr-1"></i> Pending
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Step 3: Managing Director --}}
                    <div class="approval-step">
                        <div class="approval-icon {{ $skletter->approver_3_at ? 'approved' : 'pending' }}">
                            <i class="fas fa-{{ $skletter->approver_3_at ? 'check' : 'clock' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div style="font-weight:600; font-size:13px;">Managing Director</div>
                            @if($skletter->approver3)
                                <div style="font-size:12px; color:#555;">
                                    {{ $skletter->approver3->employee_name }}
                                </div>
                            @endif
                            @if($skletter->approver_3_at)
                                <div style="font-size:11px; color:#28a745;">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ $skletter->approver_3_at->timezone('Asia/Makassar')
                                        ->translatedFormat('d F Y, H:i') }} WITA
                                </div>
                            @else
                                <div style="font-size:11px; color:#856404;">
                                    <i class="fas fa-clock mr-1"></i> Pending
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- Card: Menimbang --}}
            @if($skletter->menimbang->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-balance-scale mr-2 text-primary"></i>Menimbang</h4>
                </div>
                <div class="card-body">
                    @foreach($skletter->menimbang as $i => $item)
                        <div class="menimbang-item">
                            <strong>{{ $i + 1 }}.</strong> {{ $item->content_menimbang }}
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Card: Mengingat --}}
            @if($skletter->mengingat->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-book mr-2 text-primary"></i>Mengingat</h4>
                </div>
                <div class="card-body">
                    @foreach($skletter->mengingat as $i => $item)
                        <div class="mengingat-item">
                            <strong>{{ $i + 1 }}.</strong> {{ $item->content_mengingat }}
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Card: Quick Actions --}}
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('SkLetters') }}" class="btn btn-secondary btn-block mb-2">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                    @if($skletter->status === 'Approved Managing Director')
                        <a href="{{ route('SkLetters.pdf', $skletter->id) }}"
                           class="btn btn-danger btn-block mb-2" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> Export PDF
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
    </div>

</section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

   

    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}" });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}" });
    @endif

});
</script>
@endpush
 {{-- // Approve
    // $('#btn-approve').on('click', function () {
    //     Swal.fire({
    //         title: 'Approve SK?',
    //         text: 'Pastikan data sudah benar sebelum approve.',
    //         icon: 'question',
    //         showCancelButton: true,
    //         confirmButtonColor: '#28a745',
    //         cancelButtonColor: '#d33',
    //         confirmButtonText: 'Ya, Approve!',
    //         cancelButtonText: 'Batal'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $.post('{{ route('SkLetters.approve', $skletter->id) }}', {
    //                 _token: '{{ csrf_token() }}'
    //             }).done(function () {
    //                 location.reload();
    //             }).fail(function (xhr) {
    //                 Swal.fire('Gagal!', xhr.responseJSON?.message ?? 'Terjadi kesalahan.', 'error');
    //             });
    //         }
    //     });
    // });

    // Cancel
    // $('#btn-cancel').on('click', function () {
    //     Swal.fire({
    //         title: 'Cancel SK?',
    //         text: 'SK yang dibatalkan tidak dapat diproses kembali.',
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#dc3545',
    //         cancelButtonColor: '#6c757d',
    //         confirmButtonText: 'Ya, Cancel!',
    //         cancelButtonText: 'Tidak'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $.post('{{ route('SkLetters.cancel', $skletter->id) }}', {
    //                 _token: '{{ csrf_token() }}'
    //             }).done(function () {
    //                 location.reload();
    //             }).fail(function (xhr) {
    //                 Swal.fire('Gagal!', xhr.responseJSON?.message ?? 'Terjadi kesalahan.', 'error');
    //             });
    //         }
    //     });
    // }); --}}