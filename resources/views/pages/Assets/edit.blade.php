@extends('layouts.app')

@section('title', 'Edit Asset')
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
            <h1>Edit Asset</h1>

            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item">
                    <a href="{{ route('pages.Assets') }}">Assets</a>
                </div>
                <div class="breadcrumb-item">Edit Asset</div>
            </div>
        </div>

        <div class="section-body">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">

                        <div class="card">

                            <div class="card-header pb-0 px-3">
                                <h6 class="mb-0">Edit Asset</h6>
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

                                <form id="asset-edit"
                                      action="{{ route('Assets.update', $asset->id) }}"
                                      method="POST">

                                    @csrf
                                    @method('PUT')

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">

                                                <label for="asset_category_id" class="form-control-label">
                                                    <i class="fas fa-layer-group"></i>
                                                    Asset Category
                                                </label>

                                                <select
                                                    class="form-control select2 @error('asset_category_id') is-invalid @enderror"
                                                    id="asset_category_id"
                                                    name="asset_category_id"
                                                    required>

                                                    <option value="">Select Asset Category</option>

                                                    @foreach ($assetcategories as $assetcategory)
                                                        <option value="{{ $assetcategory->id }}"
                                                            {{ old('asset_category_id', $asset->asset_category_id) == $assetcategory->id ? 'selected' : '' }}>
                                                            {{ $assetcategory->asset_category_name }}
                                                        </option>
                                                    @endforeach

                                                </select>

                                                @error('asset_category_id')
                                                    <span class="invalid-feedback">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror

                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">

                                                <label for="asset_name" class="form-control-label">
                                                    <i class="fas fa-box"></i>
                                                    Asset Name
                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control @error('asset_name') is-invalid @enderror"
                                                    id="asset_name"
                                                    name="asset_name"
                                                    value="{{ old('asset_name', $asset->asset_name) }}"
                                                    required>

                                                @error('asset_name')
                                                    <span class="invalid-feedback">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror

                                            </div>
                                        </div>

                                    </div>

                                    <div class="row mt-3">

                                        <div class="col-md-6">
                                            <div class="form-group">

                                                <label for="uom" class="form-control-label">
                                                    <i class="fas fa-balance-scale"></i>
                                                    UOM
                                                </label>

                                                <select
                                                    class="form-control select2 @error('uom') is-invalid @enderror"
                                                    id="uom"
                                                    name="uom"
                                                    required>

                                                    <option value="">Select UOM</option>

                                                    @foreach ($uoms as $value => $label)
                                                        <option value="{{ $value }}"
                                                            {{ old('uom', $asset->uom) == $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach

                                                </select>

                                                @error('uom')
                                                    <span class="invalid-feedback">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror

                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">

                                                <label for="serial_number" class="form-control-label">
                                                    <i class="fas fa-barcode"></i>
                                                    Serial Number
                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control @error('serial_number') is-invalid @enderror"
                                                    id="serial_number"
                                                    name="serial_number"
                                                    value="{{ old('serial_number', $asset->serial_number) }}"
                                                    placeholder="ASUS-X550Z">

                                                @error('serial_number')
                                                    <span class="invalid-feedback">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror

                                            </div>
                                        </div>

                                    </div>

                                    <div class="row mt-3">

                                        <div class="col-md-6">
                                            <div class="form-group">

                                                <label for="brand" class="form-control-label">
                                                    <i class="fas fa-tags"></i>
                                                    Brand
                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control @error('brand') is-invalid @enderror"
                                                    id="brand"
                                                    name="brand"
                                                    value="{{ old('brand', $asset->brand) }}"
                                                    placeholder="ASUS">

                                                @error('brand')
                                                    <span class="invalid-feedback">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror

                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">

                                                <label for="model" class="form-control-label">
                                                    <i class="fas fa-laptop"></i>
                                                    Model
                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control @error('model') is-invalid @enderror"
                                                    id="model"
                                                    name="model"
                                                    value="{{ old('model', $asset->model) }}"
                                                    placeholder="X550Z">

                                                @error('model')
                                                    <span class="invalid-feedback">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror

                                            </div>
                                        </div>

                                    </div>

                                    <div class="row mt-3">

                                        <div class="col-md-6">
                                            <div class="form-group">

                                                <label for="purchase_date" class="form-control-label">
                                                    <i class="fas fa-calendar"></i>
                                                    Purchase Date
                                                </label>

                                                <input
                                                    type="date"
                                                    class="form-control @error('purchase_date') is-invalid @enderror"
                                                    id="purchase_date"
                                                    name="purchase_date"
                                                    value="{{ old('purchase_date', $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d') : '') }}">

                                                @error('purchase_date')
                                                    <span class="invalid-feedback">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror

                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">

                                                <label for="purchase_price" class="form-control-label">
                                                    <i class="fas fa-money-bill"></i>
                                                    Purchase Price
                                                </label>

                                                <div class="input-group">

                                                    <span class="input-group-text">Rp</span>

                                                    <input
                                                        type="text"
                                                        class="form-control @error('purchase_price') is-invalid @enderror"
                                                        id="purchase_price"
                                                        name="purchase_price"
                                                        value="{{ old('purchase_price', $asset->purchase_price ? number_format($asset->purchase_price, 2, ',', '.') : '') }}"
                                                        placeholder="12.000.000,00">

                                                </div>

                                                @error('purchase_price')
                                                    <span class="invalid-feedback d-block">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror

                                            </div>
                                        </div>

                                    </div>

                                    <div class="row mt-3">

                                        <div class="col-md-6">
                                            <div class="form-group">

                                                <label for="status" class="form-control-label">
                                                    <i class="fas fa-info-circle"></i>
                                                    Status
                                                </label>

                                                <select
                                                    class="form-control select2 @error('status') is-invalid @enderror"
                                                    id="status"
                                                    name="status">

                                                    <option value="">Select Status</option>

                                                    @foreach ($statuses as $value => $label)
                                                        <option value="{{ $value }}"
                                                            {{ old('status', $asset->status) == $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach

                                                </select>

                                                @error('status')
                                                    <span class="invalid-feedback">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror

                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">

                                                <label for="notes" class="form-control-label">
                                                    <i class="fas fa-sticky-note"></i>
                                                    Notes
                                                </label>

                                                <textarea
                                                    class="form-control @error('notes') is-invalid @enderror"
                                                    id="notes"
                                                    name="notes"
                                                    rows="4"
                                                    placeholder="Enter Notes">{{ old('notes', $asset->notes) }}</textarea>

                                                @error('notes')
                                                    <span class="invalid-feedback">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror

                                            </div>
                                        </div>

                                    </div>

                                    <div class="d-flex justify-content-end mt-4">

                                        <a href="{{ route('pages.Assets') }}"
                                           class="btn btn-secondary">
                                            <i class="fas fa-times"></i>
                                            Cancel
                                        </a>

                                        <button type="submit"
                                                id="update-btn"
                                                class="btn btn-primary">
                                            <i class="fas fa-save"></i>
                                            Update
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

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        $('.select2').select2();
    });
</script>

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

    purchasePrice.addEventListener('input', function () {
        this.value = formatRupiah(this.value);
    });

    purchasePrice.addEventListener('blur', function () {

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
    document.getElementById('update-btn').addEventListener('click', function (e) {

        e.preventDefault();

        Swal.fire({
            title: 'Are You Sure?',
            text: "Make sure the data you entered is correct!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Update!',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {
                document.getElementById('asset-edit').submit();
            }

        });

    });
</script>

@endpush