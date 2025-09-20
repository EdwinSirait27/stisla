@extends('layouts.app')
@section('title', 'Fingerprints')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush
<style>
    .card {
        border: none;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08);
        border-radius: 0.5rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        background-color: #fff;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.12);
    }

    .card-header {
        background-color: #f8fafc;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        padding: 1.25rem 1.5rem;
    }

    .card-header h6 {
        margin: 0;
        font-weight: 600;
        color: #4a5568;
        display: flex;
        align-items: center;
        font-size: 0.95rem;
    }

    .card-header h6 i {
        margin-right: 0.75rem;
        color: #5e72e4;
        transition: color 0.3s ease;
    }

    /* Table Styles */
    .table-responsive {
        padding: 0 1.5rem;
        overflow: hidden;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background-color: #f8fafc;
        color: #4a5568;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
        position: sticky;
        top: 0;
        z-index: 10;
        transition: all 0.3s ease;
        /* width: 200px; */
    }

    .th-name {
        width: 300px !important;
        /* lebar khusus untuk kolom NAME */
    }

    .table tbody tr {
        transition: all 0.25s ease;
        position: relative;
    }

    .table tbody tr:not(:last-child)::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: rgba(0, 0, 0, 0.05);
    }

    .table tbody tr:hover {
        background-color: rgba(94, 114, 228, 0.03);
        transform: scale(1.002);
    }

    .table tbody td {
        padding: 1.1rem 0.75rem;
        vertical-align: middle;
        color: #4a5568;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        border: none;
        background: #fff;
    }

    .table tbody tr:hover td {
        color: #2d3748;
    }

    /* Text alignment for specific columns */
    .text-center {
        text-align: center;
    }

    /* Action Buttons */
    .action-buttons {
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: flex-end;
    }

    .btn-primary {
        background-color: #5e72e4;
        border-color: #5e72e4;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #4a5bd1;
        border-color: #4a5bd1;
        transform: translateY(-1px);
    }

    /* Section Header */
    .section-header h1 {
        font-weight: 600;
        color: #2d3748;
        font-size: 1.5rem;
    }

    /* Smooth scroll for table */
    .table-responsive {
        -webkit-overflow-scrolling: touch;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            padding: 0 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header {
            padding: 1rem;
        }

        .table thead th {
            font-size: 0.65rem;
            padding: 0.75rem 0.5rem;
        }

        .table tbody td {
            padding: 0.85rem 0.5rem;
            font-size: 0.8rem;
        }
    }

    div.dataTables_scrollBody {
        max-height: 750px !important;
    }
</style>


