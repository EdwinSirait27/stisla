@extends('layouts.app')
@section('title', 'Create Document')
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush
<style>
    .card {
        border: none;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08);
        border-radius: 0.5rem;
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
                <h1>Buat Dokumen</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-file-signature"></i> Buat Dokumen</h6>
                            </div>
                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('documents.store') }}">
                                    @csrf

                                    <div class="form-group">
                                        <label class="form-label">Tipe Dokumen</label>
                                        <select id="document-type" name="document_type"
                                            class="form-control select2" required>
                                            <option value="ST" {{ old('document_type', 'ST') == 'ST' ? 'selected' : '' }}>
                                                Surat Tugas (ST)
                                            </option>
                                            <option value="OL" {{ old('document_type') == 'OL' ? 'selected' : '' }}>
                                                Offering Letter (Coming Soon)
                                            </option>
                                        </select>
                                    </div>

                                    <div id="type-not-supported" class="alert alert-info" style="display:none;">
                                        Tipe dokumen ini belum bisa dibuat manual dari halaman ini.
                                    </div>

                                    <div id="st-fields">
                                        <p class="text-muted" style="font-size:.85rem;">
                                            Saat ini hanya tipe dokumen <strong>Surat Tugas (ST)</strong> yang bisa
                                            dibuat manual dari sini. Tipe dokumen lain (SPK, SPPRP, PAK, dsb)
                                            diterbitkan otomatis oleh sistem.
                                        </p>

                                        <div class="form-group">
                                            <label class="form-label">Karyawan</label>
                                            <select name="employee_id" class="form-control select2"
                                                data-placeholder="Pilih karyawan">
                                                <option value=""></option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}"
                                                        {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                        {{ $employee->employee_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <p class="text-muted" style="font-size:.85rem;">
                                            Pilih minimal salah satu tujuan pemindahan (departemen, store, dan/atau
                                            posisi). Setelah <strong>Tanggal Selesai</strong> terlewati, karyawan
                                            otomatis dikembalikan ke penempatan semula.
                                        </p>

                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label class="form-label">Departemen Tujuan</label>
                                                <select name="department_id" class="form-control select2">
                                                    <option value="">— Tidak diubah —</option>
                                                    @foreach ($departments as $department)
                                                        <option value="{{ $department->id }}"
                                                            {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                            {{ $department->department_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label class="form-label">Lokasi Tujuan</label>
                                                <select name="store_id[]" class="form-control select2" multiple
                                                    data-placeholder="— Tidak diubah —">
                                                    @foreach ($stores as $store)
                                                        <option value="{{ $store->id }}"
                                                            {{ in_array($store->id, old('store_id', [])) ? 'selected' : '' }}>
                                                            {{ $store->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">
                                                    Bisa pilih lebih dari satu lokasi; lokasi pertama yang dipilih
                                                    jadi lokasi utama.
                                                </small>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label class="form-label">Posisi Tujuan</label>
                                                <select name="position_id" class="form-control select2">
                                                    <option value="">— Tidak diubah —</option>
                                                    @foreach ($positions as $position)
                                                        <option value="{{ $position->id }}"
                                                            {{ old('position_id') == $position->id ? 'selected' : '' }}>
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
                                                    value="{{ old('issued_date') }}">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label class="form-label">Tanggal Selesai (Expired Date)</label>
                                                <input type="date" name="expired_date" class="form-control"
                                                    value="{{ old('expired_date') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <a href="{{ route('document.index') }}" class="btn btn-light border">Batal</a>
                                        <button type="submit" id="btn-submit-document" class="btn btn-primary">
                                            Buat Dokumen
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

            var $stFields  = $('#st-fields');
            var $notice    = $('#type-not-supported');
            var $submitBtn = $('#btn-submit-document');
            var $stRequiredFields = $stFields.find('select[name="employee_id"], input[name="issued_date"], input[name="expired_date"]');

            function toggleDocumentTypeFields() {
                var isSupported = $('#document-type').val() === 'ST';

                $stFields.toggle(isSupported);
                $notice.toggle(!isSupported);
                $submitBtn.prop('disabled', !isSupported);
                $stRequiredFields.prop('required', isSupported);
            }

            $('#document-type').on('change', toggleDocumentTypeFields);
            toggleDocumentTypeFields();
        });
    </script>
@endpush
