@extends('layouts.app')
@section('title', 'Update Leave Type')
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

        /* Blok aturan cuti khusus */
        .special-rules-box {
            border: 1px dashed #d1d1d1;
            border-radius: 12px;
            padding: 20px;
            margin-top: 10px;
            background-color: #fbfbfb;
        }

        .form-hint {
            font-size: 12px;
            color: #98a6ad;
            margin-top: 4px;
            display: block;
        }
    </style>
@endpush
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Update Leave Type {{ $type->name }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('pages.Leavestype') }}">Leave Type</a></div>
                    <div class="breadcrumb-item">Update Leave Type {{ $type->name }}</div>
                </div>
            </div>

            <div class="section-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0 px-3">
                                    <h6 class="mb-0">{{ __('Update Leave Type') }} {{ $type->name }}</h6>
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
                                    <form id="position-edit" action="{{ route('Leavestype.update', $hashedId) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="form-control-label">
                                                        <i class="fas fa-user"></i> {{ __('Type Name') }}
                                                    </label>
                                                    <div>
                                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                                            name="name" value="{{ old('name', $type->name) }}"
                                                            placeholder="Annual Leaves" required>
                                                        <span class="form-hint">Nama yang muncul di dropdown pengajuan cuti karyawan.</span>
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
                                                    <label for="default_balance" class="form-control-label">
                                                        <i class="fas fa-calendar-check"></i> {{ __('Default Balance (days)') }}
                                                    </label>
                                                    <div>
                                                        <input type="number"
                                                            class="form-control @error('default_balance') is-invalid @enderror"
                                                            id="default_balance" name="default_balance"
                                                            value="{{ old('default_balance', $type->default_balance) }}"
                                                            min="0" max="365" step="0.5"
                                                            placeholder="mis. 90">
                                                        <span class="form-hint">Jatah saldo per karyawan per tahun. Kosongkan bila tidak memakai saldo.</span>
                                                        @error('default_balance')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">
                                                        <i class="fas fa-cog"></i> {{ __('Options') }}
                                                    </label>

                                                    <div class="form-check">
                                                        <input type="hidden" name="is_paid" value="0">
                                                        <input type="checkbox" class="form-check-input" id="is_paid"
                                                            name="is_paid" value="1"
                                                            {{ old('is_paid', $type->is_paid ? '1' : '0') == '1' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="is_paid">
                                                            {{ __('Paid leave (cuti dibayar)') }}
                                                        </label>
                                                    </div>

                                                    <div class="form-check">
                                                        <input type="hidden" name="is_active" value="0">
                                                        <input type="checkbox" class="form-check-input" id="is_active"
                                                            name="is_active" value="1"
                                                            {{ old('is_active', $type->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="is_active">
                                                            {{ __('Active (muncul di dropdown pengajuan)') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr>

                                        {{-- ── SAKLAR: cuti khusus ── --}}
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="hidden" name="is_special" value="0">
                                                <input type="checkbox" class="form-check-input" id="is_special"
                                                    name="is_special" value="1"
                                                    {{ old('is_special', $type->is_special ? '1' : '0') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_special">
                                                    <strong>{{ __('Cuti ini punya syarat khusus') }}</strong>
                                                </label>
                                            </div>
                                            <span class="form-hint">
                                                Centang bila cuti dibatasi gender, status menikah, status kepegawaian,
                                                wajib lampiran, atau durasinya dikunci.
                                                Biarkan kosong untuk cuti biasa seperti Annual Leave.
                                            </span>
                                        </div>

                                        {{-- ── Blok aturan (muncul bila is_special dicentang) ── --}}
                                        <div class="special-rules-box" id="special-rules-box" style="display:none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="gender_rule" class="form-control-label">
                                                            <i class="fas fa-venus-mars"></i> {{ __('Hanya untuk gender') }}
                                                        </label>
                                                        @php $gr = old('gender_rule', $type->gender_rule ?? 'all'); @endphp
                                                        <select class="form-control @error('gender_rule') is-invalid @enderror"
                                                            id="gender_rule" name="gender_rule">
                                                            <option value="all" {{ $gr == 'all' ? 'selected' : '' }}>Semua</option>
                                                            <option value="male" {{ $gr == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                                            <option value="female" {{ $gr == 'female' ? 'selected' : '' }}>Perempuan</option>
                                                        </select>
                                                        @error('gender_rule')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="roster_day_type" class="form-control-label">
                                                            <i class="fas fa-th"></i> {{ __('Tampil di roster sebagai') }}
                                                        </label>
                                                        @php $rdt = old('roster_day_type', $type->roster_day_type ?? 'Leave'); @endphp
                                                        <select class="form-control @error('roster_day_type') is-invalid @enderror"
                                                            id="roster_day_type" name="roster_day_type">
                                                            <option value="Leave" {{ $rdt == 'Leave' ? 'selected' : '' }}>Leave</option>
                                                            <option value="Cuti Melahirkan" {{ $rdt == 'Cuti Melahirkan' ? 'selected' : '' }}>Cuti Melahirkan</option>
                                                        </select>
                                                        <span class="form-hint">Pilihan dibatasi karena tiap tipe butuh warna di grid roster.</span>
                                                        @error('roster_day_type')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="fixed_days" class="form-control-label">
                                                            <i class="fas fa-lock"></i> {{ __('Durasi dikunci (hari)') }}
                                                        </label>
                                                        <input type="number"
                                                            class="form-control @error('fixed_days') is-invalid @enderror"
                                                            id="fixed_days" name="fixed_days"
                                                            value="{{ old('fixed_days', $type->fixed_days) }}"
                                                            min="1" max="365" placeholder="mis. 90">
                                                        <span class="form-hint">Karyawan tidak bisa mengubah durasi. Kosongkan bila durasi bebas.</span>
                                                        @error('fixed_days')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="max_days" class="form-control-label">
                                                            <i class="fas fa-arrow-up"></i> {{ __('Durasi maksimal (hari)') }}
                                                        </label>
                                                        <input type="number"
                                                            class="form-control @error('max_days') is-invalid @enderror"
                                                            id="max_days" name="max_days"
                                                            value="{{ old('max_days', $type->max_days) }}"
                                                            min="1" max="365" placeholder="mis. 3">
                                                        <span class="form-hint">Hanya dipakai bila durasi TIDAK dikunci.</span>
                                                        @error('max_days')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="allowed_status" class="form-control-label">
                                                            <i class="fas fa-id-badge"></i> {{ __('Hanya status kepegawaian') }}
                                                        </label>
                                                        <input type="text"
                                                            class="form-control @error('allowed_status') is-invalid @enderror"
                                                            id="allowed_status" name="allowed_status"
                                                            value="{{ old('allowed_status', $type->allowed_status) }}"
                                                            placeholder="mis. PKWT">
                                                        <span class="form-hint">
                                                            Pisahkan dengan koma bila lebih dari satu.
                                                            Kosongkan = semua status (DW &amp; On Job Training tetap selalu ditolak).
                                                        </span>
                                                        @error('allowed_status')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-control-label">
                                                            <i class="fas fa-check-double"></i> {{ __('Syarat tambahan') }}
                                                        </label>

                                                        <div class="form-check">
                                                            <input type="hidden" name="require_married" value="0">
                                                            <input type="checkbox" class="form-check-input" id="require_married"
                                                                name="require_married" value="1"
                                                                {{ old('require_married', $type->require_married ? '1' : '0') == '1' ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="require_married">
                                                                {{ __('Wajib sudah menikah') }}
                                                            </label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input type="hidden" name="require_attachment" value="0">
                                                            <input type="checkbox" class="form-check-input" id="require_attachment"
                                                                name="require_attachment" value="1"
                                                                {{ old('require_attachment', $type->require_attachment ? '1' : '0') == '1' ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="require_attachment">
                                                                {{ __('Wajib melampirkan bukti') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-secondary mt-4" role="alert">
                                            <span class="text-dark">
                                                <strong>Catatan Penting:</strong> <br>
                                                - Nama jenis cuti yang sudah terdaftar tidak dapat didaftarkan lagi.
                                                <br> - Sebelum memperbarui data, periksa dulu apakah sudah ada data yang serupa atau identik untuk menghindari duplikasi.
                                                {{-- <br> - <strong>Annual Leave:</strong> jangan centang "syarat khusus" — biarkan lewat jalur lama yang sudah berjalan. --}}
                                            </span>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <a href="{{ route('pages.Leavestype') }}" class="btn btn-secondary">
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
    <script>
        // Tampilkan/sembunyikan blok aturan mengikuti saklar "syarat khusus"
        (function () {
            const chkSpecial = document.getElementById('is_special');
            const box        = document.getElementById('special-rules-box');

            function toggleBox() {
                box.style.display = chkSpecial.checked ? 'block' : 'none';
            }

            chkSpecial.addEventListener('change', toggleBox);
            toggleBox(); // saat halaman dimuat (data existing / setelah validasi gagal)

            // fixed_days dan max_days saling meniadakan
            const fixedDays = document.getElementById('fixed_days');
            const maxDays   = document.getElementById('max_days');

            function toggleMaxDays() {
                const locked = fixedDays.value !== '';
                maxDays.disabled = locked;
                if (locked) maxDays.value = '';
            }

            fixedDays.addEventListener('input', toggleMaxDays);
            toggleMaxDays();
        })();
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
                    document.getElementById('position-edit').submit();
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