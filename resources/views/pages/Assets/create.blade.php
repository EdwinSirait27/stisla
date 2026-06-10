@extends('layouts.app')
@section('title', 'Create Asset')
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
    </style>
@endpush
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Create Asset</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('pages.Assets') }}">Assets</a></div>
                    <div class="breadcrumb-item">Create Assets</div>
                </div>
            </div>
            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Create Assets') }}</h6>
                                </div>
                                <div class="card-body pt-4 p-3">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
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
                                    <form id="position-create" action="{{ route('Assets.store') }}" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="asset_category_id" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Asset Category') }}
                                                    </label>
                                                    <div>
                                                        <select
                                                            class="form-control select2 @error('asset_category_id') is-invalid @enderror"
                                                            id="asset_category_id" name="asset_category_id" required>
                                                            <option value="">Select Asset Category</option>
                                                            @foreach ($assetcategories as $assetcategory)
                                                                <option value="{{ $assetcategory->id }}"
                                                                    {{ old('asset_category_id') == $assetcategory->id ? 'selected' : '' }}>
                                                                    {{ $assetcategory->asset_category_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('asset_category_id')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="asset_name" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Asset Name') }}
                                                    </label>
                                                    <div>
                                                        <input
                                                            type="text"
                                                            class="form-control @error('asset_name') is-invalid @enderror"
                                                            id="asset_name" name="asset_name" value="{{ old('asset_name') }}" required>
                                                        @error('asset_name')
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
                                                    <label for="uoms" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('UOM') }}
                                                    </label>
                                                    <div>
                                                        <select
                                                            class="form-control select2 @error('uoms') is-invalid @enderror"
                                                            id="uoms" name="uoms" required>
                                                            <option value="">Select UOM</option>

                                                            @foreach ($uoms as $value => $label)
                                                                <option value="{{ $value }}"
                                                                    {{ old('uom') == $value ? 'selected' : '' }}>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('uom')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="qty" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Quantity') }}
                                                    </label>
                                                    <div>
                                                        <input type="text"
                                                            class="form-control @error('qty') is-invalid @enderror"
                                                            id="qty" placeholder="1" name="qty"
                                                            value="{{ old('qty') }}" required>
                                                        @error('qty')
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
                                                    <label for="serial_number" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Serial Number') }}
                                                    </label>
                                                    <div>
                                                        <input type="text"
                                                            class="form-control @error('serial_number') is-invalid @enderror"
                                                            id="serial_number" placeholder="asus-x550z" name="serial_number"
                                                            value="{{ old('serial_number') }}" required>

                                                        @error('serial_number')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                       
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="brand" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Brand') }}
                                                    </label>
                                                    <div>
                                                        <input type="text"
                                                            class="form-control @error('brand') is-invalid @enderror"
                                                            id="brand" name="brand" placeholder="asus"
                                                            value="{{ old('brand') }}" required>
                                                        @error('brand')
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
                                                    <label for="model" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Model') }}
                                                    </label>
                                                    <div>
                                                        <input type="text"
                                                            class="form-control @error('model') is-invalid @enderror"
                                                            id="model" name="model" placeholder="x550z"
                                                            value="{{ old('model') }}" required>

                                                        @error('model')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="purchase_date" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Purchase Date') }}
                                                    </label>
                                                    <div>
                                                        <input type="date"
                                                            class="form-control @error('purchase_date') is-invalid @enderror"
                                                            id="purchase_date" name="purchase_date"
                                                            value="{{ old('purchase_date') }}" required>
                                                        @error('purchase_date')
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
                                                    <label for="purchase_price" class="form-control-label">
                                                        <i class="fas fa-money-bill"></i> {{ __('Purchase Price') }}
                                                    </label>

                                                    <div class="input-group">
                                                        <span class="input-group-text">Rp</span>

                                                        <input
                                                            class="form-control @error('purchase_price') is-invalid @enderror"
                                                            type="text" id="purchase_price" name="purchase_price"
                                                            value="{{ old('purchase_price', isset($asset) ? number_format($asset->purchase_price, 2, ',', '.') : '') }}"
                                                            placeholder="12.000.000,00">

                                                        @error('purchase_price')
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        
                                           
                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="notes" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Notes') }}
                                                    </label>
                                                    <div>
                                                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4"
                                                            placeholder="Enter Notes">{{ old('notes') }}</textarea>

                                                        @error('notes')
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
                                                <br> - please use English to get used to it.
                                                <br> - Before creating data, please check first whether there is already
                                                similar or identical data to avoid double input.
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('pages.Assets') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                                            </a>
                                            <button type="submit" id="create-btn" class="btn bg-primary">
                                                <i class="fas fa-save"></i> {{ __('Create') }}
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
    const purchasePrice = document.getElementById('purchase_price');

    function formatRupiah(value) {

        value = value.replace(/[^\d,]/g, '');

        let split = value.split(',');
        let number = split[0];
        let decimal = split[1];

        let sisa = number.length % 3;
        let rupiah = number.substr(0, sisa);
        let ribuan = number.substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = decimal !== undefined
            ? rupiah + ',' + decimal.substring(0, 2)
            : rupiah;

        return rupiah;
    }

    purchasePrice.addEventListener('input', function(e) {
        this.value = formatRupiah(this.value);
    });

    purchasePrice.addEventListener('blur', function(e) {

        if (this.value !== '' && !this.value.includes(',')) {
            this.value += ',00';
        }

        if (this.value.endsWith(',')) {
            this.value += '00';
        }

        let split = this.value.split(',');

        if (split.length === 2 && split[1].length === 1) {
            this.value += '0';
        }
    });
</script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
    <script>
        document.getElementById('create-btn').addEventListener('click', function(e) {
            e.preventDefault();
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
                    document.getElementById('position-create').submit();
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
