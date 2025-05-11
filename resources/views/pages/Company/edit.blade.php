@extends('layouts.app')
@section('title', 'Update Companies')
@push('style')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
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

        /* Additional CSS for improved styling */
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
        .iframe-container {
            position: relative;
            overflow: hidden;
            padding-top: 56.25%;

        }

        .iframe-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
    </style>
@endpush
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Update Companies {{ $company->name }}</h1>
                <div class="section-header-breadcrumb">
                    {{-- <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div> --}}
                    <div class="breadcrumb-item"><a href="{{ route('pages.Company') }}">Company</a></div>
                    <div class="breadcrumb-item">Update Companies {{ $company->name }}</div>
                </div>
            </div>

            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Update Companies') }} {{ $company->name }}</h6>
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

                                    <form id="company-edit" action="{{ route('Company.update', $hashedId) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Companies Name') }}
                                                    </label>
                                                    <div>
                                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                                            name="name" value="{{ old('name', $company->name) }}"
                                                            placeholder="exmple:MJM" required>
                                                        @error('name')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>







                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="foto" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Image Companies') }}
                                                    </label>
                                                    <div>
                                                        {{-- Preview Image --}}
                                                        <div class="mb-2">
                                                            @if ($company->foto)
                                                                <img id="preview-image" src="{{ asset('storage/' . $company->foto) }}" alt="Preview" class="img-thumbnail" width="150">
                                                            @else
                                                                <img id="preview-image" src="https://via.placeholder.com/150" alt="Preview" class="img-thumbnail" width="150">
                                                            @endif
                                                        </div>
                                            
                                                        {{-- File Input --}}
                                                        <input class="form-control @error('foto') is-invalid @enderror"
                                                               type="file" id="foto" name="foto" accept="image/*" onchange="previewImage(event)">
                                                        
                                                        @error('foto')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            </div>



                                            <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="address" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('Store Address') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control @error('address') is-invalid @enderror"
                                                            value="{{ old('address', $company->address ?? '') }}"
                                                            type="text" id="address" name="address"
                                                            aria-describedby="info-address"
                                                            placeholder="Insert Store Address" maxlength="255">
                                                        @error('address')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                        </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="npwp" class="form-control-label">
                                                        <i class="fas fa-id-card"></i> {{ __('NPWP') }}
                                                    </label>
                                                    <div>
                                                        <input class="form-control @error('npwp') is-invalid @enderror"
                                                            value="{{ old('npwp', $company->npwp ?? '') }}"
                                                            type="text" id="npwp" name="npwp"
                                                            aria-describedby="info-npwp"
                                                            placeholder="Insert NPWP Companies" maxlength="255">
                                                        @error('npwp')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                        
                                            


                                        <div class="alert alert-secondary mt-4" role="alert">
                                            <span class="text-dark">
                                                <strong>Important Note:</strong> <br>
                                                - If a Store name is already registered, you cannot register it again.<br>
                                                - If a Store Manager is already registered, you cannot register it again.<br>
                                                
                                            </span>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('pages.Company') }}" class="btn btn-secondary">
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
                      document.getElementById('company-edit').submit();
                  }
              });
          });
    </script>
    <script>
     
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const output = document.getElementById('preview-image');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
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
