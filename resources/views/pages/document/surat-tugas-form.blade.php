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
                <h1>Edit Surat Tugas {{ $document->document_number }}</h1>
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
                                    <div><strong>Departemen saat ini:</strong> {{ $employee->primaryDepartment->first()->department_name ?? \App\Models\Departments::find($employee->department_id)?->department_name ?? '-' }}</div>
                                    <div><strong>Store saat ini:</strong> {{ $employee->primaryStore->first()->name ?? \App\Models\Stores::find($employee->store_id)?->name ?? '-' }}</div>
                                    <div><strong>Posisi saat ini:</strong> {{ $employee->primaryPosition->first()->name ?? \App\Models\Position::find($employee->position_id)?->name ?? '-' }}</div>
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

                                    // store is multi-select: lokasi utama dulu, lalu tambahan sesuai urutan dibuat
                                    $multiValues = function (string $type) use ($activeAssignments) {
                                        $group = $activeAssignments->get($type);
                                        return $group ? $group->sortByDesc('is_primary_change')->pluck('new_reference_id')->values()->all() : [];
                                    };
                                    $pickedStores = array_values(array_filter((array) old('store_id', $multiValues('store')), 'is_string'));
                                    $scheduled = $document && \Carbon\Carbon::parse($document->issued_date)->startOfDay()->gt(today());
                                @endphp

                                <form
                                    method="POST"
                                    action="{{ route('documents.st.update', $document->id) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <p class="text-muted" style="font-size:.85rem;">
                                        Pilih minimal salah satu tujuan pemindahan (departemen, store, dan/atau posisi).
                                        Karyawan dipindah pada <strong>Tanggal Mulai</strong> dan dikembalikan ke penempatan semula
                                        sehari setelah <strong>Tanggal Selesai</strong>.
                                        @if ($scheduled)
                                            <br><strong>Terjadwal:</strong> karyawan belum dipindah, akan dipindah otomatis pada tanggal mulai.
                                        @else
                                            <br>Menyimpan perubahan akan mengembalikan pemindahan yang sekarang lalu menerapkan tujuan yang baru.
                                        @endif
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
                                            <select name="store_id[]" id="st-store" class="form-control select2" multiple
                                                data-placeholder="— Tidak diubah —">
                                                {{-- Urutan opsi = urutan yang dikirim form: yang terpilih ditulis dulu sesuai urutannya --}}
                                                @foreach ($pickedStores as $id)
                                                    @if ($s = $stores->firstWhere('id', $id))<option value="{{ $s->id }}" selected>{{ $s->name }}</option>@endif
                                                @endforeach
                                                @foreach ($stores->whereNotIn('id', $pickedStores) as $store)
                                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">
                                                Bisa pilih lebih dari satu; yang dipilih pertama (paling kiri) jadi lokasi utama.
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
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
            // Pertahankan urutan pilihan lokasi (form mengirim sesuai urutan opsi)
            $('#st-store').on('select2:select', function(e) {
                const opt = e.params.data.element;
                $(opt).detach();
                const lastPicked = $(this).find('option:selected').last();
                lastPicked.length ? lastPicked.after(opt) : $(this).prepend(opt);
                $(this).trigger('change.select2');
            });
        });
    </script>
@endpush
