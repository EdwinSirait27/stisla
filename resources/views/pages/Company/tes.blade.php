@extends('layouts.user_type.auth')
@section('title', 'Kemara-ES | Alumni')

@section('content')
    <style>
        .text-center {
            text-align: center;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    {{-- <h6>Role & Hak Akses</h6> --}}
                    <h6><i class="fas fa-user-shield"></i> Data Alumni</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0"id="users-table">
                            <thead>
                                <tr>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        No.</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Foto</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Nama Lengkap</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Alamat</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Email</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Nomor Telephone</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Tahun Masuk</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Tahun Lulus</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Instagram</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        LinedIn</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        TikTok</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Facebook</th>


                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action</th>
                                    <th>
                                        <button type="button" id="select-all" class="btn btn-primary btn-sm">
                                            Select All
                                        </button>
                                    </th>
                                </tr>
                            </thead>

                        </table>
                        {{-- <button type="button" onclick="window.location='{{ route('Profile.create') }}'"
                            class="btn btn-primary btn-sm">
                            Buat
                        </button>
                        <button type="button" id="delete-selected" class="btn btn-danger btn-sm">
                            Delete
                        </button> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTable dengan fitur buttons
        let table = $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('alumniall.alumniall') }}',
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            // Menggunakan dom yang mencakup length menu (l), buttons (B), filter (f), tabel (rt), info (i), dan pagination (p)
            dom: 'lBfrtip',
            buttons: [
                {
                    extend: 'copy',
                    text: 'Copy Data',
                    className: 'btn btn-primary',
                    exportOptions: {
                        columns: [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] // Exclude foto and action columns
                    }
                },
                {
                    extend: 'csv',
                    text: 'Export CSV',
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] // Exclude foto and action columns
                    }
                }
            ],
            columns: [
                {
                    data: 'id',
                    name: 'id',
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                {
                        data: 'foto',
                        name: 'foto',
                        className: 'text-center',
                        render: function(data, type, full, meta) {
                            let imageUrl = data ? '{{ asset('storage/alumni') }}/' + data :
                                '{{ asset('storage/alumni/we.jpg') }}';
                            return '<a href="#" class="open-image-modal" data-src="' + imageUrl +
                                '">' +
                                '<img src="' + imageUrl +
                                '" width="100" style="cursor:pointer;" />' +
                                '</a>';
                        }
                    },
                { data: 'NamaLengkap', name: 'NamaLengkap', className: 'text-center' },
                { data: 'Alamat', name: 'Alamat', className: 'text-center' },
                { data: 'Email', name: 'Email', className: 'text-center' },
                { data: 'NomorTelephone', name: 'NomorTelephone', className: 'text-center' },
                { data: 'TahunMasuk', name: 'TahunMasuk', className: 'text-center' },
                { data: 'TahunLulus', name: 'TahunLulus', className: 'text-center' },
                { data: 'Ig', name: 'Ig', className: 'text-center' },
                { data: 'Linkedin', name: 'Linkedin', className: 'text-center' },
                { data: 'Tiktok', name: 'Tiktok', className: 'text-center' },
                { data: 'Facebook', name: 'Facebook', className: 'text-center' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'id',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'export') {
                            return ''; // Exclude checkboxes from exports
                        }
                        return `<input type="checkbox" class="user-checkbox" value="${row.id}">`;
                    }
                }
            ]
        });

        // Event handler untuk select all checkbox
        $('#select-all').on('click', function() {
            let checkboxes = $('.user-checkbox');
            let allChecked = checkboxes.filter(':checked').length === checkboxes.length;
            checkboxes.prop('checked', !allChecked);
        });

        // Inisialisasi tooltip
        $(document).on('mouseenter', '[data-bs-toggle="tooltip"]', function() {
            $(this).tooltip();
        });

        // Handler untuk delete selected users
        $('#delete-selected').on('click', function() {
            let selectedIds = $('.user-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Ada profile Yang Dipilih',
                    text: 'Tolong Pilih Salah Satu.'
                });
                return;
            }

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Tidak Bisa Diubah Lagi Jika di di Delete!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Iya, Delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('profile.delete') }}',
                        method: 'POST',
                        data: {
                            ids: selectedIds,
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                );
                                table.ajax.reload();
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    'Failed to delete profile.',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'An error occurred while deleting profile.',
                                'error'
                            );
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        });

        // Handler untuk image modal
        $(document).on('click', '.open-image-modal', function(e) {
    e.preventDefault();
    let imgSrc = $(this).data('src');
    Swal.fire({
        imageUrl: imgSrc,
        imageAlt: 'Alumni Photo',
        showConfirmButton: false,
        showCloseButton: true, // Tambahkan tombol close (X)
        width: 'auto'
    });
});
    });
</script>
  
    @if (session('warning'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: '{{ session('warning') }}',
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
            });
        </script>
    @endif
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Good...',
                text: '{{ session('success') }}',
            });
        </script>
    @endif
@endsection