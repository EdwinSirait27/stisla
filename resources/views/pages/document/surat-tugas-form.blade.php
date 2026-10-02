@extends('layouts.app')
@section('title', 'Surat Tugas')
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush
<style>
    .card {
        border: none;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08);
        border-radius: 0.5rem;
    }

    .current-assignment {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.85rem;
        color: #4a5568;
    }

    .current-assignment strong {
        color: #2d3748;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.8rem;
        color: #4a5568;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px);
        display: flex;
        align-items: center;
        border: 1px solid #dde2ec;
    }
</style>
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ $document ? 'Edit Surat Tugas' : 'Buat Surat Tugas' }}</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-file-signature"></i> Surat Tugas — {{ $employee->employee_name }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="current-assignment">
                                    <div><strong>Departemen saat ini:</strong> {{ $employee->primaryDepartment->first()->department_name ?? '-' }}</div>
                                    <div><strong>Store saat ini:</strong> {{ $employee->primaryStore->first()->name ?? '-' }}</div>
                                    <div><strong>Posisi saat ini:</strong> {{ $employee->primaryPosition->first()->name ?? '-' }}</div>
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @php
                                    $activeAssignments = $document ? $document->assignments->groupBy('type') : collect();

                                    // department/position stay single-select: pick the row that
                                    // represents the primary change (there's normally just one).
                                    $singleValue = function (string $type) use ($activeAssignments) {
                                        $group = $activeAssignments->get($type);
                                        if (!$group) {
                                            return null;
                                        }
                                        $row = $group->firstWhere('is_primary_change', true) ?? $group->first();
                                        return $row->new_reference_id ?? null;
                                    };

                                    // store is multi-select: prefill every id it was moved to.
                                    $multiValues = function (string $type) use ($activeAssignments) {
                                        $group = $activeAssignments->get($type);
                                        return $group ? $group->pluck('new_reference_id')->all() : [];
                                    };
                                @endphp

                                <form
                                    method="POST"
                                    action="{{ $document ? route('documents.st.update', $document->id) : route('documents.st.store', $employee->id) }}"
                                >
                                    @csrf
                                    @if ($document)
                                        @method('PUT')
                                    @endif

                                    <p class="text-muted" style="font-size:.85rem;">
                                        Pilih minimal salah satu tujuan pemindahan (departemen, store, dan/atau posisi).
                                        Setelah <strong>Tanggal Selesai</strong> terlewati, karyawan otomatis
                                        dikembalikan ke penempatan semula.
                                    </p>

                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label class="form-label">Departemen Tujuan</label>
                                            <select name="department_id" class="form-control select2">
                                                <option value="">— Tidak diubah —</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}"
                                                        {{ old('department_id', $singleValue('department')) == $department->id ? 'selected' : '' }}>
                                                        {{ $department->department_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="form-label">Store Tujuan</label>
                                            <select name="store_id[]" class="form-control select2" multiple
                                                data-placeholder="— Tidak diubah —">
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}"
                                                        {{ in_array($store->id, old('store_id', $multiValues('store'))) ? 'selected' : '' }}>
                                                        {{ $store->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">
                                                Bisa pilih lebih dari satu; yang pertama jadi lokasi utama.
                                            </small>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="form-label">Posisi Tujuan</label>
                                            <select name="position_id" class="form-control select2">
                                                <option value="">— Tidak diubah —</option>
                                                @foreach ($positions as $position)
                                                    <option value="{{ $position->id }}"
                                                        {{ old('position_id', $singleValue('position')) == $position->id ? 'selected' : '' }}>
                                                        {{ $position->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="form-label">Tanggal Mulai (Issued Date)</label>
                                            <input type="date" name="issued_date" class="form-control"
                                                value="{{ old('issued_date', optional($document)->issued_date ? \Carbon\Carbon::parse($document->issued_date)->format('Y-m-d') : '') }}"
                                                required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label">Tanggal Selesai (Expired Date)</label>
                                            <input type="date" name="expired_date" class="form-control"
                                                value="{{ old('expired_date', optional($document)->expired_date ? \Carbon\Carbon::parse($document->expired_date)->format('Y-m-d') : '') }}"
                                                required>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <a href="{{ route('document.index') }}" class="btn btn-light border">Batal</a>
                                        <button type="submit" class="btn btn-primary">
                                            {{ $document ? 'Simpan Perubahan' : 'Buat Surat Tugas' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        jQuery(document).ready(function($) {
            $('.select2').select2({
                width: 'resolve'
            });
        });
    </script>
@endpush
