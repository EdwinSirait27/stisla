@extends('layouts.app')
@section('title', 'Update Structures')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .avatar {
            position: relative;
        }

        .iframe-container {
            position: relative;
            overflow: hidden;
            padding-top: 56.25%;
            /* Aspect ratio 16:9 */
        }

        .iframe-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
            border: 1px solid #d1d1d1;
        }

        .form-control:focus {
            border-color: #6777ef;
            box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.25);
        }

        .form-control-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #34395e;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control-label i {
            color: #6777ef;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 25px 0 rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 10px 30px 0 rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f9f9f9;
            padding: 20px;
        }

        .card-header h6 {
            font-weight: 700;
            font-size: 16px;
            color: #34395e;
        }

        .card-body {
            padding: 30px;
        }

        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 10px;
        }

        .btn-secondary {
            background-color: #cdd3d8;
            border-color: #cdd3d8;
            color: #34395e;
        }

        .btn-secondary:hover {
            background-color: #b9bfc4;
            border-color: #b9bfc4;
        }

        .bg-gradient-dark {
            background: linear-gradient(310deg, #2dce89, #2dcec7);
            border: none;
        }

        .bg-gradient-dark:hover {
            background: linear-gradient(310deg, #26b179, #26b1a9);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .alert-secondary {
            background-color: #f8f9fa;
            border-color: #f1f2f3;
        }

        .alert-secondary .text-white {
            color: #6c757d !important;
        }

        .form-check {
            padding-left: 30px;
            margin-bottom: 10px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-top: 3px;
            margin-left: -30px;
            cursor: pointer;
        }

        .form-check-label {
            cursor: pointer;
        }

        .invalid-feedback {
            display: block;
            margin-top: 5px;
            font-size: 13px;
            color: #fc544b;
        }

        .alert-danger {
            background-color: #ffdede;
            border-color: #ffd0d0;
            color: #dc3545;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        select.form-control {
            height: 42px;
        }

        #role_summary {
            height: 200px;
            resize: vertical;
            /* biar masih bisa diubah manual */
        }

        #key_respon {
            height: 200px;
            resize: vertical;
            /* biar masih bisa diubah manual */
        }

        #qualifications {
            height: 200px;
            resize: vertical;
            /* biar masih bisa diubah manual */
        }

        #notes {
            height: 200px;
            resize: vertical;
            /* biar masih bisa diubah manual */
        }

        #notes_hr {
            height: 200px;
            resize: vertical;
            /* biar masih bisa diubah manual */
        }

        #notes_dir {
            height: 200px;
            resize: vertical;
            /* biar masih bisa diubah manual */
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Update Structures {{ $structure->submissionposition->positionRelation->name }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">
                        <a href="{{ route('pages.Structures') }}">Structures</a>
                    </div>
                    <div class="breadcrumb-item">Update Structure
                        {{ $structure->submissionposition->positionRelation->name }}</div>
                </div>
            </div>

            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Update Structure from manager') }}
                                        {{ $structure->submissionposition->submitter->employee_name }} -
                                        {{ $structure->submissionposition->positionRelation->name }}</h6>
                                </div>

                                <div class="card-body pt-4 p-3">
                                    {{-- Alert errors --}}
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    {{-- Alert success --}}
                                    @if (session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" id="alert-success"
                                            role="alert">
                                            <span class="alert-text">{{ session('success') }}</span>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close">
                                                <i class="fa fa-close" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    @endif

                                    {{-- Form start --}}
                                    <form id="departments-edit" action="{{ route('Structuresnew.update', $hashedId) }}"
                                        method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="row">
                                            {{-- Is Manager --}}
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="position_id" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Position') }}
                                                    </label>

                                                    <input type="text"
                                                        class="form-control @error('position_id') is-invalid @enderror"
                                                        id="position_id" name="position_id"
                                                        value="{{ old('position_id', $structure->submissionposition->positionRelation->name) }}"
                                                        disabled placeholder="Fill Position Name">
                                                    @error('position_id')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">

                                                <div class="form-group">
                                                    <label for="store_id" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Location') }}
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('store_id') is-invalid @enderror"
                                                        id="store_id" name="store_id"
                                                        value="{{ old('store_id', $structure->submissionposition->store->name) }}"
                                                        disabled placeholder="Fill Store Name">
                                                    @error('store_id')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="type" class="form-control-label">
                                                        <i class="fas fa-list"></i> {{ __('Type') }}
                                                    </label>
                                                    <div>
                                                        @foreach ($types as $type)
                                                            <div class="form-check">
                                                                <input
                                                                    class="form-check-input @error('type') is-invalid @enderror"
                                                                    type="checkbox" name="type[]"
                                                                    id="type_{{ $type }}"
                                                                    value="{{ $type }}"
                                                                    {{ in_array($type, explode(',', $structure->submissionposition->type)) ? 'checked' : '' }}
                                                                    disabled>

                                                                <label class="form-check-label"
                                                                    for="type_{{ $type }}">
                                                                    {{ $type }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @error('type')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">

                                                <div class="form-group">
                                                    <label for="role_summary" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Role Summary') }}
                                                    </label>
                                                    <textarea id="role_summary" name="role_summary" class="form-control @error('role_summary') is-invalid @enderror"
                                                        rows="8" disabled>{{ html_entity_decode(strip_tags(old('role_summary', $structure->submissionposition->role_summary))) }}</textarea>

                                                    @error('role_summary')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">

                                                <div class="form-group">
                                                    <label for="key_respon" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Key Responsibility') }}
                                                    </label>
                                                    <textarea id="key_respon" name="key_respon" class="form-control @error('key_respon') is-invalid @enderror"
                                                        rows="8" disabled>{{ html_entity_decode(strip_tags(old('key_respon', $structure->submissionposition->key_respon))) }}</textarea>
                                                    @error('key_respon')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="qualifications" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Qualifications') }}
                                                    </label>
                                                    <textarea id="qualifications" name="qualifications"
                                                        class="form-control @error('qualifications') is-invalid @enderror" rows="8" disabled>{{ html_entity_decode(strip_tags(old('qualifications', $structure->submissionposition->qualifications))) }}</textarea>

                                                    @error('qualifications')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="notes" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Notes From Manager') }}
                                                    </label>

                                                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="8"
                                                        placeholder="message from manager to HR" disabled>{{ old('notes', $structure->submissionposition->notes) }}</textarea>

                                                    @error('notes')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="notes_hr" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Notes HR to DIR') }}
                                                    </label>
                                                    <textarea id="notes_hr" name="notes_hr" class="form-control @error('notes_hr') is-invalid @enderror" rows="8"
                                                        placeholder="message from HR to DIR" disabled>{{ old('notes_hr', $structure->submissionposition->notes_hr) }}</textarea>

                                                    @error('notes_hr')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="salary_hr" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Salary by HR') }}
                                                    </label>
                                                    <input type="number" id="salary_hr" name="salary_hr"
                                                        class="form-control @error('salary_hr') is-invalid @enderror"
                                                        value="{{ old('salary_hr', $structure->submissionposition->salary_hr) }}"
                                                        placeholder="numbers only" pattern="[0-9]+" inputmode="numeric"
                                                        disabled>
                                                    @error('salary_hr')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="salary_hr_end" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('To') }}
                                                    </label>
                                                    <input type="number" id="salary_hr_end" name="salary_hr_end"
                                                        class="form-control @error('salary_hr_end') is-invalid @enderror"
                                                        value="{{ old('salary_hr_end', $structure->submissionposition->salary_hr_end) }}"
                                                        placeholder="numbers only" pattern="[0-9]+" inputmode="numeric"
                                                        disabled>
                                                    @error('salary_hr_end')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="notes_dir" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Notes DIR to HR') }}
                                                    </label>
                                                    <textarea id="notes_dir" name="notes_dir" class="form-control @error('notes_dir') is-invalid @enderror"
                                                        rows="8" placeholder="message from DIR to HR" disabled>{{ old('notes_dir', $structure->submissionposition->notes_dir) }}</textarea>

                                                    @error('notes_dir')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>



                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="salary_counter" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Approved salary by DIR') }}
                                                    </label>
                                                    <input type="number" id="salary_counter" name="salary_counter"
                                                        class="form-control @error('salary_counter') is-invalid @enderror"
                                                        value="{{ old('salary_counter', $structure->submissionposition->salary_counter) }}"
                                                        placeholder="numbers only" pattern="[0-9]+" inputmode="numeric"
                                                        disabled>
                                                    @error('salary_counter')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="salary_counter_end" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('To') }}
                                                    </label>
                                                    <input type="number" id="salary_counter_end"
                                                        name="salary_counter_end"
                                                        class="form-control @error('salary_counter_end') is-invalid @enderror"
                                                        value="{{ old('salary_counter_end', $structure->submissionposition->salary_counter_end) }}"
                                                        placeholder="numbers only" pattern="[0-9]+" inputmode="numeric"
                                                        disabled>
                                                    @error('salary_counter_end')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="status" class="form-control-label">
                                                        <i class="fas fa-book"></i> {{ __('Status') }}
                                                    </label>
                                                    <select id="status" name="status"
                                                        class="form-control select2 @error('status') is-invalid @enderror"
                                                        disabled>
                                                        <option value="">-- Choose Status--</option>
                                                        @foreach ($statuses as $value)
                                                            <option value="{{ $value }}"
                                                                {{ old('status', $structure->status ?? '') == $value ? 'selected' : '' }}>
                                                                {{ $value }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @error('status')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>







                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="parent_id" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Direct Superior') }}
                                                    </label>
                                                    <select name="parent_id"
                                                        class="form-control select2 @error('parent_id') is-invalid @enderror">
                                                        <option value="">Choose Superior</option>
                                                        @foreach ($parents as $id => $parentName)
                                                            <option value="{{ $id }}"
                                                                {{ old('parent_id', $structure->parent_id) == $id ? 'selected' : '' }}>
                                                                {{ $parentName }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('parent_id')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="secondary_supervisors" class="form-control-label">
                                                        <i class="fas fa-user-friends"></i> Additional / Secondary
                                                        Superiors
                                                    </label>
                                                    <select name="secondary_supervisors[]"
                                                        class="form-control select2 @error('secondary_supervisors') is-invalid @enderror"
                                                        multiple>
                                                        @foreach ($parents as $id => $parentName)
                                                            <option value="{{ $id }}"
                                                                @if (collect(old('secondary_supervisors', $structure->secondarySupervisors->pluck('id')->toArray()))->contains($id)) selected @endif>
                                                                {{ $parentName }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('secondary_supervisors')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="is_manager" id="is_manager"
                                                            value="1"
                                                            class="form-check-input @error('is_manager') is-invalid @enderror"
                                                            {{ old('is_manager', $structure->is_manager) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="is_manager">
                                                            <i class="fas fa-id-card"></i> {{ __('Is Manager?') }}
                                                        </label>
                                                        @error('is_manager')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-secondary mt-4" role="alert">
                                            <span class="text-dark">
                                                <strong>Important Note:</strong><br>
                                                - Superior can be empty.<br>
                                                - Is Manager can be empty.<br>
                                            </span>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('pages.Structuresnew') }}" class="btn btn-secondary me-2">
                                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                                            </a>
                                            <button type="submit" id="edit-btn" class="btn bg-primary">
                                                <i class="fas fa-save"></i> {{ __('Update') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
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
    <script src="{{ asset('node_modules/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
    <script>
        document.getElementById('edit-btn').addEventListener('click', function(e) {
            e.preventDefault(); // Mencegah pengiriman form langsung
            Swal.fire({
                title: 'Are You Sure?',
                text: "Make sure the data you entered is correct!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Assign!',
                cancelButtonText: 'Abort'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika pengguna mengkonfirmasi, submit form
                    document.getElementById('departments-edit').submit();
                }
            });
        });
    </script>
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
{{-- <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="salary_hr" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('Salary by HR') }}
                                                    </label>

                                                    <input type="number" id="salary_hr" name="salary_hr"
                                                        class="form-control @error('salary_hr') is-invalid @enderror"
                                                        value="{{ old('salary_hr', $structure->submissionposition->salary_hr) }}"
                                                        placeholder="numbers only" pattern="[0-9]+" inputmode="numeric"
                                                        disabled>
                                                    @error('salary_hr')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                               <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="salary_hr_end" class="form-control-label">
                                                        <i class="fas fa-file-alt"></i> {{ __('To') }}
                                                    </label>

                                                    <input type="number" id="salary_hr_end" name="salary_hr_end"
                                                        class="form-control @error('salary_hr_end') is-invalid @enderror"
                                                        value="{{ old('salary_hr_end', $structure->submissionposition->salary_hr_end) }}"
                                                        placeholder="numbers only" pattern="[0-9]+" inputmode="numeric"
                                                        disabled>
                                                    @error('salary_hr_end')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div> --}}
{{-- @section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Update Structures {{ $structure->position->name }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('pages.Structures') }}">Structures</a></div>
                    <div class="breadcrumb-item">Update Structure {{ $structure->position->name }}</div>
                </div>
            </div>

            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Update Structure') }}
                                        {{ $structure->position->name }}</h6>
                                </div>
                                <div class="card-body pt-4 p-3">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if (session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" id="alert-success"
                                            role="alert">
                                            <span class="alert-text">
                                                {{ session('success') }}
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close">
                                                <i class="fa fa-close" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <form id="departments-edit" action="{{ route('Structuresnew.update', $hashedId) }}"
                                        method="POST">
                                        @csrf
                                        @method('PUT')


                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="is_manager" id="is_manager"
                                                            value="1"
                                                            class="form-check-input @error('is_manager') is-invalid @enderror"
                                                            {{ old('is_manager', $structure->is_manager) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="is_manager">
                                                            <i class="fas fa-id-card"></i> {{ __('Is Manager?') }}
                                                        </label>
                                                    @error('is_manager')
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>


                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="parent_id" class="form-control-label">
                                                    <i class="fas fa-id-card"></i> {{ __('Direct Superior') }}
                                                </label>
                                                    <select name="parent_id"
                                                        class="form-control select2 @error('parent_id') is-invalid @enderror">
                                                        <option value="">Choose Superior</option>
                                                        @foreach ($parents as $id => $parentName)
                                                            <option value="{{ $id }}"
                                                                {{ old('parent_id', $structure->parent_id) == $id ? 'selected' : '' }}>
                                                                {{ $parentName }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('parent_id')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            </div>
                                       

                                        <div class="alert alert-secondary mt-4" role="alert">
                                            <span class="text-dark">
                                                <strong>Important Note:</strong>
                                                - Superior can be empty.<br>
                                                - Is Manager can be empty.<br>
                                            </span>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('pages.Structuresnew') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                                            </a>
                                            <button type="submit" id="edit-btn" class="btn bg-primary">
                                                <i class="fas fa-save"></i> {{ __('Update') }}
                                            </button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection --}}
{{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_manager" id="is_manager"
                                                        value="1"
                                                        class="form-check-input @error('is_manager') is-invalid @enderror"
                                                        {{ old('is_manager', $structure->is_manager) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_manager">
                                                        <i class="fas fa-id-card"></i> {{ __('Is Manager?') }}
                                                    </label>
                                                    @error('is_manager')
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div> --}}