@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Fingerprints</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i> List Fingerprints</h6>
                            </div>


                            <div class="card-body">

                                <div class="row mb-2 align-items-end">
                                    <div class="col-md-2">
                                        <label for="store_name">Filter By Location</label>
                                        <select id="store_name" name="store_name" class="form-control select2">
                                            <option value="">All Stores</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store }}">{{ $store }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label for="startDate">Start Date</label>
                                        <input type="date" id="startDate"
                                            value="{{ request('start_date') ?? now()->startOfMonth()->toDateString() }}"
                                            class="form-control">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="endDate">End Date</label>
                                        <input type="date" id="endDate"
                                            value="{{ request('end_date') ?? now()->toDateString() }}" class="form-control">
                                    </div>

                                    <div class="col-md-2">
                                        <label>Show Entries</label>
                                        <div id="custom-length"></div> {{-- Di sini nanti elemen .dataTables_length akan dipindahkan --}}
                                    </div>

                                    <div class="col-md-2">
                                        <label>Search</label>
                                        <div id="custom-search"></div> {{-- Di sini nanti elemen .dataTables_filter akan dipindahkan --}}
                                    </div>

                                    <div class="col-md-2">
                                        <button id="filterBtn" class="btn btn-primary">Filter</button>
                                        <button id="resetBtn" class="btn btn-secondary">Reset</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover table-striped" id="users-table"style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Location</th>
                                                <th class="text-center">PIN</th>
                                                <th class="text-center th-name">NAME</th>
                                                <th class="text-center">NIP</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Scan Date</th>
                                                @for ($i = 1; $i <= 10; $i++)
                                                    <th class="text-center">Scan {{ $i }}</th>
                                                @endfor

                                                <th class="text-center">Duration</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Action</th>

                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="action-buttons">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>



    <!-- Modal Total Hari Bekerja -->
    <div class="modal fade" id="modalTotalHari" tabindex="-1" aria-labelledby="modalTotalHariLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTotalHariLabel">Total Hari Bekerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p><strong>PIN:</strong> <span id="modal-pin"></span></p>
                    <p><strong>Total Hari Bekerja:</strong> <span id="modal-total-hari"></span> hari</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const year = today.getFullYear();
            const month = today.getMonth(); // 0-based (Juli = 6)

            // Awal bulan
            const startDate = new Date(year, month, 1);

            // Akhir bulan
            const endDate = new Date(year, month + 1, 0);

            const formatDate = (date) => {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0'); // Bulan 1-12
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            };

            document.getElementById('startDate').value = formatDate(startDate);
            document.getElementById('endDate').value = formatDate(endDate);
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();

            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('fingerprints.fingerprints') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: function(d) {
                        d.start_date = $('#startDate').val();
                        d.end_date = $('#endDate').val();
                        d.store_name = $('#store_name').val();
                    }
                },
                scrollX: '500px',
                scrollCollapse: false,
                // paging: false,

                dom: "<'d-none'lf>" + // ini akan menyembunyikan tapi tetap membuat elemen length & filter
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-2'<'col-sm-12'B>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [

                    {
                        extend: 'csv',
                        className: 'btn btn-sm btn-success',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        exportOptions: {
                            columns: ':not(:last-child):not(.no-export)' // kolom terakhir dan kelas 'no-export' tidak ikut
                        }
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-sm btn-info',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        exportOptions: {
                            columns: ':not(:last-child):not(.no-export)' // kolom terakhir dan kelas 'no-export' tidak ikut
                        }
                    }
                ],

                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                },
                columns: [{
                        data: 'name',
                        name: 'name',
                        className: 'text-center'
                    },
                    {
                        data: 'pin',
                        name: 'pin',
                        className: 'text-center'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        className: 'text-center',
                        width: '300px'
                    },
                    {
                        data: 'employee_pengenal',
                        name: 'employee_pengenal',
                        className: 'text-center'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name',
                        className: 'text-center'
                    },
                    {
                        data: 'scan_date',
                        name: 'scan_date',
                        className: 'text-center'
                    },



                    @for ($i = 1; $i <= 10; $i++)
                        {
                            data: 'combine_{{ $i }}',
                            name: 'combine_{{ $i }}',
                            className: 'text-center'
                        }
                        @if ($i < 10)
                            ,
                        @endif
                    @endfor ,
                    {
                        data: 'duration',
                        name: 'duration'
                    },
                    {
                        data: 'updated',
                        name: 'updated',
                        render: function(data, type, row) {
                            if (row.is_updated) {
                                return '<span class="badge badge-success">✔ Updated</span>';
                            } else {
                                return '<span class="badge badge-secondary">Original</span>';
                            }
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'no-export'
                    }
                ],
                rowCallback: function(row, data, index) {
                    if (data.is_edited == 1) {
                        $(row).css('background-color', '#cce5ff');
                    }
                },

                initComplete: function() {
                    // Pindahkan length (show entries) dan search ke lokasi custom
                    $('#custom-length').html($('.dataTables_length'));
                    $('#custom-search').html($('.dataTables_filter'));
                }
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                });
            @endif

            $('#filterBtn').on('click', function() {
                table.ajax.reload();
            });

            $('#resetBtn').on('click', function() {
                $('#startDate').val('');
                $('#endDate').val('');
                $('#store_name').val('');
                table.ajax.reload();
            });

            setInterval(function() {
                var isSearching = $('.dataTables_filter input').val().trim().length > 0;
                if (!isSearching) {
                    table.ajax.reload(null, false);
                }
            }, 100000);
        });
        $(document).on('click', '.lihat-total', function() {
            const pin = $(this).data('pin');
            const employee = $(this).data('employee');
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();

            if (!startDate || !endDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal kosong!',
                    text: 'Mohon isi tanggal awal dan akhir terlebih dahulu.',
                });
                return;
            }

            $.ajax({
                url: "{{ route('Fingerprints.totalHari') }}",
                method: 'GET',
                data: {
                    pin: pin,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: `Total kehadiran ${employee}`,
                        text: `${response.total} hari`,
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Gagal mengambil data kehadiran.',
                    });
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
