@extends('layouts.app')
@section('title', 'Edited Fingerprints')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
</style>
@endpush



@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edited Fingerprints</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i>Edited Fingerprints</h6>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No.</th>
                                                <th class="text-center">Pin</th>
                                                <th class="text-center">Employee</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Store </th>
                                                <th class="text-center">Scan Date </th>
                                                <th class="text-center">Scan 1</th>
                                                <th class="text-center">Device 1</th>
                                                <th class="text-center">Scan 2</th>
                                                <th class="text-center">Device 2</th>
                                                <th class="text-center">Scan 3</th>
                                                <th class="text-center">Device 3</th>
                                                <th class="text-center">Scan 4</th>
                                                <th class="text-center">Device 4</th>
                                                <th class="text-center">Scan 5</th>
                                                <th class="text-center">Device 5</th>
                                                {{-- <th class="text-center">Scan 6</th>
                                                <th class="text-center">Device 6</th>
                                                <th class="text-center">Scan 7</th>
                                                <th class="text-center">Device 7</th>
                                                <th class="text-center">Scan 8</th>
                                                <th class="text-center">Device 8</th>
                                                <th class="text-center">Scan 9</th>
                                                <th class="text-center">Device 9</th>
                                                <th class="text-center">Scan 10</th>
                                                <th class="text-center">Device 10</th> --}}
                                                <th class="text-center">Duration</th>
                                                <th class="text-center">Attachment</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="action-buttons">
                                    <button type="button" onclick="window.location='{{ route('Department.create') }}'"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus-circle"></i> Create Departments
                                    </button>
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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
   
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 
<script>
$(document).on('click', '.view-image', function () {
    const imageUrl = $(this).data('image-url');
    Swal.fire({
        title: 'Attachment',
        imageUrl: imageUrl,
        imageAlt: 'Attachment Image',
        confirmButtonText: 'Tutup'
    });
});
</script>

    <script>
        // Wait for jQuery to be fully loaded
        jQuery(document).ready(function($) {
            // Initialize DataTable with proper configuration
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('editedfinger.editedfinger') }}',
                    type: 'GET'
                },
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                },
                columns: [{
                        data: null,
                        name: 'id',
                        className: 'text-center align-middle',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'pin',
                        name: 'pin',
                        className: 'text-center'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        className: 'text-center'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name',
                        className: 'text-center'
                    },
                    {
                        data: 'store_name',
                        name: 'store_name',
                        className: 'text-center'
                    },
                    {
                        data: 'scan_date',
                        name: 'scan_date',
                        className: 'text-center'
                    },
                    {
                        data: 'in_1',
                        name: 'in_1',
                        className: 'text-center'
                    },
                    {
                        data: 'device_1',
                        name: 'device_1',
                        className: 'text-center'
                    },
                    {
                        data: 'in_2',
                        name: 'in_2',
                        className: 'text-center'
                    },
                     {
                        data: 'device_2',
                        name: 'device_2',
                        className: 'text-center'
                    },
                    {
                        data: 'in_3',
                        name: 'in_3',
                        className: 'text-center'
                    },
                     {
                        data: 'device_3',
                        name: 'device_3',
                        className: 'text-center'
                    },
                    {
                        data: 'in_4',
                        name: 'in_4',
                        className: 'text-center'
                    },
                      {
                        data: 'device_4',
                        name: 'device_4',
                        className: 'text-center'
                    },
                    {
                        data: 'in_5',
                        name: 'in_5',
                        className: 'text-center'
                    },
                      {
                        data: 'device_5',
                        name: 'device_5',
                        className: 'text-center'
                    },
                    // {
                    //     data: 'in_6',
                    //     name: 'in_6',
                    //     className: 'text-center'
                    // },
                    //   {
                    //     data: 'device_6',
                    //     name: 'device_6',
                    //     className: 'text-center'
                    // },
                    // {
                    //     data: 'in_7',
                    //     name: 'in_7',
                    //     className: 'text-center'
                    // },
                    //   {
                    //     data: 'device_7',
                    //     name: 'device_7',
                    //     className: 'text-center'
                    // },
                    // {
                    //     data: 'in_8',
                    //     name: 'in_8',
                    //     className: 'text-center'
                    // },
                    //   {
                    //     data: 'device_8',
                    //     name: 'device_8',
                    //     className: 'text-center'
                    // },
                    // {
                    //     data: 'in_9',
                    //     name: 'in_9',
                    //     className: 'text-center'
                    // },
                    //   {
                    //     data: 'device_9',
                    //     name: 'device_9',
                    //     className: 'text-center'
                    // },
                    // {
                    //     data: 'in_10',
                    //     name: 'in_10',
                    //     className: 'text-center'
                    // },
                    //   {
                    //     data: 'device_10',
                    //     name: 'device_10',
                    //     className: 'text-center'
                    // },
                    {
                        data: 'duration',
                        name: 'duration',
                        className: 'text-center'
                    },
                    {
            data: 'attachment',
            name: 'attachment',
            orderable: false,
            searchable: false,
            render: function (data, type, row, meta) {
                return data; // <- ini penting, biar HTML tombol tidak escape jadi teks
            }
        }
                ],
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-control');
                }
            });
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                });
            @endif
        });
    </script>
@endpush
